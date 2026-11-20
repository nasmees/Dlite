<?php
/**
 * Needs list & detail
 */

class NeedController
{
    private const PER_PAGE = 20;

    public function index(): void
    {
        // Default: only active cases — completed/cancelled require explicit filter
        $status = $_GET['status'] ?? 'active';
        $validStatuses = array_merge(['active', 'all'], array_keys(Helper::statusLabels()));
        if (!in_array($status, $validStatuses, true)) $status = 'active';
        $q = trim($_GET['q'] ?? '');
        $urgency = $_GET['urgency'] ?? '';
        $scenario = $_GET['scenario'] ?? '';
        $sort = ($_GET['sort'] ?? 'latest') === 'urgent' ? 'urgent' : 'latest';
        $page = max(1, (int)($_GET['page'] ?? 1));

        $urgencyKeys = array_keys(Helper::urgencyLabels());
        $scenarioKeys = array_keys(Helper::scenarioLabels());
        if (!in_array($urgency, $urgencyKeys, true)) $urgency = '';
        if (!in_array($scenario, $scenarioKeys, true)) $scenario = '';

        $filters = ['sort' => $sort];
        if ($status === 'active') {
            $filters['statuses'] = ['pending', 'accepted', 'in_progress'];
        } elseif ($status !== 'all') {
            $filters['status'] = $status;
        }
        if ($q) $filters['q'] = $q;
        if ($urgency) $filters['urgency'] = $urgency;
        if ($scenario) $filters['scenario'] = $scenario;

        // Public visitors only see public needs; logged-in volunteers see all
        $publicOnly = !Auth::check();

        // File cache for list page — short TTL keeps it fresh during emergencies
        $cacheKey = 'needs_list:' . md5(json_encode([$filters, $page, $publicOnly]));
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            $needs = $cached['needs'];
            $pagination = $cached['pagination'];
            $counts = $cached['counts'];
        } else {
            $offset = ($page - 1) * self::PER_PAGE;
            $needs = Need::list($filters, self::PER_PAGE, $offset, $publicOnly);
            $total = Need::count($filters, $publicOnly);
            $pagination = Helper::pagination($page, self::PER_PAGE, $total);
            $counts = Need::statusCounts($publicOnly);
            Cache::set($cacheKey, [
                'needs' => $needs,
                'pagination' => $pagination,
                'counts' => $counts,
            ], CACHE_TTL);
        }

