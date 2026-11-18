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
            // Pending volunteer account? (registered but not yet activated by admin)
            $pending = User::findByPhoneAny($phone);
            if ($pending && !($pending['is_active'] ?? 0)
                && ($pending['role'] ?? '') === 'volunteer'
                && password_verify($password, $pending['password_hash'])) {
                View::json(['ok' => false, 'message' => 'บัญชีของคุณยังรอผู้ดูแลระบบอนุมัติ กรุณารอการเปิดใช้งาน'], 403);
            }
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
        Auth::login($user, !empty($_POST['remember']));
        $redirect = match ($user['role']) {
            'admin' => 'admin',
            'volunteer' => 'volunteer',
            default => '',
        };
        View::json(['ok' => true, 'message' => 'เข้าสู่ระบบสำเร็จ', 'redirect' => Helper::url($redirect)]);
    }

    /**
     * Volunteer self-registration — account stays inactive until admin approves
     */
    public function registerForm(): void
    {
        if (Auth::check()) {
            View::redirect('/');
            return;
        }
        View::render('auth/register', [
            'title' => 'สมัครอาสาสมัคร',
            'csrf' => Auth::csrfToken(),
            'organizations' => Organization::activeOptions(),
            'skills' => Volunteer::skillOptions(),
            'equipment' => Volunteer::equipmentOptions(),
        ]);
    }

    public function register(): void
    {
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง กรุณารีเฟรชหน้า'], 403);
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        if (mb_strlen($name) < 2) {
            View::json(['ok' => false, 'message' => 'กรุณากรอกชื่อ-นามสกุล'], 422);
        }
        if (!preg_match('/^0\d{8,9}$/', $phone)) {
            View::json(['ok' => false, 'message' => 'เบอร์โทรไม่ถูกต้อง (เช่น 0812345678)'], 422);
        }
        if (User::phoneExists($phone)) {
            View::json(['ok' => false, 'message' => 'เบอร์โทรนี้มีบัญชีในระบบแล้ว กรุณาเข้าสู่ระบบ'], 422);
        }
        if (strlen($password) < 6) {
            View::json(['ok' => false, 'message' => 'รหัสผ่านต้องยาวอย่างน้อย 6 ตัวอักษร'], 422);
        }
        if ($password !== $confirm) {
            View::json(['ok' => false, 'message' => 'รหัสผ่านทั้งสองช่องไม่ตรงกัน'], 422);
        }

        // Optional profile fields — org, skills, equipment
        $organizationId = (int)($_POST['organization_id'] ?? 0);
        $skills = $_POST['skills'] ?? [];
        $equipment = $_POST['equipment'] ?? [];
        $skillOptions = Volunteer::skillOptions();
        $equipmentOptions = Volunteer::equipmentOptions();
        $validOrgs = array_column(Organization::activeOptions(), 'id');

        if ($organizationId > 0 && !in_array($organizationId, array_map('intval', $validOrgs), true)) {
            View::json(['ok' => false, 'message' => 'องค์กรไม่ถูกต้อง'], 422);
        }
        if (!is_array($skills) || array_filter($skills, fn($s) => !is_string($s) || !array_key_exists($s, $skillOptions))) {
            View::json(['ok' => false, 'message' => 'ทักษะไม่ถูกต้อง'], 422);
        }
        if (!is_array($equipment) || array_filter($equipment, fn($e) => !is_string($e) || !array_key_exists($e, $equipmentOptions))) {
            View::json(['ok' => false, 'message' => 'อุปกรณ์ไม่ถูกต้อง'], 422);
        }

        $id = User::create([
            'name' => $name,
            'phone' => $phone,
            'password' => $password,
            'role' => 'volunteer',
        ]);
        User::setActive($id, false);
        Volunteer::saveProfile($id, [
            'organization_id' => $organizationId ?: null,
            'area_id' => null,
            'availability' => 'available',
            'skills' => array_values($skills),
            'equipment' => array_values($equipment),
        ]);

        View::json(['ok' => true, 'message' => 'สมัครอาสาสมัครสำเร็จ! บัญชีของคุณจะเปิดใช้งานหลังผู้ดูแลระบบอนุมัติ']);
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
