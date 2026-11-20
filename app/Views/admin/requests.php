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

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <?php
        $statCards = [
            '' => ['ทั้งหมด', $counts['all'] ?? 0, 'bg-slate-100 text-slate-700 border-slate-300', 'ring-slate-400', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            'pending' => ['รอรับเรื่อง', $counts['pending'] ?? 0, 'bg-red-50 text-red-700 border-red-200', 'ring-red-500', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            'accepted' => ['รับเรื่องแล้ว', $counts['accepted'] ?? 0, 'bg-blue-50 text-blue-700 border-blue-200', 'ring-blue-500', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            'in_progress' => ['กำลังช่วย', $counts['in_progress'] ?? 0, 'bg-amber-50 text-amber-700 border-amber-200', 'ring-amber-500', 'M13 10V3L4 14h7v7l9-11h-7z'],
            'completed' => ['สำเร็จ', $counts['completed'] ?? 0, 'bg-emerald-50 text-emerald-700 border-emerald-200', 'ring-emerald-500', 'M5 13l4 4L19 7'],
            'cancelled' => ['ยกเลิก', $counts['cancelled'] ?? 0, 'bg-slate-50 text-slate-500 border-slate-300', 'ring-slate-400', 'M6 18L18 6M6 6l12 12'],
        ];
        $curStatus = $filters['status'] ?? '';
        foreach ($statCards as $val => [$label, $count, $bgClass, $ringCls, $iconPath]): ?>
            <a href="<?= $qs(['status' => $val]) ?>"
               class="card border-2 hover:shadow-md transition <?= $curStatus === $val ? 'ring-2 ring-offset-1 ' . $ringCls . ' shadow-md' : '' ?> <?= $bgClass ?>">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 bg-white/80">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $iconPath ?>"/></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-2xl font-bold leading-none"><?= (int)$count ?></div>
                        <div class="text-xs font-medium truncate"><?= Helper::e($label) ?></div>
                    </div>
                </div>
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

    <!-- Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php if (empty($needs)): ?>
            <div class="col-span-full bg-white rounded-xl border border-slate-200 p-10 text-center text-slate-400">ไม่มีคำขอที่ตรงกับเงื่อนไข</div>
        <?php else: foreach ($needs as $n): ?>
            <div class="bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md transition relative flex flex-col">
                <!-- Checkbox top-right -->
                <div class="absolute top-3 right-3">
                    <input type="checkbox" class="case-check rounded border-slate-300 w-4 h-4" value="<?= (int)$n['id'] ?>" aria-label="เลือกรายการ <?= Helper::e($n['code']) ?>">
                </div>

                <!-- Header: code + status -->
                <div class="flex items-start gap-3 mb-3 pr-6">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 <?= Helper::statusBgColor($n['status']) ?>">
                        <?= Helper::statusIcon($n['status']) ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-semibold text-slate-800"><?= Helper::e($n['code']) ?></div>
                        <div class="text-xs text-slate-500"><?= Helper::timeAgo($n['created_at']) ?></div>
                    </div>
                </div>

                <!-- Urgency badge -->
                <?php if ($n['urgency'] !== 'normal'): ?>
                    <div class="mb-2">
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full font-medium <?= Helper::urgencyColor($n['urgency']) ?>">
                            <?= Helper::urgencyIcon($n['urgency']) ?> <?= Helper::e($urgencyLabels[$n['urgency']] ?? '') ?>
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Attached photo: latest work-log photo, fallback to report photo -->
                <?php $cardPhoto = $n['latest_work_photo'] ?? null; if (!$cardPhoto && !empty($n['photo_path'])) $cardPhoto = $n['photo_path']; ?>
                <?php if ($cardPhoto): ?>
                    <a href="<?= Helper::url('admin/requests/' . $n['id']) ?>" class="block mb-3 relative rounded-lg overflow-hidden border border-slate-200">
                        <img src="<?= Helper::e(Helper::photoUrl($cardPhoto)) ?>" alt="รูปที่แนบ" loading="lazy" class="h-24 w-full object-cover bg-slate-100">
                        <span class="absolute bottom-1 left-1 inline-flex items-center gap-1 text-[11px] font-medium bg-black/60 text-white px-1.5 py-0.5 rounded">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <?= !empty($n['latest_work_photo']) ? 'รูปหน้างาน' : 'รูปแจ้งเหตุ' ?>
                        </span>
                    </a>
                <?php endif; ?>

                <!-- Location -->
                <div class="flex items-center gap-2 text-sm text-slate-600 mb-2">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    <span class="truncate"><?= Helper::e($n['area_name'] ?? '-') ?></span>
                </div>

                <!-- Need types with icons -->
                <div class="flex flex-wrap gap-1.5 mb-3">
                    <?php foreach (array_slice($n['need_types'], 0, 3) as $t): ?>
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg bg-slate-100 text-slate-700">
                            <?= Helper::needTypeIcon($t) ?> <?= Helper::e(Helper::needTypeLabel($t)) ?>
                        </span>
                    <?php endforeach; ?>
                    <?php if (count($n['need_types']) > 3): ?><span class="text-xs text-slate-400">+<?= count($n['need_types']) - 3 ?></span><?php endif; ?>
                </div>

                <!-- People count + assignee -->
                <div class="flex items-center justify-between text-sm text-slate-500 border-t border-slate-100 pt-3 mb-3">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span><?= (int)$n['people_count'] ?> คน</span>
                    </div>
                    <div class="truncate max-w-[120px]">
                        <?= Helper::e($n['assigned_name'] ?? '—') ?>
                    </div>
                </div>

                <!-- Action button -->
                <a href="<?= Helper::url('admin/requests/' . $n['id']) ?>" class="mt-auto w-full btn-primary text-center text-sm py-2.5">
                    จัดการ
                </a>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (($pagination['pages'] ?? 1) > 1):
        $cur = $pagination['page']; $pages = $pagination['pages']; ?>
        <div class="flex items-center justify-center gap-2">
            <?php if ($cur > 1): ?><a href="<?= Helper::e($pageUrl($cur - 1)) ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50">← ก่อนหน้า</a><?php endif; ?>
            <span class="text-sm text-slate-500 px-3">หน้า <?= $cur ?> / <?= $pages ?></span>
            <?php if ($cur < $pages): ?><a href="<?= Helper::e($pageUrl($cur + 1)) ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50">ถัดไป →</a><?php endif; ?>
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
    if (!bar) return;

    function selected() {
        return Array.from(document.querySelectorAll('.case-check:checked')).map(c => c.value);
    }
    function refresh() {
        const n = selected().length;
        countEl.textContent = n;
        bar.classList.toggle('hidden', n === 0);
    }
    checks.forEach(c => c.addEventListener('change', refresh));
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
