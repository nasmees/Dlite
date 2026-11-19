<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0369a1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="description" content="<?= Helper::e(Helper::appName()) ?> - ระบบประสานงานช่วยเหลือผู้ประสบภัยน้ำท่วมชายแดนใต้">
    <title><?= Helper::e($title ?? Helper::appName()) ?> | <?= Helper::e(Helper::appName()) ?></title>
    
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
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { font-family: 'Kanit', sans-serif; }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0); }
        .map-full { height: calc(100vh - 4.5rem - env(safe-area-inset-bottom, 0px)); }
        body.has-staff .map-full { height: calc(100vh - 4.5rem - 2.5rem - env(safe-area-inset-bottom, 0px)); }
        .btn-primary {
            @apply bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white font-medium rounded-xl px-5 py-3 transition shadow-sm;
        }
        .input-field {
            width: 100% !important;
            box-sizing: border-box !important;
            min-width: 0 !important;
            border: 2px solid #94a3b8 !important;
            border-radius: 12px !important;
            padding: 0.875rem 1rem !important;
            font-size: 1.125rem !important;
            line-height: 1.625 !important;
            background-color: #fff !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
        }
        .input-field:focus {
            border-color: #2563EB !important;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.2) !important;
            outline: none !important;
        }
        .input-field::placeholder {
            color: #94a3b8 !important;
        }
        .card {
            @apply bg-white rounded-2xl shadow-md border-2 border-slate-200;
        }
        /* Offline indicator */
        #offline-bar {
            display: none;
            background: #f59e0b;
            color: #fff;
            text-align: center;
            padding: 0.35rem;
            font-size: 0.875rem;
        }
        body.offline #offline-bar { display: block; }
        /* Toast notifications */
        #toast-container { position: fixed; top: 1rem; left: 50%; transform: translateX(-50%); z-index: 100; width: min(92vw, 28rem); pointer-events: none; }
        .toast { pointer-events: auto; display: flex; align-items: center; gap: .5rem; padding: .75rem 1rem; border-radius: .75rem; font-size: .875rem; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,.15); margin-bottom: .5rem; animation: toast-in .25s ease; }
        .toast.ok { background: #059669; color: #fff; }
        .toast.err { background: #dc2626; color: #fff; }
        .toast.info { background: #0369a1; color: #fff; }
        @keyframes toast-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        .skeleton { background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: .5rem; }
        @keyframes shimmer { to { background-position: -200% 0; } }
        /* Elderly-friendly: never go below ~14px for secondary text */
        .text-2xs { font-size: 0.78rem; }
        /* Pulsing ring for "my report" pin on map */
        .pin-pulse { position: relative; }
        .pin-pulse::after {
            content: ''; position: absolute; inset: -10px; border-radius: 50%;
            border: 3px solid #2563EB; opacity: 0;
            animation: pin-ping 1.8s cubic-bezier(0,0,.2,1) infinite;
        }
        @keyframes pin-ping {
            0% { transform: scale(.6); opacity: .9; }
            80%, 100% { transform: scale(1.6); opacity: 0; }
        }
        /* Full-screen submit overlays */
        #submitOverlay { position: fixed; inset: 0; z-index: 1200; display: none; align-items: center; justify-content: center; flex-direction: column; gap: 1rem; }
        #submitOverlay.show { display: flex; }
        #submitOverlay.loading { background: rgba(15,23,42,.55); }
        #submitOverlay.success { background: #fff; }
        .spinner { width: 3rem; height: 3rem; border-radius: 50%; border: 4px solid #DBEAFE; border-top-color: #2563EB; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .check-pop { animation: check-pop .5s cubic-bezier(.2,1.4,.4,1) both; }
        @keyframes check-pop { from { transform: scale(.4); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        /* Report CTA — pulsing glow so it reads as the primary action */
        .report-cta { animation: cta-glow 2.2s ease-out infinite; }
        @keyframes cta-glow {
            0% { box-shadow: 0 0 0 0 rgba(220,38,38,.45); }
            70% { box-shadow: 0 0 0 14px rgba(220,38,38,0); }
            100% { box-shadow: 0 0 0 0 rgba(220,38,38,0); }
        }
        /* Bottom sheet drawer */
        #reportDrawer { position: fixed; inset: 0; z-index: 1100; display: none; }
        #reportDrawer.show { display: block; }
        #reportDrawer .backdrop { position: absolute; inset: 0; background: rgba(15,23,42,.5); }
        #reportDrawer .sheet {
            position: absolute; left: 0; right: 0; bottom: 0;
            height: 88vh; height: 88dvh;
            max-width: 32rem; margin: 0 auto;
            background: #fff; border-radius: 1.25rem 1.25rem 0 0;
            display: flex; flex-direction: column; overflow: hidden;
            animation: sheet-up .25s ease;
        }
        @keyframes sheet-up { from { transform: translateY(40%); opacity: .6; } to { transform: translateY(0); opacity: 1; } }
        #reportDrawer .sheet-body { overflow-y: auto; flex: 1; min-height: 0; -webkit-overflow-scrolling: touch; }
        #reportDrawer .sheet-foot { flex-shrink: 0; border-top: 1px solid #e2e8f0; background: #fff; padding: .75rem 1rem calc(.75rem + env(safe-area-inset-bottom, 0px)); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col<?= Auth::check() ? ' has-staff' : '' ?>">
    <div id="toast-container"></div>

    <script>
        // === Shared JS — must load BEFORE view scripts (they use these at parse time) ===
        window.BASE_PATH = '<?= BASE_PATH ?>';
        window.csrfToken = '<?= Auth::csrfToken() ?>';

        // Toast helper — replaces alert() everywhere
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

        // Staff action helper — POST fields as fetch, toast + reload (used by volunteer workspace)
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

        // Submit overlay helpers (loading / success)
        window.SubmitOverlay = {
            show(phase) {
                const o = document.getElementById('submitOverlay');
                if (!o) return;
                document.getElementById('overlayLoading').classList.toggle('hidden', phase !== 'loading');
                document.getElementById('overlayLoading').classList.toggle('flex', phase === 'loading');
                document.getElementById('overlaySuccess').classList.toggle('hidden', phase !== 'success');
                document.getElementById('overlaySuccess').classList.toggle('flex', phase === 'success');
                o.classList.toggle('loading', phase === 'loading');
                o.classList.toggle('success', phase === 'success');
                o.classList.add('show');
            },
            hide() { const o = document.getElementById('submitOverlay'); if (o) o.classList.remove('show'); }
        };

        // Offline detection + localStorage helper
        const OfflineHelper = {
            isOnline: navigator.onLine,
            queueKey: 'dsc_offline_queue',

            init() {
                window.addEventListener('online', () => {
                    document.body.classList.remove('offline');
                    this.isOnline = true;
                    this.flushQueue();
                });
                window.addEventListener('offline', () => {
                    document.body.classList.add('offline');
                    this.isOnline = false;
                });
                if (!navigator.onLine) document.body.classList.add('offline');
                // Retry pending submissions on load and every 60s — not only on 'online' events
                this.flushQueue();
                setInterval(() => this.flushQueue(), 60000);
            },

            queue(data) {
                const q = JSON.parse(localStorage.getItem(this.queueKey) || '[]');
                q.push({ ...data, queuedAt: Date.now() });
                localStorage.setItem(this.queueKey, JSON.stringify(q));
            },

            _flushing: false,

            async flushQueue() {
                if (this._flushing) return;
                const q = JSON.parse(localStorage.getItem(this.queueKey) || '[]');
                if (!q.length) return;
                this._flushing = true;
                const now = Date.now();
                const remaining = [];
                for (const item of q) {
                    // Drop items older than 24h or retried > 20 times — never block the queue forever
                    if (now - (item.queuedAt || 0) > 24 * 3600 * 1000 || (item.attempts || 0) >= 20) continue;
                    item.attempts = (item.attempts || 0) + 1;
                    try {
                        const form = new FormData();
                        Object.entries(item.payload || {}).forEach(([k,v]) => {
                            if (Array.isArray(v)) v.forEach(x => form.append(k + '[]', x));
                            else form.append(k, v);
                        });
                        form.append('csrf_token', item.csrf || '');
                        const res = await fetch(item.url, { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        const json = await res.json();
                        if (json.ok) {
                            // Keep local "my report" in sync with the server-side case
                            if (json.id && typeof window.saveMyReport === 'function') {
                                window.saveMyReport({ id: json.id, code: json.code, lat: item.payload?.lat, lng: item.payload?.lng, status: 'pending' });
                            }
                        } else {
                            remaining.push(item);
                        }
                    } catch (e) {
                        remaining.push(item);
                    }
                }
                localStorage.setItem(this.queueKey, JSON.stringify(remaining));
                this._flushing = false;
                if (remaining.length && navigator.onLine && typeof window.Toast !== 'undefined') {
                    window.Toast.show('มี ' + remaining.length + ' รายการรอส่ง — จะลองใหม่อัตโนมัติ', 'err');
                }
            },

            saveDraft(key, data) {
                localStorage.setItem('dsc_draft_' + key, JSON.stringify(data));
            },

            loadDraft(key) {
                try {
                    return JSON.parse(localStorage.getItem('dsc_draft_' + key) || 'null');
                } catch { return null; }
            },

            clearDraft(key) {
                localStorage.removeItem('dsc_draft_' + key);
            }
        };
        window.OfflineHelper = OfflineHelper;
        OfflineHelper.init();
    </script>

    <div id="offline-bar">โหมดออฟไลน์ — ข้อมูลจะถูกบันทึกในเครื่องและส่งเมื่อออนไลน์</div>

    <?php if (Auth::check()): $staff = Auth::user(); ?>
    <div class="bg-slate-900 text-slate-200 text-sm">
        <div class="max-w-3xl mx-auto px-4 h-10 flex items-center justify-between gap-3">
            <span class="truncate"><?= Helper::e($staff['name'] ?? '') ?></span>
            <a href="<?= Helper::url(($staff['role'] ?? '') === 'admin' ? 'admin' : 'volunteer') ?>" class="inline-flex items-center gap-1.5 font-medium text-white shrink-0">
                <?= ($staff['role'] ?? '') === 'admin' ? 'ระบบจัดการ' : 'งานอาสา' ?>
                <span id="pendingBadge" class="hidden bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[16px] h-4 px-1 items-center justify-center"></span>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php require APP_PATH . '/Views/needs/_myreport.php'; ?>

    <main class="flex-1 w-full <?= !empty($fullWidth) ? '' : 'max-w-3xl mx-auto px-4' ?> py-4 pb-24">
        <?= $content ?>
    </main>

    <!-- Bottom Navigation — แจ้งเหตุ is the center CTA -->
    <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-[1002] safe-bottom">
        <div class="flex items-end justify-around h-[4.5rem] max-w-lg mx-auto px-1">
            <a href="<?= Helper::url() ?>" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs pt-2 pb-2.5 <?= ($title ?? '') === 'Dlite' ? 'text-brand-600 font-medium' : 'text-slate-500' ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>หน้าแรก</span>
            </a>
            <a href="<?= Helper::url('map') ?>" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs pt-2 pb-2.5 <?= str_contains($title ?? '', 'แผนที่') ? 'text-brand-600 font-medium' : 'text-slate-500' ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>แผนที่</span>
            </a>

            <!-- Center CTA: report -->
            <?php $reliefMode = Helper::appMode() === 'relief'; ?>
            <div class="flex-1 flex flex-col items-center justify-end pb-1.5">
                <button type="button" id="navReportBtn" class="report-cta w-14 h-14 -mt-9 rounded-full <?= $reliefMode ? 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800' : 'bg-red-600 hover:bg-red-700 active:bg-red-800' ?> text-white flex items-center justify-center ring-4 ring-white">
                    <i class="fas <?= $reliefMode ? 'fa-male' : 'fa-person-drowning' ?> text-3xl"></i>
                </button>
                <span class="text-xs font-semibold <?= $reliefMode ? 'text-emerald-600' : 'text-red-600' ?> mt-1"><?= Helper::e(Helper::reportCta()) ?></span>
            </div>

            <a href="<?= Helper::url('track') ?>" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs pt-2 pb-2.5 <?= ($title ?? '') === 'ติดตามสถานะ' ? 'text-brand-600 font-medium' : 'text-slate-500' ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>ติดตาม</span>
            </a>
            <?php if (!Auth::check()): ?>
            <a href="<?= Helper::url('login') ?>" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs pt-2 pb-2.5 <?= str_contains($title ?? '', 'เข้าสู่ระบบ') ? 'text-brand-600 font-medium' : 'text-slate-500' ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                <span>เข้าสู่ระบบ</span>
            </a>
            <?php else: $me = Auth::user(); $meRole = $me['role'] ?? ''; ?>
            <a href="<?= Helper::url($meRole === 'volunteer' ? 'volunteer' : 'admin') ?>" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs pt-2 pb-2.5 <?= str_contains($title ?? '', 'ระบบ') || str_contains($title ?? '', 'อาสา') ? 'text-brand-600 font-medium' : 'text-slate-500' ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span><?= Helper::e(Helper::roleLabel($meRole)) ?></span>
            </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Submit overlay (loading / success) -->
    <div id="submitOverlay">
        <div id="overlayLoading" class="hidden flex-col items-center gap-4 text-white">
            <div class="spinner"></div>
            <p class="text-lg font-medium">กำลังส่งข้อมูล กรุณารอสักครู่...</p>
        </div>
        <div id="overlaySuccess" class="hidden flex-col items-center gap-4">
            <div class="check-pop w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center">
                <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-xl font-semibold text-slate-800">ส่งคำขอเรียบร้อยแล้ว</p>
            <p class="text-sm text-slate-500">ทีมงานจะรีบติดต่อกลับ — ติดตามสถานะได้จากแถบด้านบน</p>
        </div>
    </div>

    <script>
        document.getElementById('navReportBtn').addEventListener('click', function() {
            if (window.openReportDrawer) window.openReportDrawer();
            else window.location.href = '<?= Helper::url() ?>';
        });
    </script>

    <?php if (!empty($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>

    <?php require APP_PATH . '/Views/partials/announcement_popup.php'; ?>
</body>
</html>
