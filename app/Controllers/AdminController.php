<?php
/**
 * Admin / back-office: dashboard, request management, users, areas
 */

class AdminController
{
    private const PER_PAGE = 20;

    public function index(): void
    {
        Auth::requireLogin();

        $counts = Need::statusCounts(false);
        $counts['accepted'] = Need::count(['status' => 'accepted']);

        $recent = Need::list(['status' => 'pending'], 8);

        View::render('admin/dashboard', [
            'title' => 'แดชบอร์ด',
            'counts' => $counts,
            'recent' => $recent,
            'statusLabels' => Helper::statusLabels(),
            'urgencyLabels' => Helper::urgencyLabels(),
            'needTypes' => Helper::needTypes(),
            'user' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
        ], 'layouts/admin');
    }

    /**
     * Request management — all needs with filters
     */
    public function requests(): void
    {
        Auth::requireLogin();

        $status = $_GET['status'] ?? '';
        $urgency = $_GET['urgency'] ?? '';
        $q = trim($_GET['q'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));

        $filters = [];
        if ($status) $filters['status'] = $status;
        if ($urgency) $filters['urgency'] = $urgency;
        if ($q) $filters['q'] = $q;

        $offset = ($page - 1) * self::PER_PAGE;
        $needs = Need::list($filters, self::PER_PAGE, $offset, false);
        $total = Need::count($filters, false);
        $pagination = Helper::pagination($page, self::PER_PAGE, $total);
        $counts = Need::statusCounts(false);
        $counts['accepted'] = Need::count(['status' => 'accepted']);

        View::render('admin/requests', [
            'title' => 'จัดการคำขอ',
            'needs' => $needs,
            'counts' => $counts,
            'pagination' => $pagination,
            'filters' => ['status' => $status, 'urgency' => $urgency, 'q' => $q],
            'statusLabels' => Helper::statusLabels(),
            'urgencyLabels' => Helper::urgencyLabels(),
            'needTypes' => Helper::needTypes(),
            'user' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
        ], 'layouts/admin');
    }

