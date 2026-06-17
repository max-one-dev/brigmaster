/**
 * Wraps every <table> inside prose content in a horizontal-scroll container
 * (.bm-prose__table-scroll), so wide tables stay usable on narrow viewports.
 *
 * The prose body is a single freely-editable field, so authors write plain
 * <table> markup; this normalizes it at runtime instead of requiring the
 * wrapper to be typed by hand. Tables already wrapped are left untouched.
 */

const WRAPPER_CLASS = 'bm-prose__table-scroll';

export function wrapProseTables(root = document) {
  root.querySelectorAll('.bm-prose table').forEach((table) => {
    const parent = table.parentElement;
    if (parent && parent.classList.contains(WRAPPER_CLASS)) {
      return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = WRAPPER_CLASS;
    table.parentNode.insertBefore(wrapper, table);
    wrapper.appendChild(table);
  });
}
