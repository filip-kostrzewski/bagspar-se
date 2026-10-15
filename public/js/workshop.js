document.querySelectorAll('[data-workshop-focus]').forEach((message) => {
    message.focus({ preventScroll: true });
});

const stagePanels = document.querySelectorAll('[data-stage-panel]');
const mobileStageLayout = window.matchMedia('(max-width: 760px)');

const updateStagePanelVisibility = () => {
    stagePanels.forEach((panel) => {
        panel.open = !mobileStageLayout.matches;
    });
};

updateStagePanelVisibility();
mobileStageLayout.addEventListener('change', updateStagePanelVisibility);

document.querySelectorAll('[data-copy-link]').forEach((button) => {
    button.addEventListener('click', async () => {
        const input = document.getElementById(button.dataset.copyLink);
        const status = button.closest('.work-card-link-panel')?.querySelector('[data-copy-status]');

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

document.querySelectorAll('[data-print-label]').forEach((button) => {
    button.addEventListener('click', async () => {
        const qrImage = document.querySelector('.workshop-label-print__qr');
        const status = document.querySelector('[data-label-print-status]');
        const originalButtonText = button.textContent;

        button.disabled = true;
        button.textContent = 'Förbereder etikett…';

        const imageIsReady = await new Promise((resolve) => {
            if (!qrImage) {
                resolve(false);
                return;
            }

            if (qrImage.complete) {
                resolve(qrImage.naturalWidth > 0);
                return;
            }

            qrImage.addEventListener('load', () => resolve(qrImage.naturalWidth > 0), { once: true });
            qrImage.addEventListener('error', () => resolve(false), { once: true });
        });

        if (!imageIsReady) {
            if (status) {
                status.textContent = 'QR-koden kunde inte laddas. Kontrollera anslutningen och försök igen.';
            }
            button.disabled = false;
            button.textContent = originalButtonText;
            return;
        }

        try {
            await qrImage.decode();
        } catch {
            if (status) {
                status.textContent = 'QR-koden kunde inte laddas. Kontrollera anslutningen och försök igen.';
            }
            button.disabled = false;
            button.textContent = originalButtonText;
            return;
        }

        if (status) {
            status.textContent = '';
        }
        button.disabled = false;
        button.textContent = originalButtonText;
        document.body.classList.add('is-label-printing');
        window.addEventListener('afterprint', () => {
            document.body.classList.remove('is-label-printing');
        }, { once: true });
        window.print();
    });
});

document.querySelectorAll('[data-photo-upload]').forEach((form) => {
    const input = form.querySelector('input[type="file"]');
    const preview = form.querySelector('[data-photo-preview]');
    const message = form.querySelector('[data-photo-upload-message]');
    const submit = form.querySelector('button[type="submit"]');
    const originalButtonText = submit?.textContent;
    let previewUrl;
    let selectedImageIsValid = false;

    input?.addEventListener('change', async () => {
        const file = input.files?.[0];

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = undefined;
        }
        selectedImageIsValid = false;
        preview?.replaceChildren();
        if (message) {
            message.textContent = '';
            message.dataset.error = 'false';
        }

        if (!file) {
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            input.value = '';
            if (message) {
                message.textContent = 'Välj en JPG-, PNG- eller WebP-bild.';
                message.dataset.error = 'true';
            }
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            input.value = '';
            if (message) {
                message.textContent = 'Bilden är större än 5 MB. Välj en mindre fil.';
                message.dataset.error = 'true';
            }
            return;
        }

        previewUrl = URL.createObjectURL(file);
        const image = new Image();
        image.src = previewUrl;
        image.alt = `Förhandsvisning av ${file.name}`;

        try {
            await image.decode();
        } catch {
            input.value = '';
            URL.revokeObjectURL(previewUrl);
            previewUrl = undefined;
            if (message) {
                message.textContent = 'Bilden kunde inte öppnas. Välj en annan bildfil.';
                message.dataset.error = 'true';
            }
            return;
        }

        const pixelCount = image.naturalWidth * image.naturalHeight;
        if (image.naturalWidth > 6000 || image.naturalHeight > 6000 || pixelCount > 16_000_000) {
            input.value = '';
            URL.revokeObjectURL(previewUrl);
            previewUrl = undefined;
            if (message) {
                message.textContent = 'Bilden får vara högst 16 megapixlar och 6 000 pixlar på varje sida.';
                message.dataset.error = 'true';
            }
            return;
        }

        selectedImageIsValid = true;
        preview?.append(image);
        if (message) {
            const sizeInMegabytes = (file.size / (1024 * 1024)).toFixed(1).replace('.', ',');
            message.textContent = `${file.name} · ${sizeInMegabytes} MB · vald för uppladdning, inte sparad ännu.`;
        }
    });

    form.addEventListener('submit', (event) => {
        const file = input?.files?.[0];

        if (!file || !selectedImageIsValid) {
            event.preventDefault();
            if (message) {
                message.textContent = file ? 'Vänta tills bildens storlek har kontrollerats.' : 'Välj en bild innan du laddar upp.';
                message.dataset.error = 'true';
            }
            input?.focus();
            return;
        }

        if (submit) {
            submit.disabled = true;
            submit.textContent = 'Laddar upp bilden…';
        }
        if (message) {
            message.textContent = 'Bilden skickas. Den visas här först när uppladdningen är klar.';
            message.dataset.error = 'false';
        }
    });

    window.addEventListener('pageshow', () => {
        if (submit && submit.disabled) {
            submit.disabled = false;
            submit.textContent = originalButtonText;
        }
    });

    window.addEventListener('pagehide', () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
    });
});
