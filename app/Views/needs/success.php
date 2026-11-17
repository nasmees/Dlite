<div class="text-center py-10 space-y-4">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mb-2">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    </div>
    <h1 class="text-xl font-semibold text-slate-800">ส่งคำขอสำเร็จ</h1>
    <?php if (!empty($code) && !str_starts_with($code, 'OFFLINE')): ?>
        <p class="text-slate-600">รหัสอ้างอิงของคุณ</p>
        <p class="text-2xl font-bold text-brand-700 tracking-wide"><?= Helper::e($code) ?></p>
        <p class="text-sm text-slate-500">บันทึกรหัสนี้ไว้เพื่อติดตามสถานะได้ทุกเมื่อ</p>
        <a href="<?= Helper::url('track?code=' . urlencode($code)) ?>" class="inline-block text-brand-600 font-medium text-sm mt-1 hover:underline">ติดตามสถานะ →</a>
    <?php elseif (!empty($code)): ?>
        <p class="text-slate-600">คำขอของคุณถูกบันทึกไว้ในเครื่องแล้ว</p>
        <p class="text-sm text-slate-500">ระบบจะส่งให้อัตโนมัติเมื่อกลับมาออนไลน์</p>
    <?php endif; ?>
    <div class="pt-4 flex flex-col sm:flex-row gap-3 justify-center">
        <a href="<?= Helper::url('track') ?>" class="btn-primary inline-block text-center">ติดตามสถานะ</a>
        <a href="<?= Helper::url() ?>" class="bg-slate-100 text-slate-700 font-medium rounded-xl px-5 py-3 text-center">แจ้งเพิ่มอีก</a>
    </div>
</div>
