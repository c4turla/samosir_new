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
use App\Http\Controllers\DepartureController;
use App\Http\Controllers\UnloadingController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\VesselPositionController;
use App\Http\Controllers\ReportArrivalController;
use App\Http\Controllers\ReportDepartureController;
use App\Http\Controllers\ReportVesselController;
use App\Http\Controllers\ReportCatchController;

// Guest routes (no authentication required)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

// Protected routes (authentication required)
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/vessel-positions', [VesselPositionController::class, 'index'])->name('vessel-positions.index');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/api/search', [GlobalSearchController::class, 'search'])->name('search');
    
    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
        Route::post('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/signature', [ProfileController::class, 'updateSignature'])->name('profile.signature');
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
        Route::get('/{arrival}', [ArrivalController::class, 'show'])->name('arrivals.show');
        Route::get('/{arrival}/edit', [ArrivalController::class, 'edit'])->name('arrivals.edit');
        Route::put('/{arrival}', [ArrivalController::class, 'update'])->name('arrivals.update');
        Route::delete('/{arrival}', [ArrivalController::class, 'destroy'])->name('arrivals.destroy');
        Route::put('/{arrival}/approve', [ArrivalController::class, 'approve'])->name('arrivals.approve');
        Route::put('/{arrival}/reject', [ArrivalController::class, 'reject'])->name('arrivals.reject');
    });
    
    // Departures routes
    Route::prefix('departures')->group(function () {
        Route::get('/', [DepartureController::class, 'index'])->name('departures.index');
        Route::get('/create', [DepartureController::class, 'create'])->name('departures.create');
        Route::post('/', [DepartureController::class, 'store'])->name('departures.store');
        Route::get('/{departure}', [DepartureController::class, 'show'])->name('departures.show');
        Route::get('/{departure}/edit', [DepartureController::class, 'edit'])->name('departures.edit');
        Route::put('/{departure}', [DepartureController::class, 'update'])->name('departures.update');
        Route::delete('/{departure}', [DepartureController::class, 'destroy'])->name('departures.destroy');
        Route::post('/{departure}/approve', [DepartureController::class, 'approve'])->name('departures.approve');
        Route::post('/{departure}/reject', [DepartureController::class, 'reject'])->name('departures.reject');
    });

    // SPR Departures routes
    Route::prefix('spr-departures')->group(function () {
        Route::get('/', [App\Http\Controllers\SprDepartureController::class, 'index'])->name('spr-departures.index');
        Route::get('/{sprDeparture}', [App\Http\Controllers\SprDepartureController::class, 'show'])->name('spr-departures.show');
    });
    
    // Unloadings routes
    Route::prefix('unloadings')->group(function () {
        Route::get('/', [UnloadingController::class, 'index'])->name('unloadings.index');
        Route::get('/create', [UnloadingController::class, 'create'])->name('unloadings.create');
        Route::post('/', [UnloadingController::class, 'store'])->name('unloadings.store');
        Route::get('/{unloading}/edit', [UnloadingController::class, 'edit'])->name('unloadings.edit');
        Route::put('/{unloading}', [UnloadingController::class, 'update'])->name('unloadings.update');
        Route::delete('/{unloading}', [UnloadingController::class, 'destroy'])->name('unloadings.destroy');
        Route::get('/{unloading}/print', [UnloadingController::class, 'print'])->name('unloadings.print');
    });
    
    // Approval routes (syahbandar only)
    Route::prefix('approval')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('approval.index');
        Route::get('/{unloading}', [ApprovalController::class, 'show'])->name('approval.show');
        Route::post('/{unloading}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/{unloading}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
    });
    
    // Reports routes
    Route::prefix('reports')->group(function () {
        Route::get('/arrivals', [ReportArrivalController::class, 'index'])->name('reports.arrivals');
        Route::get('/arrivals/export-excel', [ReportArrivalController::class, 'exportExcel'])->name('reports.arrivals.excel');
        Route::get('/arrivals/export-pdf', [ReportArrivalController::class, 'exportPdf'])->name('reports.arrivals.pdf');
        Route::get('/departures', [ReportDepartureController::class, 'index'])->name('reports.departures');
        Route::get('/departures/export-excel', [ReportDepartureController::class, 'exportExcel'])->name('reports.departures.excel');
        Route::get('/departures/export-pdf', [ReportDepartureController::class, 'exportPdf'])->name('reports.departures.pdf');
        Route::get('/vessels', [ReportVesselController::class, 'index'])->name('reports.vessels');
        Route::get('/vessels/export-excel', [ReportVesselController::class, 'exportExcel'])->name('reports.vessels.excel');
        Route::get('/vessels/export-pdf', [ReportVesselController::class, 'exportPdf'])->name('reports.vessels.pdf');
        Route::get('/catches', [ReportCatchController::class, 'index'])->name('reports.catches');
        Route::get('/catches/export-excel', [ReportCatchController::class, 'exportExcel'])->name('reports.catches.excel');
        Route::get('/catches/export-pdf', [ReportCatchController::class, 'exportPdf'])->name('reports.catches.pdf');
    });

    // Notifications routes
    Route::post('/notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Chat routes
    Route::prefix('chat')->group(function () {
        Route::get('/users', [App\Http\Controllers\ChatController::class, 'getUsers'])->name('chat.users');
        Route::get('/conversations', [App\Http\Controllers\ChatController::class, 'getConversations'])->name('chat.conversations');
        Route::get('/conversations/{conversation}/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
        Route::post('/messages', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    });
});
