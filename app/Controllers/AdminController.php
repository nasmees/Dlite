<?php
/**
 * Admin / back-office: command center, volunteers, and organizations
 */

class AdminController
{
    private const PER_PAGE = 20;

    public function map(): void
    {
        $this->index();
    }

    public function mapPoints(): void
    {
        Auth::requireLogin();

        $status = $_GET['status'] ?? '';
        $allowedStatuses = array_keys(Helper::statusLabels());
        $filters = [];
        if ($status !== '' && $status !== 'all') {
            if (!in_array($status, $allowedStatuses, true)) {
                View::json(['ok' => false, 'message' => 'สถานะไม่ถูกต้อง'], 422);
            }
            $filters['status'] = $status;
        }

        $cacheKey = 'admin_map_points:' . md5(json_encode($filters));
        $points = Cache::remember($cacheKey, 10, fn() => Need::mapPoints($filters, false));

        View::json(['ok' => true, 'points' => $points]);
    }

    public function index(): void
    {
        Auth::requireLogin();

        View::render('admin/dashboard', [
            'title' => 'ศูนย์บัญชาการ',
            'cases' => Need::list(['status' => 'pending'], 50, 0, false),
            'user' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
            'canAccept' => Auth::isVolunteer(),
            'includeMapAssets' => true,
            'isCommandCenter' => true,
            'statusLabels' => Helper::statusLabels(),
            'urgencyLabels' => Helper::urgencyLabels(),
            'needTypes' => Helper::needTypes(),
        ], 'layouts/admin');
    }

    public function volunteers(): void
    {
        Auth::requireAdmin();

        $query = $_GET['q'] ?? '';
        $filters = [
            'q' => is_string($query) ? trim($query) : '',
            'skill' => $_GET['skill'] ?? '',
        ];
        if (!is_string($filters['skill']) || !array_key_exists($filters['skill'], Volunteer::skillOptions())) {
            $filters['skill'] = '';
        }

        View::render('admin/volunteers', [
            'title' => 'อาสาสมัคร',
            'volunteers' => Volunteer::all($filters),
            'filters' => $filters,
            'skills' => Volunteer::skillOptions(),
            'equipment' => Volunteer::equipmentOptions(),
            'organizations' => Organization::activeOptions(),
            'areas' => Area::all(),
            'csrf' => Auth::csrfToken(),
            'user' => Auth::user(),
            'isAdmin' => true,
        ], 'layouts/admin');
    }

