<?php
/**
 * Map view of needs
 */

class MapController
{
    public function index(): void
    {
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
        if ($status === 'active') {
            $filters['statuses'] = ['pending', 'accepted', 'in_progress'];
        } elseif ($status && $status !== 'all') {
            $filters['status'] = $status;
        }

        // Short cache — map data changes often during emergencies but polls are frequent
        $cacheKey = 'map_points:' . md5(json_encode($filters)) . ':' . (Auth::check() ? 's' : 'p');
        $points = Cache::remember($cacheKey, 10, fn() => Need::mapPoints($filters));

        // Anonymous visitors must not see reporter names
        if (!Auth::check()) {
            foreach ($points as &$p) {
                unset($p['name']);
            }
            unset($p);
        }

        View::json(['ok' => true, 'points' => $points]);
    }

    /**
     * Public important places (shelters, evacuation points, supply stations…)
     */
    public function pois(): void
    {
        $cacheKey = 'public_pois';
        $pois = Cache::remember($cacheKey, 60, fn() => Poi::all(true));

        $out = [];
        foreach ($pois as $p) {
            $cat = Helper::poiCategory($p['category']);
            $out[] = [
                'id' => (int)$p['id'],
                'title' => $p['title'],
                'category' => $p['category'],
                'category_label' => $cat['label'],
                'emoji' => $cat['emoji'],
                'color' => $cat['color'],
                'description' => $p['description'],
                'lat' => (float)$p['lat'],
                'lng' => (float)$p['lng'],
            ];
        }
        View::json(['ok' => true, 'pois' => $out]);
    }
}
