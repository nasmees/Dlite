<?php
/**
 * Volunteer dashboard — demo-style: profile header, stat grid,
 * my cases with quick actions, open pool.
 * Expects: $user, $profile, $stats, $mineActive, $mineDone, $pool,
 *          $statusLabels, $urgencyLabels, $scenarioLabels, $needTypes, $csrf
 */
$avail = $profile['availability'] ?? 'available';
$isAvailable = $avail === 'available';
$initial = mb_substr(trim($user['name'] ?? 'อ'), 0, 1);
$poolCount = count($pool);
$statCards = [
    ['label' => 'เคสที่ดูแล',   'num' => (int)$stats['active'],     'icon' => 'fa-clipboard-list', 'chip' => 'bg-blue-50 text-blue-600',    'numCls' => 'text-blue-600'],
    ['label' => 'กำลังช่วย',     'num' => (int)$stats['working'],    'icon' => 'fa-person-running', 'chip' => 'bg-amber-50 text-amber-600',  'numCls' => 'text-amber-600'],
    ['label' => 'เสร็จวันนี้',    'num' => (int)$stats['done_today'], 'icon' => 'fa-circle-check',   'chip' => 'bg-emerald-50 text-emerald-600','numCls' => 'text-emerald-600'],
    ['label' => 'เคสเปิดรับ',    'num' => $poolCount,                'icon' => 'fa-inbox',          'chip' => 'bg-violet-50 text-violet-600','numCls' => 'text-violet-600'],
];
$nextStatusMap = ['pending' => 'accepted', 'accepted' => 'in_progress', 'in_progress' => 'completed'];
$nextStatusLabel = ['accepted' => 'รับเรื่อง', 'in_progress' => 'เริ่มดำเนินการ', 'completed' => 'เสร็จสิ้น'];
?>
<div class="space-y-5">

    <!-- Profile header -->
    <div class="card p-4 flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xl font-bold shrink-0"><?= Helper::e($initial) ?></div>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-slate-800 truncate"><?= Helper::e($user['name'] ?? '') ?></p>
            <p class="text-sm <?= $isAvailable ? 'text-emerald-600' : 'text-slate-400' ?>"><?= $isAvailable ? 'พร้อมรับงาน' : 'พักจากการรับเคส' ?></p>
        </div>
        <button type="button" id="availToggle" data-avail="<?= $isAvailable ? 'busy' : 'available' ?>"
                class="shrink-0 text-sm font-bold rounded-xl px-4 py-2.5 transition <?= $isAvailable ? 'bg-slate-200 text-slate-600 hover:bg-slate-300' : 'bg-emerald-600 text-white hover:bg-emerald-700' ?>">
            <?= $isAvailable ? 'พัก' : 'กลับมารับงาน' ?>
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-3">
        <?php foreach ($statCards as $sc): ?>
        <div class="card p-4 flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl <?= $sc['chip'] ?> flex items-center justify-center shrink-0"><i class="fa-solid <?= $sc['icon'] ?> text-lg"></i></span>
            <span class="min-w-0">
                <span class="block font-extrabold text-2xl <?= $sc['numCls'] ?> leading-none"><?= $sc['num'] ?></span>
                <span class="block text-xs text-slate-500 mt-0.5"><?= Helper::e($sc['label']) ?></span>
            </span>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- My active cases -->
    <section id="mine">
        <h2 class="text-base font-semibold text-slate-800 mb-2.5 flex items-center gap-2">
            <i class="fa-solid fa-clipboard-list text-brand-600"></i> เคสของฉัน
            <?php if ($mineActive): ?><span class="text-sm text-slate-400 font-normal">(<?= count($mineActive) ?>)</span><?php endif; ?>
        </h2>
        <?php if (!$mineActive): ?>
            <div class="card p-5 text-center text-slate-400">ยังไม่มีเคสที่รับผิดชอบ — เลือกรับจากคลังงานด้านล่าง</div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($mineActive as $n):
                    $scenarioKey = $n['scenario'] ?? 'other';
                    $chip = Helper::scenarioChipColor($scenarioKey);
                    $icon = Helper::scenarioIcon($scenarioKey, 'w-6 h-6');
                    $title = Helper::scenarioLabel($scenarioKey) ?: 'เหตุการณ์';
                    $place = ($n['district'] ?? '') !== '' ? $n['district'] : ($n['area_name'] ?? '');
                    $loc = implode(' · ', array_values(array_unique(array_filter([$place, $n['province'] ?? '']))));
                    $people = (int)($n['people_count'] ?? 0);
                    $next = $nextStatusMap[$n['status']] ?? null;
                ?>
                <div class="card p-4">
                    <a href="<?= Helper::url('volunteer/cases/' . (int)$n['id']) ?>" class="flex items-start gap-3">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 <?= $chip ?>"><?= $icon ?></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-bold text-slate-800 leading-tight"><?= Helper::e($title) ?></p>
                                <?php if (($n['urgency'] ?? 'normal') !== 'normal'): ?>
                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold <?= Helper::urgencyChipColor($n['urgency']) ?>"><?= Helper::e($urgencyLabels[$n['urgency']] ?? $n['urgency']) ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-slate-500 mt-1">
                                <?= Helper::e($n['code']) ?><?= $loc !== '' ? ' · ' . Helper::e($loc) : '' ?><?= $people > 0 ? ' · ~' . $people . ' คน' : '' ?> · <?= Helper::timeAgo($n['created_at']) ?>
                            </p>
                        </div>
                        <span class="shrink-0 inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-semibold <?= Helper::statusColor($n['status']) ?>">
                            <?= Helper::statusDot($n['status']) ?><?= Helper::e($statusLabels[$n['status']] ?? $n['status']) ?>
                        </span>
                    </a>
                    <div class="flex gap-2 mt-3">
                        <?php if ($next): ?>
                        <button type="button" class="quick-status flex-1 text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl py-2.5 transition"
                                data-id="<?= (int)$n['id'] ?>" data-status="<?= $next ?>">
                            <i class="fa-solid <?= $next === 'completed' ? 'fa-flag-checkered' : ($next === 'in_progress' ? 'fa-person-running' : 'fa-check') ?> mr-1"></i><?= Helper::e($nextStatusLabel[$next]) ?>
                        </button>
                        <?php endif; ?>
                        <a href="<?= Helper::url('volunteer/cases/' . (int)$n['id']) ?>" class="flex-1 text-sm font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 rounded-xl py-2.5 text-center transition">
                            <i class="fa-solid fa-camera mr-1"></i> อัปเดตหน้างาน
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($mineDone): ?>
            <details class="mt-3">
                <summary class="text-sm font-semibold text-slate-500 cursor-pointer py-2">เคสที่เสร็จ/ยกเลิก (<?= count($mineDone) ?>)</summary>
                <div class="space-y-2 mt-2">
                    <?php foreach ($mineDone as $n): $showClaim = false; require APP_PATH . '/Views/volunteer/_case_row.php'; endforeach; ?>
                </div>
            </details>
        <?php endif; ?>
    </section>

    <!-- Open pool -->
    <section id="open">
        <div class="flex items-center justify-between gap-2 mb-2.5">
            <h2 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-hand-holding-hand text-emerald-600"></i> คลังงานที่รอรับ
                <span class="text-sm text-slate-400 font-normal" id="poolCount">(<?= $poolCount ?>)</span>
            </h2>
            <?php if ($pool): ?>
            <button type="button" id="nearMeBtn"
                    class="shrink-0 inline-flex items-center gap-1.5 text-sm font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 rounded-xl px-3.5 py-2 transition">
                <i class="fa-solid fa-location-dot"></i>
                <span id="nearMeLabel">เคสใกล้ฉัน</span>
            </button>
            <?php endif; ?>
        </div>
        <?php if (!$pool): ?>
            <div class="card p-5 text-center text-slate-400">ไม่มีเคสเปิดรับในขณะนี้</div>
        <?php else: ?>
            <div class="space-y-3" id="poolList">
                <?php foreach ($pool as $n): require APP_PATH . '/Views/volunteer/_pool_card.php'; endforeach; ?>
            </div>
            <p id="nearMeNote" class="hidden mt-2 text-xs text-slate-400 text-center"></p>
        <?php endif; ?>
    </section>
