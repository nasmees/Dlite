<div class="-mx-4 -mt-4">
    <div class="bg-white border-b border-slate-200 px-4 py-2 flex items-center gap-2 overflow-x-auto">
        <button type="button" data-status="all" class="status-filter shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-brand-600 text-white shadow">ทั้งหมด</button>
        <button type="button" data-status="pending" class="status-filter shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-red-50 text-red-700 border border-red-200">รอช่วยเหลือ</button>
        <button type="button" data-status="in_progress" class="status-filter shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-amber-50 text-amber-700 border border-amber-200">กำลังช่วย</button>
        <button type="button" data-status="completed" class="status-filter shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">สำเร็จแล้ว</button>
    </div>
    <div id="map" class="map-full w-full"></div>
</div>

<script>
(function() {
    const map = L.map('map').setView([6.8, 101.3], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap', maxZoom: 19
    }).addTo(map);

    let markersLayer = L.layerGroup().addTo(map);
    let markerIndex = {};
    const myReport = (window.getMyReport ? window.getMyReport() : null);

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
                        ${p.name}<br>
                        ผู้ได้รับผลกระทบ ${p.people} คน<br>
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
        pending: 'bg-red-50 text-red-700 border border-red-200',
        in_progress: 'bg-amber-50 text-amber-700 border border-amber-200',
        completed: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    };
    document.querySelectorAll('.status-filter').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.status-filter').forEach(b => {
                b.className = 'status-filter shrink-0 px-4 py-2 rounded-full text-sm font-semibold ' + (inactiveCls[b.dataset.status] || inactiveCls.all);
            });
            btn.className = 'status-filter shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-brand-600 text-white shadow';
            loadPoints(btn.dataset.status);
        });
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
    loadPoints('all').then(() => {
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
