<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TourController;
use Illuminate\Support\Facades\Route;

// Trang chủ
Route::get('/', function () {
    $featuredTours = \App\Models\Tour::with('destination')
        ->where('is_active', true)
        ->where('is_featured', true)
        ->latest()
        ->take(6)
        ->get();

    $destinations = \App\Models\Destination::where('is_active', true)
        ->withCount(['tours' => fn ($q) => $q->where('is_active', true)])
        ->take(8)
        ->get();

    return view('welcome', compact('featuredTours', 'destinations'));
})->name('home');

// Tours (public)
Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/tours/{slug}', [TourController::class, 'show'])->name('tours.show');

// Destinations (public)
Route::get('/diem-den', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('/diem-den/{slug}', [DestinationController::class, 'show'])->name('destinations.show');

// Dashboard & Profile (auth)
Route::get('/dashboard', function () {
    $myBookings = \App\Models\Booking::with('tour')
        ->where('user_id', auth()->id())
        ->latest()
        ->take(5)
        ->get();
    return view('dashboard', compact('myBookings'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Hồ sơ cá nhân
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Đặt tour
    Route::get('/tours/{slug}/dat-tour', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
});

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', fn () => redirect()->route('admin.tours.index'))->name('home');

    Route::resource('tours', \App\Http\Controllers\Admin\TourController::class);
    Route::resource('destinations', \App\Http\Controllers\Admin\DestinationController::class);
    Route::resource('bookings', \App\Http\Controllers\Admin\BookingController::class)->only(['index', 'show', 'update', 'destroy']);
});

require __DIR__.'/auth.php';