    /**
     * Single request detail — full info + workflow actions
     */
    public function requestDetail(string $id): void
    {
        Auth::requireLogin();

        $need = Need::find((int)$id);
        if (!$need) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'ไม่พบคำขอ'], 'layouts/admin');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT u.*, us.name as user_name
            FROM updates u
            LEFT JOIN users us ON us.id = u.user_id
            WHERE u.need_id = ?
            ORDER BY u.created_at DESC
        ");
        $stmt->execute([(int)$id]);
        $history = $stmt->fetchAll();

        View::render('admin/request_detail', [
            'title' => 'คำขอ ' . $need['code'],
            'need' => $need,
            'history' => $history,
            'helps' => Help::forNeed((int)$id),
            'helpTypes' => Help::helpTypes(),
            'users' => User::all(),
            'statusLabels' => Helper::statusLabels(),
            'urgencyLabels' => Helper::urgencyLabels(),
            'needTypes' => Helper::needTypes(),
            'csrf' => Auth::csrfToken(),
            'user' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
        ], 'layouts/admin');
    }

    /**
     * Actions on a request: status change / assign / add note
     */
    public function requestAction(string $id): void
    {
        Auth::requireLogin();
        if (!Auth::isVolunteer()) {
            View::json(['ok' => false, 'message' => 'ไม่มีสิทธิ์'], 403);
        }
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $need = Need::find((int)$id);
        if (!$need) {
            View::json(['ok' => false, 'message' => 'ไม่พบคำขอ'], 404);
        }

        $action = $_POST['action'] ?? '';
        $db = Database::getInstance();
        $uid = Auth::user()['id'];

        if ($action === 'status') {
            $status = $_POST['status'] ?? '';
            $message = trim($_POST['message'] ?? '');
            $allowed = ['pending','accepted','in_progress','completed','cancelled'];
            if (!in_array($status, $allowed, true)) {
                View::json(['ok' => false, 'message' => 'สถานะไม่ถูกต้อง'], 422);
            }
            $ok = Need::updateStatus((int)$id, $status, $uid, $message ?: null);
            // Auto-assign to self when accepting / starting work
            if ($ok && in_array($status, ['accepted','in_progress'], true) && !$need['assigned_to']) {
                Need::assign((int)$id, $uid);
            }
            if ($ok) Need::flushCache();
            View::json(['ok' => $ok, 'message' => $ok ? 'อัปเดตสถานะแล้ว' : 'ไม่สำเร็จ']);
        }

        if ($action === 'assign') {
            $target = (int)($_POST['user_id'] ?? 0);
            if ($target <= 0) {
                View::json(['ok' => false, 'message' => 'กรุณาเลือกผู้รับผิดชอบ'], 422);
            }
            $ok = Need::assign((int)$id, $target);
            if ($ok) {
                $assignee = User::find($target);
                $db->prepare("INSERT INTO updates (need_id, user_id, new_status, message) VALUES (?,?,?,?)")
                   ->execute([(int)$id, $uid, $need['status'], 'มอบหมายให้ ' . ($assignee['name'] ?? 'ผู้ใช้ #' . $target)]);
                Need::flushCache();
            }
            View::json(['ok' => $ok, 'message' => $ok ? 'มอบหมายแล้ว' : 'ไม่สำเร็จ']);
        }

        if ($action === 'note') {
            $message = trim($_POST['message'] ?? '');
            if ($message === '') {
                View::json(['ok' => false, 'message' => 'กรุณาระบุข้อความ'], 422);
            }
            $db->prepare("INSERT INTO updates (need_id, user_id, old_status, new_status, message) VALUES (?,?,?,?,?)")
               ->execute([(int)$id, $uid, $need['status'], $need['status'], $message]);
            Need::flushCache();
            View::json(['ok' => true, 'message' => 'บันทึกบันทึกแล้ว']);
        }

        View::json(['ok' => false, 'message' => 'คำสั่งไม่ถูกต้อง'], 422);
    }

    /**
     * User management — admin only
     */
    public function users(): void
    {
        Auth::requireAdmin();

        View::render('admin/users', [
            'title' => 'จัดการผู้ใช้',
            'users' => User::all(),
            'roles' => ['admin' => 'ผู้ดูแล', 'volunteer' => 'อาสาสมัคร', 'viewer' => 'ผู้ดู'],
            'csrf' => Auth::csrfToken(),
            'currentUser' => Auth::user(),
            'user' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
        ], 'layouts/admin');
    }

    /**
     * Area management — admin only
     */
    public function areas(): void
    {
        Auth::requireAdmin();

        View::render('admin/areas', [
            'title' => 'จัดการพื้นที่',
            'areas' => Area::all(),
            'csrf' => Auth::csrfToken(),
            'user' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
        ], 'layouts/admin');
    }

    public function saveArea(): void
    {
        Auth::requireAdmin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            View::json(['ok' => false, 'message' => 'กรุณาระบุชื่อพื้นที่'], 422);
        }

        $data = [
            'name' => $name,
            'province' => $_POST['province'] ?? '',
            'district' => $_POST['district'] ?? '',
            'subdistrict' => $_POST['subdistrict'] ?? '',
            'lat' => $_POST['lat'] ?? '',
            'lng' => $_POST['lng'] ?? '',
        ];

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $ok = Area::update($id, $data);
        } else {
            Area::create($data);
            $ok = true;
        }
        View::json(['ok' => $ok, 'message' => $ok ? 'บันทึกแล้ว' : 'ไม่สำเร็จ']);
    }

    public function deleteArea(string $id): void
    {
        Auth::requireAdmin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }
        $ok = Area::delete((int)$id);
        View::json(['ok' => $ok, 'message' => $ok ? 'ลบแล้ว' : 'ไม่สำเร็จ']);
    }

    public function createUser(): void
    {
        Auth::requireAdmin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'volunteer';

        if ($name === '' || $phone === '' || strlen($password) < 6) {
            View::json(['ok' => false, 'message' => 'กรุณากรอกชื่อ เบอร์โทร และรหัสผ่าน (อย่างน้อย 6 ตัว)'], 422);
        }
        if (!in_array($role, ['admin', 'volunteer', 'viewer'], true)) {
            View::json(['ok' => false, 'message' => 'บทบาทไม่ถูกต้อง'], 422);
        }
        if (User::findByPhone($phone)) {
            View::json(['ok' => false, 'message' => 'เบอร์โทรนี้มีบัญชีแล้ว'], 422);
        }

        User::create(['name' => $name, 'phone' => $phone, 'password' => $password, 'role' => $role]);
        View::json(['ok' => true, 'message' => 'สร้างบัญชีแล้ว']);
    }

    public function updateUser(string $id): void
    {
        Auth::requireAdmin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $uid = (int)$id;
        $data = [];
        if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
        if (isset($_POST['role'])) $data['role'] = $_POST['role'];
        if (!empty($_POST['password'])) $data['password'] = $_POST['password'];

        if (!$data) {
            View::json(['ok' => false, 'message' => 'ไม่มีข้อมูลที่จะแก้ไข'], 422);
        }
        if ($uid === (int)Auth::user()['id'] && (isset($data['role']) && $data['role'] !== 'admin')) {
            View::json(['ok' => false, 'message' => 'ไม่สามารถเปลี่ยนบทบาทของตัวเอง'], 422);
        }

        $ok = User::update($uid, $data);
        View::json(['ok' => $ok, 'message' => $ok ? 'อัปเดตแล้ว' : 'ไม่สำเร็จ']);
    }

    public function toggleUser(string $id): void
    {
        Auth::requireAdmin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $uid = (int)$id;
        if ($uid === (int)Auth::user()['id']) {
            View::json(['ok' => false, 'message' => 'ไม่สามารถปิดบัญชีตัวเอง'], 422);
        }
        $user = User::find($uid);
        if (!$user) {
            View::json(['ok' => false, 'message' => 'ไม่พบบัญชี'], 404);
        }

        User::setActive($uid, !(bool)$user['is_active']);
        View::json(['ok' => true, 'message' => $user['is_active'] ? 'ปิดบัญชีแล้ว' : 'เปิดบัญชีแล้ว']);
    }
}
