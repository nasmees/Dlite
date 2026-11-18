<?php
/**
 * Volunteer case detail — full staff view + action bar.
 * Expects: $need, $history, $user, $canAct, $canClaim, $nextStatus,
 *          $volunteers, $statusLabels, $urgencyLabels, $scenarioLabels,
 *          $needTypes, $csrf
 */
$scenarioKey = $need['scenario'] ?? 'other';
$place = ($need['district'] ?? '') !== '' ? $need['district'] : ($need['area_name'] ?? '');
$loc = implode(' · ', array_values(array_unique(array_filter([$place, $need['province'] ?? '']))));
$status = $need['status'];
$isFinal = in_array($status, ['completed', 'cancelled'], true);
$nextBtn = [
    'accepted' => ['label' => 'รับเรื่อง', 'cls' => 'bg-blue-600 hover:bg-blue-700'],
    'in_progress' => ['label' => 'เริ่มลงพื้นที่', 'cls' => 'bg-indigo-600 hover:bg-indigo-700'],
    'completed' => ['label' => 'ปิดเคส (สำเร็จ)', 'cls' => 'bg-emerald-600 hover:bg-emerald-700'],
];
$canRelease = $canAct && !$isFinal;
$canTransfer = $canAct && !$isFinal && count($volunteers) > 1;
?>
<div class="space-y-4 pb-4">

    <!-- Header card -->
    <div class="card p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 <?= Helper::scenarioChipColor($scenarioKey) ?>">
                    <?= Helper::scenarioIcon($scenarioKey, 'w-6 h-6') ?>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl font-bold text-slate-900"><?= Helper::e(Helper::scenarioLabel($scenarioKey) ?: 'เหตุการณ์') ?></h1>
                    <p class="text-sm text-slate-500"><?= Helper::e($need['code']) ?> · <?= Helper::timeAgo($need['created_at']) ?></p>
                </div>
            </div>
            <div class="flex flex-col items-end gap-1.5 shrink-0">
                <span class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-full font-semibold <?= Helper::statusColor($status) ?>">
                    <?= Helper::statusDot($status) ?><?= Helper::e($statusLabels[$status] ?? $status) ?>
                </span>
                <?php if (($need['urgency'] ?? 'normal') !== 'normal'): ?>
                    <span class="text-sm px-3 py-1.5 rounded-full font-semibold <?= Helper::urgencyChipColor($need['urgency']) ?>"><?= Helper::e($urgencyLabels[$need['urgency']] ?? $need['urgency']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-base mt-4">
            <div>
                <dt class="text-slate-500 text-sm">ผู้แจ้ง</dt>
                <dd class="font-semibold"><?= Helper::e($need['reporter_name']) ?></dd>
            </div>
            <div>
                <dt class="text-slate-500 text-sm">เบอร์โทร</dt>
                <dd class="font-semibold">
                    <?php if ($need['reporter_phone']): ?>
                        <a href="tel:<?= preg_replace('/\D/', '', $need['reporter_phone']) ?>" class="text-brand-600"><?= Helper::e(Helper::formatPhone($need['reporter_phone'])) ?></a>
                    <?php else: ?>-<?php endif; ?>
                </dd>
            </div>
            <div>
                <dt class="text-slate-500 text-sm">พื้นที่</dt>
                <dd class="font-semibold"><?= Helper::e($loc !== '' ? $loc : '-') ?></dd>
            </div>
            <?php if ($need['address']): ?>
            <div class="sm:col-span-2">
                <dt class="text-slate-500 text-sm">ที่อยู่</dt>
                <dd><?= Helper::e($need['address']) ?></dd>
            </div>
            <?php endif; ?>
            <div>
                <dt class="text-slate-500 text-sm">ผู้ได้รับผลกระทบ</dt>
                <dd class="font-semibold"><?= (int)$need['people_count'] ?> คน</dd>
            </div>
            <div>
                <dt class="text-slate-500 text-sm">ผู้รับผิดชอบ</dt>
                <dd class="font-semibold"><?= Helper::e($need['assigned_name'] ?? '— ยังไม่มี —') ?></dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-slate-500 text-sm mb-1">ความต้องการ</dt>
                <dd class="flex flex-wrap gap-1.5">
                    <?php foreach (($need['need_types'] ?? []) as $t): ?>
                        <span class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-lg font-medium <?= Helper::needTypeChipColor($t) ?>"><?= Helper::needTypeIcon($t, 'w-4 h-4') ?><?= Helper::e(Helper::needTypeLabel($t)) ?></span>
                    <?php endforeach; ?>
                    <?php if (empty($need['need_types'])): ?>-<?php endif; ?>
                </dd>
            </div>
            <?php if ($need['description']): ?>
            <div class="sm:col-span-2">
                <dt class="text-slate-500 text-sm">รายละเอียด</dt>
                <dd class="whitespace-pre-wrap"><?= Helper::e($need['description']) ?></dd>
            </div>
            <?php endif; ?>
        </dl>

        <?php if (!empty($need['photo_path'])): ?>
            <a href="<?= Helper::e(Helper::photoUrl($need['photo_path'])) ?>" target="_blank" rel="noopener" class="block mt-4">
                <img src="<?= Helper::e(Helper::photoUrl($need['photo_path'])) ?>" alt="รูปประกอบ" loading="lazy" class="w-full rounded-xl max-h-72 object-cover bg-slate-100">
            </a>
        <?php endif; ?>

        <?php if ($need['lat'] && $need['lng']): ?>
            <div id="caseMap" class="h-56 rounded-xl overflow-hidden border border-slate-200 mt-4"></div>
            <div class="flex items-center gap-3 mt-1">
                <p class="text-sm text-slate-500">พิกัด: <?= number_format((float)$need['lat'], 5) ?>, <?= number_format((float)$need['lng'], 5) ?></p>
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?= (float)$need['lat'] ?>,<?= (float)$need['lng'] ?>" target="_blank" rel="noopener" class="ml-auto inline-flex items-center gap-1.5 text-sm font-bold text-orange-600">
                    🧭 นำทาง
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Timeline (staff sees internal notes too) -->
    <?php if (!empty($history)): ?>
    <div class="card p-5">
        <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            ประวัติการดำเนินการ
        </h2>
        <ul class="space-y-4 text-base">
            <?php foreach ($history as $h): ?>
                <li class="border-l-[3px] <?= !empty($h['is_internal']) ? 'border-amber-300' : 'border-brand-300' ?> pl-4">
                    <div class="text-slate-700 font-medium">
                        <?php if ($h['old_status'] && $h['old_status'] !== $h['new_status']): ?>
                            <?= Helper::e($statusLabels[$h['old_status']] ?? $h['old_status']) ?> →
                        <?php endif; ?>
                        <strong><?= Helper::e($statusLabels[$h['new_status']] ?? $h['new_status']) ?></strong>
                        <?php if (!empty($h['is_internal'])): ?><span class="text-xs px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-semibold ml-1">ภายใน</span><?php endif; ?>
                    </div>
                    <?php if ($h['message']): ?>
                        <div class="text-slate-600 mt-0.5"><?= Helper::e($h['message']) ?></div>
                    <?php endif; ?>
                    <div class="text-sm text-slate-400 mt-0.5">
                        <?= Helper::timeAgo($h['created_at']) ?><?= !empty($h['user_name']) ? ' · ' . Helper::e($h['user_name']) : '' ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>

<!-- Sticky action bar (sits above bottom nav) -->
<?php if ($canClaim || ($canAct && !$isFinal) || $canRelease || $canTransfer): ?>
<div class="fixed bottom-[4.5rem] inset-x-0 z-[1001] safe-bottom">
    <div class="max-w-lg mx-auto px-4 pb-3">
        <div class="card !border-slate-300 shadow-lg p-3 space-y-2.5">
            <?php if ($canClaim): ?>
                <button type="button" id="actClaim" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl px-4 py-3.5 text-lg">รับเคสนี้</button>
            <?php endif; ?>

            <?php if ($canAct && !$isFinal): ?>
                <?php if ($nextStatus && isset($nextBtn[$nextStatus])): $nb = $nextBtn[$nextStatus]; ?>
                    <button type="button" id="actNext" data-status="<?= $nextStatus ?>" class="w-full <?= $nb['cls'] ?> text-white font-bold rounded-xl px-4 py-3.5 text-lg"><?= $nb['label'] ?></button>
                <?php endif; ?>

                <details class="group">
                    <summary class="text-center text-base font-semibold text-slate-500 cursor-pointer py-1.5 list-none">
                        ตัวเลือกอื่น ▾
                    </summary>
                    <div class="space-y-2.5 pt-2">
                        <?php if ($canRelease): ?>
                            <button type="button" id="actRelease" class="w-full bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl px-4 py-3">คืนเคสเข้ากองรวม</button>
                        <?php endif; ?>
                        <?php if ($canTransfer): ?>
                            <div class="rounded-xl border border-slate-200 p-3 space-y-2">
                                <p class="text-sm font-bold text-slate-600">โอนเคสให้</p>
                                <select id="transferSelect" class="input-field !text-base !py-2.5">
                                    <option value="">— เลือกอาสาสมัคร —</option>
                                    <?php foreach ($volunteers as $v): if ((int)$v['id'] === (int)$user['id']) continue; ?>
                                        <option value="<?= (int)$v['id'] ?>"><?= Helper::e($v['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" id="actTransfer" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-4 py-3">โอนเคส</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </details>
            <?php endif; ?>

            <?php if ($isFinal): ?>
                <p class="text-center text-base font-semibold text-slate-500 py-1">เคสนี้<?= $status === 'completed' ? 'เสร็จสิ้นแล้ว' : 'ถูกยกเลิก' ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="h-44"></div>
<?php endif; ?>

<?php if ($need['lat'] && $need['lng']): ?>
<script>
(function() {
    const el = document.getElementById('caseMap');
    if (!el || typeof L === 'undefined') return;
    let inited = false;
    function init() {
        if (inited) return; inited = true;
        const map = L.map(el).setView([<?= (float)$need['lat'] ?>, <?= (float)$need['lng'] ?>], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
        L.marker([<?= (float)$need['lat'] ?>, <?= (float)$need['lng'] ?>]).addTo(map);
    }
    if ('IntersectionObserver' in window) {
        const obs = new IntersectionObserver((es) => { if (es[0].isIntersecting) { init(); obs.disconnect(); } }, { rootMargin: '200px' });
        obs.observe(el);
    } else init();
})();
</script>
<?php endif; ?>

<script>
(function() {
    const id = <?= (int)$need['id'] ?>;
    const post = (path, fields) => window.staffAction('/volunteer/cases/' + id + path, fields);

    document.getElementById('actClaim')?.addEventListener('click', async (e) => {
        e.target.disabled = true; e.target.textContent = 'กำลังรับ...';
        await post('/claim', {});
    });
    document.getElementById('actNext')?.addEventListener('click', async (e) => {
        e.target.disabled = true;
        await post('/status', { status: e.target.dataset.status });
    });
    document.getElementById('actRelease')?.addEventListener('click', async (e) => {
        e.target.disabled = true; e.target.textContent = 'กำลังคืน...';
        await post('/release', {});
    });
    document.getElementById('actTransfer')?.addEventListener('click', async (e) => {
        const sel = document.getElementById('transferSelect');
        if (!sel.value) { window.Toast.show('กรุณาเลือกอาสาสมัคร', 'err'); return; }
        e.target.disabled = true;
        await post('/transfer', { to_user_id: sel.value });
    });
})();
</script>
