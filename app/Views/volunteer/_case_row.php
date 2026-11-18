<?php
/**
 * Compact case row for volunteer lists.
 * Expects: $n (need row), $showClaim (bool), $statusLabels, $urgencyLabels,
 *          $scenarioLabels, $needTypes
 */
$scenarioKey = $n['scenario'] ?? 'other';
$chip = Helper::scenarioChipColor($scenarioKey);
$icon = Helper::scenarioIcon($scenarioKey, 'w-5 h-5');
$title = Helper::scenarioLabel($scenarioKey) ?: 'เหตุการณ์';
$place = ($n['district'] ?? '') !== '' ? $n['district'] : ($n['area_name'] ?? '');
$loc = implode(' · ', array_values(array_unique(array_filter([$place, $n['province'] ?? '']))));
$urg = $n['urgency'] ?? 'normal';
?>
<div class="card p-3 flex items-center gap-3">
    <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 <?= $chip ?>"><?= $icon ?></div>
    <a href="<?= Helper::url('volunteer/cases/' . (int)$n['id']) ?>" class="flex-1 min-w-0">
        <p class="text-base font-bold text-slate-800 truncate"><?= Helper::e($title) ?></p>
        <p class="text-sm text-slate-500 truncate"><?= Helper::e($n['code']) ?><?= $loc !== '' ? ' · ' . Helper::e($loc) : '' ?> · <?= Helper::timeAgo($n['created_at']) ?></p>
        <div class="flex items-center gap-1.5 mt-1 flex-wrap">
            <?php if ($urg !== 'normal'): ?>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold <?= Helper::urgencyChipColor($urg) ?>"><?= Helper::e($urgencyLabels[$urg] ?? $urg) ?></span>
            <?php endif; ?>
            <?php if (!empty($n['assigned_name'])): ?>
                <span class="text-xs text-slate-400">👤 <?= Helper::e($n['assigned_name']) ?></span>
            <?php endif; ?>
        </div>
    </a>
    <div class="flex flex-col items-end gap-1.5 shrink-0">
        <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-semibold <?= Helper::statusColor($n['status']) ?>">
            <?= Helper::statusDot($n['status']) ?><?= Helper::e($statusLabels[$n['status']] ?? $n['status']) ?>
        </span>
        <?php if (!empty($showClaim)): ?>
            <button type="button" class="claim-btn text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl px-4 py-2" data-id="<?= (int)$n['id'] ?>">รับเคส</button>
        <?php endif; ?>
    </div>
</div>
