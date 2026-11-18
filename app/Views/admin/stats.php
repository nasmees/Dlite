<?php
/** Admin stats dashboard — charts via Chart.js. Expects $stats, $statusLabels, $urgencyLabels */
$statusMeta = [
    'pending'     => ['label' => 'รอรับเรื่อง', 'color' => '#d97706'],
    'accepted'    => ['label' => 'รับเรื่องแล้ว', 'color' => '#2563eb'],
    'in_progress' => ['label' => 'กำลังช่วยเหลือ', 'color' => '#4f46e5'],
    'completed'   => ['label' => 'เสร็จสิ้น', 'color' => '#059669'],
    'cancelled'   => ['label' => 'ยกเลิก', 'color' => '#64748b'],
];
$urgencyMeta = [
    'low'      => ['label' => 'ต่ำ', 'color' => '#94a3b8'],
    'normal'   => ['label' => 'ปกติ', 'color' => '#0ea5e9'],
    'high'     => ['label' => 'สูง', 'color' => '#f97316'],
    'critical' => ['label' => 'วิกฤต', 'color' => '#dc2626'],
];
$activeCount = (int)($stats['byStatus']['accepted'] ?? 0) + (int)($stats['byStatus']['in_progress'] ?? 0);
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<div class="space-y-5 lg:space-y-6">
    <div>
        <h2 class="text-2xl font-semibold text-slate-900 flex items-center gap-2.5">
            <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            สถิติภาพรวม
        </h2>
        <p class="text-sm text-slate-500 mt-1">สรุปเคส ความช่วยเหลือ และแนวโน้มการแจ้ง</p>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">เคสทั้งหมด</p>
            <p class="text-2xl font-bold text-slate-900 mt-1"><?= number_format($stats['total']) ?></p>
            <p class="text-xs text-slate-400 mt-0.5"><?= number_format($stats['people']) ?> คนได้รับผลกระทบ</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">รอรับเรื่อง</p>
            <p class="text-2xl font-bold text-amber-600 mt-1"><?= number_format((int)($stats['byStatus']['pending'] ?? 0)) ?></p>
            <p class="text-xs text-slate-400 mt-0.5">ต้องเร่งประสานงาน</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">กำลังดำเนินการ</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1"><?= number_format($activeCount) ?></p>
            <p class="text-xs text-slate-400 mt-0.5">รับเรื่อง + กำลังช่วย</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">เสร็จสิ้น</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1"><?= number_format((int)($stats['byStatus']['completed'] ?? 0)) ?></p>
            <p class="text-xs text-slate-400 mt-0.5">ปิดเคสแล้ว</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 col-span-2 lg:col-span-1">
            <p class="text-xs text-slate-500">อาสา / ความช่วย</p>
            <p class="text-2xl font-bold text-brand-700 mt-1"><?= number_format($stats['volunteerCount']) ?> <span class="text-sm font-normal text-slate-400">คน</span></p>
            <p class="text-xs text-slate-400 mt-0.5"><?= number_format($stats['helpCount']) ?> รายการเสนอความช่วย</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <section class="bg-white rounded-xl border border-slate-200 p-4">
            <h3 class="font-semibold text-slate-800 mb-3">เคสรายวัน (14 วันล่าสุด)</h3>
            <div class="h-64"><canvas id="chartDaily"></canvas></div>
        </section>
        <section class="bg-white rounded-xl border border-slate-200 p-4">
            <h3 class="font-semibold text-slate-800 mb-3">แยกตามสถานะ</h3>
            <div class="h-64"><canvas id="chartStatus"></canvas></div>
        </section>
        <section class="bg-white rounded-xl border border-slate-200 p-4">
            <h3 class="font-semibold text-slate-800 mb-3">แยกตามประเภทเหตุการณ์</h3>
            <div class="h-64"><canvas id="chartScenario"></canvas></div>
        </section>
        <section class="bg-white rounded-xl border border-slate-200 p-4">
            <h3 class="font-semibold text-slate-800 mb-3">แยกตามจังหวัด (Top 8)</h3>
            <div class="h-64"><canvas id="chartProvince"></canvas></div>
        </section>
        <section class="bg-white rounded-xl border border-slate-200 p-4 lg:col-span-2">
            <h3 class="font-semibold text-slate-800 mb-3">แยกตามความเร่งด่วน</h3>
            <div class="h-48"><canvas id="chartUrgency"></canvas></div>
        </section>
    </div>
</div>

<script>
(function() {
    const stats = <?= json_encode($stats, JSON_UNESCAPED_UNICODE) ?>;
    const statusLabels = <?= json_encode($statusLabels, JSON_UNESCAPED_UNICODE) ?>;
    const urgencyLabels = <?= json_encode($urgencyLabels, JSON_UNESCAPED_UNICODE) ?>;
    const scenarioLabels = <?= json_encode(array_map(fn($k) => Helper::scenarioLabel($k), array_combine(array_column($stats['byScenario'], 's'), array_column($stats['byScenario'], 's'))), JSON_UNESCAPED_UNICODE) ?>;

    Chart.defaults.font.family = "'Kanit', sans-serif";
    Chart.defaults.color = '#64748b';

    // Daily line chart
    new Chart(document.getElementById('chartDaily'), {
        type: 'line',
        data: {
            labels: stats.daily.map(r => r.d.slice(5)),
            datasets: [{
                label: 'จำนวนเคส',
                data: stats.daily.map(r => +r.c),
                borderColor: '#004c97',
                backgroundColor: 'rgba(0,76,151,0.08)',
                fill: true, tension: 0.35, pointRadius: 3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    // Status doughnut
    const statusOrder = ['pending','accepted','in_progress','completed','cancelled'];
    const statusColors = { pending:'#d97706', accepted:'#2563eb', in_progress:'#4f46e5', completed:'#059669', cancelled:'#64748b' };
    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: statusOrder.map(k => statusLabels[k] || k),
            datasets: [{
                data: statusOrder.map(k => +(stats.byStatus[k] || 0)),
                backgroundColor: statusOrder.map(k => statusColors[k]),
                borderWidth: 2, borderColor: '#fff'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom' } } }
    });

    // Scenario bar
    new Chart(document.getElementById('chartScenario'), {
        type: 'bar',
        data: {
            labels: stats.byScenario.map(r => scenarioLabels[r.s] || r.s),
            datasets: [{
                label: 'จำนวนเคส',
                data: stats.byScenario.map(r => +r.c),
                backgroundColor: '#0ea5e9',
                borderRadius: 6
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    // Province horizontal bar
    new Chart(document.getElementById('chartProvince'), {
        type: 'bar',
        data: {
            labels: stats.byProvince.map(r => r.p),
            datasets: [{
                label: 'จำนวนเคส',
                data: stats.byProvince.map(r => +r.c),
                backgroundColor: '#7c3aed',
                borderRadius: 6
            }]
        },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    // Urgency doughnut
    const urgencyOrder = ['low','normal','high','critical'];
    const urgencyColors = { low:'#94a3b8', normal:'#0ea5e9', high:'#f97316', critical:'#dc2626' };
    new Chart(document.getElementById('chartUrgency'), {
        type: 'doughnut',
        data: {
            labels: urgencyOrder.map(k => urgencyLabels[k] || k),
            datasets: [{
                data: urgencyOrder.map(k => +(stats.byUrgency[k] || 0)),
                backgroundColor: urgencyOrder.map(k => urgencyColors[k]),
                borderWidth: 2, borderColor: '#fff'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom' } } }
    });
})();
</script>
