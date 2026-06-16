// Result panel actions: print, download as PDF, and a shareable link that encodes the
// form state in the URL and restores it on load. The heavy PDF library is lazy-loaded
// inside exportEstimateToPdf, so importing it here adds no weight to the initial bundle.

import { exportEstimateToPdf } from "./pdf-export.js";

function flashCopied(button) {
  const label = button.querySelector("span") || button;
  if (label.dataset.restoreLabel) {
    return;
  }
  label.dataset.restoreLabel = label.textContent;
  label.textContent = "Ссылка скопирована";
  window.setTimeout(() => {
    label.textContent = label.dataset.restoreLabel;
    delete label.dataset.restoreLabel;
  }, 2000);
}

function buildShareUrl(form) {
  const params = new URLSearchParams();
  form.querySelectorAll("[name]").forEach((field) => {
    if (!field.name) {
      return;
    }
    if (field.type === "checkbox") {
      params.set(field.name, field.checked ? "1" : "0");
    } else if (field.value !== "") {
      params.set(field.name, field.value);
    }
  });

  return `${window.location.origin}${window.location.pathname}?${params.toString()}`;
}

async function copyShareLink(form, button) {
  const url = buildShareUrl(form);

  // Reflect the shareable state in the address bar for easy manual copy/share.
  window.history.replaceState(null, "", url);

  try {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(url);
    } else {
      const textarea = document.createElement("textarea");
      textarea.value = url;
      textarea.setAttribute("readonly", "");
      textarea.style.position = "absolute";
      textarea.style.left = "-9999px";
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand("copy");
      textarea.remove();
    }
    flashCopied(button);
  } catch (_error) {
    /* Clipboard may be unavailable; the URL is still updated for manual copy. */
  }
}

function restoreFromShareUrl(form) {
  const params = new URLSearchParams(window.location.search);
  let restored = false;

  params.forEach((value, key) => {
    const field = form.querySelector(`[name="${CSS.escape(key)}"]`);
    if (!field) {
      return;
    }
    if (field.type === "checkbox") {
      field.checked = value === "1";
    } else {
      field.value = value;
    }
    field.dispatchEvent(new Event("change", { bubbles: true }));
    restored = true;
  });

  if (restored && typeof form.requestSubmit === "function") {
    form.requestSubmit();
  }
}

export function initResultActions(form) {
  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-result-action]");
    if (!button) {
      return;
    }
    const action = button.dataset.resultAction;
    if (action === "print") {
      window.print();
    } else if (action === "download") {
      exportEstimateToPdf(form, button);
    } else if (action === "copy-link") {
      copyShareLink(form, button);
    }
  });

  // The download/PDF action only makes sense once a result exists. constructly-core fires
  // brigmaster:result-change (success true/false) whenever the result is shown or cleared.
  const downloadButton = document.querySelector('[data-result-action="download"]');
  if (downloadButton) {
    downloadButton.disabled = true;
    document.addEventListener("brigmaster:result-change", (event) => {
      downloadButton.disabled = !event.detail?.success;
    });
  }

  restoreFromShareUrl(form);
}
