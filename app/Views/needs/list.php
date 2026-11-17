<div class="space-y-4">
    <!-- Header section -->
    <section class="rounded-2xl border-2 border-slate-300 bg-white shadow-md p-5 mb-4">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <h1 class="font-semibold text-slate-800 text-lg">รายการความต้องการ</h1>
            </div>
            <div class="flex items-center gap-3">
                <?php if (Auth::check()): ?>
                    <a href="<?= Helper::url('map') ?>" class="text-sm text-brand-600 font-medium">แผนที่เต็มจอ</a>
                <?php endif; ?>
                <a href="<?= Helper::url() ?>" class="text-sm text-brand-600 font-medium">+ แจ้งใหม่</a>
            </div>
        </div>

        <!-- Search & filter -->
        <form method="get" class="flex gap-2 mb-4">
            <input type="search" name="q" value="<?= Helper::e($filters['q'] ?? '') ?>" placeholder="ค้นหาชื่อ/รหัส/พื้นที่..." class="w-full border-2 border-slate-300 rounded-xl px-3 py-2 text-sm bg-white">
            <select name="status" class="w-36 border-2 border-slate-300 rounded-xl px-3 py-2 text-sm bg-white" onchange="this.form.submit()">
                <option value="">ทุกสถานะ</option>
                <?php foreach ($statusLabels as $k => $v): ?>
                    <option value="<?= $k ?>" <?= ($filters['status'] ?? '') === $k ? 'selected' : '' ?>><?= Helper::e($v) ?></option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit" class="btn-primary px-4 py-2.5 text-sm">ค้นหา</button></noscript>
        </form>

        <!-- Status summary cards (same style as home) -->
        <?php
        $statChips = [
            ['label'=>'ทั้งหมด','key'=>'all','url'=>'needs','icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>','bg'=>'bg-slate-700','text'=>'text-white'],
            ['label'=>'รอความช่วยเหลือ','key'=>'pending','url'=>'needs?status=pending','icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>','bg'=>'bg-red-600','text'=>'text-white'],
            ['label'=>'กำลังช่วยเหลือ','key'=>'in_progress','url'=>'needs?status=in_progress','icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>','bg'=>'bg-amber-500','text'=>'text-white'],
            ['label'=>'ช่วยเหลือสำเร็จ','key'=>'completed','url'=>'needs?status=completed','icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>','bg'=>'bg-emerald-600','text'=>'text-white'],
        ];
        ?>
        <div class="flex gap-3 overflow-x-auto pb-1">
            <?php foreach ($statChips as $sc): ?>
            <a href="<?= Helper::url($sc['url']) ?>" class="shrink-0 relative w-14 h-14 rounded-xl <?= $sc['bg'] ?> flex items-center justify-center hover:opacity-90 transition" title="<?= Helper::e($sc['label']) ?>">
                <svg class="w-7 h-7 <?= $sc['text'] ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $sc['icon'] ?></svg>
                <?php $c = (int)($counts[$sc['key']] ?? 0); if ($c > 0): ?>
                <span class="absolute -top-1 -right-1 min-w-[20px] h-[20px] rounded-full bg-red-600 text-white text-sm font-bold flex items-center justify-center border-2 border-white"><?= $c ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- List section -->
    <section class="rounded-2xl border-2 border-slate-300 bg-white shadow-md p-5 mb-4">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <h2 class="font-semibold text-slate-800">รายการทั้งหมด</h2>
        </div>

        <div class="space-y-3" id="needList">
            <?php if (empty($needs)): ?>
                <div class="card p-8 text-center">
                    <div class="text-4xl mb-2 text-slate-300">
                        <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <p class="text-slate-500 text-sm">ยังไม่มีรายการ<?php if (!empty($filters['q']) || !empty($filters['status'])): ?>ที่ตรงกับเงื่อนไข<?php endif; ?></p>
                    <a href="<?= Helper::url() ?>" class="btn-primary inline-block mt-4 text-sm px-4 py-2">แจ้งความต้องการ</a>
                </div>
            <?php else: ?>
                <?php foreach ($needs as $n): ?>
                    <a href="<?= Helper::url('needs/' . $n['id']) ?>" class="card p-4 flex gap-4 hover:border-brand-400 hover:shadow-md transition active:bg-slate-50">
                        <?php if (!empty($n['photo_path'])): ?>
                            <img src="<?= Helper::e(Helper::photoUrl($n['photo_path'])) ?>"
                                 alt="" loading="lazy" decoding="async"
                                 class="w-14 h-14 rounded-xl object-cover shrink-0 bg-slate-100">
                        <?php else: ?>
                            <div class="w-14 h-14 rounded-xl bg-brand-50 flex items-center justify-center shrink-0">
                                <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-800 text-base truncate"><?= Helper::e($n['reporter_name']) ?></div>
                                    <div class="text-sm text-slate-500 mt-0.5"><?= Helper::e($n['code']) ?> · <?= Helper::timeAgo($n['created_at']) ?></div>
                                </div>
                                <span class="text-sm px-2.5 py-1 rounded-full font-medium <?= Helper::statusColor($n['status']) ?> shrink-0"><?= Helper::e($statusLabels[$n['status']] ?? $n['status']) ?></span>
                            </div>
                            <?php if ($n['address'] || $n['area_name']): ?>
                                <div class="flex items-center gap-1 text-sm text-slate-600 mb-2">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="truncate"><?= Helper::e($n['area_name'] ?? '') ?><?= $n['area_name'] && $n['address'] ? ' · ' : '' ?><?= Helper::e($n['address'] ?? '') ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($n['need_types'])): ?>
                                <div class="flex flex-wrap gap-1.5">
                                    <?php foreach (array_slice($n['need_types'], 0, 3) as $t): ?>
                                        <span class="text-sm bg-slate-100 text-slate-700 px-2 py-1 rounded-md font-medium"><?= Helper::e($needTypes[$t] ?? $t) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($n['need_types']) > 3): ?>
                                        <span class="text-sm text-slate-400">+<?= count($n['need_types']) - 3 ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="border-t border-slate-200 my-3"></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (($pagination['pages'] ?? 1) > 1): ?>
            <?php
                $baseParams = array_filter(['status' => $filters['status'] ?? '', 'q' => $filters['q'] ?? '']);
                $cur = $pagination['page'];
                $pages = $pagination['pages'];
            ?>
            <div class="flex items-center justify-center gap-2 pt-2">
                <?php if ($cur > 1): ?>
                    <a href="<?= Helper::e(Helper::pageUrl($cur - 1, $baseParams)) ?>" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">← ก่อนหน้า</a>
                <?php endif; ?>
                <span class="text-sm text-slate-500">หน้า <?= $cur ?> / <?= $pages ?></span>
                <?php if ($cur < $pages): ?>
                    <a href="<?= Helper::e(Helper::pageUrl($cur + 1, $baseParams)) ?>" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">ถัดไป →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php if (Auth::check()): ?>
<script>
// Auto-refresh counts badge for logged-in volunteers (every 30s)
(function() {
    async function refreshCounts() {
        try {
            const res = await fetch(window.BASE_PATH + '/api/needs-summary', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const json = await res.json();
            if (!json.ok) return;
            const badge = document.getElementById('pendingBadge');
            if (badge && json.counts) {
                badge.textContent = json.counts.pending;
                badge.style.display = json.counts.pending > 0 ? 'inline-flex' : 'none';
            }
        } catch (e) { /* offline — ignore */ }
    }
    refreshCounts();
    setInterval(refreshCounts, 30000);
})();
</script>
<?php endif; ?>
