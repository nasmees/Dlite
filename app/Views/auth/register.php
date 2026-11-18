<div class="max-w-sm mx-auto pt-8">
    <div class="text-center mb-6">
        <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        </div>
        <h1 class="text-xl font-semibold text-brand-800">สมัครอาสาสมัคร</h1>
        <p class="text-sm text-slate-500 mt-1">สมัครแล้วรอผู้ดูแลระบบอนุมัติก่อนเข้าใช้งาน</p>
    </div>
    <form id="registerForm" class="card p-5 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
        <div>
            <label class="block text-base font-semibold text-slate-800 mb-2">ชื่อ-นามสกุล</label>
            <input type="text" name="name" required class="input-field" placeholder="ชื่อ-นามสกุล" autocomplete="name">
        </div>
        <div>
            <label class="block text-base font-semibold text-slate-800 mb-2">เบอร์โทรศัพท์</label>
            <input type="tel" name="phone" required class="input-field" placeholder="08x-xxx-xxxx" inputmode="tel" autocomplete="tel">
            <p class="text-xs text-slate-400 mt-1">ใช้เป็นชื่อผู้ใช้ในการเข้าสู่ระบบ</p>
        </div>
        <div>
            <label class="block text-base font-semibold text-slate-800 mb-2">รหัสผ่าน</label>
            <input type="password" name="password" required class="input-field" placeholder="อย่างน้อย 6 ตัวอักษร" autocomplete="new-password">
        </div>
        <div>
            <label class="block text-base font-semibold text-slate-800 mb-2">ยืนยันรหัสผ่าน</label>
            <input type="password" name="confirm_password" required class="input-field" placeholder="ยืนยันรหัสผ่าน" autocomplete="new-password">
        </div>
        <div>
            <label class="block text-base font-semibold text-slate-800 mb-2">องค์กร <span class="text-sm font-normal text-slate-400">(ไม่บังคับ)</span></label>
            <select name="organization_id" class="input-field">
                <option value="">— ไม่สังกัดองค์กร —</option>
                <?php foreach ($organizations as $org): ?>
                    <option value="<?= (int)$org['id'] ?>"><?= Helper::e($org['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-base font-semibold text-slate-800 mb-2">ทักษะ <span class="text-sm font-normal text-slate-400">(เลือกได้หลายข้อ)</span></label>
            <div class="grid grid-cols-2 gap-1.5">
                <?php foreach ($skills as $key => $label): ?>
                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-2.5 py-2 text-sm text-slate-700 cursor-pointer hover:border-brand-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                        <input type="checkbox" name="skills[]" value="<?= Helper::e($key) ?>" class="rounded border-slate-300 text-brand-600 focus:ring-brand-600">
                        <?= Helper::e($label) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <label class="block text-base font-semibold text-slate-800 mb-2">อุปกรณ์ <span class="text-sm font-normal text-slate-400">(เลือกได้หลายข้อ)</span></label>
            <div class="grid grid-cols-2 gap-1.5">
                <?php foreach ($equipment as $key => $label): ?>
                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-2.5 py-2 text-sm text-slate-700 cursor-pointer hover:border-brand-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                        <input type="checkbox" name="equipment[]" value="<?= Helper::e($key) ?>" class="rounded border-slate-300 text-brand-600 focus:ring-brand-600">
                        <?= Helper::e($label) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit" id="registerBtn" class="w-full min-h-[56px] rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-lg font-bold transition shadow-lg">สมัครอาสาสมัคร</button>
        <p id="registerMsg" class="text-sm text-center hidden"></p>
    </form>
    <p class="text-sm text-center text-slate-400 mt-4">
        มีบัญชีแล้ว? <a href="<?= Helper::url('login') ?>" class="text-brand-600 font-medium hover:underline">เข้าสู่ระบบ</a>
    </p>
</div>
<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('registerBtn');
    const fd = new FormData(e.target);
    const msg = document.getElementById('registerMsg');
    btn.disabled = true;
    btn.textContent = 'กำลังสมัคร...';
    try {
        const res = await fetch(window.BASE_PATH + '/register', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        const json = await res.json();
        msg.classList.remove('hidden');
        if (json.ok) {
            msg.textContent = json.message;
            msg.className = 'text-sm text-center text-emerald-600';
            setTimeout(() => { window.location.href = window.BASE_PATH + '/login'; }, 2000);
        } else {
            msg.textContent = json.message || 'สมัครไม่สำเร็จ';
            msg.className = 'text-sm text-center text-red-600';
            btn.disabled = false;
            btn.textContent = 'สมัครอาสาสมัคร';
        }
    } catch (err) {
        msg.classList.remove('hidden');
        msg.textContent = 'เครือข่ายมีปัญหา กรุณาลองใหม่';
        msg.className = 'text-sm text-center text-red-600';
        btn.disabled = false;
        btn.textContent = 'สมัครอาสาสมัคร';
    }
});
</script>
