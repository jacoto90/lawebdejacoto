(() => {
    'use strict';
    document.documentElement.classList.add('js-studio');
    const forms = [...document.querySelectorAll('[data-editor-form]')];
    const states = new Map();
    const user = document.body.dataset.user;
    const storagePrefix = `jacoto-studio:${user}:`;
    const metadata = new Set(['csrf_token', 'entity', 'id', 'revision', 'request_id', 'is_new', 'operation']);
    const globalState = document.getElementById('globalSaveState');
    const saveAllButton = document.getElementById('saveAll');
    const notice = document.getElementById('studioNotice');
    const requestId = () => crypto.randomUUID ? crypto.randomUUID() : Array.from(crypto.getRandomValues(new Uint8Array(16)), b => b.toString(16).padStart(2, '0')).join('');
    const values = form => Object.fromEntries([...new FormData(form)].filter(([key]) => !metadata.has(key)));
    const equal = (a, b) => JSON.stringify(a) === JSON.stringify(b);
    const dirty = state => !equal(values(state.form), state.baseline);
    const pending = state => dirty(state) || !!state.retry;
    const store = (key, value) => { try { value === null ? localStorage.removeItem(key) : localStorage.setItem(key, JSON.stringify(value)); } catch (_) { /* Server saving still works when browser storage is unavailable. */ } };
    const showNotice = (text, error = false) => { if (notice) { notice.textContent = text; notice.hidden = !text; notice.classList.toggle('is-error', error); } };

    function updateGlobal() {
        const all = [...states.values()];
        const unsaved = all.filter(pending);
        const saving = all.some(s => s.saving);
        const failed = unsaved.some(s => s.error);
        if (globalState) {
            globalState.dataset.state = failed ? 'error' : saving ? 'saving' : unsaved.length ? 'pending' : 'saved';
            globalState.querySelector('span').textContent = failed ? 'Revisar cambios' : saving ? 'Guardando…' : unsaved.length ? `${unsaved.length} sin guardar` : 'Todo guardado';
        }
        if (saveAllButton) saveAllButton.disabled = !unsaved.length && !saving;
        all.forEach(s => s.card.classList.toggle('is-dirty', pending(s)));
    }

    function status(state, text, type = '') {
        const node = state.form.querySelector('[data-form-status]');
        node.textContent = text;
        node.dataset.state = type;
    }

    function persist(state) {
        const current = values(state.form);
        if (equal(current, state.baseline) && !state.retry) { store(state.key, null); return; }
        store(state.key, { values: current, revision: state.revision, id: state.form.elements.id.value, isNew: state.form.elements.is_new.value, retry: state.retry, at: Date.now() });
    }

    function selectLanguage(form, language) {
        form.querySelectorAll('[data-editor-lang]').forEach(button => {
            const active = button.dataset.editorLang === language;
            button.setAttribute('aria-selected', String(active));
            button.tabIndex = active ? 0 : -1;
        });
        form.querySelectorAll('[data-language-panel]').forEach(panel => { panel.hidden = panel.dataset.languagePanel !== language; });
    }

    function clearErrors(state) {
        state.error = false;
        state.form.querySelector('[data-form-error]').hidden = true;
        state.form.querySelector('.recovery-actions').hidden = true;
        state.form.querySelectorAll('[aria-invalid]').forEach(input => input.removeAttribute('aria-invalid'));
        state.form.querySelectorAll('.field-error').forEach(node => { node.textContent = ''; });
    }

    function reportError(state, message, fields = {}, code = 0) {
        state.error = true;
        state.conflict = code === 409;
        const box = state.form.querySelector('[data-form-error]');
        box.textContent = message;
        box.hidden = false;
        state.form.querySelector('.recovery-actions').hidden = false;
        state.form.querySelector('[data-login]').hidden = code !== 401;
        Object.entries(fields).forEach(([name, text]) => {
            const field = [...state.form.querySelectorAll('[data-field]')].find(node => node.dataset.field === name);
            if (!field) return;
            field.querySelector('input,textarea')?.setAttribute('aria-invalid', 'true');
            field.querySelector('.field-error').textContent = text;
        });
        status(state, code === 409 ? 'Con otra versión pendiente' : 'No se ha guardado', 'error');
        persist(state);
        updateGlobal();
    }

    function valid(state, interactive) {
        const invalid = [...state.form.querySelectorAll('input,textarea')].find(input => !input.checkValidity());
        if (!invalid) return true;
        status(state, 'Completa los campos obligatorios', 'pending');
        if (interactive) {
            state.card.open = true;
            const panel = invalid.closest('[data-language-panel]');
            if (panel) selectLanguage(state.form, panel.dataset.languagePanel);
            reportError(state, 'Completa los campos obligatorios antes de cerrar este bloque.', { [invalid.name]: invalid.validationMessage });
            invalid.focus();
            invalid.reportValidity();
        }
        return false;
    }

    function payloadFor(state, snapshot, operation = 'save') {
        return {
            ...snapshot, entity: state.form.elements.entity.value, id: state.form.elements.id.value,
            revision: String(state.revision), is_new: state.form.elements.is_new.value,
            request_id: requestId(), operation,
        };
    }

    async function send(state, payload, keepalive = false) {
        const body = new URLSearchParams({ ...payload, csrf_token: state.form.elements.csrf_token.value });
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 15000);
        try {
            const response = await fetch('/admin/save.php', { method: 'POST', body, headers: { Accept: 'application/json' }, credentials: 'same-origin', signal: controller.signal, keepalive });
            let data;
            try { data = await response.json(); } catch (_) { throw new Error('El servidor no ha confirmado el guardado. Puedes volver a intentarlo.'); }
            if (!response.ok || !data.ok) {
                const error = new Error(data.message || 'No se ha podido guardar.');
                error.code = response.status;
                error.fields = data.fields || {};
                throw error;
            }
            return data;
        } finally { clearTimeout(timeout); }
    }

    function updateCard(state, data) {
        if (data.title) {
            state.card.querySelector(':scope > summary [data-card-title]').textContent = data.title.length > 100 ? data.title.slice(0, 97) + '…' : data.title;
            state.card.dataset.search = data.title.toLocaleLowerCase('es');
        }
        if (data.active !== undefined) {
            state.card.dataset.active = String(data.active);
            const badge = state.card.querySelector(':scope > summary [data-visibility-badge]');
            if (badge) { badge.textContent = data.active ? 'Publicado' : 'Oculto'; badge.classList.toggle('is-hidden', !data.active); }
        }
        state.card.classList.remove('new-card');
        state.form.querySelectorAll('[data-delete],[data-add-another]').forEach(button => { button.hidden = false; });
        const link = state.form.querySelector('[data-created-link]');
        if (link) {
            const url = new URL(link.href);
            url.searchParams.set('open', data.id);
            link.href = url.href;
            link.hidden = false;
        }
    }

    async function flush(state, interactive = false) {
        clearTimeout(state.timer);
        if (state.removing) return false;
        if (state.promise) {
            const done = await state.promise;
            if (!done) return false;
            return dirty(state) ? flush(state, interactive) : true;
        }
        if (!dirty(state) && !state.retry) return true;
        if (!state.retry && !valid(state, interactive)) return false;
        state.promise = (async () => {
            state.saving = true;
            clearErrors(state);
            updateGlobal();
            try {
                while (dirty(state) || state.retry) {
                    if (!state.retry && !valid(state, interactive)) return false;
                    const snapshot = state.retry ? Object.fromEntries(Object.entries(state.retry).filter(([key]) => !metadata.has(key))) : values(state.form);
                    const payload = state.retry || payloadFor(state, snapshot);
                    state.retry = payload;
                    persist(state);
                    status(state, 'Guardando…', 'saving');
                    const data = await send(state, payload);
                    state.revision = data.revision;
                    state.pageRevision = data.revision;
                    state.form.elements.revision.value = String(data.revision);
                    state.form.elements.is_new.value = '0';
                    const savedKey = storagePrefix + state.form.elements.entity.value + ':' + data.id;
                    if (state.key !== savedKey) { store(state.key, null); state.key = savedKey; }
                    state.baseline = snapshot;
                    state.retry = null;
                    state.form.querySelector('[data-save]').textContent = 'Guardar';
                    updateCard(state, data);
                    persist(state);
                    status(state, 'Guardado · ' + new Intl.DateTimeFormat('es', { hour: '2-digit', minute: '2-digit' }).format(new Date()), 'saved');
                }
                return true;
            } catch (error) {
                if (error.code && error.code < 500) state.retry = null;
                reportError(state, error.name === 'AbortError' ? 'El servidor tarda en responder. Tus cambios están conservados; vuelve a guardar.' : error.message, error.fields, error.code);
                return false;
            } finally {
                state.saving = false;
                updateGlobal();
            }
        })();
        try { return await state.promise; } finally { state.promise = null; }
    }

    async function flushAll(targetForms = forms) {
        let ok = true;
        // One request at a time also cooperates with PHP's session locking.
        for (const form of targetForms) {
            const state = states.get(form);
            if (state && !(await flush(state, true))) { state.card.open = true; ok = false; }
        }
        if (!ok) showNotice('Hay cambios sin guardar. Revisa el bloque marcado antes de continuar.', true);
        else showNotice('');
        return ok;
    }

    function applyValues(form, data) {
        [...form.querySelectorAll('input:not([type=hidden]),textarea')].forEach(input => {
            if (!Object.hasOwn(data, input.name)) return;
            if (input.type === 'checkbox') input.checked = String(data[input.name]) === '1';
            else input.value = data[input.name];
        });
    }

    // A create may have reached the server even if its response was lost.
    forms.filter(form => form.elements.is_new.value === '1').forEach(form => {
        const key = storagePrefix + form.dataset.draftKey;
        try {
            const draft = JSON.parse(localStorage.getItem(key));
            const existing = draft && forms.find(candidate => candidate.elements.is_new.value === '0' && candidate.elements.entity.value === form.elements.entity.value && candidate.elements.id.value === draft.id);
            if (!existing) return;
            const existingKey = storagePrefix + existing.dataset.draftKey;
            const newer = JSON.parse(localStorage.getItem(existingKey));
            if (!newer || newer.at < draft.at) store(existingKey, { ...draft, isNew: '0' });
            store(key, null);
        } catch (_) { /* Draft recovery remains optional. */ }
    });

    forms.forEach(form => {
        const state = { form, card: form.closest('[data-editor-card]'), baseline: values(form), revision: Number(form.elements.revision.value), pageRevision: Number(form.elements.revision.value), key: storagePrefix + form.dataset.draftKey, retry: null, promise: null, timer: null, saving: false, error: false };
        states.set(form, state);
        selectLanguage(form, 'es');
        try {
            const draft = JSON.parse(localStorage.getItem(state.key));
            if (draft?.values && Date.now() - draft.at < 30 * 86400000) {
                applyValues(form, draft.values);
                if (dirty(state) || draft.retry) {
                    if (form.elements.is_new.value === '1') { form.elements.id.value = draft.id; form.elements.is_new.value = draft.isNew; }
                    state.revision = draft.revision;
                    form.elements.revision.value = String(draft.revision);
                    state.retry = draft.retry || null;
                    state.card.open = true;
                    status(state, 'Borrador recuperado · pendiente', 'pending');
                    showNotice('Hemos recuperado cambios pendientes de este navegador. Revísalos y pulsa Guardar cambios.');
                } else store(state.key, null);
            }
        } catch (_) { /* Ignore a malformed or inaccessible local draft. */ }

        const changed = () => {
            if (state.error) clearErrors(state);
            persist(state);
            status(state, dirty(state) ? 'Cambios pendientes…' : 'Sin cambios pendientes', dirty(state) ? 'pending' : 'saved');
            updateGlobal();
            clearTimeout(state.timer);
            state.timer = setTimeout(() => flush(state), 800);
        };
        form.addEventListener('input', changed);
        form.addEventListener('change', changed);
        form.addEventListener('focusout', event => {
            if (event.target.matches('input,textarea') && dirty(state)) {
                clearTimeout(state.timer);
                state.timer = setTimeout(() => flush(state), 120);
            }
        });
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (event.submitter?.hasAttribute('data-delete')) { await remove(state); return; }
            await flush(state, true);
        });
        form.querySelectorAll('[data-editor-lang]').forEach(button => {
            button.addEventListener('click', () => selectLanguage(form, button.dataset.editorLang));
            button.addEventListener('keydown', event => {
                if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
                event.preventDefault();
                const lang = event.key === 'Home' ? 'es' : event.key === 'End' ? 'en' : button.dataset.editorLang === 'es' ? 'en' : 'es';
                selectLanguage(form, lang);
                form.querySelector(`[data-editor-lang="${lang}"]`).focus();
            });
        });
        form.querySelector('[data-retry]').addEventListener('click', () => flush(state, true));
        form.querySelector('[data-discard]').addEventListener('click', async () => {
            clearTimeout(state.timer);
            if (state.saving) return;
            store(state.key, null);
            state.retry = null;
            applyValues(form, state.baseline);
            state.revision = state.pageRevision;
            form.elements.revision.value = String(state.revision);
            clearErrors(state);
            status(state, 'Borrador descartado. Recarga para ver la última versión.');
            updateGlobal();
            if (state.conflict && await flushAll()) location.reload();
        });
    });

    async function closeCard(card) {
        if (await flushAll([...card.querySelectorAll('[data-editor-form]')])) {
            card.open = false;
            card.querySelector(':scope > summary').focus({ preventScroll: true });
        }
    }

    async function remove(state) {
        clearTimeout(state.timer);
        const dialog = document.getElementById('studioDeleteDialog');
        if (!dialog) return;
        dialog.returnValue = 'cancel';
        dialog.showModal();
        const result = await new Promise(resolve => dialog.addEventListener('close', () => resolve(dialog.returnValue), { once: true }));
        if (result !== 'delete') return;
        const affected = [...state.card.querySelectorAll('[data-editor-form]')].map(form => states.get(form)).filter(Boolean);
        affected.forEach(child => { clearTimeout(child.timer); child.removing = true; });
        await Promise.all(affected.map(child => child.promise));
        try {
            state.saving = true;
            status(state, 'Eliminando…', 'saving');
            await send(state, payloadFor(state, {}, 'delete'));
            state.card.querySelectorAll('[data-editor-form]').forEach(form => {
                const child = states.get(form);
                if (child) { clearTimeout(child.timer); store(child.key, null); states.delete(form); }
            });
            state.card.remove();
            showNotice('Bloque eliminado.');
        } catch (error) { reportError(state, error.message, error.fields, error.code); }
        finally { affected.forEach(child => { child.removing = false; }); state.saving = false; updateGlobal(); }
    }

    document.addEventListener('click', async event => {
        const closeButton = event.target.closest('[data-close-editor]');
        if (closeButton) { event.preventDefault(); await closeCard(closeButton.closest('[data-editor-card]')); return; }
        const summary = event.target.closest('[data-editor-card] > summary');
        if (summary?.parentElement.open) { event.preventDefault(); await closeCard(summary.parentElement); return; }
        const add = event.target.closest('[data-new-record]');
        if (add) {
            event.preventDefault();
            const search = document.getElementById('studioSearch');
            if (search) { search.value = ''; filterCards('all'); }
            const card = document.querySelector('#newRecord [data-editor-card]');
            if (card) { card.hidden = false; card.open = true; card.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth', block: 'start' }); card.querySelector('input:not([type=hidden]):not([type=checkbox]),textarea')?.focus({ preventScroll: true }); }
            return;
        }
        const link = event.target.closest('a[href]');
        if (!link || link.target === '_blank' || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const url = new URL(link.href, location.href);
        if (url.origin === location.origin && url.pathname.startsWith('/admin/') && (url.href !== location.href || url.search)) {
            event.preventDefault();
            if (await flushAll()) location.assign(url.href);
        }
    });

    document.querySelector('[data-logout]')?.addEventListener('submit', async event => {
        event.preventDefault();
        if (await flushAll()) event.target.submit();
    });
    saveAllButton?.addEventListener('click', () => flushAll());
    document.addEventListener('keydown', event => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') { event.preventDefault(); flushAll(); }
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); document.getElementById('studioSearch')?.focus(); }
    });
    window.addEventListener('beforeunload', event => {
        if (![...states.values()].some(state => pending(state) || state.saving)) return;
        event.preventDefault();
        event.returnValue = '';
    });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') states.forEach(state => { persist(state); if (pending(state)) flush(state); });
    });
    window.addEventListener('pagehide', () => {
        states.forEach(state => {
            if (!pending(state)) return;
            persist(state);
            if (state.saving || !valid(state, false)) return;
            const payload = state.retry || payloadFor(state, values(state.form));
            state.retry = payload;
            persist(state);
            // Best effort only: the durable local draft remains until a confirmed save.
            const body = new URLSearchParams({ ...payload, csrf_token: state.form.elements.csrf_token.value });
            if (new Blob([body.toString()]).size < 60000) fetch('/admin/save.php', { method: 'POST', body, headers: { Accept: 'application/json' }, credentials: 'same-origin', keepalive: true }).catch(() => {});
        });
    });

    let activeFilter = 'all';
    function filterCards(next = activeFilter) {
        activeFilter = next;
        const query = (document.getElementById('studioSearch')?.value || '').trim().toLocaleLowerCase('es');
        let visible = 0;
        document.querySelectorAll('[data-filter]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.filter === next)));
        document.querySelectorAll('#editorList > [data-editor-card]').forEach(card => {
            const pending = [...card.querySelectorAll('[data-editor-form]')].some(form => states.has(form) && dirty(states.get(form)));
            const match = (!query || card.dataset.search.includes(query)) && (next === 'all' || card.dataset.active === next);
            card.hidden = !match && !pending;
            if (!card.hidden) visible++;
        });
        const empty = document.getElementById('studioEmpty');
        if (empty) empty.hidden = visible > 0 || !query && next === 'all';
    }
    document.getElementById('studioSearch')?.addEventListener('input', () => filterCards());
    document.querySelectorAll('[data-filter]').forEach(button => button.addEventListener('click', () => filterCards(button.dataset.filter)));

    const sidebar = document.getElementById('studioSidebar');
    const menuButton = document.getElementById('studioMenu');
    const backdrop = document.querySelector('.sidebar-backdrop');
    function setSidebar(open) {
        sidebar?.classList.toggle('is-open', open);
        menuButton?.setAttribute('aria-expanded', String(open));
        if (backdrop) backdrop.hidden = !open;
        document.body.style.overflow = open ? 'hidden' : '';
        if (open) sidebar.querySelector('a')?.focus();
        else menuButton?.focus();
    }
    menuButton?.addEventListener('click', () => setSidebar(menuButton.getAttribute('aria-expanded') !== 'true'));
    backdrop?.addEventListener('click', () => setSidebar(false));
    document.addEventListener('keydown', event => {
        if (!sidebar?.classList.contains('is-open')) return;
        if (event.key === 'Escape') setSidebar(false);
        if (event.key === 'Tab') {
            const nodes = [...sidebar.querySelectorAll('a,button')];
            if (event.shiftKey && document.activeElement === nodes[0]) { event.preventDefault(); nodes.at(-1).focus(); }
            if (!event.shiftKey && document.activeElement === nodes.at(-1)) { event.preventDefault(); nodes[0].focus(); }
        }
    });
    matchMedia('(min-width: 761px)').addEventListener('change', event => {
        if (event.matches && sidebar?.classList.contains('is-open')) setSidebar(false);
    });
    document.querySelectorAll('[data-password-toggle]').forEach(button => button.addEventListener('click', () => {
        const input = button.parentElement.querySelector('input');
        input.type = input.type === 'password' ? 'text' : 'password';
        button.textContent = input.type === 'password' ? 'Ver' : 'Ocultar';
        button.setAttribute('aria-pressed', String(input.type === 'text'));
    }));
    if (location.hash === '#newRecord') {
        const card = document.querySelector('#newRecord [data-editor-card]');
        if (card) card.open = true;
    }
    updateGlobal();
})();
