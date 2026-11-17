<?php
/**
 * Standalone report form page — same UI as the home drawer form.
 * Fallback for direct links / no-JS (scenario cards need JS;
 * manual need-type checkboxes inside "เพิ่มเติม" still work without JS).
 * Expects: $areas, $needTypes, $csrf
 */
$inDrawer = false;
?>
<div class="max-w-lg mx-auto">
    <div class="text-center pt-1 pb-4">
        <h1 class="text-2xl font-bold text-brand-800">แจ้งเหตุ</h1>
        <p class="text-slate-500 mt-1.5 text-base">กรอกข้อมูลสั้นๆ ทีมงานจะติดต่อกลับ</p>
    </div>

    <?php if (($_GET['error'] ?? '') === 'name'): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-3">กรุณาระบุชื่อผู้แจ้ง</div>
    <?php elseif (($_GET['error'] ?? '') === 'csrf'): ?>
        <div class="bg-amber-50 border border-amber-200 text-amber-700 rounded-xl px-4 py-3 mb-3">เซสชันหมดอายุ กรุณารีเฟรชหน้าแล้วลองใหม่</div>
    <?php endif; ?>

    <?php require APP_PATH . '/Views/needs/_form.php'; ?>

    <noscript>
        <div class="card p-4 mt-4 text-center">
            <p class="text-slate-600 mb-2">เปิด JavaScript เพื่อใช้ปุ่มเลือกประเภทเหตุการณ์</p>
            <p class="text-sm text-slate-500">สามารถเลือกความต้องการเองได้ในส่วน "เพิ่มเติม" ด้านล่าง</p>
        </div>
    </noscript>
</div>
