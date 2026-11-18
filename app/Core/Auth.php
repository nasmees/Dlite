<?php
/**
 * Simple session-based Auth
 */

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
            ]);
        }
    }

    public static function login(array $user, bool $remember = false): void
    {
        self::start();
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'phone' => $user['phone'],
            'role' => $user['role'],
        ];
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            User::setRememberToken((int)$user['id'], hash('sha256', $token));
            $expires = time() + REMEMBER_DAYS * 86400;
            setcookie(REMEMBER_COOKIE, $user['id'] . ':' . $token, [
                'expires' => $expires,
                'path' => '/',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    public static function logout(): void
    {
        self::start();
        $u = $_SESSION['user'] ?? null;
        if ($u) {
            User::setRememberToken((int)$u['id'], null);
        }
        if (!empty($_COOKIE[REMEMBER_COOKIE])) {
            setcookie(REMEMBER_COOKIE, '', time() - 3600, '/');
            unset($_COOKIE[REMEMBER_COOKIE]);
        }
        unset($_SESSION['user']);
        session_destroy();
    }

    public static function user(): ?array
    {
        self::start();
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        }
        // Remember-me: restore session from long-lived cookie
        $cookie = $_COOKIE[REMEMBER_COOKIE] ?? '';
        if ($cookie !== '') {
            $user = User::findByRememberCookie($cookie);
            if ($user) {
                self::login($user);
                return $_SESSION['user'];
            }
            setcookie(REMEMBER_COOKIE, '', time() - 3600, '/');
            unset($_COOKIE[REMEMBER_COOKIE]);
        }
        return null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        $u = self::user();
        return $u && $u['role'] === 'admin';
    }

    public static function isVolunteer(): bool
    {
        $u = self::user();
        return $u && in_array($u['role'], ['admin', 'volunteer'], true);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            View::redirect('/login');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'ไม่มีสิทธิ์เข้าถึง']);
            exit;
        }
    }

    public static function csrfToken(): string
    {
        self::start();
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    public static function verifyCsrf(?string $token): bool
    {
        self::start();
        return $token && hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token);
    }
}
