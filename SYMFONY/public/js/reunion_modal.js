/**
 * reunion_modal.js  →  public/js/reunion_modal.js
 *
 * Gère deux modes dans le même rm-overlay :
 *   • Création  : [data-rm-open] → restaure le contenu pré-rendu
 *   • Édition   : [data-rm-edit-url="..."] → charge le formulaire en AJAX
 */

(function () {
    'use strict';

    const overlay = document.getElementById('rm-overlay');
    if (!overlay) return;

    const dialog = overlay.querySelector('.rm-dialog');
    let createContent = null; // snapshot du contenu de création

    /* ── Ouvrir / fermer ─────────────────────────────────────────── */
    function openModal() {
        overlay.classList.add('rm-overlay--open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        const first = dialog.querySelector('input:not([type=hidden]), textarea, select');
        if (first) setTimeout(() => first.focus(), 120);
    }

    function closeModal() {
        overlay.classList.remove('rm-overlay--open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    /* ── Spinner pendant le chargement AJAX ──────────────────────── */
    function setLoading() {
        dialog.innerHTML = `
            <div class="rm-dialog__head" style="background:#0F6E56;">
                <div class="rm-dialog__head-left">
                    <span class="rm-dialog__head-icon" style="color:#9FE1CB">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                    </span>
                    <h2 class="rm-dialog__title" style="color:#9FE1CB">Chargement…</h2>
                </div>
                <button type="button" class="rm-dialog__close" data-rm-close aria-label="Fermer"
                        style="color:#9FE1CB">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="rm-dialog__body" style="align-items:center;justify-content:center;min-height:200px;">
                <div class="rm-spinner"></div>
            </div>`;
    }

    /* ── Injecter le HTML et rebinder les événements ─────────────── */
    function injectContent(html) {
        dialog.innerHTML = html;
        bindToggle();
        bindFormSubmit();
    }

    /* ── Toggle salle / en-ligne ─────────────────────────────────── */
    function bindToggle() {
        const checkbox   = dialog.querySelector('input[type="checkbox"]');
        const salleField = dialog.querySelector('#rm-salle-field');
        if (!checkbox || !salleField) return;
        function update() { salleField.classList.toggle('rm-field--disabled', checkbox.checked); }
        checkbox.addEventListener('change', update);
        update();
    }

    /* ── Soumission AJAX ─────────────────────────────────────────── */
    function validateReunionForm(form) {
        let valid = true;
        const errors = [];

        // Titre required
        const titre = form.querySelector('[name$="[titre]"]');
        if (!titre || !titre.value.trim()) {
            titre.classList.add('rm-field--error');
            errors.push('Le titre est requis.');
            valid = false;
        } else {
            titre.classList.remove('rm-field--error');
        }

        // Dates & times - debut > today, fin > debut
        const today = new Date();
        today.setHours(0,0,0,0);
        const debutDate = form.querySelector('[name$="[debut_date]"]');
        const debutTime = form.querySelector('[name$="[debut_time]"]');
        const finDate = form.querySelector('[name$="[fin_date]"]');
        const finTime = form.querySelector('[name$="[fin_time]"]');

        if (debutDate && debutTime && finDate && finTime) {
            const debut = new Date(debutDate.value + 'T' + debutTime.value);
            const fin = new Date(finDate.value + 'T' + finTime.value);
            if (debut <= today) {
                [debutDate, debutTime].forEach(f => f.classList.add('rm-field--error'));
                errors.push('La date de début doit être après aujourd\'hui.');
                valid = false;
            } else if (debut >= fin) {
                [debutDate, debutTime, finDate, finTime].forEach(f => f.classList.add('rm-field--error'));
                errors.push('La date/heure de fin doit être après le début.');
                valid = false;
            } else {
                [debutDate, debutTime, finDate, finTime].forEach(f => f.classList.remove('rm-field--error'));
            }
        }

        // Salle if not enLigne
        const enLigne = form.querySelector('[name$="[enLigne]"]');
        const salle = form.querySelector('[name$="[idSalle]"]');
        if (enLigne && !enLigne.checked && salle && !salle.value) {
            salle.classList.add('rm-field--error');
            errors.push('Choisissez une salle pour une réunion présentielle.');
            valid = false;
        } else if (salle) {
            salle.classList.remove('rm-field--error');
        }

        // Show errors
        const errEl = form.querySelector('#rm-form-errors');
        if (errEl) {
            errEl.textContent = errors.join(' ');
            errEl.hidden = valid;
        }

        return valid;
    }

    function bindFormSubmit() {
        const form = dialog.querySelector('#reunion-modal-form');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (!validateReunionForm(form)) return;

            const btn     = form.querySelector('[type="submit"]');
            const origHtml = btn ? btn.innerHTML : '';
            if (btn) { btn.disabled = true; btn.innerHTML = '…'; }

            try {
                const res  = await fetch(form.action, {
                    method:  'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body:    new FormData(form),
                });
                const json = await res.json().catch(() => null);

                if (json && json.success) {
                    const ok = dialog.querySelector('#rm-form-success');
                    if (ok) { ok.textContent = 'Réunion sauvegardée !'; ok.hidden = false; }
                    setTimeout(() => { closeModal(); window.location.href = json.redirect ?? location.href; }, 700);
                } else if (json && json.formHtml) {
                    injectContent(json.formHtml);
                } else {
                    const err = dialog.querySelector('#rm-form-errors');
                    if (err) { err.textContent = (json && json.message) || 'Veuillez vérifier les champs.'; err.hidden = false; }
                }
            } catch (e) {
                console.error('Reunion modal:', e);
            } finally {
                if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            }
        });
    }

    /* ── Mode Création : restaure le snapshot initial ────────────── */
    function openCreate() {
        if (createContent) dialog.innerHTML = createContent;
        bindToggle();
        bindFormSubmit();
        openModal();
    }

    /* ── Mode Édition : charge le formulaire via AJAX ─────────────────── */
    async function openEdit(url) {
        if (!url) {
            console.warn('openEdit: URL vide ou non définie');
            return;
        }
        setLoading();
        openModal();
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) {
                const body = dialog.querySelector('.rm-dialog__body');
                if (body) body.innerHTML =
                    `<div style="padding:1.5rem;text-align:center">
                        <p style="color:#b91c1c;font-weight:600;margin-bottom:.5rem">⚠️ Erreur ${res.status}</p>
                        <p style="color:#6b7280;font-size:.85rem">Impossible de charger le formulaire.<br>Vérifiez que vous êtes bien connecté.</p>
                        <button onclick="window.rmCloseModal()" style="margin-top:.75rem;padding:.5rem 1.25rem;border-radius:8px;border:none;background:#f3f4f6;cursor:pointer;font-weight:600;color:#374151">Fermer</button>
                    </div>`;
                return;
            }
            const json = await res.json();
            if (json && json.formHtml) {
                injectContent(json.formHtml);
            } else {
                const body = dialog.querySelector('.rm-dialog__body');
                if (body) body.innerHTML =
                    `<div style="padding:1.5rem;text-align:center">
                        <p style="color:#b91c1c;font-weight:600">⚠️ Formulaire non disponible</p>
                        <p style="color:#6b7280;font-size:.85rem">La réponse du serveur est invalide.</p>
                        <button onclick="window.rmCloseModal()" style="margin-top:.75rem;padding:.5rem 1.25rem;border-radius:8px;border:none;background:#f3f4f6;cursor:pointer;font-weight:600;color:#374151">Fermer</button>
                    </div>`;
            }
        } catch (err) {
            console.error('Edit load error:', err);
            const body = dialog.querySelector('.rm-dialog__body');
            if (body) body.innerHTML =
                `<div style="padding:1.5rem;text-align:center">
                    <p style="color:#b91c1c;font-weight:600">⚠️ Erreur de connexion</p>
                    <p style="color:#6b7280;font-size:.85rem">${err.message || 'Impossible de joindre le serveur.'}</p>
                    <button onclick="window.rmCloseModal()" style="margin-top:.75rem;padding:.5rem 1.25rem;border-radius:8px;border:none;background:#f3f4f6;cursor:pointer;font-weight:600;color:#374151">Fermer</button>
                </div>`;
        }
    }

    /* ── Délégation d'événements ─────────────────────────────────── */
    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-rm-open]'))      { openCreate(); return; }
        const editBtn = e.target.closest('[data-rm-edit-url]');
        if (editBtn) { 
            e.preventDefault();
            openEdit(editBtn.dataset.rmEditUrl); 
            return; 
        }
        if (e.target.closest('[data-rm-close]'))     { closeModal(); return; }
    });

    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && overlay.classList.contains('rm-overlay--open')) closeModal();
    });

    /* ── API publique ────────────────────────────────────────────── */
    window.rmOpenModal  = openCreate;
    window.rmCloseModal = closeModal;
    window.rmOpenEdit   = openEdit;
    window.rmPreFillDate = function (dateStr) {
        const d1 = dialog.querySelector('[name$="[debut_date]"]');
        const d2 = dialog.querySelector('[name$="[fin_date]"]');
        if (d1) d1.value = dateStr;
        if (d2) d2.value = dateStr;
    };

    /* ── Init ────────────────────────────────────────────────────── */
    createContent = dialog.innerHTML;  // snapshot du contenu de création
    bindToggle();
    bindFormSubmit();

})();