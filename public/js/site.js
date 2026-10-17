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

const workflow = document.querySelector('[data-workflow]');

if (workflow instanceof HTMLElement) {
    const controls = workflow.querySelector('[data-workflow-controls]');
    const steps = [...workflow.querySelectorAll('[data-workflow-step]')];
    const description = workflow.querySelector('[data-workflow-description]');
    const liveStatus = workflow.querySelector('[data-workflow-live]');
    const image = workflow.querySelector('[data-workflow-image]');
    const card = workflow.querySelector('.atelier-card');
    const status = workflow.querySelector('[data-workflow-status]');
    const progressSteps = [...workflow.querySelectorAll('[data-workflow-progress]')];

    if (
        controls instanceof HTMLElement
        && steps.length > 0
        && description instanceof HTMLElement
        && liveStatus instanceof HTMLElement
        && image instanceof HTMLImageElement
        && card instanceof HTMLElement
        && status instanceof HTMLElement
    ) {
        steps.forEach((step, index) => {
            step.addEventListener('click', () => {
                if (step.getAttribute('aria-pressed') === 'true') {
                    return;
                }

                const stepName = step.dataset.step;
                const fullImage = step.dataset.image;
                const smallImage = step.dataset.imageSmall;
                const smallImageWidth = Number(step.dataset.imageSmallWidth);
                const imageAlt = step.dataset.imageAlt;
                const imageWidth = Number(step.dataset.imageWidth);
                const imageHeight = Number(step.dataset.imageHeight);
                const stepLabel = step.querySelector('span:not(.atelier-step__number)')?.textContent?.trim();

                if (!stepName || !fullImage || !smallImage || !imageAlt || !stepLabel) {
                    return;
                }

                steps.forEach((otherStep) => {
                    const isSelected = otherStep === step;
                    otherStep.classList.toggle('is-active', isSelected);
                    otherStep.setAttribute('aria-pressed', String(isSelected));
                });

                description.textContent = step.dataset.description ?? '';
                status.textContent = step.dataset.status ?? stepLabel;
                card.dataset.stage = stepName;
                liveStatus.textContent = `Visar ${stepLabel} i exempelkortet.`;

                progressSteps.forEach((progressStep, progressIndex) => {
                    progressStep.classList.toggle('is-current', progressIndex === index);
                    progressStep.classList.toggle('is-done', progressIndex < index);

                    if (progressIndex === index) {
                        progressStep.setAttribute('aria-current', 'step');
                    } else {
                        progressStep.removeAttribute('aria-current');
                    }
                });

                image.classList.add('is-changing');
                image.alt = imageAlt;
                image.loading = 'eager';
                if (imageWidth > 0) {
                    image.width = imageWidth;
                }
                if (imageHeight > 0) {
                    image.height = imageHeight;
                }

                const revealNextImage = () => {
                    image.classList.remove('is-changing');
                };

                image.addEventListener('load', revealNextImage, { once: true });
                image.addEventListener('error', revealNextImage, { once: true });
                image.srcset = `${smallImage} ${smallImageWidth || 600}w, ${fullImage} ${imageWidth}w`;
                image.src = fullImage;

                if (image.complete && image.naturalWidth > 0) {
                    revealNextImage();
                }
            });
        });

        controls.hidden = false;
    }
}

const revealTargets = [...document.querySelectorAll('[data-reveal]')];
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!reduceMotion && 'IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    revealTargets.forEach((target) => {
        const isAlreadyVisible = target.getBoundingClientRect().top < window.innerHeight * 0.9;

        if (isAlreadyVisible) {
            target.classList.add('is-visible');
        } else {
            target.classList.add('will-reveal');
            revealObserver.observe(target);
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
