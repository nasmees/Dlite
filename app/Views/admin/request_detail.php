<?php
/** Admin: single request management. Expects $need, $history, $helps, $helpTypes, $users, labels, $csrf */
$status = $need['status'];
$closed = in_array($status, ['completed', 'cancelled'], true);
$canAccept = in_array($status, ['pending'], true);
$canWork = in_array($status, ['accepted', 'in_progress'], true);
$canClose = in_array($status, ['accepted', 'in_progress'], true);
?>
<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <a href="<?= Helper::url('admin/requests') ?>" class="text-sm text-slate-500 hover:text-brand-600">← กลับรายการ</a>
            <h1 class="text-2xl font-bold text-slate-800 mt-1"><?= Helper::e($need['code']) ?></h1>
            <p class="text-slate-500 mt-1"><?= Helper::e($need['reporter_name']) ?> · <?= Helper::timeAgo($need['created_at']) ?></p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm px-3 py-1.5 rounded-full font-medium <?= Helper::statusColor($status) ?>"><?= Helper::e($statusLabels[$status] ?? $status) ?></span>
            <?php if ($need['urgency'] !== 'normal'): ?>
                <span class="text-sm px-3 py-1.5 rounded-full font-medium <?= Helper::urgencyColor($need['urgency']) ?>"><?= Helper::e($urgencyLabels[$need['urgency']] ?? '') ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Main info -->
        <div class="lg:col-span-2 space-y-5">
            <!-- Request info card -->
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-800 mb-4">ข้อมูลคำขอ</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-slate-500">ผู้แจ้ง</dt><dd class="font-medium text-slate-800 mt-0.5"><?= Helper::e($need['reporter_name']) ?></dd></div>
                    <div><dt class="text-slate-500">เบอร์โทร</dt><dd class="font-medium text-slate-800 mt-0.5"><?= Helper::e($need['reporter_phone'] ?: '—') ?></dd></div>
                    <div><dt class="text-slate-500">พื้นที่</dt><dd class="font-medium text-slate-800 mt-0.5"><?= Helper::e($need['area_name'] ?? '—') ?><?= $need['district'] ? ' · ' . Helper::e($need['district']) : '' ?></dd></div>
                    <div><dt class="text-slate-500">จุดสังเกต</dt><dd class="font-medium text-slate-800 mt-0.5"><?= Helper::e($need['address'] ?: '—') ?></dd></div>
                    <div><dt class="text-slate-500">ผู้ได้รับผลกระทบ</dt><dd class="font-medium text-slate-800 mt-0.5"><?= (int)$need['people_count'] ?> คน</dd></div>
                    <div><dt class="text-slate-500">พิกัด GPS</dt><dd class="font-medium text-slate-800 mt-0.5"><?= $need['lat'] ? Helper::e($need['lat'] . ', ' . $need['lng']) : '—' ?>
                        <?php if ($need['lat'] && $need['lng']): ?>
                            <a href="https://www.openstreetmap.org/?mlat=<?= Helper::e($need['lat']) ?>&mlon=<?= Helper::e($need['lng']) ?>#map=16/<?= Helper::e($need['lat']) ?>/<?= Helper::e($need['lng']) ?>" target="_blank" class="text-brand-600 text-xs ml-1">ดูแผนที่ ↗</a>
                        <?php endif; ?>
                    </dd></div>
                </dl>
                <?php if (!empty($need['need_types'])): ?>
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <div class="text-sm text-slate-500 mb-2">ความต้องการ</div>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach ($need['need_types'] as $t): ?>
                                <span class="text-sm bg-brand-50 text-brand-700 px-2.5 py-1 rounded-lg font-medium"><?= Helper::e($needTypes[$t] ?? $t) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($need['description']): ?>
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <div class="text-sm text-slate-500 mb-1">รายละเอียด</div>
                        <p class="text-slate-700 whitespace-pre-wrap"><?= Helper::e($need['description']) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($need['photo_path'])): ?>
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <div class="text-sm text-slate-500 mb-2">รูปภาพ</div>
                        <a href="<?= Helper::e(Helper::photoUrl($need['photo_path'])) ?>" target="_blank">
                            <img src="<?= Helper::e(Helper::photoUrl($need['photo_path'])) ?>" alt="" class="rounded-xl max-h-64 border border-slate-200">
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Help offers -->
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-800 mb-4">ข้อเสนอความช่วยเหลือ (<?= count($helps) ?>)</h2>
                <?php if (empty($helps)): ?>
                    <p class="text-sm text-slate-400">ยังไม่มีข้อเสนอ</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($helps as $h): ?>
                            <div class="flex items-start justify-between gap-3 p-3 rounded-lg bg-slate-50">
                                <div>
                                    <div class="font-medium text-slate-800"><?= Helper::e($h['helper_name'] ?? 'ไม่ระบุชื่อ') ?></div>
                                    <div class="text-sm text-slate-500"><?= Helper::e($h['helper_org'] ?: '') ?> <?= $h['helper_org'] ? '·' : '' ?> <?= Helper::e($helpTypes[$h['help_type']] ?? $h['help_type']) ?></div>
                                    <?php if ($h['notes']): ?><div class="text-sm text-slate-600 mt-1"><?= Helper::e($h['notes']) ?></div><?php endif; ?>
                                </div>
                                <div class="text-xs text-slate-400 shrink-0"><?= Helper::timeAgo($h['created_at']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- History timeline -->
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-800 mb-4">ประวัติ</h2>
                <?php if (empty($history)): ?>
                    <p class="text-sm text-slate-400">ไม่มีประวัติ</p>
                <?php else: ?>
                    <div class="space-y-0">
                        <?php foreach ($history as $i => $h): ?>
                            <div class="flex gap-3 <?= $i < count($history) - 1 ? 'pb-4' : '' ?> relative">
                                <?php if ($i < count($history) - 1): ?><div class="absolute left-[7px] top-4 bottom-0 w-px bg-slate-200"></div><?php endif; ?>
                                <div class="w-4 h-4 rounded-full <?= $h['new_status'] === 'completed' ? 'bg-emerald-500' : ($h['new_status'] === 'cancelled' ? 'bg-slate-400' : 'bg-brand-500') ?> shrink-0 mt-0.5"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-slate-800">
                                        <?= Helper::e($statusLabels[$h['new_status']] ?? $h['new_status']) ?>
                                        <?php if ($h['old_status'] && $h['old_status'] !== $h['new_status']): ?>
                                            <span class="text-slate-400 font-normal">(จาก <?= Helper::e($statusLabels[$h['old_status']] ?? $h['old_status']) ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($h['message']): ?><div class="text-sm text-slate-600 mt-0.5"><?= Helper::e($h['message']) ?></div><?php endif; ?>
                                    <div class="text-xs text-slate-400 mt-0.5"><?= Helper::e($h['user_name'] ?? 'ระบบ') ?> · <?= Helper::timeAgo($h['created_at']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar actions -->
        <div class="space-y-5">
            <!-- Status workflow -->
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-800 mb-4">จัดการสถานะ</h2>
                <div class="space-y-2">
                    <?php if ($canAccept): ?>
                        <button type="button" data-action="status" data-status="accepted" class="w-full px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm transition">รับเรื่อง</button>
                    <?php endif; ?>
                    <?php if ($status === 'accepted'): ?>
                        <button type="button" data-action="status" data-status="in_progress" class="w-full px-4 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-medium text-sm transition">เริ่มดำเนินการ</button>
                    <?php endif; ?>
                    <?php if ($canWork): ?>
                        <button type="button" data-action="status" data-status="in_progress" class="w-full px-4 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-medium text-sm transition <?= $status === 'in_progress' ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= $status === 'in_progress' ? 'disabled' : '' ?>>กำลังช่วยเหลือ</button>
                    <?php endif; ?>
                    <?php if ($canClose): ?>
                        <button type="button" data-action="status" data-status="completed" class="w-full px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm transition">ปิดเรื่อง (สำเร็จ)</button>
                    <?php endif; ?>
                    <?php if (!$closed): ?>
                        <button type="button" data-action="status" data-status="cancelled" class="w-full px-4 py-2.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium text-sm transition">ยกเลิกคำขอ</button>
                    <?php endif; ?>
                    <?php if ($status === 'pending'): ?>
                        <button type="button" data-action="status" data-status="pending" class="hidden"></button>
                    <?php endif; ?>
                </div>
                <div class="mt-3">
                    <label class="block text-xs text-slate-500 mb-1">หมายเหตุ (ไม่บังคับ)</label>
                    <input type="text" id="actionMsg" class="input-field text-sm" placeholder="เช่น ติดต่อชาวบ้านแล้ว">
                </div>
            </div>

            <!-- Assign -->
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-800 mb-1">ผู้รับผิดชอบ</h2>
                <p class="text-sm text-slate-500 mb-3"><?= Helper::e($need['assigned_name'] ?? 'ยังไม่มีผู้รับผิดชอบ') ?></p>
                <form id="assignForm" class="flex gap-2">
                    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
                    <input type="hidden" name="action" value="assign">
                    <select name="user_id" class="input-field flex-1 text-sm">
                        <option value="">เลือกผู้ใช้...</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= (int)$u['id'] ?>" <?= (int)($need['assigned_to'] ?? 0) === (int)$u['id'] ? 'selected' : '' ?>><?= Helper::e($u['name']) ?> (<?= Helper::e($u['role']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-primary px-3 text-sm">มอบหมาย</button>
                </form>
            </div>

            <!-- Add note -->
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-800 mb-3">เพิ่มบันทึกภายใน</h2>
                <form id="noteForm" class="space-y-2">
                    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
                    <input type="hidden" name="action" value="note">
                    <textarea name="message" required rows="3" class="input-field text-sm" placeholder="บันทึกสำหรับทีมงาน (ไม่แสดงต่อผู้แจ้ง)"></textarea>
                    <button type="submit" class="btn-primary w-full text-sm">บันทึก</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const needId = <?= (int)$need['id'] ?>;
    const url = window.BASE_PATH + '/admin/requests/' + needId + '/action';
    const csrf = document.querySelector('input[name="csrf_token"]').value;

    async function post(fd) {
        fd.append('csrf_token', csrf);
        const res = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        return res.json();
    }

    // Status buttons
    document.querySelectorAll('button[data-action="status"]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const status = btn.dataset.status;
            if (status === 'cancelled' && !confirm('ยืนยันยกเลิกคำขอนี้?')) return;
            const fd = new FormData();
            fd.append('action', 'status');
            fd.append('status', status);
            const msg = document.getElementById('actionMsg');
            if (msg && msg.value) fd.append('message', msg.value);
            btn.disabled = true;
            try {
                const json = await post(fd);
                if (json.ok) location.reload();
                else Toast.show(json.message || 'ไม่สำเร็จ', 'err');
            } catch (e) { Toast.show('เครือข่ายมีปัญหา', 'err'); }
            btn.disabled = false;
        });
    });

    // Assign
    document.getElementById('assignForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        try {
            const json = await post(fd);
            if (json.ok) location.reload();
            else Toast.show(json.message || 'ไม่สำเร็จ', 'err');
        } catch (err) { Toast.show('เครือข่ายมีปัญหา', 'err'); }
        btn.disabled = false;
    });

    // Note
    document.getElementById('noteForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        try {
            const json = await post(fd);
            if (json.ok) { e.target.reset(); location.reload(); }
            else Toast.show(json.message || 'ไม่สำเร็จ', 'err');
        } catch (err) { Toast.show('เครือข่ายมีปัญหา', 'err'); }
        btn.disabled = false;
    });
})();
</script>
