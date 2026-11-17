<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">จัดการผู้ใช้</h1>
            <p class="text-slate-500 mt-1">สร้างบัญชีอาสาสมัคร / ผู้ดูแล</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Create user card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    เพิ่มผู้ใช้ใหม่
                </h2>
                <form id="createUserForm" class="space-y-3">
                    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">ชื่อ-นามสกุล</label>
                        <input type="text" name="name" required class="input-field" placeholder="ชื่อ-นามสกุล">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">เบอร์โทรศัพท์</label>
                        <input type="tel" name="phone" required class="input-field" placeholder="ใช้สำหรับ login" inputmode="tel">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">รหัสผ่าน</label>
                        <input type="password" name="password" required minlength="6" class="input-field" placeholder="อย่างน้อย 6 ตัวอักษร">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">บทบาท</label>
                        <select name="role" class="input-field">
                            <?php foreach ($roles as $k => $v): ?>
                                <option value="<?= $k ?>"><?= Helper::e($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary w-full">สร้างบัญชี</button>
                </form>
                <p id="createMsg" class="text-sm mt-2 hidden"></p>
            </div>
        </div>

        <!-- User list -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200">
                    <h2 class="font-semibold text-slate-800">รายชื่อผู้ใช้ (<?= count($users) ?>)</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php foreach ($users as $u): ?>
                        <div class="p-4" data-user-id="<?= (int)$u['id'] ?>">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-slate-800 truncate">
                                            <?= Helper::e($u['name']) ?>
                                            <?php if ((int)$u['id'] === (int)$currentUser['id']): ?>
                                                <span class="text-xs text-brand-600 font-normal">(คุณ)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-slate-500"><?= Helper::e(Helper::formatPhone($u['phone'])) ?> · <?= Helper::e($roles[$u['role']] ?? $u['role']) ?></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <?php if (!(bool)$u['is_active']): ?>
                                        <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-500">ปิดใช้งาน</span>
                                    <?php endif; ?>
                                    <button type="button" class="edit-btn text-sm px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition" data-id="<?= (int)$u['id'] ?>">แก้ไข</button>
                                    <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                                        <button type="button" class="toggle-btn text-sm px-3 py-1.5 rounded-lg transition <?= $u['is_active'] ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' ?>"
                                                data-id="<?= (int)$u['id'] ?>" data-active="<?= (int)$u['is_active'] ?>">
                                            <?= $u['is_active'] ? 'ปิด' : 'เปิด' ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Inline edit row (hidden) -->
                            <div class="edit-row hidden mt-3 pt-3 border-t border-slate-100" data-edit-for="<?= (int)$u['id'] ?>">
                                <form class="edit-form grid grid-cols-1 sm:grid-cols-4 gap-2" data-id="<?= (int)$u['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
                                    <input type="text" name="name" value="<?= Helper::e($u['name']) ?>" class="input-field py-2 text-sm" placeholder="ชื่อ">
                                    <select name="role" class="input-field py-2 text-sm">
                                        <?php foreach ($roles as $k => $v): ?>
                                            <option value="<?= $k ?>" <?= $u['role'] === $k ? 'selected' : '' ?>><?= Helper::e($v) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="password" name="password" class="input-field py-2 text-sm" placeholder="รหัสผ่านใหม่ (เว้นว่างถ้าไม่เปลี่ยน)">
                                    <button type="submit" class="btn-primary py-2 text-sm">บันทึก</button>
                                </form>
                                <p class="edit-msg text-sm mt-2 hidden"></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const csrf = '<?= Helper::e($csrf) ?>';

    function showMsg(el, ok, text) {
        el.classList.remove('hidden');
        el.textContent = text;
        el.className = 'text-sm mt-2 ' + (ok ? 'text-emerald-600' : 'text-red-600');
    }

    document.getElementById('createUserForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const msg = document.getElementById('createMsg');
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        try {
            const res = await fetch(window.BASE_PATH + '/admin/users', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            });
            const json = await res.json();
            showMsg(msg, json.ok, json.message || '');
            if (json.ok) setTimeout(() => location.reload(), 800);
        } catch (err) {
            showMsg(msg, false, 'เครือข่ายมีปัญหา');
        }
        btn.disabled = false;
    });

    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const fd = new FormData();
            fd.append('csrf_token', csrf);
            try {
                const res = await fetch(window.BASE_PATH + '/admin/users/' + id + '/toggle', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                });
                const json = await res.json();
                if (json.ok) location.reload();
                else Toast.show(json.message || 'ไม่สำเร็จ', 'err');
            } catch (err) {
                Toast.show('เครือข่ายมีปัญหา', 'err');
            }
        });
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const row = document.querySelector('.edit-row[data-edit-for="' + id + '"]');
            if (row) row.classList.toggle('hidden');
        });
    });

    document.querySelectorAll('.edit-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = form.dataset.id;
            const fd = new FormData(form);
            const msg = form.parentElement.querySelector('.edit-msg');
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            try {
                const res = await fetch(window.BASE_PATH + '/admin/users/' + id, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                });
                const json = await res.json();
                showMsg(msg, json.ok, json.message || '');
                if (json.ok) setTimeout(() => location.reload(), 800);
            } catch (err) {
                showMsg(msg, false, 'เครือข่ายมีปัญหา');
            }
            btn.disabled = false;
        });
    });
})();
</script>
