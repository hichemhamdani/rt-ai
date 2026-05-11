document.addEventListener('DOMContentLoaded', function () {
    var accordion = document.getElementById('rt-accordion');
    if (!accordion) return;

    accordion.querySelectorAll('.rt-accordion__trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var item = trigger.closest('.rt-accordion__item');
            var isOpen = item.classList.contains('is-open');

            // Close all
            accordion.querySelectorAll('.rt-accordion__item').forEach(function (el) {
                el.classList.remove('is-open');
                el.querySelector('.rt-accordion__trigger').setAttribute('aria-expanded', 'false');
            });

            // Open clicked if it was closed
            if (!isOpen) {
                item.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });
    });
});
