import '../../common.js';
import './calculator.scss';

const resultPanel = document.querySelector('[data-result-panel]');
const resultOpenButton = document.querySelector('[data-result-open]');
const stickyResult = document.querySelector('.bm-calculator-sticky-result');
const schemeCanvas = document.querySelector('.bm-calculator-scheme__canvas');

// The plugin renders its result block inside .brigmaster-estimator (left column).
// Relocate it into the themed sticky aside so results live in the right column.
// constructly-core finds it via getEstimatorShell() -> .bm-calculator-layout, so the
// move does not break result population, stale tracking, or focus handling.
const pluginResult = document.querySelector('.brigmaster-estimator [data-result]');
if (resultPanel && pluginResult && !resultPanel.contains(pluginResult)) {
  resultPanel.appendChild(pluginResult);
}

// Drag handle pill inside the bottom sheet: visual affordance only.
// Uses Pointer Events so the gesture works with both touch and mouse
// (the latter matters for responsive-mode testing in DevTools).
// The full-card swipe handler below covers the handle zone as well.
if (pluginResult) {
  const handle = document.createElement('div');
  handle.className = 'bm-result-handle';
  handle.setAttribute('aria-hidden', 'true');

  // Insert the pill into the result card itself so it is positioned relative to the card,
  // not the outer wrapper (which sits higher in the grid and adds unwanted gap above the title).
  const sheet = pluginResult.querySelector('.bm-calculator-result') || pluginResult;
  sheet.insertAdjacentElement('afterbegin', handle);

  // Selectors that should pass pointer events through without intercepting.
  // Swipe activation skips these so clicks/taps on controls keep working.
  const INTERACTIVE_SEL =
    'button, a, input, select, textarea, label, ' +
    '[data-bm-tooltip], .bm-tooltip-trigger, [data-result-action]';

  const DRAG_START_PX = 6;   // minimum downward movement to enter drag mode
  const DISMISS_PX   = 80;   // minimum drag distance to trigger dismiss

  let armed          = false;
  let dragging       = false;
  let startY         = 0;
  let startX         = 0;
  let capturedId     = -1;

  // pointerdown — arm the gesture; capture and drag state set later in pointermove.
  // Touch input is handled by the dedicated touch path below to avoid double-firing
  // in device-mode emulation where both pointer and touch events are dispatched.
  sheet.addEventListener('pointerdown', (e) => {
    if (e.pointerType === 'touch') return;
    // Only primary pointer (left mouse button).
    if (!e.isPrimary) return;
    // Let interactive controls receive their events normally.
    if (e.target.closest(INTERACTIVE_SEL)) return;
    // When the scroll container has scrolled down, this is a content scroll — not a dismiss.
    if (resultPanel && resultPanel.scrollTop > 0) return;

    armed      = true;
    dragging   = false;
    startY     = e.clientY;
    startX     = e.clientX;
    capturedId = e.pointerId;
  });

  // pointermove (non-passive) — detect drag direction, then move the sheet.
  sheet.addEventListener('pointermove', (e) => {
    if (e.pointerType === 'touch') return;
    if (!armed || e.pointerId !== capturedId) return;

    const dy = e.clientY - startY;
    const dx = Math.abs(e.clientX - startX);

    if (!dragging) {
      // Upward swipe or horizontal gesture — cancel arm.
      if (dy < 0 || dx > dy) {
        armed = false;
        return;
      }
      // Not yet past the activation threshold — wait.
      if (dy < DRAG_START_PX) return;

      // Downward drag confirmed — take pointer capture and enter drag mode.
      dragging = true;
      sheet.setPointerCapture(capturedId);
    }

    // Prevent the scroll container from scrolling while we are dragging the card down.
    e.preventDefault();
    sheet.style.transition = 'none';
    sheet.style.transform  = `translateY(${Math.max(0, dy)}px)`;
  }, { passive: false });

  // pointerup — decide: dismiss or snap back.
  sheet.addEventListener('pointerup', (e) => {
    if (e.pointerType === 'touch') return;
    if (!armed || e.pointerId !== capturedId) return;

    const wasDragging = dragging;
    armed    = false;
    dragging = false;

    if (!wasDragging) return;

    const dy        = e.clientY - startY;
    const threshold = Math.min(DISMISS_PX, sheet.offsetHeight * 0.25);

    // Restore transition before applying the snap-back so the card slides smoothly.
    sheet.style.transition = '';
    sheet.style.transform  = '';

    if (dy > threshold) {
      closeResultPanel();
    }
  });

  // pointercancel — external interruption (call, browser gesture); reset state.
  sheet.addEventListener('pointercancel', (e) => {
    if (e.pointerType === 'touch') return;
    armed    = false;
    dragging = false;
    sheet.style.transition = '';
    sheet.style.transform  = '';
  });

  // Touch path — handles real touchscreens and device-mode emulation.
  // With touch-action:pan-y on the card the browser may send pointercancel for
  // vertical drags, so we use Touch Events directly to stay in control.
  let touchStartY  = 0;
  let touchAtTop   = false;
  let touchDrag    = false;
  let touchLastDy  = 0;

  sheet.addEventListener('touchstart', (e) => {
    if (e.target.closest(INTERACTIVE_SEL)) return;
    touchStartY = e.touches[0].clientY;
    touchAtTop  = !resultPanel || resultPanel.scrollTop <= 0;
    touchDrag   = false;
    touchLastDy = 0;
  }, { passive: true });

  sheet.addEventListener('touchmove', (e) => {
    // When the sheet has scrolled down, let the browser handle the scroll.
    if (!touchAtTop) return;

    const dy = e.touches[0].clientY - touchStartY;
    touchLastDy = dy;

    if (dy <= 0) {
      // Upward swipe — exit drag mode and let the sheet snap back.
      if (touchDrag) {
        sheet.style.transition = '';
        sheet.style.transform  = '';
        touchDrag = false;
      }
      return;
    }

    // Downward movement: enter drag mode once the threshold is crossed.
    if (!touchDrag && dy > DRAG_START_PX) {
      touchDrag = true;
    }

    if (touchDrag) {
      // Prevent scroll while dragging the card downward.
      e.preventDefault();
      sheet.style.transition = 'none';
      sheet.style.transform  = `translateY(${dy}px)`;
    }
  }, { passive: false });

  sheet.addEventListener('touchend', () => {
    if (!touchDrag) return;

    const dy        = touchLastDy;
    const threshold = Math.min(DISMISS_PX, sheet.offsetHeight * 0.25);

    sheet.style.transition = '';
    sheet.style.transform  = '';
    touchDrag = false;

    if (dy > threshold) {
      closeResultPanel();
    }
  });

  sheet.addEventListener('touchcancel', () => {
    touchDrag = false;
    sheet.style.transition = '';
    sheet.style.transform  = '';
  });
}

