<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0F3D6E">
    <title><?= Helper::e($title ?? 'อาสาสมัคร') ?> | <?= Helper::e(Helper::appName()) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Kanit', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
                            400: '#60a5fa', 500: '#2563eb', 600: '#004c97', 700: '#003f7d',
                            800: '#003464', 900: '#002a50',
                        }
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { font-family: 'Kanit', sans-serif; background: #f8fafc; color: #0f172a; }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0); }
        /* Admin-style cards: thin slate border + subtle shadow on slate-50 bg */
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: .75rem; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
        /* Admin stat chips */
        .admin-stat {
            display: flex; align-items: baseline; gap: .4rem;
            background: #fff; border: 1px solid #e2e8f0; border-radius: .6rem;
            padding: .45rem .8rem;
        }
        .admin-stat .num { font-weight: 700; font-size: 1.05rem; line-height: 1.1; color: #004c97; }
        .admin-stat .lbl { font-size: .72rem; color: #64748b; white-space: nowrap; }
        .btn-primary {
            background: #004c97; color: #fff; font-weight: 500; border-radius: .5rem;
            padding: .55rem 1rem; transition: background .15s ease, box-shadow .15s ease;
        }
        .btn-primary:hover { background: #003f7d; box-shadow: 0 1px 2px rgba(15,23,42,.15); }
        .btn-primary:disabled { opacity: .65; cursor: wait; }
        .input-field {
            width: 100% !important; box-sizing: border-box !important; min-width: 0 !important;
            border: 2px solid #94a3b8 !important; border-radius: 12px !important;
            padding: 0.875rem 1rem !important; font-size: 1.125rem !important;
            line-height: 1.625 !important; background-color: #fff !important;
        }
        .input-field:focus { border-color: #2563EB !important; outline: none !important; }
        #toast-container { position: fixed; top: 1rem; left: 50%; transform: translateX(-50%); z-index: 3000; width: min(92vw, 28rem); pointer-events: none; }
        .toast { pointer-events: auto; display: flex; align-items: center; gap: .5rem; padding: .75rem 1rem; border-radius: .75rem; font-size: .875rem; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,.15); margin-bottom: .5rem; animation: toast-in .25s ease; }
        .toast.ok { background: #059669; color: #fff; }
        .toast.err { background: #dc2626; color: #fff; }
        .toast.info { background: #0369a1; color: #fff; }
        @keyframes toast-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        /* Same pulsing CTA style as the public "แจ้งเหตุ" button */
        .report-cta { animation: cta-glow 2.2s ease-out infinite; }
        @keyframes cta-glow {
            0% { box-shadow: 0 0 0 0 rgba(220,38,38,.45); }
            70% { box-shadow: 0 0 0 14px rgba(220,38,38,0); }
            100% { box-shadow: 0 0 0 0 rgba(220,38,38,0); }
        }
        /* Burger menu dropdown */
        #userMenu { display: none; }
        #userMenu.show { display: block; }
    </style>
</head>
<body class="text-slate-800 min-h-screen flex flex-col">
    <div id="toast-container"></div>

    <script>
        window.BASE_PATH = '<?= BASE_PATH ?>';
        window.csrfToken = '<?= Auth::csrfToken() ?>';
        window.Toast = {
            show(message, type = 'info', ms = 3500) {
                const c = document.getElementById('toast-container');
                const el = document.createElement('div');
                el.className = 'toast ' + type;
                el.textContent = message;
                c.appendChild(el);
                setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; setTimeout(() => el.remove(), 300); }, ms);
            }
        };
        window.staffAction = async function(url, fields, opts = {}) {
            const form = new FormData();
            form.append('csrf_token', window.csrfToken);
            Object.entries(fields || {}).forEach(([k, v]) => { if (v !== undefined && v !== null) form.append(k, v); });
            try {
                const res = await fetch(window.BASE_PATH + url, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: form
                });
                const json = await res.json();
                if (json.ok) {
                    window.Toast.show(json.message || 'สำเร็จ', 'ok');
                    if (opts.reload !== false) setTimeout(() => location.reload(), 700);
                } else {
                    window.Toast.show(json.message || 'ไม่สำเร็จ', 'err');
                    if (opts.reenable) opts.reenable();
                }
                return json;
            } catch (e) {
                window.Toast.show('เกิดข้อผิดพลาดเครือข่าย', 'err');
                if (opts.reenable) opts.reenable();
                return { ok: false };
            }
        };
    </script>

    <!-- Top bar — admin style: white bar with brand mark -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-[1001]">
        <div class="max-w-lg mx-auto px-4 h-14 flex items-center justify-between gap-3">
            <a href="<?= Helper::url('volunteer') ?>" class="flex items-center gap-2.5 min-w-0">
                <?php $vLogo = Helper::brandLogoUrl(); ?>
                <?php if ($vLogo): ?>
                    <img src="<?= Helper::e($vLogo) ?>" alt="" class="w-9 h-9 rounded-lg object-contain bg-white border border-slate-200 shrink-0">
                <?php else: ?>
                    <div class="w-9 h-9 rounded-lg bg-brand-600 text-white flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-water text-lg"></i>
                    </div>
                <?php endif; ?>
                <div class="min-w-0">
                    <p class="font-semibold text-slate-800 leading-tight truncate">จิตอาสา</p>
                    <p class="text-xs text-slate-500 truncate"><?= Helper::e(Helper::appName()) ?></p>
                </div>
            </a>
            <div class="flex items-center gap-3 text-sm shrink-0 relative">
                <span class="truncate max-w-[8rem] text-slate-600 hidden sm:inline"><?= Helper::e($user['name'] ?? '') ?></span>
                <button type="button" id="userMenuBtn" class="p-2 rounded-lg hover:bg-slate-100 text-slate-600">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div id="userMenu" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-2 z-[1002]">
                    <div class="px-4 py-2 border-b border-slate-100">
                        <p class="font-medium text-slate-800 truncate"><?= Helper::e($user['name'] ?? '') ?></p>
                    </div>
                    <a href="<?= Helper::url('password') ?>" class="block px-4 py-2 text-slate-700 hover:bg-slate-50">เปลี่ยนรหัสผ่าน</a>
                    <a href="<?= Helper::url('logout') ?>" class="block px-4 py-2 text-red-600 hover:bg-slate-50">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 w-full max-w-lg mx-auto px-4 py-4 pb-28">
        <?= $content ?>
    </main>

    <!-- Bottom nav — same style as public, center CTA = เปิดเคส (red pulsing) -->
    <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-[1002] safe-bottom">
        <div class="flex items-end justify-around h-[4.5rem] max-w-lg mx-auto px-1">
            <a href="<?= Helper::url('volunteer') ?>" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs pt-2 pb-2.5 text-slate-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>เคส</span>
            </a>

            <!-- Center CTA: เปิดเคส — same red pulsing style as แจ้งเหตุ -->
            <div class="flex-1 flex flex-col items-center justify-end pb-1.5">
                <a href="<?= Helper::url('volunteer/new') ?>" class="report-cta w-14 h-14 -mt-9 rounded-full bg-red-600 hover:bg-red-700 active:bg-red-800 flex items-center justify-center ring-4 ring-white">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </a>
                <span class="text-xs font-semibold text-red-600 mt-1">เปิดเคส</span>
            </div>

            <a href="<?= Helper::url('map') ?>" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs pt-2 pb-2.5 text-slate-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>แผนที่</span>
            </a>
        </div>
    </nav>

    <script>
        (function() {
            const btn = document.getElementById('userMenuBtn');
            const menu = document.getElementById('userMenu');
            if (btn && menu) {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    menu.classList.toggle('show');
                });
                document.addEventListener('click', (e) => {
                    if (!menu.contains(e.target) && e.target !== btn) {
                        menu.classList.remove('show');
                    }
                });
            }
        })();
    </script>

    <?php require APP_PATH . '/Views/partials/lightbox.php'; ?>
</body>
</html>
