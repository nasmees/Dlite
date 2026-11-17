<?php
/** Admin: manually create a request. Expects $areas, $needTypes, $urgencyLabels, $oldTypes, $oldUrgency, $errors, $old, $csrf */
$o = $old;
$err = function(string $k) use ($errors) {
    return !empty($errors[$k]) ? '<p class="text-xs text-red-600 mt-1">' . Helper::e($errors[$k]) . '</p>' : '';
};
?>
<div class="max-w-3xl space-y-5">
    <div>
        <a href="<?= Helper::url('admin/requests') ?>" class="text-sm text-slate-500 hover:text-brand-600">← กลับรายการคำขอ</a>
        <h1 class="text-2xl font-bold text-slate-800 mt-1 flex items-center gap-2.5">
            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            เพิ่มคำขอด้วยตนเอง
        </h1>
        <p class="text-slate-500 mt-1">ลงทะเบียนเคสที่รับแจ้งทางโทรศัพท์/วิทยุ — กรอกข้อมูลให้ครบถ้วน</p>
    </div>

    <form method="post" action="<?= Helper::url('admin/requests/create') ?>" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">

        <!-- Reporter -->
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-800 mb-4 flex items-center gap-2"><svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>ผู้แจ้ง</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ชื่อผู้แจ้ง <span class="text-red-600">*</span></label>
                    <input type="text" name="reporter_name" required value="<?= Helper::e($o['reporter_name'] ?? '') ?>" class="input-field" placeholder="ชื่อ-นามสกุล / ชื่อเล่น">
                    <?= $err('name') ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">เบอร์โทร</label>
                    <input type="tel" name="reporter_phone" value="<?= Helper::e($o['reporter_phone'] ?? '') ?>" class="input-field" placeholder="08x-xxx-xxxx" inputmode="tel">
                </div>
            </div>
        </div>

        <!-- Location -->
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-800 mb-1 flex items-center gap-2"><svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>ตำแหน่ง</h2>
            <p class="text-sm text-slate-500 mb-4">คลิกบนแผนที่เพื่อปักหมุด หรือกรอกพิกัดเอง (ไม่บังคับ — เว้นว่างได้ถ้าไม่ทราบ)</p>

            <div id="pickMap" class="h-96 rounded-xl border border-slate-300 mb-3"></div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ละติจูด (lat)</label>
                    <input type="number" name="lat" id="fLat" step="any" value="<?= Helper::e((string)($o['lat'] ?? '')) ?>" class="input-field" placeholder="6.869">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ลองจิจูด (lng)</label>
                    <input type="number" name="lng" id="fLng" step="any" value="<?= Helper::e((string)($o['lng'] ?? '')) ?>" class="input-field" placeholder="101.250">
                </div>
            </div>
            <?= $err('coords') ?>
            <button type="button" id="clearCoords" class="mt-2 text-sm text-slate-500 hover:text-red-600">ล้างพิกัด</button>
        </div>

        <!-- Incident -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
            <h2 class="font-semibold text-slate-800 flex items-center gap-2"><svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>รายละเอียดเหตุการณ์</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">พื้นที่</label>
                    <select name="area_id" class="input-field">
                        <option value="">-- ไม่ระบุ --</option>
                        <?php foreach ($areas as $a): ?>
                            <option value="<?= (int)$a['id'] ?>" <?= (int)($o['area_id'] ?? 0) === (int)$a['id'] ? 'selected' : '' ?>><?= Helper::e($a['name']) ?> (<?= Helper::e($a['province']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <?= $err('area') ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">จุดสังเกต / ที่อยู่</label>
                    <input type="text" name="address" value="<?= Helper::e($o['address'] ?? '') ?>" class="input-field" placeholder="เช่น บ้านเลขที่, ซอย, อำเภอ">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">จำนวนผู้ได้รับผลกระทบ</label>
                    <input type="number" name="people_count" min="1" value="<?= (int)($o['people_count'] ?? 1) ?>" class="input-field" inputmode="numeric">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ประเภทเหตุการณ์</label>
                    <select name="scenario" class="input-field">
                        <option value="">-- ไม่ระบุ --</option>
                        <?php foreach ($scenarioLabels as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($o['scenario'] ?? '') === $k ? 'selected' : '' ?>><?= Helper::e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ความเร่งด่วน</label>
                    <select name="urgency" class="input-field">
                        <?php foreach ($urgencyLabels as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $oldUrgency === $k ? 'selected' : '' ?>><?= Helper::e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">ความต้องการ</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <?php foreach ($needTypes as $key => $label): ?>
                        <label class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 cursor-pointer has-[:checked]:bg-brand-50 has-[:checked]:border-brand-600 transition">
                            <input type="checkbox" name="need_types[]" value="<?= Helper::e($key) ?>" class="rounded border-slate-300 text-brand-600" <?= in_array($key, $oldTypes, true) ? 'checked' : '' ?>>
                            <span class="text-sm font-medium text-slate-700"><?= Helper::e($label) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">รายละเอียดเพิ่มเติม</label>
                <textarea name="description" rows="3" class="input-field" placeholder="อธิบายสถานการณ์ สาเหตุ หรือสิ่งที่ต้องการ"><?= Helper::e($o['description'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">รูปภาพประกอบ <span class="text-slate-400 font-normal">(ไม่บังคับ)</span></label>
                <input type="file" name="photo" accept="image/*" class="input-field">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary px-6">บันทึกคำขอ</button>
            <a href="<?= Helper::url('admin/requests') ?>" class="px-4 py-2.5 text-sm text-slate-600 hover:text-slate-800">ยกเลิก</a>
        </div>
    </form>
</div>

<script>
(function() {
    const latIn = document.getElementById('fLat');
    const lngIn = document.getElementById('fLng');
    const mapEl = document.getElementById('pickMap');
    let marker = null;
    let pickerMap = null;

    function setCoords(lat, lng) {
        latIn.value = Number(lat).toFixed(6);
        lngIn.value = Number(lng).toFixed(6);
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else if (pickerMap) {
            marker = L.marker([lat, lng]).addTo(pickerMap);
        }
    }

    if (typeof L !== 'undefined' && mapEl) {
        pickerMap = L.map(mapEl).setView([6.8, 101.3], 9);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap', maxZoom: 19
        }).addTo(pickerMap);
        pickerMap.on('click', function(e) { setCoords(e.latlng.lat, e.latlng.lng); });
        setTimeout(function() { pickerMap.invalidateSize(); }, 100);

        // Restore marker if coords were prefilled (validation error re-render)
        if (latIn.value !== '' && lngIn.value !== '') {
            setCoords(parseFloat(latIn.value), parseFloat(lngIn.value));
            pickerMap.setView([parseFloat(latIn.value), parseFloat(lngIn.value)], 14);
        }
    }

    document.getElementById('clearCoords').addEventListener('click', function() {
        latIn.value = ''; lngIn.value = '';
        if (marker) { pickerMap.removeLayer(marker); marker = null; }
    });

    // Keep inputs and map marker in sync when typed manually
    function syncFromInputs() {
        const la = parseFloat(latIn.value), ln = parseFloat(lngIn.value);
        if (isFinite(la) && isFinite(ln)) {
            if (marker) marker.setLatLng([la, ln]);
            else if (pickerMap) marker = L.marker([la, ln]).addTo(pickerMap);
        }
    }
    latIn.addEventListener('change', syncFromInputs);
    lngIn.addEventListener('change', syncFromInputs);
})();
</script>
