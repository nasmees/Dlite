<?php
/** @var array $announcements */
/** @var array $levels */
/** @var array $audiences */
/** @var string $csrf */
/** @var bool $isAdmin */
$levelStyles = [
    'info'     => 'bg-blue-50 text-blue-700',
    'warning'  => 'bg-amber-50 text-amber-700',
    'critical' => 'bg-rose-50 text-rose-700',
];
$levelDots = [
    'info'     => 'bg-blue-500',
    'warning'  => 'bg-amber-500',
    'critical' => 'bg-rose-500',
];
$audienceLabels = ['public' => 'ประชาชน', 'staff' => 'ทีมงาน', 'all' => 'ทุกคน'];
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 space-y-6">
    <div class="flex items-center gap-3">
        <span class="w-10 h-10 rounded-xl bg-brand-600/10 text-brand-700 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
        </span>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">ประกาศ</h1>
            <p class="text-sm text-slate-500">ส่งประกาศถึงผู้ที่เปิดเว็บไซต์อยู่ — แสดงเป็น popup และจะไม่เด้งซ้ำหลังอ่านแล้ว</p>
        </div>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4"/></svg>
            <h2 class="font-semibold text-slate-800">ส่งประกาศใหม่</h2>
        </div>
        <form id="announceForm" class="p-5 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= Helper::e($csrf) ?>">
            <label class="block text-sm font-medium text-slate-700">หัวข้อ
                <input type="text" name="title" id="annTitle" required maxlength="120" class="input-field mt-1.5" placeholder="เช่น ประกาศอพยพพื้นที่ อ.รือเสาะ">
            </label>
            <label class="block text-sm font-medium text-slate-700">ข้อความ
                <textarea name="body" id="annBody" required rows="4" maxlength="1000" class="input-field mt-1.5" placeholder="รายละเอียดประกาศ..."></textarea>
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="block text-sm font-medium text-slate-700">ระดับ
                    <select name="level" id="annLevel" class="input-field mt-1.5">
                        <?php foreach ($levels as $key => $label): ?>
                            <option value="<?= $key ?>"><?= Helper::e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block text-sm font-medium text-slate-700">ส่งถึง
                    <select name="audience" id="annAudience" class="input-field mt-1.5">
                        <?php foreach ($audiences as $key => $label): ?>
                            <option value="<?= $key ?>"><?= Helper::e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" id="annSubmit" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    ส่งประกาศ
                </button>
                <span class="text-xs text-slate-400">ผู้รับจะเห็น popup ภายใน ~45 วินาที</span>
            </div>
        </form>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h2 class="font-semibold text-slate-800">ประวัติประกาศ</h2>
        </div>
        <?php if (empty($announcements)): ?>
            <p class="px-5 py-8 text-center text-sm text-slate-400">ยังไม่มีประกาศ</p>
        <?php else: ?>
            <ul class="divide-y divide-slate-100">
                <?php foreach ($announcements as $a): ?>
                    <li class="px-5 py-4 flex items-start gap-3">
                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 <?= $levelDots[$a['level']] ?? 'bg-slate-300' ?>"></span>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-medium text-slate-800"><?= Helper::e($a['title']) ?></p>
                                <span class="rounded-md px-1.5 py-0.5 text-[11px] font-medium <?= $levelStyles[$a['level']] ?? 'bg-slate-100 text-slate-600' ?>"><?= Helper::e($levels[$a['level']] ?? $a['level']) ?></span>
                                <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] text-slate-600"><?= Helper::e($audienceLabels[$a['audience']] ?? $a['audience']) ?></span>
                            </div>
                            <p class="mt-1 text-sm text-slate-600 whitespace-pre-line"><?= Helper::e($a['body']) ?></p>
                            <p class="mt-1 text-xs text-slate-400">
                                <?= Helper::e($a['author_name'] ?? 'ระบบ') ?> · <?= Helper::timeAgo($a['created_at']) ?>
                                <span class="text-slate-300">(#<?= (int)$a['id'] ?>)</span>
                            </p>
                        </div>
                        <?php if ($isAdmin): ?>
                            <button type="button" class="ann-delete rounded-md px-2.5 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50 shrink-0"
                                data-id="<?= (int)$a['id'] ?>">ลบ</button>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<script>
(function () {
    const csrf = <?= json_encode($csrf) ?>;
    const form = document.getElementById('announceForm');
    const submit = document.getElementById('annSubmit');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        submit.disabled = true;
        const body = new FormData(form);
        try {
            const res = await fetch(window.BASE_PATH + '/admin/announcements', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body
            });
            const data = await res.json();
            if (!res.ok || !data.ok) throw new Error(data.message || 'ส่งไม่สำเร็จ');
            window.Toast?.show(data.message || 'ส่งประกาศแล้ว', 'ok');
            setTimeout(() => window.location.reload(), 600);
        } catch (err) {
            window.Toast?.show(err.message || 'ส่งไม่สำเร็จ', 'err');
            submit.disabled = false;
        }
    });

    document.querySelectorAll('.ann-delete').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('ลบประกาศนี้หรือไม่?')) return;
            const body = new FormData();
            body.append('csrf_token', csrf);
            try {
                const res = await fetch(window.BASE_PATH + '/admin/announcements/' + btn.dataset.id + '/delete', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body
                });
                const data = await res.json();
                if (!res.ok || !data.ok) throw new Error(data.message || 'ลบไม่สำเร็จ');
                window.location.reload();
            } catch (err) {
                window.Toast?.show(err.message || 'ลบไม่สำเร็จ', 'err');
            }
        });
    });
})();
</script>
