<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">แดชบอร์ด</h1>
            <p class="text-slate-500 mt-1">สวัสดี <?= Helper::e($user['name']) ?></p>
        </div>
        <div class="text-sm text-slate-400">
            <?php $now = new DateTime('now', new DateTimeZone('Asia/Bangkok')); ?>
            <?= $now->format('d/m/Y H:i') ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="<?= Helper::url('admin/requests?status=pending') ?>" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                </span>
                <span class="text-xs font-medium text-slate-400">รอรับเรื่อง</span>
            </div>
            <div class="text-3xl font-bold text-amber-600"><?= (int)$counts['pending'] ?></div>
        </a>
        <a href="<?= Helper::url('admin/requests?status=accepted') ?>" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <span class="text-xs font-medium text-slate-400">รับเรื่องแล้ว</span>
            </div>
            <div class="text-3xl font-bold text-blue-600"><?= (int)$counts['accepted'] ?></div>
        </a>
        <a href="<?= Helper::url('admin/requests?status=in_progress') ?>" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                <span class="text-xs font-medium text-slate-400">กำลังช่วยเหลือ</span>
            </div>
            <div class="text-3xl font-bold text-indigo-600"><?= (int)$counts['in_progress'] ?></div>
        </a>
        <a href="<?= Helper::url('admin/requests?status=completed') ?>" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <span class="text-xs font-medium text-slate-400">เสร็จสิ้น</span>
            </div>
            <div class="text-3xl font-bold text-emerald-600"><?= (int)$counts['completed'] ?></div>
        </a>
    </div>

    <!-- Pending Requests -->
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-800 text-lg">คำขอที่รอรับเรื่อง</h2>
            <a href="<?= Helper::url('admin/requests?status=pending') ?>" class="text-sm text-brand-600 font-medium hover:underline">ดูทั้งหมด</a>
        </div>
        <?php if (empty($recent)): ?>
            <div class="text-center py-8 text-slate-400 text-sm">ไม่มีรายการรอรับเรื่อง</div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recent as $n): ?>
                    <a href="<?= Helper::url('admin/requests/' . $n['id']) ?>" class="flex items-center justify-between gap-4 p-3 rounded-lg hover:bg-slate-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <div class="font-medium text-slate-800"><?= Helper::e($n['reporter_name']) ?></div>
                                <div class="text-sm text-slate-500"><?= Helper::e($n['code']) ?> · <?= Helper::timeAgo($n['created_at']) ?></div>
                            </div>
                        </div>
                        <span class="text-sm px-3 py-1 rounded-full font-medium <?= Helper::urgencyColor($n['urgency']) ?>"><?= Helper::e($urgencyLabels[$n['urgency']] ?? '') ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="<?= Helper::url('admin/requests') ?>" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-md transition">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-lg bg-brand-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
                <span class="font-medium text-slate-800">จัดการคำขอ</span>
            </div>
        </a>
        <?php if (!empty($isAdmin)): ?>
        <a href="<?= Helper::url('admin/users') ?>" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-md transition">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-lg bg-brand-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </span>
                <span class="font-medium text-slate-800">ผู้ใช้</span>
            </div>
        </a>
        <a href="<?= Helper::url('admin/areas') ?>" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-md transition">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-lg bg-brand-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                <span class="font-medium text-slate-800">พื้นที่</span>
            </div>
        </a>
        <?php endif; ?>
    </div>
</div>