</div>

<script>
document.getElementById('availToggle')?.addEventListener('click', async (e) => {
    const btn = e.currentTarget;
    btn.disabled = true;
    await window.staffAction('/volunteer/availability', { availability: btn.dataset.avail }, { reload: false });
    btn.disabled = false;
    location.reload();
});

document.querySelectorAll('.claim-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        btn.disabled = true;
        btn.textContent = '...';
        await window.staffAction('/volunteer/cases/' + btn.dataset.id + '/claim', {});
    });
});

document.querySelectorAll('.quick-status').forEach(btn => {
    btn.addEventListener('click', async () => {
        btn.disabled = true;
        const old = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>กำลังส่ง…';
        const json = await window.staffAction('/volunteer/cases/' + btn.dataset.id + '/status',
            { status: btn.dataset.status }, { reload: false });
        if (json && json.ok) {
            // move to next stage without full reload for snappier feel
            setTimeout(() => location.reload(), 500);
        } else {
            btn.disabled = false;
            btn.innerHTML = old;
        }
    });
});

// "เคสใกล้ฉัน" — GPS sort by distance
(function() {
    const btn = document.getElementById('nearMeBtn');
    if (!btn) return;
    const label = document.getElementById('nearMeLabel');
    const note = document.getElementById('nearMeNote');

    function haversineKm(aLat, aLng, bLat, bLng) {
        const R = 6371, rad = Math.PI / 180;
        const dLat = (bLat - aLat) * rad, dLng = (bLng - aLng) * rad;
        const s = Math.sin(dLat / 2) ** 2 + Math.cos(aLat * rad) * Math.cos(bLat * rad) * Math.sin(dLng / 2) ** 2;
        return 2 * R * Math.asin(Math.sqrt(s));
    }
    function fmtKm(km) {
        return km < 1 ? Math.round(km * 1000) + ' ม.' : km.toFixed(1) + ' กม.';
    }

    btn.addEventListener('click', () => {
        if (!navigator.geolocation) {
            Toast.show('อุปกรณ์ไม่รองรับ GPS', 'err');
            return;
        }
        btn.disabled = true;
        label.textContent = 'กำลังหา...';
        navigator.geolocation.getCurrentPosition(pos => {
            const myLat = pos.coords.latitude, myLng = pos.coords.longitude;
            const list = document.getElementById('poolList');
            const cards = Array.from(list.querySelectorAll('.pool-card'));
            const withLoc = [], noLoc = [];

            cards.forEach(c => {
                const la = parseFloat(c.dataset.lat), ln = parseFloat(c.dataset.lng);
                const badge = c.querySelector('.near-dist');
                if (!isNaN(la) && !isNaN(ln)) {
                    const d = haversineKm(myLat, myLng, la, ln);
                    c.dataset.dist = d;
                    if (badge) {
                        badge.textContent = '≈ ' + fmtKm(d);
                        badge.classList.remove('hidden');
                    }
                    withLoc.push(c);
                } else {
                    if (badge) badge.remove();
                    noLoc.push(c);
                }
            });

            withLoc.sort((a, b) => a.dataset.dist - b.dataset.dist);
            [...withLoc, ...noLoc].forEach(c => list.appendChild(c));

            const nearest = withLoc.length ? ' · ใกล้สุด ' + fmtKm(withLoc[0].dataset.dist) : '';
            note.textContent = 'เรียงตามระยะทางจากตำแหน่งคุณ ' + withLoc.length + ' เคส' + nearest +
                (noLoc.length ? ' · อีก ' + noLoc.length + ' เคสไม่มีพิกัด' : '');
            note.classList.remove('hidden');
            label.textContent = '✓ เรียงแล้ว';
            btn.classList.add('bg-emerald-100', 'text-emerald-800', 'border-emerald-300');
        }, () => {
            Toast.show('ไม่สามารถระบุตำแหน่งได้ — ตรวจสอบว่าเปิด GPS', 'err');
            btn.disabled = false;
            label.textContent = 'เคสใกล้ฉัน';
        }, { timeout: 10000, maximumAge: 60000 });
    });
})();
</script>
