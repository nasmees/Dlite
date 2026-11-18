<?php
/** Admin: manage back-office accounts (admin / viewer). Expects $users, $csrf, $user */
$meId = (int)($user['id'] ?? 0);
$roleLabels = ['admin' => 'ผู้ดูแลระบบ', 'viewer' => 'ผู้ดู'];
?>
<div class="space-y-5 lg:space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-2xl font-semibold text-slate-900 flex items-center gap-2.5">
                <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                ผู้ดูแลระบบ
            </h2>
            <p class="text-sm text-slate-500 mt-1">จัดการบัญชีแอดมินและผู้ดู — เพิ่ม/แก้ไข/ระงับการใช้งาน</p>
        </div>
        <button type="button" id="addUser" class="btn-primary inline-flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
            เพิ่มบัญชีผู้ดูแล
        </button>
    </div>

    <section class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 text-sm text-slate-500">
            <span><?= count($users) ?> บัญชี</span>
            <span>ใช้งานอยู่ <span class="font-medium text-emerald-700"><?= count(array_filter($users, fn($u) => $u['is_active'])) ?></span></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead>
                    <tr class="border-y border-slate-200 text-left">
                        <th class="px-4 py-3">ผู้ดูแล</th>
                        <th class="px-4 py-3">บทบาท</th>
                        <th class="px-4 py-3">สถานะ</th>
                        <th class="px-4 py-3 text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($users)): ?>
                        <tr><td colspan="4" class="px-4 py-14 text-center text-slate-500">ไม่พบบัญชีผู้ดูแล</td></tr>
                    <?php else: foreach ($users as $u): ?>
                        <?php
                            $rowData = [
                                'id' => (int)$u['id'],
                                'name' => $u['name'],
                                'phone' => $u['phone'] ?? '',
                                'role' => $u['role'],
                            ];
                            $isSelf = (int)$u['id'] === $meId;
                        ?>
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-slate-900">
                                            <?= Helper::e($u['name']) ?>
                                            <?php if ($isSelf): ?><span class="text-[11px] text-slate-400">(คุณ)</span><?php endif; ?>
                                        </div>
                                        <div class="text-xs text-slate-500"><?= Helper::e(Helper::formatPhone($u['phone'])) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-md px-2 py-1 text-xs font-medium <?= $u['role'] === 'admin' ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-600' ?>">
                                    <?= Helper::e($roleLabels[$u['role']] ?? $u['role']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium <?= $u['is_active'] ? 'text-emerald-700' : 'text-slate-400' ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $u['is_active'] ? 'bg-emerald-600' : 'bg-slate-300' ?>"></span>
                                    <?= $u['is_active'] ? 'ใช้งานอยู่' : 'ปิดใช้งาน' ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button" class="edit-user rounded-md border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                    data-user="<?= Helper::e(json_encode($rowData, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">แก้ไข</button>
                                <?php if (!$isSelf): ?>
                                <button type="button" class="toggle-user ml-1 rounded-md px-3 py-1.5 text-xs font-medium <?= $u['is_active'] ? 'text-rose-700 hover:bg-rose-50' : 'text-brand-700 hover:bg-blue-50' ?>"
                                    data-id="<?= (int)$u['id'] ?>" data-active="<?= (int)$u['is_active'] ?>">
                                    <?= $u['is_active'] ? 'ระงับบัญชี' : 'เปิดบัญชี' ?>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<div id="userModal" class="hidden fixed inset-0 z-[1200] bg-slate-900/40 p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="userModalTitle">
    <div class="max-w-md mx-auto my-6 rounded-xl bg-white shadow-xl border border-slate-200">
        <div class="flex items-start justify-between gap-4 p-5 border-b border-slate-200">
            <div>
                <h2 id="userModalTitle" class="font-semibold text-lg text-slate-900">เพิ่มบัญชีผู้ดูแล</h2>
                <p class="text-sm text-slate-500 mt-1">บัญชีนี้ใช้เข้าสู่ระบบหลังบ้าน</p>
            </div>
            <button type="button" class="close-user-modal rounded-md p-1.5 text-slate-500 hover:bg-slate-100" aria-label="ปิดหน้าต่าง">✕</button>
        </div>
        <form id="userForm" class="p-5 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
            <input type="hidden" name="id" id="userId" value="">
            <label class="block text-sm font-medium text-slate-700">ชื่อ-นามสกุล
                <input type="text" name="name" id="userName" required class="input-field mt-1.5">
            </label>
            <label class="block text-sm font-medium text-slate-700">เบอร์โทรศัพท์ (ใช้เข้าสู่ระบบ)
                <input type="tel" name="phone" id="userPhone" required inputmode="tel" class="input-field mt-1.5">
            </label>
            <label class="block text-sm font-medium text-slate-700">รหัสผ่าน <span class="font-normal text-slate-500" id="userPasswordHint">จำเป็นสำหรับบัญชีใหม่</span>
                <input type="password" name="password" id="userPassword" minlength="6" class="input-field mt-1.5" autocomplete="new-password">
            </label>
            <label class="block text-sm font-medium text-slate-700">บทบาท
                <select name="role" id="userRole" class="input-field mt-1.5">
                    <option value="admin">ผู้ดูแลระบบ — จัดการได้ทุกอย่าง</option>
                    <option value="viewer">ผู้ดู — ดูข้อมูลอย่างเดียว</option>
                </select>
            </label>
            <p id="userFormMessage" class="hidden text-sm" role="status"></p>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" class="close-user-modal rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">ยกเลิก</button>
                <button type="submit" class="btn-primary">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const message = document.getElementById('userFormMessage');
    const password = document.getElementById('userPassword');
    const csrf = '<?= Helper::e($csrf) ?>';

    function showMessage(text, ok) {
        message.textContent = text;
        message.className = 'text-sm ' + (ok ? 'text-emerald-700' : 'text-rose-700');
    }

    function openModal(data) {
        form.reset();
        message.className = 'hidden text-sm';
        document.getElementById('userId').value = data ? data.id : '';
        document.getElementById('userName').value = data ? data.name : '';
        document.getElementById('userPhone').value = data ? data.phone : '';
        document.getElementById('userRole').value = data ? data.role : 'viewer';
        password.required = !data;
        document.getElementById('userPasswordHint').textContent = data ? 'เว้นว่างหากไม่ต้องการเปลี่ยน' : 'จำเป็นสำหรับบัญชีใหม่';
        modal.classList.remove('hidden');
        document.getElementById('userName').focus();
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    document.getElementById('addUser').addEventListener('click', () => openModal(null));
    document.querySelectorAll('.close-user-modal').forEach(button => button.addEventListener('click', closeModal));
    modal.addEventListener('click', event => { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeModal(); });

    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', () => openModal(JSON.parse(button.dataset.user)));
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        submit.disabled = true;
        try {
            const response = await fetch(window.BASE_PATH + '/admin/users', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            });
            const result = await response.json();
            if (!response.ok || !result.ok) throw new Error(result.message || 'บันทึกไม่สำเร็จ');
            window.Toast.show(result.message, 'ok');
            window.location.reload();
        } catch (error) {
            showMessage(error.message || 'เกิดข้อผิดพลาดในการบันทึก', false);
        } finally {
            submit.disabled = false;
        }
    });

    document.querySelectorAll('.toggle-user').forEach(button => {
        button.addEventListener('click', async () => {
            const action = button.dataset.active === '1' ? 'ระงับ' : 'เปิด';
            if (!window.confirm('ต้องการ' + action + 'บัญชีนี้หรือไม่?')) return;
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
