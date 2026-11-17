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
            View::redirect('/admin');
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
        View::json(['ok' => true, 'message' => 'เข้าสู่ระบบสำเร็จ', 'redirect' => Helper::url('admin')]);
    }

    public function logout(): void
    {
        Auth::logout();
        View::redirect('/');
    }
}
