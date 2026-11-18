<?php
/**
 * Auth Controller
 */

class AuthController
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 300; // 5 minutes

    public function loginForm(): void
    {
        if (Auth::check()) {
            $u = Auth::user();
            View::redirect($u['role'] === 'admin' ? '/admin' : ($u['role'] === 'volunteer' ? '/volunteer' : '/'));
        }
        View::render('auth/login', [
            'title' => 'เข้าสู่ระบบ',
            'csrf' => Auth::csrfToken(),
        ]);
    }

    public function login(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $throttleKey = 'login_throttle:' . md5($ip);

        // Check lockout
        $state = Cache::get($throttleKey);
        if (is_array($state) && ($state['until'] ?? 0) > time()) {
            $wait = (int)ceil(($state['until'] - time()) / 60);
            View::json(['ok' => false, 'message' => "พยายามหลายครั้งเกินไป กรุณารอ {$wait} นาที"], 429);
        }

        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง กรุณารีเฟรชหน้า'], 403);
        }

        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = User::authenticate($phone, $password);
        if (!$user) {
            // Record failed attempt (reset count if previous lockout expired)
            $expired = is_array($state) && ($state['until'] ?? 0) > 0 && $state['until'] <= time();
            $count = (!$expired && is_array($state)) ? (int)($state['count'] ?? 0) : 0;
            $count++;
            $newState = ['count' => $count, 'until' => $count >= self::MAX_ATTEMPTS ? time() + self::LOCK_SECONDS : 0];
            Cache::set($throttleKey, $newState, self::LOCK_SECONDS);
            $remaining = self::MAX_ATTEMPTS - $count;
            $msg = $remaining > 0
                ? "เบอร์โทรหรือรหัสผ่านไม่ถูกต้อง (เหลือ {$remaining} ครั้ง)"
                : 'พยายามหลายครั้งเกินไป กรุณารอ 5 นาที';
            View::json(['ok' => false, 'message' => $msg], $count >= self::MAX_ATTEMPTS ? 429 : 401);
        }

        // Success — clear throttle
        Cache::delete($throttleKey);
        Auth::login($user);
        $redirect = match ($user['role']) {
            'admin' => 'admin',
            'volunteer' => 'volunteer',
            default => '',
        };
        View::json(['ok' => true, 'message' => 'เข้าสู่ระบบสำเร็จ', 'redirect' => Helper::url($redirect)]);
    }

    public function logout(): void
    {
        Auth::logout();
        View::redirect('/');
    }

    public function passwordForm(): void
    {
        if (!Auth::check()) {
            View::redirect('/login');
            return;
        }
        $u = Auth::user();
        $layout = match ($u['role'] ?? '') {
            'admin' => 'layouts/admin',
            'volunteer' => 'layouts/volunteer',
            default => 'layouts/main',
        };
        View::render('auth/password', [
            'title' => 'เปลี่ยนรหัสผ่าน',
            'csrf' => Auth::csrfToken(),
            'user' => $u,
        ], $layout);
    }

    public function changePassword(): void
    {
        if (!Auth::check()) {
            View::json(['ok' => false, 'message' => 'กรุณาเข้าสู่ระบบก่อน'], 401);
        }
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง กรุณารีเฟรชหน้า'], 403);
        }

        $current = (string)($_POST['current_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        $user = User::find((int)Auth::user()['id']);
        if (!$user || !password_verify($current, $user['password_hash'])) {
            View::json(['ok' => false, 'message' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง'], 422);
        }
        if (strlen($new) < 6) {
            View::json(['ok' => false, 'message' => 'รหัสผ่านใหม่ต้องยาวอย่างน้อย 6 ตัวอักษร'], 422);
        }
        if ($new !== $confirm) {
            View::json(['ok' => false, 'message' => 'รหัสผ่านใหม่ทั้งสองช่องไม่ตรงกัน'], 422);
        }

        User::update((int)$user['id'], ['password' => $new]);
        View::json(['ok' => true, 'message' => 'เปลี่ยนรหัสผ่านสำเร็จ']);
    }
}
