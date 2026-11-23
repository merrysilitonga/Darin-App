{{--
    Efek animasi DARIN (dipasang sekali di layouts/sidebar.blade.php → berlaku di semua halaman yang sudah login).

    - Motion (motion.dev)            : kartu muncul saat di-scroll + kartu kecil terangkat saat disorot (hover).
    - GSAP + ScrollTrigger + SplitText : judul (h1, h2, h3) muncul per kata saat di-scroll.
    - Lenis                          : scroll halus (mouse/touchpad). Di HP tetap scroll bawaan.

    Pengaman:
    - Kartu & judul disembunyikan sejak awal (tanpa "kedip"), lalu dimunculkan animasi.
      Jika library CDN tidak termuat dalam 2,5 detik → semua langsung tampil normal.
    - "Kurangi animasi" aktif di perangkat → tidak ada animasi & tidak ada smooth scroll.
    - Elemen tersembunyi, isi <details> tertutup, tabel, dan komponen Alpine (x-data) tidak dianimasikan.
    - Tambahkan atribut data-tanpa-animasi pada elemen yang tidak ingin dianimasikan.
--}}
<style>
    /* Disembunyikan sampai animasi siap (kelas dipasang di <html> oleh script di bawah). */
    html.efek-tunggu-kartu main :is(section, div.bg-white[class*="rounded-2xl"], div.bg-white[class*="rounded-3xl"], div.bg-white[class*="rounded-xl"], details.bg-white) {
        opacity: 0;
    }

    html.efek-tunggu-judul main :is(h1, h2, h3) {
        visibility: hidden;
    }

    .efek-kata {
        display: inline-block;
        will-change: transform, opacity;
    }

    /* Bayangan saat kartu disorot (gerakannya diatur Motion). */
    .efek-kartu-hover {
        transition: box-shadow 0.25s ease;
    }

    .efek-kartu-hover.efek-disorot {
        box-shadow: 0 14px 30px -14px rgba(25, 23, 18, 0.28);
    }
</style>

<script>
    // Berjalan langsung (sebelum isi halaman tampil), kecuali perangkat meminta "kurangi animasi".
    (function () {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const html = document.documentElement;
        html.classList.add('efek-tunggu-kartu', 'efek-tunggu-judul');

        // Cadangan: jika CDN lambat/gagal, tampilkan semuanya tanpa animasi.
        setTimeout(function () {
            html.classList.remove('efek-tunggu-kartu', 'efek-tunggu-judul');
        }, 2500);
    })();

    // Aturan elemen yang dianimasikan (dipakai Motion & GSAP).
    window.DarinEfek = {
        selectorKartu: [
            'section',
            'div.bg-white[class*="rounded-2xl"]',
            'div.bg-white[class*="rounded-3xl"]',
            'div.bg-white[class*="rounded-xl"]',
            'details.bg-white',
        ].join(', '),

        bolehDianimasikan: function (elemen) {
            if (elemen.closest('[data-tanpa-animasi], [x-data], template, table')) {
                return false;
            }

            const details = elemen.closest('details');

            if (details && !details.open && !elemen.closest('summary')) {
                return false;
            }

            return elemen.getClientRects().length > 0;
        },
    };
</script>

