import { formatNumber } from "../core/formatters.js";
import { clearErrors, finalizeSuccessfulResult, getEstimatorShell, markResultStale, readTrimmed, setFieldError, setModeLockState, toggleVisibility } from "../core/form-state.js";
import { validateBaseFields, validatePositiveField } from "../core/validation.js";
import { buildMixturePayload } from "../core/mixture.js";
import { renderMixtureCard } from "../ui/result-panel.js";
import { initEstimateForms } from "../core/bootstrap.js";


    export function showScreedResult(form, payload) {
        const resultNode = getEstimatorShell(form)?.querySelector("[data-result]");
        if (!resultNode) {
            return;
        }

        const volumeNode = resultNode.querySelector("[data-result-volume]");
        const areaNode = resultNode.querySelector("[data-result-screed-area]");
        const heightNode = resultNode.querySelector("[data-result-screed-height]");
        const rebarCard = resultNode.querySelector('[data-result-card="screed-reinforcement"]');
        const rebarMass = resultNode.querySelector("[data-result-screed-rebar-mass]");
        const rebarLen = resultNode.querySelector("[data-result-screed-rebar-length]");
        const reinforcement = payload?.reinforcement;

        if (volumeNode) {
            volumeNode.textContent = formatNumber(payload?.concrete?.volumeM3);
        }
        if (areaNode) {
            const areaVal = payload?.concrete?.areaM2;
            areaNode.textContent = Number.isFinite(Number(areaVal))
                ? formatNumber(areaVal)
                : "-";
        }
        if (heightNode) {
            heightNode.textContent = formatNumber(payload?.concrete?.heightM);
        }

        if (rebarCard && rebarMass && rebarLen) {
            if (reinforcement && Number.isFinite(Number(reinforcement.massKg))) {
                rebarCard.hidden = false;
                rebarMass.textContent = formatNumber(reinforcement.massKg);
                rebarLen.textContent = formatNumber(reinforcement.totalLengthWithReserveM);
            } else {
                rebarCard.hidden = true;
                rebarMass.textContent = "-";
                rebarLen.textContent = "-";
            }
        }

        renderMixtureCard(
            resultNode.querySelector('[data-result-card="mixture"]'),
            payload?.mixture,
            "Смесь и материалы"
        );

        resultNode.hidden = false;
        resultNode.classList.add("is-success");
        finalizeSuccessfulResult(form);
    }


    export function syncScreedFormGroups(form) {
        const calculator = form.querySelector('[name="calculator"]')?.value;
        if (calculator !== "screed") {
            return;
        }

        const mode = form.querySelector('[name="mode"]')?.value || "dimensions";
        const isAreaMode = mode === "area";
        setModeLockState(form, isAreaMode);

        const includeRebar = form.querySelector('[name="includeReinforcement"]')?.checked === true;
        const dimensionsGroups = form.querySelectorAll('[data-field-group="screed-dimensions"]');
        const areaGroup = form.querySelector('[data-field-group="screed-area"]');
        const heightGroup = form.querySelector('[data-field-group="screed-height"]');
        const rebarAccordion = form.querySelector('[data-toggle-target="screed-reinforcement"]');

        dimensionsGroups.forEach((el) => toggleVisibility(el, mode === "dimensions"));
        toggleVisibility(areaGroup, mode === "area");
        toggleVisibility(heightGroup, true);
        toggleVisibility(rebarAccordion, mode === "dimensions" && includeRebar);
    }


    export function initScreedForm(form) {
        const calculator = form.querySelector('[name="calculator"]')?.value;
        if (calculator !== "screed") {
            return;
        }

        const modeSelect = form.querySelector('[name="mode"]');
        const includeRebar = form.querySelector('[name="includeReinforcement"]');

        syncScreedFormGroups(form);
        [modeSelect, includeRebar].forEach((node) => {
            if (!node) {
                return;
            }
            node.addEventListener("change", () => {
                clearErrors(form);
                markResultStale(form);
                syncScreedFormGroups(form);
            });
        });
    }

export function buildPayload(form, formData) {
    const calculator = readTrimmed(formData, "calculator") || "screed";
    const mode = readTrimmed(formData, "mode");
    const payload = {
      calculator,
      mode,
    };

    let isValid = validateBaseFields(form, payload);


            const isAreaMode = mode === "area";
            const includeReinforcement = isAreaMode
                ? false
                : formData.get("includeReinforcement") !== null;
            payload.includeReinforcement = includeReinforcement;
            payload.height = readTrimmed(formData, "height");

            isValid =
                validatePositiveField(
                    form,
                    payload,
                    "height",
                    "Высота должна быть больше 0."
                ) && isValid;

            if (mode === "dimensions") {
                payload.length = readTrimmed(formData, "length");
                payload.width = readTrimmed(formData, "width");
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "length",
                        "Длина должна быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "width",
                        "Ширина должна быть больше 0."
                    ) && isValid;
            } else if (mode === "area") {
                payload.area = readTrimmed(formData, "area");
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "area",
                        "Площадь должна быть больше 0."
                    ) && isValid;
            } else {
                setFieldError(form, "mode", "Выберите режим dimensions или area.");
                isValid = false;
            }

            if (includeReinforcement) {
                payload.rebarDiameterMm = readTrimmed(formData, "rebarDiameterMm");
                payload.rebarStepMm = readTrimmed(formData, "rebarStepMm");
                payload.rebarLayers = readTrimmed(formData, "rebarLayers");
                payload.rebarReservePercent = readTrimmed(formData, "rebarReservePercent");

                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "rebarDiameterMm",
                        "Диаметр арматуры должен быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "rebarStepMm",
                        "Шаг арматуры должен быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "rebarLayers",
                        "Количество слоёв должно быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "rebarReservePercent",
                        "Запас арматуры должен быть больше 0."
                    ) && isValid;
            }

            const mixtureResult = buildMixturePayload(form, formData, "", {
                allowDryReady: true,
                includeGravel: false,
            });
            payload.mixture = mixtureResult.mixture;
            isValid = mixtureResult.isValid && isValid;

    return {
      isValid,
      payload,
    };
  }

const calculatorModule = {
  calculator: "screed",
  init: initScreedForm,
  buildPayload,
  showResult: showScreedResult,
};

initEstimateForms(calculatorModule);