// iOS Safari ignores overflow:hidden on body when content is already scrolled.
// position:fixed + negative top restores correct position on unlock.
let savedScrollY = 0;

function lockScroll() {
  savedScrollY = window.scrollY || window.pageYOffset || 0;
  document.body.style.position = 'fixed';
  document.body.style.top = `-${savedScrollY}px`;
  document.body.style.width = '100%';
  document.body.classList.add('bm-scroll-lock');
}

function unlockScroll() {
  document.body.classList.remove('bm-scroll-lock');
  document.body.style.position = '';
  document.body.style.top = '';
  document.body.style.width = '';
  const html = document.documentElement;
  const prevBehavior = html.style.scrollBehavior;
  html.style.scrollBehavior = 'auto';
  window.scrollTo(0, savedScrollY);
  html.style.scrollBehavior = prevBehavior;
}

const closeResultPanel = () => {
  if (!resultPanel || !resultOpenButton) {
    return;
  }

  resultPanel.classList.remove('is-open');
  unlockScroll();
  resultOpenButton.setAttribute('aria-expanded', 'false');
};

if (resultPanel && resultOpenButton) {
  resultOpenButton.addEventListener('click', () => {
    const isOpen = resultPanel.classList.toggle('is-open');

    if (isOpen) { lockScroll(); } else { unlockScroll(); }
    resultOpenButton.setAttribute('aria-expanded', String(isOpen));
  });

  resultPanel.addEventListener('click', (event) => {
    if (event.target === resultPanel) {
      closeResultPanel();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeResultPanel();
    }
  });
}

// Shared matchMedia query for ≤900 px mobile checks.
const mq900 = window.matchMedia('(max-width:900px)');

// Reveal the mobile sticky "show results" bar only while a successful result exists.
// constructly-core fires brigmaster:result-change (bubbling to document) whenever a
// calculation succeeds or the result is cleared, so the bar tracks the real result
// state instead of the submit event — it never flashes on a validation error.
if (stickyResult) {
  document.addEventListener('brigmaster:result-change', (event) => {
    stickyResult.classList.toggle('is-visible', Boolean(event.detail?.success));
  });
}

// Auto-open the bottom sheet on mobile (≤900 px) after a successful calculation.
// Only fires on success; errors stay inside the form and never trigger this.
// Double requestAnimationFrame defers the class addition until the browser has
// committed the freshly injected result layout, so the slide-in transition always
// starts from a settled transform baseline (avoids a jump on the very first open).
if (resultPanel && resultOpenButton) {
  document.addEventListener('brigmaster:result-change', (event) => {
    if (!event.detail?.success) return;
    if (!mq900.matches) return;
    if (resultPanel.classList.contains('is-open')) return;
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        resultPanel.classList.add('is-open');
        lockScroll();
        resultOpenButton.setAttribute('aria-expanded', 'true');
      });
    });
  });
}

if (schemeCanvas instanceof HTMLCanvasElement) {
  const ctx = schemeCanvas.getContext('2d');

  if (ctx) {
    ctx.clearRect(0, 0, schemeCanvas.width, schemeCanvas.height);
    ctx.strokeStyle = '#3e5f7b';
    ctx.lineWidth = 10;
    ctx.strokeRect(90, 72, 436, 267);
    ctx.lineWidth = 6;
    ctx.strokeRect(150, 122, 316, 167);
    ctx.fillStyle = '#3e5f7b';
    ctx.beginPath();
    ctx.arc(90, 72, 7, 0, Math.PI * 2);
    ctx.arc(526, 72, 7, 0, Math.PI * 2);
    ctx.arc(526, 339, 7, 0, Math.PI * 2);
    ctx.arc(90, 339, 7, 0, Math.PI * 2);
    ctx.fill();
  }
}
