<div class="space-y-4">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="<?= Helper::url('needs') ?>" class="hover:text-brand-600">รายการ</a>
        <span>/</span>
        <span><?= Helper::e($need['code']) ?></span>
    </div>

    <div class="card p-4 space-y-3">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-lg font-semibold"><?= Helper::e($need['reporter_name']) ?></h1>
                <p class="text-sm text-slate-500"><?= Helper::e($need['code']) ?></p>
            </div>
            <div class="flex flex-col items-end gap-1">
                <span class="text-sm px-2.5 py-1 rounded-full <?= Helper::statusColor($need['status']) ?>"><?= Helper::e($statusLabels[$need['status']] ?? $need['status']) ?></span>
                <?php if ($need['urgency'] !== 'normal'): ?>
                    <span class="text-sm px-2.5 py-1 rounded-full <?= Helper::urgencyColor($need['urgency']) ?>"><?= Helper::e($urgencyLabels[$need['urgency']] ?? $need['urgency']) ?></span>
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

        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
            <div>
                <dt class="text-slate-500">เบอร์โทร</dt>
                <dd class="font-medium">
                    <?php if (!empty($isLoggedIn) && $need['reporter_phone']): ?>
                        <a href="tel:<?= preg_replace('/\D/', '', $need['reporter_phone']) ?>" class="text-brand-600 font-semibold"><?= Helper::e(Helper::formatPhone($need['reporter_phone'])) ?></a>
                    <?php elseif ($need['reporter_phone']): ?>
                        <span class="text-slate-400"><?= Helper::e(substr($need['reporter_phone'], 0, 3)) ?>-xxx-xxxx</span>
                        <span class="text-sm text-slate-400">(เข้าสู่ระบบเพื่อดู)</span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt class="text-slate-500">จำนวนคน</dt>
                <dd class="font-medium"><?= (int)$need['people_count'] ?> คน</dd>
            </div>
            <div class="col-span-2">
                <dt class="text-slate-500">พื้นที่</dt>
                <dd class="font-medium"><?= Helper::e(($need['area_name'] ?? '') . ($need['province'] ? ' · ' . $need['province'] : '')) ?: '-' ?></dd>
            </div>
            <?php if ($need['address']): ?>
            <div class="col-span-2">
                <dt class="text-slate-500">ที่อยู่</dt>
                <dd><?= Helper::e($need['address']) ?></dd>
            </div>
            <?php endif; ?>
            <div class="col-span-2">
                <dt class="text-slate-500">ความต้องการ</dt>
                <dd class="flex flex-wrap gap-1 mt-1">
                    <?php foreach ($need['need_types'] as $t): ?>
                        <span class="text-sm bg-brand-50 text-brand-700 px-2 py-0.5 rounded-md"><?= Helper::e($needTypes[$t] ?? $t) ?></span>
                    <?php endforeach; ?>
                    <?php if (empty($need['need_types'])): ?>-<?php endif; ?>
                </dd>
            </div>
            <?php if ($need['description']): ?>
            <div class="col-span-2">
                <dt class="text-slate-500">รายละเอียด</dt>
                <dd class="whitespace-pre-wrap"><?= Helper::e($need['description']) ?></dd>
            </div>
            <?php endif; ?>
            <div>
                <dt class="text-slate-500">แจ้งเมื่อ</dt>
                <dd><?= Helper::e($need['created_at']) ?></dd>
            </div>
            <?php if ($need['assigned_name']): ?>
            <div>
                <dt class="text-slate-500">ผู้รับผิดชอบ</dt>
                <dd><?= Helper::e($need['assigned_name']) ?></dd>
            </div>
            <?php endif; ?>
        </dl>

        <?php if ($need['lat'] && $need['lng']): ?>
            <div id="detailMap" class="h-48 rounded-xl overflow-hidden border border-slate-200"></div>
            <p class="text-sm text-slate-500">พิกัด: <?= number_format($need['lat'], 5) ?>, <?= number_format($need['lng'], 5) ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($isVolunteer)): ?>
    <!-- Quick status actions -->
    <?php
        $actions = [
            'pending' => [
                ['accepted', 'รับเรื่อง', 'bg-blue-600 hover:bg-blue-700'],
                ['cancelled', 'ยกเลิก', 'bg-slate-200 text-slate-600'],
            ],
            'accepted' => [
                ['in_progress', 'เริ่มช่วยเหลือ', 'bg-indigo-600 hover:bg-indigo-700'],
                ['completed', 'เสร็จสิ้น', 'bg-emerald-600 hover:bg-emerald-700'],
                ['cancelled', 'ยกเลิก', 'bg-slate-200 text-slate-600'],
            ],
            'in_progress' => [
                ['completed', 'เสร็จสิ้น', 'bg-emerald-600 hover:bg-emerald-700'],
                ['cancelled', 'ยกเลิก', 'bg-slate-200 text-slate-600'],
            ],
        ];
        $currentActions = $actions[$need['status']] ?? [];
    ?>
    <?php if (!empty($currentActions)): ?>
    <div class="card p-4">
        <h2 class="font-medium mb-3">อัปเดตสถานะ</h2>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($currentActions as [$st, $label, $cls]): ?>
                <button type="button" class="status-btn px-4 py-2.5 rounded-xl text-sm font-medium text-white <?= $cls ?>" data-status="<?= $st ?>"><?= $label ?></button>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="csrf_token" id="statusCsrf" value="<?= Helper::e($csrf) ?>">
        <p id="statusMsg" class="text-sm mt-2 hidden"></p>
    </div>
    <?php endif; ?>

    <!-- Offer help -->
    <?php if (!in_array($need['status'], ['completed', 'cancelled'], true)): ?>
    <div class="card p-4">
        <h2 class="font-medium mb-3">เสนอความช่วยเหลือ</h2>
        <form id="helpForm" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
            <select name="help_type" class="input-field" required>
                <option value="">-- เลือกประเภทความช่วยเหลือ --</option>
                <?php foreach ($helpTypes as $k => $v): ?>
                    <option value="<?= $k ?>"><?= Helper::e($v) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="helper_org" class="input-field" placeholder="องค์กร / หน่วยงาน (ถ้ามี)">
            <textarea name="notes" rows="2" class="input-field" placeholder="รายละเอียด เช่น จำนวนคน เวลาไปถึง"></textarea>
            <button type="submit" class="w-full bg-brand-50 text-brand-700 border border-brand-200 font-medium rounded-xl px-5 py-3 text-sm active:bg-brand-100">ส่งข้อเสนอ</button>
        </form>
        <p id="helpMsg" class="text-sm mt-2 hidden"></p>
    </div>
    <?php endif; ?>

    <!-- Help offers -->
    <?php if (!empty($helps)): ?>
    <div class="card p-4">
        <h2 class="font-medium mb-3">ข้อเสนอความช่วยเหลือ (<?= count($helps) ?>)</h2>
        <ul class="space-y-2 text-sm">
            <?php foreach ($helps as $h): ?>
                <li class="border border-slate-100 rounded-xl p-3">
                    <div class="flex items-center justify-between">
                        <span class="font-medium"><?= Helper::e($h['user_name'] ?? $h['helper_name'] ?? 'อาสา') ?></span>
                        <span class="text-sm bg-brand-50 text-brand-700 px-2 py-0.5 rounded-md"><?= Helper::e($helpTypes[$h['help_type']] ?? $h['help_type'] ?? '') ?></span>
                    </div>
                    <?php if ($h['helper_org']): ?>
                        <div class="text-sm text-slate-500 mt-0.5"><?= Helper::e($h['helper_org']) ?></div>
                    <?php endif; ?>
                    <?php if ($h['notes']): ?>
                        <div class="text-slate-600 mt-1"><?= Helper::e($h['notes']) ?></div>
                    <?php endif; ?>
                    <div class="text-sm text-slate-400 mt-1"><?= Helper::timeAgo($h['created_at']) ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($history)): ?>
    <div class="card p-4">
        <h2 class="font-medium mb-3">ประวัติการอัปเดต</h2>
        <ul class="space-y-3 text-sm">
            <?php foreach ($history as $h): ?>
                <li class="border-l-2 border-brand-200 pl-3">
                    <div class="text-slate-600">
                        <?php if ($h['old_status']): ?>
                            <?= Helper::e($statusLabels[$h['old_status']] ?? $h['old_status']) ?> → 
                        <?php endif; ?>
                        <strong><?= Helper::e($statusLabels[$h['new_status']] ?? $h['new_status']) ?></strong>
                    </div>
                    <?php if ($h['message']): ?>
                        <div class="text-slate-500"><?= Helper::e($h['message']) ?></div>
                    <?php endif; ?>
                    <div class="text-sm text-slate-400 mt-0.5">
                        <?= Helper::e($h['user_name'] ?? 'ระบบ') ?> · <?= Helper::timeAgo($h['created_at']) ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>

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

