document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.site-header');
    const updateHeader = () => {
        if (!header) return;
        header.classList.toggle('is-scrolled', window.scrollY > 8);
    };
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });

    const popup = document.querySelector('[data-discount-popup]');
    const closeBtn = document.querySelector('[data-discount-popup-close]');
    if (popup) {
        setTimeout(() => popup.classList.add('is-visible'), 10000);
        popup.addEventListener('click', (e) => {
            if (e.target === popup) popup.classList.remove('is-visible');
        });
        if (closeBtn) {
            closeBtn.addEventListener('click', () => popup.classList.remove('is-visible'));
        }
    }
});
