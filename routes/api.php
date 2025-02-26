<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Api\V1\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Api\V1\Admin\TourController as AdminTourController;
use App\Http\Controllers\Api\V1\Admin\TravelController as AdminTravelController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\TourController;
use App\Http\Controllers\Api\V1\TravelController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'travels',
], static function () {
    Route::get('/', [TravelController::class, 'index'])->name('travels.index');
    Route::group([
        'prefix' => '{travel:slug}/tours',
    ], static function () {
        Route::get('/', [TourController::class, 'index'])->name('tours.index');
        Route::get('{tour}', [TourController::class, 'show'])->name('tours.show');
        Route::group([
            'prefix' => '{tour}/comments',
            'middleware' => ['throttle:api', 'auth:sanctum'],
            'as' => 'travels.tour.comments.',
        ], static function () {
            Route::post('/', CommentController::class)->name('store');
        });
    });
});

Route::group([
    'prefix' => 'auth',
    'middleware' => ['throttle:api'],
    'as' => 'auth.',
], static function () {
    Route::post('register', RegisterController::class)->name('register')->middleware('guest');
    Route::post('login', LoginController::class)->name('login')->middleware('guest');
    Route::post('logout', LogoutController::class)->name('logout')->middleware('auth:sanctum');
});

Route::group(
    [
        'prefix' => 'admin',
        'middleware' => ['throttle:api', 'auth:sanctum'],
        'as' => 'admin.',
    ],
    static function () {
        Route::group([
            'prefix' => 'travels',
            'middleware' => 'role:ADMIN|EDITOR',
        ], static function () {
            Route::post('store', [AdminTravelController::class, 'store'])->name('travels.store');
            Route::group([
                'prefix' => '{travel:id}',
            ], static function () {
                Route::put('/', [AdminTravelController::class, 'update'])->name('travels.update');
                Route::delete('/', [AdminTravelController::class, 'destroy'])->name('travels.destroy');
                Route::group([
                    'prefix' => 'tours',
                ], static function () {
                    Route::post('store', [AdminTourController::class, 'store'])->name('tours.store');
                    Route::put('{tour}', [AdminTourController::class, 'update'])->name('tours.update');
                    Route::delete('{tour}', [AdminTourController::class, 'destroy'])->name('tours.destroy');
                    Route::delete('{tour}/files', [AdminTourController::class, 'destroyFiles'])->name(
                        'tours.destroy.files'
                    );
                });
            });
        });

        Route::group([
            'middleware' => 'role:ADMIN|EDITOR',
        ], static function () {
            Route::apiResource('comments', AdminCommentController::class)->except('store');
            Route::delete('comments/{comment}/files', [AdminCommentController::class, 'destroyFiles'])->name(
                'comments.destroy.files'
            );
        });

        Route::group([
            'prefix' => 'users',
            'middleware' => 'role:ADMIN',
            'as' => 'users.',
        ], static function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
        });
        Route::group([
            'prefix' => 'roles',
            'middleware' => 'role:ADMIN',
            'as' => 'roles.',
        ], static function () {
            Route::get('/', [AdminRoleController::class, 'index'])->name('index');
            Route::post('/users/{user}/add', [AdminRoleController::class, 'assignRolesToUser'])->name('user.add');
            Route::delete('/users/{user}/delete', [AdminRoleController::class, 'deleteRolesToUser'])->name(
                'user.delete'
            );
        });
    }
);
