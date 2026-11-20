<?php
/**
 * Public status tracking by request code (e.g. DSC-1A2B3C4D)
 */

class TrackController
{
    public function index(): void
    {
        // Single field accepts either a case code or a phone number
        $query = strtoupper(trim($_GET['q'] ?? $_GET['code'] ?? ''));
        $isPhone = preg_match('/^\d[\d\s\-+()]*$/', $query) === 1 && strlen(preg_replace('/\D/', '', $query)) >= 9;

        $need = null;
        $history = [];
        $results = [];

        if ($query !== '') {
            if ($isPhone) {
                $results = Need::findByPhone($query);
            } else {
                $found = Need::findByCode($query);
                if ($found) {
                    $need = $found;
                    $db = Database::getInstance();
                    $stmt = $db->prepare("
                        SELECT old_status, new_status, message, photo_path, created_at
                        FROM updates
                        WHERE need_id = ? AND is_internal = 0
                        ORDER BY created_at ASC, id ASC
                    ");
                    $stmt->execute([$need['id']]);
                    $history = $stmt->fetchAll();
                }
            }
        }

        View::render('needs/track', [
            'title' => 'ติดตามสถานะ',
            'query' => $query,
            'isPhone' => $isPhone,
            'results' => $results,
            'need' => $need,
            'history' => $history,
            'statusLabels' => Helper::statusLabels(),
            'urgencyLabels' => Helper::urgencyLabels(),
            'scenarioLabels' => Helper::scenarioLabels(),
            'needTypes' => Helper::needTypes(),
            'csrf' => Auth::csrfToken(),
        ]);
    }
}
