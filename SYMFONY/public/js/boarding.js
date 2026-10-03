/**
 * boarding.js — Shared JS for Onboarding & Offboarding pages.
 *
 * Globals injected by the Twig template:
 *   GENERATE_URL    — Symfony route for /generate
 *   BOARDING_MODE   — 'onboarding' | 'offboarding'
 *   TOGGLE_URL_BASE — base for /task/{id}/toggle
 *   MARK_ALL_URL    — base for /{id}/mark-all
 *   PUBLISH_URL     — base for /{id}/publish-post
 *   LOAD_URL_BASE   — base for /load/{utilisateurId}
 */

'use strict';

// ── State ─────────────────────────────────────────────────────────────────
let currentTasks        = [];
let currentBoardingId   = null;  // DB id of the active Onboarding or Offboarding record
let currentEmployeeInfo = null;  // Last rendered employee info

// ── DOM references ────────────────────────────────────────────────────────
const $           = id => document.getElementById(id);
const generateBtn       = $('generateBtn');
const publishPostBtn    = $('publishPostBtn');
const taskPanel         = $('taskPanel');
const statsRow          = $('statsRow');
const employeeCard      = $('employeeCard');
const employeeCardBody  = $('employeeCardBody');
const autoActionsCard   = $('autoActionsCard');
const autoActionsBody   = $('autoActionsBody');
const autoActionsFooter = $('autoActionsFooter');
const alertBox          = $('alertBox');
const employeeSelect    = $('employeeSelect');

// Accent colour for the progress bar
const ACCENT = BOARDING_MODE === 'onboarding' ? '#667eea' : '#ef4444';

// ── Bootstrap ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    generateBtn.addEventListener('click', handleGenerate);

    // Auto-load saved session when user picks an employee
    if (employeeSelect) {
        employeeSelect.addEventListener('change', handleEmployeeChange);
    }

    // Publish post button opens preview modal
    if (publishPostBtn) {
        publishPostBtn.addEventListener('click', openPostModal);
    }

    // Confirm publish inside modal
    const confirmBtn = $('confirmPublishBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', handlePublishPost);
    }
});

// ── Auto-load saved session ───────────────────────────────────────────────
async function handleEmployeeChange() {
    const utilisateurId = employeeSelect.value;
    if (!utilisateurId) return;

    try {
        const res  = await fetch(LOAD_URL_BASE + utilisateurId);
        const data = await res.json();

        const key = BOARDING_MODE === 'onboarding' ? 'onboarding' : 'offboarding';
        if (data[key]) {
            await restoreSession(data[key]);
        }
    } catch (err) {
        console.warn('Impossible de charger la session existante.', err);
    }
}

async function restoreSession(ob) {
    currentBoardingId = ob.id;

    // Build a map of saved isDone states by task id
    const savedDone = {};
    (ob.tasks || []).forEach(t => { savedDone[t.id] = t.isDone; });

    // Pre-fill the secondary select if we have saved data
    const secondarySelect = BOARDING_MODE === 'onboarding' ? $('departmentSelect') : $('reasonSelect');
    const dateInput       = BOARDING_MODE === 'onboarding' ? $('arrivalDate')       : $('departureDate');

    const savedSecondary = BOARDING_MODE === 'onboarding' ? ob.department : ob.reason;
    const savedDate      = BOARDING_MODE === 'onboarding' ? ob.arrivalDate : ob.departureDate;

    if (savedSecondary && secondarySelect) secondarySelect.value = savedSecondary;
    if (savedDate && dateInput)           dateInput.value        = savedDate;

    // Re-generate from server to get full task metadata (icon, category, dayOffset)
    if (savedSecondary) {
        const body = BOARDING_MODE === 'onboarding'
            ? { employeeId: employeeSelect.value, department: savedSecondary, arrivalDate: savedDate }
            : { employeeId: employeeSelect.value, reason: savedSecondary, departureDate: savedDate };

        try {
            const res  = await fetch(GENERATE_URL, {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body   : JSON.stringify(body),
            });
            const data = await res.json();

            if (res.ok) {
                currentBoardingId = data.onboardingId ?? data.offboardingId;
                currentTasks = data.tasks.map(t => ({
                    ...t,
                    done       : t.done ?? savedDone[t.id] ?? false,
                    aiGenerated: false,
                }));
                renderAll(data.employeeInfo, data.autoActions, ob.postPublished ?? data.postPublished ?? false);
                return;
            }
        } catch (err) {
            console.warn('Restore re-generate failed, falling back.', err);
        }
    }

    // Fallback: simplified restore without icons
    currentTasks = (ob.tasks || []).map(t => ({
        id         : t.id,
        icon       : '📋',
        label      : t.label,
        category   : 'Général',
        dayOffset  : 0,
        done       : t.isDone,
        aiGenerated: false,
    }));

    renderStats();
    renderTaskPanel();
    updatePublishBtn(ob.postPublished ?? false);

    employeeCard.style.display    = '';
    autoActionsCard.style.display = '';
    employeeCard.classList.remove('card--hidden');
    autoActionsCard.classList.remove('card--hidden');

    if (ob.employeeInfo) renderEmployeeCard(ob.employeeInfo);
}

