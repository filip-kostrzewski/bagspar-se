const root = document.querySelector('[data-demo-card]');

if (root) {
    const STEPS = ['inlämnad', 'skrapning', 'repair', 'kittning', 'målning', 'klar'];
    const STEP_LABELS = {
        inlämnad: 'Mottagen',
        skrapning: 'Skrapning',
        repair: 'Trälagning',
        kittning: 'Glasning',
        målning: 'Målning',
        klar: 'Klar',
    };
    const STORAGE_KEY = 'bagspar-demo-card';
    const ID_KEY = 'bagspar-demo-id';
    const syncUrl = root.dataset.syncUrl;
    const defaults = captureDefaults();
    let current = readStored() ?? clone(defaults);
    let syncing = false;
    let resync = false;
    let retryTimer = null;

    render();

    if (current.phase === 'pending') {
        ensureRetry();
        syncNow();
    } else if (navigator.onLine && readDemoId()) {
        pull();
    }

    root.querySelectorAll('[data-step]').forEach((button) => {
        button.addEventListener('click', () => {
            const step = button.dataset.step;

            if (!step || step === current.step) {
                return;
            }

            current.step = step;
            markPending();
            syncNow();
        });
    });

    root.querySelector('[data-note-form]')?.addEventListener('submit', (event) => {
        event.preventDefault();
        const field = root.querySelector('#demo-note');

        if (!field) {
            return;
        }

        const text = field.value.trim();

        if (text === '' || current.notes.length >= 20) {
            return;
        }

        current.notes.push({
            id: createId(),
            text: text.slice(0, 280),
            meta: 'Sparad i kortet',
        });
        field.value = '';
        markPending();
        syncNow();
    });

    root.querySelector('[data-photo-input]')?.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        event.target.value = '';

        if (!file || !file.type.startsWith('image/') || current.photos.length >= 3) {
            return;
        }

        shrinkImage(file)
            .then((src) => {
                current.photos.push({
                    id: createId(),
                    src,
                });
                persist();
                render();
            })
            .catch(() => {
                setStatus('Bilden kunde inte läsas.', 'waiting');
            });
    });

    root.querySelector('[data-reset]')?.addEventListener('click', (event) => {
        const button = event.currentTarget;
        const message = button instanceof HTMLElement
            ? button.dataset.resetConfirm || 'Återställ exemplet? Ändringar du gjort på kortet tas bort.'
            : 'Återställ exemplet? Ändringar du gjort på kortet tas bort.';

        if (!window.confirm(message)) {
            return;
        }

        const demoId = readDemoId();
        current = clone(defaults);
        current.phase = demoId ? 'pending' : 'idle';
        persist();
        render();

        if (demoId) {
            syncNow();
        }
    });

    root.querySelectorAll('[data-photo]').forEach((photo) => {
        const open = photo.querySelector('[data-photo-open]');
        const close = photo.querySelector('[data-photo-close]');

        open?.addEventListener('click', () => {
            const expanded = photo.getAttribute('aria-expanded') === 'true';
            closePhotos();

            if (!expanded) {
                photo.setAttribute('aria-expanded', 'true');
                if (close instanceof HTMLElement) {
                    close.focus();
                }
            }
        });

        close?.addEventListener('click', () => {
            photo.setAttribute('aria-expanded', 'false');

            if (open instanceof HTMLElement) {
                open.focus();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closePhotos();
        }
    });

    window.addEventListener('online', () => {
        if (current.phase === 'pending') {
            syncNow();

            return;
        }

        render();
    });

    window.addEventListener('offline', () => {
        render();
    });

    function captureDefaults() {
        return {
            step: root.querySelector('[aria-current="step"]')?.dataset.step || 'repair',
            notes: [...root.querySelectorAll('[data-note]')].map((item) => ({
                id: item.dataset.note,
                text: item.querySelector('[data-note-text]')?.textContent?.trim() || '',
                meta: item.querySelector('[data-note-meta]')?.textContent?.trim() || 'Påhittad',
            })),
            photos: [],
            phase: 'idle',
        };
    }

    function markPending() {
        current.phase = 'pending';
        ensureDemoId();
        persist();
        render();
        ensureRetry();
    }

    function ensureRetry() {
        if (retryTimer) {
            return;
        }

        retryTimer = window.setInterval(() => {
            if (current.phase !== 'pending') {
                window.clearInterval(retryTimer);
                retryTimer = null;

                return;
            }

            if (navigator.onLine) {
                syncNow();
            }
        }, 1500);
    }

    async function syncNow() {
        if (!syncUrl || current.phase !== 'pending') {
            render();

            return;
        }

        if (!navigator.onLine) {
            render();

            return;
        }

        if (syncing) {
            resync = true;

            return;
        }

        syncing = true;
        setStatus('Synkar…', 'waiting');

        try {
            const response = await fetch(syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    demo_id: ensureDemoId(),
                    step: current.step,
                    notes: current.notes.map((note) => ({
                        id: note.id,
                        text: note.text,
                        meta: note.meta,
                    })),
                }),
            });

            if (!response.ok) {
                throw new Error('sync failed');
            }

            current.phase = 'synced';
            persist();
        } catch {
            current.phase = 'pending';
            persist();
        } finally {
            syncing = false;

            if (resync) {
                resync = false;
                syncNow();
            } else {
                render();
            }
        }
    }

    async function pull() {
        const demoId = readDemoId();

        if (!syncUrl || !demoId || current.phase === 'pending' || !navigator.onLine) {
            return;
        }

        try {
            const response = await fetch(`${syncUrl}?demo_id=${encodeURIComponent(demoId)}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const body = await response.json();

            if (!body.card) {
                return;
            }

            current.step = body.card.step;
            current.notes = Array.isArray(body.card.notes) ? body.card.notes : current.notes;
            current.phase = 'synced';
            persist();
            render();
        } catch {
            render();
        }
    }

    function render() {
        const stepIndex = Math.max(0, STEPS.indexOf(current.step));
        const status = root.querySelector('[data-status-label]');

        if (status) {
            status.textContent = STEP_LABELS[current.step] || current.step;
        }

        root.querySelectorAll('[data-step]').forEach((button) => {
            const index = STEPS.indexOf(button.dataset.step || '');
            button.classList.toggle('is-done', index > -1 && index < stepIndex);

            if (index === stepIndex) {
                button.setAttribute('aria-current', 'step');
            } else {
                button.removeAttribute('aria-current');
            }
        });

        const list = root.querySelector('[data-notes]');

        if (list) {
            list.replaceChildren();
            current.notes.forEach((note) => {
                const item = document.createElement('li');
                const text = document.createElement('p');
                const meta = document.createElement('p');
                text.textContent = note.text;
                meta.textContent = note.meta || 'Påhittad';
                meta.className = 'job-notes__meta';
                item.append(text, meta);
                list.append(item);
            });
        }

        const photos = root.querySelector('[data-user-photos]');

        if (photos) {
            photos.replaceChildren();
            current.photos.forEach((photo) => {
                if (typeof photo.src !== 'string' || !photo.src.startsWith('data:image/')) {
                    return;
                }

                const figure = document.createElement('figure');
                const image = document.createElement('img');
                const caption = document.createElement('figcaption');
                image.src = photo.src;
                image.alt = 'Tillagd bild';
                caption.textContent = 'Tillagd bild';
                figure.append(image, caption);
                photos.append(figure);
            });
        }

        if (!navigator.onLine && current.phase === 'pending') {
            setStatus('Sparat i kortet. Synkas när anslutningen kommer tillbaka.', 'waiting');
        } else if (!navigator.onLine) {
            setStatus('Ingen täckning. Exempeluppgifterna går att läsa och uppdatera.', 'offline');
        } else if (current.phase === 'synced') {
            setStatus('Synkat.', 'synced');
        } else if (current.phase === 'pending') {
            setStatus('Sparat i kortet. Synkas när anslutningen kommer tillbaka.', 'waiting');
        } else {
            setStatus('Exempel med testuppgifter. Kan öppnas utan täckning.', 'idle');
        }
    }

    function setStatus(text, state) {
        const status = root.querySelector('[data-sync-status]');

        if (!status) {
            return;
        }

        status.textContent = text;
        status.dataset.state = state;
    }

    function closePhotos() {
        root.querySelectorAll('[data-photo]').forEach((photo) => {
            photo.setAttribute('aria-expanded', 'false');
        });
    }

    function persist() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(current));
        } catch {
            current.photos = [];
        }
    }

    function readStored() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);

            if (!raw) {
                return null;
            }

            const parsed = JSON.parse(raw);

            if (!parsed || typeof parsed.step !== 'string' || !Array.isArray(parsed.notes)) {
                return null;
            }

            return {
                step: STEPS.includes(parsed.step) ? parsed.step : defaults.step,
                notes: parsed.notes
                    .filter((note) => note && typeof note.text === 'string')
                    .slice(0, 20)
                    .map((note) => ({
                        id: String(note.id || createId()).slice(0, 40),
                        text: note.text.slice(0, 280),
                        meta: String(note.meta || 'Påhittad').slice(0, 80),
                    })),
                photos: Array.isArray(parsed.photos) ? parsed.photos.slice(0, 3) : [],
                phase: parsed.phase === 'pending' || parsed.phase === 'synced' ? parsed.phase : 'idle',
            };
        } catch {
            return null;
        }
    }

    function ensureDemoId() {
        const existing = readDemoId();

        if (existing) {
            return existing;
        }

        const created = createId();

        try {
            localStorage.setItem(ID_KEY, created);
        } catch {
            // The id still works for this visit.
        }

        return created;
    }

    function readDemoId() {
        try {
            const value = localStorage.getItem(ID_KEY);

            return value && /^[0-9a-f-]{36}$/i.test(value) ? value : null;
        } catch {
            return null;
        }
    }

    function createId() {
        if (window.crypto?.randomUUID) {
            return crypto.randomUUID();
        }

        const bytes = new Uint8Array(16);
        crypto.getRandomValues(bytes);
        bytes[6] = (bytes[6] & 0x0f) | 0x40;
        bytes[8] = (bytes[8] & 0x3f) | 0x80;

        const hex = [...bytes].map((byte) => byte.toString(16).padStart(2, '0')).join('');

        return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
    }

    function clone(value) {
        return JSON.parse(JSON.stringify(value));
    }

    function shrinkImage(file) {
        return new Promise((resolve, reject) => {
            const image = new Image();
            const url = URL.createObjectURL(file);

            image.onload = () => {
                const maxEdge = 480;
                const scale = Math.min(1, maxEdge / Math.max(image.width, image.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(image.width * scale));
                canvas.height = Math.max(1, Math.round(image.height * scale));
                const context = canvas.getContext('2d');

                if (!context) {
                    URL.revokeObjectURL(url);
                    reject(new Error('canvas'));

                    return;
                }

                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                URL.revokeObjectURL(url);
                resolve(canvas.toDataURL('image/jpeg', 0.72));
            };

            image.onerror = () => {
                URL.revokeObjectURL(url);
                reject(new Error('image'));
            };

            image.src = url;
        });
    }
}
