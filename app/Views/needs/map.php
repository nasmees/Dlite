<style>
    /* Mobile-friendly map page */
    .leaflet-popup-content { font-family: 'Kanit', sans-serif; min-width: 200px; font-size: 14px; line-height: 1.5; }
    .leaflet-popup-content a { font-weight: 600; }
    .map-chip {
        flex-shrink: 0; padding: .3rem .65rem; border-radius: 9999px;
        font-size: .75rem; font-weight: 600; white-space: nowrap;
        box-shadow: 0 2px 8px rgba(0,0,0,.18);
    }
    .map-legend {
        position: absolute; bottom: .75rem; left: .75rem; z-index: 1001;
        background: rgba(255,255,255,.95); border-radius: .75rem;
        padding: .5rem .75rem; box-shadow: 0 2px 8px rgba(0,0,0,.15);
        font-size: .8rem; line-height: 1.4; backdrop-filter: blur(4px);
    }
    .map-legend .dot { width: .6rem; height: .6rem; border-radius: 9999px; display: inline-block; margin-right: .35rem; }
    #mapWrap { height: calc(100dvh - 6rem - env(safe-area-inset-bottom, 0px)); }
    body.has-staff #mapWrap { height: calc(100dvh - 8.5rem - env(safe-area-inset-bottom, 0px)); }
    @media (min-width: 640px) {
        #mapWrap { height: calc(100dvh - 7rem - env(safe-area-inset-bottom, 0px)); }
        body.has-staff #mapWrap { height: calc(100dvh - 9.5rem - env(safe-area-inset-bottom, 0px)); }
    }
</style>

<div id="mapWrap" class="relative card !p-0 overflow-hidden">
    <div id="map" class="w-full h-full"></div>

    <!-- Filter toggle + collapsible panel (keeps map unobstructed) -->
    <button type="button" id="filterToggle" class="absolute top-2 right-2 z-[1002] map-chip bg-white/95 text-slate-700 border border-slate-200 inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
        ตัวกรอง
    </button>
    <div id="filterPanel" class="absolute top-11 right-2 z-[1001] hidden bg-white/95 backdrop-blur rounded-xl shadow-lg border border-slate-200 p-2 flex flex-wrap gap-1.5 justify-end max-w-[calc(100%-4.5rem)]">
        <button type="button" data-status="active" class="status-filter map-chip bg-brand-600 text-white">ต้องการความช่วย</button>
        <button type="button" data-status="all" class="status-filter map-chip bg-white/95 text-slate-700 border border-slate-200">ทั้งหมด</button>
        <button type="button" data-status="pending" class="status-filter map-chip bg-red-50 text-red-700 border border-red-200">รอช่วยเหลือ</button>
        <button type="button" data-status="in_progress" class="status-filter map-chip bg-amber-50 text-amber-700 border border-amber-200">กำลังช่วย</button>
        <button type="button" data-status="completed" class="status-filter map-chip bg-emerald-50 text-emerald-700 border border-emerald-200">สำเร็จแล้ว</button>
    </div>

    <!-- Legend -->
    <div class="map-legend">
        <div><span class="dot" style="background:#DC2626"></span>รอช่วยเหลือ</div>
        <div><span class="dot" style="background:#EAB308"></span>กำลังช่วย</div>
        <div><span class="dot" style="background:#16A34A"></span>สำเร็จแล้ว</div>
        <div><span class="dot" style="background:#2563EB"></span>จุดของคุณ</div>
        <div><span style="font-size:.85rem">⛺📦🏥</span> จุดสำคัญ (พักพิง/อพยพ/เสบียง)</div>
    </div>
</div>

