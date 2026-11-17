<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#f8fafc">
    <title><?= Helper::e($title ?? 'Admin') ?> | Deep South Care Lite</title>
    
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
            gap: 1rem;
            height: calc(100dvh - 2rem);
            min-height: 480px;
        }
        .command-cases,
        .command-map-panel { min-height: 0; display: flex; flex-direction: column; }
        .command-case-list { overflow-y: auto; overscroll-behavior: contain; }
        .command-map { flex: 1; min-height: 0; background: #f1f5f9; }
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
        if (str_starts_with($reqPath, '/admin/volunteers')) $adminNav = 'volunteers';
        elseif (str_starts_with($reqPath, '/admin/organizations')) $adminNav = 'organizations';
        $roleLabels = ['admin' => 'ผู้ดูแลระบบ', 'volunteer' => 'อาสาสมัคร', 'viewer' => 'ผู้ดู'];
        $roleLabel = $roleLabels[$user['role'] ?? ''] ?? ($user['role'] ?? '');
    ?>
    <!-- Sidebar Overlay (mobile) -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/40 z-30 lg:hidden" aria-hidden="true"></div>
    <button id="menuToggle" type="button" class="fixed bottom-4 left-4 z-20 lg:hidden inline-flex items-center justify-center w-11 h-11 rounded-lg border border-slate-200 bg-white text-slate-700 shadow-md" aria-label="เปิดเมนู" aria-expanded="false" aria-controls="sidebar">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    
    <!-- Sidebar: fixed on desktop, drawer on mobile -->
    <aside id="sidebar" class="admin-sidebar flex flex-col">
        <!-- Logo/Brand -->
        <div class="sidebar-brand p-4 border-b border-slate-200 flex items-center gap-3">
            <div class="sidebar-brand-logo w-9 h-9 rounded-lg bg-brand-600 text-white flex items-center justify-center shrink-0 font-semibold text-sm">DR</div>
            <div class="sidebar-brand-copy min-w-0">
                <h1 class="font-semibold text-slate-800 leading-tight">Relief Admin</h1>
                <p class="text-xs text-slate-500 truncate">Disaster Relief System</p>
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
                <?php if (!empty($isAdmin)): ?>
                <a href="<?= Helper::url('admin/volunteers') ?>" title="อาสาสมัคร" class="sidebar-link <?= $adminNav === 'volunteers' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m6-10a4 4 0 100-8 4 4 0 000 8zm14 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                    <span class="sidebar-link-label">อาสาสมัคร</span>
                </a>
                <a href="<?= Helper::url('admin/organizations') ?>" title="องค์กรเครือข่าย" class="sidebar-link <?= $adminNav === 'organizations' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/></svg>
                    <span class="sidebar-link-label">องค์กรเครือข่าย</span>
                </a>
                <?php endif; ?>
            </div>
        </nav>
        
        <!-- User Info & Logout -->
        <div class="p-3 border-t border-slate-200">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="sidebar-user-copy flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-800 truncate"><?= Helper::e($user['name'] ?? 'Admin') ?></p>
                    <p class="text-xs text-slate-500"><?= Helper::e($roleLabel) ?></p>
                </div>
            </div>
            <a href="<?= Helper::url() ?>" title="เปิดเว็บประชาชน" class="sidebar-footer-link flex items-center gap-3 px-3 py-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition rounded-lg text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span class="sidebar-footer-label">เปิดเว็บประชาชน</span>
            </a>
            <a href="<?= Helper::url('logout') ?>" title="ออกจากระบบ" class="sidebar-footer-link flex items-center gap-3 px-3 py-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition rounded-lg text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span class="sidebar-footer-label">ออกจากระบบ</span>
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <div id="mainContent" class="min-h-screen flex flex-col">
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
    </script>
</body>
</html>
