<?php
/**
 * Needs list & detail
 */

class NeedController
{
    private const PER_PAGE = 20;

    public function index(): void
    {
        $status = $_GET['status'] ?? '';
        $q = trim($_GET['q'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [];
        if ($status) $filters['status'] = $status;
        if ($q) $filters['q'] = $q;

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
            $counts = [
                'all' => Need::count([], $publicOnly),
                'pending' => Need::count(['status' => 'pending'], $publicOnly),
                'in_progress' => Need::count(['status' => 'in_progress'], $publicOnly),
                'completed' => Need::count(['status' => 'completed'], $publicOnly),
            ];
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
            'filters' => ['status' => $status, 'q' => $q],
            'statusLabels' => Helper::statusLabels(),
            'urgencyLabels' => Helper::urgencyLabels(),
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
            WHERE u.need_id = ?
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
            ->prepare("INSERT INTO updates (need_id, user_id, new_status, message) VALUES (?, ?, ?, ?)")
            ->execute([(int)$id, $user['id'], $need['status'], 'เสนอความช่วยเหลือ: ' . (Help::helpTypes()[$helpType] ?? $helpType)]);

        Need::flushCache();
        View::json(['ok' => true, 'message' => 'บันทึกความช่วยเหลือแล้ว']);
    }
}
