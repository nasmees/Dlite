<?php
/**
 * Dlite - Front Controller
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
$router->post('/needs/{id}/close', [NeedController::class, 'close']);

// Track status by code (public)
$router->get('/track', [TrackController::class, 'index']);

// API
$router->get('/api/points', [MapController::class, 'points']);
$router->get('/api/needs-summary', [NeedController::class, 'summary']);
$router->get('/api/report-status', [NeedController::class, 'reportStatus']);
$router->get('/api/need-detail', [NeedController::class, 'detailJson']);

// Volunteer workspace (mobile-first)
$router->get('/volunteer', [VolunteerController::class, 'index']);
$router->get('/volunteer/new', [VolunteerController::class, 'newForm']);
$router->post('/volunteer/new', [VolunteerController::class, 'store']);
$router->get('/volunteer/cases/{id}', [VolunteerController::class, 'show']);
$router->post('/volunteer/cases/{id}/claim', [VolunteerController::class, 'claim']);
$router->post('/volunteer/cases/{id}/status', [VolunteerController::class, 'setStatus']);
$router->post('/volunteer/cases/{id}/note', [VolunteerController::class, 'note']);
$router->post('/volunteer/cases/{id}/release', [VolunteerController::class, 'release']);
$router->post('/volunteer/cases/{id}/transfer', [VolunteerController::class, 'transfer']);
$router->post('/volunteer/availability', [VolunteerController::class, 'availability']);

// Auth
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/password', [AuthController::class, 'passwordForm']);
$router->post('/password', [AuthController::class, 'changePassword']);

// Announcements (broadcast popup)
$router->get('/announcements/latest', [AdminController::class, 'latestAnnouncement']);
$router->get('/admin/announcements', [AdminController::class, 'announcements']);
$router->post('/admin/announcements', [AdminController::class, 'saveAnnouncement']);
$router->post('/admin/announcements/{id}/delete', [AdminController::class, 'deleteAnnouncement']);

// Admin
$router->get('/admin', [AdminController::class, 'index']);
$router->get('/admin/map', [AdminController::class, 'map']);
$router->get('/admin/map/points', [AdminController::class, 'mapPoints']);
$router->get('/admin/volunteers', [AdminController::class, 'volunteers']);
$router->post('/admin/volunteers', [AdminController::class, 'saveVolunteer']);
$router->post('/admin/volunteers/{id}/availability', [AdminController::class, 'updateVolunteerAvailability']);
$router->post('/admin/volunteers/{id}/toggle', [AdminController::class, 'toggleVolunteerAccount']);
$router->post('/admin/volunteers/{id}/delete', [AdminController::class, 'deleteVolunteer']);
$router->get('/admin/organizations', [AdminController::class, 'organizations']);
$router->post('/admin/organizations', [AdminController::class, 'saveOrganization']);
$router->post('/admin/organizations/{id}/toggle', [AdminController::class, 'toggleOrganization']);
$router->post('/admin/organizations/{id}/delete', [AdminController::class, 'deleteOrganization']);
$router->get('/admin/requests', [AdminController::class, 'requests']);
$router->get('/admin/requests/create', [AdminController::class, 'createRequestForm']);
$router->post('/admin/requests/create', [AdminController::class, 'createRequest']);
$router->get('/admin/requests/pending-count', [AdminController::class, 'pendingCount']);
$router->get('/admin/requests/latest-case', [AdminController::class, 'latestCase']);
$router->get('/admin/requests/export', [AdminController::class, 'exportRequests']);
$router->post('/admin/requests/bulk', [AdminController::class, 'bulkRequestAction']);
$router->get('/admin/requests/{id}', [AdminController::class, 'requestDetail']);
$router->post('/admin/requests/{id}/action', [AdminController::class, 'requestAction']);
$router->post('/admin/requests/{id}/update', [AdminController::class, 'updateRequest']);
$router->post('/admin/requests/{id}/delete', [AdminController::class, 'deleteRequest']);
$router->get('/admin/areas', [AdminController::class, 'areas']);
$router->post('/admin/areas', [AdminController::class, 'saveArea']);
$router->post('/admin/areas/{id}/delete', [AdminController::class, 'deleteArea']);
$router->get('/admin/pois', [AdminController::class, 'pois']);
$router->post('/admin/pois', [AdminController::class, 'savePoi']);
$router->post('/admin/pois/{id}/delete', [AdminController::class, 'deletePoi']);
$router->get('/admin/stats', [AdminController::class, 'stats']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->post('/admin/users', [AdminController::class, 'saveUser']);
$router->post('/admin/users/{id}/toggle', [AdminController::class, 'toggleUser']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings', [AdminController::class, 'saveSettings']);
$router->get('/api/pois', [MapController::class, 'pois']);

$router->dispatch();
