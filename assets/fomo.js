(function () {
    const cfg = window.blbFomoConfig || {};
    const STYLE       = cfg.style || 'popup';
    const POSITION    = cfg.position || 'bottom-left';
    const INTERVAL    = Number(cfg.interval) || 8000;
    const DURATION    = Number(cfg.duration) || 5000;
    const MAX         = Number(cfg.max_per_page) || 10;
    const DELAY_FIRST = Number(cfg.delay_first) || 3000;
    const NOWA        = Number(cfg.nowa) || '6281234567890';
    // const SOUND       = cfg.sound_enabled !== false; // default: aktif

    let bookings  = [];
    let shown     = 0;
    let popupEl   = null;
    let barEl     = null;
    let hideTimer = null;
    let seqTimer  = null;

    /* ---------- Fetch data ---------- */
    function fetchData() {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', cfg.ajaxurl || '/wp-admin/admin-ajax.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.success && res.data.length) {
                    bookings = res.data;
                    setTimeout(startLoop, DELAY_FIRST);
                }
            } catch(e) {}
        };
        xhr.send('action=blb_fomo_data');
    }

    /* ---------- Sound ---------- */
    function playSound() {
        // if (!SOUND) return;
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();

            // Nada pertama (ding tinggi)
            const osc1  = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(880, ctx.currentTime);
            gain1.gain.setValueAtTime(0.3, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
            osc1.start(ctx.currentTime);
            osc1.stop(ctx.currentTime + 0.4);

            // Nada kedua (ding rendah, 0.1s setelah pertama)
            const osc2  = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(660, ctx.currentTime + 0.1);
            gain2.gain.setValueAtTime(0.2, ctx.currentTime + 0.1);
            gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
            osc2.start(ctx.currentTime + 0.1);
            osc2.stop(ctx.currentTime + 0.5);

        } catch(e) {
            console.warn('Sound error:', e);
        }
    }

    /* ---------- Loop ---------- */
    function startLoop() {
        if (!bookings.length || shown >= MAX) return;
        showNext();
    }

    function showNext() {
        if (!bookings.length) return;
        if (shown >= MAX) return;

        const item = bookings[shown % bookings.length];
        shown++;

        try {
            if (STYLE === 'popup'   || STYLE === 'both') showPopup(item);
            if (STYLE === 'bar'     || STYLE === 'both') showBar(item);
            if (STYLE === 'bar-atas') showBarAtas(item);

            playSound(); // ← bunyi setiap notifikasi muncul
        } catch(e) {
            console.error('error saat show:', e);
        }

        clearTimeout(seqTimer);
        seqTimer = setTimeout(showNext, DURATION + INTERVAL);
    }

    /* ---------- Popup ---------- */
    function createPopup() {
        const el = document.createElement('div');
        el.className = 'blb-fomo-popup blb-pos-' + POSITION;
        el.innerHTML = `
            <div class="blb-fomo-icon">🧺</div>
            <div class="blb-fomo-body">
                <a class="blb-nowa" target="_blank">
                    <div class="blb-fomo-title"></div>
                    <div class="blb-fomo-sub"></div>
                    <div class="blb-fomo-meta"></div>
                </a>
            </div>
            <button class="blb-fomo-close" aria-label="Tutup">×</button>
            <div class="blb-fomo-progress"></div>`;
        el.querySelector('.blb-fomo-close').addEventListener('click', () => hidePopup());
        document.body.appendChild(el);
        return el;
    }

    function showPopup(item) {
        if (!popupEl) popupEl = createPopup();

        const name = firstName(item.name);
        const loc  = item.location ? ' · ' + truncate(item.location, 22) : '';
        const no_wa = 'https://wa.me/' + NOWA;

        popupEl.querySelector('.blb-fomo-title').textContent = name + ' baru saja booking';
        popupEl.querySelector('.blb-fomo-sub').textContent   = item.service + loc;
        popupEl.querySelector('.blb-fomo-meta').textContent  = '⏰ ' + (item.pickup_time ? 'Pickup ' + item.pickup_time + ' · ' : '') + item.ago;
        popupEl.querySelector('.blb-nowa').setAttribute('href', no_wa);

        const prog = popupEl.querySelector('.blb-fomo-progress');
        prog.style.transition = 'none';
        prog.style.width = '100%';

        popupEl.classList.remove('blb-hide');
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                popupEl.classList.add('blb-show');
                prog.style.transition = 'width ' + (DURATION / 1000) + 's linear';
                prog.style.width = '0%';
            });
        });

        clearTimeout(hideTimer);
        hideTimer = setTimeout(() => hidePopup(), DURATION);
    }

    function hidePopup() {
        if (!popupEl) return;
        popupEl.classList.remove('blb-show');
        popupEl.classList.add('blb-hide');
    }

    /* ---------- Bar ---------- */
    function createBar() {
        const el = document.createElement('div');
        el.className = 'blb-fomo-bar';
        el.innerHTML = `<a href="https://wa.me/${NOWA}" target="_blank" style="color: inherit;"><span style="font-size:18px;">🧺 </span><span class="blb-fomo-bar-text" style="font-size:18px;"></span></a><button class="blb-fomo-close" aria-label="Tutup">×</button>`;
        el.querySelector('.blb-fomo-close').addEventListener('click', () => hideBar());
        document.body.appendChild(el);
        return el;
    }

    /* ---------- Bar Atas ---------- */
    function createBarAtas() {
        const el = document.createElement('div');
        el.className = 'blb-fomo-bar blb-fomo-bar-atas';
        el.innerHTML = `<a href="https://wa.me/${NOWA}" target="_blank" style="color: inherit;"><span style="font-size:18px;">🧺 </span><span class="blb-fomo-bar-text" style="font-size:18px;"></span></a><button class="blb-fomo-close" aria-label="Tutup">×</button>`;
        el.querySelector('.blb-fomo-close').addEventListener('click', () => hideBarAtas());
        document.body.appendChild(el);
        return el;
    }

    function showBar(item) {
        if (!barEl) barEl = createBar();

        const name = firstName(item.name);
        const loc  = item.location ? ' dari <strong>' + esc(truncate(item.location, 20)) + '</strong>' : '';
        const time = item.pickup_time ? ' pickup jam <strong>' + esc(item.pickup_time) + '</strong>' : '';

        barEl.querySelector('.blb-fomo-bar-text').innerHTML =
            '<strong>' + esc(name) + '</strong> baru booking <strong>' + esc(item.service) + '</strong>' +
            loc + time + ' — ' + esc(item.ago);

        barEl.classList.remove('blb-hide');
        requestAnimationFrame(() => requestAnimationFrame(() => barEl.classList.add('blb-show')));
        setTimeout(() => hideBar(), DURATION);
    }

    function hideBar() {
        if (!barEl) return;
        barEl.classList.remove('blb-show');
        barEl.classList.add('blb-hide');
    }

    let barAtasEl = null;

    function showBarAtas(item) {
        if (!barAtasEl) barAtasEl = createBarAtas();

        const name = firstName(item.name);
        const loc  = item.location ? ' dari <strong>' + esc(truncate(item.location, 20)) + '</strong>' : '';
        const time = item.pickup_time ? ' pickup jam <strong>' + esc(item.pickup_time) + '</strong>' : '';

        barAtasEl.querySelector('.blb-fomo-bar-text').innerHTML =
            '<strong>' + esc(name) + '</strong> baru booking <strong>' + esc(item.service) + '</strong>' +
            loc + time + ' — ' + esc(item.ago);

        barAtasEl.classList.remove('blb-hide');
        requestAnimationFrame(() => requestAnimationFrame(() => barAtasEl.classList.add('blb-show')));
        setTimeout(() => hideBarAtas(), DURATION);
    }

    function hideBarAtas() {
        if (!barAtasEl) return;
        barAtasEl.classList.remove('blb-show');
        barAtasEl.classList.add('blb-hide');
    }

    /* ---------- Utils ---------- */
    function firstName(name) {
        const parts = (name || '').trim().split(' ');
        const first = parts[0] || 'Seseorang';
        const last  = parts.length > 1 ? ' ' + parts[parts.length - 1][0] + '.' : '';
        return first + last;
    }
    function truncate(str, len) { return str.length > len ? str.slice(0, len) + '…' : str; }
    function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    /* ---------- Init ---------- */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fetchData);
    } else {
        fetchData();
    }
})();