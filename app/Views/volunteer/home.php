<?php
/**
 * Volunteer dashboard — my cases + open pool.
 * Expects: $user, $profile, $stats, $mineActive, $mineDone, $pool,
 *          $statusLabels, $urgencyLabels, $scenarioLabels, $needTypes, $csrf
 */
$avail = $profile['availability'] ?? 'available';
$isAvailable = $avail === 'available';
?>
<div class="space-y-5">

    <!-- Page header (admin style) -->
    <div>
        <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2.5">
            <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            งานอาสาสมัคร
        </h1>
        <p class="text-slate-500 mt-1">จัดการเคสที่ได้รับมอบหมายและรับเคสจากกองรวม</p>
    </div>

    <!-- Availability toggle -->
    <div class="card p-4 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <span class="w-11 h-11 rounded-full flex items-center justify-center shrink-0 <?= $isAvailable ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-200 text-slate-500' ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-base font-bold text-slate-800"><?= $isAvailable ? 'ว่างรับงาน' : 'ไม่ว่าง' ?></p>
                <p class="text-sm text-slate-500"><?= $isAvailable ? 'พร้อมรับเคสใหม่' : 'พักจากการรับเคส' ?></p>
            </div>
        </div>
        <button type="button" id="availToggle" data-avail="<?= $isAvailable ? 'busy' : 'available' ?>"
                class="shrink-0 text-base font-bold rounded-xl px-5 py-3 transition <?= $isAvailable ? 'bg-slate-200 text-slate-600 hover:bg-slate-300' : 'bg-emerald-600 text-white hover:bg-emerald-700' ?>">
            <?= $isAvailable ? 'พัก' : 'กลับมารับงาน' ?>
        </button>
    </div>

    <!-- Stats (admin chips) -->
    <div class="flex flex-wrap gap-2">
        <div class="admin-stat"><span class="num"><?= (int)$stats['active'] ?></span><span class="lbl">เคสที่ดูแล</span></div>
        <div class="admin-stat"><span class="num"><?= (int)$stats['working'] ?></span><span class="lbl">กำลังช่วย</span></div>
        <div class="admin-stat"><span class="num"><?= (int)$stats['done_today'] ?></span><span class="lbl">เสร็จวันนี้</span></div>
    </div>

    <!-- My active cases -->
    <section id="mine">
        <h2 class="text-lg font-semibold text-slate-800 mb-2.5 flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            งานของฉัน
            <?php if ($mineActive): ?><span class="text-sm text-slate-400 font-normal">(<?= count($mineActive) ?>)</span><?php endif; ?>
        </h2>
        <?php if (!$mineActive): ?>
            <div class="card p-5 text-center text-slate-400 text-base">ยังไม่มีเคสที่รับผิดชอบ — เลือกรับจากเคสเปิดรับด้านล่าง</div>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($mineActive as $n): $showClaim = false; require APP_PATH . '/Views/volunteer/_case_row.php'; endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($mineDone): ?>
            <details class="mt-3">
                <summary class="text-base font-semibold text-slate-500 cursor-pointer py-2">เคสที่เสร็จ/ยกเลิก (<?= count($mineDone) ?>)</summary>
                <div class="space-y-2 mt-2">
                    <?php foreach ($mineDone as $n): $showClaim = false; require APP_PATH . '/Views/volunteer/_case_row.php'; endforeach; ?>
                </div>
            </details>
        <?php endif; ?>
    </section>

    <!-- Open pool -->
    <section id="open">
        <h2 class="text-lg font-semibold text-slate-800 mb-2.5 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4"/></svg>
            เคสเปิดรับ
            <span class="text-sm text-slate-400 font-normal">(<?= count($pool) ?>)</span>
        </h2>
        <?php if (!$pool): ?>
            <div class="card p-5 text-center text-slate-400 text-base">ไม่มีเคสเปิดรับในขณะนี้ 🎉</div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($pool as $n): require APP_PATH . '/Views/volunteer/_pool_card.php'; endforeach; ?>
            </div>
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
</script>
