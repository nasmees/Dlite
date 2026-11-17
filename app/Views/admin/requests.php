<?php
/** Admin: request management list. Expects $needs, $counts, $pagination, $filters, $statusLabels, $urgencyLabels, $needTypes */
$qs = function(array $over = []) use ($filters) {
    $p = array_filter(array_merge($filters, $over), fn($v) => $v !== '' && $v !== null);
    return Helper::url('admin/requests' . ($p ? '?' . http_build_query($p) : ''));
};
?>
<div class="space-y-5 lg:space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl lg:text-3xl font-semibold text-slate-800">คำขอความช่วยเหลือ</h1>
            <p class="text-slate-500 mt-1">ทั้งหมด <?= (int)($counts['all'] ?? 0) ?> รายการ</p>
        </div>
    </div>

    <!-- Status quick filters -->
    <div class="flex gap-2 overflow-x-auto pb-2 -mx-1 px-1" role="navigation" aria-label="กรองตามสถานะ">
        <?php
        $tabs = [
            '' => ['ทั้งหมด', $counts['all'] ?? 0],
            'pending' => ['รอรับเรื่อง', $counts['pending'] ?? 0],
            'accepted' => ['รับเรื่องแล้ว', $counts['accepted'] ?? 0],
            'in_progress' => ['กำลังช่วย', $counts['in_progress'] ?? 0],
            'completed' => ['สำเร็จ', $counts['completed'] ?? 0],
            'cancelled' => ['ยกเลิก', $counts['cancelled'] ?? 0],
        ];
        foreach ($tabs as $val => [$label, $count]): ?>
            <a href="<?= $qs(['status' => $val]) ?>"
               aria-current="<?= ($filters['status'] ?? '') === $val ? 'page' : 'false' ?>"
               class="shrink-0 relative px-4 py-2 rounded-full text-sm font-medium transition <?= ($filters['status'] ?? '') === $val ? 'bg-brand-100 text-brand-800' : 'bg-white border border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50' ?>">
                <?= Helper::e($label) ?>
                <span class="ml-1.5 text-xs opacity-80"><?= (int)$count ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Search + urgency filter -->
    <form method="get" class="flex flex-col sm:flex-row gap-2">
        <?php if (($filters['status'] ?? '') !== ''): ?><input type="hidden" name="status" value="<?= Helper::e($filters['status']) ?>"><?php endif; ?>
        <input type="search" name="q" value="<?= Helper::e($filters['q'] ?? '') ?>" placeholder="ค้นหา ชื่อ / รหัส / ที่อยู่ / รายละเอียด..." class="input-field flex-1">
        <select name="urgency" class="input-field w-full sm:w-44" onchange="this.form.submit()">
            <option value="">ทุกความเร่งด่วน</option>
            <?php foreach ($urgencyLabels as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($filters['urgency'] ?? '') === $k ? 'selected' : '' ?>><?= Helper::e($v) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-primary px-5 shrink-0">ค้นหา</button>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left">
                        <th class="px-4 py-3 font-medium text-slate-600">รหัส / ผู้แจ้ง</th>
                        <th class="px-4 py-3 font-medium text-slate-600">พื้นที่</th>
                        <th class="px-4 py-3 font-medium text-slate-600">ความต้องการ</th>
                        <th class="px-4 py-3 font-medium text-slate-600">สถานะ</th>
                        <th class="px-4 py-3 font-medium text-slate-600">ผู้รับผิดชอบ</th>
                        <th class="px-4 py-3 font-medium text-slate-600">เวลา</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($needs)): ?>
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">ไม่มีคำขอที่ตรงกับเงื่อนไข</td></tr>
                    <?php else: foreach ($needs as $n): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800"><?= Helper::e($n['code']) ?></div>
                                <div class="text-slate-500"><?= Helper::e($n['reporter_name']) ?> · <?= (int)$n['people_count'] ?> คน</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <?= Helper::e($n['area_name'] ?? '-') ?>
                                <?php if ($n['address']): ?><div class="text-xs text-slate-400 truncate max-w-[160px]"><?= Helper::e($n['address']) ?></div><?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1 max-w-[200px]">
                                    <?php foreach (array_slice($n['need_types'], 0, 2) as $t): ?>
                                        <span class="text-xs bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded"><?= Helper::e($needTypes[$t] ?? $t) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($n['need_types']) > 2): ?><span class="text-xs text-slate-400">+<?= count($n['need_types']) - 2 ?></span><?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded-full font-medium <?= Helper::statusColor($n['status']) ?>"><?= Helper::e($statusLabels[$n['status']] ?? $n['status']) ?></span>
                                <?php if ($n['urgency'] !== 'normal'): ?>
                                    <div class="mt-1"><span class="text-xs px-2 py-0.5 rounded-full <?= Helper::urgencyColor($n['urgency']) ?>"><?= Helper::e($urgencyLabels[$n['urgency']] ?? '') ?></span></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600"><?= Helper::e($n['assigned_name'] ?? '—') ?></td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap"><?= Helper::timeAgo($n['created_at']) ?></td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?= Helper::url('admin/requests/' . $n['id']) ?>" class="text-brand-600 font-medium hover:underline text-sm">จัดการ →</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if (($pagination['pages'] ?? 1) > 1):
        $baseParams = array_filter(['status' => $filters['status'] ?? '', 'urgency' => $filters['urgency'] ?? '', 'q' => $filters['q'] ?? '']);
        $cur = $pagination['page']; $pages = $pagination['pages']; ?>
        <div class="flex items-center justify-center gap-2">
            <?php if ($cur > 1): ?><a href="<?= Helper::e(Helper::pageUrl($cur - 1, $baseParams)) ?>" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">← ก่อนหน้า</a><?php endif; ?>
            <span class="text-sm text-slate-500">หน้า <?= $cur ?> / <?= $pages ?></span>
            <?php if ($cur < $pages): ?><a href="<?= Helper::e(Helper::pageUrl($cur + 1, $baseParams)) ?>" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">ถัดไป →</a><?php endif; ?>
        </div>
    <?php endif; ?>
</div>
