<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LibraryCardController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\ShelfController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::view('/api-docs', 'swagger')->name('api.docs');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
});

Route::middleware(['auth', 'role:Quản lý thư viện,Thủ thư'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('sach', BookController::class)->parameters(['sach' => 'book']);
    Route::resource('tacgia', AuthorController::class)->except(['show'])->parameters(['tacgia' => 'author']);
    Route::resource('theloai', CategoryController::class)->except(['show'])->parameters(['theloai' => 'category']);
    Route::resource('nhaxuatban', PublisherController::class)->except(['show'])->parameters(['nhaxuatban' => 'publisher']);
    Route::resource('ngonngu', LanguageController::class)->except(['show'])->parameters(['ngonngu' => 'language']);
    Route::resource('kesach', ShelfController::class)->except(['show'])->parameters(['kesach' => 'shelf']);
    Route::resource('docgia', ReaderController::class)->parameters(['docgia' => 'reader']);
    Route::resource('thethuvien', LibraryCardController::class)->except(['show'])->parameters(['thethuvien' => 'libraryCard']);

    Route::resource('muontra', BorrowController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
        ->parameters(['muontra' => 'borrow']);
    Route::post('/muontra/{borrow}/return', [BorrowController::class, 'returnBook'])->name('muontra.return');
    Route::post('/muontra/{borrow}/extend', [BorrowController::class, 'extend'])->name('muontra.extend');

    Route::middleware('role:Quản lý thư viện')->group(function () {
        Route::resource('nhanvien', StaffController::class)->parameters(['nhanvien' => 'staff']);
    });
});
