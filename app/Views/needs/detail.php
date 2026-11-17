<?php
/**
 * Public case detail — read-only for everyone.
 * Staff (logged in) get a banner linking to the admin case page.
 * Expects: $need, $history, $helps, $helpTypes, $statusLabels,
 *          $urgencyLabels, $needTypes, $scenarioLabels, $csrf,
 *          $isVolunteer, $isLoggedIn
 */
$isStaff = !empty($isLoggedIn);
$scenarioTitle = !empty($need['scenario']) && isset($scenarioLabels[$need['scenario']])
    ? $scenarioLabels[$need['scenario']]
    : 'รายละเอียดเหตุการณ์';
?>
<div class="space-y-4">
    <div class="flex items-center gap-2 text-base text-slate-500">
        <a href="<?= Helper::url('needs') ?>" class="hover:text-brand-600 font-medium">รายการ</a>
        <span>/</span>
        <span><?= Helper::e($need['code']) ?></span>
    </div>

    <?php if ($isStaff): ?>
    <div class="rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3.5 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 text-base text-brand-800 font-medium">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>มุมมองสาธารณะ — จัดการเคสนี้ได้ที่หน้า Admin</span>
        </div>
        <a href="<?= Helper::url('admin/requests/' . (int)$need['id']) ?>" class="shrink-0 text-base font-bold text-white bg-brand-600 hover:bg-brand-700 px-4 py-2.5 rounded-xl">ไปหน้า Admin →</a>
    </div>
    <?php endif; ?>

    <div class="card p-5 space-y-4">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold text-slate-900"><?= Helper::e($isStaff ? $need['reporter_name'] : $scenarioTitle) ?></h1>
                <p class="text-base text-slate-500 mt-1"><?= Helper::e($need['code']) ?> · <?= Helper::timeAgo($need['created_at']) ?></p>
            </div>
            <div class="flex flex-col items-end gap-1.5 shrink-0">
                <span class="inline-flex items-center gap-1.5 text-base px-3 py-1.5 rounded-full font-semibold <?= Helper::statusColor($need['status']) ?>"><?= Helper::statusDot($need['status']) ?><?= Helper::e($statusLabels[$need['status']] ?? $need['status']) ?></span>
                <?php if ($need['urgency'] !== 'normal'): ?>
                    <span class="text-base px-3 py-1.5 rounded-full font-semibold <?= Helper::urgencyColor($need['urgency']) ?>"><?= Helper::e($urgencyLabels[$need['urgency']] ?? $need['urgency']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($need['photo_path'])): ?>
            <a href="<?= Helper::e(Helper::photoUrl($need['photo_path'])) ?>" target="_blank" rel="noopener">
                <img src="<?= Helper::e(Helper::photoUrl($need['photo_path'])) ?>"
                     alt="รูปประกอบคำขอ" loading="lazy" decoding="async"
                     class="w-full rounded-xl object-cover max-h-64 bg-slate-100">
            </a>
        <?php endif; ?>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-base">
            <div>
                <dt class="flex items-center gap-1.5 text-slate-500 mb-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    เบอร์โทร
                </dt>
                <dd class="font-semibold text-lg">
                    <?php if (!empty($isStaff) && $need['reporter_phone']): ?>
                        <a href="tel:<?= preg_replace('/\D/', '', $need['reporter_phone']) ?>" class="text-brand-600"><?= Helper::e(Helper::formatPhone($need['reporter_phone'])) ?></a>
                    <?php elseif ($need['reporter_phone']): ?>
                        <span class="text-slate-400"><?= Helper::e(substr($need['reporter_phone'], 0, 3)) ?>-xxx-xxxx</span>
                        <span class="text-sm text-slate-400">(ทีมงานเท่านั้นที่ดูได้)</span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt class="flex items-center gap-1.5 text-slate-500 mb-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    จำนวนคน
                </dt>
                <dd class="font-semibold text-lg"><?= (int)$need['people_count'] ?> คน</dd>
            </div>
            <div>
                <dt class="flex items-center gap-1.5 text-slate-500 mb-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    ประเภทเหตุการณ์
                </dt>
                <dd><span class="inline-flex items-center gap-2 text-base px-3 py-1.5 rounded-lg font-semibold <?= Helper::scenarioChipColor($need['scenario'] ?? 'other') ?>"><?= Helper::scenarioIcon($need['scenario'], 'w-5 h-5 shrink-0') ?><?= Helper::e($scenarioLabels[$need['scenario']] ?? '-') ?></span></dd>
            </div>
            <div>
                <dt class="flex items-center gap-1.5 text-slate-500 mb-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    พื้นที่
                </dt>
                <dd class="font-semibold text-lg"><?= Helper::e(($need['district'] ?? '') !== '' ? $need['district'] : ($need['area_name'] ?? '')) ?><?= !empty($need['province']) ? ' · ' . Helper::e($need['province']) : '' ?></dd>
            </div>
            <?php if ($isStaff && $need['address']): ?>
            <div class="sm:col-span-2">
                <dt class="flex items-center gap-1.5 text-slate-500 mb-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    ที่อยู่
                </dt>
                <dd class="font-medium text-lg"><?= Helper::e($need['address']) ?></dd>
            </div>
            <?php endif; ?>
            <div class="sm:col-span-2">
                <dt class="flex items-center gap-1.5 text-slate-500 mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    ความต้องการ
                </dt>
                <dd class="flex flex-wrap gap-1.5">
                    <?php foreach ($need['need_types'] as $t): ?>
                        <span class="inline-flex items-center gap-1.5 text-base px-3 py-1.5 rounded-lg font-medium <?= Helper::needTypeChipColor($t) ?>"><?= Helper::needTypeIcon($t, 'w-5 h-5 shrink-0') ?><?= Helper::e($needTypes[$t] ?? $t) ?></span>
                    <?php endforeach; ?>
                    <?php if (empty($need['need_types'])): ?>-<?php endif; ?>
                </dd>
            </div>
            <?php if ($need['description']): ?>
            <div class="sm:col-span-2">
                <dt class="flex items-center gap-1.5 text-slate-500 mb-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    รายละเอียด
                </dt>
                <dd class="whitespace-pre-wrap text-lg leading-relaxed"><?= Helper::e($need['description']) ?></dd>
            </div>
            <?php endif; ?>
            <div>
                <dt class="flex items-center gap-1.5 text-slate-500 mb-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    แจ้งเมื่อ
                </dt>
                <dd class="font-medium text-lg"><?= Helper::timeAgo($need['created_at']) ?> <span class="text-slate-400 text-sm"><?= Helper::e($need['created_at']) ?></span></dd>
            </div>
            <?php if ($need['assigned_name']): ?>
            <div>
                <dt class="flex items-center gap-1.5 text-slate-500 mb-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    ผู้รับผิดชอบ
                </dt>
                <dd class="font-medium text-lg"><?= Helper::e($need['assigned_name']) ?></dd>
            </div>
            <?php endif; ?>
        </dl>

        <?php if ($need['lat'] && $need['lng']): ?>
            <div id="detailMap" class="h-64 rounded-xl overflow-hidden border border-slate-200"></div>
            <p class="text-base text-slate-500">พิกัด: <?= number_format($need['lat'], 5) ?>, <?= number_format($need['lng'], 5) ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($history)): ?>
    <div class="card p-5">
        <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            ประวัติการดำเนินการ
        </h2>
        <ul class="space-y-4 text-base">
            <?php foreach ($history as $h): ?>
                <li class="border-l-[3px] border-brand-300 pl-4">
                    <div class="text-slate-700 font-medium">
                        <?php if ($h['old_status'] && $h['old_status'] !== $h['new_status']): ?>
                            <?= Helper::e($statusLabels[$h['old_status']] ?? $h['old_status']) ?> →
                        <?php endif; ?>
                        <strong><?= Helper::e($statusLabels[$h['new_status']] ?? $h['new_status']) ?></strong>
                    </div>
                    <?php if ($h['message']): ?>
                        <div class="text-slate-600 mt-0.5"><?= Helper::e($h['message']) ?></div>
                    <?php endif; ?>
                    <div class="text-sm text-slate-400 mt-0.5">
                        <?= Helper::timeAgo($h['created_at']) ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if (!in_array($need['status'], ['completed', 'cancelled'], true)): ?>
    <div class="card p-4 border-2 border-emerald-300 bg-emerald-50/60">
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

<?php if (!in_array($need['status'], ['completed', 'cancelled'], true)): ?>
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

<?php if ($need['lat'] && $need['lng']): ?>
<script>
// Lazy-init map only when scrolled into view (saves bandwidth on slow networks)
(function() {
    const el = document.getElementById('detailMap');
    if (!el || typeof L === 'undefined') return;
    let inited = false;
    function init() {
        if (inited) return;
        inited = true;
        const map = L.map(el).setView([<?= (float)$need['lat'] ?>, <?= (float)$need['lng'] ?>], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
        L.marker([<?= (float)$need['lat'] ?>, <?= (float)$need['lng'] ?>]).addTo(map);
    }
    if ('IntersectionObserver' in window) {
        const obs = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) { init(); obs.disconnect(); }
        }, { rootMargin: '200px' });
        obs.observe(el);
    } else {
        init();
    }
})();
</script>
<?php endif; ?>
