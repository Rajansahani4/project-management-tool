<?php

use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Signed download route — no JWT required, URL signature provides auth
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download')
        ->middleware('signed');

    Route::prefix('auth')->group(function () {
        Route::post('register', RegisterController::class);
        Route::post('login', LoginController::class);

        Route::middleware('auth:api')->group(function () {
            Route::post('logout', LogoutController::class);
            Route::get('me', MeController::class);
            Route::patch('profile', [ProfileController::class, 'update'])->name('auth.profile');
            Route::post('change-password', ChangePasswordController::class)->name('auth.change-password');
        });
    });

    Route::middleware('auth:api')->group(function () {
        Route::apiResource('projects', ProjectController::class);

        Route::prefix('projects/{project}')->group(function () {
            // Members
            Route::get('members', [ProjectMemberController::class, 'index'])->name('projects.members.index');
            Route::post('members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
            Route::patch('members/{member}', [ProjectMemberController::class, 'update'])->name('projects.members.update');
            Route::delete('members/{member}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');

            // Tasks
            Route::apiResource('tasks', TaskController::class);
            Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
            Route::patch('tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');
            Route::post('tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');

            Route::prefix('tasks/{task}')->group(function () {
                Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
                Route::patch('comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
                Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

                Route::post('attachments', [AttachmentController::class, 'store'])->name('attachments.store');
                Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
            });
        });
    });
});
