<?php
/** Admin: site branding settings — app name + logo (white-label). Expects $appName, $logoUrl, $csrf */
?>
<div class="max-w-xl space-y-5">
    <div>
        <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2.5">
            <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            ตั้งค่าระบบ
        </h1>
        <p class="text-slate-500 mt-1">ตั้งค่าระบบ — ชื่อแอป โลโก้ โหมดการทำงาน และการปรับแต่งรายการในฟอร์มแจ้งเหตุ</p>
    </div>

    <form id="settingsForm" enctype="multipart/form-data" class="bg-white rounded-xl border border-slate-200 p-5 space-y-5">
        <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">

        <!-- Operating mode -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">โหมดการทำงาน</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="flex items-start gap-3 rounded-xl border-2 p-4 cursor-pointer transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 <?= $appMode === 'disaster' ? 'border-brand-600 bg-brand-50' : 'border-slate-200 bg-white' ?>">
                    <input type="radio" name="app_mode" value="disaster" class="mt-1 text-brand-600" <?= $appMode === 'disaster' ? 'checked' : '' ?>>
                    <span>
                        <span class="block text-sm font-bold text-slate-800">ภัยพิบัติ / น้ำท่วม</span>
                        <span class="block text-xs text-slate-500 mt-0.5">ตัวเลือกแจ้งเหตุแบบเหตุภัยพิบัติ (น้ำท่วม, อพยพ, SOS)</span>
                    </span>
                </label>
                <label class="flex items-start gap-3 rounded-xl border-2 p-4 cursor-pointer transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 <?= $appMode === 'relief' ? 'border-brand-600 bg-brand-50' : 'border-slate-200 bg-white' ?>">
                    <input type="radio" name="app_mode" value="relief" class="mt-1 text-brand-600" <?= $appMode === 'relief' ? 'checked' : '' ?>>
                    <span>
                        <span class="block text-sm font-bold text-slate-800">งานบรรเทาทั่วไป (Relief)</span>
                        <span class="block text-xs text-slate-500 mt-0.5">ตัวเลือกแจ้งเหตุแบบงาน relief (สุขภาพ, ปรึกษา, ฝึกอาชีพ, เงินช่วยเหลือ)</span>
                    </span>
                </label>
            </div>
            <p class="text-xs text-slate-400 mt-1">เปลี่ยนโหมดแล้วตัวเลือกในฟอร์มแจ้งเหตุและป้ายกำกับทั้งหมดจะเปลี่ยนตาม — ข้อมูลเก่ายังแสดงผลได้ปกติ</p>
        </div>

        <!-- App name -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">ชื่อแอป <span class="text-red-600">*</span></label>
            <input type="text" name="app_name" id="appName" value="<?= Helper::e($appName) ?>" required maxlength="60" class="input-field" placeholder="เช่น ระบบช่วยเหลือน้ำท่วม">
            <p class="text-xs text-slate-400 mt-1">แสดงบน title แท็บเบราว์เซอร์และส่วนหัวของระบบ</p>
        </div>

        <!-- Logo -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">โลโก้</label>
            <div class="flex items-start gap-4">
                <div id="logoPreview" class="w-20 h-20 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                    <?php if ($logoUrl): ?>
                        <img src="<?= Helper::e($logoUrl) ?>" alt="โลโก้" class="w-full h-full object-contain">
                    <?php else: ?>
                        <i class="fa-solid fa-image text-2xl text-slate-300"></i>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <input type="file" name="logo" id="logoInput" accept="image/jpeg,image/png,image/webp,image/gif" class="input-field !py-2 text-sm">
                    <p class="text-xs text-slate-400 mt-1">JPG / PNG / WEBP / GIF ไม่เกิน 8MB — แนะนำรูปสี่เหลี่ยมจัตุรัส พื้นหลังโปร่งใส</p>
                    <?php if ($logoUrl): ?>
                        <label class="mt-2 inline-flex items-center gap-2 text-sm text-red-600 cursor-pointer">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-red-600 w-4 h-4">
                            ลบโลโก้ปัจจุบัน (กลับไปใช้ไอคอนเริ่มต้น)
                        </label>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Report-form list customization (per operating mode) -->
        <?php
        $mode = $appMode;
        $customMode = $uiCustomAll[$mode] ?? [];
        $renderColorPicker = function(string $group, string $key, ?string $current) use ($uiPalettes) {
            echo '<div class="flex items-center gap-1.5 flex-wrap">';
            echo '<label class="cursor-pointer" title="สีเริ่มต้น"><input type="radio" name="ui[' . $group . '][' . $key . '][color]" value="" class="peer sr-only"' . ($current === null || $current === '' ? ' checked' : '') . '><span class="block w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:ring-2 peer-checked:ring-slate-500 peer-checked:border-slate-500" style="background:linear-gradient(135deg,#fff 45%,#cbd5e1 45%)"></span></label>';
            foreach ($uiPalettes as $pname => $p) {
                $hex = $p[2] ?? '#94a3b8';
                echo '<label class="cursor-pointer" title="' . Helper::e($pname) . '"><input type="radio" name="ui[' . $group . '][' . $key . '][color]" value="' . $pname . '" class="peer sr-only"' . ($current === $pname ? ' checked' : '') . '><span class="block w-5 h-5 rounded-full border border-black/10 peer-checked:ring-2 peer-checked:ring-offset-1 peer-checked:ring-slate-600 transition" style="background:' . $hex . '"></span></label>';
            }
            echo '</div>';
        };
        ?>
        <div class="rounded-xl border border-slate-200 p-4 space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">ปรับแต่งรายการในฟอร์มแจ้งเหตุ</h2>
                    <p class="text-xs text-slate-500 mt-0.5">เปลี่ยนชื่อ / ไอคอน / สีของตัวเลือก "เกิดอะไรขึ้น?" และ "ต้องการบริการอะไร?" — ใช้ได้เฉพาะโหมด <strong><?= $mode === 'relief' ? 'งานบรรเทา' : 'ภัยพิบัติ' ?></strong> ปัจจุบัน (อีกโหมดตั้งค่าแยกกัน)</p>
                </div>
            </div>

            <!-- Scenario cards: เกิดอะไรขึ้น? -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wide">เกิดอะไรขึ้น? (<?= count($scenarioCards) ?> รายการ)</h3>
                    <button type="button" class="text-xs text-slate-400 hover:text-red-600" data-reset="scenario">คืนค่าเริ่มต้น</button>
                </div>
                <div class="space-y-2.5" id="uiScenarioRows">
                    <?php foreach ($scenarioCards as $sc): $o = $customMode['scenario'][$sc['key']] ?? []; ?>
                    <div class="rounded-lg border border-slate-200 p-3 space-y-2" data-row="scenario" data-key="<?= Helper::e($sc['key']) ?>">
                        <div class="flex items-center gap-2">
                            <span class="ui-icon-preview shrink-0 w-6 h-6 flex items-center justify-center text-slate-500"><?= Helper::scenarioIcon($sc['key'], 'w-5 h-5') ?></span>
                            <span class="text-xs text-slate-400 font-mono"><?= Helper::e($sc['key']) ?></span>
                            <span class="text-xs text-slate-500 truncate ml-auto">ปัจจุบัน: <?= Helper::e($sc['label']) ?></span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input type="text" name="ui[scenario][<?= Helper::e($sc['key']) ?>][label]" value="<?= Helper::e($o['label'] ?? '') ?>" maxlength="80" class="input-field !py-1.5 !text-sm" placeholder="ชื่อรายการ (เว้นว่าง = ค่าเริ่มต้น)">
                            <input type="text" name="ui[scenario][<?= Helper::e($sc['key']) ?>][sub]" value="<?= Helper::e($o['sub'] ?? '') ?>" maxlength="120" class="input-field !py-1.5 !text-sm" placeholder="คำอธิบายย่อย">
                        </div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <input type="text" name="ui[scenario][<?= Helper::e($sc['key']) ?>][icon]" value="<?= Helper::e($o['icon'] ?? '') ?>" class="input-field !py-1.5 !text-sm w-36" placeholder="ไอคอน fa-…" data-icon-input>
                            <?php $renderColorPicker('scenario', $sc['key'], $o['color'] ?? null); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Need types: ต้องการบริการอะไร? -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wide">ต้องการบริการอะไร? (<?= count($needTypeMap) ?> รายการ)</h3>
                    <button type="button" class="text-xs text-slate-400 hover:text-red-600" data-reset="need_type">คืนค่าเริ่มต้น</button>
                </div>
                <div class="space-y-2.5" id="uiNeedTypeRows">
                    <?php foreach ($needTypeMap as $tk => $tl): $o = $customMode['need_type'][$tk] ?? []; ?>
                    <div class="rounded-lg border border-slate-200 p-3 space-y-2" data-row="need_type" data-key="<?= Helper::e($tk) ?>">
                        <div class="flex items-center gap-2">
                            <span class="ui-icon-preview shrink-0 w-6 h-6 flex items-center justify-center text-slate-500"><?= Helper::needTypeIcon($tk, 'w-5 h-5') ?></span>
                            <span class="text-xs text-slate-400 font-mono"><?= Helper::e($tk) ?></span>
                            <span class="text-xs text-slate-500 truncate ml-auto">ปัจจุบัน: <?= Helper::e($tl) ?></span>
                        </div>
                        <input type="text" name="ui[need_type][<?= Helper::e($tk) ?>][label]" value="<?= Helper::e($o['label'] ?? '') ?>" maxlength="80" class="input-field !py-1.5 !text-sm" placeholder="ชื่อรายการ (เว้นว่าง = ค่าเริ่มต้น)">
                        <div class="flex items-center gap-3 flex-wrap">
                            <input type="text" name="ui[need_type][<?= Helper::e($tk) ?>][icon]" value="<?= Helper::e($o['icon'] ?? '') ?>" class="input-field !py-1.5 !text-sm w-36" placeholder="ไอคอน fa-…" data-icon-input>
                            <?php $renderColorPicker('need_type', $tk, $o['color'] ?? null); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <input type="hidden" name="ui_custom" id="uiCustomJson" value="<?= Helper::e(json_encode($uiCustomAll, JSON_UNESCAPED_UNICODE)) ?>">

        <div class="flex items-center justify-end gap-2 pt-1">
            <button type="button" id="settingsReset" class="px-4 py-2.5 text-sm text-slate-600 hover:text-slate-800">รีเซ็ต</button>
            <button type="submit" class="btn-primary">บันทึก</button>
        </div>
    </form>
