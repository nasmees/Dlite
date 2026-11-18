<div class="max-w-sm mx-auto pt-4">
    <div class="mb-5">
        <h1 class="text-2xl font-semibold text-slate-900">เปลี่ยนรหัสผ่าน</h1>
        <p class="text-sm text-slate-500 mt-1"><?= Helper::e($user['name'] ?? '') ?></p>
    </div>
    <form id="pwForm" class="card p-5 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
        <div>
            <label class="block text-base font-semibold text-slate-800 mb-2">รหัสผ่านปัจจุบัน</label>
            <input type="password" name="current_password" required class="input-field" autocomplete="current-password">
        </div>
        <div>
            <label class="block text-base font-semibold text-slate-800 mb-2">รหัสผ่านใหม่</label>
            <input type="password" name="new_password" required minlength="6" class="input-field" autocomplete="new-password">
            <p class="text-xs text-slate-400 mt-1">อย่างน้อย 6 ตัวอักษร</p>
        </div>
        <div>
            <label class="block text-base font-semibold text-slate-800 mb-2">ยืนยันรหัสผ่านใหม่</label>
            <input type="password" name="confirm_password" required minlength="6" class="input-field" autocomplete="new-password">
        </div>
        <button type="submit" id="pwBtn" class="w-full min-h-[48px] rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold transition">บันทึกรหัสผ่าน</button>
        <p id="pwMsg" class="text-sm text-center hidden"></p>
    </form>
</div>
<script>
document.getElementById('pwForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('pwBtn');
    const msg = document.getElementById('pwMsg');
    const fd = new FormData(e.target);
    btn.disabled = true;
    btn.textContent = 'กำลังบันทึก...';
    try {
        const res = await fetch(window.BASE_PATH + '/password', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        const json = await res.json();
        msg.classList.remove('hidden');
        if (json.ok) {
            msg.textContent = json.message;
            msg.className = 'text-sm text-center text-emerald-600';
            e.target.reset();
        } else {
            msg.textContent = json.message || 'บันทึกไม่สำเร็จ';
            msg.className = 'text-sm text-center text-red-600';
        }
    } catch (err) {
        msg.classList.remove('hidden');
        msg.textContent = 'เครือข่ายมีปัญหา กรุณาลองใหม่';
        msg.className = 'text-sm text-center text-red-600';
    }
    btn.disabled = false;
    btn.textContent = 'บันทึกรหัสผ่าน';
});
</script>
