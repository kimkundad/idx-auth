<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\AttendeeExportController;

Route::get('/', function () {
    return redirect()->route('dashboard'); // หรือหน้าไหนก็ได้หลัง login
})->middleware('auth');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/attendees/{attendee}/label', [AttendeeController::class, 'label'])
  ->name('attendees.label')
  ->middleware('auth');


Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AttendeeController::class, 'index'])->name('dashboard');

    Route::get('/attendees/export', [AttendeeExportController::class, 'export'])
    ->name('attendees.export'); // ✅ ส่งออก Excel

    Route::get('/attendees/lookup', [AttendeeController::class, 'lookup'])->name('attendees.lookup'); // ✅ ค้นหา QR

    Route::post('/attendees/{attendee}/checkin', [AttendeeController::class, 'checkin'])->name('attendees.checkin');
    Route::get('/attendees/{attendee}/edit', [AttendeeController::class, 'edit'])->name('attendees.edit');
    Route::put('/attendees/{attendee}', [AttendeeController::class, 'update'])->name('attendees.update');
    Route::delete('/attendees/{attendee}', [AttendeeController::class, 'destroy'])->name('attendees.destroy');
});

require __DIR__.'/auth.php';
