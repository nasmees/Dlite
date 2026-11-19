<?php
/**
 * Home / Request Help (main entry for victims)
 */

class HomeController
{
    public function index(): void
    {
        // Sectioned home — map card + status summary + latest requests
        $publicOnly = !Auth::check();
        View::render('needs/home', [
            'title' => 'Dlite',
            'areas' => Area::all(),
            'needTypes' => Helper::needTypes(),
            'csrf' => Auth::csrfToken(),
            'counts' => Need::statusCounts($publicOnly),
            'urgent' => Need::urgentCases($publicOnly, 5),
            'statusLabels' => Helper::statusLabels(),
            'urgencyLabels' => Helper::urgencyLabels(),
            'scenarioLabels' => Helper::scenarioLabels(),
        ]);
    }

    /**
     * Standalone form page — fallback for no-JS / direct link
     */
    public function requestForm(): void
    {
        View::render('needs/request', [
            'title' => 'ขอความช่วยเหลือ',
            'areas' => Area::all(),
            'needTypes' => Helper::needTypes(),
            'csrf' => Auth::csrfToken(),
        ]);
    }

    public function store(): void
    {
        // Progressive enhancement: if request is a normal (non-JS) form POST,
        // redirect back with result instead of returning JSON
        $wantsJson = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || !empty($_POST['ajax']);

        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            if ($wantsJson) {
                View::json(['ok' => false, 'message' => 'โทเค็นไม่ถูกต้อง กรุณารีเฟรชหน้า'], 403);
            }
            View::redirect('/?error=csrf');
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

        // Idempotency: offline-queue retries reuse the same client_token — return the existing case instead of duplicating
        $clientToken = trim($_POST['client_token'] ?? '');
        if ($clientToken !== '' && preg_match('/^[A-Za-z0-9_-]{8,64}$/', $clientToken)) {
            $dup = Database::getInstance()
                ->prepare("SELECT id FROM needs WHERE client_token = ?");
            $dup->execute([$clientToken]);
            $dupId = (int)$dup->fetchColumn();
            if ($dupId > 0) {
                $existing = Need::find($dupId);
                if ($existing) {
                    if (!$wantsJson) {
                        View::redirect('/success?code=' . urlencode($existing['code']));
                    }
                    View::json(['ok' => true, 'message' => 'ส่งคำขอสำเร็จ', 'code' => $existing['code'], 'id' => $dupId, 'duplicate' => true]);
                }
            }
        }

        if ($name === '') {
            if ($wantsJson) {
                View::json(['ok' => false, 'message' => 'กรุณาระบุชื่อผู้แจ้ง'], 422);
            }
            View::redirect('/?error=name');
        }

        // Handle photo upload
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
            'urgency' => in_array($urgency, ['low','normal','high','critical'], true) ? $urgency : 'normal',
            'photo_path' => $photoPath,
            'scenario' => $scenario,
            'client_token' => (preg_match('/^[A-Za-z0-9_-]{8,64}$/', $clientToken) === 1) ? $clientToken : null,
        ]);

        Need::flushCache();
        $need = Need::find($id);

        if (!$wantsJson) {
            View::redirect('/success?code=' . urlencode($need['code']));
        }

        View::json([
            'ok' => true,
            'message' => 'ส่งคำขอสำเร็จ',
            'code' => $need['code'],
            'id' => $id,
        ]);
    }

    public function success(): void
    {
        $code = $_GET['code'] ?? '';
        View::render('needs/success', [
            'title' => 'ส่งคำขอสำเร็จ',
            'code' => $code,
        ]);
    }
}
