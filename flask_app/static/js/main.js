let activeModal = null;

function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    if (activeModal && activeModal !== modal) {
        closeModal(activeModal.id);
    }
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    activeModal = modal;

    const firstField = modal.querySelector('input, textarea, select') || modal.querySelector('button, a');
    if (firstField) firstField.focus({ preventScroll: true });
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    if (activeModal === modal) activeModal = null;
    if (!document.querySelector('.modal-overlay.is-open')) {
        document.body.classList.remove('modal-open');
    }
}

function setFormMessage(form, message) {
    const error = form.querySelector('[data-form-error]');
    if (!error) return;
    error.textContent = message;
    error.hidden = !message;
}

function normalizePhoneDigits(value) {
    let digits = value.replace(/\D/g, '');
    if (digits.startsWith('8')) digits = `7${digits.slice(1)}`;
    if (digits.startsWith('7')) digits = digits.slice(1);
    return digits.slice(0, 10);
}

function formatPhone(value) {
    const digits = normalizePhoneDigits(value);
    let formatted = '+7';
    if (digits.length > 0) formatted += ` (${digits.slice(0, 3)}`;
    if (digits.length >= 3) formatted += ')';
    if (digits.length > 3) formatted += ` ${digits.slice(3, 6)}`;
    if (digits.length > 6) formatted += `-${digits.slice(6, 8)}`;
    if (digits.length > 8) formatted += `-${digits.slice(8, 10)}`;
    return formatted;
}

function isPhoneComplete(value) {
    return normalizePhoneDigits(value).length === 10;
}

function setupModalControls() {
    document.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', () => closeModal(button.closest('.modal-overlay').id));
    });

    document.querySelectorAll('.modal-overlay').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal(modal.id);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && activeModal) closeModal(activeModal.id);
    });
}

function setupApplicationTriggers() {
    const applicationLabels = ['Онлайн-заявка', 'Оставить заявку', 'Связаться', 'Записаться'];
    document.querySelectorAll('a, button').forEach((element) => {
        const text = element.textContent.trim();
        if (!element.matches('[data-application-trigger]') && !applicationLabels.includes(text)) return;
        element.addEventListener('click', (event) => {
            event.preventDefault();
            const form = document.querySelector('[data-application-form]');
            const formView = document.querySelector('[data-application-form-view]');
            const successView = document.querySelector('[data-application-success]');
            if (form) {
                form.reset();
                setFormMessage(form, '');
            }
            if (formView) formView.hidden = false;
            if (successView) successView.hidden = true;
            openModal('application-modal');
        });
    });
}

function resetLoginModal() {
    const form = document.querySelector('[data-login-form]');
    if (form) {
        form.reset();
        setFormMessage(form, '');
    }
}

function resetRegisterModal() {
    const form = document.querySelector('[data-register-form]');
    const formView = document.querySelector('[data-register-form-view]');
    const successView = document.querySelector('[data-register-success]');
    if (form) {
        form.reset();
        setFormMessage(form, '');
    }
    if (formView) formView.hidden = false;
    if (successView) successView.hidden = true;
}

function setupLoginTrigger() {
    document.querySelectorAll('[data-login-trigger], [data-open-login]').forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();
            resetLoginModal();
            openModal('login-modal');
        });
    });
}

function setupRegisterTrigger() {
    document.querySelectorAll('[data-register-trigger]').forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();
            resetRegisterModal();
            openModal('register-modal');
        });
    });
}

function setupPhoneMask() {
    document.querySelectorAll('[data-phone-input]').forEach((input) => {
        input.addEventListener('focus', () => {
            if (!input.value.trim()) input.value = '+7';
        });
        input.addEventListener('input', () => {
            input.value = formatPhone(input.value);
        });
        input.addEventListener('blur', () => {
            if (normalizePhoneDigits(input.value).length === 0) input.value = '';
        });
    });
}

function setupApplicationForm() {
    const form = document.querySelector('[data-application-form]');
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        setFormMessage(form, '');
        const phoneInput = form.querySelector('[data-phone-input]');
        if (!phoneInput || !isPhoneComplete(phoneInput.value)) {
            setFormMessage(form, 'Укажите телефон в формате +7 (999) 999-99-99.');
            if (phoneInput) phoneInput.focus();
            return;
        }

        const submitButton = form.querySelector('[type="submit"]');
        if (submitButton) submitButton.disabled = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.ok === false) {
                setFormMessage(form, data.error || 'Не удалось отправить заявку. Попробуйте ещё раз.');
                return;
            }
            form.reset();
            const formView = document.querySelector('[data-application-form-view]');
            const successView = document.querySelector('[data-application-success]');
            if (formView) formView.hidden = true;
            if (successView) successView.hidden = false;
        } catch (error) {
            setFormMessage(form, 'Не удалось отправить заявку. Проверьте подключение и попробуйте ещё раз.');
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });
}

function setupRegisterForm() {
    const form = document.querySelector('[data-register-form]');
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        setFormMessage(form, '');
        const name = form.querySelector('[name="name"]');
        const email = form.querySelector('[name="email"]');
        const password = form.querySelector('[name="password"]');
        if (!name.value.trim() || !email.value.trim() || password.value.length < 6) {
            setFormMessage(form, 'Заполните имя, email и пароль от 6 символов.');
            (!name.value.trim() ? name : (!email.value.trim() ? email : password)).focus();
            return;
        }

        const submitButton = form.querySelector('[type="submit"]');
        if (submitButton) submitButton.disabled = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.ok === false) {
                setFormMessage(form, data.error || 'Не удалось завершить регистрацию.');
                return;
            }
            form.reset();
            const formView = document.querySelector('[data-register-form-view]');
            const successView = document.querySelector('[data-register-success]');
            if (formView) formView.hidden = true;
            if (successView) successView.hidden = false;
        } catch (error) {
            setFormMessage(form, 'Не удалось завершить регистрацию. Проверьте подключение и попробуйте ещё раз.');
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });
}

function setupLoginForm() {
    const form = document.querySelector('[data-login-form]');
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        setFormMessage(form, '');
        const email = form.querySelector('[name="email"]');
        const password = form.querySelector('[name="password"]');
        if (!email.value.trim() || !password.value.trim()) {
            setFormMessage(form, 'Заполните e-mail и пароль.');
            (!email.value.trim() ? email : password).focus();
            return;
        }

        const submitButton = form.querySelector('[type="submit"]');
        if (submitButton) submitButton.disabled = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.ok === false) {
                setFormMessage(form, data.error || 'Неверный e-mail или пароль.');
                return;
            }
            window.location.href = data.redirect || '/profile';
        } catch (error) {
            setFormMessage(form, 'Не удалось войти. Проверьте подключение и попробуйте ещё раз.');
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.site-header');
    const updateHeader = () => {
        if (!header) return;
        header.classList.toggle('is-scrolled', window.scrollY > 8);
    };
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });

    setupModalControls();
    setupApplicationTriggers();
    setupLoginTrigger();
    setupRegisterTrigger();
    setupPhoneMask();
    setupApplicationForm();
    setupLoginForm();
    setupRegisterForm();

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
