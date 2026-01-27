<?php

use Illuminate\Support\Facades\Route;
use Lyre\School\Http\Controllers;

Route::prefix('api')
    ->middleware(['api', \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class])
    ->group(function () {
        Route::apiResources([
            'assessments' => Controllers\AssessmentController::class,
            'assessmentattempts' => Controllers\AssessmentAttemptController::class,
            'assessmenttasks' => Controllers\AssessmentTaskController::class,
            'tasks' => Controllers\TaskController::class,
            'taskanswers' => Controllers\TaskAnswerController::class,
            'selectedtaskanswers' => Controllers\SelectedTaskAnswerController::class,
        ]);

        Route::get('assessments/{assessment}/publish/', [Controllers\AssessmentController::class, 'publish']);
        Route::get('assessmentattempts/{assessmentattempt}/submit/', [Controllers\AssessmentAttemptController::class, 'submit']);
    });
