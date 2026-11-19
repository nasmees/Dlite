/* Dlite Demo — RELIEF MODE overrides.
   Load AFTER assets/demo.js; replaces the disaster-mode data on window.DEMO.
   Mirrors Helper.php SCENARIOS_RELIEF / NEED_TYPES_RELIEF / scenarioCards(). */
(function () {
  const D = window.DEMO;
  if (!D) return;

  D.CASES = [
    {
      id: 11, code: 'DL-2701', scenario: 'health', urgency: 'normal', status: 'pending',
      title: 'ลงทะเบียนตรวจสุขภาพเคลื่อนที่', place: 'ต.เขาตูม · นราธิวาส', people: 40,
      types: ['medical', 'counseling'], lat: 6.42, lng: 101.82, time: '18 นาทีที่แล้ว',
      desc: 'ชุมชน 40 คนขอให้ทีมแพทย์ลงพื้นที่ตรวจสุขภาพประจำเดือน',
      updates: [
        { t: '18 นาทีที่แล้ว', txt: 'ประชาชนส่งคำขอผ่านระบบ', done: true },
        { t: '—', txt: 'รอทีมประสานงานมอบหมายอาสา', done: false },
      ]
    },
    {
      id: 12, code: 'DL-2702', scenario: 'aid', urgency: 'high', status: 'pending',
      title: 'ขอทุนการศึกษาบุตร', place: 'ต.สะเตงนอก · นราธิวาส', people: 1,
      types: ['financial'], lat: 6.40, lng: 101.85, time: '45 นาทีที่แล้ว',
      desc: 'ครอบครัวมีบุตร 2 คนกำลังเรียน ต้องการทุนการศึกษา',
      updates: [
        { t: '45 นาทีที่แล้ว', txt: 'ประชาชนส่งคำขอผ่านระบบ', done: true },
        { t: '—', txt: 'รอทีมประสานงานพิจารณา', done: false },
      ]
    },
    {
      id: 13, code: 'DL-2703', scenario: 'training', urgency: 'normal', status: 'in_progress',
      title: 'อบรมช่างไฟฟ้าเบื้องต้น', place: 'ศูนย์อบรมเมืองนราธิวาส', people: 15,
      types: ['training'], lat: 6.43, lng: 101.82, time: '2 ชั่วโมงที่แล้ว',
      desc: 'เปิดรับสมัครชาวบ้าน 15 คน เรียน 3 วัน',
      updates: [
        { t: '2 ชั่วโมงที่แล้ว', txt: 'ประชาชนส่งคำขอผ่านระบบ', done: true },
        { t: '1 ชั่วโมงที่แล้ว', txt: 'ทีมประสานงานมอบหมายให้อาสา', done: true },
        { t: '30 นาทีที่แล้ว', txt: 'ยืนยันสถานที่และวิทยากรแล้ว', done: true },
        { t: '—', txt: 'รอวันเปิดอบรม', done: false },
      ]
    },
    {
      id: 14, code: 'DL-2704', scenario: 'counsel', urgency: 'normal', status: 'accepted',
      title: 'ต้องการคำปรึกษากฎหมาย', place: 'เมืองนราธิวาส', people: 1,
      types: ['counseling'], lat: 6.435, lng: 101.815, time: '3 ชั่วโมงที่แล้ว',
      desc: 'ขอคำปรึกษาเรื่องสิทธิที่ดินทำกิน',
      updates: [
        { t: '3 ชั่วโมงที่แล้ว', txt: 'ประชาชนส่งคำขอผ่านระบบ', done: true },
        { t: '2 ชั่วโมงที่แล้ว', txt: 'ทีมประสานงานมอบหมายให้อาสา', done: true },
        { t: '—', txt: 'อาสานัดหมายวันให้คำปรึกษา', done: false },
      ]
    },
    {
      id: 15, code: 'DL-2705', scenario: 'supplies', urgency: 'normal', status: 'pending',
      title: 'ขาดแคลนเสบียง / ของใช้', place: 'ต.บางพลี · นราธิวาส', people: 18,
      types: ['supplies'], lat: 6.46, lng: 101.79, time: '4 ชั่วโมงที่แล้ว',
      desc: 'ครัวเรือน 18 หลังขาดของใช้จำเป็นประจำเดือน',
      updates: [
        { t: '4 ชั่วโมงที่แล้ว', txt: 'ประชาชนส่งคำขอผ่านระบบ', done: true },
        { t: '—', txt: 'รอทีมประสานงานมอบหมายอาสา', done: false },
      ]
    },
    {
      id: 16, code: 'DL-2700', scenario: 'training', urgency: 'normal', status: 'completed',
      title: 'อบรมเย็บปักเบื้องต้น', place: 'ต.สะเตงนอก · นราธิวาส', people: 12,
      types: ['training'], lat: 6.405, lng: 101.845, time: 'เมื่อวาน',
      desc: 'จบคอร์สเรียบร้อย ผู้เรียน 12 คน',
      updates: [
        { t: 'เมื่อวาน', txt: 'ประชาชนส่งคำขอผ่านระบบ', done: true },
        { t: 'เมื่อวาน', txt: 'ทีมประสานงานมอบหมายให้อาสา', done: true },
        { t: 'เมื่อวาน', txt: 'ดำเนินการเสร็จสิ้น — ปิดเคส', done: true },
      ]
    },
  ];

  D.SCENARIOS = {
    health:   { label: 'ตรวจสุขภาพ / พบแพทย์', icon: 'fa-stethoscope',           chip: '#ecfdf5', cicon: '#059669' },
    counsel:  { label: 'ต้องการคำปรึกษา',          icon: 'fa-comments',              chip: '#f5f3ff', cicon: '#7c3aed' },
    training: { label: 'ฝึกอาชีพ / หางาน',         icon: 'fa-graduation-cap',         chip: '#e0f2fe', cicon: '#0369a1' },
    aid:      { label: 'ขอเงินช่วยเหลือ / สงเคราะห์', icon: 'fa-hand-holding-dollar', chip: '#fffbeb', cicon: '#d97706' },
    supplies: { label: 'ขาดแคลนเสบียง / ของใช้',   icon: 'fa-box-open',               chip: '#fff7ed', cicon: '#ea580c' },
    other:    { label: 'เรื่องอื่นๆ',               icon: 'fa-ellipsis',               chip: '#f1f5f9', cicon: '#64748b' },
  };

  D.NEED_TYPES = {
    medical: 'บริการสุขภาพ / พบแพทย์',
    counseling: 'ให้คำปรึกษา',
    training: 'ฝึกอาชีพ / เรียนรู้ทักษะ',
    financial: 'เงินช่วยเหลือ / สงเคราะห์',
    supplies: 'เสบียง / ของใช้จำเป็น',
    other: 'อื่นๆ',
  };

  D.SCENARIO_LIST = [
    { key: 'health',   label: 'ตรวจสุขภาพ / พบแพทย์', sub: 'ลงทะเบียนรับบริการสุขภาพ', types: ['medical', 'counseling'], urgency: 'normal',   sos: false },
    { key: 'counsel',  label: 'ต้องการคำปรึกษา',        sub: 'จิตใจ กฎหมาย การเงิน',      types: ['counseling'],             urgency: 'normal',   sos: false },
    { key: 'training', label: 'ฝึกอาชีพ / หางาน',       sub: 'เรียนรู้ทักษะเพื่อมีรายได้',   types: ['training'],               urgency: 'normal',   sos: false },
    { key: 'aid',      label: 'ขอเงินช่วยเหลือ',         sub: 'สงเคราะห์ / เยียวยา',         types: ['financial'],              urgency: 'high',     sos: false },
    { key: 'supplies', label: 'ขาดแคลนเสบียง / ของใช้', sub: 'อาหาร น้ำ ของใช้จำเป็น',     types: ['supplies'],               urgency: 'normal',   sos: false },
    { key: 'other',    label: 'เรื่องอื่นๆ',             sub: 'เล่าเหตุการณ์โดยย่อ',         types: ['other'],                  urgency: 'normal',   sos: false },
  ];

  // relief CTA: green "ส่งคำขอ" (matches the real app's relief mode)
  D.CTA = { label: 'ส่งคำขอ', icon: 'fa-person', bg: '#059669', fg: '#059669' };
  D.MODE_LINK = { href: '../index.html', label: 'โหมดภัยพิบัติ →' };
  D.BANNER_HOME = '../../';
})();
