<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas (Sin autenticación)
|--------------------------------------------------------------------------
*/

// Redirigir raíz a login si no está autenticado, o al dashboard si está autenticado
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Requieren autenticación + dispositivo único)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'single.device'])->group(function () {
    
    // === DASHBOARD ===
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/bot-status', [DashboardController::class, 'checkBotStatusAjax'])->name('dashboard.bot-status');
    Route::get('/dashboard/bot-qr', [DashboardController::class, 'getBotQR'])->name('dashboard.bot-qr');
    
    // === RUTAS DE TAREAS ===
    Route::get('/tareas', [TareaController::class, 'index'])->name('tareas.index');
    Route::get('/tareas/crear', [TareaController::class, 'create'])->name('tareas.create');
    Route::post('/tareas', [TareaController::class, 'store'])->name('tareas.store');
    Route::get('/tareas/{tarea}/editar', [TareaController::class, 'edit'])->name('tareas.edit');
    Route::put('/tareas/{tarea}', [TareaController::class, 'update'])->name('tareas.update');
    Route::delete('/tareas/{tarea}', [TareaController::class, 'destroy'])->name('tareas.destroy');
    Route::get('/tareas/{tarea}', [TareaController::class, 'show'])->name('tareas.show');
    Route::patch('/tareas/{tarea}/toggle', [TareaController::class, 'toggle'])->name('tareas.toggle');

    // === RUTAS DE CONTACTOS ===
    Route::get('/contactos', [ContactoController::class, 'index'])->name('contactos.index');
    Route::get('/contactos/crear', [ContactoController::class, 'create'])->name('contactos.create');
    Route::post('/contactos', [ContactoController::class, 'store'])->name('contactos.store');
    Route::get('/contactos/{contacto}/editar', [ContactoController::class, 'edit'])->name('contactos.edit');
    Route::put('/contactos/{contacto}', [ContactoController::class, 'update'])->name('contactos.update');
    Route::delete('/contactos/{contacto}', [ContactoController::class, 'destroy'])->name('contactos.destroy');
    Route::patch('/contactos/{contacto}/toggle', [ContactoController::class, 'toggle'])->name('contactos.toggle');

    // === RUTAS DE ADMINISTRACIÓN (Solo para admins) ===
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::patch('/users/{user}/reset-device', [AdminController::class, 'resetDevice'])->name('users.reset-device');
        Route::patch('/users/{user}/toggle-active', [AdminController::class, 'toggleActive'])->name('users.toggle-active');
    });

    // === RUTA PARA CERRAR SESIÓN ===
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login')->with('status', '👋 Sesión cerrada exitosamente.');
    })->name('logout');

});

/*
|--------------------------------------------------------------------------
| Información de la aplicación
|--------------------------------------------------------------------------
| 
| IMPORTANTE: 
| - El registro público está DESHABILITADO
| - Solo los administradores pueden crear usuarios
| - Las rutas de login y password reset están manejadas por Fortify
| - El dashboard es la página principal después del login
| - Un usuario solo puede estar activo en un dispositivo a la vez
|
| Middleware aplicado:
| - 'auth': Usuario debe estar autenticado
| - 'single.device': Usuario solo puede estar activo en un dispositivo
|
|--------------------------------------------------------------------------
*/