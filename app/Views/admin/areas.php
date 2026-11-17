<?php
/** Admin: area management. Expects $areas, $csrf */
?>
<div class="space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">จัดการพื้นที่</h1>
        <p class="text-slate-500 mt-1">พื้นที่/ตำบลที่ใช้ในแบบฟอร์มแจ้งเหตุ (<?= count($areas) ?> รายการ)</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Create / edit form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-800 mb-4" id="areaFormTitle">เพิ่มพื้นที่ใหม่</h2>
                <form id="areaForm" class="space-y-3">
                    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
                    <input type="hidden" name="id" id="areaId" value="">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">ชื่อพื้นที่ *</label>
                        <input type="text" name="name" id="areaName" required class="input-field" placeholder="เช่น บ้านยาง triggering, บ้านปูเต๊ะ">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">จังหวัด</label>
                            <input type="text" name="province" id="areaProvince" class="input-field" placeholder="ยะลา">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">อำเภอ</label>
                            <input type="text" name="district" id="areaDistrict" class="input-field" placeholder="เมืองยะลา">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">ตำบล</label>
                        <input type="text" name="subdistrict" id="areaSubdistrict" class="input-field" placeholder="ตำบล...">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">ละติจูด</label>
                            <input type="text" name="lat" id="areaLat" class="input-field" placeholder="6.1234" inputmode="decimal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">ลองจิจูด</label>
                            <input type="text" name="lng" id="areaLng" class="input-field" placeholder="101.2345" inputmode="decimal">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary flex-1">บันทึก</button>
                        <button type="button" id="areaReset" class="px-4 py-2.5 rounded-lg bg-slate-100 text-slate-600 text-sm hover:bg-slate-200 transition">ล้าง</button>
                    </div>
                </form>
                <p id="areaMsg" class="text-sm mt-2 hidden"></p>
            </div>
        </div>

        <!-- Area list -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-left">
                            <th class="px-4 py-3 font-medium text-slate-600">ชื่อพื้นที่</th>
                            <th class="px-4 py-3 font-medium text-slate-600">จังหวัด/อำเภอ</th>
                            <th class="px-4 py-3 font-medium text-slate-600">พิกัด</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($areas)): ?>
                            <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">ยังไม่มีพื้นที่ — เพิ่มด้านซ้าย</td></tr>
                        <?php else: foreach ($areas as $a): ?>
                            <tr class="hover:bg-slate-50" data-area='<?= Helper::e(json_encode($a, JSON_UNESCAPED_UNICODE)) ?>'>
                                <td class="px-4 py-3 font-medium text-slate-800"><?= Helper::e($a['name']) ?></td>
                                <td class="px-4 py-3 text-slate-600"><?= Helper::e(trim(($a['province'] ?? '') . ' ' . ($a['district'] ?? ''))) ?: '—' ?></td>
                                <td class="px-4 py-3 text-slate-500 text-xs"><?= $a['lat'] ? Helper::e($a['lat'] . ', ' . $a['lng']) : '—' ?></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <button type="button" class="edit-area text-sm px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition">แก้ไข</button>
                                    <button type="button" class="del-area text-sm px-3 py-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition ml-1.5">ลบ</button>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const csrf = '<?= Helper::e($csrf) ?>';
    const form = document.getElementById('areaForm');
    const msg = document.getElementById('areaMsg');

    function showMsg(ok, text) {
        msg.classList.remove('hidden');
        msg.textContent = text;
        msg.className = 'text-sm mt-2 ' + (ok ? 'text-emerald-600' : 'text-red-600');
    }

    function resetForm() {
        form.reset();
        document.getElementById('areaId').value = '';
        document.getElementById('areaFormTitle').textContent = 'เพิ่มพื้นที่ใหม่';
    }
    document.getElementById('areaReset').addEventListener('click', resetForm);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        try {
            const res = await fetch(window.BASE_PATH + '/admin/areas', {
                method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd
            });
            const json = await res.json();
            showMsg(json.ok, json.message || '');
            if (json.ok) setTimeout(() => location.reload(), 800);
        } catch (err) { showMsg(false, 'เครือข่ายมีปัญหา'); }
        btn.disabled = false;
    });

    document.querySelectorAll('.edit-area').forEach(btn => {
        btn.addEventListener('click', () => {
            const tr = btn.closest('tr');
            const a = JSON.parse(tr.dataset.area);
            document.getElementById('areaId').value = a.id;
            document.getElementById('areaName').value = a.name || '';
            document.getElementById('areaProvince').value = a.province || '';
            document.getElementById('areaDistrict').value = a.district || '';
            document.getElementById('areaSubdistrict').value = a.subdistrict || '';
            document.getElementById('areaLat').value = a.lat || '';
            document.getElementById('areaLng').value = a.lng || '';
            document.getElementById('areaFormTitle').textContent = 'แก้ไข: ' + a.name;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    document.querySelectorAll('.del-area').forEach(btn => {
        btn.addEventListener('click', async () => {
            const tr = btn.closest('tr');
            const a = JSON.parse(tr.dataset.area);
            if (!confirm('ลบพื้นที่ "' + a.name + '"?')) return;
            const fd = new FormData();
            fd.append('csrf_token', csrf);
            try {
                const res = await fetch(window.BASE_PATH + '/admin/areas/' + a.id + '/delete', {
                    method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd
                });
                const json = await res.json();
                if (json.ok) location.reload();
                else Toast.show(json.message || 'ไม่สำเร็จ', 'err');
            } catch (err) { Toast.show('เครือข่ายมีปัญหา', 'err'); }
        });
    });
})();
</script>
