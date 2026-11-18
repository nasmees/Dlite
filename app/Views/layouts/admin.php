<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#f8fafc">
    <title><?= Helper::e($title ?? 'Admin') ?> | <?= Helper::e(Helper::appName()) ?></title>
    
    <!-- Kanit Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Kanit', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#2563eb',
                            600: '#004c97',
                            700: '#003f7d',
                            800: '#003464',
                            900: '#002a50',
                        }
                    }
                }
            }
        }
    </script>
    <?php if (!empty($includeMapAssets)): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body {
            font-family: 'Kanit', sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }
        .admin-content > :first-child { width: 100%; max-width: 1600px; margin-inline: auto; }
        .admin-sidebar {
            width: 16rem;
            background: #fff;
            color: #0f172a;
            border-right: 1px solid #e2e8f0;
            transition: width .2s ease, transform .2s ease;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: .65rem .75rem;
            color: #475569;
            border-radius: .5rem;
            font-size: .9rem;
            font-weight: 500;
            transition: background .15s ease, color .15s ease;
        }
        .sidebar-link:hover { color: #0f172a; background: #f1f5f9; }
        .sidebar-link.active { color: #004c97; background: #eff6ff; }
        .sidebar-link.active svg { color: #004c97; }
        .sidebar-label {
            padding: 1.25rem .75rem .45rem;
            font-size: .72rem;
            letter-spacing: .04em;
            color: #64748b;
            font-weight: 600;
        }
        .input-field {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: .5rem;
            padding: .55rem .75rem;
            font-size: 0.875rem;
            background: #fff;
            color: #0f172a;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .input-field:focus {
            outline: none;
            border-color: #004c97;
            box-shadow: 0 0 0 3px rgba(0,76,151,.12);
        }
        .btn-primary {
            background: #004c97;
            color: #fff;
            font-weight: 500;
            border-radius: .5rem;
            padding: .55rem 1rem;
            transition: background .15s ease, box-shadow .15s ease;
        }
        .btn-primary:hover { background: #003f7d; box-shadow: 0 1px 2px rgba(15,23,42,.15); }
        .btn-primary:disabled { opacity: .65; cursor: wait; }
        .admin-content .bg-white.rounded-xl.border {
            border-color: #e2e8f0;
            border-radius: .75rem;
            box-shadow: 0 1px 2px rgba(15,23,42,.04);
        }
        .admin-content table thead tr {
            background: #f8fafc;
        }
        .admin-content table th {
            color: #475569;
            font-weight: 500;
            white-space: nowrap;
        }
        .admin-map {
            height: min(68vh, 760px);
            min-height: 360px;
            background: #f1f5f9;
        }
        .command-center {
            display: grid;
            grid-template-columns: minmax(350px, 390px) minmax(0, 1fr);
            grid-template-rows: auto minmax(0, 1fr);
            gap: 1rem;
            height: calc(100dvh - 2rem);
            min-height: 480px;
        }
        .admin-stats { grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: .5rem; }
        .admin-stat {
            display: flex; align-items: baseline; gap: .4rem;
            background: #fff; border: 1px solid #e2e8f0; border-radius: .6rem;
            padding: .45rem .8rem;
        }
        .admin-stat .num { font-weight: 700; font-size: 1.05rem; line-height: 1.1; color: #004c97; }
        .admin-stat .lbl { font-size: .72rem; color: #64748b; white-space: nowrap; }
        .map-filter-btn {
            padding: .3rem .7rem; border-radius: 999px; font-size: .75rem; font-weight: 500;
            background: #f1f5f9; color: #475569; transition: background .15s ease, color .15s ease;
        }
        .map-filter-btn:hover { background: #e2e8f0; }
        .map-filter-btn.active { background: #004c97; color: #fff; }
        .command-cases,
        .command-map-panel { min-height: 0; display: flex; flex-direction: column; }
        .command-case-list { overflow-y: auto; overscroll-behavior: contain; }
        .command-map { height: 100%; min-height: 0; background: #f1f5f9; }
        .map-legend-dot { width: .55rem; height: .55rem; border-radius: 999px; display: inline-block; }
        .admin-content a:focus-visible,
        .admin-content button:focus-visible,
        #sidebar a:focus-visible,
        #menuToggle:focus-visible,
        #sidebarCollapse:focus-visible {
            outline: 2px solid #004c97;
            outline-offset: 2px;
        }
        #sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: 16rem;
            transform: translateX(-100%);
            transition: transform 0.25s ease;
            z-index: 40;
        }
        #sidebar.open { transform: translateX(0); }
        #sidebarOverlay { display: none; }
        #sidebarOverlay.show { display: block; }
        #mainContent { margin-left: 16rem; transition: margin-left .2s ease; }
        body.sidebar-collapsed #sidebar { width: 4.5rem; }
        body.sidebar-collapsed #mainContent { margin-left: 4.5rem; }
        body.sidebar-collapsed .sidebar-brand-copy,
        body.sidebar-collapsed .sidebar-link-label,
        body.sidebar-collapsed .sidebar-label,
        body.sidebar-collapsed .sidebar-user-copy,
        body.sidebar-collapsed .sidebar-footer-label { display: none; }
        body.sidebar-collapsed .sidebar-brand-logo { display: none; }
        body.sidebar-collapsed .sidebar-brand,
        body.sidebar-collapsed .sidebar-link,
        body.sidebar-collapsed .sidebar-footer-link { justify-content: center; }
        body.sidebar-collapsed .sidebar-link { padding-left: .65rem; padding-right: .65rem; }
        body.sidebar-collapsed #sidebar > div:last-child > div:first-child { justify-content: center; }
        body.sidebar-collapsed #sidebarCollapse { margin: 0 auto; }
        @media (min-width: 1024px) {
            #sidebar { transform: none; }
            #sidebarOverlay { display: none !important; }
        }
        @media (max-width: 1023px) {
            #mainContent { margin-left: 0; }
            .command-center { height: auto; min-height: 0; grid-template-columns: minmax(0, 1fr); }
            .command-cases { min-height: 330px; max-height: 55vh; }
            .command-map-panel { height: 65vh; min-height: 420px; }
            .command-map { min-height: 0; }
        }
    </style>
</head>
<body class="admin-shell min-h-screen">
    <script>
        window.BASE_PATH = '<?= BASE_PATH ?>';
        window.csrfToken = '<?= Auth::csrfToken() ?>';
        window.Toast = {
            show(message, type = 'info', ms = 3500) {
                let c = document.getElementById('toast-container');
                if (!c) {
                    c = document.createElement('div');
                    c.id = 'toast-container';
                    c.style.cssText = 'position:fixed;top:1rem;left:50%;transform:translateX(-50%);z-index:2000;width:min(92vw,28rem);pointer-events:none;';
                    document.body.appendChild(c);
                }
                const el = document.createElement('div');
                const colors = { ok: '#059669', err: '#dc2626', info: '#0369a1' };
                el.style.cssText = 'pointer-events:auto;display:flex;align-items:center;gap:.5rem;padding:.75rem 1rem;border-radius:.75rem;font-size:.875rem;font-weight:500;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,.15);margin-bottom:.5rem;background:' + (colors[type] || colors.info);
                el.textContent = message;
                c.appendChild(el);
                setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; setTimeout(() => el.remove(), 300); }, ms);
            }
        };
    </script>
    <?php
        $user = $user ?? Auth::user() ?? [];
        $isAdmin = $isAdmin ?? Auth::isAdmin();
        $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = rtrim((string)BASE_PATH, '/');
        if ($base !== '' && str_starts_with($reqPath, $base)) {
            $reqPath = substr($reqPath, strlen($base)) ?: '/';
        }
        $adminNav = 'command';
        if (str_starts_with($reqPath, '/admin/requests')) $adminNav = 'requests';
        elseif (str_starts_with($reqPath, '/admin/volunteers')) $adminNav = 'volunteers';
        elseif (str_starts_with($reqPath, '/admin/organizations')) $adminNav = 'organizations';
        elseif (str_starts_with($reqPath, '/admin/areas')) $adminNav = 'areas';
        elseif (str_starts_with($reqPath, '/admin/announcements')) $adminNav = 'announcements';
        elseif (str_starts_with($reqPath, '/admin/users')) $adminNav = 'users';
        elseif (str_starts_with($reqPath, '/admin/stats')) $adminNav = 'stats';
        elseif (str_starts_with($reqPath, '/admin/settings')) $adminNav = 'settings';
        $roleLabels = ['admin' => 'ผู้ดูแลระบบ', 'volunteer' => 'อาสาสมัคร', 'viewer' => 'ผู้ดู'];
        $roleLabel = $roleLabels[$user['role'] ?? ''] ?? ($user['role'] ?? '');
        $brandLogo = Helper::brandLogoUrl();
    ?>
    <!-- Top bar (mobile) -->
    <header class="fixed top-0 inset-x-0 z-30 lg:hidden bg-white border-b border-slate-200">
        <div class="flex items-center gap-1 h-14 px-2">
            <button id="menuToggle" type="button" class="inline-flex items-center justify-center w-11 h-11 rounded-lg text-slate-700 hover:bg-slate-100" aria-label="เปิดเมนู" aria-expanded="false" aria-controls="sidebar">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex items-center gap-2.5 min-w-0 px-1">
                <?php if ($brandLogo): ?>
                    <img src="<?= Helper::e($brandLogo) ?>" alt="" class="w-8 h-8 rounded-lg object-contain bg-white border border-slate-200 shrink-0">
                <?php else: ?>
                    <div class="w-8 h-8 rounded-lg bg-brand-600 text-white flex items-center justify-center shrink-0 font-semibold text-xs">DR</div>
                <?php endif; ?>
                <div class="min-w-0">
                    <p class="font-semibold text-slate-800 leading-tight truncate"><?= Helper::e(Helper::appName()) ?></p>
                    <p class="text-xs text-slate-500 truncate">Admin Panel</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar Overlay (mobile) -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/40 z-30 lg:hidden" aria-hidden="true"></div>
    
    <!-- Sidebar: fixed on desktop, drawer on mobile -->
    <aside id="sidebar" class="admin-sidebar flex flex-col">
        <!-- Logo/Brand -->
        <div class="sidebar-brand p-4 border-b border-slate-200 flex items-center gap-3">
            <?php if ($brandLogo): ?>
                <img src="<?= Helper::e($brandLogo) ?>" alt="" class="w-9 h-9 rounded-lg object-contain bg-white border border-slate-200 shrink-0">
            <?php else: ?>
                <div class="sidebar-brand-logo w-9 h-9 rounded-lg bg-brand-600 text-white flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-water text-lg"></i>
                </div>
            <?php endif; ?>
            <div class="sidebar-brand-copy min-w-0">
                <h1 class="font-semibold text-slate-800 leading-tight truncate"><?= Helper::e(Helper::appName()) ?></h1>
                <p class="text-xs text-slate-500 truncate">Admin Panel</p>
            </div>
            <button id="sidebarCollapse" type="button" class="hidden lg:inline-flex ml-auto p-2 rounded-md hover:bg-slate-100" aria-label="ย่อหรือขยายเมนู" aria-expanded="true">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5h16M4 12h16M4 19h16M9 5v14"/></svg>
            </button>
        </div>
        
        <!-- Primary navigation -->
        <nav class="flex-1 px-3 pb-4 overflow-y-auto">
            <p class="sidebar-label">จัดการระบบ</p>
            <div class="space-y-1">
                <a href="<?= Helper::url('admin') ?>" title="ศูนย์บัญชาการ" class="sidebar-link <?= $adminNav === 'command' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 4.75A1.75 1.75 0 014.75 3h14.5A1.75 1.75 0 0121 4.75v14.5A1.75 1.75 0 0119.25 21H4.75A1.75 1.75 0 013 19.25V4.75zM9 3v18m6-18v18M3 9h6m0 6h-6m12-6h6m-6 6h6"/></svg>
                    <span class="sidebar-link-label">ศูนย์บัญชาการ</span>
                </a>
                <a href="<?= Helper::url('admin/requests') ?>" title="จัดการคำขอ" class="sidebar-link <?= $adminNav === 'requests' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="sidebar-link-label">จัดการคำขอ</span>
                </a>
                <a href="<?= Helper::url('admin/stats') ?>" title="สถิติ" class="sidebar-link <?= $adminNav === 'stats' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span class="sidebar-link-label">สถิติ</span>
                </a>
                <a href="<?= Helper::url('admin/announcements') ?>" title="ประกาศ" class="sidebar-link <?= $adminNav === 'announcements' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    <span class="sidebar-link-label">ประกาศ</span>
                </a>
                <?php if (!empty($isAdmin)): ?>
                <a href="<?= Helper::url('admin/users') ?>" title="ผู้ดูแลระบบ" class="sidebar-link <?= $adminNav === 'users' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span class="sidebar-link-label">ผู้ดูแลระบบ</span>
                </a>
                <a href="<?= Helper::url('admin/volunteers') ?>" title="อาสาสมัคร" class="sidebar-link <?= $adminNav === 'volunteers' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m6-10a4 4 0 100-8 4 4 0 000 8zm14 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                    <span class="sidebar-link-label">อาสาสมัคร</span>
                </a>
                <a href="<?= Helper::url('admin/organizations') ?>" title="องค์กรเครือข่าย" class="sidebar-link <?= $adminNav === 'organizations' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/></svg>
                    <span class="sidebar-link-label">องค์กรเครือข่าย</span>
                </a>
                <a href="<?= Helper::url('admin/areas') ?>" title="จัดการพื้นที่" class="sidebar-link <?= $adminNav === 'areas' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="sidebar-link-label">จัดการพื้นที่</span>
                </a>
                <a href="<?= Helper::url('admin/pois') ?>" title="จุดสำคัญ" class="sidebar-link <?= str_starts_with($reqPath, '/admin/pois') ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    <span class="sidebar-link-label">จุดสำคัญ</span>
                </a>
                <a href="<?= Helper::url('admin/settings') ?>" title="ตั้งค่าเว็บไซต์" class="sidebar-link <?= $adminNav === 'settings' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="sidebar-link-label">ตั้งค่าเว็บไซต์</span>
                </a>
                <?php endif; ?>
            </div>
        </nav>
        
        <!-- User Info & Logout -->
        <div class="p-3 border-t border-slate-200">
            <div class="relative">
                <button type="button" id="profileToggle" class="w-full flex items-center gap-3 rounded-lg px-1 py-1 hover:bg-slate-50 transition text-left">
                    <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div class="sidebar-user-copy flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate"><?= Helper::e($user['name'] ?? 'Admin') ?></p>
                        <p class="text-xs text-slate-500"><?= Helper::e($roleLabel) ?></p>
                    </div>
                    <svg class="sidebar-user-copy w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                </button>
                <div id="profileMenu" class="hidden absolute bottom-full left-0 mb-2 w-52 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden z-50">
                    <div class="px-3.5 py-2.5 border-b border-slate-100">
                        <p class="text-sm font-semibold text-slate-800 truncate"><?= Helper::e($user['name'] ?? 'Admin') ?></p>
                        <p class="text-xs text-slate-500"><?= Helper::e($roleLabel) ?></p>
                    </div>
                    <a href="<?= Helper::url('password') ?>" class="flex items-center gap-2.5 px-3.5 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        เปลี่ยนรหัสผ่าน
                    </a>
                    <a href="<?= Helper::url('logout') ?>" class="flex items-center gap-2.5 px-3.5 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        ออกจากระบบ
                    </a>
                </div>
            </div>
            <a href="<?= Helper::url() ?>" title="เปิดเว็บประชาชน" class="sidebar-footer-link flex items-center gap-3 px-3 py-2 mt-1 text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition rounded-lg text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span class="sidebar-footer-label">เปิดเว็บประชาชน</span>
            </a>
        </div>
    </aside>
    
    <?php if ($reqPath !== '/admin/requests/create'): ?>
    <!-- Quick-add case FAB (all admin pages) -->
    <a href="<?= Helper::url('admin/requests/create') ?>" aria-label="เพิ่มเคสใหม่"
       class="fixed bottom-5 right-5 z-[1001] inline-flex items-center gap-2 rounded-full bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-sm pl-4 pr-5 py-3 shadow-lg shadow-red-600/30 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        แจ้งเหตุ
    </a>
    <?php endif; ?>

    <!-- Main Content -->
    <div id="mainContent" class="min-h-screen flex flex-col pt-14 lg:pt-0">
        <!-- Page Content -->
        <main class="admin-content flex-1 <?= !empty($isCommandCenter) ? 'p-3 lg:p-4' : 'p-4 lg:p-6' ?>">
            <?= $content ?>
        </main>
    </div>
    
    <script>
        // Sidebar toggle for mobile
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('menuToggle');
        const collapse = document.getElementById('sidebarCollapse');
        
        function setMobileSidebarOpen(isOpen) {
            sidebar.classList.toggle('open', isOpen);
            overlay.classList.toggle('show', isOpen);
            toggle.setAttribute('aria-expanded', String(isOpen));
        }

        toggle.addEventListener('click', () => {
            setMobileSidebarOpen(!sidebar.classList.contains('open'));
        });
        
        collapse.addEventListener('click', () => {
            const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
            collapse.setAttribute('aria-expanded', String(!isCollapsed));
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth < 1024) {
                document.body.classList.remove('sidebar-collapsed');
                collapse.setAttribute('aria-expanded', 'true');
            }
        });

        overlay.addEventListener('click', () => setMobileSidebarOpen(false));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') setMobileSidebarOpen(false);
        });
        
        // Close sidebar when clicking a link on mobile
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    setMobileSidebarOpen(false);
                }
            });
        });

        // Profile dropdown (password / logout)
        const profileToggle = document.getElementById('profileToggle');
        const profileMenu = document.getElementById('profileMenu');
        if (profileToggle && profileMenu) {
            profileToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                profileMenu.classList.toggle('hidden');
            });
            document.addEventListener('click', (e) => {
                if (!profileMenu.classList.contains('hidden') && !profileMenu.contains(e.target) && e.target !== profileToggle) {
                    profileMenu.classList.add('hidden');
                }
            });
        }
    </script>

    <?php require APP_PATH . '/Views/partials/announcement_popup.php'; ?>
</body>
</html>
