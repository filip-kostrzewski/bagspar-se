document.querySelectorAll('[data-workshop-focus]').forEach((message) => {
    message.focus({ preventScroll: true });
});

document.querySelectorAll('.workshop-flash').forEach((flash) => {
    window.setTimeout(() => {
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        flash.classList.add('is-hiding');

        const hideFlash = () => {
            if (flash.contains(document.activeElement)) {
                const main = document.getElementById('main');

                if (main) {
                    if (! main.hasAttribute('tabindex')) {
                        main.tabIndex = -1;
                    }

                    main.focus();
                }
            }

            flash.hidden = true;
        };

        if (reduceMotion) {
            hideFlash();

            return;
        }

        window.setTimeout(hideFlash, 280);
    }, 3000);
});

const trapDialogFocus = (dialog) => {
    dialog.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab' || ! dialog.open) {
            return;
        }

        const focusable = [...dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])')]
            .filter((element) => element instanceof HTMLElement && element.getClientRects().length > 0);

        if (focusable.length === 0) {
            event.preventDefault();

            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        const active = document.activeElement;

        if (event.shiftKey && (active === first || active === dialog)) {
            event.preventDefault();
            last.focus();

            return;
        }

        if (! event.shiftKey && (active === last || active === dialog)) {
            event.preventDefault();
            first.focus();
        }
    });
};

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle ?? '');

        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        button.textContent = showing ? 'Visa' : 'Dölj';
        button.setAttribute('aria-pressed', String(!showing));
    });
});

document.querySelectorAll('[data-file-picker]').forEach((button) => {
    const input = document.getElementById(button.dataset.filePicker ?? '');
    const name = button.parentElement?.querySelector('[data-file-name]');

    if (!(input instanceof HTMLInputElement)) {
        return;
    }

    button.addEventListener('click', () => {
        input.click();
    });

    input.addEventListener('change', () => {
        const files = input.files ?? [];

        if (name) {
            if (files.length === 0) {
                name.textContent = 'Ingen bild vald';
            } else {
                name.textContent = files.length === 1 ? files[0].name : `${files.length} bilder valda`;
            }
        }
    });
});

document.querySelectorAll('[data-copy-link]').forEach((button) => {
    button.addEventListener('click', async () => {
        const input = document.getElementById(button.dataset.copyLink);
        const status = button.closest('.work-card-link-panel, .client-status-share')?.querySelector('[data-copy-status]');

        if (!input) {
            return;
        }

        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(input.value);
            } else {
                input.focus();
                input.select();
                if (!document.execCommand('copy')) {
                    throw new Error('Clipboard copy failed');
                }
            }

            if (status) {
                status.textContent = 'Länken har kopierats.';
            }
        } catch {
            input.focus();
            input.select();
            if (status) {
                status.textContent = 'Markera länken och kopiera den manuellt.';
            }
        }
    });
});

document.querySelectorAll('[data-print-report]').forEach((button) => {
    button.addEventListener('click', async () => {
        const originalButtonText = button.textContent;
        const reportImages = [...document.querySelectorAll('.report-page img')];
        button.disabled = true;
        button.textContent = 'Förbereder utskrift…';

        reportImages.forEach((image) => {
            image.loading = 'eager';
        });

        await Promise.all(reportImages.map((image) => {
            if (image.complete) {
                return Promise.resolve();
            }

            return new Promise((resolve) => {
                image.addEventListener('load', resolve, { once: true });
                image.addEventListener('error', resolve, { once: true });
            });
        }));

        await Promise.all(reportImages.map((image) => image.decode().catch(() => undefined)));
        button.disabled = false;
        button.textContent = originalButtonText;
        window.print();
    });
});