// ── Generate ──────────────────────────────────────────────────────────────
async function handleGenerate() {
    const utilisateurId  = employeeSelect?.value;
    const dateEl         = BOARDING_MODE === 'onboarding' ? $('arrivalDate')     : $('departureDate');
    const secondaryEl    = BOARDING_MODE === 'onboarding' ? $('departmentSelect'): $('reasonSelect');

    if (!utilisateurId) { showAlert('⚠️ Veuillez sélectionner un employé.'); return; }
    if (!secondaryEl?.value) {
        const label = BOARDING_MODE === 'onboarding' ? 'un département' : 'le motif de départ';
        showAlert(`⚠️ Veuillez sélectionner ${label}.`);
        return;
    }

    generateBtn.disabled    = true;
    generateBtn.textContent = '⏳ Génération…';

    const body = BOARDING_MODE === 'onboarding'
        ? { employeeId: utilisateurId, department: secondaryEl.value, arrivalDate: dateEl?.value }
        : { employeeId: utilisateurId, reason: secondaryEl.value, departureDate: dateEl?.value };

    try {
        const res  = await fetch(GENERATE_URL, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body   : JSON.stringify(body),
        });
        const data = await res.json();

        if (!res.ok) { showAlert('❌ ' + (data.error ?? 'Erreur serveur.')); return; }

        currentBoardingId = data.onboardingId ?? data.offboardingId ?? null;
        currentTasks      = data.tasks.map(t => ({ ...t, done: t.done ?? false, aiGenerated: false }));

        renderAll(data.employeeInfo, data.autoActions, data.postPublished ?? false);

    } catch (err) {
        showAlert('❌ Impossible de contacter le serveur.');
        console.error(err);
    } finally {
        generateBtn.disabled    = false;
        generateBtn.textContent = BOARDING_MODE === 'onboarding' ? '✨ Générer le parcours' : '📋 Générer le parcours';
    }
}

function renderAll(employeeInfo, autoActions, postPublished = false) {
    currentEmployeeInfo = employeeInfo;
    renderStats();
    renderTaskPanel();
    if (employeeInfo) renderEmployeeCard(employeeInfo);
    if (autoActions)  renderAutoActions(autoActions, employeeInfo);
    updatePublishBtn(postPublished);

    employeeCard.style.display    = '';
    autoActionsCard.style.display = '';
    employeeCard.classList.remove('card--hidden');
    autoActionsCard.classList.remove('card--hidden');
}

// ── Publish post — open modal with pre-filled content ─────────────────────
function updatePublishBtn(postPublished) {
    if (!publishPostBtn) return;
    publishPostBtn.style.display = '';

    if (postPublished) {
        publishPostBtn.textContent       = '✅ Post déjà publié';
        publishPostBtn.disabled          = true;
        publishPostBtn.style.background  = '#64748b';
        publishPostBtn.style.cursor      = 'default';
    } else {
        publishPostBtn.textContent       = BOARDING_MODE === 'onboarding' ? '📢 Publier un post de bienvenue' : '📢 Publier un post';
        publishPostBtn.disabled          = false;
        publishPostBtn.style.background  = '#7c3aed';
        publishPostBtn.style.cursor      = 'pointer';
    }
}

function openPostModal() {
    if (!currentBoardingId) {
        showAlert('⚠️ Aucun parcours actif. Générez d\'abord le parcours.');
        return;
    }

    const info   = currentEmployeeInfo;
    const modal  = $('postModal');
    const content= $('postContent');
    if (!modal || !content) return;

    // Build pre-filled professional post content
    if (info) {
        if (BOARDING_MODE === 'onboarding') {
            const arrival = info.arrivalDate ? formatDate(info.arrivalDate) : 'aujourd\'hui';
            content.value =
                `🎉 Bienvenue à ${info.name} !\n\n` +
                `Nous sommes ravis d'accueillir ${info.name} qui rejoint l'équipe ${info.department} en tant que ${info.role} à compter du ${arrival}.\n\n` +
                `Toute l'équipe Humania lui souhaite une excellente intégration et une belle aventure parmi nous ! 🚀\n\n` +
                `#Onboarding #NouveauCollaborateur #Bienvenue #Humania`;
        } else {
            const departure = info.departureDate ? formatDate(info.departureDate) : 'prochainement';
            const reason    = info.reason ? ` suite à une ${info.reason.toLowerCase()}` : '';
            content.value =
                `👋 Au revoir et merci ${info.name} !\n\n` +
                `${info.name} (${info.department} — ${info.role}) quitte nos équipes le ${departure}${reason}.\n\n` +
                `Nous le/la remercions chaleureusement pour sa contribution et lui souhaitons le meilleur pour la suite. 🙏\n\n` +
                `#Offboarding #Humania #Départ`;
        }
    }

    modal.style.display = 'flex';
}

