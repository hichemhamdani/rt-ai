(function () {
    'use strict';

    var HEADER_H = 85;
    var BUFFER   = 400;

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof gsap === 'undefined') return;

        var section   = document.querySelector('.rt-craft');
        var container = document.querySelector('.rt-craft__features');
        if (!container || !section) return;

        var slides = Array.from(container.querySelectorAll('.rt-craft__feature'));
        if (slides.length < 2) return;

        /* ── Init cartes empilées ── */
        slides.forEach(function (s) {
            s.style.position = 'absolute';
            s.style.top = s.style.left = '0';
            s.style.width = '100%';
        });
        gsap.set(slides[0], { x: 0,  y: 0,  rotation: 0,  scale: 1,    opacity: 1,    filter: 'blur(0px)', zIndex: 2 });
        gsap.set(slides[1], { x: 14, y: 22, rotation: 5,  scale: 0.88, opacity: 0.45, filter: 'blur(3px)', zIndex: 1 });
        gsap.set(container, { height: slides[0].offsetHeight });

        /* ── Timeline pausée ── */
        var tl = gsap.timeline({ paused: true });
        tl.to(slides[0], { x: -14, y: 22, rotation: -5, scale: 0.88, opacity: 0.45, filter: 'blur(3px)', zIndex: 1, duration: 1, ease: 'none' }, 0);
        tl.to(slides[1], { x: 0,   y: 0,  rotation: 0,  scale: 1,    opacity: 1,    filter: 'blur(0px)', zIndex: 2, duration: 1, ease: 'none' }, 0);
        tl.to(container, { height: slides[1].offsetHeight, duration: 1, ease: 'none' }, 0);

        /* ── État ── */
        var locked      = false;
        var accumulator = 0;
        var canLock     = true;
        var exitDir     = null;

        /* ── Snap scroll vers la position exacte de la section ── */
        function snapToSection() {
            var rect = section.getBoundingClientRect();
            var delta = rect.top - HEADER_H;
            if (Math.abs(delta) > 0) {
                window.scrollBy({ top: delta, behavior: 'instant' });
            }
        }

        /* ── Wheel handler — intercepte et pilote l'animation ── */
        function onWheel(e) {
            if (!locked) return;
            e.preventDefault();
            e.stopPropagation();

            accumulator += e.deltaY;

            if (accumulator <= 0) {
                accumulator = 0;
                tl.progress(0);
                locked  = false;
                canLock = false;
                exitDir = 'up';
                window.scrollBy({ top: -1, behavior: 'instant' });
            } else if (accumulator >= BUFFER) {
                accumulator = BUFFER;
                tl.progress(1);
                locked  = false;
                canLock = false;
                exitDir = 'down';
                window.scrollBy({ top: 1, behavior: 'instant' });
            } else {
                tl.progress(accumulator / BUFFER);
            }
        }

        window.addEventListener('wheel', onWheel, { passive: false });

        /* ── Détecter quand verrouiller ── */
        window.addEventListener('scroll', function () {
            if (locked) return;
            var rect = section.getBoundingClientRect();
            if (!canLock) {
                if (exitDir === 'down' && rect.top < HEADER_H - 120) { canLock = true; exitDir = null; }
                else if (exitDir === 'up'  && rect.top > HEADER_H + 120) { canLock = true; exitDir = null; }
                return;
            }
            var nearTop = rect.top <= HEADER_H + 2 && rect.top >= HEADER_H - 80;
            var inView  = rect.bottom > window.innerHeight * 0.4;
            if (nearTop && inView) {
                snapToSection();
                accumulator = Math.round(tl.progress() * BUFFER);
                locked = true;
            }
        }, { passive: true });

        /* ── Touch mobile ── */
        var touchY = 0;
        window.addEventListener('touchstart', function (e) {
            touchY = e.touches[0].clientY;
        }, { passive: true });
        window.addEventListener('touchmove', function (e) {
            if (!locked) return;
            e.preventDefault();
            var delta = touchY - e.touches[0].clientY;
            touchY = e.touches[0].clientY;
            accumulator += delta * 2;
            if (accumulator <= 0) {
                tl.progress(0); locked = false;
            } else if (accumulator >= BUFFER) {
                tl.progress(1); locked = false;
                window.scrollBy({ top: 1, behavior: 'instant' });
            } else {
                tl.progress(accumulator / BUFFER);
            }
        }, { passive: false });
    });
})();