document.querySelectorAll('[data-print-labels]').forEach((button) => {
    button.addEventListener('click', async () => {
        const qrImages = [...document.querySelectorAll('.print-label__qr')];
        const status = document.querySelector('[data-label-print-status]');
        const originalButtonText = button.textContent;

        button.disabled = true;
        button.textContent = 'Förbereder etikett…';

        const imagesAreReady = await Promise.all(qrImages.map((qrImage) => new Promise((resolve) => {
            if (qrImage.complete) {
                resolve(qrImage.naturalWidth > 0);
                return;
            }

            qrImage.addEventListener('load', () => resolve(qrImage.naturalWidth > 0), { once: true });
            qrImage.addEventListener('error', () => resolve(false), { once: true });
        })));

        if (qrImages.length === 0 || imagesAreReady.some((ready) => !ready)) {
            if (status) {
                status.textContent = 'QR-koden kunde inte laddas. Kontrollera anslutningen och försök igen.';
            }
            button.disabled = false;
            button.textContent = originalButtonText;
            return;
        }

        await Promise.all(qrImages.map((qrImage) => qrImage.decode().catch(() => undefined)));

        if (status) {
            status.textContent = '';
        }
        button.disabled = false;
        button.textContent = originalButtonText;
        window.print();
    });
});

const confirmDialog = document.querySelector('[data-confirm-dialog]');
let pendingConfirmForm = null;

if (confirmDialog instanceof HTMLDialogElement) {
    trapDialogFocus(confirmDialog);
    confirmDialog.addEventListener('close', () => {
        const form = pendingConfirmForm;
        pendingConfirmForm = null;

        if (confirmDialog.returnValue !== 'confirm' || ! (form instanceof HTMLFormElement)) {
            return;
        }

        form.dataset.confirmed = 'true';
        form.requestSubmit();
    });
}

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (form.dataset.confirmed === 'true') {
            delete form.dataset.confirmed;

            return;
        }

        const message = form.getAttribute('data-confirm');

        if (! message || ! (confirmDialog instanceof HTMLDialogElement)) {
            event.preventDefault();

            return;
        }

        const expected = form.getAttribute('data-confirm-match');

        if (expected !== null) {
            const field = form.querySelector('input[name="confirmation"]');
            const typed = field instanceof HTMLInputElement ? field.value : '';

            if (typed !== expected) {
                event.preventDefault();
                const error = form.querySelector('[data-confirm-error]');
                const mismatch = form.getAttribute('data-confirm-match-message') || 'Bekräftelsen stämmer inte.';

                if (error) {
                    error.hidden = false;
                    error.textContent = mismatch;
                }

                if (field instanceof HTMLInputElement) {
                    field.setAttribute('aria-invalid', 'true');
                    field.focus();
                }

                return;
            }
        }

        event.preventDefault();
        pendingConfirmForm = form;

        const messageNode = confirmDialog.querySelector('[data-confirm-message]');
        const titleNode = confirmDialog.querySelector('.workshop-dialog__title');
        const acceptButton = confirmDialog.querySelector('[data-confirm-accept]');
        const cancelButton = confirmDialog.querySelector('[data-confirm-cancel]');
        const title = form.getAttribute('data-confirm-title');

        if (titleNode) {
            titleNode.textContent = title && title !== '' ? title : 'Bekräfta';
        }

        if (messageNode) {
            messageNode.textContent = message;
        }

        if (acceptButton instanceof HTMLButtonElement) {
            const label = event.submitter instanceof HTMLElement ? event.submitter.textContent.trim() : '';
            acceptButton.textContent = label !== '' ? label : 'Fortsätt';
        }

        confirmDialog.showModal();

        if (cancelButton instanceof HTMLElement) {
            cancelButton.focus();
        }
    });
});