async function handlePublishPost() {
    if (!currentBoardingId) return;

    const confirmBtn = $('confirmPublishBtn');
    const content    = $('postContent')?.value?.trim();

    if (!content) { showAlert('⚠️ Le contenu du post est vide.'); return; }

    confirmBtn.disabled    = true;
    confirmBtn.textContent = '⏳ Publication…';

    const modePrefix = BOARDING_MODE === 'onboarding' ? '/onboarding/' : '/offboarding/';
    const url        = PUBLISH_URL + currentBoardingId + '/publish-post';

    try {
        const res  = await fetch(url, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body   : JSON.stringify({ contenu: content }),
        });
        const data = await res.json();

        if (!res.ok) {
            showAlert('❌ ' + (data.error ?? 'Erreur lors de la publication.'));
        } else {
            $('postModal').style.display = 'none';
            showAlert('✅ Post publié avec succès dans le fil d\'actualité !');
            updatePublishBtn(true);
        }
    } catch (err) {
        showAlert('❌ Impossible de contacter le serveur.');
        console.error(err);
    } finally {
        confirmBtn.disabled    = false;
        confirmBtn.textContent = BOARDING_MODE === 'onboarding' ? '🎉 Publier maintenant' : '📢 Publier maintenant';
    }
}

// ── Render: stats row ─────────────────────────────────────────────────────
function renderStats() {
    const done    = currentTasks.filter(t => t.done).length;
    const total   = currentTasks.length;
    const aiCount = currentTasks.filter(t => t.aiGenerated).length;

    let cards;
    if (BOARDING_MODE === 'onboarding') {
        const today = currentTasks.filter(t => t.dayOffset === 0).length;
        cards = [
            { icon: '📋', value: total,   label: 'Tâches totales',      fg: '#667eea', bg: '#ede9fe' },
            { icon: '✅', value: done,    label: 'Complétées',           fg: '#16a34a', bg: '#dcfce7' },
            { icon: '📅', value: today,   label: 'À faire aujourd\'hui', fg: '#f59e0b', bg: '#fef3c7' },
            { icon: '🤖', value: aiCount, label: 'Tâches IA',            fg: '#0ea5e9', bg: '#e0f2fe' },
        ];
    } else {
        const urgent = currentTasks.filter(t => t.dayOffset === 0 && !t.done).length;
        cards = [
            { icon: '📋', value: total,   label: 'Tâches totales', fg: '#475569', bg: '#f1f5f9' },
            { icon: '✅', value: done,    label: 'Complétées',     fg: '#16a34a', bg: '#dcfce7' },
            { icon: '🚨', value: urgent,  label: 'Urgentes (J)',   fg: '#dc2626', bg: '#fee2e2' },
            { icon: '🤖', value: aiCount, label: 'Tâches IA',      fg: '#f59e0b', bg: '#fef3c7' },
        ];
    }

    statsRow.innerHTML = cards.map(c => `
        <div class="stat-card">
            <div class="stat-card__icon">${c.icon}</div>
            <div class="stat-card__value" style="color:${c.fg}">${c.value}</div>
            <div class="stat-card__label">${c.label}</div>
        </div>
    `).join('');
}

