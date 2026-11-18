<?php
/**
 * Volunteer workspace — mobile-first case handling.
 * Volunteers log in, claim open cases, update status, transfer, release,
 * and open new cases on behalf of reporters.
 */

class VolunteerController
{
    private function guard(): array
    {
        Auth::requireLogin();
        if (!Auth::isVolunteer()) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'เฉพาะอาสาสมัคร']);
            exit;
        }
        return Auth::user();
    }

    private function verifyCsrf(): void
    {
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง กรุณารีเฟรชหน้า'], 403);
        }
    }

    /**
     * Dashboard: my cases + open pool + availability + stats
     */
    public function index(): void
    {
        $u = $this->guard();

        $mine = Need::list(['assigned_to' => (int)$u['id']], 60, 0);
        $mineActive = [];
        $mineDone = [];
        foreach ($mine as $n) {
            if (in_array($n['status'], ['pending', 'accepted', 'in_progress'], true)) {
                $mineActive[] = $n;
            } else {
                $mineDone[] = $n;
            }
        }
        $mineDone = array_slice($mineDone, 0, 10);

        // Claimable pool: unassigned cases still in pending/accepted
        $pool = Need::list(['unassigned' => 1, 'statuses' => ['pending', 'accepted']], 60, 0);

        View::render('volunteer/home', [
            'title' => 'งานของฉัน',
            'user' => $u,
            'profile' => Volunteer::myProfile((int)$u['id']),
            'stats' => Need::myStats((int)$u['id']),
            'mineActive' => $mineActive,
            'mineDone' => $mineDone,
            'pool' => $pool,
            'statusLabels' => Helper::statusLabels(),
            'urgencyLabels' => Helper::urgencyLabels(),
            'scenarioLabels' => Helper::scenarioLabels(),
            'needTypes' => Helper::needTypes(),
            'csrf' => Auth::csrfToken(),
        ], 'layouts/volunteer');
    }

    /**
     * Case detail — staff view: full reporter contact, map, timeline,
     * and action bar (claim / status / release / transfer)
     */
    public function show(string $id): void
    {
        $u = $this->guard();
        $need = Need::find((int)$id);
        if (!$need) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'ไม่พบข้อมูล'], 'layouts/volunteer');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT u.*, us.name AS user_name
            FROM updates u
            LEFT JOIN users us ON us.id = u.user_id
            WHERE u.need_id = ?
            ORDER BY u.created_at ASC, u.id ASC
        ");
        $stmt->execute([(int)$id]);
        $history = $stmt->fetchAll();

        $isAdmin = Auth::isAdmin();
        $isMine = (int)($need['assigned_to'] ?? 0) === (int)$u['id'];
        $canAct = $isMine || $isAdmin;
        $canClaim = !$need['assigned_to'] && in_array($need['status'], ['pending', 'accepted'], true);

        // Next-step status the actor may set
        $nextStatus = null;
        if ($canAct && !in_array($need['status'], ['completed', 'cancelled'], true)) {
            $nextStatus = match ($need['status']) {
                'pending' => 'accepted',
                'accepted' => 'in_progress',
                'in_progress' => 'completed',
                default => null,
            };
        }

        View::render('volunteer/case', [
            'title' => 'เคส ' . $need['code'],
            'need' => $need,
            'history' => $history,
            'user' => $u,
            'canAct' => $canAct,
            'canClaim' => $canClaim,
            'nextStatus' => $nextStatus,
            'volunteers' => Volunteer::options(),
            'statusLabels' => Helper::statusLabels(),
            'urgencyLabels' => Helper::urgencyLabels(),
            'scenarioLabels' => Helper::scenarioLabels(),
            'needTypes' => Helper::needTypes(),
            'csrf' => Auth::csrfToken(),
        ], 'layouts/volunteer');
    }

    public function claim(string $id): void
    {
        $u = $this->guard();
        $this->verifyCsrf();

        $ok = Need::claimCase((int)$id, (int)$u['id'], $u['name']);
        if ($ok) Need::flushCache();
        View::json(['ok' => $ok, 'message' => $ok ? 'รับเคสเรียบร้อย' : 'เคสนี้มีผู้รับแล้วหรือปิดไปแล้ว']);
    }

    public function setStatus(string $id): void
    {
        $u = $this->guard();
        $this->verifyCsrf();

        $need = Need::find((int)$id);
        if (!$need) {
            View::json(['ok' => false, 'message' => 'ไม่พบเคส'], 404);
        }
        $isMine = (int)($need['assigned_to'] ?? 0) === (int)$u['id'];
        if (!$isMine && !Auth::isAdmin()) {
            View::json(['ok' => false, 'message' => 'เฉพาะผู้รับผิดชอบเคสนี้เท่านั้น'], 403);
        }

        // Volunteers may only move forward along the workflow
        $status = $_POST['status'] ?? '';
        $allowedNext = match ($need['status']) {
            'pending' => ['accepted'],
            'accepted' => ['in_progress', 'completed'],
            'in_progress' => ['completed'],
            default => [],
        };
        if (Auth::isAdmin()) {
            $allowedNext = ['pending', 'accepted', 'in_progress', 'completed', 'cancelled'];
        }
        if (!in_array($status, $allowedNext, true)) {
            View::json(['ok' => false, 'message' => 'ไม่สามารถเปลี่ยนเป็นสถานะนี้ได้'], 422);
        }

        $message = trim($_POST['message'] ?? '');
        $ok = Need::updateStatus((int)$id, $status, (int)$u['id'], $message !== '' ? $message : null);
        if ($ok && in_array($status, ['accepted', 'in_progress'], true) && !$need['assigned_to']) {
            Need::assign((int)$id, (int)$u['id']);
        }
        if ($ok) Need::flushCache();
        View::json(['ok' => $ok, 'message' => $ok ? 'อัปเดตสถานะแล้ว' : 'ไม่สำเร็จ']);
    }

    public function release(string $id): void
    {
        $u = $this->guard();
        $this->verifyCsrf();

        $ok = Need::releaseCase((int)$id, (int)$u['id'], $u['name']);
        if ($ok) Need::flushCache();
        View::json(['ok' => $ok, 'message' => $ok ? 'คืนเคสเข้ากองรวมแล้ว' : 'คืนเคสไม่สำเร็จ (อาจไม่ใช่เคสของคุณหรือปิดแล้ว)']);
    }

    public function transfer(string $id): void
    {
        $u = $this->guard();
        $this->verifyCsrf();

        $toId = (int)($_POST['to_user_id'] ?? 0);
        $target = null;
        foreach (Volunteer::options() as $v) {
            if ((int)$v['id'] === $toId) { $target = $v; break; }
        }
        if (!$target) {
            View::json(['ok' => false, 'message' => 'ไม่พบอาสาสมัครที่เลือก'], 422);
        }

        $ok = Need::transferCase((int)$id, (int)$u['id'], $toId, $u['name'], $target['name']);
        if ($ok) Need::flushCache();
        View::json(['ok' => $ok, 'message' => $ok ? 'โอนเคสให้ ' . $target['name'] . ' แล้ว' : 'โอนได้เฉพาะเคสที่คุณเป็นผู้รับผิดชอบ']);
    }

    public function availability(): void
    {
        $u = $this->guard();
        $this->verifyCsrf();

        $avail = $_POST['availability'] ?? '';
        if (!in_array($avail, ['available', 'busy'], true)) {
            View::json(['ok' => false, 'message' => 'สถานะไม่ถูกต้อง'], 422);
        }
        Volunteer::setAvailability((int)$u['id'], $avail);
        View::json(['ok' => true, 'availability' => $avail]);
    }

    /**
     * Open a new case on behalf of a reporter — auto-assigns to self
     */
    public function newForm(): void
    {
        $u = $this->guard();
        View::render('volunteer/form', [
            'title' => 'เปิดเคสใหม่',
            'user' => $u,
            'areas' => Area::all(),
            'needTypes' => Helper::needTypes(),
            'urgencyLabels' => Helper::urgencyLabels(),
            'scenarioLabels' => Helper::scenarioLabels(),
            'csrf' => Auth::csrfToken(),
        ], 'layouts/volunteer');
    }

    public function store(): void
    {
        $u = $this->guard();
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            View::redirect('/volunteer/new');
        }

        $name = trim($_POST['reporter_name'] ?? '');
        $phone = trim($_POST['reporter_phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $lat = !empty($_POST['lat']) ? (float)$_POST['lat'] : null;
        $lng = !empty($_POST['lng']) ? (float)$_POST['lng'] : null;
        $people = max(1, (int)($_POST['people_count'] ?? 1));
        $types = $_POST['need_types'] ?? [];
        if (!is_array($types)) $types = [];
        $description = trim($_POST['description'] ?? '');
        $urgency = $_POST['urgency'] ?? 'normal';
        $scenarioKeys = array_keys(Helper::scenarioLabels());
        $scenario = in_array($_POST['scenario'] ?? '', $scenarioKeys, true) ? $_POST['scenario'] : null;
        $areaId = !empty($_POST['area_id']) ? (int)$_POST['area_id'] : null;

        if ($name === '') {
            View::redirect('/volunteer/new');
        }

        $photoPath = null;
        if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoPath = ImageUploader::store($_FILES['photo']);
        }

        $id = Need::create([
            'reporter_name' => $name,
            'reporter_phone' => $phone,
            'area_id' => $areaId,
            'address' => $address,
            'lat' => $lat,
            'lng' => $lng,
            'people_count' => $people,
            'need_types' => $types,
            'description' => $description,
            'urgency' => in_array($urgency, ['low', 'normal', 'high', 'critical'], true) ? $urgency : 'normal',
            'photo_path' => $photoPath,
            'scenario' => $scenario,
        ]);

        // Volunteer who opened the case owns it from the start
        Need::assign($id, (int)$u['id']);
        Need::updateStatus($id, 'accepted', (int)$u['id'], 'อาสาสมัคร ' . $u['name'] . ' เปิดเคสและรับผิดชอบ');
        Need::flushCache();

        View::redirect('/volunteer/cases/' . $id);
    }
}
