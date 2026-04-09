<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\AuthorApiController;
use App\Http\Controllers\Api\BookApiController;
use App\Http\Controllers\Api\BorrowApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\LanguageApiController;
use App\Http\Controllers\Api\LibraryCardApiController;
use App\Http\Controllers\Api\PublisherApiController;
use App\Http\Controllers\Api\ReaderApiController;
use App\Http\Controllers\Api\ShelfApiController;
use App\Http\Controllers\Api\StaffApiController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthApiController::class, 'me']);
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);

    Route::get('/dashboard', [DashboardApiController::class, 'index']);

    Route::apiResource('authors', AuthorApiController::class)->parameters(['authors' => 'author']);
    Route::apiResource('categories', CategoryApiController::class)->parameters(['categories' => 'category']);
    Route::apiResource('publishers', PublisherApiController::class)->parameters(['publishers' => 'publisher']);
    Route::apiResource('languages', LanguageApiController::class)->parameters(['languages' => 'language']);
    Route::apiResource('shelves', ShelfApiController::class)->parameters(['shelves' => 'shelf']);
    Route::apiResource('books', BookApiController::class)->parameters(['books' => 'book']);
    Route::apiResource('readers', ReaderApiController::class)->parameters(['readers' => 'reader']);
    Route::apiResource('library-cards', LibraryCardApiController::class)->parameters(['library-cards' => 'libraryCard']);

    Route::apiResource('borrows', BorrowApiController::class)
        ->only(['index', 'store', 'show', 'destroy'])
        ->parameters(['borrows' => 'borrow']);
    Route::post('/borrows/{borrow}/return', [BorrowApiController::class, 'returnBook']);
    Route::post('/borrows/{borrow}/extend', [BorrowApiController::class, 'extend']);

    Route::middleware('role:Quản lý thư viện')->group(function () {
        Route::apiResource('staffs', StaffApiController::class)->parameters(['staffs' => 'staff']);
    });
});
