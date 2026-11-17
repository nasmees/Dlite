<?php
/**
 * Deep South Care Lite - Front Controller
 * Shared hosting ready (Apache)
 */

declare(strict_types=1);

require_once __DIR__ . '/app/config.php';

// Autoload simple
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/Core/' . $class . '.php',
        APP_PATH . '/Models/' . $class . '.php',
        APP_PATH . '/Controllers/' . $class . '.php',
        APP_PATH . '/Services/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

Auth::start();
Database::getInstance(); // Ensure DB + migration

$router = new Router();

// Public
$router->get('/', [HomeController::class, 'index']);
$router->get('/request', [HomeController::class, 'requestForm']);
$router->post('/request', [HomeController::class, 'store']);
$router->get('/success', [HomeController::class, 'success']);

$router->get('/map', [MapController::class, 'index']);

$router->get('/needs', [NeedController::class, 'index']);
$router->get('/needs/{id}', [NeedController::class, 'show']);
$router->post('/needs/{id}/status', [NeedController::class, 'updateStatus']);
$router->post('/needs/{id}/help', [NeedController::class, 'help']);

// Track status by code (public)
$router->get('/track', [TrackController::class, 'index']);

// API
$router->get('/api/points', [MapController::class, 'points']);
$router->get('/api/needs-summary', [NeedController::class, 'summary']);
$router->get('/api/report-status', [NeedController::class, 'reportStatus']);

// Auth
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// Admin
$router->get('/admin', [AdminController::class, 'index']);
$router->get('/admin/requests', [AdminController::class, 'requests']);
$router->get('/admin/requests/{id}', [AdminController::class, 'requestDetail']);
$router->post('/admin/requests/{id}/action', [AdminController::class, 'requestAction']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->post('/admin/users', [AdminController::class, 'createUser']);
$router->post('/admin/users/{id}', [AdminController::class, 'updateUser']);
$router->post('/admin/users/{id}/toggle', [AdminController::class, 'toggleUser']);
$router->get('/admin/areas', [AdminController::class, 'areas']);
$router->post('/admin/areas', [AdminController::class, 'saveArea']);
$router->post('/admin/areas/{id}/delete', [AdminController::class, 'deleteArea']);

$router->dispatch();