<?php if (!empty($isVolunteer)): ?>
<script>
document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const status = btn.dataset.status;
        const labels = { accepted: 'รับเรื่อง', in_progress: 'เริ่มช่วยเหลือ', completed: 'เสร็จสิ้น', cancelled: 'ยกเลิก' };
        const msg = document.getElementById('statusMsg');
        const label = labels[status] || status;

        // Optional note via prompt (simple, works everywhere)
        const note = prompt('เพิ่มหมายเหตุ (ไม่บังคับ):', '');
        if (note === null) return; // cancelled

        btn.disabled = true;
        const fd = new FormData();
        fd.append('csrf_token', document.getElementById('statusCsrf').value);
        fd.append('status', status);
        fd.append('message', note);
        try {
            const res = await fetch(window.BASE_PATH + '/needs/<?= (int)$need['id'] ?>/status', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            });
            const json = await res.json();
            msg.classList.remove('hidden');
            msg.textContent = json.message || (json.ok ? 'สำเร็จ' : 'ไม่สำเร็จ');
            msg.className = 'text-sm mt-2 ' + (json.ok ? 'text-emerald-600' : 'text-red-600');
            if (json.ok) setTimeout(() => location.reload(), 800);
            else btn.disabled = false;
        } catch (err) {
            Toast.show('เกิดข้อผิดพลาดเครือข่าย', 'err');
            btn.disabled = false;
        }
    });
});

const helpForm = document.getElementById('helpForm');
if (helpForm) {
    helpForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(helpForm);
        const msg = document.getElementById('helpMsg');
        const btn = helpForm.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'กำลังส่ง...';
        try {
            const res = await fetch(window.BASE_PATH + '/needs/<?= (int)$need['id'] ?>/help', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            });
            const json = await res.json();
            msg.classList.remove('hidden');
            msg.textContent = json.message || '';
            msg.className = 'text-sm mt-2 ' + (json.ok ? 'text-emerald-600' : 'text-red-600');
            if (json.ok) setTimeout(() => location.reload(), 800);
            else { btn.disabled = false; btn.textContent = 'ส่งข้อเสนอ'; }
        } catch (err) {
            Toast.show('เกิดข้อผิดพลาดเครือข่าย', 'err');
            btn.disabled = false;
            btn.textContent = 'ส่งข้อเสนอ';
        }
    });
}
</script>
<?php endif; ?>
