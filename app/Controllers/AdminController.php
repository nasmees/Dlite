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

        $counts = Need::statusCounts(false);
        $stats = [
            'total' => $counts['all'],
            'pending' => $counts['pending'],
            'active' => Need::count(['status' => 'accepted']) + $counts['in_progress'],
            'today' => Need::countToday(),
            'critical' => Need::count(['status' => 'pending', 'urgency' => 'critical']),
        ];

        View::render('admin/dashboard', [
            'title' => 'ศูนย์บัญชาการ',
            'cases' => Need::list(['status' => 'pending'], 50, 0, false),
            'stats' => $stats,
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

    /**
     * Suspend / reactivate a volunteer account — admin only
     */
    public function toggleVolunteerAccount(string $id): void
    {
        Auth::requireAdmin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $volunteer = User::find((int)$id);
        if (!$volunteer || $volunteer['role'] !== 'volunteer') {
            View::json(['ok' => false, 'message' => 'ไม่พบข้อมูลอาสาสมัคร'], 404);
        }
        if ((int)$volunteer['id'] === (int)Auth::user()['id']) {
            View::json(['ok' => false, 'message' => 'ไม่สามารถระงับบัญชีตัวเอง'], 422);
        }

        User::setActive((int)$id, !(bool)$volunteer['is_active']);
        View::json(['ok' => true, 'message' => $volunteer['is_active'] ? 'ระงับบัญชีแล้ว' : 'เปิดบัญชีแล้ว']);
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
            'users' => User::all(),
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
            'scenarioLabels' => Helper::scenarioLabels(),
            'areas' => Area::all(),
            'csrf' => Auth::csrfToken(),
            'user' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
        ], 'layouts/admin');
    }

    /**
     * Create a request manually (staff intake by phone/radio)
     */
    public function createRequestForm(): void
    {
        Auth::requireLogin();
        $this->renderRequestForm([], []);
    }

    public function createRequest(): void
    {
        Auth::requireLogin();
        $old = $this->collectRequestInput();

        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->renderRequestForm(['csrf' => 'โทเค็นไม่ถูกต้อง กรุณารีเฟรชหน้า'], $old);
            return;
        }

        $errors = [];
        if ($old['reporter_name'] === '') {
            $errors['name'] = 'กรุณาระบุชื่อผู้แจ้ง';
        }
        if ($old['area_id'] > 0 && !Area::find($old['area_id'])) {
            $errors['area'] = 'ไม่พบพื้นที่ที่เลือก';
        }
        $hasLat = $old['lat'] !== null;
        $hasLng = $old['lng'] !== null;
        if ($hasLat !== $hasLng) {
            $errors['coords'] = 'กรุณาระบุละติจูดและลองจิจูดให้ครบ หรือเว้นว่างทั้งคู่';
        }

        $urgencyOptions = ['low', 'normal', 'high', 'critical'];
        $urgency = in_array($_POST['urgency'] ?? '', $urgencyOptions, true) ? $_POST['urgency'] : 'normal';
        $typeOptions = Helper::needTypes();
        $types = array_values(array_intersect(
            is_array($_POST['need_types'] ?? null) ? $_POST['need_types'] : [],
            array_keys($typeOptions)
        ));

        $photoPath = null;
        if (empty($errors) && !empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoPath = ImageUploader::store($_FILES['photo']);
        }

        if ($errors) {
            $this->renderRequestForm($errors, $old);
            return;
        }

        $id = Need::create([
            'reporter_name' => $old['reporter_name'],
            'reporter_phone' => $old['reporter_phone'] !== '' ? $old['reporter_phone'] : null,
            'area_id' => $old['area_id'] > 0 ? $old['area_id'] : null,
            'address' => $old['address'] !== '' ? $old['address'] : null,
            'lat' => $old['lat'],
            'lng' => $old['lng'],
            'people_count' => max(1, $old['people_count']),
            'need_types' => $types,
            'description' => $old['description'] !== '' ? $old['description'] : null,
            'urgency' => $urgency,
            'photo_path' => $photoPath,
            'scenario' => $old['scenario'],
        ]);
        Need::flushCache();

        View::redirect('admin/requests/' . $id);
    }

    private function collectRequestInput(): array
    {
        $lat = trim((string)($_POST['lat'] ?? ''));
        $lng = trim((string)($_POST['lng'] ?? ''));
        return [
            'reporter_name' => trim((string)($_POST['reporter_name'] ?? '')),
            'reporter_phone' => trim((string)($_POST['reporter_phone'] ?? '')),
            'people_count' => max(1, (int)($_POST['people_count'] ?? 1)),
            'area_id' => (int)($_POST['area_id'] ?? 0),
            'address' => trim((string)($_POST['address'] ?? '')),
            'lat' => $lat !== '' && is_numeric($lat) ? (float)$lat : null,
            'lng' => $lng !== '' && is_numeric($lng) ? (float)$lng : null,
            'description' => trim((string)($_POST['description'] ?? '')),
            'scenario' => in_array($_POST['scenario'] ?? '', array_keys(Helper::scenarioLabels()), true) ? $_POST['scenario'] : null,
        ];
    }

    private function renderRequestForm(array $errors, array $old): void
    {
        View::render('admin/request_form', [
            'title' => 'เพิ่มคำขอ',
            'areas' => Area::all(),
            'needTypes' => Helper::needTypes(),
            'urgencyLabels' => Helper::urgencyLabels(),
            'scenarioLabels' => Helper::scenarioLabels(),
            'oldTypes' => is_array($_POST['need_types'] ?? null) ? $_POST['need_types'] : [],
            'oldUrgency' => in_array($_POST['urgency'] ?? '', ['low','normal','high','critical'], true) ? $_POST['urgency'] : 'normal',
            'errors' => $errors,
            'old' => $old,
            'csrf' => Auth::csrfToken(),
            'user' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
            'includeMapAssets' => true,
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
                $db->prepare("INSERT INTO updates (need_id, user_id, new_status, message, is_internal) VALUES (?,?,?,?,1)")
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
            $db->prepare("INSERT INTO updates (need_id, user_id, old_status, new_status, message, is_internal) VALUES (?,?,?,?,?,1)")
               ->execute([(int)$id, $uid, $need['status'], $need['status'], $message]);
            Need::flushCache();
            View::json(['ok' => true, 'message' => 'บันทึกบันทึกแล้ว']);
        }

        View::json(['ok' => false, 'message' => 'คำสั่งไม่ถูกต้อง'], 422);
    }

    /**
     * Permanently delete a request — admin only
     */
    public function deleteRequest(string $id): void
    {
        Auth::requireAdmin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $need = Need::find((int)$id);
        if (!$need) {
            View::json(['ok' => false, 'message' => 'ไม่พบคำขอ'], 404);
        }

        $photoPath = $need['photo_path'] ?? null;
        $ok = Need::delete((int)$id);
        if ($ok) {
            Need::flushCache();
            if ($photoPath && str_starts_with($photoPath, 'uploads/')) {
                $file = PUBLIC_PATH . '/' . ltrim($photoPath, '/');
                if (is_file($file)) @unlink($file);
            }
        }
        View::json(['ok' => $ok, 'message' => $ok ? 'ลบคำขอแล้ว' : 'ลบไม่สำเร็จ']);
    }

    /**
     * Pending case count for live badge polling
     */
    public function pendingCount(): void
    {
        Auth::requireLogin();
        View::json(['ok' => true, 'count' => Need::count(['status' => 'pending'])]);
    }

    /**
     * Export filtered requests as CSV
     */
    public function exportRequests(): void
    {
        Auth::requireLogin();

        $filters = [];
        $status = $_GET['status'] ?? '';
        $urgency = $_GET['urgency'] ?? '';
        $q = trim((string)($_GET['q'] ?? ''));
        if ($status !== '' && $status !== 'all') $filters['status'] = $status;
        if ($urgency !== '') $filters['urgency'] = $urgency;
        if ($q !== '') $filters['q'] = $q;

        $rows = Need::list($filters, 5000, 0, false);
        $scenarioLabels = Helper::scenarioLabels();
        $urgencyLabels = Helper::urgencyLabels();
        $statusLabels = Helper::statusLabels();
        $needTypes = Helper::needTypes();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="requests-' . date('Ymd-His') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM for Excel Thai support
        fputcsv($out, ['รหัส', 'ผู้แจ้ง', 'เบอร์โทร', 'พื้นที่', 'จังหวัด', 'ที่อยู่', 'ละติจูด', 'ลองจิจูด', 'จำนวนคน', 'ความต้องการ', 'รายละเอียด', 'ความเร่งด่วน', 'สถานะ', 'ประเภทเหตุการณ์', 'ผู้รับผิดชอบ', 'เวลาที่แจ้ง']);
        foreach ($rows as $r) {
            $types = is_array($r['need_types']) ? $r['need_types'] : (json_decode($r['need_types'] ?? '[]', true) ?: []);
            fputcsv($out, [
                $r['code'],
                $r['reporter_name'],
                $r['reporter_phone'] ?? '',
                $r['area_name'] ?? '',
                $r['province'] ?? '',
                $r['address'] ?? '',
                $r['lat'] ?? '',
                $r['lng'] ?? '',
                $r['people_count'],
                implode(', ', array_map(fn($t) => $needTypes[$t] ?? $t, $types)),
                $r['description'] ?? '',
                $urgencyLabels[$r['urgency']] ?? $r['urgency'],
                $statusLabels[$r['status']] ?? $r['status'],
                $scenarioLabels[$r['scenario'] ?? ''] ?? ($r['scenario'] ?? ''),
                $r['assigned_name'] ?? '',
                $r['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }

    /**
     * Bulk status change / assign on selected requests
     */
    public function bulkRequestAction(): void
    {
        Auth::requireLogin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $ids = $_POST['ids'] ?? [];
        if (!is_array($ids)) $ids = [];
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($v) => $v > 0)));
        $ids = array_slice($ids, 0, 200);
        if (!$ids) {
            View::json(['ok' => false, 'message' => 'ไม่ได้เลือกรายการ'], 422);
        }

        $action = $_POST['bulk_action'] ?? '';
        $db = Database::getInstance();
        $uid = Auth::user()['id'];
        $done = 0;

        if ($action === 'status') {
            $status = $_POST['status'] ?? '';
            $allowed = ['pending', 'accepted', 'in_progress', 'completed', 'cancelled'];
            if (!in_array($status, $allowed, true)) {
                View::json(['ok' => false, 'message' => 'สถานะไม่ถูกต้อง'], 422);
            }
            foreach ($ids as $id) {
                $need = Need::find($id);
                if (!$need) continue;
                if (Need::updateStatus($id, $status, $uid, 'อัปเดตสถานะเป็นกลุ่ม')) {
                    if (in_array($status, ['accepted', 'in_progress'], true) && !$need['assigned_to']) {
                        Need::assign($id, $uid);
                    }
                    $done++;
                }
            }
        } elseif ($action === 'assign') {
            $target = (int)($_POST['user_id'] ?? 0);
            $assignee = $target > 0 ? User::find($target) : null;
            if (!$assignee) {
                View::json(['ok' => false, 'message' => 'กรุณาเลือกผู้รับผิดชอบ'], 422);
            }
            foreach ($ids as $id) {
                $need = Need::find($id);
                if (!$need) continue;
                if (Need::assign($id, $target)) {
                    $db->prepare("INSERT INTO updates (need_id, user_id, new_status, message) VALUES (?,?,?,?)")
                       ->execute([$id, $uid, $need['status'], 'มอบหมายให้ ' . $assignee['name'] . ' (กลุ่ม)']);
                    $done++;
                }
            }
        } else {
            View::json(['ok' => false, 'message' => 'คำสั่งไม่ถูกต้อง'], 422);
        }

        if ($done) Need::flushCache();
        View::json(['ok' => true, 'message' => "ดำเนินการ {$done} รายการแล้ว", 'count' => $done]);
    }

    /**
     * Update editable fields of a request
     */
    public function updateRequest(string $id): void
    {
        Auth::requireLogin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $need = Need::find((int)$id);
        if (!$need) {
            View::json(['ok' => false, 'message' => 'ไม่พบคำขอ'], 404);
        }

        $name = trim((string)($_POST['reporter_name'] ?? ''));
        if ($name === '') {
            View::json(['ok' => false, 'message' => 'กรุณาระบุชื่อผู้แจ้ง'], 422);
        }

        $latRaw = trim((string)($_POST['lat'] ?? ''));
        $lngRaw = trim((string)($_POST['lng'] ?? ''));
        $lat = $latRaw !== '' && is_numeric($latRaw) ? (float)$latRaw : null;
        $lng = $lngRaw !== '' && is_numeric($lngRaw) ? (float)$lngRaw : null;
        if (($lat === null) !== ($lng === null)) {
            View::json(['ok' => false, 'message' => 'ละติจูด/ลองจิจูดต้องกรอกคู่กัน'], 422);
        }

        $areaId = (int)($_POST['area_id'] ?? 0);
        if ($areaId > 0 && !Area::find($areaId)) {
            View::json(['ok' => false, 'message' => 'ไม่พบพื้นที่ที่เลือก'], 422);
        }

        $urgency = $_POST['urgency'] ?? '';
        if (!in_array($urgency, ['low', 'normal', 'high', 'critical'], true)) {
            View::json(['ok' => false, 'message' => 'ความเร่งด่วนไม่ถูกต้อง'], 422);
        }
        $scenario = in_array($_POST['scenario'] ?? '', array_keys(Helper::scenarioLabels()), true) ? $_POST['scenario'] : null;
        $types = $_POST['need_types'] ?? [];
        if (!is_array($types)) $types = [];
        $typeOptions = Helper::needTypes();
        $types = array_values(array_intersect($types, array_keys($typeOptions)));

        $ok = Need::updateDetails((int)$id, [
            'reporter_name' => $name,
            'reporter_phone' => trim((string)($_POST['reporter_phone'] ?? '')) ?: null,
            'area_id' => $areaId > 0 ? $areaId : null,
            'address' => trim((string)($_POST['address'] ?? '')) ?: null,
            'lat' => $lat,
            'lng' => $lng,
            'people_count' => max(1, (int)($_POST['people_count'] ?? 1)),
            'need_types' => json_encode($types, JSON_UNESCAPED_UNICODE),
            'description' => trim((string)($_POST['description'] ?? '')) ?: null,
            'urgency' => $urgency,
            'scenario' => $scenario,
        ]);
        if ($ok) Need::flushCache();
        View::json(['ok' => $ok, 'message' => $ok ? 'บันทึกข้อมูลแล้ว' : 'ไม่สำเร็จ']);
    }

    /**
     * Broadcast announcements — staff can compose, admin can delete
     */
    public function announcements(): void
    {
        Auth::requireLogin();

        View::render('admin/announcements', [
            'title' => 'ประกาศ',
            'announcements' => Announcement::all(),
            'levels' => ['info' => 'ข้อมูลทั่วไป', 'warning' => 'แจ้งเตือน', 'critical' => 'เร่งด่วน'],
            'audiences' => ['public' => 'ประชาชน', 'staff' => 'ทีมงาน', 'all' => 'ทุกคน'],
            'csrf' => Auth::csrfToken(),
            'user' => Auth::user(),
            'isAdmin' => Auth::isAdmin(),
        ], 'layouts/admin');
    }

    public function saveAnnouncement(): void
    {
        Auth::requireLogin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $level = $_POST['level'] ?? 'info';
        $audience = $_POST['audience'] ?? 'all';

        if ($title === '' || $body === '') {
            View::json(['ok' => false, 'message' => 'กรุณาระบุหัวข้อและข้อความ'], 422);
        }
        if (!in_array($level, Announcement::LEVELS, true)) {
            View::json(['ok' => false, 'message' => 'ระดับไม่ถูกต้อง'], 422);
        }
        if (!in_array($audience, Announcement::AUDIENCES, true)) {
            View::json(['ok' => false, 'message' => 'กลุ่มเป้าหมายไม่ถูกต้อง'], 422);
        }

        Announcement::create([
            'title' => $title,
            'body' => $body,
            'level' => $level,
            'audience' => $audience,
            'created_by' => Auth::user()['id'] ?? null,
        ]);
        View::json(['ok' => true, 'message' => 'ส่งประกาศแล้ว']);
    }

    public function deleteAnnouncement(string $id): void
    {
        Auth::requireAdmin();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }
        Announcement::delete((int)$id);
        View::json(['ok' => true, 'message' => 'ลบประกาศแล้ว']);
    }

    /**
     * Latest announcement visible to the current viewer (public endpoint, polled by popup JS)
     */
    public function latestAnnouncement(): void
    {
        // ?for=admin → staff audience; otherwise public audience
        $for = ($_GET['for'] ?? '') === 'admin' ? 'staff' : 'public';
        // Short server-side cache — one DB hit per 20s regardless of open tabs.
        // Wrapped in array so "no announcement" is cached too (Cache can't store null).
        $cached = Cache::remember('ann_latest:' . $for, 20, fn() => ['a' => Announcement::latestFor($for)]);
        $latest = $cached['a'] ?? null;
        View::json(['ok' => true, 'announcement' => $latest ?: null]);
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
}
