<?php
/** Admin: manage important places (POIs) shown on public maps.
 *  Expects: $pois, $poiCategories, $csrf
 */
?>
<div class="max-w-5xl space-y-5">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2.5">
                <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                จุดสำคัญบนแผนที่
            </h1>
            <p class="text-slate-500 mt-1">จุดพักพิง จุดอพยพ จุดแจกจ่าย ฯลฯ — แสดงบนแผนที่สาธารณะให้ผู้ประสบภัยเห็น</p>
        </div>
        <button type="button" id="poiAddBtn" class="btn-primary shrink-0">
            <i class="fa-solid fa-plus mr-2"></i>เพิ่มจุดใหม่
        </button>
    </div>

    <!-- List -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-left">
                    <th class="px-4 py-3 font-medium text-slate-600">จุด</th>
                    <th class="px-4 py-3 font-medium text-slate-600">หมวด</th>
                    <th class="px-4 py-3 font-medium text-slate-600">สถานะ</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($pois)): ?>
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">ยังไม่มีจุดสำคัญ — กดปุ่มเพิ่มจุดใหม่</td></tr>
                <?php else: foreach ($pois as $p): $cat = Helper::poiCategory($p['category']); ?>
                    <tr class="hover:bg-slate-50" data-poi='<?= Helper::e(json_encode([
                        'id' => (int)$p['id'], 'title' => $p['title'], 'category' => $p['category'],
                        'description' => $p['description'] ?? '', 'lat' => (float)$p['lat'], 'lng' => (float)$p['lng'],
                        'is_active' => (int)$p['is_active'],
                    ], JSON_UNESCAPED_UNICODE)) ?>'>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800"><?= $cat['emoji'] ?> <?= Helper::e($p['title']) ?></div>
                            <?php if ($p['description']): ?><div class="text-xs text-slate-400 truncate max-w-[220px] mt-0.5"><?= Helper::e($p['description']) ?></div><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-slate-600 whitespace-nowrap"><?= Helper::e($cat['label']) ?></td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-1 rounded-full font-medium <?= $p['is_active'] ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' ?>">
                                <?= $p['is_active'] ? 'แสดงอยู่' : 'ซ่อน' ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <button type="button" class="poi-edit text-brand-600 font-medium hover:underline text-sm mr-3">แก้ไข</button>
                            <button type="button" class="poi-del text-red-600 font-medium hover:underline text-sm">ลบ</button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Form modal -->
