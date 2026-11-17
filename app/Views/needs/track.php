<?php
/**
 * Track a request — auto-shows the visitor's own saved report (localStorage),
 * with manual code lookup as fallback.
 * Expects: $query, $isPhone, $results, $need, $history, $statusLabels, $urgencyLabels,
 *          $scenarioLabels, $needTypes
 */
$myScenario = '';
if ($need && !empty($need['scenario']) && isset($scenarioLabels[$need['scenario']])) {
    $myScenario = $scenarioLabels[$need['scenario']];
}
?>
<div class="space-y-4 max-w-2xl mx-auto">
    <div class="text-center pt-1">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-600/10 text-brand-700 mb-2">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">ติดตามสถานะคำขอ</h1>
        <p class="text-base text-slate-500 mt-1.5">ไม่ต้องจำรหัส — ระบบจะแสดงเคสที่คุณเคยแจ้งไว้ให้อัตโนมัติ</p>
    </div>

    <!-- Auto: my saved report (filled by JS from localStorage) -->
    <div id="myTrackCard" class="hidden"></div>

    <!-- Manual lookup: case code OR phone number (auto-detected) -->
    <form method="get" action="<?= Helper::url('track') ?>" class="card p-4">
        <label class="block text-base font-semibold text-slate-700 mb-2">ค้นหาด้วยรหัสเคส หรือ เบอร์โทรศัพท์</label>
        <div class="space-y-2.5">
            <input type="text" name="q" value="<?= Helper::e($query) ?>" placeholder="เช่น DSC-1A2B3C4D หรือ 0812345678"
                   class="input-field w-full !text-lg" autocomplete="off">
            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl px-5 py-3.5 text-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                ค้นหา
            </button>
        </div>
    </form>

    <?php if (!empty($results)): ?>
        <div class="card p-4">
            <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                พบ <?= count($results) ?> รายการที่ใช้เบอร์นี้
            </h3>
            <ul class="space-y-2.5">
                <?php foreach ($results as $r): ?>
                    <li>
                        <a href="<?= Helper::url('track?code=' . urlencode($r['code'])) ?>" class="flex items-center justify-between gap-2 px-4 py-3.5 rounded-xl bg-slate-50 hover:bg-slate-100 transition">
                            <div class="min-w-0">
                                <p class="text-base font-semibold text-slate-800 truncate flex items-center gap-1.5"><?= Helper::scenarioIcon($r['scenario'] ?? null, 'w-5 h-5 shrink-0 ' . Helper::scenarioIconColor($r['scenario'] ?? 'other')) ?><?= Helper::e(!empty($scenarioLabels[$r['scenario']]) ? $scenarioLabels[$r['scenario']] : $r['code']) ?></p>
                                <p class="text-sm text-slate-500 mt-0.5"><?= Helper::e($r['code']) ?> · <?= Helper::timeAgo($r['created_at']) ?></p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-full font-semibold <?= Helper::statusColor($r['status']) ?> shrink-0"><?= Helper::statusDot($r['status']) ?><?= Helper::e($statusLabels[$r['status']] ?? $r['status']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($query !== '' && $need === null && empty($results)): ?>
        <div class="card p-6 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 text-slate-400 mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <?php if (!empty($isPhone)): ?>
                <p class="text-lg text-slate-700">ไม่พบคำขอที่ใช้เบอร์ <strong><?= Helper::e($query) ?></strong></p>
                <p class="text-base text-slate-400 mt-1.5">ตรวจสอบเบอร์อีกครั้ง หรือใช้รหัสเคสแทน</p>
            <?php else: ?>
                <p class="text-lg text-slate-700">ไม่พบรหัส <strong><?= Helper::e($query) ?></strong></p>
                <p class="text-base text-slate-400 mt-1.5">กรุณาตรวจสอบรหัสอีกครั้ง หรือสอบถามทีมประสานงาน</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($need): ?>
    <div class="card p-5 space-y-4">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2"><?php if (!empty($need['scenario'])): ?><?= Helper::scenarioIcon($need['scenario'], 'w-6 h-6 shrink-0 ' . Helper::scenarioIconColor($need['scenario'])) ?><?php endif; ?><?= Helper::e($myScenario !== '' ? $myScenario : $need['code']) ?></h2>
                <p class="text-base text-slate-500 mt-0.5"><?= Helper::e($need['code']) ?> · <?= Helper::timeAgo($need['created_at']) ?></p>
            </div>
            <div class="flex flex-col items-end gap-1.5 shrink-0">
                <span class="inline-flex items-center gap-1.5 text-base px-3 py-1.5 rounded-full font-semibold <?= Helper::statusColor($need['status']) ?>"><?= Helper::statusDot($need['status']) ?><?= Helper::e($statusLabels[$need['status']] ?? $need['status']) ?></span>
                <?php if ($need['urgency'] !== 'normal'): ?>
                    <span class="text-base px-3 py-1.5 rounded-full font-semibold <?= Helper::urgencyColor($need['urgency']) ?>"><?= Helper::e($urgencyLabels[$need['urgency']] ?? $need['urgency']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-2 text-base">
            <?php if (!empty($need['need_types'])): ?>
                <div class="flex flex-wrap gap-1.5">
                    <?php foreach ($need['need_types'] as $t): ?>
                        <span class="inline-flex items-center gap-1.5 text-base px-3 py-1.5 rounded-lg font-medium <?= Helper::needTypeChipColor($t) ?>"><?= Helper::needTypeIcon($t, 'w-5 h-5 shrink-0') ?><?= Helper::e($needTypes[$t] ?? $t) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php
            $place = ($need['district'] ?? '') !== '' ? $need['district'] : ($need['area_name'] ?? '');
            $loc = implode(' · ', array_values(array_unique(array_filter([$place, $need['province'] ?? '']))));
            ?>
            <?php if ($loc !== ''): ?>
                <p class="flex items-center gap-2 text-slate-700">
                    <svg class="w-5 h-5 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <?= Helper::e($loc) ?>
                </p>
            <?php endif; ?>
            <?php if ((int)$need['people_count'] > 0): ?>
                <p class="flex items-center gap-2 text-slate-700">
                    <svg class="w-5 h-5 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    ผู้ได้รับผลกระทบ <?= (int)$need['people_count'] ?> คน
                </p>
            <?php endif; ?>
        </div>

        <a href="<?= Helper::url('needs/' . (int)$need['id']) ?>" class="block text-center text-lg font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border-2 border-brand-200 rounded-xl px-4 py-3.5">
            ดูรายละเอียดเหตุการณ์ →
        </a>

        <?php if (!in_array($need['status'], ['completed', 'cancelled'], true)): ?>
        <div class="rounded-2xl border-2 border-emerald-300 bg-emerald-50 p-4">
            <button type="button" id="closeCaseToggle" class="w-full flex items-center justify-center gap-2 text-emerald-800 font-bold text-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                ได้รับความช่วยเหลือแล้ว? ปิดเคสเอง
            </button>
            <form id="closeCaseForm" class="hidden mt-4 space-y-2.5">
                <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
                <input type="hidden" name="code" value="<?= Helper::e($need['code']) ?>">
                <input type="tel" name="phone" required inputmode="tel" placeholder="เบอร์โทรที่ใช้แจ้งเคสนี้" class="input-field w-full !text-base">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl px-4 py-3.5 text-lg">ยืนยันปิดเคส</button>
                <p id="closeCaseMsg" class="text-base hidden"></p>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($history)): ?>
    <div class="card p-5">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            ไทม์ไลน์สถานะ
        </h3>
        <ul class="space-y-4 text-base">
            <?php $lastIdx = count($history) - 1; foreach ($history as $hi => $h): ?>
                <li class="flex gap-3.5">
                    <div class="flex flex-col items-center">
                        <div class="w-3.5 h-3.5 rounded-full <?= $hi === $lastIdx ? 'bg-brand-600' : 'bg-slate-300' ?> mt-1.5 shrink-0"></div>
                        <?php if ($hi < $lastIdx): ?><div class="w-0.5 flex-1 bg-slate-200 mt-1.5"></div><?php endif; ?>
                    </div>
                    <div class="pb-1">
                        <div class="text-slate-800 font-medium">
                            <?php if ($h['old_status'] && $h['old_status'] !== $h['new_status']): ?>
                                <?= Helper::e($statusLabels[$h['old_status']] ?? $h['old_status']) ?> →
                            <?php endif; ?>
                            <strong><?= Helper::e($statusLabels[$h['new_status']] ?? $h['new_status']) ?></strong>
                        </div>
                        <?php if ($h['message']): ?>
                            <div class="text-slate-600 mt-0.5"><?= Helper::e($h['message']) ?></div>
                        <?php endif; ?>
                        <div class="text-sm text-slate-400 mt-0.5"><?= Helper::timeAgo($h['created_at']) ?></div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <p class="text-base text-center text-slate-400">หากมีข้อสงสัย ติดต่อทีมประสานงานโดยใช้รหัส <?= Helper::e($need['code']) ?></p>
    <?php endif; ?>
