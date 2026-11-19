<?php
/**
 * Open-pool case card — bigger than _case_row so volunteers can decide
 * whether to claim without opening the detail page.
 * Expects: $n (need row with need_types decoded), $statusLabels, $urgencyLabels
 */
$scenarioKey = $n['scenario'] ?? 'other';
$chip = Helper::scenarioChipColor($scenarioKey);
$icon = Helper::scenarioIcon($scenarioKey, 'w-7 h-7');
$title = Helper::scenarioLabel($scenarioKey) ?: 'เหตุการณ์';
$place = ($n['district'] ?? '') !== '' ? $n['district'] : ($n['area_name'] ?? '');
$loc = implode(' · ', array_values(array_unique(array_filter([$place, $n['province'] ?? '']))));
$urg = $n['urgency'] ?? 'normal';
$isCrit = $urg === 'critical';
$types = array_values(array_filter((array)($n['need_types'] ?? [])));
$people = (int)($n['people_count'] ?? 0);
$desc = trim((string)($n['description'] ?? ''));
?>
<?php
$hasLoc = !empty($n['lat']) && !empty($n['lng']);
?>
<div class="card overflow-hidden pool-card <?= $isCrit ? 'border-red-300' : '' ?>"
     <?php if ($hasLoc): ?>data-lat="<?= (float)$n['lat'] ?>" data-lng="<?= (float)$n['lng'] ?>"<?php endif; ?>>
    <?php if ($isCrit): ?><div class="h-1.5 bg-red-500"></div><?php endif; ?>

    <a href="<?= Helper::url('volunteer/cases/' . (int)$n['id']) ?>" class="block p-3.5 pb-2">
        <div class="flex items-start gap-3">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 <?= $chip ?>"><?= $icon ?></div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-lg font-bold text-slate-800 leading-tight"><?= Helper::e($title) ?></p>
                    <?php if ($urg !== 'normal'): ?>
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold <?= Helper::urgencyChipColor($urg) ?> <?= $isCrit ? 'animate-pulse' : '' ?>">
                            <?= Helper::e($urgencyLabels[$urg] ?? $urg) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-slate-500 mt-1 flex items-center gap-x-2 gap-y-0.5 flex-wrap">
                    <?php if ($loc !== ''): ?><span class="inline-flex items-center gap-1">📍 <?= Helper::e($loc) ?></span><?php endif; ?>
                    <?php if ($people > 0): ?><span class="inline-flex items-center gap-1">👥 <?= $people ?> คน</span><?php endif; ?>
                    <span class="near-dist hidden text-xs font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded"></span>
                    <span class="text-slate-400"><?= Helper::e($n['code']) ?> · <?= Helper::timeAgo($n['created_at']) ?></span>
                </p>
            </div>
        </div>

        <?php if ($types): ?>
            <div class="flex items-center gap-1.5 mt-2.5 flex-wrap">
                <?php foreach ($types as $t): ?>
                    <span class="inline-flex items-center gap-1 text-sm px-2.5 py-1 rounded-full font-semibold <?= Helper::needTypeChipColor($t) ?>">
                        <?= Helper::needTypeIcon($t) ?><?= Helper::e(Helper::needTypeLabel($t)) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($desc !== ''): ?>
            <p class="text-sm text-slate-600 mt-2 leading-relaxed line-clamp-2"><?= Helper::e($desc) ?></p>
        <?php endif; ?>
    </a>

    <div class="px-3.5 pb-3.5 pt-1 flex gap-2">
        <a href="<?= Helper::url('volunteer/cases/' . (int)$n['id']) ?>"
           class="flex-1 text-sm font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 rounded-xl py-3 text-center transition">
            <i class="fa-solid fa-eye mr-1"></i> ดูรายละเอียด
        </a>
        <button type="button" class="claim-btn flex-[1.4] text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-xl py-3 transition" data-id="<?= (int)$n['id'] ?>">
            รับเคสนี้
        </button>
    </div>
</div>
