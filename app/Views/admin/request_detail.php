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
            <a href="<?= Helper::url('admin') ?>" class="text-sm text-slate-500 hover:text-brand-600">← กลับศูนย์บัญชาการ</a>
            <h1 class="text-2xl font-bold text-slate-800 mt-1 flex items-center gap-2.5">
                <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <?= Helper::e($need['code']) ?>
            </h1>
            <p class="text-slate-500 mt-1"><?= Helper::e($need['reporter_name']) ?> · <?= Helper::timeAgo($need['created_at']) ?></p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm px-3 py-1.5 rounded-full font-medium inline-flex items-center gap-1.5 <?= Helper::statusColor($status) ?>"><?= Helper::statusDot($status) ?><?= Helper::e($statusLabels[$status] ?? $status) ?></span>
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
                <h2 class="font-semibold text-slate-800 mb-4 flex items-center gap-2"><svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>ข้อมูลคำขอ</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-slate-500">ผู้แจ้ง</dt><dd class="font-medium text-slate-800 mt-0.5"><?= Helper::e($need['reporter_name']) ?></dd></div>
                    <?php if (!empty($need['scenario'])): ?>
                    <div><dt class="text-slate-500">ประเภทเหตุการณ์</dt><dd class="font-medium text-slate-800 mt-0.5"><span class="inline-block text-xs px-2 py-0.5 rounded bg-brand-50 text-brand-700"><?= Helper::e(Helper::scenarioLabel($need['scenario'] ?? null)) ?></span></dd></div>
                    <?php endif; ?>
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
                                <span class="text-sm bg-brand-50 text-brand-700 px-2.5 py-1 rounded-lg font-medium"><?= Helper::e(Helper::needTypeLabel($t)) ?></span>
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
                        <a href="<?= Helper::e(Helper::photoUrl($need['photo_path'])) ?>" target="_blank" rel="noopener" class="lightbox block">
                            <img src="<?= Helper::e(Helper::photoUrl($need['photo_path'])) ?>" alt="" class="rounded-xl max-h-64 border border-slate-200">
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Edit request details -->
            <details class="bg-white rounded-xl border border-slate-200 p-5">
                <summary class="font-semibold text-slate-800 cursor-pointer flex items-center gap-2 list-none">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    แก้ไขข้อมูลคำขอ
                    <span class="ml-auto text-xs text-slate-400 font-normal">แตะเพื่อแก้ไข</span>
                </summary>
                <form id="editNeedForm" class="mt-4 space-y-3">
                    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">ชื่อผู้แจ้ง *</label>
                            <input type="text" name="reporter_name" required value="<?= Helper::e($need['reporter_name']) ?>" class="input-field text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">เบอร์โทร</label>
                            <input type="tel" name="reporter_phone" value="<?= Helper::e($need['reporter_phone'] ?? '') ?>" class="input-field text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">พื้นที่</label>
                            <select name="area_id" class="input-field text-sm">
                                <option value="">-- ไม่ระบุ --</option>
                                <?php foreach ($areas as $a): ?>
                                    <option value="<?= (int)$a['id'] ?>" <?= (int)($need['area_id'] ?? 0) === (int)$a['id'] ? 'selected' : '' ?>><?= Helper::e($a['name']) ?> (<?= Helper::e($a['province']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">จำนวนผู้ได้รับผลกระทบ</label>
                            <input type="number" name="people_count" min="1" value="<?= (int)$need['people_count'] ?>" class="input-field text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">ละติจูด</label>
                            <input type="number" name="lat" step="any" value="<?= Helper::e((string)($need['lat'] ?? '')) ?>" class="input-field text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">ลองจิจูด</label>
                            <input type="number" name="lng" step="any" value="<?= Helper::e((string)($need['lng'] ?? '')) ?>" class="input-field text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">จุดสังเกต / ที่อยู่</label>
                            <input type="text" name="address" value="<?= Helper::e($need['address'] ?? '') ?>" class="input-field text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">ประเภทเหตุการณ์</label>
                            <select name="scenario" class="input-field text-sm">
                                <option value="">-- ไม่ระบุ --</option>
                                <?php foreach ($scenarioLabels as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= ($need['scenario'] ?? '') === $k ? 'selected' : '' ?>><?= Helper::e($v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">ความเร่งด่วน</label>
                            <select name="urgency" class="input-field text-sm">
                                <?php foreach ($urgencyLabels as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= ($need['urgency'] ?? '') === $k ? 'selected' : '' ?>><?= Helper::e($v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">ความต้องการ</label>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach ($needTypes as $key => $label): ?>
                                <label class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 cursor-pointer has-[:checked]:bg-brand-50 has-[:checked]:border-brand-600 transition">
                                    <input type="checkbox" name="need_types[]" value="<?= Helper::e($key) ?>" class="rounded border-slate-300 text-brand-600" <?= in_array($key, $need['need_types'] ?? [], true) ? 'checked' : '' ?>>
                                    <span class="text-xs font-medium text-slate-700"><?= Helper::e($label) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">รายละเอียด</label>
                        <textarea name="description" rows="2" class="input-field text-sm"><?= Helper::e($need['description'] ?? '') ?></textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" class="btn-primary px-5 text-sm">บันทึกการแก้ไข</button>
                        <span id="editMsg" class="text-sm hidden"></span>
                    </div>
                </form>
            </details>

            <!-- Help offers -->
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-800 mb-4 flex items-center gap-2"><svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>ข้อเสนอความช่วยเหลือ (<?= count($helps) ?>)</h2>
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
                <h2 class="font-semibold text-slate-800 mb-4 flex items-center gap-2"><svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>ประวัติ</h2>
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
                                    <?php if (!empty($h['photo_path'])): ?>
                                        <a href="<?= Helper::e(Helper::photoUrl($h['photo_path'])) ?>" target="_blank" rel="noopener" class="lightbox block mt-1">
                                            <img src="<?= Helper::e(Helper::photoUrl($h['photo_path'])) ?>" alt="รูปหน้างาน" loading="lazy" class="rounded-lg max-h-40 w-auto border border-slate-200">
                                        </a>
                                    <?php endif; ?>
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
                <h2 class="font-semibold text-slate-800 mb-4 flex items-center gap-2"><svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>จัดการสถานะ</h2>
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
                <h2 class="font-semibold text-slate-800 mb-1 flex items-center gap-2"><svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>ผู้รับผิดชอบ</h2>
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
                <h2 class="font-semibold text-slate-800 mb-3 flex items-center gap-2"><svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>เพิ่มบันทึกภายใน</h2>
                <form id="noteForm" class="space-y-2">
                    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
                    <input type="hidden" name="action" value="note">
                    <textarea name="message" required rows="3" class="input-field text-sm" placeholder="บันทึกสำหรับทีมงาน (ไม่แสดงต่อผู้แจ้ง)"></textarea>
                    <button type="submit" class="btn-primary w-full text-sm">บันทึก</button>
                </form>
            </div>

            <?php if (!empty($isAdmin)): ?>
            <!-- Danger zone -->
            <div class="bg-white rounded-xl border border-red-200 p-5">
                <h2 class="font-semibold text-red-700 mb-1 flex items-center gap-2"><svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>Danger zone</h2>
                <p class="text-sm text-slate-500 mb-3">ลบเคสนี้ถาวร — ข้อมูลทั้งหมด (ประวัติ, ข้อเสนอความช่วยเหลือ, รูปภาพ) จะหายไป</p>
                <button type="button" id="deleteCaseBtn" class="w-full px-4 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium text-sm transition">ลบเคส</button>
            </div>
            <?php endif; ?>
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

    // Delete (admin only — button only rendered for admins)
    const deleteBtn = document.getElementById('deleteCaseBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            if (!confirm('ลบเคส ' + document.querySelector('h1').textContent + ' ถาวร? การกระทำนี้ไม่สามารถย้อนกลับได้')) return;
            const fd = new FormData();
            fd.append('csrf_token', csrf);
            deleteBtn.disabled = true;
            try {
                const res = await fetch(window.BASE_PATH + '/admin/requests/' + needId + '/delete', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                });
                const json = await res.json();
                if (json.ok) {
                    Toast.show('ลบเคสแล้ว', 'ok');
                    setTimeout(() => window.location.href = window.BASE_PATH + '/admin/requests', 500);
                } else {
                    Toast.show(json.message || 'ลบไม่สำเร็จ', 'err');
                    deleteBtn.disabled = false;
                }
            } catch (e) {
                Toast.show('เครือข่ายมีปัญหา', 'err');
                deleteBtn.disabled = false;
            }
        });
    }

    // Edit details
    const editForm = document.getElementById('editNeedForm');
    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(editForm);
            const msg = document.getElementById('editMsg');
            const btn = editForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            try {
                const res = await fetch(window.BASE_PATH + '/admin/requests/' + needId + '/update', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                });
                const json = await res.json();
                if (json.ok) {
                    Toast.show('บันทึกข้อมูลแล้ว', 'ok');
                    setTimeout(() => location.reload(), 500);
                } else {
                    msg.textContent = json.message || 'ไม่สำเร็จ';
                    msg.className = 'text-sm text-red-600';
                    msg.classList.remove('hidden');
                }
            } catch (err) {
                Toast.show('เครือข่ายมีปัญหา', 'err');
            }
            btn.disabled = false;
        });
    }

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
