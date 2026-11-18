<div class="command-center">
    <div class="admin-stats">
        <?php
        $statItems = [
            ['ทั้งหมด', $stats['total'] ?? 0, '#004c97'],
            ['รอรับเรื่อง', $stats['pending'] ?? 0, '#dc2626'],
            ['กำลังดำเนินการ', $stats['active'] ?? 0, '#2563eb'],
            ['เพิ่มวันนี้', $stats['today'] ?? 0, '#059669'],
            ['วิกฤตค้าง', $stats['critical'] ?? 0, '#d97706'],
        ];
        foreach ($statItems as [$lbl, $val, $color]): ?>
            <div class="admin-stat"><span class="num" style="color:<?= $color ?>"><?= (int)$val ?></span><span class="lbl"><?= Helper::e($lbl) ?></span></div>
        <?php endforeach; ?>
    </div>

    <section class="command-cases bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-200">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-slate-900 flex items-center gap-2"><svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>เคสเร่งด่วน</h2>
                    <p class="text-sm text-slate-500 mt-1">คำขอที่รอรับเรื่องและต้องประสานงาน</p>
                </div>
                <span id="pendingCaseBadge" class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                    <?= count($cases) ?> เคส
                </span>
            </div>
        </div>

        <div class="command-case-list flex-1 p-3 space-y-3">
            <?php if (empty($cases)): ?>
                <div class="h-full min-h-56 flex flex-col items-center justify-center text-center px-6">
                    <div class="w-11 h-11 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="font-medium text-slate-800">ไม่มีเคสที่รอรับเรื่อง</p>
                    <p class="text-sm text-slate-500 mt-1">เมื่อมีคำขอใหม่ จะแสดงในรายการนี้</p>
                </div>
            <?php else: ?>
                <?php foreach ($cases as $case): ?>
                    <article class="rounded-lg border border-slate-200 p-3 hover:border-slate-300 transition" data-case-id="<?= (int)$case['id'] ?>">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center flex-wrap gap-2">
                                    <span class="font-semibold text-slate-900"><?= Helper::e($case['code']) ?></span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium <?= Helper::urgencyColor($case['urgency']) ?>">
                                        <?= Helper::e($urgencyLabels[$case['urgency']] ?? $case['urgency']) ?>
                                    </span>
                                    <?php if (!empty($case['scenario'])): ?>
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium bg-brand-50 text-brand-700">
                                            <?= Helper::e(Helper::scenarioLabel($case['scenario'] ?? null)) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-slate-700 mt-1"><?= Helper::e($case['reporter_name']) ?> · <?= (int)$case['people_count'] ?> คน</p>
                                <p class="text-xs text-slate-500 mt-1">
                                    <?= Helper::e($case['area_name'] ?? 'ไม่ระบุพื้นที่') ?>
                                    <?php if (!empty($case['address'])): ?> · <?= Helper::e($case['address']) ?><?php endif; ?>
                                </p>
                                <p class="text-xs text-slate-400 mt-1"><?= Helper::timeAgo($case['created_at']) ?></p>
                            </div>
                            <a href="<?= Helper::url('admin/requests/' . $case['id']) ?>" class="text-xs font-medium text-brand-700 hover:underline shrink-0">รายละเอียด</a>
                        </div>

                        <?php if (!empty($case['need_types'])): ?>
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                <?php foreach (array_slice($case['need_types'], 0, 3) as $type): ?>
                                    <span class="rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-600"><?= Helper::e(Helper::needTypeLabel($type)) ?></span>
                                <?php endforeach; ?>
                                <?php if (count($case['need_types']) > 3): ?>
                                    <span class="px-1 py-1 text-xs text-slate-500">+<?= count($case['need_types']) - 3 ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-100">
                            <?php if ($canAccept): ?>
                                <button type="button" class="accept-case btn-primary text-sm" data-case-id="<?= (int)$case['id'] ?>">รับเคส</button>
                            <?php endif; ?>
                            <?php if (!empty($case['lat']) && !empty($case['lng'])): ?>
                                <button type="button" class="focus-case rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                                    data-lat="<?= Helper::e((string)$case['lat']) ?>" data-lng="<?= Helper::e((string)$case['lng']) ?>" data-case-id="<?= (int)$case['id'] ?>">
                                    ดูบนแผนที่
                                </button>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="command-map-panel bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border-b border-slate-200">
            <div>
                <h2 class="font-semibold text-slate-900 flex items-center gap-2"><svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>แผนที่สถานการณ์</h2>
                <p id="mapPointCount" class="text-sm text-slate-500 mt-1" role="status" aria-live="polite">กำลังโหลดตำแหน่ง…</p>
            </div>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-xs text-slate-600">
                <span class="inline-flex items-center gap-1.5"><i class="map-legend-dot bg-red-600"></i>รอรับเรื่อง</span>
                <span class="inline-flex items-center gap-1.5"><i class="map-legend-dot bg-blue-600"></i>รับเรื่องแล้ว</span>
                <span class="inline-flex items-center gap-1.5"><i class="map-legend-dot bg-amber-500"></i>กำลังช่วย</span>
                <span class="inline-flex items-center gap-1.5"><i class="map-legend-dot bg-emerald-600"></i>เสร็จสิ้น</span>
                <span class="inline-flex items-center gap-1.5"><i class="map-legend-dot bg-slate-500"></i>ยกเลิก</span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-1.5 px-4 py-2 border-b border-slate-100" id="mapFilters" role="group" aria-label="กรองแผนที่ตามสถานะ">
            <?php
            $mapFilters = [
                'all' => 'ทั้งหมด',
                'pending' => 'รอรับเรื่อง',
                'accepted' => 'รับเรื่องแล้ว',
                'in_progress' => 'กำลังช่วย',
                'completed' => 'สำเร็จ',
                'cancelled' => 'ยกเลิก',
            ];
            foreach ($mapFilters as $fv => $fl): ?>
                <button type="button" class="map-filter-btn <?= $fv === 'all' ? 'active' : '' ?>" data-status="<?= $fv ?>"><?= Helper::e($fl) ?></button>
            <?php endforeach; ?>
        </div>
        <div class="relative flex-1 min-h-0">
            <div id="commandMap" class="command-map w-full" role="application" aria-label="แผนที่แสดงตำแหน่งคำขอความช่วยเหลือ"></div>
            <div id="mapMessage" class="hidden absolute top-4 left-1/2 -translate-x-1/2 z-[1000] rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-md" role="status" aria-live="polite"></div>
        </div>
    </section>
