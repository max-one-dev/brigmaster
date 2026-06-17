// Entry shared by every page. Importing this registers the bootstrap loop;
// individual components register themselves when added on later stages.

import { bootstrap } from './js/core/bootstrap.js';
import { injectIconSprite } from './js/core/icon-sprite.js';
import './js/components/dropdown.js';
import './js/components/header-menu.js';
import './js/components/accordion.js';
import './js/components/select.js';
import './js/components/tooltip.js';
import './js/components/toc.js';
import { initRankMathFaq } from './js/components/rank-math-faq.js';
import { wrapProseTables } from './js/components/prose-tables.js';

function init() {
  injectIconSprite();
  wrapProseTables();
  bootstrap();
  initRankMathFaq();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