// ── Render: checklist ─────────────────────────────────────────────────────
function renderTaskPanel() {
    const done  = currentTasks.filter(t => t.done).length;
    const total = currentTasks.length;
    const pct   = total ? done / total : 0;
    const pctPx = Math.round(pct * 100);

    const titleText = BOARDING_MODE === 'onboarding'
        ? '✅ Checklist d\'intégration'
        : '📋 Checklist de départ';

    const barColor = BOARDING_MODE === 'offboarding' && pct >= 1 ? '#16a34a' : ACCENT;

    // Group by category
    const byCategory = {};
    currentTasks.forEach(t => {
        (byCategory[t.category] = byCategory[t.category] || []).push(t);
    });

    let html = `
        <div class="task-panel__header">
            <span class="task-panel__title">${titleText}</span>
            <span class="task-panel__progress-text">${done} / ${total} complétées</span>
        </div>
        <div class="progress-bar-wrap">
            <div class="progress-bar">
                <div class="progress-bar__fill" style="width:${pctPx}%;background:${barColor}"></div>
            </div>
        </div>
    `;

    Object.entries(byCategory).forEach(([cat, tasks]) => {
        html += `<div class="task-category-label">${cat}</div>`;
        tasks.forEach(task => {
            const idx = currentTasks.indexOf(task);
            html += buildTaskRow(task, idx);
        });
    });

    taskPanel.innerHTML = html;

    // Attach toggle listeners
    taskPanel.querySelectorAll('.task-row').forEach(row => {
        row.addEventListener('click', () => handleTaskToggle(parseInt(row.dataset.idx, 10)));
        row.addEventListener('keydown', e => {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                handleTaskToggle(parseInt(row.dataset.idx, 10));
            }
        });
    });
}

