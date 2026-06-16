// "Скачать результат" → PDF. v1: a client-side snapshot of the already-polished print
// layout. We add `.bm-pdf-export` to <body> so the shared print styles apply on screen,
// then rasterize the calculator block with html2canvas and emit a single A4 page via
// jsPDF (html2pdf.js wrapper). Text is rasterized (acceptable for now); a future version
// could render selectable text server-side or via jsPDF from the result data.

const EXPORT_CLASS = "bm-pdf-export";

let isBusy = false;

function pad2(value) {
  return String(value).padStart(2, "0");
}

function buildFileName(form) {
  const calc = form.querySelector('[name="calculator"]')?.value || "raschet";
  const now = new Date();
  const date = `${now.getFullYear()}-${pad2(now.getMonth() + 1)}-${pad2(now.getDate())}`;
  return `${calc}-${date}.pdf`;
}

function setButtonBusy(button, busy) {
  if (!button) {
    return;
  }
  const label = button.querySelector("span") || button;
  if (busy) {
    button.dataset.pdfRestoreLabel = label.textContent;
    label.textContent = "Готовим PDF…";
    button.disabled = true;
    button.setAttribute("aria-busy", "true");
  } else {
    if (button.dataset.pdfRestoreLabel) {
      label.textContent = button.dataset.pdfRestoreLabel;
      delete button.dataset.pdfRestoreLabel;
    }
    button.disabled = false;
    button.removeAttribute("aria-busy");
  }
}

function flashButton(button, message) {
  if (!button) {
    return;
  }
  const label = button.querySelector("span") || button;
  const previous = label.textContent;
  label.textContent = message;
  window.setTimeout(() => {
    label.textContent = previous;
  }, 2500);
}

export async function exportEstimateToPdf(form, button) {
  if (isBusy || !form) {
    return;
  }

  const layout = form.closest(".bm-calculator-layout") || form.parentElement;
  const resultNode = layout?.querySelector("[data-result]");

  // Nothing to export until there is a successful, visible result.
  if (!resultNode || resultNode.hidden || !resultNode.classList.contains("is-success")) {
    return;
  }

  // Open the viewer tab synchronously inside the click gesture so the popup blocker lets
  // it through; we point it at the generated PDF once it's ready.
  const viewer = window.open("", "_blank");

  isBusy = true;
  setButtonBusy(button, true);

  try {
    // Make sure web fonts (Inter / Cyrillic) are ready before rasterizing.
    if (document.fonts?.ready) {
      await document.fonts.ready;
    }

    const { default: html2pdf } = await import("html2pdf.js");

    const jsPdf = await html2pdf()
      .set({
        margin: [14, 12, 14, 12],
        image: { type: "jpeg", quality: 0.95 },
        html2canvas: {
          scale: 2,
          useCORS: true,
          backgroundColor: "#ffffff",
          // Force a desktop width so the two-column print layout renders instead of the
          // ≤767px mobile drawer view.
          windowWidth: 1280,
          // Apply the print layout to the CLONED document only, so the live page never
          // flashes the full-screen estimate while the snapshot is being taken.
          onclone: (clonedDoc) => clonedDoc.body.classList.add(EXPORT_CLASS),
        },
        jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
        pagebreak: { mode: ["avoid-all"] },
      })
      .from(layout)
      .toPdf()
      .get("pdf");

    const blobUrl = jsPdf.output("bloburl");
    if (viewer && !viewer.closed) {
      // Open the PDF in the prepared tab — the browser viewer offers download + print.
      viewer.location.href = blobUrl;
    } else {
      // Popup was blocked — fall back to a direct download.
      jsPdf.save(buildFileName(form));
    }
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error("PDF export failed", error);
    if (viewer && !viewer.closed) {
      viewer.close();
    }
    flashButton(button, "Не удалось открыть");
  } finally {
    setButtonBusy(button, false);
    isBusy = false;
  }
}
