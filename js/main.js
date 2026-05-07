
document.addEventListener('DOMContentLoaded', () => {
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