        View::render('needs/list', [
            'title' => 'รายการความต้องการ',
            'needs' => $needs,
            'counts' => $counts,
            'pagination' => $pagination,
            'filters' => ['status' => $status, 'q' => $q, 'urgency' => $urgency, 'scenario' => $scenario, 'sort' => $sort],
            'isLoggedIn' => Auth::check(),
            'statusLabels' => Helper::statusLabels(),
            'urgencyLabels' => Helper::urgencyLabels(),
            'scenarioLabels' => Helper::scenarioLabels(),
            'needTypes' => Helper::needTypes(),
        ]);
    }

    public function show(string $id): void
    {
        $need = Need::find((int)$id);
        if (!$need) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'ไม่พบข้อมูล']);
            return;
        }

        $db = Database::getInstance();
        $updates = $db->prepare("
            SELECT u.*, us.name as user_name
            FROM updates u
            LEFT JOIN users us ON us.id = u.user_id
            WHERE u.need_id = ? AND u.is_internal = 0
            ORDER BY u.created_at DESC
        ");
        $updates->execute([(int)$id]);
        $history = $updates->fetchAll();

        View::render('needs/detail', [
            'title' => 'รายละเอียดคำขอ ' . $need['code'],
            'need' => $need,
            'history' => $history,
            'helps' => Help::forNeed((int)$id),
            'helpTypes' => Help::helpTypes(),
            'statusLabels' => Helper::statusLabels(),
            'urgencyLabels' => Helper::urgencyLabels(),
            'needTypes' => Helper::needTypes(),
            'scenarioLabels' => Helper::scenarioLabels(),
            'csrf' => Auth::csrfToken(),
            'isVolunteer' => Auth::isVolunteer(),
            'isLoggedIn' => Auth::check(),
        ]);
    }

    public function updateStatus(string $id): void
    {
        Auth::requireLogin();
        if (!Auth::isVolunteer()) {
            View::json(['ok' => false, 'message' => 'ไม่มีสิทธิ์'], 403);
        }
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง'], 403);
        }

        $status = $_POST['status'] ?? '';
        $message = trim($_POST['message'] ?? '');
        $allowed = ['pending','accepted','in_progress','completed','cancelled'];
        if (!in_array($status, $allowed, true)) {
            View::json(['ok' => false, 'message' => 'สถานะไม่ถูกต้อง'], 422);
        }

        $ok = Need::updateStatus((int)$id, $status, Auth::user()['id'], $message ?: null);
        if ($ok && in_array($status, ['accepted','in_progress'], true)) {
            Need::assign((int)$id, Auth::user()['id']);
        }
        if ($ok) {
            Need::flushCache();
        }

        View::json(['ok' => $ok, 'message' => $ok ? 'อัปเดตสถานะแล้ว' : 'ไม่สำเร็จ']);
    }

    /**
     * Reporter self-closes their own case (e.g. help already arrived informally).
     * Verified by case code + reporter phone — no login required.
     */
    public function close(string $id): void
    {
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง กรุณารีเฟรชหน้า'], 403);
        }

        $need = Need::find((int)$id);
        if (!$need) {
            View::json(['ok' => false, 'message' => 'ไม่พบรายการ'], 404);
        }
        if (in_array($need['status'], ['completed', 'cancelled'], true)) {
            View::json(['ok' => false, 'message' => 'รายการนี้ปิดแล้ว'], 422);
        }

        $code = strtoupper(trim($_POST['code'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $norm = fn(string $p) => substr(preg_replace('/\D/', '', $p), -9);

        if ($code === '' || $code !== strtoupper($need['code'])) {
            View::json(['ok' => false, 'message' => 'รหัสเคสไม่ถูกต้อง'], 422);
        }
        if ($norm($phone) === '' || strlen($norm($phone)) < 9 || $norm($phone) !== $norm($need['reporter_phone'] ?? '')) {
            View::json(['ok' => false, 'message' => 'เบอร์โทรไม่ตรงกับผู้แจ้ง'], 422);
        }

        Need::updateStatus((int)$id, 'completed', null, 'ผู้แจ้งปิดเคสด้วยตนเอง — ได้รับความช่วยเหลือแล้ว');
        Need::flushCache();
        View::json(['ok' => true, 'message' => 'ปิดเคสเรียบร้อยแล้ว ขอบคุณที่แจ้งเหตุ']);
    }

    /**
     * Public-safe status lookup for "my report" banner (no personal data)
     */
    public function reportStatus(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $need = $id ? Need::find($id) : null;
        if (!$need) {
            View::json(['ok' => true, 'found' => false]);
        }
        View::json([
            'ok' => true,
            'found' => true,
            'id' => (int)$need['id'],
            'code' => $need['code'],
            'status' => $need['status'],
            'lat' => $need['lat'],
            'lng' => $need['lng'],
            'scenario' => $need['scenario'] ?? null,
            'need_types' => $need['need_types'] ?? [],
            'people_count' => (int)($need['people_count'] ?? 0),
            'district' => $need['district'] ?? null,
            'province' => $need['province'] ?? null,
            'created_at' => $need['created_at'] ?? null,
        ]);
    }

    /**
     * Public-safe case detail + public timeline for the track-page popup.
     * Strips reporter identity, phone, address, assignee.
     */
    public function detailJson(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $need = $id ? Need::find($id) : null;
        if (!$need) {
            View::json(['ok' => true, 'found' => false]);
            return;
        }
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT old_status, new_status, message, photo_path, created_at
            FROM updates
            WHERE need_id = ? AND is_internal = 0
            ORDER BY created_at ASC, id ASC
        ");
        $stmt->execute([$need['id']]);

        View::json([
            'ok' => true,
            'found' => true,
            'need' => [
                'id' => (int)$need['id'],
                'code' => $need['code'],
                'status' => $need['status'],
                'urgency' => $need['urgency'] ?? 'normal',
                'scenario' => $need['scenario'] ?? null,
                'need_types' => $need['need_types'] ?? [],
                'people_count' => (int)($need['people_count'] ?? 0),
                'district' => $need['district'] ?? null,
                'province' => $need['province'] ?? null,
                'area_name' => $need['area_name'] ?? null,
                'description' => $need['description'] ?? null,
                'photo_path' => $need['photo_path'] ?? null,
                'lat' => $need['lat'],
                'lng' => $need['lng'],
                'created_at' => $need['created_at'] ?? null,
            ],
            'history' => $stmt->fetchAll(),
        ]);
    }

    /**
     * Lightweight counts endpoint for auto-refresh badges
     */
    public function summary(): void
    {
        $publicOnly = !Auth::check();
        View::json([
            'ok' => true,
            'counts' => [
                'all' => Need::count([], $publicOnly),
                'pending' => Need::count(['status' => 'pending'], $publicOnly),
                'accepted' => Need::count(['status' => 'accepted'], $publicOnly),
                'in_progress' => Need::count(['status' => 'in_progress'], $publicOnly),
                'completed' => Need::count(['status' => 'completed'], $publicOnly),
            ],
        ]);
    }

    /**
     * Volunteer offers assistance on a need
     */
    public function help(string $id): void
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
            View::json(['ok' => false, 'message' => 'ไม่พบรายการ'], 404);
        }
        if (in_array($need['status'], ['completed', 'cancelled'], true)) {
            View::json(['ok' => false, 'message' => 'รายการนี้ปิดแล้ว'], 422);
        }

        $user = Auth::user();
        $helpType = $_POST['help_type'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        $allowedTypes = array_keys(Help::helpTypes());
        if (!in_array($helpType, $allowedTypes, true)) {
            View::json(['ok' => false, 'message' => 'ประเภทความช่วยเหลือไม่ถูกต้อง'], 422);
        }

        Help::create([
            'need_id' => (int)$id,
            'helper_name' => $user['name'],
            'helper_phone' => $user['phone'],
            'helper_org' => trim($_POST['helper_org'] ?? ''),
            'help_type' => $helpType,
            'notes' => $notes ?: null,
        ]);

        // Log to updates timeline so reporter can see progress
        Database::getInstance()
            ->prepare("INSERT INTO updates (need_id, user_id, new_status, message, is_internal) VALUES (?, ?, ?, ?, 1)")
            ->execute([(int)$id, $user['id'], $need['status'], 'เสนอความช่วยเหลือ: ' . (Help::helpTypes()[$helpType] ?? $helpType)]);

        Need::flushCache();
        View::json(['ok' => true, 'message' => 'บันทึกความช่วยเหลือแล้ว']);
    }
}
