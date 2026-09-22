(() => {
    const form = document.querySelector('[data-answer-form]');
    if (!form || !window.fetch) return;

    const button = form.querySelector('button[type="submit"]');
    const textarea = form.querySelector('[name="answer_text"]');
    const radios = [...form.querySelectorAll('[name="selected_option_id"]')];
    const version = form.querySelector('[name="question_version"]').value;
    const type = textarea ? 'open' : 'multiple_choice';
    const key = form.dataset.draftKey;

    let busy = false;
    let needsReload = false;

    const notice = document.createElement('div');
    notice.className = 'notice notice-error';
    notice.hidden = true;
    notice.style.display = 'none';
    notice.tabIndex = -1;
    notice.setAttribute('role', 'alert');

    notice.innerHTML = `
        <h2></h2>
        <p>Je invoer blijft op deze pagina staan.</p>
        <ul></ul>
        <p class="submitted-answer" hidden></p>
        <a class="button button-secondary" hidden>
            Pagina opnieuw openen
        </a>
    `;

    form.prepend(notice);

    const reloadLink = notice.querySelector('a');
    reloadLink.href = window.location.href;
    reloadLink.style.display = 'none';

    function readInput() {
        const selected = radios.find((radio) => radio.checked);

        return {
            type,
            version,
            value: textarea ? textarea.value : (selected?.value ?? ''),
            label: textarea
                ? textarea.value
                : (selected?.closest('label')?.textContent.trim() ?? ''),
        };
    }

    function saveDraft() {
        try {
            sessionStorage.setItem(key, JSON.stringify(readInput()));
            return true;
        } catch {
            return false;
        }
    }

    function showNotice(title, messages, reload = false, previous = '') {
        needsReload = reload;

        notice.querySelector('h2').textContent = title;

        const list = notice.querySelector('ul');
        list.replaceChildren();

        if (reload && !saveDraft()) {
            messages = [
                ...messages,
                'Kopieer je antwoord voordat je de pagina opnieuw opent.',
            ];
        }

        for (const message of messages) {
            const item = document.createElement('li');
            item.textContent = message;
            list.append(item);
        }

        const preview = notice.querySelector('.submitted-answer');
        preview.hidden = !previous;
        preview.textContent = previous ? `Vorige invoer: ${previous}` : '';

        reloadLink.hidden = !reload;
        reloadLink.style.display = reload ? '' : 'none';

        notice.hidden = false;
        notice.style.display = '';

        button.textContent = 'Opnieuw versturen';
        button.disabled = reload;

        notice.focus();
    }

    try {
        const draft = JSON.parse(sessionStorage.getItem(key));

        if (
            draft &&
            typeof draft.value === 'string' &&
            draft.value &&
            !readInput().value
        ) {
            if (draft.type === type && type === 'open') {
                textarea.value = draft.value;

                showNotice('Invoer teruggezet', [
                    'Controleer de vraag en je antwoord voordat je opnieuw verstuurt.',
                ]);
            } else if (
                draft.type === type &&
                draft.version === version &&
                radios.some((radio) => radio.value === draft.value)
            ) {
                radios.forEach((radio) => {
                    radio.checked = radio.value === draft.value;
                });

                showNotice('Invoer teruggezet', [
                    'Controleer je keuze voordat je opnieuw verstuurt.',
                ]);
            } else {
                showNotice(
                    'De vraag is gewijzigd',
                    ['Lees de vraag opnieuw en vul je antwoord opnieuw in.'],
                    false,
                    String(draft.label || draft.value)
                );
            }
        }
    } catch {
        // Invoer op de huidige pagina blijft bruikbaar zonder browseropslag.
    }

    form.addEventListener('input', saveDraft);
    form.addEventListener('change', saveDraft);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (busy || needsReload || !form.reportValidity()) return;

        saveDraft();

        const body = new FormData(form);

        const controls = [...form.elements].filter(
            (control) => !control.disabled
        );

        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 20000);

        busy = true;

        controls.forEach((control) => {
            control.disabled = true;
        });

        button.textContent = 'Versturen…';
        notice.hidden = true;
        notice.style.display = 'none';

        form.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                },
                signal: controller.signal,
            });

            const data = await response.json().catch(() => null);

            if (response.ok && typeof data?.redirect === 'string') {
                const target = new URL(
                    data.redirect,
                    window.location.href
                );

                if (target.origin !== window.location.origin) {
                    throw new Error('Unexpected redirect');
                }

                try {
                    sessionStorage.removeItem(key);
                } catch {
                    // Geen browseropslag beschikbaar.
                }

                window.location.assign(target.href);
                return;
            }

            if (response.status === 422 && data?.errors) {
                const messages = Object.values(data.errors)
                    .flat()
                    .filter((message) => typeof message === 'string');

                showNotice(
                    'Niet opgeslagen',
                    messages,
                    Boolean(
                        data.errors.student ||
                        data.errors.question_version
                    )
                );
            } else if (
                response.status === 419 ||
                response.status === 401
            ) {
                showNotice(
                    'Niet opgeslagen',
                    ['Je sessie is verlopen. Open de pagina opnieuw.'],
                    true
                );
            } else if (response.status === 429) {
                showNotice('Niet opgeslagen', [
                    'Je verstuurt te snel. Wacht even en probeer opnieuw.',
                ]);
            } else if (response.status === 404) {
                showNotice(
                    'Vraag niet gevonden',
                    ['Deze vraag is niet meer beschikbaar.'],
                    true
                );
            } else {
                throw new Error('No save confirmation');
            }
        } catch {
            showNotice('Opslaan niet bevestigd', [
                'We konden niet bevestigen of je antwoord is opgeslagen. Controleer je verbinding en probeer opnieuw.',
                'Een antwoord dat al is opgeslagen, wordt niet opnieuw toegevoegd.',
            ]);
        } finally {
            clearTimeout(timeout);
            busy = false;

            controls.forEach((control) => {
                control.disabled = false;
            });

            button.disabled = needsReload;
            form.removeAttribute('aria-busy');
        }
    });
})();
