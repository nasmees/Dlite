<?php
/**
 * Map view of needs
 */

class MapController
{
    public function index(): void
    {
        // Fullscreen map is a volunteer tool — public uses the map card on home
        if (!Auth::check()) {
            View::redirect('/');
            return;
        }
        View::render('needs/map', [
            'title' => 'แผนที่ความช่วยเหลือ',
            'csrf' => Auth::csrfToken(),
            'fullWidth' => true,
        ]);
    }

    public function points(): void
    {
        $status = $_GET['status'] ?? null;
        $filters = [];
        if ($status && $status !== 'all') {
            $filters['status'] = $status;
        }

        // Short cache — map data changes often during emergencies but polls are frequent
        $cacheKey = 'map_points:' . md5(json_encode($filters));
        $points = Cache::remember($cacheKey, 10, fn() => Need::mapPoints($filters));

        View::json(['ok' => true, 'points' => $points]);
    }
}
