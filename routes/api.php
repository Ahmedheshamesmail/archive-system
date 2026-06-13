<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LetterController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\ReplyController;
use App\Http\Controllers\Api\ReplyAttachmentController;
use App\Http\Controllers\Api\DashboardController;

Route::post(
    '/login',
    [AuthController::class,'login']
);

Route::middleware([
    'auth:sanctum'
])->group(function () {

    /**
     * auth
     */
    Route::post(
        '/logout',
        [AuthController::class,'logout']
    );

    Route::get(
        '/me',
        [AuthController::class,'me']
    );

    /**
     * everyone
     * admin + data_entry + viewer
     */
    Route::get(
        '/dashboard',
        [DashboardController::class,'index']
    );

    Route::get(
        '/letters',
        [LetterController::class,'index']
    );

    Route::get(
        '/letters/{id}',
        [LetterController::class,'show']
    );

    Route::get(
        '/letters/{id}/replies',
        [ReplyController::class,'index']
    );

    Route::get(
        '/replies/{id}',
        [ReplyController::class, 'show']
    );


    /**
     * admin + data_entry
     */
    Route::middleware([
        'role:admin,data_entry'
    ])->group(function () {

        Route::post(
            '/letters',
            [LetterController::class,'store']
        );

        Route::put(
            '/letters/{id}',
            [LetterController::class,'update']
        );

        Route::post(
            '/letters/{id}/replies',
            [ReplyController::class,'store']
        );

        Route::put(
            '/replies/{id}',
            [ReplyController::class,'update']
        );
    });

    /**
     * admin only
     */
    Route::middleware([
        'role:admin'
    ])->group(function () {

        Route::delete(
            '/letters/{id}',
            [LetterController::class,'destroy']
        );

        Route::delete(
            '/replies/{id}',
            [ReplyController::class,'destroy']
        );

        Route::post(
            '/letters/{id}/restore',
            [LetterController::class,'restore']
        );

        Route::post(
            '/replies/{id}/restore',
            [ReplyController::class,'restore']
        );
    });

});

 Route::get(
        '/attachments/{id}/view',
        [AttachmentController::class,'view']
    );

    Route::get(
        '/attachments/{id}/download',
        [AttachmentController::class,'download']
    );

    Route::get(
        '/reply-attachments/{id}/view',
        [ReplyAttachmentController::class,'view']
    );

    Route::get(
        '/reply-attachments/{id}/download',
        [ReplyAttachmentController::class,'download']
    );