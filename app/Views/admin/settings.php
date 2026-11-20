<?php
/** Admin: system settings — branding, operating mode, report-form list customization. */
$iconChoices = [
    'fa-water','fa-house-flood-water','fa-person-drowning','fa-cloud-rain','fa-umbrella','fa-wind','fa-snowflake',
    'fa-bowl-food','fa-utensils','fa-bottle-water','fa-box','fa-box-open','fa-gift','fa-people-group','fa-person',
    'fa-child','fa-baby','fa-wheelchair','fa-kit-medical','fa-stethoscope','fa-user-doctor','fa-syringe','fa-pills',
    'fa-capsules','fa-bandage','fa-bed-pulse','fa-thermometer','fa-mask-face','fa-hand-holding-medical','fa-hand-holding-heart',
    'fa-truck-medical','fa-briefcase','fa-money-bill-wave','fa-coins','fa-hand-holding-dollar','fa-comments','fa-comment-dots',
    'fa-phone','fa-mobile-screen','fa-graduation-cap','fa-book','fa-person-chalkboard','fa-shirt','fa-shoe-prints',
    'fa-house','fa-house-chimney','fa-tent','fa-map-pin','fa-location-dot','fa-truck','fa-car','fa-bus','fa-helicopter',
    'fa-dog','fa-cat','fa-paw','fa-cow','fa-seedling','fa-leaf','fa-tree','fa-sun','fa-fire','fa-bolt',
    'fa-triangle-exclamation','fa-circle-exclamation','fa-bell','fa-flag','fa-bullhorn','fa-star','fa-heart',
    'fa-handshake','fa-hands-holding-child','fa-school','fa-mosque','fa-church','fa-toolbox','fa-wrench','fa-hammer',
    'fa-broom','fa-trash','fa-soap','fa-ellipsis','fa-question','fa-rotate-left',
];
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
        $baseUrgency = [];
        foreach (Helper::scenarioCardBase($mode) as $bc) { $baseUrgency[$bc['key']] = $bc['urgency']; }
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
                    <p class="text-xs text-slate-500 mt-0.5">เปลี่ยนชื่อ / ไอคอน / สีของตัวเลือก "เกิดอะไรขึ้น?" และ "ต้องการบริการอะไร?" — เลือกโหมดด้านบนเพื่อสลับรายการ (แต่ละโหมดตั้งค่าแยกกัน)</p>
                    <?php if ($appMode !== $savedMode): ?>
                        <p class="text-xs text-amber-600 mt-1 font-medium">กำลังดูตัวอย่างโหมด <?= $appMode === 'relief' ? 'งานบรรเทา' : 'ภัยพิบัติ' ?> — ยังไม่บันทึก กด "บันทึก" เพื่อใช้โหมดนี้</p>
                    <?php endif; ?>
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
                            <span class="ui-icon-preview shrink-0 w-6 h-6 flex items-center justify-center text-slate-500"><?= Helper::scenarioIcon($sc['key'], 'w-5 h-5', $mode) ?></span>
                            <span class="text-xs text-slate-400 font-mono"><?= Helper::e($sc['key']) ?></span>
                            <span class="text-xs text-slate-500 truncate ml-auto">ปัจจุบัน: <?= Helper::e($sc['label']) ?></span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input type="text" name="ui[scenario][<?= Helper::e($sc['key']) ?>][label]" value="<?= Helper::e($o['label'] ?? '') ?>" maxlength="80" class="input-field !py-1.5 !text-sm" placeholder="ชื่อรายการ (เว้นว่าง = ค่าเริ่มต้น)">
                            <input type="text" name="ui[scenario][<?= Helper::e($sc['key']) ?>][sub]" value="<?= Helper::e($o['sub'] ?? '') ?>" maxlength="120" class="input-field !py-1.5 !text-sm" placeholder="คำอธิบายย่อย">
                        </div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="icon-picker inline-flex items-center">
                                <input type="hidden" name="ui[scenario][<?= Helper::e($sc['key']) ?>][icon]" value="<?= Helper::e($o['icon'] ?? '') ?>" class="picker-value">
                                <button type="button" class="picker-btn inline-flex items-center gap-1.5 text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition">
                                    <i class="fa-solid <?= Helper::e($o['icon'] ?? 'fa-icons') ?> picker-btn-icon"></i>
                                    <span><?= $o['icon'] ?? 'เลือกไอคอน' ?></span>
                                </button>
                            </span>
                            <?php $renderColorPicker('scenario', $sc['key'], $o['color'] ?? null); ?>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <span class="shrink-0">ความเร่งด่วนเริ่มต้น:</span>
                            <select name="ui[scenario][<?= Helper::e($sc['key']) ?>][urgency]" class="input-field !py-1 !px-2 !text-xs w-auto">
                                <option value="">ใช้ค่าเริ่มต้น (<?= Helper::e(Helper::urgencyLabels()[$baseUrgency[$sc['key']] ?? 'normal'] ?? 'ปกติ') ?>)</option>
                                <?php foreach (Helper::urgencyLabels() as $uk => $ul): ?>
                                    <option value="<?= $uk ?>" <?= ($o['urgency'] ?? '') === $uk ? 'selected' : '' ?>><?= Helper::e($ul) ?></option>
                                <?php endforeach; ?>
                            </select>
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
                            <span class="ui-icon-preview shrink-0 w-6 h-6 flex items-center justify-center text-slate-500"><?= Helper::needTypeIcon($tk, 'w-5 h-5', $mode) ?></span>
                            <span class="text-xs text-slate-400 font-mono"><?= Helper::e($tk) ?></span>
                            <span class="text-xs text-slate-500 truncate ml-auto">ปัจจุบัน: <?= Helper::e($tl) ?></span>
                        </div>
                        <input type="text" name="ui[need_type][<?= Helper::e($tk) ?>][label]" value="<?= Helper::e($o['label'] ?? '') ?>" maxlength="80" class="input-field !py-1.5 !text-sm" placeholder="ชื่อรายการ (เว้นว่าง = ค่าเริ่มต้น)">
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="icon-picker inline-flex items-center">
                                <input type="hidden" name="ui[need_type][<?= Helper::e($tk) ?>][icon]" value="<?= Helper::e($o['icon'] ?? '') ?>" class="picker-value">
                                <button type="button" class="picker-btn inline-flex items-center gap-1.5 text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition">
                                    <i class="fa-solid <?= Helper::e($o['icon'] ?? 'fa-icons') ?> picker-btn-icon"></i>
                                    <span><?= $o['icon'] ?? 'เลือกไอคอน' ?></span>
                                </button>
                            </span>
                            <?php $renderColorPicker('need_type', $tk, $o['color'] ?? null); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- Shared icon picker panel -->
        <div id="iconPicker" class="hidden fixed z-[6000] w-72 bg-white border border-slate-200 rounded-xl shadow-2xl p-2">
            <input id="iconPickerSearch" type="text" placeholder="ค้นหาไอคอน เช่น water, home, food…" class="input-field !py-1.5 !text-sm mb-2 w-full" autocomplete="off">
            <div id="iconPickerGrid" class="grid grid-cols-6 gap-1 max-h-56 overflow-y-auto">
                <button type="button" class="picker-opt w-9 h-9 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400" data-icon="" title="ใช้ไอคอนเริ่มต้น"><i class="fa-solid fa-rotate-left"></i></button>
                <?php foreach ($iconChoices as $ic): ?>
                <button type="button" class="picker-opt w-9 h-9 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-600" data-icon="<?= $ic ?>" title="<?= $ic ?>"><i class="fa-solid <?= $ic ?>"></i></button>
                <?php endforeach; ?>
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
    const SETTINGS_URL = <?= json_encode(Helper::url('admin/settings')) ?>;

    // Switching operating mode reloads the page so the lists below show
    // that mode's items (?mode= preview — nothing is saved until submit).
    let uiDirty = false;
    form.addEventListener('input', (e) => {
        if (e.target.name === 'app_name' || e.target.name === 'logo' || e.target.closest('[data-row]')) uiDirty = true;
    });
    let currentMode = MODE;
    document.querySelectorAll('input[name="app_mode"]').forEach(r => {
        r.addEventListener('change', () => {
            if (!r.checked) return;
            if (uiDirty && !confirm('เปลี่ยนโหมดจะรีเฟรชหน้าและล้างค่าที่ยังไม่บันทึก ดำเนินการต่อ?')) {
                document.querySelector('input[name="app_mode"][value="' + currentMode + '"]').checked = true;
                return;
            }
            currentMode = r.value;
            location.href = SETTINGS_URL + '?mode=' + r.value;
        });
    });

    document.querySelectorAll('.ui-icon-preview').forEach(p => { p.dataset.def = p.innerHTML; });

    // --- Shared icon picker ---
    const picker = document.getElementById('iconPicker');
    const pickerSearch = document.getElementById('iconPickerSearch');
    const pickerGrid = document.getElementById('iconPickerGrid');
    let pickerInput = null, pickerBtn = null;

    function closePicker() {
        picker.classList.add('hidden');
        pickerInput = pickerBtn = null;
    }
    document.querySelectorAll('.picker-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (pickerBtn === btn) { closePicker(); return; }
            pickerBtn = btn;
            pickerInput = btn.closest('.icon-picker').querySelector('.picker-value');
            const rect = btn.getBoundingClientRect();
            picker.classList.remove('hidden');
            const pw = picker.offsetWidth, ph = picker.offsetHeight;
            let top = rect.bottom + 6;
            if (top + ph > window.innerHeight - 8) top = Math.max(8, rect.top - ph - 6);
            let left = Math.min(Math.max(8, rect.left), window.innerWidth - pw - 8);
            picker.style.top = top + 'px';
            picker.style.left = left + 'px';
            pickerSearch.value = '';
            pickerGrid.querySelectorAll('.picker-opt').forEach(o => o.classList.remove('hidden'));
            pickerSearch.focus();
        });
    });
    pickerSearch.addEventListener('input', () => {
        const q = pickerSearch.value.trim().toLowerCase();
        pickerGrid.querySelectorAll('.picker-opt').forEach(o => {
            o.classList.toggle('hidden', q !== '' && !(o.dataset.icon + ' ' + o.title).toLowerCase().includes(q));
        });
    });
    pickerGrid.addEventListener('click', (e) => {
        const opt = e.target.closest('.picker-opt');
        if (!opt || !pickerInput) return;
        const icon = opt.dataset.icon || '';
        pickerInput.value = icon;
        pickerBtn.querySelector('.picker-btn-icon').className = 'fa-solid ' + (icon || 'fa-icons') + ' picker-btn-icon';
        pickerBtn.querySelector('span').textContent = icon || 'เลือกไอคอน';
        const row = pickerBtn.closest('[data-row]');
        const prev = row && row.querySelector('.ui-icon-preview');
        if (prev) prev.innerHTML = icon ? '<i class="fa-solid ' + icon + ' w-5 h-5"></i>' : prev.dataset.def;
        uiDirty = true;
        closePicker();
    });
    document.addEventListener('click', (e) => {
        if (!picker.classList.contains('hidden') && !picker.contains(e.target) && !e.target.closest('.picker-btn')) closePicker();
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePicker(); });
    document.querySelectorAll('[data-reset]').forEach(btn => {
        btn.addEventListener('click', () => {
            const group = btn.dataset.reset;
            document.querySelectorAll('[data-row="' + group + '"]').forEach(row => {
                row.querySelectorAll('input[type="text"]').forEach(i => { i.value = ''; });
                row.querySelectorAll('select').forEach(s => { s.selectedIndex = 0; });
                const pv = row.querySelector('.picker-value');
                if (pv) {
                    pv.value = '';
                    const btn = row.querySelector('.picker-btn');
                    if (btn) {
                        btn.querySelector('.picker-btn-icon').className = 'fa-solid fa-icons picker-btn-icon';
                        btn.querySelector('span').textContent = 'เลือกไอคอน';
                    }
                }
                const def = row.querySelector('input[name$="[color]"][value=""]');
                if (def) def.checked = true;
                const prev = row.querySelector('.ui-icon-preview');
                if (prev) prev.innerHTML = prev.dataset.def;
                uiDirty = true;
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
                const el = row.querySelector('[name$="[' + suffix + ']"]');
                return el ? el.value.trim() : '';
            };
            const icon = val('icon');
            const e = {};
            if (val('label')) e.label = val('label');
            if (val('sub'))   e.sub   = val('sub');
            if (/^fa-[a-z0-9-]+$/.test(icon)) e.icon = icon;
            const colorEl = row.querySelector('input[name$="[color]"]:checked');
            if (colorEl && colorEl.value) e.color = colorEl.value;
            if (val('urgency')) e.urgency = val('urgency');
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
