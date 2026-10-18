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

const lightbox = document.querySelector('[data-lightbox]');

if (lightbox instanceof HTMLDialogElement) {
    const lightboxImage = lightbox.querySelector('[data-lightbox-image]');
    const lightboxCaption = lightbox.querySelector('[data-lightbox-caption]');
    const lightboxClose = lightbox.querySelector('[data-lightbox-close]');
    const lightboxFull = lightbox.querySelector('[data-lightbox-full]');
    const lightboxFrame = lightbox.querySelector('[data-lightbox-frame]');
    let lightboxOpener = null;

    const lightboxFocusable = () => [...lightbox.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])')]
        .filter((element) => element instanceof HTMLElement && !element.hasAttribute('disabled') && element.getAttribute('aria-hidden') !== 'true');

    document.querySelectorAll('[data-lightbox-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const source = button.querySelector('img');

            if (!(source instanceof HTMLImageElement) || !(lightboxImage instanceof HTMLImageElement)) {
                return;
            }

            const caption = button.closest('figure')?.querySelector('figcaption')?.textContent?.replace(/\s+/g, ' ').trim() || source.alt;

            lightboxImage.src = source.currentSrc || source.src;
            lightboxImage.alt = source.alt;
            lightboxImage.removeAttribute('width');
            lightboxImage.removeAttribute('height');

            if (lightboxFull instanceof HTMLAnchorElement) {
                lightboxFull.href = lightboxImage.src;
            }

            if (lightboxFrame instanceof HTMLElement) {
                lightboxFrame.scrollTo(0, 0);
            }

            if (lightboxCaption) {
                lightboxCaption.textContent = caption;
            }

            lightboxOpener = button;
            document.documentElement.classList.add('lightbox-open');
            lightbox.showModal();

            if (lightboxClose instanceof HTMLElement) {
                lightboxClose.focus();
            }
        });
    });

    lightbox.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            lightbox.close();

            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const items = lightboxFocusable();

        if (items.length === 0) {
            event.preventDefault();

            return;
        }

        const first = items[0];
        const last = items[items.length - 1];
        const active = document.activeElement;

        if (event.shiftKey && (active === first || !lightbox.contains(active))) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && (active === last || !lightbox.contains(active))) {
            event.preventDefault();
            first.focus();
        }
    });

    lightbox.addEventListener('click', (event) => {
        if (event.target === lightbox) {
            lightbox.close();
        }
    });

    if (lightboxClose instanceof HTMLElement) {
        lightboxClose.addEventListener('click', () => {
            lightbox.close();
        });
    }

    lightbox.addEventListener('close', () => {
        document.documentElement.classList.remove('lightbox-open');

        if (lightboxImage instanceof HTMLImageElement) {
            lightboxImage.removeAttribute('src');
            lightboxImage.alt = '';
        }

        if (lightboxFull instanceof HTMLAnchorElement) {
            lightboxFull.removeAttribute('href');
        }

        if (lightboxOpener instanceof HTMLElement) {
            lightboxOpener.focus();
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
