<?php
/**
 * Public status tracking by request code (e.g. DSC-1A2B3C4D)
 */

class TrackController
{
    public function index(): void
    {
        $code = strtoupper(trim($_GET['code'] ?? ''));
        $need = null;
        $history = [];

        if ($code !== '') {
            $found = Need::findByCode($code);
            if ($found) {
                $need = $found;
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    SELECT old_status, new_status, message, created_at
                    FROM updates
                    WHERE need_id = ?
                    ORDER BY created_at ASC, id ASC
                ");
                $stmt->execute([$need['id']]);
                $history = $stmt->fetchAll();
            }
        }

        View::render('needs/track', [
            'title' => 'ติดตามสถานะ',
            'code' => $code,
            'need' => $need,
            'history' => $history,
            'statusLabels' => Helper::statusLabels(),
            'urgencyLabels' => Helper::urgencyLabels(),
            'needTypes' => Helper::needTypes(),
        ]);
    }
}
