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

    /**
     * Operating mode: 'disaster' (flood relief, default) or 'relief' (general relief work)
     */
    public static function appMode(): string
    {
        $m = Setting::get('app_mode', 'disaster');
        return in_array($m, ['disaster', 'relief'], true) ? $m : 'disaster';
    }

    private const NEED_TYPES_DISASTER = [
        'food' => 'อาหาร',
        'water' => 'น้ำดื่ม',
        'medicine' => 'ยา / เวชภัณฑ์',
        'shelter' => 'ที่พัก / ที่อพยพ',
        'evacuation' => 'ช่วยอพยพ',
        'clothing' => 'เสื้อผ้า',
        'other' => 'อื่นๆ',
    ];

    private const NEED_TYPES_RELIEF = [
        'medical' => 'บริการสุขภาพ / พบแพทย์',
        'counseling' => 'ให้คำปรึกษา',
        'training' => 'ฝึกอาชีพ / เรียนรู้ทักษะ',
        'financial' => 'เงินช่วยเหลือ / สงเคราะห์',
        'supplies' => 'เสบียง / ของใช้จำเป็น',
        'other' => 'อื่นๆ',
    ];

    public static function needTypes(): array
    {
        return self::appMode() === 'relief' ? self::NEED_TYPES_RELIEF : self::NEED_TYPES_DISASTER;
    }

    /**
     * Display label for a stored need_type — falls back to the disaster set
     * so legacy records still render when the site runs in relief mode.
     */
    public static function needTypeLabel(?string $key): string
    {
        if ($key === null || $key === '') return 'อื่นๆ';
        $types = self::needTypes();
        if (isset($types[$key])) return $types[$key];
        if (isset(self::NEED_TYPES_DISASTER[$key])) return self::NEED_TYPES_DISASTER[$key];
        return $key;
    }

    private const SCENARIOS_DISASTER = [
        'flood' => 'น้ำท่วม / ถนนท่วม',
        'evac' => 'ต้องอพยพ / บ้านเสียหาย',
        'sos' => 'เหตุฉุกเฉิน SOS',
        'medical' => 'เจ็บป่วย / บาดเจ็บ',
        'supplies' => 'ขาดแคลนอาหาร / น้ำ',
        'other' => 'อื่นๆ',
    ];

    private const SCENARIOS_RELIEF = [
        'health' => 'ตรวจสุขภาพ / พบแพทย์',
        'counsel' => 'ต้องการคำปรึกษา',
        'training' => 'ฝึกอาชีพ / หางาน',
        'aid' => 'ขอเงินช่วยเหลือ / สงเคราะห์',
        'supplies' => 'ขาดแคลนเสบียง / ของใช้',
        'other' => 'เรื่องอื่นๆ',
    ];

    /**
     * Scenario keys used by the public report form's "เกิดอะไรขึ้น?" cards
     */
    public static function scenarioLabels(): array
    {
        return self::appMode() === 'relief' ? self::SCENARIOS_RELIEF : self::SCENARIOS_DISASTER;
    }

    /**
     * Display label for a stored scenario — falls back to the disaster set
     * so legacy records still render when the site runs in relief mode.
     */
    public static function scenarioLabel(?string $key): string
    {
        if ($key === null || $key === '') return '';
        $labels = self::scenarioLabels();
        if (isset($labels[$key])) return $labels[$key];
        if (isset(self::SCENARIOS_DISASTER[$key])) return self::SCENARIOS_DISASTER[$key];
        return $key;
    }

    /**
     * Scenario cards for the report form — label/sub/types/urgency/sos per mode
     */
    public static function scenarioCards(): array
    {
        if (self::appMode() === 'relief') {
            return [
                ['key' => 'health',    'label' => 'ตรวจสุขภาพ / พบแพทย์',  'sub' => 'ลงทะเบียนรับบริการสุขภาพ',       'types' => ['medical', 'counseling'], 'urgency' => 'normal',   'sos' => false],
                ['key' => 'counsel',   'label' => 'ต้องการคำปรึกษา',         'sub' => 'จิตใจ กฎหมาย การเงิน',            'types' => ['counseling'],              'urgency' => 'normal',   'sos' => false],
                ['key' => 'training',  'label' => 'ฝึกอาชีพ / หางาน',        'sub' => 'เรียนรู้ทักษะเพื่อมีรายได้',       'types' => ['training'],                'urgency' => 'normal',   'sos' => false],
                ['key' => 'aid',       'label' => 'ขอเงินช่วยเหลือ',          'sub' => 'สงเคราะห์ / เยียวยา',              'types' => ['financial'],               'urgency' => 'high',     'sos' => false],
                ['key' => 'supplies',  'label' => 'ขาดแคลนเสบียง / ของใช้',   'sub' => 'อาหาร น้ำ ของใช้จำเป็น',         'types' => ['supplies'],                'urgency' => 'normal',   'sos' => false],
                ['key' => 'other',     'label' => 'เรื่องอื่นๆ',              'sub' => 'เล่าเหตุการณ์โดยย่อ',             'types' => ['other'],                   'urgency' => 'normal',   'sos' => false],
            ];
        }
        return [
            ['key' => 'flood',    'label' => 'น้ำท่วม / ถนนท่วม',   'sub' => 'ขออาหาร น้ำ ที่พัก',        'types' => ['food','water','shelter'], 'urgency' => 'high',     'sos' => false],
            ['key' => 'evac',     'label' => 'ต้องอพยพ / บ้านเสียหาย','sub' => 'เร่งด่วน ต้องการที่พัก',      'types' => ['shelter','evacuation'],   'urgency' => 'critical', 'sos' => false],
            ['key' => 'sos',      'label' => 'เหตุฉุกเฉิน SOS',       'sub' => 'อันตรายถึงชีวิต ต้องการทันที', 'types' => ['medicine','evacuation'],  'urgency' => 'critical', 'sos' => true],
            ['key' => 'medical',  'label' => 'เจ็บป่วย / ได้รับบาดเจ็บ','sub' => 'ต้องการยาและการรักษา',      'types' => ['medicine'],               'urgency' => 'high',     'sos' => false],
            ['key' => 'supplies', 'label' => 'ขาดแคลนอาหาร / น้ำ',    'sub' => 'แจ้งจุดแจกจ่าย',             'types' => ['food','water'],           'urgency' => 'normal',   'sos' => false],
            ['key' => 'other',    'label' => 'ความเสียหายอื่นๆ',       'sub' => 'เล่าเหตุการณ์โดยย่อ',         'types' => ['other'],                  'urgency' => 'normal',   'sos' => false],
        ];
    }

    /**
     * Important-place (POI) categories: label + emoji + marker color
     */
    public static function poiCategories(): array
    {
        return [
            'shelter'     => ['label' => 'จุดพักพิง',     'emoji' => '⛺', 'color' => '#059669'],
            'evacuation'  => ['label' => 'จุดอพยพ',       'emoji' => '🚩', 'color' => '#EA580C'],
            'supply'      => ['label' => 'จุดแจกจ่าย',    'emoji' => '📦', 'color' => '#D97706'],
            'medical'     => ['label' => 'จุดพยาบาล',     'emoji' => '🏥', 'color' => '#E11D48'],
            'meeting'     => ['label' => 'จุดนัดพบ',      'emoji' => '🤝', 'color' => '#2563EB'],
            'other'       => ['label' => 'จุดสำคัญอื่นๆ', 'emoji' => '📌', 'color' => '#64748B'],
        ];
    }

    public static function poiCategory(string $key): array
    {
        $cats = self::poiCategories();
        return $cats[$key] ?? $cats['other'];
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
            // relief-mode scenarios
            'health'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'counsel'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
            'training' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>',
            'aid'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
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
            // relief-mode need types
            'medical'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
            'counseling' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
            'training'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>',
            'financial'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
            'supplies'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
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
            'health'   => 'bg-emerald-50 text-emerald-700',
            'counsel'  => 'bg-violet-50 text-violet-700',
            'training' => 'bg-sky-50 text-sky-700',
            'aid'      => 'bg-amber-50 text-amber-700',
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
            'medical'    => 'bg-rose-50 text-rose-700',
            'counseling' => 'bg-violet-50 text-violet-700',
            'training'   => 'bg-sky-50 text-sky-700',
            'financial'  => 'bg-amber-50 text-amber-700',
            'supplies'   => 'bg-orange-50 text-orange-700',
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
            'health'   => 'text-emerald-600',
            'counsel'  => 'text-violet-600',
            'training' => 'text-sky-600',
            'aid'      => 'text-amber-600',
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
            'medical'    => 'text-rose-600',
            'counseling' => 'text-violet-600',
            'training'   => 'text-sky-600',
            'financial'  => 'text-amber-600',
            'supplies'   => 'text-orange-600',
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

    /**
     * Primary call-to-action label — disaster reports an incident, relief requests a service
     */
    public static function reportCta(): string
    {
        return self::appMode() === 'relief' ? 'ส่งคำขอ' : 'แจ้งเหตุ';
    }

    public static function roleLabel(string $role): string
    {
        return [
            'admin' => 'ผู้ดูแลระบบ',
            'volunteer' => 'อาสาสมัคร',
            'viewer' => 'ผู้ดู',
        ][$role] ?? $role;
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

    public static function statusBgColor(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-amber-100 text-amber-600',
            'accepted' => 'bg-blue-100 text-blue-600',
            'in_progress' => 'bg-indigo-100 text-indigo-600',
            'completed' => 'bg-emerald-100 text-emerald-600',
            'cancelled' => 'bg-slate-100 text-slate-500',
            default => 'bg-slate-100 text-slate-500',
        };
    }

    public static function statusIcon(string $status): string
    {
        $icons = [
            'pending' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'accepted' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'in_progress' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
            'completed' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
            'cancelled' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
        ];
        return $icons[$status] ?? $icons['pending'];
    }

    public static function urgencyIcon(string $u): string
    {
        $icons = [
            'low' => '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>',
            'normal' => '',
            'high' => '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
            'critical' => '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0zM12 9v4m0 4h.01"/></svg>',
        ];
        return $icons[$u] ?? '';
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

    /**
     * White-label branding — app name + logo from admin settings
     */
    public static function appName(): string
    {
        return Setting::get('app_name') ?: APP_NAME;
    }

    public static function brandLogo(): ?string
    {
        $p = Setting::get('brand_logo');
        return $p ?: null;
    }

    public static function brandLogoUrl(): ?string
    {
        $p = self::brandLogo();
        return $p ? BASE_PATH . '/' . ltrim($p, '/') : null;
    }
}