<script>
(function() {
    const map = L.map('map').setView([6.8, 101.3], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap', maxZoom: 19
    }).addTo(map);

    let markersLayer = L.layerGroup().addTo(map);
    let poisLayer = L.layerGroup().addTo(map);
    let markerIndex = {};
    const myReport = (window.getMyReport ? window.getMyReport() : null);

    // Important places (shelters, evacuation points…) — independent of case filters
    const escHtml = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    async function loadPois() {
        try {
            const res = await fetch(window.BASE_PATH + '/api/pois');
            const json = await res.json();
            if (!json.ok) return;
            json.pois.forEach(p => {
                const icon = L.divIcon({
                    className: '',
                    html: `<div style="font-size:24px;line-height:1;filter:drop-shadow(0 2px 3px rgba(0,0,0,.4))">${p.emoji}</div>`,
                    iconSize: [28, 28], iconAnchor: [14, 14]
                });
                const m = L.marker([p.lat, p.lng], { icon });
                m.bindPopup(`
                    <div style="font-family:Kanit,sans-serif;min-width:190px">
                        <strong style="font-size:15px">${p.emoji} ${escHtml(p.title)}</strong><br>
                        <span style="color:${p.color};font-weight:600;font-size:12px">${escHtml(p.category_label)}</span>
                        ${p.description ? '<br><span style="font-size:13px;color:#475569">' + escHtml(p.description) + '</span>' : ''}
                        <br><a href="https://www.google.com/maps/dir/?api=1&destination=${p.lat},${p.lng}" target="_blank" rel="noopener" style="display:inline-block;margin-top:6px;font-weight:700;color:#EA580C">🧭 นำทางไปจุดนี้ →</a>
                    </div>
                `);
                poisLayer.addLayer(m);
            });
        } catch (e) { console.error(e); }
    }
    loadPois();

    const statusColors = {
        pending: '#DC2626',
        accepted: '#EAB308',
        in_progress: '#EAB308',
        completed: '#16A34A',
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
                        ${p.name ? p.name + '<br>' : ''}${p.place ? p.place + '<br>' : ''}
                        ผู้ได้รับผลกระทบ ${p.people} คน<br>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${p.lat},${p.lng}" target="_blank" rel="noopener" style="color:#EA580C;font-weight:600">🧭 นำทาง →</a><br>
                        <a href="${window.BASE_PATH}/needs/${p.id}" style="color:#0F3D6E;font-weight:600">ดูรายละเอียด →</a>
                    </div>
                `);
                markersLayer.addLayer(m);
                markerIndex[p.id] = m;
                bounds.push([p.lat, p.lng]);
            });
            if (myReport && myReport.lat && myReport.lng && !markerIndex[myReport.id]
                && (!status || status === 'all' || status === (myReport.status || 'pending'))) {
                const m = L.marker([parseFloat(myReport.lat), parseFloat(myReport.lng)], { icon: createIcon('#2563EB', true) });
                m.bindPopup(`<div style="font-family:Kanit,sans-serif"><strong>จุดแจ้งเหตุของคุณ</strong><br>${myReport.code || ''}<br><a href="${window.BASE_PATH}/needs/${myReport.id}" style="color:#0F3D6E;font-weight:600">ดูรายละเอียด →</a></div>`);
                markersLayer.addLayer(m);
                markerIndex[myReport.id] = m;
                bounds.push([parseFloat(myReport.lat), parseFloat(myReport.lng)]);
            }
            if (bounds.length > 0 && !focusHandled) {
                map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
            }
        } catch (e) { console.error(e); }
    }

    const inactiveCls = {
        all: 'bg-white/95 text-slate-700 border border-slate-200',
        active: 'bg-white/95 text-brand-700 border border-brand-200',
        pending: 'bg-red-50 text-red-700 border border-red-200',
        in_progress: 'bg-amber-50 text-amber-700 border border-amber-200',
        completed: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    };
    document.querySelectorAll('.status-filter').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.status-filter').forEach(b => {
                b.className = 'status-filter map-chip ' + (inactiveCls[b.dataset.status] || inactiveCls.all);
            });
            btn.className = 'status-filter map-chip bg-brand-600 text-white';
            loadPoints(btn.dataset.status);
        });
    });

    // Collapsible filter panel — keeps the map unobstructed
    const filterToggle = document.getElementById('filterToggle');
    const filterPanel = document.getElementById('filterPanel');
    filterToggle?.addEventListener('click', (e) => {
        e.stopPropagation();
        filterPanel.classList.toggle('hidden');
    });
    filterPanel?.addEventListener('click', (e) => e.stopPropagation());
    document.addEventListener('click', (e) => {
        if (filterPanel && !filterPanel.classList.contains('hidden')
            && !filterPanel.contains(e.target) && e.target !== filterToggle) {
            filterPanel.classList.add('hidden');
        }
    });

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
    loadPoints('active').then(() => {
        if (focusId) {
            focusHandled = true;
            setTimeout(() => window.centerOnNeed(parseInt(focusId, 10)), 300);
        }
    });

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            L.circleMarker([pos.coords.latitude, pos.coords.longitude], {
                radius: 7, color: '#0F3D6E', fillColor: '#2563EB', fillOpacity: 0.9, weight: 2
            }).addTo(map).bindPopup('ตำแหน่งของคุณ');
        }, () => {}, { timeout: 8000 });
    }
})();
</script>
