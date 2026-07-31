/**
 * bm-tooltip.js — переиспользуемый тултип / bottom-sheet (mobile).
 * Не зависит от сторонних библиотек.
 * Экспортирует initBmTooltips() и вызывает её на DOMContentLoaded.
 */

const PANEL_CLS   = 'bm-tooltip__panel';
const SHEET_CLS   = 'is-sheet';
const ROOT_MARKUP = `
<div class="bm-tooltip" role="tooltip" hidden>
  <div class="bm-tooltip__backdrop" data-bm-tooltip-close></div>
  <div class="bm-tooltip__panel" tabindex="-1">
    <button type="button" class="bm-tooltip__close" data-bm-tooltip-close aria-label="Закрыть">×</button>
    <div class="bm-tooltip__body"></div>
  </div>
</div>`;

/** @type {HTMLElement|null} */
let root        = null;
/** @type {HTMLElement|null} */
let activeTrig  = null;
let closeTimer  = null;

const isCoarse = () => matchMedia('(pointer: coarse)').matches;

/**
 * Безопасно приводит event-таргет к ближайшему Element.
 * e.target может быть текстовым узлом / документом → .closest отсутствует.
 * @param {EventTarget|Node|null} node
 * @returns {Element|null}
 */
function resolveEl(node) {
    if (!node) return null;
    if (node.nodeType === 1) return node;      // Element
    return node.parentElement || null;         // текстовый узел и т.п.
}

function ensureRoot() {
    if (root) return root;
    const tmp = document.createElement('div');
    tmp.innerHTML = ROOT_MARKUP.trim();
    root = tmp.firstElementChild;
    document.body.appendChild(root);

    // Закрытие по кнопкам [data-bm-tooltip-close]
    root.addEventListener('click', (e) => {
        const _el = resolveEl(e.target);
        if (_el && _el.closest('[data-bm-tooltip-close]')) close();
    });

    // Esc
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !root.hidden) close();
    });

    // Десктоп: отменить закрытие при наведении на панель
    const panel = root.querySelector(`.${PANEL_CLS}`);
    panel.addEventListener('mouseenter', () => clearTimeout(closeTimer));
    panel.addEventListener('mouseleave', () => scheduleClose());
    panel.addEventListener('focusin',   () => clearTimeout(closeTimer));
    panel.addEventListener('focusout',  () => scheduleClose());

    return root;
}

function scheduleClose() {
    clearTimeout(closeTimer);
    closeTimer = setTimeout(close, 120);
}

function open(trigger) {
    const tpl = trigger.parentElement?.querySelector('.bm-tooltip-tpl');

    ensureRoot();
    const body = root.querySelector('.bm-tooltip__body');
    body.innerHTML = '';

    if (tpl instanceof HTMLTemplateElement) {
        body.appendChild(tpl.content.cloneNode(true));
    } else {
        const text = trigger.getAttribute('data-bm-tooltip');
        if (!text) return;
        const p = document.createElement('p');
        p.className = 'bm-tooltip__text';
        p.textContent = text; // textContent — безопасно, без XSS
        body.appendChild(p);
    }

    if (isCoarse()) {
        root.setAttribute('role', 'dialog');
        root.setAttribute('aria-modal', 'true');
        root.classList.add(SHEET_CLS);
        document.body.style.overflow = 'hidden';
        clearPanelInline();
    } else {
        root.setAttribute('role', 'tooltip');
        root.removeAttribute('aria-modal');
        root.classList.remove(SHEET_CLS);
        positionPanel(trigger);

        const img = root.querySelector('.bm-tooltip__body img');
        if (img && !img.complete) {
            img.addEventListener('load', () => {
                if (!root.hidden && !isCoarse()) positionPanel(trigger);
            }, { once: true });
        }
    }

    root.hidden = false;
    trigger.setAttribute('aria-expanded', 'true');
    activeTrig = trigger;

    if (isCoarse()) {
        const panel = root.querySelector(`.${PANEL_CLS}`);
        panel.focus();
        trapFocus(panel);
    }
}

function close() {
    clearTimeout(closeTimer);
    if (!root || root.hidden) return;
    root.hidden = true;
    root.classList.remove(SHEET_CLS);
    document.body.style.overflow = '';
    if (activeTrig) {
        activeTrig.setAttribute('aria-expanded', 'false');
        activeTrig = null;
    }
}

