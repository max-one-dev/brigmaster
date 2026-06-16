// Builds a read-only summary of the parameters the user entered, so the printed
// estimate ("Save as PDF") shows the inputs next to the results. The live form is
// hidden in print, so this summary is injected as a sibling of the form (inside
// .brigmaster-estimator, after the form) and revealed only via @media print.

import { escapeHtml } from "../core/formatters.js";

const SUMMARY_CLASS = "bm-print-params";

// A field belongs to the active calculation when no ancestor up to the form marks it
// as an inactive mode branch. We deliberately key off the explicit "--hidden" toggle
// class (and the [hidden] attribute) rather than computed visibility, so parameters
// inside a collapsed <details> accordion are still included in the summary.
function isFieldActive(form, field) {
  if (field.disabled) {
    return false;
  }
  let node = field;
  while (node && node !== form) {
    if (node.hasAttribute("hidden")) {
      return false;
    }
    if (node.classList && node.classList.contains("brigmaster-estimator__field-group--hidden")) {
      return false;
    }
    node = node.parentElement;
  }
  return true;
}

function getLabelText(form, field) {
  let labelEl = null;
  const id = field.getAttribute("id");
  if (id) {
    labelEl = form.querySelector(`label[for="${CSS.escape(id)}"]`);
  }
  if (!labelEl) {
    labelEl = field.closest(".brigmaster-estimator__field, .brigmaster-estimator__toggle")?.querySelector("label");
  }
  if (!labelEl) {
    return "";
  }
  // Labels usually wrap their text in a leading <span> next to a tooltip trigger;
  // take that span, otherwise fall back to the label's own flattened text.
  const primary = labelEl.querySelector(":scope > span");
  const source = primary || labelEl;
  return (source.textContent || "").replace(/\s+/g, " ").trim();
}

function getValueText(field) {
  if (field.type === "checkbox") {
    return field.checked ? "Да" : "Нет";
  }
  if (field.type === "radio") {
    return field.checked ? "Да" : "";
  }
  if (field.tagName === "SELECT") {
    return (field.options[field.selectedIndex]?.textContent || "").trim();
  }
  return String(field.value ?? "").trim();
}

function rowHtml(label, value) {
  return `<div class="bm-calculator-result__material"><span class="bm-calculator-result__material-head"><span>${escapeHtml(
    label
  )}</span><strong>${escapeHtml(value)}</strong></span></div>`;
}

function collectRows(form) {
  const rows = [];
  let currentSegment = null;

  form.querySelectorAll("input, select, textarea").forEach((field) => {
    const type = (field.getAttribute("type") || "").toLowerCase();
    if (type === "hidden" || type === "submit" || type === "button" || type === "reset") {
      return;
    }
    if (!isFieldActive(form, field)) {
      return;
    }

    // Skip self-mix conversion helpers (per-unit weight) and the purchase-unit selects:
    // they only feed the kg↔bag/tonne conversion that the results already express
    // ("23 мешка", "3 т"), so they are noise on a printed estimate.
    if (field.hasAttribute("data-mixture-unit-input") || /PurchaseUnit/.test(field.name || "")) {
      return;
    }

    const value = getValueText(field);
    if (value === "") {
      return;
    }
    const label = getLabelText(form, field);
    if (!label) {
      return;
    }

    // Group per-segment fields (segments mode) under each segment's own title.
    const segmentCard = field.closest("[data-strip-segment-item]");
    if (segmentCard) {
      if (segmentCard !== currentSegment) {
        currentSegment = segmentCard;
        const title = segmentCard
          .querySelector(".brigmaster-estimator__segment-title")
          ?.textContent?.trim();
        if (title) {
          rows.push(`<h3 class="bm-calculator-result__section-title">${escapeHtml(title)}</h3>`);
        }
      }
    } else {
      currentSegment = null;
    }

    rows.push(rowHtml(label, value));
  });

  return rows;
}

export function updatePrintParamsSummary(form) {
  if (!form || !form.parentElement) {
    return;
  }

  const rows = collectRows(form);
  let container = form.parentElement.querySelector(`:scope > .${SUMMARY_CLASS}`);

  if (!rows.length) {
    container?.remove();
    return;
  }

  if (!container) {
    container = document.createElement("div");
    container.className = SUMMARY_CLASS;
    form.insertAdjacentElement("afterend", container);
  }
  container.innerHTML = `<div class="bm-calculator-result__list">${rows.join("")}</div>`;
}

export function clearPrintParamsSummary(form) {
  form?.parentElement?.querySelector(`:scope > .${SUMMARY_CLASS}`)?.remove();
}
