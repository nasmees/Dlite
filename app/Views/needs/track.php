<div class="space-y-5">
    <div class="text-center pt-1">
        <h1 class="text-xl font-semibold text-brand-800">ติดตามสถานะคำขอ</h1>
        <p class="text-sm text-slate-500 mt-1">กรอกรหัสอ้างอิงที่ได้รับหลังส่งคำขอ</p>
    </div>

    <form method="get" action="<?= Helper::url('track') ?>" class="card p-4 flex gap-2">
        <input type="text" name="code" value="<?= Helper::e($code) ?>" placeholder="เช่น DSC-1A2B3C4D"
               class="input-field flex-1 uppercase" style="text-transform:uppercase" autocomplete="off">
        <button type="submit" class="btn-primary px-5">ค้นหา</button>
    </form>

    <?php if ($code !== '' && $need === null): ?>
        <div class="card p-6 text-center">
            <p class="text-slate-600">ไม่พบรหัส <strong><?= Helper::e($code) ?></strong></p>
            <p class="text-sm text-slate-400 mt-1">กรุณาตรวจสอบรหัสอีกครั้ง หรือสอบถามทีมประสานงาน</p>
        </div>
    <?php endif; ?>

    <?php if ($need): ?>
    <div class="card p-4 space-y-3">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="font-semibold text-lg"><?= Helper::e($need['code']) ?></h2>
                <p class="text-sm text-slate-500">แจ้งเมื่อ <?= Helper::e($need['created_at']) ?></p>
            </div>
            <div class="flex flex-col items-end gap-1">
                <span class="text-sm px-2.5 py-1 rounded-full <?= Helper::statusColor($need['status']) ?>"><?= Helper::e($statusLabels[$need['status']] ?? $need['status']) ?></span>
                <?php if ($need['urgency'] !== 'normal'): ?>
                    <span class="text-sm px-2.5 py-1 rounded-full <?= Helper::urgencyColor($need['urgency']) ?>"><?= Helper::e($urgencyLabels[$need['urgency']] ?? $need['urgency']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-sm space-y-1">
            <?php if (!empty($need['need_types'])): ?>
                <div class="flex flex-wrap gap-1">
                    <?php foreach ($need['need_types'] as $t): ?>
                        <span class="text-sm bg-brand-50 text-brand-700 px-2 py-0.5 rounded-md"><?= Helper::e($needTypes[$t] ?? $t) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($need['area_name']): ?>
                <p class="text-slate-600">พื้นที่: <?= Helper::e($need['area_name']) ?><?= $need['province'] ? ' · ' . Helper::e($need['province']) : '' ?></p>
            <?php endif; ?>
            <?php if ($need['people_count'] > 1): ?>
                <p class="text-slate-600">ผู้ได้รับผลกระทบ <?= (int)$need['people_count'] ?> คน</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($history)): ?>
    <div class="card p-4">
        <h3 class="font-medium mb-3">ไทม์ไลน์สถานะ</h3>
        <ul class="space-y-3 text-sm">
            <?php $lastIdx = count($history) - 1; foreach ($history as $hi => $h): ?>
                <li class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-2.5 h-2.5 rounded-full <?= $hi === $lastIdx ? 'bg-brand-600' : 'bg-slate-300' ?> mt-1.5 shrink-0"></div>
                        <?php if ($hi < $lastIdx): ?><div class="w-px flex-1 bg-slate-200 mt-1"></div><?php endif; ?>
                    </div>
                    <div class="pb-1">
                        <div class="text-slate-700">
                            <?php if ($h['old_status']): ?>
                                <?= Helper::e($statusLabels[$h['old_status']] ?? $h['old_status']) ?> →
                            <?php endif; ?>
                            <strong><?= Helper::e($statusLabels[$h['new_status']] ?? $h['new_status']) ?></strong>
                        </div>
                        <?php if ($h['message']): ?>
                            <div class="text-slate-500 text-sm mt-0.5"><?= Helper::e($h['message']) ?></div>
                        <?php endif; ?>
                        <div class="text-sm text-slate-400 mt-0.5"><?= Helper::e($h['created_at']) ?></div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <p class="text-sm text-center text-slate-400">หากมีข้อสงสัย ติดต่อทีมประสานงานโดยใช้รหัส <?= Helper::e($need['code']) ?></p>
    <?php endif; ?>
</div>
