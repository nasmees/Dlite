/* Dlite Demo — shared mock data + UI helpers. No backend; everything is hardcoded.
   Relief mode overrides: load this file, then assets/relief.js which replaces
   CASES / SCENARIOS / NEED_TYPES / SCENARIO_LIST / CTA on the DEMO object. */
window.DEMO = (function () {

  // ---------- Mock cases (disaster mode) ----------
  const CASES = [
    {
      id: 1, code: 'DL-2601', scenario: 'flood', urgency: 'critical', status: 'pending',
      title: 'น้ำท่วมหนัก ต.เขาตูม', place: 'ต.เขาตูม · นราธิวาส', people: 10,
      types: ['food', 'water', 'shelter'], lat: 6.42, lng: 101.82, time: '12 นาทีที่แล้ว',
      desc: 'น้ำท่วมสูงประมาณ 1 เมตร มีผู้ติดอยู่ในบ้าน 2 หลัง',
      updates: [
        { t: '12 นาทีที่แล้ว', txt: 'ประชาชนแจ้งเหตุผ่านระบบ', done: true },
        { t: '—', txt: 'รอทีมประสานงานมอบหมายอาสา', done: false },
      ]
    },
    {
      id: 2, code: 'DL-2602', scenario: 'medical', urgency: 'high', status: 'in_progress',
      title: 'ต้องการรถพยาบาล', place: 'เมืองนราธิวาส', people: 1,
      types: ['medicine'], lat: 6.43, lng: 101.82, time: '34 นาทีที่แล้ว',
      desc: 'ผู้ป่วยสูงอายุมีอาการแน่นหน้าอก',
      updates: [
        { t: '34 นาทีที่แล้ว', txt: 'ประชาชนแจ้งเหตุผ่านระบบ', done: true },
        { t: '20 นาทีที่แล้ว', txt: 'ทีมประสานงานมอบหมายให้อาสา', done: true },
        { t: '8 นาทีที่แล้ว', txt: 'อาสากำลังเดินทางไปรับผู้ป่วย', done: true },
        { t: '—', txt: 'รอส่งต่อโรงพยาบาล', done: false },
      ]
    },
    {
      id: 3, code: 'DL-2603', scenario: 'supplies', urgency: 'normal', status: 'pending',
      title: 'ขาดแคลนเสบียง', place: 'ต.สะเตงนอก · นราธิวาส', people: 25,
      types: ['food', 'water'], lat: 6.40, lng: 101.85, time: '1 ชั่วโมงที่แล้ว',
      desc: 'ครัวเรือน 25 หลังขาดอาหารและน้ำดื่ม',
      updates: [
        { t: '1 ชั่วโมงที่แล้ว', txt: 'ประชาชนแจ้งเหตุผ่านระบบ', done: true },
        { t: '—', txt: 'รอทีมประสานงานมอบหมายอาสา', done: false },
      ]
    },
    {
      id: 4, code: 'DL-2604', scenario: 'evac', urgency: 'critical', status: 'accepted',
      title: 'ต้องอพยพ / บ้านเสียหาย', place: 'ต.บางพลี · นราธิวาส', people: 6,
      types: ['shelter', 'evacuation'], lat: 6.46, lng: 101.79, time: '2 ชั่วโมงที่แล้ว',
      desc: 'บ้านทรุด 1 หลัง ครอบครัว 6 คนต้องการที่พักชั่วคราว',
      updates: [
        { t: '2 ชั่วโมงที่แล้ว', txt: 'ประชาชนแจ้งเหตุผ่านระบบ', done: true },
        { t: '1 ชั่วโมงที่แล้ว', txt: 'ทีมประสานงานมอบหมายให้อาสา', done: true },
        { t: '—', txt: 'อาสากำลังเตรียมอพยพ', done: false },
      ]
    },
    {
      id: 5, code: 'DL-2605', scenario: 'sos', urgency: 'critical', status: 'in_progress',
      title: 'เหตุฉุกเฉิน SOS', place: 'ตำบลแถวใกล้เคียง', people: 3,
      types: ['medicine', 'evacuation'], lat: 6.38, lng: 101.80, time: '3 ชั่วโมงที่แล้ว',
      desc: 'มีผู้ได้รับบาดเจ็บต้องการการช่วยเหลือทันที',
      updates: [
        { t: '3 ชั่วโมงที่แล้ว', txt: 'ประชาชนแจ้งเหตุผ่านระบบ', done: true },
        { t: '2 ชั่วโมงที่แล้ว', txt: 'ทีมประสานงานมอบหมายให้อาสา', done: true },
        { t: '1 ชั่วโมงที่แล้ว', txt: 'อาสาถึงจุดเกิดเหตุ กำลังช่วยเหลือ', done: true },
        { t: '—', txt: 'รอส่งต่อโรงพยาบาล', done: false },
      ]
    },
    {
      id: 6, code: 'DL-2600', scenario: 'supplies', urgency: 'normal', status: 'completed',
      title: 'ขาดแคลนอาหาร / น้ำ', place: 'ต.สะเตงนอก · นราธิวาส', people: 12,
      types: ['food', 'water'], lat: 6.41, lng: 101.86, time: 'เมื่อวาน',
      desc: 'จุดแจกจ่ายอาหารและน้ำดื่ม',
      updates: [
        { t: 'เมื่อวาน', txt: 'ประชาชนแจ้งเหตุผ่านระบบ', done: true },
        { t: 'เมื่อวาน', txt: 'ทีมประสานงานมอบหมายให้อาสา', done: true },
        { t: 'เมื่อวาน', txt: 'ช่วยเหลือสำเร็จ — ปิดเคส', done: true },
      ]
    },
  ];

  // ---------- POIs (important places) ----------
  const POIS = [
    { emoji: '🏥', title: 'โรงพยาบาลศูนย์', cat: 'จุดพยาบาล', lat: 6.435, lng: 101.815 },
    { emoji: '⛺', title: 'ศูนย์พักพิง ต.เขาตูม', cat: 'จุดพักพิง', lat: 6.425, lng: 101.828 },
    { emoji: '📦', title: 'จุดแจกจ่ายเสบียง', cat: 'จุดแจกจ่าย', lat: 6.398, lng: 101.852 },
    { emoji: '🚩', title: 'จุดอพยพ ต.สะเตงนอก', cat: 'จุดอพยพ', lat: 6.405, lng: 101.845 },
    { emoji: '🤝', title: 'จุดนัดพบทีมอาสา', cat: 'จุดนัดพบ', lat: 6.418, lng: 101.808 },
  ];

  // ---------- Report-form scenario cards (mirrors Helper::scenarioCards, disaster mode) ----------
  const SCENARIO_LIST = [
    { key: 'flood',    label: 'น้ำท่วม / ถนนท่วม',           sub: 'ขออาหาร น้ำ ที่พัก',        types: ['food','water','shelter'],     urgency: 'high',     sos: false },
    { key: 'evac',     label: 'ต้องอพยพ / บ้านเสียหาย',      sub: 'เร่งด่วน ต้องการที่พัก',      types: ['shelter','evacuation'],       urgency: 'critical', sos: false },
    { key: 'sos',      label: 'เหตุฉุกเฉิน SOS',              sub: 'อันตรายถึงชีวิต ต้องการทันที', types: ['medicine','evacuation'],      urgency: 'critical', sos: true },
    { key: 'medical',  label: 'เจ็บป่วย / ได้รับบาดเจ็บ',     sub: 'ต้องการยาและการรักษา',       types: ['medicine'],                  urgency: 'high',     sos: false },
    { key: 'supplies', label: 'ขาดแคลนอาหาร / น้ำ',          sub: 'แจ้งจุดแจกจ่าย',             types: ['food','water'],              urgency: 'normal',   sos: false },
    { key: 'other',    label: 'ความเสียหายอื่นๆ',             sub: 'เล่าเหตุการณ์โดยย่อ',         types: ['other'],                     urgency: 'normal',   sos: false },
  ];

  // ---------- Label maps (mirroring Helper.php) ----------
  const STATUS = {
    pending:      { label: 'รอรับเรื่อง',     cls: 'st-pending' },
    accepted:     { label: 'รับเรื่องแล้ว',   cls: 'st-accepted' },
    in_progress:  { label: 'กำลังช่วยเหลือ', cls: 'st-in_progress' },
    completed:    { label: 'เสร็จสิ้น',       cls: 'st-completed' },
    cancelled:    { label: 'ยกเลิก',         cls: 'st-cancelled' },
  };
  const URGENCY = { low: 'ต่ำ', normal: 'ปกติ', high: 'สูง', critical: 'วิกฤต' };
  const URGENCY_CLS = { low: 'st-completed', normal: 'st-accepted', high: 'st-in_progress', critical: 'st-pending' };
  const SCENARIOS = {
    flood:    { label: 'น้ำท่วม / ถนนท่วม',          icon: 'fa-water',                 chip: '#e0f2fe', cicon: '#0369a1' },
    evac:     { label: 'ต้องอพยพ / บ้านเสียหาย',      icon: 'fa-house',                 chip: '#fff7ed', cicon: '#d97706' },
    sos:      { label: 'เหตุฉุกเฉิน SOS',              icon: 'fa-triangle-exclamation', chip: '#fef2f2', cicon: '#dc2626' },
    medical:  { label: 'เจ็บป่วย / บาดเจ็บ',          icon: 'fa-heart-pulse',           chip: '#fdf2f8', cicon: '#e11d48' },
    supplies: { label: 'ขาดแคลนอาหาร / น้ำ',         icon: 'fa-box-open',              chip: '#fffbeb', cicon: '#b45309' },
    other:    { label: 'อื่นๆ',                       icon: 'fa-ellipsis',              chip: '#f1f5f9', cicon: '#64748b' },
  };
  const NEED_TYPES = {
    food: 'อาหาร', water: 'น้ำดื่ม', medicine: 'ยา/การรักษา', shelter: 'ที่พัก',
    evacuation: 'การอพยพ', clothing: 'เสื้อผ้า', other: 'อื่นๆ',
  };
  const STATUS_COLOR = { pending: '#DC2626', accepted: '#2563EB', in_progress: '#EAB308', completed: '#16A34A', cancelled: '#94A3B8' };

  // Primary CTA per mode (disaster default)
  const CTA = { label: 'แจ้งเหตุ', icon: 'fa-person-drowning', bg: '#dc2626', fg: '#dc2626' };
  // Mode switch link shown in the demo banner
  const MODE_LINK = { href: 'relief/index.html', label: 'โหมดบรรเทา →' };
  const BANNER_HOME = '../';

  // ---------- Helpers (read through `api` so relief.js overrides take effect) ----------
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  const api = {};
  api.CASES = CASES; api.POIS = POIS; api.STATUS = STATUS; api.URGENCY = URGENCY;
  api.SCENARIOS = SCENARIOS; api.NEED_TYPES = NEED_TYPES; api.SCENARIO_LIST = SCENARIO_LIST;
  api.STATUS_COLOR = STATUS_COLOR; api.CTA = CTA; api.MODE_LINK = MODE_LINK; api.BANNER_HOME = BANNER_HOME;

  api.caseByCode = code => api.CASES.find(c => c.code.toLowerCase() === String(code).trim().toLowerCase()) || null;
  api.activeCases = () => api.CASES.filter(c => c.status !== 'completed' && c.status !== 'cancelled');

  api.statusPill = status => {
    const s = api.STATUS[status] || api.STATUS.pending;
    return `<span class="pill ${s.cls}"><span class="dot" style="background:currentColor"></span>${s.label}</span>`;
  };
  api.urgencyPill = u => `<span class="pill ${URGENCY_CLS[u] || 'st-accepted'}">${URGENCY[u] || u}</span>`;

  api.scenarioChip = (key, size) => {
    const s = api.SCENARIOS[key] || api.SCENARIOS.other;
    const sz = size || 42;
    return `<div class="case-ic" style="background:${s.chip};color:${s.cicon};width:${sz}px;height:${sz}px;font-size:${sz * 0.42}px"><i class="fa-solid ${s.icon}"></i></div>`;
  };

  api.caseCard = (c, opts = {}) => {
    const meta = [c.place, c.people > 0 ? `~${c.people} คน` : '', ...c.types.slice(0, 2).map(t => api.NEED_TYPES[t] || t)].filter(Boolean);
    return `<a class="card case-card" href="${opts.href || 'track.html?code=' + c.code}">
      ${api.scenarioChip(c.scenario)}
      <div class="case-t">
        <div style="display:flex;justify-content:space-between;gap:8px;align-items:center">
          <b>${esc(c.title)}</b>
          ${api.urgencyPill(c.urgency)}
        </div>
        <small>${esc(meta.join(' · '))} · ${esc(c.time)}</small>
      </div>
      ${api.statusPill(c.status)}
    </a>`;
  };

  api.caseDetail = c => {
    const s = api.SCENARIOS[c.scenario] || api.SCENARIOS.other;
    const timeline = c.updates.map(u => `
      <div class="tl-item ${u.done ? 'done' : ''}">
        <b>${esc(u.txt)}</b><span>${esc(u.t)}</span>
      </div>`).join('');
    return `
      <div class="card" style="padding:18px">
        <div style="display:flex;gap:12px;align-items:flex-start">
          <div class="case-ic" style="background:${s.chip};color:${s.cicon};width:48px;height:48px;font-size:20px"><i class="fa-solid ${s.icon}"></i></div>
          <div style="flex:1;min-width:0">
            <b style="font-size:16px">${esc(c.title)}</b>
            <div class="muted" style="margin-top:2px">${esc(c.code)} · ${esc(c.place)} · ${esc(c.time)}</div>
          </div>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:12px">
          ${api.statusPill(c.status)} ${api.urgencyPill(c.urgency)}
          ${c.types.map(t => `<span class="pill st-accepted">${api.NEED_TYPES[t] || t}</span>`).join('')}
        </div>
        ${c.desc ? `<p class="muted" style="margin-top:12px;line-height:1.6">${esc(c.desc)}</p>` : ''}
        <div style="display:flex;gap:8px;margin-top:14px">
          <a class="btn btn-ghost" style="flex:1" target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination=${c.lat},${c.lng}"><i class="fa-solid fa-diamond-turn-right"></i> นำทาง</a>
          <span class="pill st-accepted" style="align-self:center">~${c.people} คนได้รับผลกระทบ</span>
        </div>
      </div>
      <div class="sec-title"><i class="fa-solid fa-clock-rotate-left"></i> ความคืบหน้า</div>
      <div class="card" style="padding:18px"><div class="timeline">${timeline}</div></div>`;
  };

  // ---------- Bottom nav (5 slots, center CTA follows D.CTA) ----------
  api.bottomNav = active => {
    const items = [
      { href: 'index.html',      icon: 'fa-house',            label: 'หน้าแรก',  key: 'home' },
      { href: 'index.html#map',  icon: 'fa-location-dot',     label: 'แผนที่',    key: 'map' },
      { cta: true,               icon: api.CTA.icon,         label: api.CTA.label, key: 'report' },
      { href: 'track.html',      icon: 'fa-magnifying-glass', label: 'ติดตาม',   key: 'track' },
      { href: 'login.html',      icon: 'fa-right-to-bracket', label: 'เข้าสู่ระบบ', key: 'login' },
    ];
    return `<nav class="bottomnav"><div class="inner">
      ${items.map(it => it.cta
        ? `<a class="nav-cta" href="report.html"><span class="fab" style="background:${api.CTA.bg};box-shadow:0 0 0 4px #fff,0 4px 14px ${api.CTA.bg}66"><i class="fa-solid ${it.icon}"></i></span><span style="color:${api.CTA.fg}">${it.label}</span></a>`
        : `<a class="navitem ${active === it.key ? 'active' : ''}" href="${it.href}"><i class="fa-solid ${it.icon}"></i><span>${it.label}</span></a>`
      ).join('')}
    </div></nav>`;
  };

  api.demoBanner = () => `<div class="demo-bar"><i class="fa-solid fa-flask"></i> DEMO — ข้อมูลจำลอง ลองกดเล่นได้เลย ไม่มีการบันทึกจริง
    ${api.MODE_LINK ? `<a href="${api.MODE_LINK.href}">${api.MODE_LINK.label}</a> · ` : ''}
    <a href="${api.BANNER_HOME}">← กลับหน้าแรก</a></div>`;

  api.toast = (msg, ms) => {
    let t = document.getElementById('toast');
    if (!t) { t = document.createElement('div'); t.id = 'toast'; document.body.appendChild(t); }
    t.textContent = msg; t.style.display = 'block';
    clearTimeout(t._h); t._h = setTimeout(() => t.style.display = 'none', ms || 2600);
  };

  api.esc = esc;
  return api;
})();
