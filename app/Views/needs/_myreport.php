<?php
/**
 * "My Report" banner — shows saved report status at top of every page.
 * Reads localStorage 'dsc_my_report' = {id, code, lat, lng, status}
 * - Tap banner → /track
 * - ✕ dismisses until the status changes or a new report is filed
 * - Hidden on /track (that page has its own auto-card)
 */
?>
<div id="myReportBanner" class="hidden"></div>
<script>
(function() {
    const KEY = 'dsc_my_reports';        // array, newest first: [{id, code, lat, lng, status}]
    const LEGACY_KEY = 'dsc_my_report'; // old single-object key — migrated on read
    const DISMISS_KEY = 'dsc_my_report_dismissed';
    const MAX_REPORTS = 10;

    function readAll() {
        try {
            const arr = JSON.parse(localStorage.getItem(KEY) || 'null');
            if (Array.isArray(arr)) return arr.filter(r => r && r.id);
            const legacy = JSON.parse(localStorage.getItem(LEGACY_KEY) || 'null');
            if (legacy && legacy.id) {
                try {
                    localStorage.setItem(KEY, JSON.stringify([legacy]));
                    localStorage.removeItem(LEGACY_KEY);
                } catch (e) {}
                return [legacy];
            }
        } catch (e) {}
        return [];
    }
    window.getMyReports = readAll;
    window.getMyReport = function() { return readAll()[0] || null; };
    window.saveMyReport = function(r) {
        try {
            const arr = readAll().filter(x => x.id !== r.id);
            arr.unshift(r);
            localStorage.setItem(KEY, JSON.stringify(arr.slice(0, MAX_REPORTS)));
            localStorage.removeItem(LEGACY_KEY);
        } catch (e) {}
    };
    window.removeMyReport = function(id) {
        try {
            localStorage.setItem(KEY, JSON.stringify(readAll().filter(x => x.id !== id)));
            localStorage.removeItem(LEGACY_KEY);
        } catch (e) {}
    };

    const report = window.getMyReport();
    if (!report || !report.id) return;
    if (/\/track(\/|$|\?)/.test(window.location.pathname + window.location.search)) return;

    const statusLabels = {
        pending: 'รอการตรวจสอบ', accepted: 'รับเรื่องแล้ว',
        in_progress: 'กำลังเข้าพื้นที่ช่วยเหลือ', completed: 'ช่วยเหลือสำเร็จแล้ว',
        cancelled: 'ยกเลิก'
    };
    const statusBg = {
        pending: 'bg-red-600', accepted: 'bg-blue-600', in_progress: 'bg-amber-500',
        completed: 'bg-emerald-600', cancelled: 'bg-slate-500'
    };
    const statusDot = {
        pending: 'bg-red-200', accepted: 'bg-blue-200', in_progress: 'bg-amber-200',
        completed: 'bg-emerald-200', cancelled: 'bg-slate-300'
    };

    function sig(r) { return r.id + ':' + (r.status || ''); }
    function isDismissed(r) {
        try { return localStorage.getItem(DISMISS_KEY) === sig(r); } catch { return false; }
    }

    async function render() {
        const banner = document.getElementById('myReportBanner');
        if (!banner) return;
        let status = report.status || 'pending';
        let code = report.code || '';
        // Refresh status from server
        try {
            const res = await fetch(window.BASE_PATH + '/api/report-status?id=' + encodeURIComponent(report.id), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            if (json.ok && json.found) {
                status = json.status;
                code = json.code || code;
                report.status = status;
                report.code = code;
                window.saveMyReport(report);
            } else if (json.ok && !json.found) {
                // Server no longer has this case (deleted) — drop stale local data
                window.removeMyReport(report.id);
                return;
            }
        } catch (e) { /* offline — show cached */ }

        // User dismissed this exact state — stay hidden until status changes
        if (isDismissed(report)) return;

        const label = statusLabels[status] || status;
        const bg = statusBg[status] || 'bg-brand-600';
        const dot = statusDot[status] || 'bg-white/60';

        banner.className = bg + ' text-white cursor-pointer';
        banner.innerHTML = `
            <div class="max-w-3xl mx-auto px-4 py-2.5 flex items-center gap-3">
                <span class="w-2.5 h-2.5 rounded-full ${dot} shrink-0"></span>
                <div class="flex-1 min-w-0 text-sm">
                    <span class="font-semibold">เรื่องแจ้งของคุณ</span>
                    <span class="mx-1.5 opacity-70">•</span>
                    <span>${label}</span>
                    ${code ? `<span class="opacity-70"> (${code})</span>` : ''}
                </div>
                <button type="button" id="myReportLocate" class="shrink-0 bg-white/20 hover:bg-white/30 rounded-lg px-3 py-1.5 text-sm font-medium">
                    ดูบนแผนที่
                </button>
                <button type="button" id="myReportDismiss" class="shrink-0 w-8 h-8 rounded-lg hover:bg-white/20 flex items-center justify-center" aria-label="ปิด">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>`;
        banner.classList.remove('hidden');

        document.getElementById('myReportDismiss').addEventListener('click', (e) => {
            e.stopPropagation();
            try { localStorage.setItem(DISMISS_KEY, sig(report)); } catch (err) {}
            banner.classList.add('hidden');
        });
        document.getElementById('myReportLocate').addEventListener('click', (e) => {
            e.stopPropagation();
            if (typeof window.centerOnNeed === 'function') {
                window.centerOnNeed(report.id);
            } else {
                window.location.href = window.BASE_PATH + '/?focus=' + encodeURIComponent(report.id);
            }
        });
        banner.addEventListener('click', () => {
            window.location.href = window.BASE_PATH + '/track';
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', render);
    } else {
        render();
    }
})();
</script>
