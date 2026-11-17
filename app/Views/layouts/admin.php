<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0F172A">
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
                            50: '#EFF6FF',
                            100: '#DBEAFE',
                            200: '#BFDBFE',
                            300: '#93C5FD',
                            400: '#60A5FA',
                            500: '#2563EB',
                            600: '#0F3D6E',
                            700: '#0A2D52',
                            800: '#07203A',
                            900: '#04101E',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { font-family: 'Kanit', sans-serif; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.85rem;
            color: #cbd5e1;
            border-radius: 0.65rem;
            font-size: 0.95rem;
            font-weight: 500;
            transition: background .15s ease, color .15s ease;
        }
        .sidebar-link:hover { color: #fff; background: #1e293b; }
        .sidebar-link.active { background: #0F3D6E; color: #fff; }
        .sidebar-label {
            padding: 1rem 0.85rem 0.35rem;
            font-size: 0.7rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
        }
        .input-field {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            background: #fff;
        }
        .input-field:focus {
            outline: none;
            border-color: #2563EB;
            box-shadow: 0 0 0 3px rgba(37,99,235,.2);
        }
        .btn-primary {
            background: #0F3D6E;
            color: #fff;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.55rem 1.1rem;
            transition: background .15s ease;
        }
        .btn-primary:hover { background: #0A2D52; }
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
        @media (min-width: 1024px) {
            #sidebar { transform: none; }
            #sidebarOverlay { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">
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
        $adminNav = 'dashboard';
        if (str_starts_with($reqPath, '/admin/requests')) $adminNav = 'requests';
        elseif (str_starts_with($reqPath, '/admin/users')) $adminNav = 'users';
        elseif (str_starts_with($reqPath, '/admin/areas')) $adminNav = 'areas';
        $roleLabels = ['admin' => 'ผู้ดูแลระบบ', 'volunteer' => 'อาสาสมัคร', 'viewer' => 'ผู้ดู'];
        $roleLabel = $roleLabels[$user['role'] ?? ''] ?? ($user['role'] ?? '');
    ?>
    <!-- Sidebar Overlay (mobile) -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-30 lg:hidden"></div>
    
    <!-- Sidebar: fixed on desktop, drawer on mobile -->
    <aside id="sidebar" class="bg-slate-900 text-white flex flex-col">
        <!-- Logo/Brand -->
        <div class="p-5 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-brand-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h1 class="font-bold text-white">DSC Admin</h1>
                    <p class="text-xs text-slate-400">Deep South Care Lite</p>
                </div>
            </div>
        </div>
        
        <!-- Navigation — admin sections only -->
        <nav class="flex-1 px-3 pb-4 overflow-y-auto">
            <p class="sidebar-label">งานประจำ</p>
            <div class="space-y-1">
                <a href="<?= Helper::url('admin') ?>" class="sidebar-link <?= $adminNav === 'dashboard' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    แดชบอร์ด
                </a>
                <a href="<?= Helper::url('admin/requests') ?>" class="sidebar-link <?= $adminNav === 'requests' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    คำขอความช่วยเหลือ
                </a>
            </div>
            <?php if (!empty($isAdmin)): ?>
            <p class="sidebar-label">ตั้งค่าระบบ</p>
            <div class="space-y-1">
                <a href="<?= Helper::url('admin/users') ?>" class="sidebar-link <?= $adminNav === 'users' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    ผู้ใช้
                </a>
                <a href="<?= Helper::url('admin/areas') ?>" class="sidebar-link <?= $adminNav === 'areas' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    พื้นที่
                </a>
            </div>
            <?php endif; ?>
        </nav>
        
        <!-- User Info & Logout -->
        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate"><?= Helper::e($user['name'] ?? 'Admin') ?></p>
                    <p class="text-xs text-slate-400"><?= Helper::e($roleLabel) ?></p>
                </div>
            </div>
            <a href="<?= Helper::url() ?>" class="flex items-center gap-3 px-4 py-2.5 text-slate-400 hover:text-white hover:bg-slate-800 transition rounded-lg text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                เปิดเว็บประชาชน
            </a>
            <a href="<?= Helper::url('logout') ?>" class="flex items-center gap-3 px-4 py-2.5 text-slate-300 hover:text-white hover:bg-slate-800 transition rounded-lg text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                ออกจากระบบ
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <div class="lg:ml-64 min-h-screen flex flex-col">
        <header class="bg-white border-b border-slate-200 px-4 lg:px-6 py-3 flex items-center gap-3 sticky top-0 z-20">
            <button id="menuToggle" type="button" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-slate-100" aria-label="เปิดเมนู">
                <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="min-w-0">
                <p class="text-xs text-slate-400">ระบบจัดการ</p>
                <h1 class="font-semibold text-slate-800 truncate"><?= Helper::e($title ?? 'แดชบอร์ด') ?></h1>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="flex-1 p-4 lg:p-6">
            <?= $content ?>
        </main>
    </div>
    
    <script>
        // Sidebar toggle for mobile
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('menuToggle');
        
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        });
        
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
        
        // Close sidebar when clicking a link on mobile
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
