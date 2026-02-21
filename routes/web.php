<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FishSpeciesController;
use App\Http\Controllers\LandingSiteController;
use App\Http\Controllers\VesselController;
use App\Http\Controllers\ArrivalController;

// Guest routes (no authentication required)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

// Protected routes (authentication required)
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
        Route::post('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    });
    
    // Settings routes
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/', [SettingsController::class, 'update'])->name('settings.update');
    });
    
    // User Management routes (admin only)
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/{user}/password', [UserController::class, 'updatePassword'])->name('users.password');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
    
    // Fish Species routes
    Route::prefix('fish-species')->group(function () {
        Route::get('/', [FishSpeciesController::class, 'index'])->name('fish-species.index');
        Route::get('/create', [FishSpeciesController::class, 'create'])->name('fish-species.create');
        Route::post('/', [FishSpeciesController::class, 'store'])->name('fish-species.store');
        Route::get('/{fishSpecies}/edit', [FishSpeciesController::class, 'edit'])->name('fish-species.edit');
        Route::put('/{fishSpecies}', [FishSpeciesController::class, 'update'])->name('fish-species.update');
        Route::delete('/{fishSpecies}', [FishSpeciesController::class, 'destroy'])->name('fish-species.destroy');
    });
    
    // Landing Sites routes
    Route::prefix('landing-sites')->group(function () {
        Route::get('/', [LandingSiteController::class, 'index'])->name('landing-sites.index');
        Route::get('/create', [LandingSiteController::class, 'create'])->name('landing-sites.create');
        Route::post('/', [LandingSiteController::class, 'store'])->name('landing-sites.store');
        Route::get('/{landingSite}/edit', [LandingSiteController::class, 'edit'])->name('landing-sites.edit');
        Route::put('/{landingSite}', [LandingSiteController::class, 'update'])->name('landing-sites.update');
        Route::delete('/{landingSite}', [LandingSiteController::class, 'destroy'])->name('landing-sites.destroy');
    });
    
    // Vessels routes
    Route::prefix('vessels')->group(function () {
        Route::get('/', [VesselController::class, 'index'])->name('vessels.index');
        Route::get('/approval', [VesselController::class, 'approval'])->name('vessels.approval');
        Route::get('/create', [VesselController::class, 'create'])->name('vessels.create');
        Route::post('/', [VesselController::class, 'store'])->name('vessels.store');
        Route::get('/{vessel}/edit', [VesselController::class, 'edit'])->name('vessels.edit');
        Route::put('/{vessel}', [VesselController::class, 'update'])->name('vessels.update');
        Route::delete('/{vessel}', [VesselController::class, 'destroy'])->name('vessels.destroy');
        Route::put('/{vessel}/approve', [VesselController::class, 'approve'])->name('vessels.approve');
        Route::put('/{vessel}/reject', [VesselController::class, 'reject'])->name('vessels.reject');
        Route::post('/{vessel}/assign-manager', [VesselController::class, 'assignManager'])->name('vessels.assign-manager');
        Route::put('/{vessel}/managers/{user}', [VesselController::class, 'updateManager'])->name('vessels.update-manager');
        Route::delete('/{vessel}/managers/{user}', [VesselController::class, 'removeManager'])->name('vessels.remove-manager');
    });
    
    // Arrivals routes
    Route::prefix('arrivals')->group(function () {
        Route::get('/', [ArrivalController::class, 'index'])->name('arrivals.index');
        Route::get('/create', [ArrivalController::class, 'create'])->name('arrivals.create');
        Route::post('/', [ArrivalController::class, 'store'])->name('arrivals.store');
        Route::get('/{arrival}/edit', [ArrivalController::class, 'edit'])->name('arrivals.edit');
        Route::put('/{arrival}', [ArrivalController::class, 'update'])->name('arrivals.update');
        Route::delete('/{arrival}', [ArrivalController::class, 'destroy'])->name('arrivals.destroy');
        Route::put('/{arrival}/approve', [ArrivalController::class, 'approve'])->name('arrivals.approve');
        Route::put('/{arrival}/reject', [ArrivalController::class, 'reject'])->name('arrivals.reject');
    });
});
