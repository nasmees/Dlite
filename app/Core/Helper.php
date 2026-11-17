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
