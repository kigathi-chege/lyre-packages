<?php

namespace Lyre\Guest\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Lyre\Guest\Models\Guest;

class MergeGuestUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $fromId;
    public int $toId;
    public int $tries = 5;
    public ?string $uniqueFor = null;

    public function __construct(int $fromId, int $toId)
    {
        $this->fromId = $fromId;
        $this->toId = $toId;
        $this->uniqueFor = 'merge_user:' . $fromId . ':' . $toId;
    }

    /**
     * Laravel's unique id behaviour (ShouldBeUnique).
     */
    public function uniqueId(): string
    {
        return (string) $this->uniqueFor;
    }

    public function handle()
    {
        Log::info('MergeUserJob starting', ['from' => $this->fromId, 'to' => $this->toId]);

        // Acquire lock: prefer Postgres advisory lock when PG; fallback to Cache::lock
        $gotLock = $this->acquireLock();
        if (!$gotLock) {
            Log::warning('Could not acquire merge lock; re-releasing job', ['from' => $this->fromId, 'to' => $this->toId]);
            $this->release(30);
            return;
        }

        try {
            $this->performMerge();
            Log::info('MergeUserJob completed successfully', ['from' => $this->fromId, 'to' => $this->toId]);
        } catch (\Throwable $e) {
            Log::error('MergeUserJob failed', ['from' => $this->fromId, 'to' => $this->toId, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        } finally {
            $this->releaseLock();
        }
    }

    protected function acquireLock(): bool
    {
        try {
            $driver = DB::getDriverName();
        } catch (\Throwable $e) {
            $driver = null;
        }

        $lockName = 'merge_user_' . $this->fromId . '_' . $this->toId;

        if ($driver === 'pgsql') {
            // Use 64-bit hash for advisory lock
            $key = $this->pgAdvisoryKey($this->fromId, $this->toId);
            try {
                $res = DB::select('SELECT pg_try_advisory_lock(?) as locked', [$key]);
                $locked = (bool) ($res[0]->locked ?? false);
                if ($locked) {
                    // remember that we have pg lock in cache so releaseLock knows to unlock
                    Cache::put('merge_lock:pg:' . $key, true, 300);
                }
                return $locked;
            } catch (\Throwable $e) {
                Log::warning('Failed to acquire pg advisory lock - falling back to cache lock', ['error' => $e->getMessage()]);
            }
        }

        // Fallback to Laravel cache lock
        try {
            $lock = Cache::lock('merge_lock:' . $lockName, 300);
            if ($lock->get()) {
                // store lock instance data in cache for release
                Cache::put('merge_lock:cache:' . $lockName, true, 300);
                return true;
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to acquire cache lock', ['error' => $e->getMessage()]);
        }

        return false;
    }

    protected function releaseLock(): void
    {
        try {
            $driver = DB::getDriverName();
        } catch (\Throwable $e) {
            $driver = null;
        }

        $lockName = 'merge_user_' . $this->fromId . '_' . $this->toId;

        if ($driver === 'pgsql') {
            $key = $this->pgAdvisoryKey($this->fromId, $this->toId);
            if (Cache::pull('merge_lock:pg:' . $key)) {
                try {
                    DB::select('SELECT pg_advisory_unlock(?)', [$key]);
                } catch (\Throwable $e) {
                    Log::warning('Failed to release pg advisory lock', ['error' => $e->getMessage()]);
                }
            }
            return;
        }

        try {
            if (Cache::pull('merge_lock:cache:' . $lockName)) {
                // release via Cache::lock() instance (best-effort)
                $lock = Cache::lock('merge_lock:' . $lockName);
                try {
                    $lock->release();
                } catch (\Throwable $e) {
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to release cache lock', ['error' => $e->getMessage()]);
        }
    }

    protected function pgAdvisoryKey(int $a, int $b): int
    {
        // combine two ints into a signed 64-bit-ish hash; keep deterministic
        // use crc32 to reduce size but keep distribution
        return abs(hexdec(substr(hash('sha256', $a . ':' . $b), 0, 15))) % 9223372036854775807;
    }

    /**
     * Build a list of discovered relation descriptors that reference User.
     *
     * Each descriptor:
     * [
     *   'type' => 'foreign'|'pivot'|'morph',
     *   'table' => 'table_name',
     *   'column' => 'foreign_key_name' OR pivot foreign column,
     *   'pivot_other_column' => (for pivot) other column if needed,
     *   'morph_id' => (for morph) id column,
     *   'morph_type' => (for morph) type column,
     * ]
     */
    protected function discoverRelations(): array
    {
        $userClass = function_exists('get_user_model') ? get_user_model() : (class_exists('App\\Models\\User') ? 'App\\Models\\User' : null);
        if (!$userClass) {
            Log::warning('Could not determine user model class during discoverRelations');
            return [];
        }

        $relations = [];

        // Get all app models using helper if available
        $modelClasses = function_exists('get_model_classes') ? get_model_classes() : null;
        if (!$modelClasses) {
            // fallback: attempt to scan App\Models namespace only
            if (class_exists('App\\Models\\User')) {
                $modelClasses = ['User' => 'App\\Models\\User'];
            } else {
                $modelClasses = [];
            }
        }

        foreach ($modelClasses as $modelName => $modelClass) {
            try {
                if (!class_exists($modelClass)) continue;
                $reflect = new \ReflectionClass($modelClass);
                if (!$reflect->isInstantiable()) continue;
                if (!$reflect->isSubclassOf(\Illuminate\Database\Eloquent\Model::class)) continue;

                $model = new $modelClass();

                // Use the existing helper if available to get relationships
                $rels = [];
                if (method_exists($modelClass, 'getModelRelationships')) {
                    try {
                        $rels = $modelClass::getModelRelationships(1);
                        // shape: ['relationName' => RelatedClass]
                    } catch (\Throwable $e) {
                        $rels = [];
                    }
                } else {
                    // fallback to reflect methods
                    $rels = $this->extractModelRelationshipsFallback($model);
                }

                foreach ($rels as $relName => $relatedClass) {
                    if ($relatedClass !== $userClass && $relatedClass !== $userClass) {
                        continue;
                    }

                    // instantiate relation safely
                    try {
                        if (!method_exists($model, $relName)) continue;
                        $relObj = $model->$relName();
                    } catch (\Throwable $e) {
                        continue;
                    }

                    // handle BelongsTo (child -> user)
                    if ($relObj instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                        // this means model has foreign key pointing to user
                        $foreignKey = $relObj->getForeignKeyName();
                        $relations[] = [
                            'type' => 'foreign',
                            'table' => $model->getTable(),
                            'column' => $foreignKey,
                        ];
                        continue;
                    }

                    // handle HasMany/HasOne (user -> model) - we'll update model foreign column
                    if (
                        $relObj instanceof \Illuminate\Database\Eloquent\Relations\HasMany ||
                        $relObj instanceof \Illuminate\Database\Eloquent\Relations\HasOne
                    ) {
                        $foreignKey = $relObj->getForeignKeyName();
                        $relations[] = [
                            'type' => 'foreign',
                            'table' => $relObj->getRelated()->getTable(),
                            'column' => $foreignKey,
                        ];
                        continue;
                    }

                    // handle BelongsToMany (pivot)
                    if ($relObj instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany) {
                        $pivotTable = $relObj->getTable();
                        $foreignPivotKey = $relObj->getForeignPivotKeyName();
                        $relatedPivotKey = $relObj->getRelatedPivotKeyName();
                        // There are two user sides possible. We'll update pivot where foreignPivotKey == fromId
                        $relations[] = [
                            'type' => 'pivot',
                            'table' => $pivotTable,
                            'column' => $foreignPivotKey,
                            'related_column' => $relatedPivotKey,
                        ];
                        continue;
                    }

                    // handle Morph relations (morphMany, morphToMany)
                    if (
                        $relObj instanceof \Illuminate\Database\Eloquent\Relations\MorphMany ||
                        $relObj instanceof \Illuminate\Database\Eloquent\Relations\MorphToMany ||
                        $relObj instanceof \Illuminate\Database\Eloquent\Relations\MorphOne
                    ) {
                        $morphType = $relObj->getMorphType();
                        $morphId = $relObj->getForeignKeyName();
                        $relations[] = [
                            'type' => 'morph',
                            'table' => $relObj->getRelated()->getTable(),
                            'morph_type' => $morphType,
                            'morph_id' => $morphId,
                        ];
                        continue;
                    }

                    // Other relation types may be ignored for now
                }
            } catch (\Throwable $e) {
                // skip problematic models
                continue;
            }
        }

        // Deduplicate by table+column/type
        $unique = [];
        foreach ($relations as $r) {
            $key = $r['type'] . '|' . $r['table'] . '|' . ($r['column'] ?? ($r['morph_id'] ?? ''));
            $unique[$key] = $r;
        }

        return array_values($unique);
    }

    protected function extractModelRelationshipsFallback($model): array
    {
        $relationships = [];
        $class = new \ReflectionClass($model);
        foreach ($class->getMethods() as $method) {
            if ($method->class !== get_class($model)) continue;
            if ($method->getNumberOfParameters() > 0) continue;
            try {
                $rel = $method->invoke($model);
                if ($rel instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                    $relationships[$method->getName()] = get_class($rel->getRelated());
                }
            } catch (\Throwable $e) {
                continue;
            }
        }
        return $relationships;
    }

    protected function performMerge(): void
    {
        $from = $this->fromId;
        $to = $this->toId;

        if ($from === $to) {
            Log::info('Merge aborted: from and to are identical', ['id' => $from]);
            return;
        }

        // Check source exists
        $userModel = function_exists('get_user_model') ? get_user_model() : (class_exists('App\\Models\\User') ? 'App\\Models\\User' : null);
        if (!$userModel) {
            throw new \RuntimeException('User model not found for merge');
        }

        $sourceExists = (bool) DB::table((new $userModel())->getTable())->where('id', $from)->exists();
        if (!$sourceExists) {
            Log::warning('Source user does not exist, nothing to merge', ['from' => $from]);
            return;
        }

        $targetExists = (bool) DB::table((new $userModel())->getTable())->where('id', $to)->exists();
        if (!$targetExists) {
            throw new \RuntimeException('Target user does not exist for merge: ' . $to);
        }

        // Discover relations dynamically
        $relations = $this->discoverRelations();
        Log::info('Discovered relations for merge', ['count' => count($relations)]);

        // For each relation, perform chunked updates
        foreach ($relations as $relation) {
            try {
                if ($relation['type'] === 'foreign') {
                    $this->updateForeignKeyInChunks($relation['table'], $relation['column'], $from, $to);
                } elseif ($relation['type'] === 'pivot') {
                    $this->updateForeignKeyInChunks($relation['table'], $relation['column'], $from, $to);
                    // Also update the other side if necessary (rare)
                    if (!empty($relation['related_column'])) {
                        $this->updateForeignKeyInChunks($relation['table'], $relation['related_column'], $from, $to);
                    }
                } elseif ($relation['type'] === 'morph') {
                    $this->updateMorphInChunks($relation['table'], $relation['morph_id'], $relation['morph_type'], $from, $to, $userModel);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to update relation during merge', [
                    'relation' => $relation,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Continue with other relations - best-effort
            }
        }

        // Finally associate orphaned guest records (Guest table) and delete source user
        try {
            // Update guest->user rows where applicable (if Guest model exists)
            if (class_exists(Guest::class)) {
                try {
                    DB::table((new Guest())->getTable())->where('user_id', $from)->update(['user_id' => $to]);
                    Log::info('Guest rows reassigned to canonical user', ['from' => $from, 'to' => $to]);
                } catch (\Throwable $e) {
                    Log::warning('Failed to reassign Guest rows', ['error' => $e->getMessage()]);
                }
            }

            // Delete source user using direct DB delete to avoid model events
            $userTable = (new $userModel())->getTable();
            DB::transaction(function () use ($userTable, $from) {
                DB::table($userTable)->where('id', $from)->delete();
            });

            Log::info('Source user deleted after merge', ['from' => $from, 'to' => $to]);
        } catch (\Throwable $e) {
            Log::error('Failed to finalize merge (delete source)', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    protected function updateForeignKeyInChunks(string $table, string $column, int $from, int $to, int $chunk = 1000): void
    {
        Log::debug('updateForeignKeyInChunks start', ['table' => $table, 'column' => $column, 'from' => $from, 'to' => $to]);

        // Ensure table exists
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                Log::debug('Skipping update: table does not exist', ['table' => $table]);
                return;
            }
        } catch (\Throwable $e) {
            Log::warning('Failed schema check, skipping table', ['table' => $table, 'error' => $e->getMessage()]);
            return;
        }

        do {
            $ids = DB::table($table)->where($column, $from)->limit($chunk)->pluck('id')->toArray();

            if (empty($ids)) break;

            DB::transaction(function () use ($table, $column, $ids, $to) {
                DB::table($table)->whereIn('id', $ids)->update([$column => $to]);
            });

            Log::debug('Chunk updated', ['table' => $table, 'column' => $column, 'count' => count($ids)]);
        } while (!empty($ids));
    }

    protected function updateMorphInChunks(string $table, string $morphId, string $morphType, int $from, int $to, string $userModel, int $chunk = 1000): void
    {
        Log::debug('updateMorphInChunks start', ['table' => $table, 'morph_id' => $morphId, 'morph_type' => $morphType, 'from' => $from, 'to' => $to]);

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                Log::debug('Skipping morph update: table does not exist', ['table' => $table]);
                return;
            }
        } catch (\Throwable $e) {
            Log::warning('Failed schema check for morph table', ['table' => $table, 'error' => $e->getMessage()]);
            return;
        }

        do {
            $ids = DB::table($table)
                ->where($morphId, $from)
                ->where($morphType, $userModel)
                ->limit($chunk)
                ->pluck('id')->toArray();

            if (empty($ids)) break;

            DB::transaction(function () use ($table, $morphId, $morphType, $ids, $to, $userModel) {
                DB::table($table)
                    ->whereIn('id', $ids)
                    ->where($morphType, $userModel)
                    ->update([$morphId => $to, $morphType => $userModel]);
            });

            Log::debug('Morph chunk updated', ['table' => $table, 'count' => count($ids)]);
        } while (!empty($ids));
    }
}