</div>

<script>
(function() {
    const mapElement = document.getElementById('commandMap');
    const countElement = document.getElementById('mapPointCount');
    const messageElement = document.getElementById('mapMessage');
    const statusLabels = <?= json_encode(Helper::statusLabels(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const needTypes = <?= json_encode($needTypes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const markerColors = {
        pending: '#dc2626',
        accepted: '#2563eb',
        in_progress: '#d97706',
        completed: '#059669',
        cancelled: '#64748b'
    };

    if (typeof L === 'undefined') {
        countElement.textContent = 'โหลดแผนที่ไม่สำเร็จ';
        messageElement.textContent = 'ไม่สามารถโหลดส่วนแสดงแผนที่ได้ กรุณาตรวจสอบการเชื่อมต่อแล้วลองใหม่';
        messageElement.classList.remove('hidden');
        return;
    }

    const map = L.map(mapElement).setView([6.8, 101.3], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 19
    }).addTo(map);
    const markers = L.layerGroup().addTo(map);
    const markerIndex = new Map();
    let hasFittedMarkers = false;
    let currentStatus = 'all';
    let lastPending = <?= (int)($stats['pending'] ?? 0) ?>;

    function popupContent(point) {
        const content = document.createElement('div');
        const title = document.createElement('strong');
        title.textContent = point.code || 'คำขอความช่วยเหลือ';
        content.appendChild(title);

        const summary = document.createElement('p');
        summary.textContent = (point.name || 'ไม่ระบุชื่อ') + ' · ' + Number(point.people || 0).toLocaleString('th-TH') + ' คน';
        content.appendChild(summary);

        const status = document.createElement('p');
        status.textContent = 'สถานะ: ' + (statusLabels[point.status] || point.status);
        content.appendChild(status);

        const types = document.createElement('p');
        types.textContent = 'ความต้องการ: ' + (Array.isArray(point.types)
            ? point.types.map(type => needTypes[type] || type).join(', ')
            : '-');
        content.appendChild(types);

        const link = document.createElement('a');
        link.href = window.BASE_PATH + '/admin/requests/' + encodeURIComponent(point.id);
        link.textContent = 'ดูรายละเอียดเคส';
        link.className = 'font-medium text-blue-700';
        content.appendChild(link);
        return content;
    }

    async function loadPoints() {
        try {
            const url = window.BASE_PATH + '/admin/map/points' + (currentStatus !== 'all' ? '?status=' + encodeURIComponent(currentStatus) : '');
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Map request failed with status ' + response.status);
            const result = await response.json();
            if (!result.ok || !Array.isArray(result.points)) {
                throw new Error(result.message || 'Invalid map response');
            }

            markers.clearLayers();
            markerIndex.clear();
            const bounds = [];
            result.points.forEach(point => {
                const lat = Number(point.lat);
                const lng = Number(point.lng);
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                const marker = L.circleMarker([lat, lng], {
                    radius: point.urgency === 'critical' ? 9 : 7,
                    color: '#fff',
                    weight: 2,
                    fillColor: markerColors[point.status] || '#004c97',
                    fillOpacity: 0.95
                });
                marker.bindPopup(popupContent(point));
                markers.addLayer(marker);
                markerIndex.set(String(point.id), marker);
                bounds.push([lat, lng]);
            });

            countElement.textContent = bounds.length.toLocaleString('th-TH') + ' จุดบนแผนที่ · อัปเดตล่าสุด ' +
                new Date().toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
            messageElement.classList.toggle('hidden', bounds.length > 0);
            messageElement.textContent = bounds.length ? '' : 'ยังไม่มีคำขอที่ระบุตำแหน่งบนแผนที่';
            if (bounds.length && !hasFittedMarkers) {
                map.fitBounds(bounds, { padding: [32, 32], maxZoom: 14 });
                hasFittedMarkers = true;
            }
            map.invalidateSize();
        } catch (error) {
            countElement.textContent = 'โหลดตำแหน่งไม่สำเร็จ';
            messageElement.textContent = 'ไม่สามารถโหลดตำแหน่งได้ กรุณาลองรีเฟรชหน้า';
            messageElement.classList.remove('hidden');
            console.error(error);
        }
    }

    document.querySelectorAll('.accept-case').forEach(button => {
        button.addEventListener('click', async () => {
            const id = button.dataset.caseId;
            const form = new FormData();
            form.append('csrf_token', window.csrfToken);
            form.append('action', 'status');
            form.append('status', 'accepted');
            button.disabled = true;
            try {
                const response = await fetch(window.BASE_PATH + '/admin/requests/' + id + '/action', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: form
                });
                const result = await response.json();
                if (!response.ok || !result.ok) throw new Error(result.message || 'รับเคสไม่สำเร็จ');
                if (window.Toast) window.Toast.show('รับเคสเรียบร้อยแล้ว', 'ok');
                setTimeout(() => window.location.reload(), 500);
            } catch (error) {
                button.disabled = false;
                if (window.Toast) window.Toast.show(error.message || 'รับเคสไม่สำเร็จ', 'err');
            }
        });
    });

    document.querySelectorAll('#mapFilters .map-filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#mapFilters .map-filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentStatus = btn.dataset.status;
            hasFittedMarkers = false;
            loadPoints();
        });
    });

    async function pollPending() {
        try {
            const res = await fetch(window.BASE_PATH + '/admin/requests/pending-count', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            if (!json.ok) return;
            const badge = document.getElementById('pendingCaseBadge');
            if (badge) badge.textContent = Number(json.count).toLocaleString('th-TH') + ' เคส';
            if (json.count > lastPending && window.Toast) {
                Toast.show('มีเคสใหม่รอรับเรื่อง ' + Number(json.count).toLocaleString('th-TH') + ' เคส', 'info');
            }
            lastPending = json.count;
        } catch (e) { /* silent — next poll retries */ }
    }

    document.querySelectorAll('.focus-case').forEach(button => {
        button.addEventListener('click', () => {
            const marker = markerIndex.get(button.dataset.caseId);
            if (marker) {
                map.setView(marker.getLatLng(), 15);
                marker.openPopup();
            } else {
                map.setView([Number(button.dataset.lat), Number(button.dataset.lng)], 15);
            }
        });
    });

    loadPoints();
    pollPending();
    window.setInterval(() => { loadPoints(); pollPending(); }, 30000);
})();
</script>
