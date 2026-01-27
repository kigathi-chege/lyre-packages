<?php

namespace Lyre\Guest\Listeners;

use Illuminate\Support\Str;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lyre\Guest\Models\Guest;

// TODO: Kigathi - August 14 2025 - We can use this to track user sessions

class UserEventSubscriber
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handleUserLogin(\Illuminate\Auth\Events\Login $event): void
    {
        try {
            $this->handleAuthEvent($event->user);
        } catch (\Throwable $e) {
            Log::error('Error in handleUserLogin', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        // Keep backwards-compat calls if you have them - guarded
        if (function_exists('after_login')) {
            try {
                after_login($event->user, request());
            } catch (\Throwable $e) {
            }
        }
    }

    public function handleUserRegistered(\Illuminate\Auth\Events\Registered $event): void
    {
        try {
            $this->handleAuthEvent($event->user);
        } catch (\Throwable $e) {
            Log::error('Error in handleUserRegistered', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        if (function_exists('after_register')) {
            try {
                after_register($event->user, request());
            } catch (\Throwable $e) {
            }
        }
    }

    public function handleUserLogout(\Illuminate\Auth\Events\Logout $event): void
    {
        if (function_exists('after_logout')) {
            try {
                after_logout($event->user, request());
            } catch (\Throwable $e) {
            }
        }
    }

    /**
     * Shared handling for login/register events.
     */
    protected function handleAuthEvent($actualUser): void
    {
        if (!$actualUser) return;

        // Ensure $actualUser is an Eloquent model instance (resolve if necessary)
        if (!$actualUser instanceof \Illuminate\Database\Eloquent\Model) {
            $userModelClass = function_exists('get_user_model')
                ? get_user_model()
                : (class_exists('App\\Models\\User') ? 'App\\Models\\User' : null);

            if ($userModelClass && method_exists($actualUser, 'getAuthIdentifier')) {
                $id = $actualUser->getAuthIdentifier();
                $actualUser = $userModelClass::find($id);
                if (!$actualUser) return;
            } else {
                return;
            }
        }

        // Quick guard - don't merge if flagged as guest or if account is invalid
        if ($actualUser->is_guest ?? false) return;

        // Resolve guest from request attributes or helper
        $request = request();
        $guest = $request->attributes->get('guest');

        if (!$guest) {
            // fallback to helper (if exists)
            if (function_exists('retrieve_guest_uuid')) {
                $guestUuid = retrieve_guest_uuid($request);
                if ($guestUuid && Str::isUuid($guestUuid)) {
                    $guest = Guest::where('uuid', $guestUuid)->first();
                }
            }
        }

        if (!$guest) return;

        // Refresh guest to ensure model has proper connection
        try {
            $guest = Guest::where('id', $guest->id)->first();
        } catch (\Throwable $e) {
            Log::warning('Could not refresh guest model', ['error' => $e->getMessage()]);
            return;
        }

        if (!$guest) return;

        // Find the guest user (dummy account) - may be null
        $guestUser = null;
        try {
            $guestUser = $guest->user ?? null;
        } catch (\Throwable $e) {
            Log::warning('Failed to access guest->user', ['error' => $e->getMessage()]);
            $guestUser = null;
        }

        if (!$guestUser) {
            // Simple path: associate guest row to actual user id (no dummy present)
            try {
                DB::table($guest->getTable())->where('id', $guest->id)->update(['user_id' => $actualUser->id]);
                Log::info('Associated guest row to actual user (no dummy user present)', [
                    'guest_id' => $guest->id,
                    'user_id' => $actualUser->id,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to associate guest row to user', ['guest_id' => $guest->id, 'user_id' => $actualUser->id, 'error' => $e->getMessage()]);
            }
            return;
        }

        // If guest user is already the real user, nothing to do
        if ($guestUser->getKey() === $actualUser->getKey()) {
            Log::debug('Guest user equals actual user; skipping merge', ['guest_user_id' => $guestUser->id, 'actual_user_id' => $actualUser->id]);
            return;
        }

        // Dispatch merge job (queued). The job is idempotent and safe to re-run.
        try {
            \Lyre\Guest\Jobs\MergeGuestUser::dispatch($guestUser->getKey(), $actualUser->getKey())
                ->onQueue(config('lyre.merge_queue', 'merges'));
            Log::info('MergeUserJob dispatched', ['from' => $guestUser->id, 'to' => $actualUser->id]);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch MergeUserJob', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            \Illuminate\Auth\Events\Login::class => 'handleUserLogin',
            \Illuminate\Auth\Events\Logout::class => 'handleUserLogout',
            \Illuminate\Auth\Events\Registered::class => 'handleUserRegistered',
        ];
    }
}
