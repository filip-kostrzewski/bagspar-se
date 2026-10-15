const storageKey = 'bagspar-cookie-consent';
const banner = document.querySelector('[data-cookie-banner]');

const consentUpdate = (granted) => ({
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied',
    analytics_storage: granted ? 'granted' : 'denied',
});

const writeChoice = (value) => {
    try {
        window.localStorage.setItem(storageKey, value);
    } catch (error) {
        // The choice still applies on this page when storage is blocked.
    }
};

const applyChoice = (value) => {
    if (typeof window.gtag === 'function') {
        window.gtag('consent', 'update', consentUpdate(value === 'granted'));
    }

    document.documentElement.classList.add('cookie-choice-saved');
};

const showBanner = () => {
    if (!(banner instanceof HTMLElement)) {
        return;
    }

    document.documentElement.classList.remove('cookie-choice-saved');
    banner.focus({ preventScroll: true });
};

if (banner instanceof HTMLElement) {
    banner.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        const choice = target.closest('[data-cookie-choice]');

        if (!(choice instanceof HTMLElement)) {
            return;
        }

        const value = choice.dataset.cookieChoice;

        if (value !== 'granted' && value !== 'denied') {
            return;
        }

        writeChoice(value);
        applyChoice(value);
    });
}

document.querySelectorAll('[data-cookie-settings]').forEach((opener) => {
    opener.addEventListener('click', (event) => {
        event.preventDefault();
        showBanner();
    });
});
