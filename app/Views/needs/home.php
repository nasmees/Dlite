<?php
/**
 * Home — sectioned civic view
 * Map card + status summary + latest requests + slide-up report drawer.
 * Expects: $areas, $needTypes, $csrf, $counts, $latest, $statusLabels, $urgencyLabels
 */
?>
<div class="space-y-4 -mt-2">
    <!-- Hero -->
    <div class="pt-1 flex items-center gap-3">
        <div class="w-12 h-12 rounded-2xl bg-brand-600 flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-brand-800 leading-tight">ศูนย์ประสานงานช่วยเหลือน้ำท่วม</h1>
            <p class="text-slate-500 mt-0.5">
                <?php if (($counts['pending'] ?? 0) > 0): ?>
                    มี <span class="font-semibold text-red-600"><?= (int)$counts['pending'] ?></span> เรื่องรอความช่วยเหลือ
                <?php else: ?>
                    ยังไม่มีเรื่องที่รอความช่วยเหลือ
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Section: Map -->
    <section>
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <h2 class="font-semibold text-slate-800">แผนที่เหตุการณ์ในพื้นที่</h2>
        </div>
        <div class="card !p-0 overflow-hidden relative">
            <div id="homeMap" class="w-full" style="height:55dvh;min-height:300px"></div>

            <!-- Status filter chips -->
            <div class="absolute top-2 right-2 z-[1001] flex gap-2 overflow-x-auto pb-1">
                <button type="button" data-status="all" class="status-filter shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold bg-brand-600 text-white shadow">ทั้งหมด</button>
                <button type="button" data-status="pending" class="status-filter shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/95 text-red-700 shadow">รอช่วยเหลือ</button>
                <button type="button" data-status="in_progress" class="status-filter shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/95 text-amber-700 shadow">กำลังช่วย</button>
                <button type="button" data-status="completed" class="status-filter shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/95 text-emerald-700 shadow">สำเร็จแล้ว</button>
            </div>

            <!-- Legend -->
            <div class="absolute bottom-3 left-3 z-[1001] bg-white/95 rounded-xl px-3 py-2 shadow text-sm space-y-1">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background:#DC2626"></span>รอช่วยเหลือ</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background:#EAB308"></span>กำลังเข้าพื้นที่</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background:#16A34A"></span>ช่วยเหลือแล้ว</div>
            </div>

            <!-- FAB: report -->
            <button type="button" id="fabReport" class="report-cta absolute bottom-20 md:bottom-5 right-4 z-[1001] min-h-[56px] px-5 rounded-2xl bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold flex items-center gap-2">
                <i class="fas fa-person-drowning text-xl"></i>
                แจ้งเหตุ
            </button>
        </div>
    </section>

    <!-- Section: Status summary -->
    <section class="rounded-2xl border-2 border-slate-300 bg-white shadow-md p-5 mb-4">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <h2 class="font-semibold text-slate-800">สถานะความช่วยเหลือ</h2>
        </div>
                <?php
        $statCards = [
            ['label'=>'ทั้งหมด','count'=>$counts['all']??0,'url'=>'needs','icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>','chip'=>'bg-white border-2 border-slate-300 text-slate-700','num'=>'text-slate-800'],
            ['label'=>'รอความช่วยเหลือ','count'=>$counts['pending']??0,'url'=>'needs?status=pending','icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>','chip'=>'bg-white border-2 border-red-300 text-red-600','num'=>'text-red-600'],
            ['label'=>'กำลังช่วยเหลือ','count'=>$counts['in_progress']??0,'url'=>'needs?status=in_progress','icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>','chip'=>'bg-white border-2 border-amber-300 text-amber-600','num'=>'text-amber-600'],
            ['label'=>'ช่วยเหลือสำเร็จ','count'=>$counts['completed']??0,'url'=>'needs?status=completed','icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>','chip'=>'bg-white border-2 border-emerald-300 text-emerald-600','num'=>'text-emerald-600'],
        ];
        ?>
        <div class="grid grid-cols-2 gap-3">
            <?php foreach ($statCards as $sc): ?>
            <a href="<?= Helper::url($sc['url']) ?>" class="card p-4 flex items-center gap-4 hover:border-brand-300 active:bg-slate-50 transition">
                <span class="w-11 h-11 rounded-xl <?= $sc['chip'] ?> flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $sc['icon'] ?></svg>
                </span>
                <span class="flex-1">
                    <span class="font-bold text-2xl <?= $sc['num'] ?> leading-none"><?= (int)$sc['count'] ?></span>
                    <span class="block text-sm text-slate-500 mt-0.5"><?= Helper::e($sc['label']) ?></span>
                </span>
            </a>
            <?php endforeach; ?>
        </div>    </section>

    <!-- Section: Latest requests -->
    <section class="rounded-2xl border-2 border-slate-300 bg-white shadow-md p-5 mb-4">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h2 class="font-semibold text-slate-800">เรื่องล่าสุด</h2>
            </div>
            <a href="<?= Helper::url('needs') ?>" class="text-sm text-brand-600 font-medium">ดูทั้งหมด →</a>
        </div>
        <div class="space-y-3 mt-3">
            <?php if (empty($latest)): ?>
                <div class="card p-6 text-center">
                    <p class="text-slate-500 text-sm">ยังไม่มีเรื่องแจ้ง — เป็นคนแรกที่แจ้งเหตุได้เลย</p>
                    <button type="button" onclick="window.openReportDrawer && window.openReportDrawer()" class="report-cta inline-block mt-3 text-sm px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold">แจ้งเหตุ</button>
                </div>
            <?php else: ?>
                <?php foreach ($latest as $n): ?>
                    <a href="<?= Helper::url('needs/' . $n['id']) ?>" class="card p-4 flex gap-4 hover:border-brand-400 hover:shadow-md transition active:bg-slate-50">
                        <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-800 text-base truncate"><?= Helper::e($n['reporter_name']) ?></div>
                                    <div class="text-sm text-slate-500 mt-0.5"><?= Helper::e($n['code']) ?> · <?= Helper::timeAgo($n['created_at']) ?></div>
                                </div>
                                <span class="text-sm px-2.5 py-1 rounded-full font-medium <?= Helper::statusColor($n['status']) ?> shrink-0"><?= Helper::e($statusLabels[$n['status']] ?? $n['status']) ?></span>
                            </div>
                            <?php if ($n['address'] || $n['area_name']): ?>
                                <div class="flex items-center gap-1 text-sm text-slate-600 mb-2">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="truncate"><?= Helper::e($n['area_name'] ?? '') ?><?= $n['area_name'] && $n['address'] ? ' · ' : '' ?><?= Helper::e($n['address'] ?? '') ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($n['need_types'])): ?>
                                <div class="flex flex-wrap gap-1.5">
                                    <?php foreach (array_slice($n['need_types'], 0, 3) as $t): ?>
                                        <span class="text-xs bg-slate-100 text-slate-700 px-2 py-1 rounded-md font-medium"><?= Helper::e($needTypes[$t] ?? $t) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="border-t border-slate-200 my-3"></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<noscript>
    <div class="card p-4 text-center my-4">
        <p class="text-sm text-slate-600 mb-3">กรุณาเปิด JavaScript เพื่อใช้งานแผนที่</p>
        <a href="<?= Helper::url('request') ?>" class="btn-primary inline-block px-5 py-3">แจ้งความต้องการ (แบบฟอร์ม)</a>
    </div>
