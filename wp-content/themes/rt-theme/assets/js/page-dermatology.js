(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.ld-faq__item').forEach(function (item) {
            var btn    = item.querySelector('.ld-faq__question');
            var answer = item.querySelector('.ld-faq__answer');

            btn.addEventListener('click', function () {
                var isOpen = item.classList.contains('is-open');

                document.querySelectorAll('.ld-faq__item.is-open').forEach(function (open) {
                    open.classList.remove('is-open');
                    open.querySelector('.ld-faq__answer').style.maxHeight = null;
                    open.querySelector('.ld-faq__question').setAttribute('aria-expanded', 'false');
                });

                if (!isOpen) {
                    item.classList.add('is-open');
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        });
    });
})();
