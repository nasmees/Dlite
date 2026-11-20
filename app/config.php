<?php
/**
 * Dlite - Configuration
 * Pure PHP + SQLite for shared hosting
 */

define('APP_NAME', 'Dlite');
define('APP_VERSION', '1.0.0');
define('APP_URL', ''); // Auto-detect or set base URL

// Paths (index.php อยู่ที่ root ของโปรเจกต์ — อัป FTP แล้วรันได้เลย)
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH); // assets + uploads อยู่ที่ root
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('DB_PATH', STORAGE_PATH . '/database.sqlite');

// Security
define('SESSION_NAME', 'dsc_lite_session');
define('CSRF_TOKEN_NAME', 'csrf_token');
define('REMEMBER_COOKIE', 'dsc_lite_remember');
define('REMEMBER_DAYS', 30);

// Upload — accept large camera photos; ImageUploader resizes/compresses to ~1280px JPEG after upload
// (phone cameras produce 3-8MB files; keep headroom for PHP post_max_size too)
define('UPLOAD_MAX_SIZE', 8 * 1024 * 1024); // 8MB
define('UPLOAD_ALLOWED', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// SQLite settings
define('DB_BUSY_TIMEOUT', 5000); // ms

// Cache TTL for public lists (seconds)
define('CACHE_TTL', 15);

// Timezone
date_default_timezone_set('Asia/Bangkok');

// Error reporting (turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', STORAGE_PATH . '/logs/error.log');

// Auto base path detection for shared hosting
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
define('BASE_PATH', $basePath);
