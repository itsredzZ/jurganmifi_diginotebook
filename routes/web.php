<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('pages.dashboard'))->name('dashboard');
Route::get('/inventory', fn () => view('pages.inventory'))->name('inventory');
Route::get('/sales', fn () => view('pages.sales'))->name('sales');
Route::get('/profit', fn () => view('pages.profit'))->name('profit');
Route::get('/business-strategy', fn () => view('pages.strategy'))->name('business-strategy');