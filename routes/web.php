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
use App\Http\Controllers\ReportServiceController;
use App\Http\Controllers\EquipmentServiceController;
use App\Http\Controllers\IceCruiserServiceController;
use App\Http\Controllers\WaterServiceController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\FaqController;

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

    // FAQ route
    Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
    
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
        Route::get('/{id}/edit', [FishSpeciesController::class, 'edit'])->name('fish-species.edit');
        Route::put('/{id}', [FishSpeciesController::class, 'update'])->name('fish-species.update');
        Route::delete('/{id}', [FishSpeciesController::class, 'destroy'])->name('fish-species.destroy');
    });
    
    // Landing Sites routes
    Route::prefix('landing-sites')->group(function () {
        Route::get('/', [LandingSiteController::class, 'index'])->name('landing-sites.index');
        Route::get('/create', [LandingSiteController::class, 'create'])->name('landing-sites.create');
        Route::post('/', [LandingSiteController::class, 'store'])->name('landing-sites.store');
        Route::get('/{id}/edit', [LandingSiteController::class, 'edit'])->name('landing-sites.edit');
        Route::put('/{id}', [LandingSiteController::class, 'update'])->name('landing-sites.update');
        Route::delete('/{id}', [LandingSiteController::class, 'destroy'])->name('landing-sites.destroy');
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
        Route::put('/{vessel}/managers/{user}/approve', [VesselController::class, 'approveManager'])->name('vessels.approve-manager');
        Route::put('/{vessel}/managers/{user}/reject', [VesselController::class, 'rejectManager'])->name('vessels.reject-manager');
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
        Route::put('/{arrival}/forward', [ArrivalController::class, 'forward'])->name('arrivals.forward');
        Route::get('/{arrival}/print', [ArrivalController::class, 'print'])->name('arrivals.print');
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
        Route::get('/{departure}/print', [DepartureController::class, 'print'])->name('departures.print');
        Route::post('/{departure}/approve', [DepartureController::class, 'approve'])->name('departures.approve');
        Route::post('/{departure}/reject', [DepartureController::class, 'reject'])->name('departures.reject');
        Route::post('/{departure}/forward', [DepartureController::class, 'forward'])->name('departures.forward');
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
        
        // Services Report Routes
        Route::get('/services', [ReportServiceController::class, 'index'])->name('reports.services');
        Route::get('/services/export-excel', [ReportServiceController::class, 'exportExcel'])->name('reports.services.excel');
        Route::get('/services/export-pdf', [ReportServiceController::class, 'exportPdf'])->name('reports.services.pdf');
    });

    // Notifications routes
    Route::post('/notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Chat routes
    Route::prefix('chat')->group(function () {
        Route::get('/', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
        Route::get('/users', [App\Http\Controllers\ChatController::class, 'getUsers'])->name('chat.users');
        Route::get('/conversations', [App\Http\Controllers\ChatController::class, 'getConversations'])->name('chat.conversations');
        Route::post('/conversations/get', [App\Http\Controllers\ChatController::class, 'getOrCreateConversation'])->name('chat.conversation.get');
        Route::get('/conversations/{conversation}/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
        Route::delete('/conversations/{conversation}', [App\Http\Controllers\ChatController::class, 'deleteConversation'])->name('chat.conversation.delete');
        Route::post('/messages', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
        Route::delete('/messages/{message}', [App\Http\Controllers\ChatController::class, 'deleteMessage'])->name('chat.message.delete');
        Route::put('/messages/{message}', [App\Http\Controllers\ChatController::class, 'updateMessage'])->name('chat.message.update');
    });

    // Services routes (Menu Utama Jasa)
    Route::get('/services', [ServicesController::class, 'index'])->name('services.index');

    // Equipment Services routes (Peralatan)
    Route::prefix('equipment-services')->group(function () {
        Route::get('/', [EquipmentServiceController::class, 'index'])->name('equipment-services.index');
        Route::get('/create', [EquipmentServiceController::class, 'create'])->name('equipment-services.create');
        Route::post('/', [EquipmentServiceController::class, 'store'])->name('equipment-services.store');
        Route::get('/{id}', [EquipmentServiceController::class, 'show'])->name('equipment-services.show');
        Route::get('/{id}/edit', [EquipmentServiceController::class, 'edit'])->name('equipment-services.edit');
        Route::put('/{id}', [EquipmentServiceController::class, 'update'])->name('equipment-services.update');
        Route::get('/{id}/print', [EquipmentServiceController::class, 'printOrder'])->name('equipment-services.print');
        Route::get('/{id}/calculation', [EquipmentServiceController::class, 'calculation'])->name('equipment-services.calculation');
        Route::get('/{id}/print-calculation', [EquipmentServiceController::class, 'printCalculation'])->name('equipment-services.print-calculation');
        Route::post('/{id}/calculate', [EquipmentServiceController::class, 'calculate'])->name('equipment-services.calculate');
        Route::post('/{id}/complete', [EquipmentServiceController::class, 'complete'])->name('equipment-services.complete');
        Route::delete('/{id}', [EquipmentServiceController::class, 'destroy'])->name('equipment-services.destroy');
    });

    // Ice Cruiser Services routes
    Route::prefix('ice-cruiser-services')->group(function () {
        Route::get('/', [IceCruiserServiceController::class, 'index'])->name('ice-cruiser-services.index');
        Route::get('/create', [IceCruiserServiceController::class, 'create'])->name('ice-cruiser-services.create');
        Route::post('/', [IceCruiserServiceController::class, 'store'])->name('ice-cruiser-services.store');
        Route::get('/{id}', [IceCruiserServiceController::class, 'show'])->name('ice-cruiser-services.show');
        Route::get('/{id}/edit', [IceCruiserServiceController::class, 'edit'])->name('ice-cruiser-services.edit');
        Route::put('/{id}', [IceCruiserServiceController::class, 'update'])->name('ice-cruiser-services.update');
        Route::get('/{id}/print', [IceCruiserServiceController::class, 'printOrder'])->name('ice-cruiser-services.print');
        Route::get('/{id}/calculation', [IceCruiserServiceController::class, 'calculation'])->name('ice-cruiser-services.calculation');
        Route::get('/{id}/print-calculation', [IceCruiserServiceController::class, 'printCalculation'])->name('ice-cruiser-services.print-calculation');
        Route::post('/{id}/calculate', [IceCruiserServiceController::class, 'calculate'])->name('ice-cruiser-services.calculate');
        Route::post('/{id}/complete', [IceCruiserServiceController::class, 'complete'])->name('ice-cruiser-services.complete');
        Route::delete('/{id}', [IceCruiserServiceController::class, 'destroy'])->name('ice-cruiser-services.destroy');
    });

    // Water Services routes (Air)
    Route::prefix('water-services')->group(function () {
        Route::get('/', [WaterServiceController::class, 'index'])->name('water-services.index');
        Route::get('/create', [WaterServiceController::class, 'create'])->name('water-services.create');
        Route::post('/', [WaterServiceController::class, 'store'])->name('water-services.store');
        Route::get('/{id}', [WaterServiceController::class, 'show'])->name('water-services.show');
        Route::get('/{id}/edit', [WaterServiceController::class, 'edit'])->name('water-services.edit');
        Route::put('/{id}', [WaterServiceController::class, 'update'])->name('water-services.update');
        Route::get('/{id}/calculation', [WaterServiceController::class, 'calculation'])->name('water-services.calculation');
        Route::post('/{id}/calculate', [WaterServiceController::class, 'calculate'])->name('water-services.calculate');
        Route::get('/{id}/print-order', [WaterServiceController::class, 'printOrder'])->name('water-services.print-order');
        Route::get('/{id}/print-calculation', [WaterServiceController::class, 'printCalculation'])->name('water-services.print-calculation');
        Route::delete('/{id}', [WaterServiceController::class, 'destroy'])->name('water-services.destroy');
        Route::post('/{id}/complete', [WaterServiceController::class, 'complete'])->name('water-services.complete');
    });
});