    public function saveVolunteer(): void
    {
        Auth::requireAdmin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $availability = $_POST['availability'] ?? 'available';
        $organizationId = (int)($_POST['organization_id'] ?? 0);
        $areaId = (int)($_POST['area_id'] ?? 0);
        $skillOptions = Volunteer::skillOptions();
        $equipmentOptions = Volunteer::equipmentOptions();
        $skills = $_POST['skills'] ?? [];
        $equipment = $_POST['equipment'] ?? [];

        if ($name === '' || $phone === '') {
            View::json(['ok' => false, 'message' => 'กรุณากรอกชื่อและเบอร์โทรศัพท์'], 422);
        }
        if (!in_array($availability, ['available', 'busy'], true)) {
            View::json(['ok' => false, 'message' => 'สถานะอาสาสมัครไม่ถูกต้อง'], 422);
        }
        if (!is_array($skills) || array_filter($skills, fn($skill) => !is_string($skill) || !array_key_exists($skill, $skillOptions))) {
            View::json(['ok' => false, 'message' => 'ทักษะที่เลือกไม่ถูกต้อง'], 422);
        }
        if (!is_array($equipment) || array_filter($equipment, fn($item) => !is_string($item) || !array_key_exists($item, $equipmentOptions))) {
            View::json(['ok' => false, 'message' => 'อุปกรณ์ที่เลือกไม่ถูกต้อง'], 422);
        }
        if ($organizationId > 0 && !Organization::find($organizationId)) {
            View::json(['ok' => false, 'message' => 'ไม่พบองค์กรที่เลือก'], 422);
        }
        if ($areaId > 0 && !Area::find($areaId)) {
            View::json(['ok' => false, 'message' => 'ไม่พบพื้นที่ที่เลือก'], 422);
        }

        $user = $id > 0 ? User::find($id) : null;
        if ($id > 0 && (!$user || $user['role'] !== 'volunteer')) {
            View::json(['ok' => false, 'message' => 'ไม่พบข้อมูลอาสาสมัคร'], 404);
        }
        if ($id === 0 && strlen($password) < 6) {
            View::json(['ok' => false, 'message' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร'], 422);
        }
        if ($id > 0 && $password !== '' && strlen($password) < 6) {
            View::json(['ok' => false, 'message' => 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร'], 422);
        }
        if (User::phoneExists($phone, $id > 0 ? $id : null)) {
            View::json(['ok' => false, 'message' => 'เบอร์โทรศัพท์นี้มีบัญชีแล้ว'], 422);
        }

        $profile = [
            'organization_id' => $organizationId ?: null,
            'area_id' => $areaId ?: null,
            'availability' => $availability,
            'skills' => array_values(array_unique($skills)),
            'equipment' => array_values(array_unique($equipment)),
        ];

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            if ($id === 0) {
                $id = User::create([
                    'name' => $name,
                    'phone' => $phone,
                    'password' => $password,
                    'role' => 'volunteer',
                ]);
            } else {
                $userData = ['name' => $name, 'phone' => $phone];
                if ($password !== '') $userData['password'] = $password;
                if (!User::update($id, $userData)) {
                    throw new RuntimeException('Unable to update volunteer account');
                }
            }
            Volunteer::saveProfile($id, $profile);
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }

        View::json(['ok' => true, 'message' => 'บันทึกข้อมูลอาสาสมัครแล้ว']);
    }

    public function updateVolunteerAvailability(string $id): void
    {
        Auth::requireAdmin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $volunteer = User::find((int)$id);
        if (!$volunteer || $volunteer['role'] !== 'volunteer') {
            View::json(['ok' => false, 'message' => 'ไม่พบข้อมูลอาสาสมัคร'], 404);
        }
        $availability = $_POST['availability'] ?? '';
        if (!in_array($availability, ['available', 'busy'], true)) {
            View::json(['ok' => false, 'message' => 'สถานะอาสาสมัครไม่ถูกต้อง'], 422);
        }

        if (!Volunteer::setAvailability((int)$id, $availability)) {
            View::json(['ok' => false, 'message' => 'อัปเดตสถานะไม่สำเร็จ'], 500);
        }
        View::json(['ok' => true, 'message' => 'อัปเดตสถานะแล้ว']);
    }

    public function organizations(): void
    {
        Auth::requireAdmin();

        View::render('admin/organizations', [
            'title' => 'องค์กรเครือข่าย',
            'organizations' => Organization::all(),
            'csrf' => Auth::csrfToken(),
            'user' => Auth::user(),
            'isAdmin' => true,
        ], 'layouts/admin');
    }

    public function saveOrganization(): void
    {
        Auth::requireAdmin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $logoUrl = trim($_POST['logo_url'] ?? '');
        $email = trim($_POST['contact_email'] ?? '');
        if ($name === '') {
            View::json(['ok' => false, 'message' => 'กรุณาระบุชื่อองค์กร'], 422);
        }
        if ($id > 0 && !Organization::find($id)) {
            View::json(['ok' => false, 'message' => 'ไม่พบองค์กร'], 404);
        }
        if ($logoUrl !== '') {
            $parts = parse_url($logoUrl);
            if (!filter_var($logoUrl, FILTER_VALIDATE_URL) || ($parts['scheme'] ?? '') !== 'https') {
                View::json(['ok' => false, 'message' => 'ลิงก์โลโก้ต้องเป็น HTTPS ที่ถูกต้อง'], 422);
            }
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            View::json(['ok' => false, 'message' => 'อีเมลประสานงานไม่ถูกต้อง'], 422);
        }

        $organizationId = Organization::save([
            'name' => $name,
            'logo_url' => $logoUrl ?: null,
            'verified' => isset($_POST['verified']) ? 1 : 0,
            'contact_name' => trim($_POST['contact_name'] ?? '') ?: null,
            'contact_phone' => trim($_POST['contact_phone'] ?? '') ?: null,
            'contact_email' => $email ?: null,
            'description' => trim($_POST['description'] ?? '') ?: null,
        ], $id > 0 ? $id : null);

        View::json(['ok' => true, 'message' => 'บันทึกข้อมูลองค์กรแล้ว', 'id' => $organizationId]);
    }

    public function toggleOrganization(string $id): void
    {
        Auth::requireAdmin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $organization = Organization::find((int)$id);
        if (!$organization) {
            View::json(['ok' => false, 'message' => 'ไม่พบองค์กร'], 404);
        }
        if (!Organization::setActive((int)$id, !(bool)$organization['is_active'])) {
            View::json(['ok' => false, 'message' => 'อัปเดตสถานะองค์กรไม่สำเร็จ'], 500);
        }
        View::json(['ok' => true, 'message' => 'อัปเดตสถานะองค์กรแล้ว']);
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