</noscript>

<!-- Report drawer -->
<div id="reportDrawer">
    <div class="backdrop" id="drawerBackdrop"></div>
    <div class="sheet max-w-lg mx-auto">
        <?php $inDrawer = true; require APP_PATH . '/Views/needs/_form.php'; ?>
    </div>
</div>

<script>
window.addEventListener('error', function(e) {
    console.error('[DSC]', e.message, e.filename, e.lineno);
});
(function() {
    // ---------- Drawer ----------
    const drawer = document.getElementById('reportDrawer');
    function openDrawer() {
        drawer.classList.add('show');
        document.body.style.overflow = 'hidden';
        if (window.dscTryGps) window.dscTryGps();
    }
    function closeDrawer() {
        drawer.classList.remove('show');
        document.body.style.overflow = '';
    }
    window.openReportDrawer = openDrawer;
    document.getElementById('fabReport').addEventListener('click', openDrawer);
    document.getElementById('drawerBackdrop').addEventListener('click', closeDrawer);
    const closeBtn = document.getElementById('drawerClose');
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);

    // ---------- Map ----------
    const map = L.map('homeMap').setView([6.8, 101.3], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap', maxZoom: 19
    }).addTo(map);

    let markersLayer = L.layerGroup().addTo(map);
    let markerIndex = {}; // id -> marker
    const myReport = (window.getMyReport ? window.getMyReport() : null);

    const statusColors = {
        pending: '#DC2626',      // red — waiting for help
        accepted: '#EAB308',     // yellow — on the way
        in_progress: '#EAB308',
        completed: '#16A34A',    // green — resolved
        cancelled: '#94A3B8'
    };

    function createIcon(color, isMine) {
        const inner = isMine
            ? `<div class="pin-pulse" style="background:${color};width:22px;height:22px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.4)"></div>`
            : `<div style="background:${color};width:16px;height:16px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.35)"></div>`;
        return L.divIcon({
            className: '', html: inner,
            iconSize: isMine ? [22, 22] : [16, 16],
            iconAnchor: isMine ? [11, 11] : [8, 8]
        });
    }

    async function loadPoints(status) {
        markersLayer.clearLayers();
        markerIndex = {};
        const url = window.BASE_PATH + '/api/points' + (status && status !== 'all' ? '?status=' + status : '');
        try {
            const res = await fetch(url);
            const json = await res.json();
            if (!json.ok) return;
            const bounds = [];
            json.points.forEach(p => {
                const isMine = myReport && p.id === myReport.id;
                const color = isMine ? '#2563EB' : (statusColors[p.status] || '#2563EB');
                const m = L.marker([p.lat, p.lng], { icon: createIcon(color, isMine) });
                const types = (p.types || []).join(', ') || '-';
                m.bindPopup(`
                    <div style="font-family:Kanit,sans-serif;min-width:170px">
                        <strong>${isMine ? 'จุดแจ้งเหตุของคุณ — ' : ''}${p.code}</strong><br>
                        ${p.name}<br>
                        ผู้ได้รับผลกระทบ ${p.people} คน<br>
                        <a href="${window.BASE_PATH}/needs/${p.id}" style="color:#0F3D6E;font-weight:600">ดูรายละเอียด →</a>
                    </div>
                `);
                markersLayer.addLayer(m);
                markerIndex[p.id] = m;
                bounds.push([p.lat, p.lng]);
            });
            // If my report isn't in current filter, still show it
            if (myReport && myReport.lat && myReport.lng && !markerIndex[myReport.id]
                && (!status || status === 'all' || status === (myReport.status || 'pending'))) {
                const m = L.marker([parseFloat(myReport.lat), parseFloat(myReport.lng)], { icon: createIcon('#2563EB', true) });
                m.bindPopup(`<div style="font-family:Kanit,sans-serif"><strong>จุดแจ้งเหตุของคุณ</strong><br>${myReport.code || ''}<br><a href="${window.BASE_PATH}/needs/${myReport.id}" style="color:#0F3D6E;font-weight:600">ดูรายละเอียด →</a></div>`);
                markersLayer.addLayer(m);
                markerIndex[myReport.id] = m;
                bounds.push([parseFloat(myReport.lat), parseFloat(myReport.lng)]);
            }
            if (bounds.length > 0 && !focusHandled) {
                map.fitBounds(bounds, { padding: [50, 50], maxZoom: 13 });
            }
        } catch (e) { console.error(e); }
    }

    // Filter chips
    const chipCls = {
        all: 'bg-white/95 text-slate-700',
        pending: 'bg-white/95 text-red-700',
        in_progress: 'bg-white/95 text-amber-700',
        completed: 'bg-white/95 text-emerald-700',
    };
    document.querySelectorAll('.status-filter').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.status-filter').forEach(b => {
                b.className = 'status-filter shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold shadow ' + (chipCls[b.dataset.status] || chipCls.all);
            });
            btn.className = 'status-filter shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold bg-brand-600 text-white shadow';
            loadPoints(btn.dataset.status);
        });
    });

    // Center on a specific need (from banner button or ?focus=)
    let focusHandled = false;
    window.centerOnNeed = function(id) {
        loadPoints('all').then(() => {
            const m = markerIndex[id];
            if (m) {
                map.setView(m.getLatLng(), 16);
                m.openPopup();
            } else if (myReport && myReport.id === id && myReport.lat) {
                map.setView([parseFloat(myReport.lat), parseFloat(myReport.lng)], 16);
            }
        });
    };

    const focusId = new URLSearchParams(location.search).get('focus');
    loadPoints('all').then(() => {
        if (focusId) {
            focusHandled = true;
            setTimeout(() => window.centerOnNeed(parseInt(focusId, 10)), 300);
        }
        // If only my report exists, center on it
        if (myReport && myReport.lat && !focusId) {
            setTimeout(() => {
                const m = markerIndex[myReport.id];
                if (m) map.setView(m.getLatLng(), 15);
                else map.setView([parseFloat(myReport.lat), parseFloat(myReport.lng)], 15);
            }, 400);
        }
    });

    // User location dot
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            L.circleMarker([pos.coords.latitude, pos.coords.longitude], {
                radius: 7, color: '#0F3D6E', fillColor: '#2563EB', fillOpacity: 0.9, weight: 2
            }).addTo(map).bindPopup('ตำแหน่งของคุณ');
        }, () => {}, { timeout: 8000 });
    }
})();
</script>
