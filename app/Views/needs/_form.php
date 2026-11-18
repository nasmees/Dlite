<?php
/**
 * Report form partial — used inside the bottom-sheet drawer on the home map
 * and requires JS (progressive enhancement). No-JS users use /request page.
 * Expects: $areas, $needTypes, $csrf
 */
$scenarios = Helper::scenarioCards();
$reliefMode = Helper::appMode() === 'relief';
$ctaColorCls = $reliefMode
    ? 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800'
    : 'bg-red-600 hover:bg-red-700 active:bg-red-800';
?>
<form id="reportForm" class="flex flex-col h-full min-h-0" enctype="multipart/form-data"
      action="<?= Helper::url('request') ?>" method="post">
    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
    <input type="hidden" name="client_token" value="<?= Helper::e(bin2hex(random_bytes(16))) ?>">
    <input type="hidden" name="lat" id="rLat">
    <input type="hidden" name="lng" id="rLng">
    <input type="hidden" name="urgency" id="rUrgency" value="normal">
    <input type="hidden" name="scenario" id="rScenario">

    <div class="sheet-body px-4 py-4 space-y-4">
        <?php if (!empty($inDrawer)): ?>
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800"><?= Helper::e(Helper::reportCta()) ?></h2>
            <button type="button" id="drawerClose" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <?php endif; ?>

        <!-- Scenario cards: large thumb-friendly targets -->
        <div>
            <p class="flex items-center gap-1.5 text-base font-bold text-slate-800 mb-2">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                <?= $reliefMode ? 'ต้องการบริการอะไร?' : 'เกิดอะไรขึ้น?' ?> <span class="text-red-500">*</span>
            </p>
            <div class="space-y-2" id="scenarioList">
                <?php foreach ($scenarios as $s): ?>
                    <button type="button"
                            class="scenario-btn w-full min-h-[72px] flex items-center gap-4 rounded-2xl border-2 px-5 py-4 text-left transition
                                   <?= $s['sos'] ? 'border-red-200 bg-red-50' : 'border-slate-300 bg-white' ?>"
                            data-types="<?= implode(',', $s['types']) ?>"
                            data-urgency="<?= $s['urgency'] ?>"
                            data-scenario="<?= $s['key'] ?>"
                            data-sos="<?= $s['sos'] ? '1' : '0' ?>">
                        <span class="scenario-icon w-12 h-12 rounded-xl flex items-center justify-center shrink-0 <?= Helper::scenarioChipColor($s['key']) ?>">
                            <?= Helper::scenarioIcon($s['key'], 'w-6 h-6') ?>
                        </span>
                        <span class="flex-1">
                            <span class="block text-base font-bold text-slate-800"><?= Helper::e($s['label']) ?></span>
                            <span class="block text-base text-slate-500 mt-0.5"><?= Helper::e($s['sub']) ?></span>
                        </span>
                        <span class="scenario-check hidden w-7 h-7 rounded-full bg-brand-600 text-white items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Contact card -->
        <div class="rounded-2xl border-2 border-slate-300 bg-white p-5 shadow-sm mb-4">
            <p class="flex items-center gap-1.5 text-base font-bold text-slate-800 mb-4">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                ข้อมูลติดต่อ
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="min-w-0">
                <label class="flex items-center gap-1.5 text-base font-semibold text-slate-800 mb-2">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    ชื่อผู้แจ้ง <span class="text-red-600">*</span>
                </label>
                <input type="text" name="reporter_name" id="rf_name" required class="input-field !text-xl" placeholder="ชื่อ-นามสกุล / ชื่อเล่น" autocomplete="name">
            </div>
            <div class="min-w-0">
                <label class="flex items-center gap-1.5 text-base font-semibold text-slate-800 mb-2">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    เบอร์โทร <span class="text-slate-400 font-normal text-sm">(ไม่บังคับ)</span>
                </label>
                <input type="tel" name="reporter_phone" id="rf_phone" class="input-field !text-xl" placeholder="08x-xxx-xxxx" inputmode="tel" autocomplete="tel">
            </div>
            </div>
        </div>

        <!-- GPS status box -->
        <div id="gpsBox" class="rounded-2xl border-2 border-brand-300 bg-brand-50 p-4 mb-4 shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span id="gpsDot" class="w-3 h-3 rounded-full bg-slate-300 shrink-0"></span>
                <p id="gpsStatus" class="text-base font-semibold text-brand-800 flex-1">รอระบุตำแหน่ง — จะหาพิกัดอัตโนมัติ</p>
                <button type="button" id="btnGps" class="text-base font-semibold text-brand-700 bg-white border border-brand-300 rounded-lg px-4 py-2.5 shrink-0 flex items-center gap-1 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    ระบุเอง
                </button>
            </div>
            <div id="mapPicker" class="hidden rounded-xl overflow-hidden border border-brand-300 h-48 mt-3"></div>
        </div>

        <!-- Incident details card -->
        <div class="rounded-2xl border-2 border-slate-300 bg-white p-5 space-y-4 shadow-sm mb-4">
            <p class="flex items-center gap-1.5 text-base font-bold text-slate-800">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <?= $reliefMode ? 'รายละเอียดคำขอ' : 'รายละเอียดเหตุการณ์' ?>
            </p>

            <!-- Landmark (single simple field per spec) -->
            <div>
                <label class="flex items-center gap-1.5 text-base font-semibold text-slate-800 mb-2">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    จุดสังเกตใกล้เคียง <span class="text-slate-400 font-normal text-sm">(ไม่บังคับ)</span>
                </label>
                <input type="text" name="address" id="rf_landmark" class="input-field !text-xl" placeholder="เช่น หน้าวัด, ซอยข้างร้านขายยา, บ้านหลังคาสีฟ้า">
            </div>

            <!-- Photo with client-side compression -->
            <div>
                <label class="flex items-center gap-1.5 text-base font-semibold text-slate-800 mb-2">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    รูปภาพ <span class="text-slate-400 font-normal">(ไม่บังคับ — บีบอัดอัตโนมัติ)</span>
                </label>
                <input type="file" name="photo" id="rf_photo" accept="image/*" capture="environment" class="hidden">
                <div id="photoDrop" class="border-2 border-dashed border-slate-300 rounded-2xl p-4 text-center cursor-pointer active:bg-white bg-white">
                    <div id="photoPreviewWrap" class="hidden mb-2">
                        <img id="photoPreview" class="max-h-40 mx-auto rounded-xl object-cover" alt="">
                        <p id="photoSize" class="text-sm text-slate-500 mt-1"></p>
                    </div>
                    <svg class="w-8 h-8 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p id="photoLabel" class="text-base text-slate-500 mt-1">แตะเพื่อถ่ายรูป / เลือกรูป</p>
                </div>
            </div>
        </div>

        <!-- Optional details -->
        <style>details[open] .details-chevron { transform: rotate(180deg); }</style>
        <details class="rounded-2xl border-2 border-amber-300 bg-amber-50 overflow-hidden">
            <summary class="px-4 py-4 text-base font-bold text-amber-800 cursor-pointer select-none flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="flex-1">เพิ่มเติม (ไม่บังคับ)</span>
                <span class="text-xs font-normal text-amber-600 bg-amber-100 rounded-full px-2.5 py-0.5">แตะเพื่อกรอก</span>
                <svg class="details-chevron w-5 h-5 text-amber-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 space-y-3">
                <div>
                    <label class="flex items-center gap-1.5 text-base font-semibold text-slate-800 mb-2">
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        จำนวนผู้ได้รับผลกระทบ
                    </label>
                    <input type="number" name="people_count" id="rf_people" min="1" value="1" class="input-field" inputmode="numeric">
                </div>
                <div>
                    <label class="flex items-center gap-1.5 text-base font-semibold text-slate-800 mb-2">
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        พื้นที่
                    </label>
                    <select name="area_id" id="rf_area" class="input-field">
                        <option value="">-- เลือกพื้นที่ --</option>
                        <?php foreach ($areas as $a): ?>
                            <option value="<?= (int)$a['id'] ?>"><?= Helper::e($a['name']) ?> (<?= Helper::e($a['province']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="flex items-center gap-1.5 text-base font-semibold text-slate-800 mb-2">
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        รายละเอียด
                    </label>
                    <textarea name="description" id="rf_desc" rows="3" class="input-field" placeholder="อธิบายสถานการณ์เพิ่มเติม"></textarea>
                </div>
                <div>
                    <label class="flex items-center gap-1.5 text-base font-medium text-slate-700 mb-2">
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        ความต้องการเฉพาะ
                    </label>
                    <div class="grid grid-cols-1 gap-2.5">
                        <?php foreach ($needTypes as $key => $label): ?>
                            <label class="flex items-center gap-3 bg-white border-2 border-slate-300 rounded-xl px-4 py-3.5 cursor-pointer has-[:checked]:bg-brand-50 has-[:checked]:border-brand-600 transition">
                                <?= Helper::needTypeIcon($key, 'w-6 h-6 shrink-0 text-brand-600') ?>
                                <span class="flex-1 text-base font-medium text-slate-800"><?= Helper::e($label) ?></span>
                                <input type="checkbox" name="need_types[]" value="<?= Helper::e($key) ?>" class="rounded border-slate-300 text-brand-600 w-5 h-5 shrink-0">
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">เลือกเองได้ถ้าไม่ตรงกับปุ่มด้านบน (เลือกได้หลายข้อ)</p>
                </div>
            </div>
        </details>
    </div>

    <!-- Sticky CTA: Deep Trust Blue, turns red on SOS -->
    <div class="sheet-foot border-t border-slate-300 bg-white px-4 pt-3 pb-3 <?= empty($inDrawer) ? 'sticky bottom-[4.5rem] z-10' : '' ?>">
        <button type="submit" id="rf_submit" class="w-full min-h-[56px] rounded-2xl <?= $ctaColorCls ?> text-white text-lg font-bold transition shadow-lg">
            <?= $reliefMode ? 'ส่งคำขอเลย' : 'ส่งแจ้งเหตุทันที' ?>
        </button>
        <p class="text-sm text-center text-slate-500 mt-1.5">ข้อมูลถูกส่งถึงทีมประสานงานโดยตรง</p>
    </div>
</form>

<script>
(function() {
    const form = document.getElementById('reportForm');
    const latInput = document.getElementById('rLat');
    const lngInput = document.getElementById('rLng');
    const urgencyInput = document.getElementById('rUrgency');
    const scenarioInput = document.getElementById('rScenario');
    const submitBtn = document.getElementById('rf_submit');
    const gpsStatus = document.getElementById('gpsStatus');
    const gpsDot = document.getElementById('gpsDot');
    const mapPicker = document.getElementById('mapPicker');
    let map = null, marker = null, selectedScenario = null;

    // ---------- Scenario cards ----------
    document.querySelectorAll('.scenario-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.scenario-btn').forEach(b => {
                b.classList.remove('border-brand-600', 'bg-brand-50', 'border-red-600', 'bg-red-50', 'border-red-200');
                b.classList.add('border-slate-300', 'bg-white');
                const chk = b.querySelector('.scenario-check');
                chk.classList.add('hidden'); chk.classList.remove('flex');
            });
            const isSos = btn.dataset.sos === '1';
            btn.classList.remove('border-slate-300', 'bg-white');
            btn.classList.add(isSos ? 'border-red-600' : 'border-brand-600', isSos ? 'bg-red-50' : 'bg-brand-50');
            const chk = btn.querySelector('.scenario-check');
            chk.classList.remove('hidden'); chk.classList.add('flex');
            chk.classList.toggle('bg-red-600', isSos);
            chk.classList.toggle('bg-brand-600', !isSos);

            // Apply types + urgency
            const types = btn.dataset.types.split(',');
            form.querySelectorAll('input[name="need_types[]"]').forEach(cb => {
                cb.checked = types.includes(cb.value);
            });
            urgencyInput.value = btn.dataset.urgency;
            scenarioInput.value = btn.dataset.scenario;
            selectedScenario = btn.dataset.types;
            updateCtaColor(isSos || btn.dataset.urgency === 'critical');
            saveDraft();
        });
    });

    function updateCtaColor(urgent) {
        submitBtn.className = 'w-full min-h-[56px] rounded-2xl text-white text-lg font-bold transition shadow-lg ' +
            '<?= $ctaColorCls ?>';
    }

    // ---------- Draft ----------
    function collectDraft() {
        return {
            reporter_name: document.getElementById('rf_name').value,
            reporter_phone: document.getElementById('rf_phone').value,
            address: document.getElementById('rf_landmark').value,
            people_count: document.getElementById('rf_people').value,
            description: document.getElementById('rf_desc').value,
            area_id: document.getElementById('rf_area').value,
            lat: latInput.value, lng: lngInput.value,
            urgency: urgencyInput.value,
            scenario: scenarioInput.value,
            need_types: Array.from(form.querySelectorAll('input[name="need_types[]"]:checked')).map(c => c.value)
        };
    }
    function saveDraft() { OfflineHelper.saveDraft('request', collectDraft()); }
    form.addEventListener('input', saveDraft);
    form.addEventListener('change', saveDraft);

    const draft = OfflineHelper.loadDraft('request');
    if (draft) {
        const fieldMap = { reporter_name: 'rf_name', reporter_phone: 'rf_phone', address: 'rf_landmark', people_count: 'rf_people', description: 'rf_desc', area_id: 'rf_area' };
        Object.entries(fieldMap).forEach(([k, id]) => {
            const el = document.getElementById(id);
            if (el && draft[k]) el.value = draft[k];
        });
        if (draft.lat) latInput.value = draft.lat;
        if (draft.lng) lngInput.value = draft.lng;
        if (draft.urgency) { urgencyInput.value = draft.urgency; updateCtaColor(draft.urgency === 'critical'); }
        if (draft.need_types) {
            draft.need_types.forEach(t => {
                const cb = form.querySelector(`input[name="need_types[]"][value="${t}"]`);
                if (cb) cb.checked = true;
            });
        }
        if (draft.scenario) {
            const btn = form.querySelector(`.scenario-btn[data-scenario="${draft.scenario}"]`);
            if (btn) btn.click();
        }
        if (draft.lat && draft.lng) {
            gpsStatus.textContent = "✓ พบตำแหน่งของคุณแล้ว";
            gpsDot.className = 'w-3 h-3 rounded-full bg-emerald-500 shrink-0';
        }
    }

    // ---------- GPS ----------
    function setLocation(lat, lng, acc) {
        latInput.value = lat; lngInput.value = lng;
        gpsStatus.textContent = "✓ พบตำแหน่งของคุณแล้ว";
        gpsDot.className = 'w-3 h-3 rounded-full bg-emerald-500 shrink-0';
        if (map && marker) { marker.setLatLng([lat, lng]); map.setView([lat, lng], 16); }
        saveDraft();
    }
    function tryGps(highAccuracy) {
        if (!navigator.geolocation) {
            gpsStatus.textContent = 'อุปกรณ์ไม่รองรับ GPS — กรุณาปักหมุดบนแผนที่';
            gpsDot.className = 'w-3 h-3 rounded-full bg-amber-500 shrink-0';
            return;
        }
        gpsStatus.textContent = 'กำลังหาตำแหน่ง...';
        navigator.geolocation.getCurrentPosition(
            pos => setLocation(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy),
            () => {
                gpsStatus.textContent = 'ไม่พบตำแหน่ง — แตะ "ระบุเอง" เพื่อปักหมุด';
                gpsDot.className = 'w-3 h-3 rounded-full bg-amber-500 shrink-0';
            },
            { enableHighAccuracy: highAccuracy, timeout: 12000, maximumAge: 60000 }
        );
    }
    // GPS fires when the drawer first opens (avoids prompting on page load)
    let gpsTried = false;
    function tryGpsOnce() {
        if (gpsTried) return;
        gpsTried = true;
        if (latInput.value && lngInput.value) return; // draft already has location
        tryGps(false);
    }
    window.dscTryGps = tryGpsOnce;
    // Standalone page (no drawer) → try GPS shortly after load
    if (!document.getElementById('reportDrawer')) {
        setTimeout(tryGpsOnce, 600);
    }

    document.getElementById('btnGps').addEventListener('click', () => {
        mapPicker.classList.toggle('hidden');
        if (!mapPicker.classList.contains('hidden') && !map) {
            const la = latInput.value ? parseFloat(latInput.value) : 6.87;
            const ln = lngInput.value ? parseFloat(lngInput.value) : 101.25;
            map = L.map('mapPicker').setView([la, ln], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM', maxZoom: 19 }).addTo(map);
            marker = L.marker([la, ln], { draggable: true }).addTo(map);
            marker.on('dragend', () => { const p = marker.getLatLng(); setLocation(p.lat, p.lng); });
            map.on('click', e => { marker.setLatLng(e.latlng); setLocation(e.latlng.lat, e.latlng.lng); });
            setTimeout(() => map.invalidateSize(), 200);
        } else if (map) {
            setTimeout(() => map.invalidateSize(), 200);
        }
    });

    // ---------- Client-side image compression (<300KB target) ----------
    const photoInput = document.getElementById('rf_photo');
    const photoDrop = document.getElementById('photoDrop');
    photoDrop.addEventListener('click', () => photoInput.click());
    photoInput.addEventListener('change', async () => {
        const file = photoInput.files[0];
        if (!file) return;
        const preview = document.getElementById('photoPreview');
        const wrap = document.getElementById('photoPreviewWrap');
        const sizeEl = document.getElementById('photoSize');
        preview.src = URL.createObjectURL(file);
        wrap.classList.remove('hidden');
        sizeEl.textContent = 'กำลังบีบอัดรูป...';

        try {
            const compressed = await compressImage(file, 1600, 300 * 1024);
            // Replace input with compressed blob
            const dt = new DataTransfer();
            dt.items.add(compressed);
            photoInput.files = dt.files;
            sizeEl.textContent = `รูปพร้อมส่ง (${(compressed.size / 1024).toFixed(0)} KB)`;
        } catch (e) {
            sizeEl.textContent = `รูปเดิม ${(file.size / 1024).toFixed(0)} KB (บีบอัดไม่ได้)`;
        }
    });

    function compressImage(file, maxDim, maxBytes) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => {
                URL.revokeObjectURL(img.src);
                let { width: w, height: h } = img;
                const scale = Math.min(1, maxDim / Math.max(w, h));
                w = Math.round(w * scale); h = Math.round(h * scale);
                const canvas = document.createElement('canvas');
                canvas.width = w; canvas.height = h;
                canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                const tryQuality = (q, attempt) => {
                    canvas.toBlob(blob => {
                        if (!blob) return reject(new Error('toBlob failed'));
                        if (blob.size <= maxBytes || attempt >= 5 || q <= 0.5) {
                            resolve(new File([blob], 'photo.jpg', { type: 'image/jpeg' }));
                        } else {
                            tryQuality(Math.max(0.5, q - 0.12), attempt + 1);
                        }
                    }, 'image/jpeg', q);
                };
                tryQuality(0.82, 0);
            };
            img.onerror = reject;
            img.src = URL.createObjectURL(file);
        });
    }

    // ---------- Submit ----------
    function buildPayload(formData) {
        const payload = {};
        formData.forEach((v, k) => {
            if (k === 'photo' || k === 'csrf_token') return;
            if (k.endsWith('[]')) {
                const key = k.slice(0, -2);
                if (!payload[key]) payload[key] = [];
                payload[key].push(v);
            } else {
                payload[k] = v;
            }
        });
        return payload;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!form.querySelector('input[name="need_types[]"]:checked')) {
            Toast.show('กรุณาเลือกประเภทเหตุการณ์ก่อน', 'err');
            return;
        }
        submitBtn.disabled = true;
        SubmitOverlay.show('loading');

        const formData = new FormData(form);

        const warnPhotoLost = () => {
            const pi = form.querySelector('input[name="photo"]');
            if (pi && pi.files && pi.files.length) {
                Toast.show('รูปภาพไม่สามารถส่งแบบออฟไลน์ได้ — เคสจะถูกส่งเมื่อกลับมาออนไลน์ (ไม่มีรูป)', 'err');
            }
        };

        if (!navigator.onLine) {
            warnPhotoLost();
            OfflineHelper.queue({ url: window.BASE_PATH + '/request', payload: buildPayload(formData), csrf: formData.get('csrf_token') });
            OfflineHelper.clearDraft('request');
            SubmitOverlay.show('success');
            setTimeout(() => { window.location.href = window.BASE_PATH + '/'; }, 1800);
            return;
        }

        try {
            const res = await fetch(window.BASE_PATH + '/request', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const json = await res.json();
            if (json.ok) {
                OfflineHelper.clearDraft('request');
                if (window.saveMyReport) {
                    window.saveMyReport({ id: json.id, code: json.code, lat: latInput.value, lng: lngInput.value, status: 'pending' });
                }
                SubmitOverlay.show('success');
                setTimeout(() => { window.location.href = window.BASE_PATH + '/'; }, 1800);
            } else {
                SubmitOverlay.hide();
                Toast.show(json.message || 'เกิดข้อผิดพลาด', 'err');
                submitBtn.disabled = false;
            }
        } catch (err) {
            warnPhotoLost();
            OfflineHelper.queue({ url: window.BASE_PATH + '/request', payload: buildPayload(formData), csrf: formData.get('csrf_token') });
            OfflineHelper.clearDraft('request');
            SubmitOverlay.show('success');
            setTimeout(() => { window.location.href = window.BASE_PATH + '/'; }, 1800);
        }
    });
})();
</script>
