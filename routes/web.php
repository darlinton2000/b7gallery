<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GalleryController;

Route::get('/', [GalleryController::class, 'index'])->name('index');
Route::post('/upload', [GalleryController::class, 'upload'])->name('upload');
Route::get('/delete/{id}', [GalleryController::class, 'delete'])->name('delete');
