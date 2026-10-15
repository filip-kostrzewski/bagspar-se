const focusFormFeedback = () => {
    const successMessage = document.querySelector('#form-result');
    const firstInvalidField = document.querySelector('[aria-invalid="true"]');
    const feedback = successMessage ?? firstInvalidField;

    if (!feedback) {
        return;
    }

    window.setTimeout(() => {
        if (document.contains(feedback)) {
            feedback.focus({ preventScroll: Boolean(successMessage) });
        }
    }, 0);
};

if (document.readyState === 'complete') {
    focusFormFeedback();
} else {
    window.addEventListener('load', focusFormFeedback, { once: true });
}

const interestForm = document.querySelector('[data-interest-form]');

if (interestForm) {
    interestForm.addEventListener('submit', (event) => {
        if (interestForm.dataset.submitting === 'true') {
            event.preventDefault();

            return;
        }

        interestForm.dataset.submitting = 'true';

        const submitButton = interestForm.querySelector('button[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            submitButton.textContent = 'Skickar…';
        }
    });
}

const serviceWorkerScript = document.querySelector('script[data-sw-url]');

if ('serviceWorker' in navigator && serviceWorkerScript instanceof HTMLScriptElement) {
    const serviceWorkerUrl = serviceWorkerScript.dataset.swUrl;

    if (serviceWorkerUrl) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register(serviceWorkerUrl).catch(() => {});
        });
    }
}