</div>

<script>
(function() {
    const form = document.getElementById('settingsForm');
    const logoInput = document.getElementById('logoInput');
    const preview = document.getElementById('logoPreview');
    const defaultLogo = <?= json_encode($logoUrl) ?>;

    logoInput.addEventListener('change', () => {
        const f = logoInput.files[0];
        if (!f) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-contain">';
        };
        reader.readAsDataURL(f);
    });

    document.getElementById('settingsReset').addEventListener('click', () => {
        form.reset();
        if (defaultLogo) {
            preview.innerHTML = '<img src="' + defaultLogo + '" class="w-full h-full object-contain">';
        } else {
            preview.innerHTML = '<i class="fa-solid fa-image text-2xl text-slate-300"></i>';
        }
    });

    // --- Report-form list customization ---
    const MODE = <?= json_encode($mode) ?>;
    document.querySelectorAll('.ui-icon-preview').forEach(p => { p.dataset.def = p.innerHTML; });
    document.querySelectorAll('[data-icon-input]').forEach(inp => {
        inp.addEventListener('input', () => {
            const prev = inp.closest('[data-row]').querySelector('.ui-icon-preview');
            const v = inp.value.trim();
            prev.innerHTML = /^fa-[a-z0-9-]+$/.test(v)
                ? '<i class="fa-solid ' + v + ' w-5 h-5"></i>'
                : prev.dataset.def;
        });
    });
    document.querySelectorAll('[data-reset]').forEach(btn => {
        btn.addEventListener('click', () => {
            const group = btn.dataset.reset;
            document.querySelectorAll('[data-row="' + group + '"]').forEach(row => {
                row.querySelectorAll('input[type="text"]').forEach(i => { i.value = ''; });
                const def = row.querySelector('input[name$="[color]"][value=""]');
                if (def) def.checked = true;
                const prev = row.querySelector('.ui-icon-preview');
                if (prev) prev.innerHTML = prev.dataset.def;
            });
        });
    });
    form.addEventListener('submit', () => {
        let base = {};
        try { base = JSON.parse(document.getElementById('uiCustomJson').value || '{}') || {}; } catch (e) {}
        const cur = { scenario: {}, need_type: {} };
        document.querySelectorAll('[data-row]').forEach(row => {
            const g = row.dataset.row, k = row.dataset.key;
            const val = (suffix) => {
                const el = row.querySelector('input[name$="[' + suffix + ']"]');
                return el ? el.value.trim() : '';
            };
            const icon = val('icon');
            const e = {};
            if (val('label')) e.label = val('label');
            if (val('sub'))   e.sub   = val('sub');
            if (/^fa-[a-z0-9-]+$/.test(icon)) e.icon = icon;
            const colorEl = row.querySelector('input[name$="[color]"]:checked');
            if (colorEl && colorEl.value) e.color = colorEl.value;
            if (Object.keys(e).length) cur[g][k] = e;
        });
        base[MODE] = cur;
        document.getElementById('uiCustomJson').value = JSON.stringify(base);
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        const res = await fetch(window.BASE_PATH + '/admin/settings', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.ok) {
            Toast.show(json.message || 'บันทึกแล้ว', 'ok');
            setTimeout(() => location.reload(), 700);
        } else {
            Toast.show(json.message || 'ไม่สำเร็จ', 'err');
        }
    });
})();
</script>