{{-- MOTION: kartu masuk & hover --}}
<script type="module">
    import { animate, inView, hover } from 'https://cdn.jsdelivr.net/npm/motion@14.11.1/+esm';

    const html = document.documentElement;
    const main = document.querySelector('main');

    if (!html.classList.contains('efek-tunggu-kartu') || !main) {
        html.classList.remove('efek-tunggu-kartu');
    } else {
        const efek = window.DarinEfek;

        // Hanya kotak paling luar (kotak di dalam kotak ikut bergerak bersama induknya).
        const kandidat = Array.from(main.querySelectorAll(efek.selectorKartu)).filter(efek.bolehDianimasikan);
        const kartu = kandidat
            .filter((elemen) => !kandidat.some((lain) => lain !== elemen && lain.contains(elemen)))
            .slice(0, 80);

        kartu.forEach((elemen) => {
            elemen.style.opacity = '0';
            elemen.style.transform = 'translateY(24px)';
        });

        // Kartu disiapkan → kelas penyembunyi dilepas (kartu bersarang ikut tampil bersama induknya).
        html.classList.remove('efek-tunggu-kartu');

        // Kartu yang masuk layar pada waktu bersamaan muncul bergantian (jeda 0,08 detik).
        const sudahMuncul = new WeakSet();
        let urutan = 0;
        let resetUrutan = null;

        inView(kartu, (elemen) => {
            if (sudahMuncul.has(elemen)) {
                return;
            }

            sudahMuncul.add(elemen);

            const jeda = urutan * 0.08;
            urutan += 1;
            clearTimeout(resetUrutan);
            resetUrutan = setTimeout(() => { urutan = 0; }, 120);

            animate(
                elemen,
                { opacity: [0, 1], y: [24, 0] },
                { duration: 0.6, delay: jeda, ease: [0.22, 1, 0.36, 1] }
            ).then(() => {
                elemen.style.opacity = '';
                elemen.style.transform = '';
            });
        }, { margin: '0px 0px -8% 0px' });

        // Hover: hanya kartu kecil di dalam grid (kartu statistik, ringkasan), bukan tabel/form besar.
        const kartuKecil = kartu.filter((elemen) => {
            const induk = elemen.parentElement;

            return induk && induk.classList.contains('grid') && elemen.offsetHeight <= 260;
        });

        kartuKecil.forEach((elemen) => elemen.classList.add('efek-kartu-hover'));

        hover(kartuKecil, (elemen) => {
            elemen.classList.add('efek-disorot');
            animate(elemen, { y: -4, scale: 1.015 }, { type: 'spring', stiffness: 400, damping: 25 });

            return () => {
                elemen.classList.remove('efek-disorot');
                animate(elemen, { y: 0, scale: 1 }, { type: 'spring', stiffness: 400, damping: 30 });
            };
        });
    }
</script>

{{-- LENIS: smooth scroll --}}
<link rel="stylesheet" href="https://unpkg.com/lenis@1.3.26/dist/lenis.css">
<script src="https://unpkg.com/lenis@1.3.26/dist/lenis.min.js" defer></script>

{{-- GSAP: judul muncul per kata saat di-scroll --}}
<script src="https://cdn.jsdelivr.net/npm/gsap@3.15.0/dist/gsap.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.15.0/dist/ScrollTrigger.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.15.0/dist/SplitText.min.js" defer></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const html = document.documentElement;
        const main = document.querySelector('main');
        const adaGsap = Boolean(window.gsap && window.ScrollTrigger);

        // SMOOTH SCROLL (Lenis). Tabel/daftar yang bisa di-scroll sendiri tetap normal (allowNestedScroll).
        if (window.Lenis && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            const lenis = new Lenis({
                autoRaf: !adaGsap,
                anchors: true,
                allowNestedScroll: true,
                lerp: 0.1,
            });

            // Sinkron dengan GSAP ScrollTrigger agar animasi judul terpicu tepat waktu.
            if (adaGsap) {
                lenis.on('scroll', ScrollTrigger.update);
                gsap.ticker.add(function (waktu) {
                    lenis.raf(waktu * 1000);
                });
                gsap.ticker.lagSmoothing(0);
            }

            window.darinLenis = lenis;
        }

        if (!html.classList.contains('efek-tunggu-judul') || !main || !window.gsap || !window.ScrollTrigger || !window.SplitText) {
            html.classList.remove('efek-tunggu-judul');

            return;
        }

        gsap.registerPlugin(ScrollTrigger, SplitText);

        main.querySelectorAll('h1, h2, h3').forEach(function (judul) {
            if (!window.DarinEfek.bolehDianimasikan(judul) || !judul.textContent.trim()) {
                return;
            }

            const pecahan = SplitText.create(judul, {
                type: 'words',
                wordsClass: 'efek-kata',
            });

            gsap.from(pecahan.words, {
                yPercent: 70,
                opacity: 0,
                duration: 0.55,
                ease: 'power3.out',
                stagger: 0.06,
                scrollTrigger: {
                    trigger: judul,
                    start: 'top 92%',
                    once: true,
                },
            });
        });

        // Kata sudah disembunyikan GSAP → judul boleh terlihat.
        html.classList.remove('efek-tunggu-judul');

        window.addEventListener('load', function () {
            ScrollTrigger.refresh();
        });
    });
</script>
