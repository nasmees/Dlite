<?php
/**
 * "My Report" banner — shows saved report status at top of every page.
 * Reads localStorage 'dsc_my_report' = {id, code, lat, lng}
 */
?>
<div id="myReportBanner" class="hidden bg-brand-600 text-white"></div>
<script>
(function() {
    const KEY = 'dsc_my_report';
    window.getMyReport = function() {
        try { return JSON.parse(localStorage.getItem(KEY) || 'null'); } catch { return null; }
    };
    window.saveMyReport = function(r) {
        try { localStorage.setItem(KEY, JSON.stringify(r)); } catch {}
    };

    const report = window.getMyReport();
    if (!report || !report.id) return;

    const statusLabels = {
        pending: 'รอการตรวจสอบ', accepted: 'รับเรื่องแล้ว',
        in_progress: 'กำลังเข้าพื้นที่ช่วยเหลือ', completed: 'ช่วยเหลือสำเร็จแล้ว',
        cancelled: 'ยกเลิก'
    };
    const statusColor = {
        pending: 'bg-red-500', accepted: 'bg-amber-500', in_progress: 'bg-amber-500',
        completed: 'bg-emerald-500', cancelled: 'bg-slate-400'
    };

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
            }
        } catch (e) { /* offline — show cached */ }

        const label = statusLabels[status] || status;
        const dot = statusColor[status] || 'bg-slate-400';
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
            </div>`;
        banner.classList.remove('hidden');

        document.getElementById('myReportLocate').addEventListener('click', () => {
            if (typeof window.centerOnNeed === 'function') {
                window.centerOnNeed(report.id);
            } else {
                window.location.href = window.BASE_PATH + '/?focus=' + encodeURIComponent(report.id);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', render);
    } else {
        render();
    }
})();
</script>
