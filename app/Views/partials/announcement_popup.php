<?php
/**
 * Broadcast announcement popup — polls /announcements/latest and shows
 * the newest announcement the viewer hasn't dismissed yet (localStorage).
 * Included by both public and admin layouts.
 */
?>
<div id="annPopup" class="hidden fixed inset-0 z-[1500] items-center justify-center p-4" role="dialog" aria-modal="true">
    <div id="annPopupBackdrop" class="absolute inset-0 bg-slate-900/50"></div>
    <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden">
        <div id="annPopupHeader" class="px-5 py-4 flex items-center gap-3 text-white">
            <span id="annPopupIcon" class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </span>
            <div class="min-w-0">
                <p id="annPopupLevel" class="text-sm font-semibold uppercase tracking-wide opacity-90"></p>
                <h2 id="annPopupTitle" class="text-xl font-bold leading-snug"></h2>
            </div>
        </div>
        <div class="px-5 py-4">
            <p id="annPopupBody" class="text-lg text-slate-800 whitespace-pre-line leading-loose"></p>
            <p id="annPopupTime" class="mt-3 text-sm text-slate-400"></p>
        </div>
        <div class="px-5 pb-5">
            <button type="button" id="annPopupClose" class="w-full rounded-xl bg-brand-600 px-4 py-3.5 text-lg font-bold text-white hover:bg-brand-700">รับทราบ</button>
        </div>
    </div>
</div>

<script>
(function () {
    const LS_KEY = 'dsc_announcement_seen';
    const POLL_MS = 45000;
    const popup = document.getElementById('annPopup');
    const header = document.getElementById('annPopupHeader');
    const levelEl = document.getElementById('annPopupLevel');
    const titleEl = document.getElementById('annPopupTitle');
    const bodyEl = document.getElementById('annPopupBody');
    const timeEl = document.getElementById('annPopupTime');
    const closeBtn = document.getElementById('annPopupClose');
    const backdrop = document.getElementById('annPopupBackdrop');
    let currentId = 0;
    let open = false;

    const LEVELS = {
        info:     { label: 'ประกาศ',       cls: 'bg-blue-600' },
        warning:  { label: 'แจ้งเตือน',     cls: 'bg-amber-500' },
        critical: { label: 'เร่งด่วน',      cls: 'bg-rose-600' }
    };

    function seenId() {
        try { return parseInt(localStorage.getItem(LS_KEY) || '0', 10) || 0; } catch (e) { return 0; }
    }
    function markSeen(id) {
        try {
            if (id > seenId()) localStorage.setItem(LS_KEY, String(id));
        } catch (e) {}
    }

    function show(a) {
        const lv = LEVELS[a.level] || LEVELS.info;
        header.className = 'px-5 py-4 flex items-center gap-3 text-white ' + lv.cls;
        levelEl.textContent = lv.label;
        titleEl.textContent = a.title || '';
        titleEl.title = a.title || '';
        bodyEl.textContent = a.body || '';
        timeEl.textContent = a.created_at ? ('ประกาศเมื่อ ' + a.created_at) : '';
        currentId = parseInt(a.id, 10) || 0;
        popup.classList.remove('hidden');
        popup.classList.add('flex');
        open = true;
    }
    function hide() {
        popup.classList.add('hidden');
        popup.classList.remove('flex');
        open = false;
    }
    function dismiss() {
        if (currentId) markSeen(currentId);
        hide();
    }

    closeBtn.addEventListener('click', dismiss);
    backdrop.addEventListener('click', dismiss);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && open) dismiss(); });

    // Admin pages poll for staff-targeted announcements, public pages for public ones
    const FOR = /\/admin(\/|$)/.test(window.location.pathname) ? 'admin' : 'public';

    async function poll() {
        try {
            const res = await fetch(window.BASE_PATH + '/announcements/latest?for=' + FOR, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!res.ok) return;
            const data = await res.json();
            const a = data && data.announcement;
            if (a && parseInt(a.id, 10) > seenId() && !open) show(a);
        } catch (e) { /* offline / endpoint missing — ignore */ }
    }

    setTimeout(poll, 3000);          // first check shortly after load
    setInterval(poll, POLL_MS);
})();
</script>