document.querySelectorAll('[data-customer-combobox]').forEach((root) => {
    const select = root.querySelector('[data-customer-select]');
    const search = root.querySelector('[data-customer-search]');
    const list = root.querySelector('[data-customer-list]');

    if (! (select instanceof HTMLSelectElement) || ! (search instanceof HTMLInputElement) || ! list) {
        return;
    }

    const options = Array.from(select.options).map((option) => ({
        value: option.value,
        label: option.textContent.trim(),
    }));
    let visibleOptions = [];
    let activeIndex = -1;

    const hideList = () => {
        list.hidden = true;
        search.setAttribute('aria-expanded', 'false');
        search.removeAttribute('aria-activedescendant');
        activeIndex = -1;
    };

    const setActive = (index, shouldScroll) => {
        const items = [...list.querySelectorAll('[role="option"]')];

        if (items.length === 0 || index < 0) {
            activeIndex = -1;
            search.removeAttribute('aria-activedescendant');

            return;
        }

        activeIndex = index;
        items.forEach((item, itemIndex) => {
            const selected = itemIndex === activeIndex;
            item.setAttribute('aria-selected', selected ? 'true' : 'false');
            item.classList.toggle('is-active', selected);

            if (selected) {
                search.setAttribute('aria-activedescendant', item.id);

                if (shouldScroll) {
                    item.scrollIntoView({ block: 'nearest' });
                }
            }
        });
    };

    const choose = (option) => {
        select.value = option.value;

        if (option.value === 'new') {
            const typed = search.value.trim();
            const nameInput = root.querySelector('input[name="new_customer_name"]');

            if (nameInput instanceof HTMLInputElement && typed !== '' && typed !== option.label) {
                nameInput.value = typed.slice(0, 160);
            }
        } else {
            search.value = option.label;
        }

        hideList();
        select.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const render = () => {
        const query = search.value.trim().toLocaleLowerCase('sv');
        visibleOptions = options.filter((option) => {
            if (option.value === '') {
                return false;
            }

            if (option.value === 'new' || query === '') {
                return true;
            }

            return option.label.toLocaleLowerCase('sv').includes(query);
        });

        list.replaceChildren();
        visibleOptions.forEach((option, index) => {
            const item = document.createElement('div');
            item.id = `${list.id}-option-${index}`;
            item.className = 'customer-combobox__option';
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', 'false');
            item.textContent = option.label;
            item.addEventListener('mousedown', (event) => {
                event.preventDefault();
            });
            item.addEventListener('click', () => choose(option));
            list.append(item);
        });
        list.hidden = visibleOptions.length === 0;
        search.setAttribute('aria-expanded', list.hidden ? 'false' : 'true');
        setActive(visibleOptions.length === 0 ? -1 : 0, false);
    };

    root.dataset.enhanced = 'true';
    select.classList.add('visually-hidden');
    select.tabIndex = -1;
    select.setAttribute('aria-hidden', 'true');

    const selected = options.find((option) => option.value === select.value && option.value !== '' && option.value !== 'new');
    if (selected) {
        search.value = selected.label;
    }

    search.addEventListener('focus', render);
    search.addEventListener('input', () => {
        const exact = options.find((option) => option.value !== 'new' && option.label.toLocaleLowerCase('sv') === search.value.trim().toLocaleLowerCase('sv'));

        if (search.value.trim() === '') {
            select.value = '';
        } else if (exact) {
            select.value = exact.value;
        }

        render();
    });
    search.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            const wasHidden = list.hidden;

            if (wasHidden) {
                render();
            }

            if (visibleOptions.length === 0) {
                return;
            }

            if (wasHidden) {
                setActive(event.key === 'ArrowDown' ? 0 : visibleOptions.length - 1, true);

                return;
            }

            const direction = event.key === 'ArrowDown' ? 1 : -1;
            const next = activeIndex < 0
                ? (direction > 0 ? 0 : visibleOptions.length - 1)
                : (activeIndex + direction + visibleOptions.length) % visibleOptions.length;
            setActive(next, true);

            return;
        }

        if (event.key === 'Escape' && ! list.hidden) {
            event.preventDefault();
            event.stopPropagation();
            hideList();

            return;
        }

        if (event.key === 'Enter' && ! list.hidden && visibleOptions[activeIndex]) {
            event.preventDefault();
            choose(visibleOptions[activeIndex]);
        }
    });

    root.closest('form')?.addEventListener('submit', (event) => {
        if (select.value === '' || select.value === 'new') {
            return;
        }

        const current = options.find((option) => option.value === select.value);
        if (current && search.value.trim() !== current.label) {
            event.preventDefault();
            search.focus();
            render();
        }
    });

    document.addEventListener('click', (event) => {
        if (! root.contains(event.target)) {
            hideList();
        }
    });
});

