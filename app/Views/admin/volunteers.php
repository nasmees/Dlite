<div class="space-y-5 lg:space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-2xl font-semibold text-slate-900">อาสาสมัคร</h2>
            <p class="text-sm text-slate-500 mt-1">จัดการข้อมูล ทักษะ และสถานะการปฏิบัติงาน</p>
        </div>
        <button type="button" id="addVolunteer" class="btn-primary inline-flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
            เพิ่มอาสาสมัคร
        </button>
    </div>

    <section class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <form method="get" class="flex flex-col md:flex-row gap-3 p-4 border-b border-slate-200">
            <label class="relative flex-1">
                <span class="sr-only">ค้นหาอาสาสมัคร</span>
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.34-4.34M19 11a8 8 0 11-16 0 8 8 0 0116 0z"/></svg>
                <input type="search" name="q" value="<?= Helper::e($filters['q']) ?>" placeholder="ค้นหาชื่อ เบอร์โทร องค์กร หรือพื้นที่" class="input-field pl-9">
            </label>
            <label>
                <span class="sr-only">กรองตามทักษะ</span>
                <select name="skill" class="input-field md:w-56" onchange="this.form.submit()">
                    <option value="">ทุกทักษะ</option>
                    <?php foreach ($skills as $key => $label): ?>
                        <option value="<?= Helper::e($key) ?>" <?= $filters['skill'] === $key ? 'selected' : '' ?>><?= Helper::e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">ค้นหา</button>
        </form>

        <div class="flex items-center justify-between px-4 py-3 text-sm text-slate-500">
            <span><?= count($volunteers) ?> คน</span>
            <span>พร้อมงาน <span class="font-medium text-emerald-700"><?= count(array_filter($volunteers, fn($v) => $v['availability'] === 'available' && (bool)$v['is_active'])) ?></span></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-sm">
                <thead>
                    <tr class="border-y border-slate-200 text-left">
                        <th class="px-4 py-3">อาสาสมัคร</th>
                        <th class="px-4 py-3">องค์กร</th>
                        <th class="px-4 py-3">พื้นที่</th>
                        <th class="px-4 py-3">ทักษะ / อุปกรณ์</th>
                        <th class="px-4 py-3">สถานะ</th>
                        <th class="px-4 py-3 text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($volunteers)): ?>
                        <tr><td colspan="6" class="px-4 py-14 text-center text-slate-500">
                            <p class="font-medium text-slate-700">ไม่พบข้อมูลอาสาสมัคร</p>
                            <p class="text-xs mt-1">ลองเปลี่ยนคำค้นหา หรือล้างตัวกรองทักษะ</p>
                        </td></tr>
                    <?php else: foreach ($volunteers as $volunteer): ?>
                        <?php
                            $rowData = [
                                'id' => (int)$volunteer['id'],
                                'name' => $volunteer['name'],
                                'phone' => $volunteer['phone'] ?? '',
                                'organization_id' => (int)($volunteer['organization_id'] ?? 0),
                                'area_id' => (int)($volunteer['area_id'] ?? 0),
                                'availability' => $volunteer['availability'],
                                'skills' => $volunteer['skills'],
                                'equipment' => $volunteer['equipment'],
                            ];
                        ?>
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-blue-50 text-brand-700 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-slate-900"><?= Helper::e($volunteer['name']) ?></div>
                                        <div class="text-xs text-slate-500"><?= Helper::e(Helper::formatPhone($volunteer['phone'])) ?></div>
                                        <?php if ((bool)$volunteer['is_sample']): ?><span class="text-[11px] text-slate-400">ข้อมูลตัวอย่าง</span><?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-700"><?= Helper::e($volunteer['organization_name'] ?? '—') ?></td>
                            <td class="px-4 py-3 text-slate-700"><?= Helper::e($volunteer['area_name'] ?? '—') ?></td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5 max-w-sm">
                                    <?php foreach ($volunteer['skills'] as $skill): ?>
                                        <span class="rounded-md bg-blue-50 px-2 py-1 text-xs text-blue-800"><?= Helper::e($skills[$skill] ?? $skill) ?></span>
                                    <?php endforeach; ?>
                                    <?php foreach ($volunteer['equipment'] as $item): ?>
                                        <span class="rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-700"><?= Helper::e($equipment[$item] ?? $item) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (empty($volunteer['skills']) && empty($volunteer['equipment'])): ?>
                                        <span class="text-xs text-slate-400">ยังไม่ระบุ</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <button type="button" class="availability-toggle inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium <?= $volunteer['availability'] === 'available' ? 'bg-emerald-50 text-emerald-700' : 'bg-orange-50 text-orange-700' ?>"
                                    data-id="<?= (int)$volunteer['id'] ?>" data-availability="<?= Helper::e($volunteer['availability']) ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $volunteer['availability'] === 'available' ? 'bg-emerald-600' : 'bg-orange-500' ?>"></span>
                                    <?= $volunteer['availability'] === 'available' ? 'พร้อมงาน' : 'ติดภารกิจ' ?>
                                </button>
                                <?php if (!(bool)$volunteer['is_active']): ?>
                                    <span class="block text-[11px] text-slate-400 mt-1">บัญชีปิดใช้งาน</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button" class="edit-volunteer rounded-md border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                    data-volunteer="<?= Helper::e(json_encode($rowData, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">แก้ไข</button>
                                <button type="button" class="toggle-account ml-1 rounded-md px-3 py-1.5 text-xs font-medium <?= $volunteer['is_active'] ? 'text-rose-700 hover:bg-rose-50' : 'text-brand-700 hover:bg-blue-50' ?>"
                                    data-id="<?= (int)$volunteer['id'] ?>" data-active="<?= (int)$volunteer['is_active'] ?>">
                                    <?= $volunteer['is_active'] ? 'ระงับบัญชี' : 'เปิดบัญชี' ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<div id="volunteerModal" class="hidden fixed inset-0 z-[1200] bg-slate-900/40 p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="volunteerModalTitle">
    <div class="max-w-2xl mx-auto my-6 rounded-xl bg-white shadow-xl border border-slate-200">
        <div class="flex items-start justify-between gap-4 p-5 border-b border-slate-200">
            <div>
                <h2 id="volunteerModalTitle" class="font-semibold text-lg text-slate-900">เพิ่มอาสาสมัคร</h2>
                <p class="text-sm text-slate-500 mt-1">ข้อมูลนี้ใช้สำหรับประสานงานและจัดสรรกำลังคน</p>
            </div>
            <button type="button" class="close-volunteer-modal rounded-md p-1.5 text-slate-500 hover:bg-slate-100" aria-label="ปิดหน้าต่าง">✕</button>
        </div>
        <form id="volunteerForm" class="p-5 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
            <input type="hidden" name="id" id="volunteerId" value="">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="block text-sm font-medium text-slate-700">ชื่อ-นามสกุล
                    <input type="text" name="name" id="volunteerName" required class="input-field mt-1.5">
                </label>
                <label class="block text-sm font-medium text-slate-700">เบอร์โทรศัพท์
                    <input type="tel" name="phone" id="volunteerPhone" required inputmode="tel" class="input-field mt-1.5">
                </label>
            </div>
            <label class="block text-sm font-medium text-slate-700">รหัสผ่าน <span class="font-normal text-slate-500" id="passwordHint">จำเป็นสำหรับบัญชีใหม่</span>
                <input type="password" name="password" id="volunteerPassword" minlength="6" class="input-field mt-1.5" autocomplete="new-password">
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="block text-sm font-medium text-slate-700">องค์กร
                    <select name="organization_id" id="volunteerOrganization" class="input-field mt-1.5">
                        <option value="">ไม่สังกัดองค์กร</option>
                        <?php foreach ($organizations as $organization): ?>
                            <option value="<?= (int)$organization['id'] ?>"><?= Helper::e($organization['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block text-sm font-medium text-slate-700">พื้นที่รับผิดชอบ
                    <select name="area_id" id="volunteerArea" class="input-field mt-1.5">
                        <option value="">ไม่ระบุพื้นที่</option>
                        <?php foreach ($areas as $area): ?>
                            <option value="<?= (int)$area['id'] ?>"><?= Helper::e($area['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <label class="block text-sm font-medium text-slate-700">สถานะงาน
                <select name="availability" id="volunteerAvailability" class="input-field mt-1.5">
                    <option value="available">พร้อมงาน</option>
                    <option value="busy">ติดภารกิจ</option>
                </select>
            </label>
            <fieldset>
                <legend class="text-sm font-medium text-slate-700 mb-2">ทักษะ</legend>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <?php foreach ($skills as $key => $label): ?>
                        <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                            <input type="checkbox" name="skills[]" value="<?= Helper::e($key) ?>" class="rounded border-slate-300 text-brand-600 focus:ring-brand-600">
                            <?= Helper::e($label) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <fieldset>
                <legend class="text-sm font-medium text-slate-700 mb-2">อุปกรณ์</legend>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <?php foreach ($equipment as $key => $label): ?>
                        <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                            <input type="checkbox" name="equipment[]" value="<?= Helper::e($key) ?>" class="rounded border-slate-300 text-brand-600 focus:ring-brand-600">
                            <?= Helper::e($label) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <p id="volunteerFormMessage" class="hidden text-sm" role="status"></p>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" class="close-volunteer-modal rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">ยกเลิก</button>
                <button type="submit" class="btn-primary">บันทึกข้อมูล</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('volunteerModal');
    const form = document.getElementById('volunteerForm');
    const message = document.getElementById('volunteerFormMessage');
    const password = document.getElementById('volunteerPassword');
    const csrf = '<?= Helper::e($csrf) ?>';

    function showMessage(text, ok) {
        message.textContent = text;
        message.className = 'text-sm ' + (ok ? 'text-emerald-700' : 'text-rose-700');
    }

    function openModal(data) {
        form.reset();
        message.className = 'hidden text-sm';
        document.getElementById('volunteerId').value = data ? data.id : '';
        document.getElementById('volunteerName').value = data ? data.name : '';
        document.getElementById('volunteerPhone').value = data ? data.phone : '';
        document.getElementById('volunteerOrganization').value = data ? data.organization_id : '';
        document.getElementById('volunteerArea').value = data ? data.area_id : '';
        document.getElementById('volunteerAvailability').value = data ? data.availability : 'available';
        password.required = !data;
        document.getElementById('passwordHint').textContent = data ? 'เว้นว่างหากไม่ต้องการเปลี่ยน' : 'จำเป็นสำหรับบัญชีใหม่';
        if (data) {
            form.querySelectorAll('input[name="skills[]"]').forEach(input => input.checked = data.skills.includes(input.value));
            form.querySelectorAll('input[name="equipment[]"]').forEach(input => input.checked = data.equipment.includes(input.value));
        }
        modal.classList.remove('hidden');
        document.getElementById('volunteerName').focus();
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    document.getElementById('addVolunteer').addEventListener('click', () => openModal(null));
    document.querySelectorAll('.close-volunteer-modal').forEach(button => button.addEventListener('click', closeModal));
    modal.addEventListener('click', event => { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeModal(); });

    document.querySelectorAll('.edit-volunteer').forEach(button => {
        button.addEventListener('click', () => openModal(JSON.parse(button.dataset.volunteer)));
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        submit.disabled = true;
        try {
            const response = await fetch(window.BASE_PATH + '/admin/volunteers', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            });
            const result = await response.json();
            if (!response.ok || !result.ok) throw new Error(result.message || 'บันทึกข้อมูลไม่สำเร็จ');
            window.Toast.show(result.message, 'ok');
            window.location.reload();
        } catch (error) {
            showMessage(error.message || 'เกิดข้อผิดพลาดในการบันทึก', false);
        } finally {
            submit.disabled = false;
        }
    });

    document.querySelectorAll('.availability-toggle').forEach(button => {
        button.addEventListener('click', async () => {
            const next = button.dataset.availability === 'available' ? 'busy' : 'available';
            const body = new FormData();
            body.append('csrf_token', csrf);
            body.append('availability', next);
            button.disabled = true;
            try {
                const response = await fetch(window.BASE_PATH + '/admin/volunteers/' + button.dataset.id + '/availability', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body
                });
                const result = await response.json();
                if (!response.ok || !result.ok) throw new Error(result.message || 'อัปเดตสถานะไม่สำเร็จ');
                window.location.reload();
            } catch (error) {
                button.disabled = false;
                window.Toast.show(error.message || 'อัปเดตสถานะไม่สำเร็จ', 'err');
            }
        });
    });

    document.querySelectorAll('.toggle-account').forEach(button => {
        button.addEventListener('click', async () => {
            const action = button.dataset.active === '1' ? 'ระงับ' : 'เปิด';
            if (!window.confirm('ต้องการ' + action + 'บัญชีอาสาสมัครนี้หรือไม่?')) return;
            const body = new FormData();
            body.append('csrf_token', csrf);
            try {
                const response = await fetch(window.BASE_PATH + '/admin/users/' + button.dataset.id + '/toggle', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body
                });
                const result = await response.json();
                if (!response.ok || !result.ok) throw new Error(result.message || 'เปลี่ยนสถานะบัญชีไม่สำเร็จ');
                window.location.reload();
            } catch (error) {
                window.Toast.show(error.message || 'เปลี่ยนสถานะบัญชีไม่สำเร็จ', 'err');
            }
        });
    });
})();
</script>
