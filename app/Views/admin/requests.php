<?php
/** Admin: request management list. Expects $needs, $counts, $pagination, $filters, $statusLabels, $urgencyLabels, $needTypes */
$qs = function(array $over = []) use ($filters) {
    $p = array_filter(array_merge($filters, $over), fn($v) => $v !== '' && $v !== null);
    return Helper::url('admin/requests' . ($p ? '?' . http_build_query($p) : ''));
};
$pageUrl = function(int $p) use ($filters) {
    $params = array_filter(array_merge($filters, ['page' => $p]), fn($v) => $v !== '' && $v !== null);
    return Helper::url('admin/requests' . ($params ? '?' . http_build_query($params) : ''));
};
$exportParams = array_filter($filters, fn($v) => $v !== '' && $v !== null);
$exportUrl = Helper::url('admin/requests/export' . ($exportParams ? '?' . http_build_query($exportParams) : ''));
?>
<div class="space-y-5 lg:space-y-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl lg:text-3xl font-semibold text-slate-800 flex items-center gap-2.5">
                <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                คำขอความช่วยเหลือ
            </h1>
            <p class="text-slate-500 mt-1">ทั้งหมด <?= (int)($counts['all'] ?? 0) ?> รายการ</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="<?= Helper::e($exportUrl) ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                ส่งออก CSV
            </a>
            <a href="<?= Helper::url('admin/requests/create') ?>" class="btn-primary px-4 py-2.5 text-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                เพิ่มเคส
            </a>
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
                        <th class="px-3 py-3 w-10"><input type="checkbox" id="checkAll" class="rounded border-slate-300" aria-label="เลือกทั้งหมด"></th>
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
                        <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">ไม่มีคำขอที่ตรงกับเงื่อนไข</td></tr>
                    <?php else: foreach ($needs as $n): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-3 py-3"><input type="checkbox" class="case-check rounded border-slate-300" value="<?= (int)$n['id'] ?>" aria-label="เลือกรายการ <?= Helper::e($n['code']) ?>"></td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800"><?= Helper::e($n['code']) ?></div>
                                <div class="text-slate-500"><?= Helper::e($n['reporter_name']) ?> · <?= (int)$n['people_count'] ?> คน</div>
                                <?php if (!empty($n['scenario'])): ?>
                                    <span class="inline-block mt-1 text-[11px] px-1.5 py-0.5 rounded bg-brand-50 text-brand-700 font-medium"><?= Helper::e(Helper::scenarioLabels()[$n['scenario']] ?? $n['scenario']) ?></span>
                                <?php endif; ?>
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
                                <span class="text-xs px-2 py-1 rounded-full font-medium inline-flex items-center gap-1.5 <?= Helper::statusColor($n['status']) ?>"><?= Helper::statusDot($n['status']) ?><?= Helper::e($statusLabels[$n['status']] ?? $n['status']) ?></span>
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
        $cur = $pagination['page']; $pages = $pagination['pages']; ?>
        <div class="flex items-center justify-center gap-2">
            <?php if ($cur > 1): ?><a href="<?= Helper::e($pageUrl($cur - 1)) ?>" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">← ก่อนหน้า</a><?php endif; ?>
            <span class="text-sm text-slate-500">หน้า <?= $cur ?> / <?= $pages ?></span>
            <?php if ($cur < $pages): ?><a href="<?= Helper::e($pageUrl($cur + 1)) ?>" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">ถัดไป →</a><?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Bulk action bar -->
<div id="bulkBar" class="hidden fixed bottom-5 left-1/2 -translate-x-1/2 z-30 bg-slate-900 text-white rounded-xl shadow-2xl px-4 py-3 flex flex-wrap items-center justify-center gap-2 max-w-[94vw]">
    <span class="text-sm font-medium whitespace-nowrap"><span id="bulkCount">0</span> รายการที่เลือก</span>
    <select id="bulkStatus" class="bg-slate-800 border border-slate-700 rounded-lg text-sm px-2 py-1.5 text-white">
        <option value="">เปลี่ยนสถานะเป็น...</option>
        <?php foreach ($statusLabels as $k => $v): ?>
            <option value="<?= $k ?>"><?= Helper::e($v) ?></option>
        <?php endforeach; ?>
    </select>
    <select id="bulkAssignee" class="bg-slate-800 border border-slate-700 rounded-lg text-sm px-2 py-1.5 text-white max-w-[10rem]">
        <option value="">มอบหมายให้...</option>
        <?php foreach ($users as $u): ?>
            <option value="<?= (int)$u['id'] ?>"><?= Helper::e($u['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="button" id="bulkApply" class="btn-primary text-sm px-4 py-1.5">ดำเนินการ</button>
    <button type="button" id="bulkClear" class="text-slate-400 hover:text-white text-sm px-2">ล้าง</button>
</div>

<script>
(function() {
    const checks = document.querySelectorAll('.case-check');
    const bar = document.getElementById('bulkBar');
    const countEl = document.getElementById('bulkCount');
    const checkAll = document.getElementById('checkAll');
    if (!bar) return;

    function selected() {
        return Array.from(document.querySelectorAll('.case-check:checked')).map(c => c.value);
    }
    function refresh() {
        const n = selected().length;
        countEl.textContent = n;
        bar.classList.toggle('hidden', n === 0);
        checkAll.checked = n > 0 && n === checks.length;
        checkAll.indeterminate = n > 0 && n < checks.length;
    }
    checks.forEach(c => c.addEventListener('change', refresh));
    checkAll.addEventListener('change', () => {
        checks.forEach(c => { c.checked = checkAll.checked; });
        refresh();
    });
    document.getElementById('bulkClear').addEventListener('click', () => {
        checks.forEach(c => { c.checked = false; });
        refresh();
    });
    document.getElementById('bulkApply').addEventListener('click', async () => {
        const ids = selected();
        if (!ids.length) return;
        const fd = new FormData();
        fd.append('csrf_token', window.csrfToken);
        const st = document.getElementById('bulkStatus').value;
        const asg = document.getElementById('bulkAssignee').value;
        if (st) {
            fd.append('bulk_action', 'status');
            fd.append('status', st);
        } else if (asg) {
            fd.append('bulk_action', 'assign');
            fd.append('user_id', asg);
        } else {
            Toast.show('เลือกการกระทำก่อน', 'err');
            return;
        }
        if (!confirm('ยืนยันดำเนินการกับ ' + ids.length + ' รายการ?')) return;
        ids.forEach(i => fd.append('ids[]', i));
        const btn = document.getElementById('bulkApply');
        btn.disabled = true;
        try {
            const res = await fetch(window.BASE_PATH + '/admin/requests/bulk', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            });
            const json = await res.json();
            if (json.ok) {
                Toast.show(json.message || 'สำเร็จ', 'ok');
                setTimeout(() => location.reload(), 600);
            } else {
                Toast.show(json.message || 'ไม่สำเร็จ', 'err');
            }
        } catch (e) {
            Toast.show('เครือข่ายมีปัญหา', 'err');
        }
        btn.disabled = false;
    });
})();
</script>
