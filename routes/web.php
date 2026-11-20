<?php

use App\Http\Controllers\GmailConnectionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::prefix('admin/gmail')->name('gmail.')->middleware('can:connect-gmail')->group(function (): void {
    Route::post('/connect', [GmailConnectionController::class, 'authorizeMailbox'])->name('connect')->block(20, 3);
    Route::get('/callback', [GmailConnectionController::class, 'callback'])->name('callback')->block(20, 3);
    Route::post('/disconnect', [GmailConnectionController::class, 'disconnect'])->name('disconnect')->block(20, 3);
});
