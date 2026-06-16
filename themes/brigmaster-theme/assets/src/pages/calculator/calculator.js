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

const closeResultPanel = () => {
  if (!resultPanel || !resultOpenButton) {
    return;
  }

  resultPanel.classList.remove('is-open');
  document.body.classList.remove('bm-scroll-lock');
  resultOpenButton.setAttribute('aria-expanded', 'false');
};

if (resultPanel && resultOpenButton) {
  resultOpenButton.addEventListener('click', () => {
    const isOpen = resultPanel.classList.toggle('is-open');

    document.body.classList.toggle('bm-scroll-lock', isOpen);
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

// Reveal the mobile sticky "show results" bar only while a successful result exists.
// constructly-core fires brigmaster:result-change (bubbling to document) whenever a
// calculation succeeds or the result is cleared, so the bar tracks the real result
// state instead of the submit event — it never flashes on a validation error.
if (stickyResult) {
  document.addEventListener('brigmaster:result-change', (event) => {
    stickyResult.classList.toggle('is-visible', Boolean(event.detail?.success));
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