async function handleTaskToggle(idx) {
    const task = currentTasks[idx];
    if (!task) return;

    // Optimistic UI update
    task.done = !task.done;
    renderStats();
    renderTaskPanel();

    // Persist to DB if task has an id
    if (task.id) {
        try {
            const urlBase = BOARDING_MODE === 'onboarding'
                ? TOGGLE_URL_BASE
                : TOGGLE_URL_BASE;

            const url = urlBase + task.id + '/toggle';
            const res  = await fetch(url, {
                method : 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();

            if (!res.ok) {
                // Revert on error
                task.done = !task.done;
                renderStats();
                renderTaskPanel();
                showAlert('❌ Impossible de sauvegarder la tâche.');
            } else if (data.allDone) {
                showAlert('🎉 Toutes les tâches sont complétées ! Pensez à publier un post.');
                updatePublishBtn(false);
            }
        } catch (err) {
            console.error('Toggle task error:', err);
        }
    }
}

function buildTaskRow(task, idx) {
    const checkColor = BOARDING_MODE === 'onboarding' ? '#667eea' : '#ef4444';
    const checkStyle = task.done
        ? `background:${checkColor};border-color:${checkColor}`
        : 'background:transparent;border-color:#cbd5e1';

    const dayLabel = BOARDING_MODE === 'onboarding'
        ? `J+${task.dayOffset}`
        : (task.dayOffset === 0 ? 'Jour J (départ)' : `J-${task.dayOffset}`);

    let badgeBg, badgeFg;
    if (BOARDING_MODE === 'onboarding') {
        badgeBg = task.dayOffset === 0 ? '#dcfce7' : task.dayOffset <= 3 ? '#ede9fe' : '#f1f5f9';
        badgeFg = task.dayOffset === 0 ? '#16a34a' : task.dayOffset <= 3 ? '#667eea' : '#64748b';
    } else {
        badgeBg = task.dayOffset === 0 ? '#fee2e2' : task.dayOffset <= 5 ? '#fef3c7' : '#f1f5f9';
        badgeFg = task.dayOffset === 0 ? '#dc2626' : task.dayOffset <= 5 ? '#f59e0b' : '#64748b';
    }

    const aiClass  = task.aiGenerated ? `task-row--ai-${BOARDING_MODE === 'onboarding' ? 'on' : 'off'}` : '';
    const aiSuffix = task.aiGenerated ? '&nbsp;·&nbsp;🤖 IA' : '';

    return `
        <div class="task-row ${aiClass}" data-idx="${idx}" role="button" tabindex="0"
             aria-label="${task.label}" aria-pressed="${task.done}">
            <div class="task-row__check task-row__check--${task.done ? 'done' : 'undone'}"
                 style="${checkStyle}">
                ${task.done ? '✓' : ''}
            </div>
            <span class="task-row__emoji">${task.icon}</span>
            <div class="task-row__info">
                <div class="task-row__label ${task.done ? 'task-row__label--done' : ''}">${task.label}</div>
                <div class="task-row__sub">${dayLabel} · ${task.category}${aiSuffix}</div>
            </div>
            <span class="task-badge" style="background:${badgeBg};color:${badgeFg}">${dayLabel}</span>
        </div>
    `;
}

// ── Render: employee info card ────────────────────────────────────────────
function renderEmployeeCard(info) {
    const isOn       = BOARDING_MODE === 'onboarding';
    const avatarBg   = isOn ? '#ede9fe' : '#fee2e2';
    const avatarColor= isOn ? '#667eea' : '#dc2626';
    const roleColor  = isOn ? '#667eea' : '#ef4444';
    const statusText = isOn ? '● En cours d\'intégration' : '● Offboarding en cours';
    const statusBg   = isOn ? '#dcfce7' : '#fee2e2';
    const statusFg   = isOn ? '#16a34a' : '#dc2626';
    const dateLabel  = isOn ? 'Date d\'arrivée' : 'Date de départ';
    const dateValue  = isOn ? info.arrivalDate : info.departureDate;

    const initials = getInitials(info.name);

    let extraRow = '';
    if (!isOn && info.reason) {
        extraRow = infoRowHtml('📌', 'Motif', info.reason);
    }

    employeeCardBody.innerHTML = `
        <div class="emp-header">
            <div class="emp-avatar" style="background:${avatarBg};color:${avatarColor}">${initials}</div>
            <div>
                <div class="emp-name">${info.name}</div>
                <div class="emp-role" style="color:${roleColor}">${info.role}</div>
            </div>
        </div>
        <div class="emp-divider"></div>
        ${infoRowHtml('🏢', 'Département', info.department)}
        ${infoRowHtml('📧', 'Email', info.email)}
        ${dateValue ? infoRowHtml('📅', dateLabel, formatDate(dateValue)) : ''}
        ${extraRow}
        ${infoRowHtml('🎯', 'Tâches générées', currentTasks.length + ' étapes')}
        <span class="status-badge" style="background:${statusBg};color:${statusFg}">${statusText}</span>
    `;
}

// ── Render: auto-actions card ─────────────────────────────────────────────
function renderAutoActions(actions, info) {
    const colorMap = {
        green : { bg: '#dcfce7', fg: '#16a34a' },
        blue  : { bg: '#e0f2fe', fg: '#0ea5e9' },
        yellow: { bg: '#fef3c7', fg: '#f59e0b' },
        purple: { bg: '#ede9fe', fg: '#667eea' },
        slate : { bg: '#f1f5f9', fg: '#475569' },
        red   : { bg: '#fee2e2', fg: '#dc2626' },
    };

    autoActionsBody.innerHTML = actions.map(a => {
        const { bg, fg } = colorMap[a.color] || colorMap.slate;
        return `
            <div class="action-row" style="background:${bg}">
                <span class="action-row__icon">${a.icon}</span>
                <div class="action-row__info">
                    <div class="action-row__label" style="color:${fg}">${a.label}</div>
                    <div class="action-row__detail">${a.detail}</div>
                </div>
                <span class="action-row__check" style="color:${fg}">✓</span>
            </div>
        `;
    }).join('');

    const isOn      = BOARDING_MODE === 'onboarding';
    const aiClass   = isOn ? 'btn--ai-on' : 'btn--ai-off';
    const markColor = isOn ? '#667eea' : '#ef4444';

    autoActionsFooter.innerHTML = `
        <button class="btn btn--full" id="markAllBtn"
                style="background:${markColor};color:#fff">
            ✅ Tout marquer complété
        </button>
        <button class="btn btn--outline btn--full" id="resetBtn">
            ↺ Réinitialiser
        </button>
    `;

    document.getElementById('markAllBtn').addEventListener('click', () => handleMarkAll(true));
    document.getElementById('resetBtn').addEventListener('click',   () => handleMarkAll(false));
}

async function handleMarkAll(done) {
    currentTasks.forEach(t => t.done = done);
    renderStats();
    renderTaskPanel();

    if (currentBoardingId) {
        try {
            const url = MARK_ALL_URL + currentBoardingId + '/mark-all';
            await fetch(url, {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body   : JSON.stringify({ done }),
            });
        } catch (err) {
            console.error('Mark-all error:', err);
        }
    }
}


// ── Helpers ───────────────────────────────────────────────────────────────
function infoRowHtml(icon, key, value) {
    return `
        <div class="info-row">
            <span class="info-row__icon">${icon}</span>
            <span class="info-row__key">${key} :</span>
            <span class="info-row__val">${value ?? '—'}</span>
        </div>
    `;
}

function getInitials(name) {
    const parts = (name ?? '').trim().split(/\s+/);
    if (parts.length >= 2) return parts[0][0].toUpperCase() + parts[1][0].toUpperCase();
    return (name ?? '--').substring(0, 2).toUpperCase();
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const [y, m, d] = dateStr.split('-');
    return `${d}/${m}/${y}`;
}

function showAlert(msg) {
    alertBox.textContent = msg;
    alertBox.classList.remove('alert-box--hidden');
    setTimeout(() => alertBox.classList.add('alert-box--hidden'), 3500);
}
