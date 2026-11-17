<?php
/**
 * Helper functions
 */

class Helper
{
    public static function e(?string $str): string
    {
        return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function generateCode(): string
    {
        return 'DSC-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    public static function needTypes(): array
    {
        return [
            'food' => 'อาหาร',
            'water' => 'น้ำดื่ม',
            'medicine' => 'ยา / เวชภัณฑ์',
            'shelter' => 'ที่พัก / ที่อพยพ',
            'evacuation' => 'ช่วยอพยพ',
            'clothing' => 'เสื้อผ้า',
            'other' => 'อื่นๆ',
        ];
    }

    /**
     * Scenario keys used by the public report form's "เกิดอะไรขึ้น?" cards
     */
    public static function scenarioLabels(): array
    {
        return [
            'flood' => 'น้ำท่วม / ถนนท่วม',
            'evac' => 'ต้องอพยพ / บ้านเสียหาย',
            'sos' => 'เหตุฉุกเฉิน SOS',
            'medical' => 'เจ็บป่วย / บาดเจ็บ',
            'supplies' => 'ขาดแคลนอาหาร / น้ำ',
            'other' => 'อื่นๆ',
        ];
    }

    /**
     * SVG inner content (path elements) for each scenario — stroke icons
     */
    public static function scenarioIcons(): array
    {
        return [
            'flood'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c3 4 6 7.5 6 11a6 6 0 11-12 0c0-3.5 3-7 6-11z"/>',
            'evac'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
            'sos'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
            'medical'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
            'supplies' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
            'other'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ];
    }

    /**
     * SVG inner content (path elements) for each need type
     */
    public static function needTypeIcons(): array
    {
        return [
            'food'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 002-2V2M7 2v20M21 15V2a5 5 0 00-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>',
            'water'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c3 4 6 7.5 6 11a6 6 0 11-12 0c0-3.5 3-7 6-11z"/>',
            'medicine'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
            'shelter'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
            'evacuation' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>',
            'clothing'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h6a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>',
            'other'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ];
    }

    /**
     * Chip color classes per scenario — bg + text pair
     */
    public static function scenarioChipColor(string $key): string
    {
        return match ($key) {
            'flood'    => 'bg-sky-50 text-sky-700',
            'evac'     => 'bg-orange-50 text-orange-700',
            'sos'      => 'bg-red-50 text-red-700',
            'medical'  => 'bg-rose-50 text-rose-700',
            'supplies' => 'bg-amber-50 text-amber-700',
            default    => 'bg-slate-100 text-slate-600',
        };
    }

    /**
     * Chip color classes per need type — bg + text pair
     */
    public static function needTypeChipColor(string $key): string
    {
        return match ($key) {
            'food'       => 'bg-amber-50 text-amber-700',
            'water'      => 'bg-sky-50 text-sky-700',
            'medicine'   => 'bg-rose-50 text-rose-700',
            'shelter'    => 'bg-emerald-50 text-emerald-700',
            'evacuation' => 'bg-indigo-50 text-indigo-700',
            'clothing'   => 'bg-violet-50 text-violet-700',
            default      => 'bg-slate-100 text-slate-600',
        };
    }

    /**
     * Text color class per scenario — for bare icons
     */
    public static function scenarioIconColor(string $key): string
    {
        return match ($key) {
            'flood'    => 'text-sky-600',
            'evac'     => 'text-orange-600',
            'sos'      => 'text-red-600',
            'medical'  => 'text-rose-600',
            'supplies' => 'text-amber-600',
            default    => 'text-slate-500',
        };
    }

    /**
     * Text color class per need type — for bare icons
     */
    public static function needTypeIconColor(string $key): string
    {
        return match ($key) {
            'food'       => 'text-amber-600',
            'water'      => 'text-sky-600',
            'medicine'   => 'text-rose-600',
            'shelter'    => 'text-emerald-600',
            'evacuation' => 'text-indigo-600',
            'clothing'   => 'text-violet-600',
            default      => 'text-slate-500',
        };
    }

    public static function scenarioIcon(?string $key, string $class = 'w-5 h-5'): string
    {
        $icons = self::scenarioIcons();
        return '<svg class="' . $class . '" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . ($icons[$key] ?? $icons['other']) . '</svg>';
    }

    public static function needTypeIcon(?string $key, string $class = 'w-4 h-4'): string
    {
        $icons = self::needTypeIcons();
        return '<svg class="' . $class . '" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . ($icons[$key] ?? $icons['other']) . '</svg>';
    }

    public static function urgencyLabels(): array
    {
        return [
            'low' => 'ต่ำ',
            'normal' => 'ปกติ',
            'high' => 'สูง',
            'critical' => 'วิกฤต',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'pending' => 'รอรับเรื่อง',
            'accepted' => 'รับเรื่องแล้ว',
            'in_progress' => 'กำลังช่วยเหลือ',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก',
        ];
    }

    /**
     * Colored dot matching map marker colors — for status badges
     */
    public static function statusDot(string $status): string
    {
        $colors = [
            'pending' => '#dc2626',
            'accepted' => '#2563eb',
            'in_progress' => '#d97706',
            'completed' => '#059669',
            'cancelled' => '#64748b',
        ];
        $c = $colors[$status] ?? '#64748b';
        return '<span class="inline-block w-1.5 h-1.5 rounded-full align-middle" style="background:' . $c . '"></span>';
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-amber-100 text-amber-800',
            'accepted' => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-indigo-100 text-indigo-800',
            'completed' => 'bg-emerald-100 text-emerald-800',
            'cancelled' => 'bg-slate-100 text-slate-600',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    public static function urgencyChipColor(string $u): string
    {
        return match ($u) {
            'low' => 'bg-slate-100 text-slate-600',
            'normal' => 'bg-sky-50 text-sky-700',
            'high' => 'bg-orange-50 text-orange-700',
            'critical' => 'bg-red-50 text-red-700',
            default => 'bg-slate-100 text-slate-600',
        };
    }

    public static function urgencyColor(string $u): string
    {
        return match ($u) {
            'low' => 'bg-slate-100 text-slate-600',
            'normal' => 'bg-sky-100 text-sky-800',
            'high' => 'bg-orange-100 text-orange-800',
            'critical' => 'bg-red-100 text-red-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    public static function timeAgo(string $datetime): string
    {
        $ts = strtotime($datetime);
        $diff = time() - $ts;
        if ($diff < 60) return 'เมื่อสักครู่';
        if ($diff < 3600) return floor($diff / 60) . ' นาทีที่แล้ว';
        if ($diff < 86400) return floor($diff / 3600) . ' ชั่วโมงที่แล้ว';
        if ($diff < 604800) return floor($diff / 86400) . ' วันที่แล้ว';
        return date('d/m/Y H:i', $ts);
    }

    public static function formatPhone(?string $phone): string
    {
        if (!$phone) return '-';
        $p = preg_replace('/\D/', '', $phone);
        if (strlen($p) === 10) {
            return substr($p, 0, 3) . '-' . substr($p, 3, 3) . '-' . substr($p, 6);
        }
        return $phone;
    }

    public static function photoUrl(?string $path): ?string
    {
        if (!$path) return null;
        return BASE_PATH . '/' . ltrim($path, '/');
    }

    /**
     * Build pagination URLs preserving current query params
     */
    public static function pageUrl(int $page, array $params = []): string
    {
        $params['page'] = $page;
        return Helper::url('needs') . '?' . http_build_query($params);
    }

    public static function pagination(int $page, int $perPage, int $total): array
    {
        $pages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        return ['page' => $page, 'pages' => $pages, 'total' => $total, 'perPage' => $perPage];
    }

    public static function asset(string $path): string
    {
        return BASE_PATH . '/assets/' . ltrim($path, '/');
    }

    public static function url(string $path = ''): string
    {
        return BASE_PATH . '/' . ltrim($path, '/');
    }
}