// Фиксированная ширина панели на десктопе (см. max-width в _bm-tooltip-panel.scss).
const PANEL_W = 320;
const GAP     = 8;  // зазор между иконкой и панелью
const MARGIN  = 8;  // минимальный отступ от краёв вьюпорта

/** Снять инлайновые позицию/ширину (нужно для sheet-режима — их задаёт CSS). */
function clearPanelInline() {
    const panel = root && root.querySelector(`.${PANEL_CLS}`);
    if (!panel) return;
    panel.style.left  = '';
    panel.style.top   = '';
    panel.style.width = '';
    panel.style.right = '';
    panel.style.bottom = '';
}

function positionPanel(trigger) {
    const panel = root.querySelector(`.${PANEL_CLS}`);

    // Контент уже внедрён в open(). Фиксируем ширину и делаем панель измеримой.
    panel.style.right  = '';
    panel.style.bottom = '';
    panel.style.width  = `${PANEL_W}px`;

    const prevVis    = root.style.visibility;
    const prevHidden = root.hidden;
    root.style.visibility = 'hidden';
    root.hidden = false;

    const panelH = panel.offsetHeight;
    const panelW = PANEL_W;

    root.hidden = prevHidden;
    root.style.visibility = prevVis;

    const tr = trigger.getBoundingClientRect();
    const vw = window.innerWidth;
    const vh = window.innerHeight;

    // ── Горизонталь ── левый край панели у левого края иконки.
    let left = tr.left;
    if (left + panelW + MARGIN > vw) {
        // Флип влево: правый край панели у правого края иконки.
        left = tr.right - panelW;
    }
    if (left < MARGIN) left = MARGIN;

    // ── Вертикаль ── по умолчанию снизу.
    const spaceBelow = vh - tr.bottom;
    let top = tr.bottom + GAP;
    if (panelH + GAP > spaceBelow) {
        // Флип вверх: нижний-левый угол панели прикреплён к иконке.
        top = tr.top - GAP - panelH;
    }
    if (top < MARGIN) top = MARGIN;

    panel.style.left = `${left}px`;
    panel.style.top  = `${top}px`;
}

/** Простой фокус-трап внутри panel (мобилка) */
function trapFocus(panel) {
    const handler = (e) => {
        if (e.key !== 'Tab') return;
        const focusable = [...panel.querySelectorAll(
            'a[href],button:not([disabled]),input,select,textarea,[tabindex]:not([tabindex="-1"])'
        )].filter(el => !el.hidden);
        if (!focusable.length) { e.preventDefault(); return; }
        const first = focusable[0];
        const last  = focusable[focusable.length - 1];
        if (e.shiftKey) {
            if (document.activeElement === first) { e.preventDefault(); last.focus(); }
        } else {
            if (document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    };
    panel.addEventListener('keydown', handler);
    // Снять трап при закрытии
    const observer = new MutationObserver(() => {
        if (root.hidden) { panel.removeEventListener('keydown', handler); observer.disconnect(); }
    });
    observer.observe(root, { attributes: true, attributeFilter: ['hidden'] });
}

export function initBmTooltips() {
    document.addEventListener('click', (e) => {
        const _el = resolveEl(e.target);
        const trigger = _el ? _el.closest('[data-bm-tooltip]') : null;
        if (!trigger) return;
        if (isCoarse()) {
            e.preventDefault();
            if (activeTrig === trigger) { close(); return; }
            open(trigger);
        }
    });

    document.addEventListener('mouseenter', (e) => {
        if (isCoarse()) return;
        const _el = resolveEl(e.target);
        const trigger = _el ? _el.closest('[data-bm-tooltip]') : null;
        if (!trigger) return;
        clearTimeout(closeTimer);
        open(trigger);
    }, true);

    document.addEventListener('mouseleave', (e) => {
        if (isCoarse()) return;
        const _el = resolveEl(e.target);
        const trigger = _el ? _el.closest('[data-bm-tooltip]') : null;
        if (!trigger) return;
        scheduleClose();
    }, true);

    document.addEventListener('focusin', (e) => {
        if (isCoarse()) return;
        const _el = resolveEl(e.target);
        const trigger = _el ? _el.closest('[data-bm-tooltip]') : null;
        if (!trigger) return;
        clearTimeout(closeTimer);
        open(trigger);
    });

    document.addEventListener('focusout', (e) => {
        if (isCoarse()) return;
        const _el = resolveEl(e.target);
        const trigger = _el ? _el.closest('[data-bm-tooltip]') : null;
        if (!trigger) return;
        scheduleClose();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBmTooltips);
} else {
    initBmTooltips();
}
