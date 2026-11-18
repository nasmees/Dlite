<div class="max-w-sm mx-auto pt-8">
    <div class="text-center mb-6">
        <div class="w-16 h-16 rounded-2xl bg-brand-50 flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
        </div>
        <h1 class="text-xl font-semibold text-brand-800">เข้าสู่ระบบ</h1>
        <p class="text-sm text-slate-500 mt-1">สำหรับอาสาสมัครและผู้ดูแลระบบ</p>
    </div>
    <form id="loginForm" class="card p-5 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
        <div>
            <label class="flex items-center gap-1.5 text-base font-semibold text-slate-800 mb-2">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                เบอร์โทรศัพท์
            </label>
            <input type="tel" name="phone" required class="input-field" placeholder="08x-xxx-xxxx" inputmode="tel" autocomplete="tel">
        </div>
        <div>
            <label class="flex items-center gap-1.5 text-base font-semibold text-slate-800 mb-2">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                รหัสผ่าน
            </label>
            <input type="password" name="password" required class="input-field" placeholder="รหัสผ่าน" autocomplete="current-password">
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer select-none">
            <input type="checkbox" name="remember" value="1" checked class="rounded border-slate-300 text-brand-600 w-4 h-4">
            จดจำการเข้าสู่ระบบ 30 วัน
        </label>
        <button type="submit" id="loginBtn" class="w-full min-h-[56px] rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-lg font-bold transition shadow-lg">เข้าสู่ระบบ</button>
        <p id="loginMsg" class="text-sm text-center hidden"></p>
    </form>
    <p class="text-sm text-center text-slate-400 mt-4">
        ยังไม่มีบัญชีอาสาสมัคร? <a href="<?= Helper::url('register') ?>" class="text-emerald-600 font-medium hover:underline">สมัครอาสาสมัคร</a>
    </p>
</div>
<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('loginBtn');
    const fd = new FormData(e.target);
    const msg = document.getElementById('loginMsg');
    btn.disabled = true;
    btn.textContent = 'กำลังเข้าสู่ระบบ...';
    try {
        const res = await fetch(window.BASE_PATH + '/login', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        const json = await res.json();
        msg.classList.remove('hidden');
        if (json.ok) {
            msg.textContent = json.message;
            msg.className = 'text-sm text-center text-emerald-600';
            window.location.href = json.redirect || (window.BASE_PATH + '/admin');
        } else {
            msg.textContent = json.message || 'เข้าสู่ระบบไม่สำเร็จ';
            msg.className = 'text-sm text-center text-red-600';
            btn.disabled = false;
            btn.textContent = 'เข้าสู่ระบบ';
        }
    } catch (err) {
        msg.classList.remove('hidden');
        msg.textContent = 'เครือข่ายมีปัญหา กรุณาลองใหม่';
        msg.className = 'text-sm text-center text-red-600';
        btn.disabled = false;
        btn.textContent = 'เข้าสู่ระบบ';
    }
});
</script>
