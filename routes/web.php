<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Web\BattleController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\CompareController;
use App\Http\Controllers\Web\CustomPokemonController;
use App\Http\Controllers\Web\MoveController;
use App\Http\Controllers\Web\DamageCalcController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\MasterDataController;
use App\Http\Controllers\Web\PokemonController;
use App\Http\Controllers\Web\TeamController;
use Illuminate\Support\Facades\Route;

// 認証ルート（ゲスト用）
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class,   'showForm'])->name('login');
    Route::post('/login',   [LoginController::class,   'login']);
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register',[RegisterController::class, 'register']);

    // パスワードリセット
    Route::get('/forgot-password',  [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password',        [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// 認証必須ルート
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('pokemon')->name('pokemon.')->group(function () {
        Route::get('/', [PokemonController::class, 'index'])->name('index');
        Route::get('/type-chart', [PokemonController::class, 'typeChart'])->name('type-chart');
        Route::get('/{id}', [PokemonController::class, 'show'])->name('show');
    });

    Route::prefix('moves')->name('moves.')->group(function () {
        Route::get('/', [MoveController::class, 'index'])->name('index');
        Route::get('/{id}', [MoveController::class, 'show'])->name('show');
    });

    Route::prefix('custom-pokemon')->name('custom-pokemon.')->group(function () {
        Route::get('/', [CustomPokemonController::class, 'index'])->name('index');
        Route::get('/create', [CustomPokemonController::class, 'create'])->name('create');
        Route::get('/bulk', [CustomPokemonController::class, 'bulk'])->name('bulk');
        Route::get('/{id}', [CustomPokemonController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CustomPokemonController::class, 'edit'])->name('edit');
    });

    Route::prefix('damage-calc')->name('damage-calc.')->group(function () {
        Route::get('/', [DamageCalcController::class, 'index'])->name('index');
        Route::get('/formula', [DamageCalcController::class, 'formula'])->name('formula');
        Route::get('/speed-compare', [DamageCalcController::class, 'speedCompare'])->name('speed-compare');
    });

    Route::get('/compare', [CompareController::class, 'index'])->name('compare');

    Route::prefix('master')->name('master.')->group(function () {
        Route::get('/abilities', [MasterDataController::class, 'abilities'])->name('abilities');
        Route::get('/items',     [MasterDataController::class, 'items'])->name('items');
        Route::get('/moves',     [MasterDataController::class, 'moves'])->name('moves');
        Route::get('/pokemon',   [MasterDataController::class, 'pokemon'])->name('pokemon');
        Route::get('/import',    [MasterDataController::class, 'import'])->name('import');
    });

    Route::prefix('battles')->name('battles.')->group(function () {
        Route::get('/', [BattleController::class, 'index'])->name('index');
        Route::get('/create', [BattleController::class, 'create'])->name('create');
        Route::get('/{id}', [BattleController::class, 'show'])->name('show');
    });

    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.update-info');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
