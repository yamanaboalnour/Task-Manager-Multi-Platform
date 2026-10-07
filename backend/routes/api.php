<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RegistrationRequestController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\SurveyResponseController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register-request', [RegistrationRequestController::class, 'store']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordController::class, 'forgot']);
    Route::post('/reset-password', [PasswordController::class, 'reset']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [ProfileController::class, 'show'])->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/registration-requests', [RegistrationRequestController::class, 'index']);
        Route::post('/registration-requests/{registrationRequest}/approve', [RegistrationRequestController::class, 'approve']);
        Route::post('/registration-requests/{registrationRequest}/reject', [RegistrationRequestController::class, 'reject']);

        Route::get('/surveys', [SurveyController::class, 'index']);
        Route::post('/surveys', [SurveyController::class, 'store']);
        Route::get('/surveys/{survey}', [SurveyController::class, 'show']);
        Route::put('/surveys/{survey}', [SurveyController::class, 'update']);
        Route::delete('/surveys/{survey}', [SurveyController::class, 'destroy']);
        Route::post('/surveys/{survey}/publish', [SurveyController::class, 'publish']);
        Route::get('/surveys/{survey}/responses', [SurveyResponseController::class, 'index']);
        Route::post('/surveys/{survey}/responses', [SurveyResponseController::class, 'store']);
        Route::get('/my-survey-responses', [SurveyResponseController::class, 'mine']);

        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::patch('/users/{user}', [UserController::class, 'update']);

        Route::get('/tasks', [TaskController::class, 'index']);
        Route::post('/tasks', [TaskController::class, 'store']);
        Route::get('/tasks/{task}', [TaskController::class, 'show']);
        Route::put('/tasks/{task}', [TaskController::class, 'update']);
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
        Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete']);
    });
});