document.querySelectorAll('[data-photo-upload]').forEach((form) => {
    const input = form.querySelector('input[type="file"]');
    const preview = form.querySelector('[data-photo-preview]');
    const message = form.querySelector('[data-photo-upload-message]');
    const submit = form.querySelector('button[type="submit"]');
    const originalButtonText = submit?.textContent;
    const maxFiles = Math.min(20, Number.parseInt(form.dataset.photoRemaining ?? '20', 10) || 20);
    let previewUrls = [];
    let selectionIsValid = false;
    let checkId = 0;

    const releasePreviews = () => {
        previewUrls.forEach((url) => URL.revokeObjectURL(url));
        previewUrls = [];
    };

    const showMessage = (text, isError) => {
        if (message) {
            message.textContent = text;
            message.dataset.error = isError ? 'true' : 'false';
        }
    };

    const reject = (text) => {
        input.value = '';
        releasePreviews();
        preview?.replaceChildren();
        selectionIsValid = false;
        form.querySelector('[data-file-name]')?.replaceChildren('Ingen bild vald');
        showMessage(text, true);
    };

    input?.addEventListener('change', async () => {
        const files = Array.from(input.files ?? []);
        const currentCheck = ++checkId;

        releasePreviews();
        selectionIsValid = false;
        preview?.replaceChildren();
        showMessage('', false);

        if (files.length === 0) {
            return;
        }

        if (files.length > maxFiles) {
            reject(maxFiles === 1
                ? 'Det finns plats för en bild till på den här bågen.'
                : `Det finns plats för ${maxFiles} bilder till på den här bågen. Välj färre bilder.`);
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        const images = [];

        for (const file of files) {
            if (!allowedTypes.includes(file.type)) {
                reject(`${file.name} är inte en JPG-, PNG- eller WebP-bild.`);
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                reject(`${file.name} är större än 5 MB. Välj en mindre fil.`);
                return;
            }

            const url = URL.createObjectURL(file);
            previewUrls.push(url);
            const image = new Image();
            image.src = url;
            image.alt = `Förhandsvisning av ${file.name}`;

            try {
                await image.decode();
            } catch {
                if (currentCheck === checkId) {
                    reject(`${file.name} kunde inte öppnas. Välj en annan bildfil.`);
                }
                return;
            }

            if (currentCheck !== checkId) {
                return;
            }

            if (image.naturalWidth > 6000 || image.naturalHeight > 6000 || image.naturalWidth * image.naturalHeight > 12_000_000) {
                reject(`${file.name} får vara högst 12 megapixlar och 6 000 pixlar på varje sida.`);
                return;
            }

            images.push(image);
        }

        selectionIsValid = true;
        preview?.append(...images);
        const totalBytes = files.reduce((sum, file) => sum + file.size, 0);
        const sizeInMegabytes = (totalBytes / (1024 * 1024)).toFixed(1).replace('.', ',');
        showMessage(
            files.length === 1
                ? `${files[0].name} · ${sizeInMegabytes} MB · vald för uppladdning, inte sparad ännu.`
                : `${files.length} bilder · ${sizeInMegabytes} MB · valda för uppladdning, inte sparade ännu.`,
            false,
        );
    });

    form.addEventListener('submit', (event) => {
        const hasFiles = (input?.files?.length ?? 0) > 0;

        if (!hasFiles || !selectionIsValid) {
            event.preventDefault();
            showMessage(hasFiles ? 'Vänta tills bildernas storlek har kontrollerats.' : 'Välj minst en bild innan du laddar upp.', true);
            input?.focus();
            return;
        }

        const many = (input?.files?.length ?? 0) > 1;

        if (submit) {
            submit.disabled = true;
            submit.textContent = many ? 'Laddar upp bilderna…' : 'Laddar upp bilden…';
        }
        showMessage(many ? 'Bilderna skickas. De visas här först när uppladdningen är klar.' : 'Bilden skickas. Den visas här först när uppladdningen är klar.', false);
    });

    window.addEventListener('pageshow', () => {
        if (submit && submit.disabled) {
            submit.disabled = false;
            submit.textContent = originalButtonText;
        }
    });

    window.addEventListener('pagehide', releasePreviews);
});
