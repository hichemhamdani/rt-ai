(function () {
    'use strict';

    var THRESHOLD = 0.15;

    var groups = [
        { selector: '.rt-expertise__image',    anim: 'fade-left'  },
        { selector: '.rt-expertise__content',  anim: 'fade-right' },
        { selector: '.rt-craft__image',        anim: 'fade-left'  },
        { selector: '.rt-craft__content',      anim: 'fade-right' },
        { selector: '.rt-smart__img-left',     anim: 'fade-left'  },
        { selector: '.rt-smart__img-right',    anim: 'fade-right' },
        { selector: '.rt-smart__center',       anim: 'fade-up'    },
        { selector: '.rt-craft__feature',      anim: 'fade-up',   stagger: true },
        { selector: '.rt-service-card',        anim: 'fade-up',   stagger: true },
        { selector: '.rt-product-card',        anim: 'fade-up',   stagger: true },
        { selector: '.rt-faq__item',           anim: 'fade-up',   stagger: true },
        { selector: '.rt-clients__header',     anim: 'fade-up'    },
        { selector: '.rt-consultation__img-col',    anim: 'fade-left'  },
        { selector: '.rt-consultation__form-col',   anim: 'fade-right' },
        { selector: '.rt-products-section__header', anim: 'fade-up'    },
        { selector: '.rt-section__header',          anim: 'fade-up'    },
        { selector: '.rt-services__header',         anim: 'fade-up'    },
        { selector: '.rt-faq__header',              anim: 'fade-up'    },
    ];

    /* ── Split text en spans par caractère ── */
    function splitIntoChars(el) {
        if (el.dataset.charSplit) return;
        el.dataset.charSplit = '1';

        var raw = el.innerHTML;
        var result = '';
        var charIndex = 0;
        var i = 0;

        while (i < raw.length) {
            if (raw[i] === '<') {
                var close = raw.indexOf('>', i);
                result += raw.substring(i, close + 1);
                i = close + 1;
            } else if (raw[i] === '&') {
                var semi = raw.indexOf(';', i);
                var entity = raw.substring(i, semi + 1);
                var delay = (charIndex * 0.028).toFixed(3);
                result += '<span class="rt-char" style="--char-delay:' + delay + 's">' + entity + '</span>';
                charIndex++;
                i = semi + 1;
            } else if (raw[i] === ' ') {
                result += ' ';
                i++;
            } else {
                var delay = (charIndex * 0.028).toFixed(3);
                result += '<span class="rt-char" style="--char-delay:' + delay + 's">' + raw[i] + '</span>';
                charIndex++;
                i++;
            }
        }

        el.innerHTML = result;
    }

    /* ── Scroll-triggered animations ── */
    function init() {
        if (!('IntersectionObserver' in window)) {
            document.querySelectorAll('[data-anim]').forEach(function (el) {
                el.classList.add('anim-visible');
            });
            document.querySelectorAll('h2:not(.rt-hero__title)').forEach(function (el) {
                splitIntoChars(el);
                el.classList.add('rt-h2-animated');
            });
            return;
        }

        // Observer classique pour fade-in sections
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('anim-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: THRESHOLD });

        groups.forEach(function (group) {
            var els = document.querySelectorAll(group.selector);
            els.forEach(function (el, i) {
                if (el.hasAttribute('data-anim')) return;
                el.setAttribute('data-anim', group.anim);
                if (group.stagger) {
                    el.style.setProperty('--anim-delay', (i * 0.1) + 's');
                }
                observer.observe(el);
            });
        });

        // Observe elements with data-anim already set in HTML (e.g. single product hero)
        document.querySelectorAll('[data-anim]').forEach(function (el) {
            observer.observe(el);
        });

        // Observer pour les h2 + h3 ciblés — animation lettre par lettre au scroll
        var h2Observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    splitIntoChars(entry.target);
                    entry.target.classList.add('rt-h2-animated');
                    h2Observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        document.querySelectorAll('h2').forEach(function (el) {
            if (el.closest('.rt-hero')) return;
            h2Observer.observe(el);
        });

        document.querySelectorAll('.rt-craft__title, .rt-smart__title').forEach(function (el) {
            h2Observer.observe(el);
        });

        document.querySelectorAll('.sp-hero-block__heading, .ld-hero__title').forEach(function (el) {
            h2Observer.observe(el);
        });
    }

    /* ── Hero H1 : animation au chargement ── */
    function heroTitleSweep() {
        var h1 = document.querySelector('.rt-hero__title');
        if (!h1) return;
        splitIntoChars(h1);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(); heroTitleSweep(); });
    } else {
        init();
        heroTitleSweep();
    }
})();