<div id="poiFormModal" class="fixed inset-0 z-[2000] hidden">
    <div class="absolute inset-0 bg-slate-900/60" id="poiFormBackdrop"></div>
    <div class="absolute inset-4 sm:inset-8 md:inset-12 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden max-w-2xl mx-auto my-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
            <h3 class="font-semibold text-slate-800 text-lg" id="formTitle">เพิ่มจุดใหม่</h3>
            <button type="button" id="poiFormClose" class="p-2 rounded-lg hover:bg-slate-100 text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
            <form id="poiForm" class="space-y-4">
                <input type="hidden" name="id" id="poiId">
                <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
                <input type="hidden" name="lat" id="poiLat">
                <input type="hidden" name="lng" id="poiLng">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ชื่อจุด <span class="text-red-600">*</span></label>
                    <input type="text" id="poiName" required class="input-field" placeholder="เช่น ศูนย์พักพิงวัดบ้านใหม่">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">หมวดหมู่</label>
                    <select id="poiCategory" class="input-field">
                        <?php foreach ($poiCategories as $k => $c): ?>
                            <option value="<?= $k ?>"><?= $c['emoji'] ?> <?= Helper::e($c['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">รายละเอียด</label>
                    <textarea id="poiDesc" rows="3" class="input-field" placeholder="ที่อยู่ สิ่งอำนวยความสะดวก เวลาทำการ ฯลฯ"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ตำแหน่ง <span class="text-red-600">*</span></label>
                    <input type="text" id="poiCoordDisplay" readonly class="input-field !bg-slate-50 cursor-pointer hover:bg-slate-100 transition" placeholder="คลิกเพื่อเลือกตำแหน่งบนแผนที่">
                </div>
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" id="poiActive" checked class="rounded border-slate-300 text-brand-600 w-4 h-4">
                    แสดงบนแผนที่สาธารณะ
                </label>
            </form>
        </div>
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50 flex gap-2 justify-end">
            <button type="button" id="poiCancel" class="px-4 py-2.5 text-sm text-slate-600 hover:text-slate-800">ยกเลิก</button>
            <button type="submit" form="poiForm" class="btn-primary">บันทึก</button>
        </div>
    </div>
</div>

<!-- Map picker modal (nested inside form modal conceptually, but DOM separate for z-index) -->
<div id="poiMapModal" class="fixed inset-0 z-[2001] hidden">
    <div class="absolute inset-0 bg-slate-900/60" id="poiMapBackdrop"></div>
    <div class="absolute inset-4 sm:inset-8 md:inset-12 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200">
            <h3 class="font-semibold text-slate-800">เลือกตำแหน่งบนแผนที่</h3>
            <button type="button" id="poiMapClose" class="p-2 rounded-lg hover:bg-slate-100 text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="poiMap" class="flex-1 min-h-0"></div>
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50 flex gap-2 items-center">
            <button type="button" id="poiGeo" class="text-sm font-semibold bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg px-3 py-2">📍 ตำแหน่งปัจจุบัน</button>
            <button type="button" id="poiClear" class="text-sm font-semibold bg-white border border-slate-300 hover:bg-red-50 hover:text-red-600 text-slate-600 rounded-lg px-3 py-2">ล้าง</button>
            <p id="poiCoordLabel" class="text-xs text-emerald-600 font-semibold ml-auto"></p>
        </div>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('poiForm');
    const latIn = document.getElementById('poiLat');
    const lngIn = document.getElementById('poiLng');
    const coordDisplay = document.getElementById('poiCoordDisplay');
    const coordLabel = document.getElementById('poiCoordLabel');
    const formModal = document.getElementById('poiFormModal');
    const formBackdrop = document.getElementById('poiFormBackdrop');
    const mapModal = document.getElementById('poiMapModal');
    const mapBackdrop = document.getElementById('poiMapBackdrop');
    let marker = null, pickerMap = null;

    // Form modal: open/close
    function openFormModal(poi = null) {
        formModal.classList.remove('hidden');
        if (poi) {
            document.getElementById('poiId').value = poi.id;
            document.getElementById('poiName').value = poi.title;
            document.getElementById('poiCategory').value = poi.category;
            document.getElementById('poiDesc').value = poi.description || '';
            document.getElementById('poiActive').checked = !!poi.is_active;
            document.getElementById('formTitle').textContent = 'แก้ไข: ' + poi.title;
            latIn.value = poi.lat;
            lngIn.value = poi.lng;
            coordDisplay.value = '📍 ' + Number(poi.lat).toFixed(5) + ', ' + Number(poi.lng).toFixed(5);
        } else {
            form.reset();
            document.getElementById('poiId').value = '';
            document.getElementById('formTitle').textContent = 'เพิ่มจุดใหม่';
            document.getElementById('poiActive').checked = true;
            latIn.value = ''; lngIn.value = '';
            coordDisplay.value = '';
        }
    }
    function closeFormModal() {
        formModal.classList.add('hidden');
    }
    document.getElementById('poiAddBtn').addEventListener('click', () => openFormModal());
    document.getElementById('poiFormClose').addEventListener('click', closeFormModal);
    document.getElementById('poiCancel').addEventListener('click', closeFormModal);
    formBackdrop.addEventListener('click', closeFormModal);

    // Map picker modal: open/close
    function openMapModal() {
        mapModal.classList.remove('hidden');
        if (!pickerMap) {
            pickerMap = L.map('poiMap').setView([6.8, 101.3], 9);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(pickerMap);
            pickerMap.on('click', e => setCoords(e.latlng.lat, e.latlng.lng));
            // Existing POIs as preview markers
            document.querySelectorAll('tr[data-poi]').forEach(tr => {
                try {
                    const p = JSON.parse(tr.dataset.poi);
                    if (p.lat && p.lng) {
                        const cat = <?= json_encode(array_map(fn($c) => ['emoji' => $c['emoji'], 'color' => $c['color']], $poiCategories)) ?>[p.category] || { emoji: '📌', color: '#64748B' };
                        L.marker([p.lat, p.lng], {
                            icon: L.divIcon({ className: '', html: `<div style="font-size:22px;filter:drop-shadow(0 1px 3px rgba(0,0,0,.4))">${cat.emoji}</div>`, iconSize: [26, 26], iconAnchor: [13, 13] })
                        }).addTo(pickerMap);
                    }
                } catch (e) {}
            });
        }
        setTimeout(() => pickerMap.invalidateSize(), 150);
        // If coords already set, center on them
        if (latIn.value && lngIn.value) {
            const lat = parseFloat(latIn.value), lng = parseFloat(lngIn.value);
            setCoords(lat, lng);
            pickerMap.setView([lat, lng], 14);
        }
    }
    function closeMapModal() {
        mapModal.classList.add('hidden');
    }
    coordDisplay.addEventListener('click', openMapModal);
    document.getElementById('poiMapClose').addEventListener('click', closeMapModal);
    mapBackdrop.addEventListener('click', closeMapModal);

    function setCoords(lat, lng) {
        latIn.value = Number(lat).toFixed(6);
        lngIn.value = Number(lng).toFixed(6);
        coordDisplay.value = '📍 ' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5);
        if (marker) marker.setLatLng([lat, lng]);
        else marker = L.marker([lat, lng], { draggable: true }).addTo(pickerMap);
        marker.off('dragend').on('dragend', () => { const p = marker.getLatLng(); setCoords(p.lat, p.lng); });
        coordLabel.textContent = '📍 ' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5);
    }

    document.getElementById('poiGeo').addEventListener('click', () => {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(pos => {
            setCoords(pos.coords.latitude, pos.coords.longitude);
            pickerMap.setView([pos.coords.latitude, pos.coords.longitude], 15);
        }, () => {}, { timeout: 8000 });
    });
    document.getElementById('poiClear').addEventListener('click', () => {
        latIn.value = ''; lngIn.value = '';
        coordDisplay.value = '';
        if (marker) { pickerMap.removeLayer(marker); marker = null; }
        coordLabel.textContent = '';
    });

    // Edit / delete
    document.querySelectorAll('.poi-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const p = JSON.parse(btn.closest('tr').dataset.poi);
            openFormModal(p);
        });
    });
    document.querySelectorAll('.poi-del').forEach(btn => {
        btn.addEventListener('click', async () => {
            const tr = btn.closest('tr');
            const p = JSON.parse(tr.dataset.poi);
            if (!confirm('ลบจุด "' + p.title + '" ?')) return;
            const fd = new FormData();
            fd.append('csrf_token', window.csrfToken);
            const res = await fetch(window.BASE_PATH + '/admin/pois/' + p.id + '/delete', { method: 'POST', body: fd });
            const json = await res.json();
            if (json.ok) { Toast.show(json.message, 'ok'); setTimeout(() => location.reload(), 600); }
            else Toast.show(json.message || 'ไม่สำเร็จ', 'err');
        });
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        fd.set('category', document.getElementById('poiCategory').value);
        fd.set('title', document.getElementById('poiName').value);
        fd.set('description', document.getElementById('poiDesc').value);
        fd.set('is_active', document.getElementById('poiActive').checked ? '1' : '0');
        if (!fd.get('lat')) { Toast.show('กรุณาเลือกตำแหน่งก่อน', 'err'); return; }
        const res = await fetch(window.BASE_PATH + '/admin/pois', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.ok) { Toast.show(json.message || 'บันทึกแล้ว', 'ok'); setTimeout(() => location.reload(), 700); }
        else Toast.show(json.message || 'ไม่สำเร็จ', 'err');
    });
})();
</script>
