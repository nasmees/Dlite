<div class="space-y-5 lg:space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-2xl font-semibold text-slate-900 flex items-center gap-2.5">
                <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01"/></svg>
                องค์กรเครือข่าย
            </h2>
            <p class="text-sm text-slate-500 mt-1">จัดการหน่วยงานพันธมิตรและข้อมูลผู้ประสานงาน</p>
        </div>
        <button type="button" id="addOrganization" class="btn-primary inline-flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
            เพิ่มองค์กร
        </button>
    </div>

    <?php if (empty($organizations)): ?>
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <div class="mx-auto w-12 h-12 rounded-lg bg-blue-50 text-brand-700 flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01"/></svg>
            </div>
            <p class="font-medium text-slate-800">ยังไม่มีองค์กรเครือข่าย</p>
            <p class="text-sm text-slate-500 mt-1">เพิ่มองค์กรเพื่อเชื่อมโยงกับอาสาสมัคร</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4">
            <?php foreach ($organizations as $organization): ?>
                <?php
                    $orgData = [
                        'id' => (int)$organization['id'],
                        'name' => $organization['name'],
                        'logo_url' => $organization['logo_url'],
                        'verified' => (int)$organization['verified'],
                        'contact_name' => $organization['contact_name'],
                        'contact_phone' => $organization['contact_phone'],
                        'contact_email' => $organization['contact_email'],
                        'description' => $organization['description'],
                        'is_active' => (int)$organization['is_active'],
                        'is_sample' => (int)$organization['is_sample'],
                        'volunteer_count' => (int)$organization['volunteer_count'],
                    ];
                ?>
                <article class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col">
                    <div class="flex items-start gap-3">
                        <div class="org-logo-box relative w-12 h-12 rounded-lg bg-blue-50 text-brand-700 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01"/></svg>
                            <?php if (!empty($organization['logo_url'])): ?>
                                <img src="<?= Helper::e($organization['logo_url']) ?>" alt="" referrerpolicy="no-referrer" class="absolute inset-0 w-full h-full rounded-lg object-cover bg-white">
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-slate-900"><?= Helper::e($organization['name']) ?></h3>
                                <?php if ((bool)$organization['verified']): ?>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-800">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.78-9.78a.75.75 0 00-1.06-1.06L9 10.94 7.28 9.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.06 0l4.25-4.25z" clip-rule="evenodd"/></svg>
                                        ยืนยันแล้ว
                                    </span>
                                <?php else: ?>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">ยังไม่ยืนยัน</span>
                                <?php endif; ?>
                            </div>
                            <?php if ((bool)$organization['is_sample']): ?><p class="text-xs text-slate-400 mt-1">ข้อมูลองค์กรตัวอย่าง</p><?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 my-4 py-4 border-y border-slate-100">
                        <div>
                            <p class="text-xs text-slate-500">อาสาสมัครในสังกัด</p>
                            <p class="text-xl font-semibold text-slate-900 mt-1"><?= (int)$organization['volunteer_count'] ?> <span class="text-sm font-normal text-slate-500">คน</span></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">สถานะองค์กร</p>
                            <p class="mt-1 text-sm font-medium <?= $organization['is_active'] ? 'text-emerald-700' : 'text-slate-500' ?>">
                                <?= $organization['is_active'] ? 'ใช้งาน' : 'ปิดใช้งาน' ?>
                            </p>
                        </div>
                    </div>

                    <div class="space-y-1.5 text-sm text-slate-600 flex-1">
                        <p class="font-medium text-slate-800"><?= Helper::e($organization['contact_name'] ?: 'ไม่ระบุผู้ประสานงาน') ?></p>
                        <p><?= Helper::e($organization['contact_phone'] ?: 'ไม่ระบุเบอร์ติดต่อ') ?></p>
                    </div>

                    <div class="flex items-center justify-between gap-2 mt-5">
                        <button type="button" class="view-organization text-sm font-medium text-brand-700 hover:underline"
                            data-organization="<?= Helper::e(json_encode($orgData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>">
                            ดูรายละเอียดองค์กร
                        </button>
                        <button type="button" class="edit-organization rounded-md border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                            data-organization="<?= Helper::e(json_encode($orgData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>">
                            แก้ไข
                        </button>
                        <button type="button" class="delete-organization rounded-md px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50"
                            data-id="<?= (int)$organization['id'] ?>" data-name="<?= Helper::e($organization['name']) ?>">ลบ</button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="organizationFormModal" class="hidden fixed inset-0 z-[1200] bg-slate-900/40 p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="organizationFormTitle">
    <div class="max-w-xl mx-auto my-6 rounded-xl bg-white shadow-xl border border-slate-200">
        <div class="flex items-start justify-between gap-4 p-5 border-b border-slate-200">
            <div>
                <h2 id="organizationFormTitle" class="font-semibold text-lg text-slate-900">เพิ่มองค์กร</h2>
                <p class="text-sm text-slate-500 mt-1">กรอกข้อมูลสำหรับประสานงานเครือข่าย</p>
            </div>
            <button type="button" class="close-organization-modal rounded-md p-1.5 text-slate-500 hover:bg-slate-100" aria-label="ปิดหน้าต่าง">✕</button>
        </div>
        <form id="organizationForm" class="p-5 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
            <input type="hidden" name="id" id="organizationId">
            <label class="block text-sm font-medium text-slate-700">ชื่อองค์กร *
                <input type="text" name="name" id="organizationName" required class="input-field mt-1.5">
            </label>
            <label class="block text-sm font-medium text-slate-700">ลิงก์โลโก้ (HTTPS)
                <input type="url" name="logo_url" id="organizationLogo" placeholder="https://example.org/logo.png" class="input-field mt-1.5">
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="block text-sm font-medium text-slate-700">ผู้ประสานงาน
                    <input type="text" name="contact_name" id="organizationContact" class="input-field mt-1.5">
                </label>
                <label class="block text-sm font-medium text-slate-700">เบอร์ติดต่อ
                    <input type="tel" name="contact_phone" id="organizationPhone" inputmode="tel" class="input-field mt-1.5">
                </label>
            </div>
            <label class="block text-sm font-medium text-slate-700">อีเมล
                <input type="email" name="contact_email" id="organizationEmail" class="input-field mt-1.5">
            </label>
            <label class="block text-sm font-medium text-slate-700">รายละเอียด
                <textarea name="description" id="organizationDescription" rows="3" class="input-field mt-1.5"></textarea>
            </label>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="verified" id="organizationVerified" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-600">
                ยืนยันตัวตนองค์กรแล้ว
            </label>
            <p id="organizationFormMessage" class="hidden text-sm" role="status"></p>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" class="close-organization-modal rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">ยกเลิก</button>
                <button type="submit" class="btn-primary">บันทึกองค์กร</button>
            </div>
        </form>
    </div>
</div>

<div id="organizationDetailsModal" class="hidden fixed inset-0 z-[1200] bg-slate-900/40 p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="organizationDetailsTitle">
    <div class="max-w-lg mx-auto my-12 rounded-xl bg-white shadow-xl border border-slate-200">
        <div class="flex items-start justify-between gap-4 p-5 border-b border-slate-200">
            <div>
                <h2 id="organizationDetailsTitle" class="font-semibold text-lg text-slate-900"></h2>
                <p id="organizationDetailsBadge" class="text-sm text-slate-500 mt-1"></p>
            </div>
            <button type="button" class="close-organization-details rounded-md p-1.5 text-slate-500 hover:bg-slate-100" aria-label="ปิดรายละเอียด">✕</button>
        </div>
        <div class="p-5 space-y-4 text-sm">
            <p id="organizationDetailsDescription" class="text-slate-600"></p>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs text-slate-500">อาสาสมัครในสังกัด</p><p id="organizationDetailsCount" class="font-medium text-slate-900 mt-1"></p></div>
                <div><p class="text-xs text-slate-500">ผู้ประสานงาน</p><p id="organizationDetailsContact" class="font-medium text-slate-900 mt-1"></p></div>
                <div><p class="text-xs text-slate-500">เบอร์ติดต่อ</p><p id="organizationDetailsPhone" class="font-medium text-slate-900 mt-1"></p></div>
                <div><p class="text-xs text-slate-500">อีเมล</p><p id="organizationDetailsEmail" class="font-medium text-slate-900 mt-1 break-all"></p></div>
            </div>
        </div>
        <div class="flex justify-between gap-2 p-5 border-t border-slate-100">
            <button type="button" id="toggleOrganization" class="text-sm font-medium text-rose-700 hover:underline"></button>
            <div class="flex gap-2">
                <button type="button" class="close-organization-details rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">ปิด</button>
                <button type="button" id="editOrganizationFromDetails" class="btn-primary">แก้ไข</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const formModal = document.getElementById('organizationFormModal');
    const detailsModal = document.getElementById('organizationDetailsModal');
    const form = document.getElementById('organizationForm');
    const message = document.getElementById('organizationFormMessage');
    const csrf = '<?= Helper::e($csrf) ?>';
    let activeOrganization = null;

    function closeForm() { formModal.classList.add('hidden'); }
    function closeDetails() { detailsModal.classList.add('hidden'); }

    function openForm(data) {
        form.reset();
        message.className = 'hidden text-sm';
        document.getElementById('organizationId').value = data ? data.id : '';
        document.getElementById('organizationName').value = data ? data.name : '';
        document.getElementById('organizationLogo').value = data ? (data.logo_url || '') : '';
        document.getElementById('organizationContact').value = data ? (data.contact_name || '') : '';
        document.getElementById('organizationPhone').value = data ? (data.contact_phone || '') : '';
        document.getElementById('organizationEmail').value = data ? (data.contact_email || '') : '';
        document.getElementById('organizationDescription').value = data ? (data.description || '') : '';
        document.getElementById('organizationVerified').checked = data ? Boolean(Number(data.verified)) : false;
        document.getElementById('organizationFormTitle').textContent = data ? 'แก้ไของค์กร' : 'เพิ่มองค์กร';
        closeDetails();
        formModal.classList.remove('hidden');
        document.getElementById('organizationName').focus();
    }

    function openDetails(data) {
        activeOrganization = data;
        document.getElementById('organizationDetailsTitle').textContent = data.name;
        document.getElementById('organizationDetailsBadge').textContent =
            (Number(data.verified) ? 'ยืนยันตัวตนแล้ว' : 'ยังไม่ยืนยันตัวตน') +
            ' · ' + (Number(data.is_active) ? 'ใช้งาน' : 'ปิดใช้งาน') +
            (Number(data.is_sample) ? ' · ข้อมูลตัวอย่าง' : '');
        document.getElementById('organizationDetailsDescription').textContent = data.description || 'ไม่มีรายละเอียดเพิ่มเติม';
        document.getElementById('organizationDetailsCount').textContent = Number(data.volunteer_count).toLocaleString('th-TH') + ' คน';
        document.getElementById('organizationDetailsContact').textContent = data.contact_name || 'ไม่ระบุ';
        document.getElementById('organizationDetailsPhone').textContent = data.contact_phone || 'ไม่ระบุ';
        document.getElementById('organizationDetailsEmail').textContent = data.contact_email || 'ไม่ระบุ';
        document.getElementById('toggleOrganization').textContent = Number(data.is_active) ? 'ปิดใช้งานองค์กร' : 'เปิดใช้งานองค์กร';
        detailsModal.classList.remove('hidden');
    }

    document.getElementById('addOrganization').addEventListener('click', () => openForm(null));
    document.querySelectorAll('.close-organization-modal').forEach(button => button.addEventListener('click', closeForm));
    document.querySelectorAll('.close-organization-details').forEach(button => button.addEventListener('click', closeDetails));
    formModal.addEventListener('click', event => { if (event.target === formModal) closeForm(); });
    detailsModal.addEventListener('click', event => { if (event.target === detailsModal) closeDetails(); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') { closeForm(); closeDetails(); }
    });

    document.querySelectorAll('.org-logo-box img').forEach(image => {
        image.addEventListener('error', () => image.remove());
    });
    document.querySelectorAll('.view-organization').forEach(button => {
        button.addEventListener('click', () => openDetails(JSON.parse(button.dataset.organization)));
    });
    document.querySelectorAll('.edit-organization').forEach(button => {
        button.addEventListener('click', () => openForm(JSON.parse(button.dataset.organization)));
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        submit.disabled = true;
        try {
            const response = await fetch(window.BASE_PATH + '/admin/organizations', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            });
            const result = await response.json();
            if (!response.ok || !result.ok) throw new Error(result.message || 'บันทึกองค์กรไม่สำเร็จ');
            window.Toast.show(result.message, 'ok');
            window.location.reload();
        } catch (error) {
            message.textContent = error.message || 'บันทึกองค์กรไม่สำเร็จ';
            message.className = 'text-sm text-rose-700';
        } finally {
            submit.disabled = false;
        }
    });

    document.getElementById('editOrganizationFromDetails').addEventListener('click', () => {
        if (activeOrganization) openForm(activeOrganization);
    });

    document.getElementById('toggleOrganization').addEventListener('click', async () => {
        if (!activeOrganization) return;
        const verb = Number(activeOrganization.is_active) ? 'ปิด' : 'เปิด';
        if (!window.confirm('ต้องการ' + verb + 'ใช้งานองค์กรนี้หรือไม่?')) return;
        const body = new FormData();
        body.append('csrf_token', csrf);
        try {
            const response = await fetch(window.BASE_PATH + '/admin/organizations/' + activeOrganization.id + '/toggle', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body
            });
            const result = await response.json();
            if (!response.ok || !result.ok) throw new Error(result.message || 'เปลี่ยนสถานะองค์กรไม่สำเร็จ');
            window.location.reload();
        } catch (error) {
            window.Toast.show(error.message || 'เปลี่ยนสถานะองค์กรไม่สำเร็จ', 'err');
        }
    });

    document.querySelectorAll('.delete-organization').forEach(button => {
        button.addEventListener('click', async () => {
            if (!window.confirm('ลบองค์กร "' + button.dataset.name + '" ถาวร? อาสาในองค์กรจะกลายเป็นไม่สังกัดองค์กร')) return;
            const body = new FormData();
            body.append('csrf_token', csrf);
            button.disabled = true;
            try {
                const response = await fetch(window.BASE_PATH + '/admin/organizations/' + button.dataset.id + '/delete', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body
                });
                const result = await response.json();
                if (!response.ok || !result.ok) throw new Error(result.message || 'ลบองค์กรไม่สำเร็จ');
                window.location.reload();
            } catch (error) {
                button.disabled = false;
                window.Toast.show(error.message || 'ลบองค์กรไม่สำเร็จ', 'err');
            }
        });
    });
})();
</script>
