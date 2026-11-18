<?php
/** Admin: manage important places (POIs) shown on public maps.
 *  Expects: $pois, $poiCategories, $csrf
 */
?>
<div class="max-w-4xl space-y-5">
    <div>
        <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2.5">
            <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            จุดสำคัญบนแผนที่
        </h1>
        <p class="text-slate-500 mt-1">จุดพักพิง จุดอพยพ จุดแจกจ่าย ฯลฯ — แสดงบนแผนที่สาธารณะให้ผู้ประสบภัยเห็น</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
        <!-- Form -->
        <form id="poiForm" class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5 space-y-4 h-fit">
            <h2 class="font-semibold text-slate-800" id="formTitle">เพิ่มจุดใหม่</h2>
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
                <div id="poiMap" class="h-56 rounded-xl border border-slate-300 mb-2"></div>
                <p class="text-xs text-slate-500 mb-2">คลิกบนแผนที่เพื่อปักหมุด หรือใช้พิกัดปัจจุบัน</p>
                <div class="flex gap-2">
                    <button type="button" id="poiGeo" class="flex-1 text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg px-3 py-2">📍 ตำแหน่งปัจจุบัน</button>
                    <button type="button" id="poiClear" class="text-sm font-semibold bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-600 rounded-lg px-3 py-2">ล้าง</button>
                </div>
                <p id="poiCoordLabel" class="text-xs text-emerald-600 font-semibold mt-1.5"></p>
            </div>
            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                <input type="checkbox" id="poiActive" checked class="rounded border-slate-300 text-brand-600 w-4 h-4">
                แสดงบนแผนที่สาธารณะ
            </label>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary flex-1">บันทึก</button>
                <button type="button" id="poiCancel" class="hidden px-4 py-2.5 text-sm text-slate-600 hover:text-slate-800">ยกเลิก</button>
            </div>
        </form>

        <!-- List -->
        <div class="lg:col-span-3 bg-white rounded-xl border border-slate-200 overflow-hidden h-fit">
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
                        <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">ยังไม่มีจุดสำคัญ — เพิ่มจากฟอร์มด้านซ้าย</td></tr>
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
</div>

<script>
(function() {
    const form = document.getElementById('poiForm');
    const latIn = document.getElementById('poiLat');
    const lngIn = document.getElementById('poiLng');
    const coordLabel = document.getElementById('poiCoordLabel');
    let marker = null, pickerMap = null;

    pickerMap = L.map('poiMap').setView([6.8, 101.3], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(pickerMap);
    pickerMap.on('click', e => setCoords(e.latlng.lat, e.latlng.lng));
    setTimeout(() => pickerMap.invalidateSize(), 150);

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

    function setCoords(lat, lng) {
        latIn.value = Number(lat).toFixed(6);
        lngIn.value = Number(lng).toFixed(6);
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
        if (marker) { pickerMap.removeLayer(marker); marker = null; }
        coordLabel.textContent = '';
    });

    // Edit / delete
    document.querySelectorAll('.poi-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const p = JSON.parse(btn.closest('tr').dataset.poi);
            document.getElementById('poiId').value = p.id;
            document.getElementById('poiName').value = p.title;
            document.getElementById('poiCategory').value = p.category;
            document.getElementById('poiDesc').value = p.description || '';
            document.getElementById('poiActive').checked = !!p.is_active;
            document.getElementById('formTitle').textContent = 'แก้ไข: ' + p.title;
            document.getElementById('poiCancel').classList.remove('hidden');
            setCoords(p.lat, p.lng);
            pickerMap.setView([p.lat, p.lng], 14);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
    document.getElementById('poiCancel').addEventListener('click', () => {
        form.reset();
        document.getElementById('poiId').value = '';
        document.getElementById('formTitle').textContent = 'เพิ่มจุดใหม่';
        document.getElementById('poiCancel').classList.add('hidden');
        document.getElementById('poiActive').checked = true;
        document.getElementById('poiClear').click();
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
        if (!fd.get('lat')) { Toast.show('กรุณาปักหมุดบนแผนที่ก่อน', 'err'); return; }
        const res = await fetch(window.BASE_PATH + '/admin/pois', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.ok) { Toast.show(json.message || 'บันทึกแล้ว', 'ok'); setTimeout(() => location.reload(), 700); }
        else Toast.show(json.message || 'ไม่สำเร็จ', 'err');
    });
})();
</script>
