<?php
/**
 * Volunteer "open case" form — staff filing on behalf of a reporter.
 * Auto-assigns the case to the volunteer who submits it.
 * Expects: $areas, $needTypes, $urgencyLabels, $scenarioLabels, $csrf
 */
?>
<form method="post" action="<?= Helper::url('volunteer/new') ?>" enctype="multipart/form-data" class="space-y-4" id="caseForm">
    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
    <input type="hidden" name="lat" id="vLat">
    <input type="hidden" name="lng" id="vLng">

    <!-- Scenario -->
    <div class="card p-4">
        <p class="text-base font-bold text-slate-800 mb-2 flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            ประเภทเหตุการณ์
        </p>
        <select name="scenario" class="input-field">
            <option value="">— เลือก —</option>
            <?php foreach ($scenarioLabels as $k => $label): ?>
                <option value="<?= Helper::e($k) ?>"><?= Helper::e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Reporter -->
    <div class="card p-4 space-y-3">
        <p class="text-base font-bold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            ผู้แจ้ง / ผู้ประสบภัย
        </p>
        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-1">ชื่อ-สกุล <span class="text-red-500">*</span></label>
            <input type="text" name="reporter_name" required class="input-field" placeholder="ชื่อผู้แจ้ง">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-1">เบอร์โทร</label>
            <input type="tel" name="reporter_phone" inputmode="tel" class="input-field" placeholder="08x-xxx-xxxx">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-1">พื้นที่</label>
            <select name="area_id" class="input-field">
                <option value="">— เลือกพื้นที่ —</option>
                <?php foreach ($areas as $a): ?>
                    <option value="<?= (int)$a['id'] ?>"><?= Helper::e(trim(($a['name'] ?? '') . ' ' . ($a['district'] ?? '') . ' ' . ($a['province'] ?? ''))) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-1">ที่อยู่ / จุดเกิดเหตุ</label>
            <textarea name="address" rows="2" class="input-field" placeholder="บ้านเลขที่ หมู่ ถนน ฯลฯ"></textarea>
        </div>
    </div>

    <!-- Location: map + GPS -->
    <div class="card p-4 space-y-3">
        <p class="text-base font-bold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            ตำแหน่ง
        </p>
        <p class="text-sm text-slate-500">แตะบนแผนที่หรือลากหมุดเพื่อปักจุดเกิดเหตุ</p>
        <div id="pickMap" class="h-72 rounded-xl border border-slate-300"></div>
        <div class="flex items-center gap-2">
            <button type="button" id="geoBtn" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl px-4 py-3 text-base">
                📍 ใช้ตำแหน่งปัจจุบัน
            </button>
            <button type="button" id="clearCoords" class="shrink-0 bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-500 font-semibold rounded-xl px-4 py-3 text-base">
                ✕ ล้าง
            </button>
            <span id="geoStatus" class="text-sm text-slate-500"></span>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">ละติจูด</label>
                <input type="number" id="vLatNum" step="any" class="input-field !text-base" placeholder="6.869">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">ลองจิจูด</label>
                <input type="number" id="vLngNum" step="any" class="input-field !text-base" placeholder="101.250">
            </div>
        </div>
    </div>

    <!-- Situation -->
    <div class="card p-4 space-y-3">
        <p class="text-base font-bold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            สถานการณ์
        </p>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-semibold text-slate-600 mb-1">จำนวนผู้ได้รับผล</label>
                <input type="number" name="people_count" min="1" value="1" class="input-field">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-600 mb-1">ความเร่งด่วน</label>
                <select name="urgency" class="input-field">
                    <?php foreach ($urgencyLabels as $k => $label): ?>
                        <option value="<?= Helper::e($k) ?>" <?= $k === 'normal' ? 'selected' : '' ?>><?= Helper::e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-1.5">ความต้องการ</label>
            <div class="grid grid-cols-2 gap-2">
                <?php foreach ($needTypes as $key => $label): ?>
                    <label class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 cursor-pointer has-[:checked]:bg-brand-50 has-[:checked]:border-brand-500">
                        <?= Helper::needTypeIcon($key, 'w-5 h-5 text-brand-600 shrink-0') ?>
                        <span class="text-base font-medium"><?= Helper::e($label) ?></span>
                        <input type="checkbox" name="need_types[]" value="<?= Helper::e($key) ?>" class="ml-auto accent-brand-600 w-5 h-5">
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-1">รายละเอียดเหตุการณ์</label>
            <textarea name="description" rows="3" class="input-field" placeholder="อาการ น้ำระดับ ความต้องการเร่งด่วน ฯลฯ"></textarea>
        </div>
    </div>

    <!-- Photo: camera / gallery + preview -->
    <div class="card p-4 space-y-3">
        <p class="text-base font-bold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            รูปภาพ <span class="text-sm font-normal text-slate-400">(ไม่บังคับ)</span>
        </p>
        <input type="file" name="photo" id="photoInput" accept="image/*" class="hidden">
        <div class="grid grid-cols-2 gap-2">
            <button type="button" id="btnCamera" class="flex items-center justify-center gap-2 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold rounded-xl px-4 py-3.5 text-base border-2 border-brand-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                ถ่ายรูป
            </button>
            <button type="button" id="btnGallery" class="flex items-center justify-center gap-2 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold rounded-xl px-4 py-3.5 text-base border-2 border-slate-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                เลือกรูป
            </button>
        </div>
        <div id="photoPreviewWrap" class="hidden relative">
            <img id="photoPreview" alt="ตัวอย่างรูป" class="w-full rounded-xl max-h-64 object-cover bg-slate-100">
            <button type="button" id="photoRemove" class="absolute top-2 right-2 bg-slate-900/70 hover:bg-red-600 text-white rounded-full w-9 h-9 flex items-center justify-center" aria-label="ลบรูป">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <p id="photoName" class="text-sm text-slate-500 mt-1.5 truncate"></p>
        </div>
    </div>

    <button type="submit" class="btn-primary w-full px-5 py-3.5 text-lg font-semibold">
        เปิดเคสและรับผิดชอบ
    </button>
    <p class="text-sm text-center text-slate-500">เคสจะถูก assign ให้คุณโดยอัตโนมัติ</p>
