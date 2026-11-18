<?php
/**
 * Public needs list — compact cards, status tabs with counts, filters
 * Expects: $needs, $counts, $pagination, $filters, $isLoggedIn,
 *          $statusLabels, $urgencyLabels, $scenarioLabels, $needTypes
 */
$currentStatus = $filters['status'] ?? '';
$tabParams = array_filter([
    'q' => $filters['q'] ?? '',
    'urgency' => $filters['urgency'] ?? '',
    'scenario' => $filters['scenario'] ?? '',
    'sort' => ($filters['sort'] ?? 'latest') === 'latest' ? '' : ($filters['sort'] ?? ''),
]);
$tabUrl = function (?string $status) use ($tabParams) {
    $p = $tabParams;
    if ($status !== null && $status !== '') $p['status'] = $status;
    return Helper::url('needs') . ($p ? '?' . http_build_query($p) : '');
};
$tabs = ['active' => 'กำลังดำเนินการ'] + $statusLabels + ['all' => 'ทั้งหมด'];
?>
<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900">รายการความต้องการ</h1>
            <p class="text-sm text-slate-500">เหตุการณ์ที่กำลังดำเนินการอยู่</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="<?= Helper::url('map') ?>" class="text-sm text-brand-600 font-medium">แผนที่</a>
            <a href="<?= Helper::url() ?>" class="text-sm font-semibold text-white <?= Helper::appMode() === 'relief' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700' ?> px-3.5 py-2 rounded-xl">+ แจ้งใหม่</a>
        </div>
    </div>

    <!-- Filters -->
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm p-3 sm:p-4">
        <!-- Status tabs with counts -->
        <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1">
            <?php foreach ($tabs as $key => $label): ?>
                <?php
                $active = $currentStatus === $key;
                $cnt = $key === 'active'
                    ? (int)($counts['pending'] + $counts['accepted'] + $counts['in_progress'])
                    : (int)($counts[$key] ?? 0);
                ?>
                <a href="<?= Helper::e($tabUrl($key)) ?>"
                   class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-medium transition <?= $active ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    <?= Helper::e($label) ?>
                    <span class="text-xs <?= $active ? 'text-white/80' : 'text-slate-400' ?>"><?= $cnt ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <form method="get" class="flex flex-wrap gap-2 mt-3">
            <?php if ($currentStatus !== ''): ?>
                <input type="hidden" name="status" value="<?= Helper::e($currentStatus) ?>">
            <?php endif; ?>
            <input type="search" name="q" value="<?= Helper::e($filters['q'] ?? '') ?>" placeholder="ค้นหา..." class="flex-1 min-w-[140px] border border-slate-300 rounded-xl px-3 py-2 text-sm bg-white">
            <noscript><button type="submit" class="btn-primary px-4 py-2 text-sm">ค้นหา</button></noscript>
        </form>

        <!-- Advanced filters (collapsed by default) -->
        <details class="mt-2 group" <?= (!empty($filters['urgency']) || !empty($filters['scenario']) || ($filters['sort'] ?? 'latest') === 'urgent') ? 'open' : '' ?>>
            <summary class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-700 cursor-pointer select-none list-none">
                <svg class="w-4 h-4 transition group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                ตัวกรองเพิ่มเติม
                <?php if (!empty($filters['urgency']) || !empty($filters['scenario']) || ($filters['sort'] ?? 'latest') === 'urgent'): ?>
                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                <?php endif; ?>
            </summary>
            <form method="get" class="flex flex-wrap gap-2 mt-2">
                <?php if ($currentStatus !== ''): ?>
                    <input type="hidden" name="status" value="<?= Helper::e($currentStatus) ?>">
                <?php endif; ?>
                <?php if (!empty($filters['q'])): ?>
                    <input type="hidden" name="q" value="<?= Helper::e($filters['q']) ?>">
                <?php endif; ?>
                <select name="urgency" class="border border-slate-300 rounded-xl px-2.5 py-2 text-sm bg-white flex-1 min-w-[130px]" onchange="this.form.submit()">
                    <option value="">ทุกความเร่งด่วน</option>
                    <?php foreach ($urgencyLabels as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($filters['urgency'] ?? '') === $k ? 'selected' : '' ?>><?= Helper::e($v) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="scenario" class="border border-slate-300 rounded-xl px-2.5 py-2 text-sm bg-white flex-1 min-w-[130px]" onchange="this.form.submit()">
                    <option value="">ทุกประเภทเหตุการณ์</option>
                    <?php foreach ($scenarioLabels as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($filters['scenario'] ?? '') === $k ? 'selected' : '' ?>><?= Helper::e($v) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="sort" class="border border-slate-300 rounded-xl px-2.5 py-2 text-sm bg-white" onchange="this.form.submit()">
                    <option value="latest" <?= ($filters['sort'] ?? 'latest') === 'latest' ? 'selected' : '' ?>>ล่าสุด</option>
                    <option value="urgent" <?= ($filters['sort'] ?? '') === 'urgent' ? 'selected' : '' ?>>เร่งด่วนสุด</option>
                </select>
            </form>
        </details>
    </section>

    <!-- List -->
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm p-3 sm:p-4">
        <div class="space-y-1" id="needList">
            <?php if (empty($needs)): ?>
                <div class="p-8 text-center">
                    <p class="text-slate-500 text-sm">ยังไม่มีรายการ<?php if (($currentStatus !== '' && $currentStatus !== 'active') || !empty($filters['q']) || !empty($filters['urgency']) || !empty($filters['scenario'])): ?>ที่ตรงกับเงื่อนไข<?php endif; ?></p>
                    <a href="<?= Helper::url() ?>" class="btn-primary inline-block mt-4 text-sm px-4 py-2">แจ้งความต้องการ</a>
                </div>
            <?php else: ?>
                <?php foreach ($needs as $n): ?>
                    <?php
                    $place = ($n['district'] ?? '') !== '' ? $n['district'] : ($n['area_name'] ?? '');
                    $locParts = array_values(array_unique(array_filter([$place, $n['province'] ?? ''])));
                    $meta = array_merge(
                        !empty($isLoggedIn) && $n['reporter_name'] !== '' ? [$n['reporter_name']] : [],
                        $locParts ? [implode(' · ', array_slice($locParts, 0, 2))] : [],
                        ((int)($n['people_count'] ?? 0) > 0) ? ['~' . number_format((int)$n['people_count']) . ' คน'] : [],
                        array_map(fn($t) => Helper::needTypeLabel($t), array_slice($n['need_types'] ?? [], 0, 3))
                    );
                    if (count($n['need_types'] ?? []) > 3) {
                        $meta[] = '+' . (count($n['need_types']) - 3);
                    }
                    $isUrgent = in_array($n['urgency'], ['high', 'critical'], true);
                    ?>
                    <a href="<?= Helper::url('needs/' . $n['id']) ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-slate-50 active:bg-slate-100 transition">
                        <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-slate-800 truncate"><?= Helper::e(Helper::scenarioLabel($n['scenario'] ?? null) ?: 'เหตุการณ์') ?> <span class="font-normal text-slate-400">· <?= Helper::e($n['code']) ?> · <?= Helper::timeAgo($n['created_at']) ?></span></p>
                                <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full font-medium <?= Helper::statusColor($n['status']) ?> shrink-0"><?= Helper::statusDot($n['status']) ?><?= Helper::e($statusLabels[$n['status']] ?? $n['status']) ?></span>
                            </div>
                            <p class="text-sm text-slate-600 truncate mt-0.5"><?= Helper::e(implode(' · ', $meta)) ?><?= $isUrgent ? ' · <span class="' . ($n['urgency'] === 'critical' ? 'text-red-600 font-semibold' : 'text-orange-600 font-medium') . '">' . Helper::e($urgencyLabels[$n['urgency']]) . '</span>' : '' ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (($pagination['pages'] ?? 1) > 1): ?>
            <?php
                $baseParams = array_filter([
                    'status' => $currentStatus, 'q' => $filters['q'] ?? '',
                    'urgency' => $filters['urgency'] ?? '', 'scenario' => $filters['scenario'] ?? '',
                    'sort' => ($filters['sort'] ?? 'latest') === 'latest' ? '' : ($filters['sort'] ?? ''),
                ]);
                $cur = $pagination['page'];
                $pages = $pagination['pages'];
            ?>
            <div class="flex items-center justify-center gap-2 pt-3">
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
