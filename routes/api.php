<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// メール認証のルート
Route::get('/verify-email', VerifyEmailController::class)
    ->name('verification.verify');

Route::post('/email/resend-verification', [EmailVerificationNotificationController::class, 'resendWithoutAuth'])
    ->name('verification.resend');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// PWリセット
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [AuthController::class, 'reset']);

Route::middleware('auth:web')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:web'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/getUserInfo', [AuthController::class, 'getUserInfo']);

    Route::prefix('tickets')->group(function () {
        Route::get('/answers/{user_id}', [TicketController::class, 'getWithMatchingUser']);
        Route::post('/create', [TicketController::class, 'create']);
        Route::put('/{ticket_id}', [TicketController::class, 'edit'])->whereNumber('ticket_id');
        Route::delete('/{ticket_id}', [TicketController::class, 'delete'])->whereNumber('ticket_id');
        Route::get('/all/{category?}', [TicketController::class, 'getAll']);
        Route::get('/{ticket_id}/detail', [TicketController::class, 'getByTicketId'])->whereNumber('ticket_id');
        Route::get('/{userId}/{category?}', [TicketController::class, 'getUserTickets']);
        Route::post('/{ticket_id}/answers', [TicketController::class, 'addAnswer'])->whereNumber('ticket_id');
    });

    Route::prefix('users')->group(function () {
        Route::get('/{userId}', [AuthController::class, 'getUser']);
        Route::put('/{userId}', [AuthController::class, 'update']);
        Route::delete('/{userId}', [AuthController::class, 'delete']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:web')->get('/auth/check', [AuthController::class, 'checkAuth']);