</form>

<script>
(function() {
    'use strict';

    /* ---------- Map picker ---------- */
    const latNum = document.getElementById('vLatNum');
    const lngNum = document.getElementById('vLngNum');
    const latH = document.getElementById('vLat');
    const lngH = document.getElementById('vLng');
    const mapEl = document.getElementById('pickMap');
    const geoStatus = document.getElementById('geoStatus');
    let marker = null;
    let pickerMap = null;

    function setCoords(lat, lng, opts) {
        opts = opts || {};
        latH.value = Number(lat).toFixed(6);
        lngH.value = Number(lng).toFixed(6);
        if (!opts.skipInputs) {
            latNum.value = Number(lat).toFixed(6);
            lngNum.value = Number(lng).toFixed(6);
        }
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else if (pickerMap) {
            marker = L.marker([lat, lng], { draggable: true }).addTo(pickerMap);
            marker.on('dragend', function() {
                const p = marker.getLatLng();
                setCoords(p.lat, p.lng);
            });
        }
        geoStatus.textContent = '✓ ปักหมุดแล้ว';
        geoStatus.className = 'text-sm text-emerald-600 font-semibold';
    }

    if (typeof L !== 'undefined' && mapEl) {
        pickerMap = L.map(mapEl).setView([6.8, 101.3], 9);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap', maxZoom: 19
        }).addTo(pickerMap);
        pickerMap.on('click', function(e) { setCoords(e.latlng.lat, e.latlng.lng); });
        setTimeout(function() { pickerMap.invalidateSize(); }, 150);
    }

    function syncFromInputs() {
        const la = parseFloat(latNum.value), ln = parseFloat(lngNum.value);
        if (isFinite(la) && isFinite(ln)) {
            latH.value = la.toFixed(6);
            lngH.value = ln.toFixed(6);
            if (marker) marker.setLatLng([la, ln]);
            else if (pickerMap) {
                marker = L.marker([la, ln], { draggable: true }).addTo(pickerMap);
                marker.on('dragend', function() {
                    const p = marker.getLatLng();
                    setCoords(p.lat, p.lng);
                });
            }
            pickerMap && pickerMap.setView([la, ln], 14);
        }
    }
    latNum.addEventListener('change', syncFromInputs);
    lngNum.addEventListener('change', syncFromInputs);

    document.getElementById('geoBtn')?.addEventListener('click', function() {
        if (!navigator.geolocation) { geoStatus.textContent = 'อุปกรณ์ไม่รองรับ'; return; }
        geoStatus.textContent = 'กำลังระบุตำแหน่ง...';
        geoStatus.className = 'text-sm text-slate-500';
        navigator.geolocation.getCurrentPosition(
            function(pos) {
                const la = pos.coords.latitude, ln = pos.coords.longitude;
                setCoords(la, ln);
                if (pickerMap) pickerMap.setView([la, ln], 16);
            },
            function() { geoStatus.textContent = 'ดึงตำแหน่งไม่ได้ — แตะบนแผนที่แทน'; geoStatus.className = 'text-sm text-red-500'; },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });

    document.getElementById('clearCoords')?.addEventListener('click', function() {
        latH.value = ''; lngH.value = ''; latNum.value = ''; lngNum.value = '';
        if (marker && pickerMap) { pickerMap.removeLayer(marker); marker = null; }
        geoStatus.textContent = '';
    });

    /* ---------- Photo: camera / gallery + preview ---------- */
    const photoInput = document.getElementById('photoInput');
    const previewWrap = document.getElementById('photoPreviewWrap');
    const preview = document.getElementById('photoPreview');
    const photoName = document.getElementById('photoName');
    let captureMode = false;

    function pickPhoto(useCamera) {
        captureMode = !!useCamera;
        // Re-create input so the `capture` attribute applies on mobile
        const fresh = document.createElement('input');
        fresh.type = 'file';
        fresh.name = 'photo';
        fresh.accept = 'image/*';
        if (useCamera) fresh.setAttribute('capture', 'environment');
        fresh.id = 'photoInput';
        photoInput.replaceWith(fresh);
        fresh.addEventListener('change', onPhotoChosen);
        fresh.click();
    }

    function onPhotoChosen(e) {
        const f = e.target.files && e.target.files[0];
        if (!f) return;
        photoName.textContent = f.name;
        const reader = new FileReader();
        reader.onload = function(ev) {
            preview.src = ev.target.result;
            previewWrap.classList.remove('hidden');
        };
        reader.readAsDataURL(f);
    }

    // Bind current input (in case user picks without camera/gallery buttons)
    photoInput.addEventListener('change', onPhotoChosen);
    document.getElementById('btnCamera')?.addEventListener('click', () => pickPhoto(true));
    document.getElementById('btnGallery')?.addEventListener('click', () => pickPhoto(false));

    document.getElementById('photoRemove')?.addEventListener('click', function() {
        const cur = document.getElementById('photoInput');
        if (cur) cur.value = '';
        previewWrap.classList.add('hidden');
        preview.src = '';
        photoName.textContent = '';
    });

    // Warn before leaving with unsent form? keep simple — no.
    document.getElementById('caseForm')?.addEventListener('submit', function() {
        // ensure hidden lat/lng synced from visible inputs
        syncFromInputs();
    });
})();
</script>