</div>

<?php if ($need && !in_array($need['status'], ['completed', 'cancelled'], true)): ?>
<script>
document.getElementById('closeCaseToggle')?.addEventListener('click', () => {
    document.getElementById('closeCaseForm')?.classList.toggle('hidden');
});
document.getElementById('closeCaseForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const msg = document.getElementById('closeCaseMsg');
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    const fd = new FormData(form);
    try {
        const res = await fetch(window.BASE_PATH + '/needs/<?= (int)$need['id'] ?>/close', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        const json = await res.json();
        msg.classList.remove('hidden');
        msg.textContent = json.message || '';
        msg.className = 'text-sm ' + (json.ok ? 'text-emerald-700' : 'text-red-600');
        if (json.ok) setTimeout(() => location.reload(), 1000);
        else btn.disabled = false;
    } catch (err) {
        msg.classList.remove('hidden');
        msg.textContent = 'เกิดข้อผิดพลาดเครือข่าย';
        msg.className = 'text-sm text-red-600';
        btn.disabled = false;
    }
});
</script>
<?php endif; ?>

<?php
// Meta (label + colors + icon svg) for the JS-rendered "my report" card
$scenarioMetaJs = [];
foreach (Helper::scenarioLabels() as $k => $label) {
    $scenarioMetaJs[$k] = [
        'label' => $label,
        'chip' => Helper::scenarioChipColor($k),
        'icon' => Helper::scenarioIcon($k, 'w-6 h-6'),
    ];
}
$needTypeMetaJs = [];
foreach (Helper::needTypes() as $k => $label) {
    $needTypeMetaJs[$k] = [
        'label' => $label,
        'chip' => Helper::needTypeChipColor($k),
        'icon' => Helper::needTypeIcon($k, 'w-4 h-4'),
    ];
}
$urgencyMetaJs = [];
foreach (Helper::urgencyLabels() as $k => $label) {
    $urgencyMetaJs[$k] = ['label' => $label, 'chip' => Helper::urgencyChipColor($k)];
}
$jsonFlags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
?>
<script>
// Auto-list the visitor's own saved reports (saved to localStorage on submit)
(function () {
    const card = document.getElementById('myTrackCard');
    let reports = [];
    if (typeof window.getMyReports === 'function') {
        reports = window.getMyReports();
    } else {
        try {
            const legacy = JSON.parse(localStorage.getItem('dsc_my_report') || 'null');
            if (legacy && legacy.id) reports = [legacy];
        } catch (e) {}
    }
    reports = reports.slice(0, 8);
    if (!reports.length) return;
    const statusLabels = <?= json_encode([
        'pending' => 'รอการตรวจสอบ', 'accepted' => 'รับเรื่องแล้ว',
        'in_progress' => 'กำลังเข้าพื้นที่ช่วยเหลือ', 'completed' => 'ช่วยเหลือสำเร็จแล้ว',
        'cancelled' => 'ยกเลิก'
    ], JSON_UNESCAPED_UNICODE) ?>;
    const statusBadge = {
        pending: 'bg-amber-100 text-amber-700', accepted: 'bg-blue-100 text-blue-700',
        in_progress: 'bg-indigo-100 text-indigo-700', completed: 'bg-emerald-100 text-emerald-700',
        cancelled: 'bg-slate-100 text-slate-500'
    };
    const statusIcons = {
        pending: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        accepted: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        in_progress: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>',
        completed: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
        cancelled: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    };
    const scenarioMeta = <?= json_encode($scenarioMetaJs, $jsonFlags) ?>;
    const needTypeMeta = <?= json_encode($needTypeMetaJs, $jsonFlags) ?>;
    const urgencyMeta = <?= json_encode($urgencyMetaJs, $jsonFlags) ?>;
    const csrfToken = <?= json_encode($csrf ?? '') ?>;
    const pinIcon = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
    function timeAgoTH(iso) {
        if (!iso) return '';
        const t = new Date(String(iso).replace(' ', 'T')).getTime();
        if (isNaN(t)) return '';
        const diff = Math.floor((Date.now() - t) / 1000);
        if (diff < 60) return 'เมื่อสักครู่';
        if (diff < 3600) return Math.floor(diff / 60) + ' นาทีที่แล้ว';
        if (diff < 86400) return Math.floor(diff / 3600) + ' ชั่วโมงที่แล้ว';
        if (diff < 604800) return Math.floor(diff / 86400) + ' วันที่แล้ว';
        return String(iso).slice(0, 10).split('-').reverse().join('/');
    }

    async function fetchOne(r) {
        try {
            const res = await fetch(window.BASE_PATH + '/api/report-status?id=' + encodeURIComponent(r.id), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            if (json.ok && json.found) {
                r.status = json.status; r.code = json.code || r.code;
                return { r, extra: json };
            }
            if (json.ok && !json.found && typeof window.removeMyReport === 'function') {
                // Case deleted on server — drop stale local copy
                window.removeMyReport(r.id);
            }
        } catch (e) {}
        return { r, extra: {} };
    }

    function renderItem({ r, extra }) {
        const status = r.status || 'pending';
        const code = r.code || '';
        const label = statusLabels[status] || status;
        const badge = statusBadge[status] || 'bg-slate-100 text-slate-500';
        const icon = statusIcons[status] || statusIcons.pending;
        const base = window.BASE_PATH || '';

        const meta = scenarioMeta[extra.scenario] || null;
        const iconBox = meta
            ? `<div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 ${meta.chip}">${meta.icon}</div>`
            : `<div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 bg-slate-100 text-slate-500">${pinIcon.replace('w-6 h-6', 'w-5 h-5')}</div>`;
        const title = meta ? meta.label : 'เหตุการณ์ของคุณ';
        const ago = timeAgoTH(extra.created_at);
        const sub = `รหัส ${esc(code)}${ago ? ' · ' + esc(ago) : ''}`;

        const isActive = ['pending', 'accepted', 'in_progress'].includes(status);
        const pulse = isActive ? 'animate-pulse' : '';

        return `
            <button type="button" data-case-id="${encodeURIComponent(r.id)}" class="w-full text-left flex items-center gap-3 p-3.5 rounded-2xl bg-white border border-slate-200 hover:border-brand-300 hover:bg-brand-50/40 transition">
                ${iconBox}
                <div class="flex-1 min-w-0">
                    <p class="text-base font-bold text-slate-800 truncate">${esc(title)}</p>
                    <p class="text-sm text-slate-500 mt-0.5 truncate">${sub}</p>
                </div>
                <span class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 ${pulse} ${badge}" title="${esc(label)}">
                    ${icon}<span class="sr-only">${esc(label)}</span>
                </span>
            </button>`;
    }

    async function render() {
        const items = await Promise.all(reports.map(fetchOne));
        const valid = items.filter(i => i.r && i.r.id);
        if (!valid.length) return;

        card.innerHTML = `
            <div class="card p-4">
                <div class="flex items-center gap-2.5 mb-3">
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <h2 class="text-lg font-bold text-slate-800">เรื่องแจ้งของคุณ</h2>
                    <span class="text-sm text-slate-400">(${valid.length})</span>
                </div>
                <div class="space-y-2">${valid.map(renderItem).join('')}</div>
                <p class="text-sm text-slate-400 mt-3">แตะที่รายการเพื่อดูรายละเอียด</p>
            </div>`;
        card.querySelectorAll('[data-case-id]').forEach(btn => {
            btn.addEventListener('click', () => openCase(btn.dataset.caseId));
        });
        card.classList.remove('hidden');
    }

    // ---- Case detail popup ----
    let modalEl = null;
    function ensureModal() {
        if (modalEl) return modalEl;
        modalEl = document.createElement('div');
        modalEl.className = 'hidden fixed inset-0 z-[1600] flex items-end sm:items-center justify-center';
        modalEl.innerHTML = `
            <div class="absolute inset-0 bg-slate-900/60" data-close></div>
            <div class="relative bg-white w-full sm:max-w-lg rounded-t-3xl sm:rounded-2xl max-h-[80vh] overflow-y-auto shadow-2xl mb-[4.5rem] sm:mb-4">
                <div id="caseModalBody" class="p-5"></div>
            </div>`;
        document.body.appendChild(modalEl);
        modalEl.addEventListener('click', (e) => {
            if (e.target.closest('[data-close]') || e.target.closest('[data-modal-close]')) closeModal();
        });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
        return modalEl;
    }
    function closeModal() {
        if (modalEl) {
            modalEl.classList.add('hidden');
            modalEl.classList.remove('flex');
            document.body.style.overflow = '';
        }
    }

    const pinSm = pinIcon.replace('w-6 h-6', 'w-4 h-4');

    function renderModalBody(n, history) {
        const base = window.BASE_PATH || '';
        const status = n.status || 'pending';
        const label = statusLabels[status] || status;
        const badge = statusBadge[status] || 'bg-slate-100 text-slate-500';
        const sIcon = statusIcons[status] || statusIcons.pending;
        const meta = scenarioMeta[n.scenario] || null;
        const iconBox = meta
            ? `<div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 ${meta.chip}">${meta.icon}</div>`
            : `<div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 bg-slate-100 text-slate-500">${pinIcon}</div>`;
        const title = meta ? meta.label : 'เหตุการณ์ของคุณ';
        const urg = urgencyMeta[n.urgency] || null;
        const ago = timeAgoTH(n.created_at);
        const loc = [n.district, n.province].filter(Boolean).join(' · ') || n.area_name || '';

        const chips = (Array.isArray(n.need_types) ? n.need_types : []).slice(0, 6).map(t => {
            const m = needTypeMeta[t];
            return m ? `<span class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-lg font-medium ${m.chip}">${m.icon}${esc(m.label)}</span>` : '';
        }).join('');

        const hist = (history || []).map(h => {
            const st = statusLabels[h.new_status] || h.new_status;
            const old = h.old_status && h.old_status !== h.new_status ? (statusLabels[h.old_status] || h.old_status) + ' → ' : '';
            return `<li class="flex gap-2.5 text-sm">
                <span class="w-2 h-2 rounded-full bg-brand-400 mt-1.5 shrink-0"></span>
                <div><span class="font-medium text-slate-700">${old}<strong>${esc(st)}</strong></span>
                ${h.message ? `<div class="text-slate-500">${esc(h.message)}</div>` : ''}
                <div class="text-slate-400">${esc(timeAgoTH(h.created_at))}</div></div>
            </li>`;
        }).join('');

        return `
            <div class="flex items-start justify-between gap-2 mb-4">
                <div class="flex items-center gap-3 min-w-0">
                    ${iconBox}
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">รายละเอียดเหตุการณ์</p>
                        <h3 class="text-lg font-bold text-slate-900 truncate">${esc(title)}</h3>
                        <p class="text-sm text-slate-500">รหัส ${esc(n.code)}${ago ? ' · ' + esc(ago) : ''}</p>
                    </div>
                </div>
                <button type="button" data-modal-close class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 shrink-0" aria-label="ปิด">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex flex-wrap gap-1.5 mb-3">
                <span class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-full font-semibold ${badge}">${sIcon}${esc(label)}</span>
                ${urg ? `<span class="inline-flex items-center text-sm px-3 py-1.5 rounded-full font-semibold ${urg.chip}">${esc(urg.label)}</span>` : ''}
            </div>
            <div class="space-y-1.5 text-base text-slate-700 mb-3">
                ${loc ? `<p class="flex items-center gap-2">${pinSm} ${esc(loc)}</p>` : ''}
                ${n.people_count > 0 ? `<p>ผู้ได้รับผลกระทบ ${esc(n.people_count)} คน</p>` : ''}
            </div>
            ${chips ? `<div class="flex flex-wrap gap-1.5 mb-3">${chips}</div>` : ''}
            ${n.description ? `<p class="text-base text-slate-600 whitespace-pre-wrap bg-slate-50 rounded-xl p-3 mb-3">${esc(n.description)}</p>` : ''}
            ${n.photo_path ? `<a href="${base}/${esc(n.photo_path)}" target="_blank" rel="noopener" class="block mb-3"><img src="${base}/${esc(n.photo_path)}" alt="รูปประกอบ" class="w-full rounded-xl max-h-56 object-cover bg-slate-100"></a>` : ''}
            ${hist ? `<div class="mb-4"><p class="text-sm font-bold text-slate-500 mb-2">ไทม์ไลน์</p><ul class="space-y-2.5">${hist}</ul></div>` : ''}
            <div class="pt-1">
                <a href="${base}/needs/${encodeURIComponent(n.id)}" class="block text-center text-base font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl px-4 py-3">ดูรายละเอียดเต็ม →</a>
            </div>
            ${!['completed', 'cancelled'].includes(status) ? `
            <div class="mt-3 rounded-2xl border-2 border-emerald-300 bg-emerald-50 p-4">
                <button type="button" id="modalCloseToggle" class="w-full flex items-center justify-center gap-2 text-emerald-800 font-bold text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    ได้รับความช่วยเหลือแล้ว? ปิดเคสเอง
                </button>
                <form id="modalCloseForm" class="hidden mt-3.5 space-y-2.5">
                    <input type="hidden" name="csrf_token" value="${esc(csrfToken)}">
                    <input type="hidden" name="code" value="${esc(n.code)}">
                    <input type="tel" name="phone" required inputmode="tel" placeholder="เบอร์โทรที่ใช้แจ้งเคสนี้" class="input-field w-full !text-base">
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl px-4 py-3 text-base">ยืนยันปิดเคส</button>
                    <p id="modalCloseMsg" class="text-base hidden"></p>
                </form>
            </div>` : ''}`;
    }

    function bindModalClose(needId) {
        const toggle = document.getElementById('modalCloseToggle');
        const form = document.getElementById('modalCloseForm');
        if (!toggle || !form) return;
        toggle.addEventListener('click', () => form.classList.toggle('hidden'));
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const msg = document.getElementById('modalCloseMsg');
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            try {
                const res = await fetch(window.BASE_PATH + '/needs/' + encodeURIComponent(needId) + '/close', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form)
                });
                const json = await res.json();
                msg.classList.remove('hidden');
                msg.textContent = json.message || '';
                msg.className = 'text-base ' + (json.ok ? 'text-emerald-700' : 'text-red-600');
                if (json.ok) {
                    setTimeout(() => { closeModal(); render(); }, 1200);
                } else {
                    btn.disabled = false;
                }
            } catch (err) {
                msg.classList.remove('hidden');
                msg.textContent = 'เกิดข้อผิดพลาดเครือข่าย';
                msg.className = 'text-base text-red-600';
                btn.disabled = false;
            }
        });
    }

    async function openCase(id) {
        const m = ensureModal();
        const body = m.querySelector('#caseModalBody');
        body.innerHTML = '<div class="py-12 text-center text-slate-400 text-base">กำลังโหลด...</div>';
        m.classList.remove('hidden');
        m.classList.add('flex');
        document.body.style.overflow = 'hidden';
        try {
            const res = await fetch(window.BASE_PATH + '/api/need-detail?id=' + encodeURIComponent(id), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            if (json.ok && json.found) {
                body.innerHTML = renderModalBody(json.need, json.history);
                bindModalClose(json.need.id);
            } else {
                body.innerHTML = '<div class="py-12 text-center text-slate-400 text-base">ไม่พบข้อมูลเหตุการณ์</div>';
            }
        } catch (e) {
            body.innerHTML = '<div class="py-12 text-center text-red-500 text-base">โหลดไม่สำเร็จ กรุณาลองใหม่</div>';
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', render);
    } else {
        render();
    }
})();
</script>
