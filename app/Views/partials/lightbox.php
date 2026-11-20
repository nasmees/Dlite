<?php
/**
 * Lightweight image lightbox — intercepts clicks on <a class="lightbox">…</a>
 * and shows the image in a fullscreen popup instead of navigating away.
 * Include once per layout, before </body>.
 */
?>
<div id="lightbox" role="dialog" aria-modal="true">
    <button type="button" id="lightboxClose" aria-label="ปิด" class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/15 hover:bg-white/30 text-white text-2xl leading-none flex items-center justify-center transition">&times;</button>
    <img id="lightboxImg" src="" alt="รูปภาพ" class="max-w-full max-h-[85vh] rounded-xl shadow-2xl object-contain">
</div>
<style>
    #lightbox { display: none; position: fixed; inset: 0; z-index: 5000; background: rgba(0,0,0,.85); align-items: center; justify-content: center; padding: 1rem; }
    #lightbox.open { display: flex; }
    body.lightbox-open { overflow: hidden; }
</style>
<script>
(function () {
    const box = document.getElementById('lightbox');
    const img = document.getElementById('lightboxImg');
    if (!box) return;
    function open(url, alt) {
        img.src = url;
        img.alt = alt || 'รูปภาพ';
        box.classList.add('open');
        document.body.classList.add('lightbox-open');
    }
    function close() {
        box.classList.remove('open');
        document.body.classList.remove('lightbox-open');
        img.src = '';
    }
    document.addEventListener('click', (e) => {
        const a = e.target.closest('a.lightbox');
        if (a) {
            e.preventDefault();
            const im = a.querySelector('img');
            open(a.getAttribute('href'), im ? im.getAttribute('alt') : '');
            return;
        }
        if (e.target === box || e.target.closest('#lightboxClose')) {
            close();
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && box.classList.contains('open')) close();
    });
})();
</script>
