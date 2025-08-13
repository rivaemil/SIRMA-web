<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BitacoraPdfController;

Route::view('/', 'frontend')->name('app');// routes/web.php

Route::get('/bitacoras/{id}/pdf', [BitacoraPdfController::class, 'downloadWeb'])
     ->name('bitacoras.pdf'); // si usas login por sesión, puedes envolver con ->middleware('auth')

