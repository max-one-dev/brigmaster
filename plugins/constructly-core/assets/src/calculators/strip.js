import { escapeHtml, formatNumber, hasMeaningfulNumber } from "../core/formatters.js";
import { clearErrors, closeAllTooltips, finalizeSuccessfulResult, getEstimatorShell, initTooltips, isMobileTooltipViewport, markResultStale, openTooltip, positionTooltipWithinViewport, readTrimmed, setFieldError, setModeLockState, setTooltipBackdropVisible, toggleTooltip, toggleVisibility } from "../core/form-state.js";
import { isPositiveInteger, isPositiveNumber, validateBaseFields, validatePositiveField, validateSelectedValue } from "../core/validation.js";
import { buildMixturePayload, syncPileMixtureBlocks } from "../core/mixture.js";
import { buildPileReinforcementColumnsHtml, renderMixtureCard, renderStripReinforcementCard, syncResultGridLayout } from "../ui/result-panel.js";
import { initEstimateForms } from "../core/bootstrap.js";


    export function showStripResult(form, payload) {
        const resultNode = getEstimatorShell(form)?.querySelector("[data-result]");
        if (!resultNode) {
            return;
        }

        const concrete = payload?.concrete || {};
        const reinforcement = payload?.reinforcement || null;
        const formwork = payload?.formwork || null;
        const concreteLengthNode = resultNode.querySelector("[data-result-strip-concrete-length]");
        const concreteVolumeNode = resultNode.querySelector("[data-result-strip-concrete-volume]");
        const reinforcementCard = resultNode.querySelector('[data-result-card="strip-reinforcement"]');
        const formworkCard = resultNode.querySelector('[data-result-card="strip-formwork"]');

        if (concreteLengthNode) {
            concreteLengthNode.textContent = formatNumber(concrete.totalLengthM);
        }
        if (concreteVolumeNode) {
            concreteVolumeNode.textContent = formatNumber(concrete.volumeM3);
        }

        renderStripReinforcementCard(reinforcementCard, reinforcement);

        if (formworkCard) {
            if (formwork) {
                formworkCard.hidden = false;
                const areaNode = formworkCard.querySelector("[data-result-strip-formwork-area]");
                const linearNode = formworkCard.querySelector("[data-result-strip-formwork-linear]");
                if (areaNode) {
                    areaNode.textContent = formatNumber(formwork.totalFormworkAreaWithReserveM2);
                }
                if (linearNode) {
                    linearNode.textContent = formatNumber(formwork.totalFormworkLinearM);
                }
            } else {
                formworkCard.hidden = true;
            }
        }

        renderMixtureCard(
            resultNode.querySelector('[data-result-card="mixture"]'),
            payload?.mixture,
            "",
            { omitVolume: true }
        );

        syncResultGridLayout(resultNode);
        resultNode.hidden = false;
        resultNode.classList.add("is-success");
        finalizeSuccessfulResult(form);
    }


    export function buildStripSegmentPayload(segmentNode, includeReinforcement, includeFormwork) {
        const getSegmentValue = (field) =>
            String(segmentNode.querySelector(`[data-segment-input="${field}"]`)?.value || "").trim();

        const segmentPayload = {
            segmentLengthM: getSegmentValue("segmentLengthM"),
            segmentWidthM: getSegmentValue("segmentWidthM"),
            segmentHeightM: getSegmentValue("segmentHeightM"),
        };

        if (includeReinforcement) {
            const segmentIncludeReinforcement =
                segmentNode.querySelector("[data-segment-include-reinforcement]")?.checked !== false;
            segmentPayload.segmentIncludeReinforcement = segmentIncludeReinforcement;
            if (segmentIncludeReinforcement) {
                const segmentUseGlobalRebarParams =
                    segmentNode.querySelector("[data-segment-use-global-rebar]")?.checked !== false;
                segmentPayload.segmentUseGlobalRebarParams = segmentUseGlobalRebarParams;
                if (!segmentUseGlobalRebarParams) {
                    segmentPayload.segmentLongitudinalBarsCount = getSegmentValue("segmentLongitudinalBarsCount");
                    segmentPayload.segmentLongitudinalDiameterMm = getSegmentValue("segmentLongitudinalDiameterMm");
                    segmentPayload.segmentTransverseDiameterMm = getSegmentValue("segmentTransverseDiameterMm");
                    segmentPayload.segmentTransverseStepMm = getSegmentValue("segmentTransverseStepMm");
                }
            }
        }

        if (includeFormwork) {
            const segmentIncludeFormwork =
                segmentNode.querySelector("[data-segment-include-formwork]")?.checked !== false;
            segmentPayload.segmentIncludeFormwork = segmentIncludeFormwork;
            if (segmentIncludeFormwork) {
                const segmentUseGlobalFormworkParams =
                    segmentNode.querySelector("[data-segment-use-global-formwork]")?.checked !== false;
                segmentPayload.segmentUseGlobalFormworkParams = segmentUseGlobalFormworkParams;
                if (!segmentUseGlobalFormworkParams) {
                    segmentPayload.segmentFormworkHeightM = getSegmentValue("segmentFormworkHeightM");
                }
            }
        }

        return segmentPayload;
    }


    export function createStripSegmentMarkup(index) {
        return `
      <article class="brigmaster-estimator__segment-card" data-strip-segment-item data-segment-index="${index}">
        <div class="brigmaster-estimator__segment-head">
          <h3 class="brigmaster-estimator__segment-title">Участок ${index + 1}</h3>
          <button type="button" class="brigmaster-estimator__segment-remove" data-strip-remove-segment>Удалить</button>
        </div>
        <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three">
          <div class="brigmaster-estimator__field">
            <label for="segment-${index}-length">Длина участка (м)</label>
            <input id="segment-${index}-length" type="number" min="0.01" step="0.01" value="10" data-segment-input="segmentLengthM">
            <div class="brigmaster-estimator__error" data-segment-error-field="segmentLengthM" data-field-error="segments.${index}.segmentLengthM" aria-live="polite"></div>
          </div>
          <div class="brigmaster-estimator__field">
            <label for="segment-${index}-width">Ширина участка (м)</label>
            <input id="segment-${index}-width" type="number" min="0.01" step="0.01" value="0.4" data-segment-input="segmentWidthM">
            <div class="brigmaster-estimator__error" data-segment-error-field="segmentWidthM" data-field-error="segments.${index}.segmentWidthM" aria-live="polite"></div>
          </div>
          <div class="brigmaster-estimator__field">
            <label for="segment-${index}-height">Высота участка (м)</label>
            <input id="segment-${index}-height" type="number" min="0.01" step="0.01" value="1" data-segment-input="segmentHeightM">
            <div class="brigmaster-estimator__error" data-segment-error-field="segmentHeightM" data-field-error="segments.${index}.segmentHeightM" aria-live="polite"></div>
          </div>
        </div>
        <div class="brigmaster-estimator__segment-section" data-segment-rebar-root>
          <div class="brigmaster-estimator__segment-toggles">
            <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
              <input id="segment-${index}-include-rebar" type="checkbox" checked data-segment-include-reinforcement data-checkbox-key="segment-include-rebar">
              <label for="segment-${index}-include-rebar" class="brigmaster-estimator__label-row" data-label-for-checkbox="segment-include-rebar"><span>Учитывать арматуру для этого участка</span></label>
              <div class="brigmaster-estimator__error" data-segment-error-field="segmentIncludeReinforcement" data-field-error="segments.${index}.segmentIncludeReinforcement" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field brigmaster-estimator__toggle brigmaster-estimator__field-group" data-segment-rebar-settings>
              <input id="segment-${index}-use-global-rebar" type="checkbox" checked data-segment-use-global-rebar data-checkbox-key="segment-use-global-rebar">
              <label for="segment-${index}-use-global-rebar" class="brigmaster-estimator__label-row" data-label-for-checkbox="segment-use-global-rebar">
                <span>Использовать общие параметры</span>
                <span class="brigmaster-estimator__tooltip-anchor">
                  <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: использовать общие параметры арматуры" aria-expanded="false" aria-controls="segment-${index}-use-global-rebar-tooltip">i</button>
                  <div id="segment-${index}-use-global-rebar-tooltip" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                    При включении для этого участка применяются общие настройки арматуры из глобального блока ниже.
                  </div>
                </span>
              </label>
              <div class="brigmaster-estimator__error" data-segment-error-field="segmentUseGlobalRebarParams" data-field-error="segments.${index}.segmentUseGlobalRebarParams" aria-live="polite"></div>
            </div>
          </div>
          <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-segment-rebar-local>
            <div class="brigmaster-estimator__field">
              <label for="segment-${index}-longitudinal-bars-count" class="brigmaster-estimator__label-row">
                <span>Кол-во продольных стержней</span>
                <span class="brigmaster-estimator__tooltip-anchor">
                  <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: количество продольных стержней" aria-expanded="false" aria-controls="segment-${index}-seg-long-bars-tooltip">i</button>
                  <div id="segment-${index}-seg-long-bars-tooltip" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                    Число рабочих стержней в сечении этого участка. Обычно 4–6. Больше стержней — выше расход арматуры.
                  </div>
                </span>
              </label>
              <input id="segment-${index}-longitudinal-bars-count" type="number" min="1" step="1" value="4" data-segment-input="segmentLongitudinalBarsCount">
              <p class="brigmaster-estimator__hint">Обычно 4-6 стержней для частного дома.</p>
              <div class="brigmaster-estimator__error" data-segment-error-field="segmentLongitudinalBarsCount" data-field-error="segments.${index}.segmentLongitudinalBarsCount" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
              <label for="segment-${index}-longitudinal-diameter" class="brigmaster-estimator__label-row">
                <span>Диаметр продольной (мм)</span>
                <span class="brigmaster-estimator__tooltip-anchor">
                  <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр продольной арматуры" aria-expanded="false" aria-controls="segment-${index}-seg-long-diameter-tooltip">i</button>
                  <div id="segment-${index}-seg-long-diameter-tooltip" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                    Диаметр рабочих стержней в мм. Типично 10–14 мм. Чем больше диаметр, тем выше масса и прочность.
                  </div>
                </span>
              </label>
              <input id="segment-${index}-longitudinal-diameter" type="number" min="1" step="1" value="12" data-segment-input="segmentLongitudinalDiameterMm">
              <p class="brigmaster-estimator__hint">Чаще всего 10-14 мм.</p>
              <div class="brigmaster-estimator__error" data-segment-error-field="segmentLongitudinalDiameterMm" data-field-error="segments.${index}.segmentLongitudinalDiameterMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
              <label for="segment-${index}-transverse-diameter" class="brigmaster-estimator__label-row">
                <span>Диаметр поперечной (мм)</span>
                <span class="brigmaster-estimator__tooltip-anchor">
                  <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр поперечной арматуры" aria-expanded="false" aria-controls="segment-${index}-seg-transverse-diameter-tooltip">i</button>
                  <div id="segment-${index}-seg-transverse-diameter-tooltip" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                    Диаметр хомутов в мм. Обычно 6–10 мм. Влияет на массу поперечной арматуры.
                  </div>
                </span>
              </label>
              <input id="segment-${index}-transverse-diameter" type="number" min="1" step="1" value="8" data-segment-input="segmentTransverseDiameterMm">
              <p class="brigmaster-estimator__hint">Обычно 6-10 мм для хомутов.</p>
              <div class="brigmaster-estimator__error" data-segment-error-field="segmentTransverseDiameterMm" data-field-error="segments.${index}.segmentTransverseDiameterMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
              <label for="segment-${index}-transverse-step" class="brigmaster-estimator__label-row">
                <span>Шаг поперечной (мм)</span>
                <span class="brigmaster-estimator__tooltip-anchor">
                  <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: шаг поперечной арматуры" aria-expanded="false" aria-controls="segment-${index}-seg-transverse-step-tooltip">i</button>
                  <div id="segment-${index}-seg-transverse-step-tooltip" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                    Расстояние между хомутами в мм. Типично 200–400 мм. Меньший шаг — больше хомутов и расход стали.
                  </div>
                </span>
              </label>
              <input id="segment-${index}-transverse-step" type="number" min="10" step="10" value="300" data-segment-input="segmentTransverseStepMm">
              <p class="brigmaster-estimator__hint">Меньше шаг = больше хомутов и расход стали.</p>
              <div class="brigmaster-estimator__error" data-segment-error-field="segmentTransverseStepMm" data-field-error="segments.${index}.segmentTransverseStepMm" aria-live="polite"></div>
            </div>
          </div>
        </div>
        <div class="brigmaster-estimator__segment-section" data-segment-formwork-root>
          <div class="brigmaster-estimator__segment-toggles">
            <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
              <input id="segment-${index}-include-formwork" type="checkbox" checked data-segment-include-formwork data-checkbox-key="segment-include-formwork">
              <label for="segment-${index}-include-formwork" class="brigmaster-estimator__label-row" data-label-for-checkbox="segment-include-formwork"><span>Учитывать опалубку для этого участка</span></label>
              <div class="brigmaster-estimator__error" data-segment-error-field="segmentIncludeFormwork" data-field-error="segments.${index}.segmentIncludeFormwork" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field brigmaster-estimator__toggle brigmaster-estimator__field-group" data-segment-formwork-settings>
              <input id="segment-${index}-use-global-formwork" type="checkbox" checked data-segment-use-global-formwork data-checkbox-key="segment-use-global-formwork">
              <label for="segment-${index}-use-global-formwork" class="brigmaster-estimator__label-row" data-label-for-checkbox="segment-use-global-formwork">
                <span>Использовать общие параметры</span>
                <span class="brigmaster-estimator__tooltip-anchor">
                  <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: использовать общие параметры опалубки" aria-expanded="false" aria-controls="segment-${index}-use-global-formwork-tooltip">i</button>
                  <div id="segment-${index}-use-global-formwork-tooltip" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                    При включении для этого участка применяются общие настройки опалубки из глобального блока ниже.
                  </div>
                </span>
              </label>
              <div class="brigmaster-estimator__error" data-segment-error-field="segmentUseGlobalFormworkParams" data-field-error="segments.${index}.segmentUseGlobalFormworkParams" aria-live="polite"></div>
            </div>
          </div>
          <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-segment-formwork-local>
            <div class="brigmaster-estimator__field">
              <label for="segment-${index}-formwork-height">Высота опалубки участка (м)</label>
              <input id="segment-${index}-formwork-height" type="number" min="0.01" step="0.01" value="0.8" data-segment-input="segmentFormworkHeightM">
              <p class="brigmaster-estimator__hint">Считаются только боковые щиты участка.</p>
              <div class="brigmaster-estimator__error" data-segment-error-field="segmentFormworkHeightM" data-field-error="segments.${index}.segmentFormworkHeightM" aria-live="polite"></div>
            </div>
          </div>
        </div>
      </article>
    `;
    }


    export function reindexStripSegments(form) {
        const listNode = form.querySelector("[data-strip-segments-list]");
        if (!listNode) {
            return;
        }
        const segmentNodes = listNode.querySelectorAll("[data-strip-segment-item]");
        segmentNodes.forEach((segmentNode, index) => {
            segmentNode.dataset.segmentIndex = String(index);
            const titleNode = segmentNode.querySelector(".brigmaster-estimator__segment-title");
            if (titleNode) {
                titleNode.textContent = `Участок ${index + 1}`;
            }
            const errorNodes = segmentNode.querySelectorAll("[data-segment-error-field]");
            errorNodes.forEach((errorNode) => {
                const field = errorNode.getAttribute("data-segment-error-field");
                if (field) {
                    errorNode.setAttribute("data-field-error", `segments.${index}.${field}`);
                }
            });
            const removeButton = segmentNode.querySelector("[data-strip-remove-segment]");
            if (removeButton) {
                removeButton.disabled = segmentNodes.length === 1;
            }

            const checkboxMappings = [
                { key: "segment-include-rebar", id: `segment-${index}-include-rebar` },
                { key: "segment-use-global-rebar", id: `segment-${index}-use-global-rebar` },
                { key: "segment-include-formwork", id: `segment-${index}-include-formwork` },
                { key: "segment-use-global-formwork", id: `segment-${index}-use-global-formwork` },
            ];
            checkboxMappings.forEach(({ key, id }) => {
                const checkbox = segmentNode.querySelector(`[data-checkbox-key="${key}"]`);
                const label = segmentNode.querySelector(`[data-label-for-checkbox="${key}"]`);
                if (checkbox) {
                    checkbox.id = id;
                }
                if (label) {
                    label.setAttribute("for", id);
                }
            });

            const inputMappings = [
                { field: "segmentLengthM", id: `segment-${index}-length` },
                { field: "segmentWidthM", id: `segment-${index}-width` },
                { field: "segmentHeightM", id: `segment-${index}-height` },
                { field: "segmentLongitudinalBarsCount", id: `segment-${index}-longitudinal-bars-count` },
                { field: "segmentLongitudinalDiameterMm", id: `segment-${index}-longitudinal-diameter` },
                { field: "segmentTransverseDiameterMm", id: `segment-${index}-transverse-diameter` },
                { field: "segmentTransverseStepMm", id: `segment-${index}-transverse-step` },
                { field: "segmentFormworkHeightM", id: `segment-${index}-formwork-height` },
            ];
            inputMappings.forEach(({ field, id }) => {
                const input = segmentNode.querySelector(`[data-segment-input="${field}"]`);
                if (input) {
                    input.id = id;
                    const fieldWrap = input.closest(".brigmaster-estimator__field");
                    const label = fieldWrap?.querySelector("label[for]");
                    if (label) {
                        label.setAttribute("for", id);
                    }
                }
            });

            const rebarSettings = segmentNode.querySelector("[data-segment-rebar-settings]");
            const formworkSettings = segmentNode.querySelector("[data-segment-formwork-settings]");
            const rebarLocal = segmentNode.querySelector("[data-segment-rebar-local]");
            const formworkLocal = segmentNode.querySelector("[data-segment-formwork-local]");
            const useGlobalRebarNode = segmentNode.querySelector("[data-segment-use-global-rebar]");
            const useGlobalFormworkNode = segmentNode.querySelector("[data-segment-use-global-formwork]");
            if (index === 0) {
                if (useGlobalRebarNode) {
                    useGlobalRebarNode.checked = true;
                    useGlobalRebarNode.disabled = true;
                }
                if (useGlobalFormworkNode) {
                    useGlobalFormworkNode.checked = true;
                    useGlobalFormworkNode.disabled = true;
                }
                toggleVisibility(rebarSettings, false);
                toggleVisibility(formworkSettings, false);
                toggleVisibility(rebarLocal, false);
                toggleVisibility(formworkLocal, false);
            } else {
                if (useGlobalRebarNode) {
                    useGlobalRebarNode.disabled = false;
                }
                if (useGlobalFormworkNode) {
                    useGlobalFormworkNode.disabled = false;
                }
            }
        });
    }


    export function syncStripSegmentVisibility(segmentNode, includeReinforcementGlobal, includeFormworkGlobal) {
        const includeRebarNode = segmentNode.querySelector("[data-segment-include-reinforcement]");
        const useGlobalRebarNode = segmentNode.querySelector("[data-segment-use-global-rebar]");
        const rebarSettings = segmentNode.querySelector("[data-segment-rebar-settings]");
        const rebarLocal = segmentNode.querySelector("[data-segment-rebar-local]");
        const includeFormworkNode = segmentNode.querySelector("[data-segment-include-formwork]");
        const useGlobalFormworkNode = segmentNode.querySelector("[data-segment-use-global-formwork]");
        const formworkSettings = segmentNode.querySelector("[data-segment-formwork-settings]");
        const formworkLocal = segmentNode.querySelector("[data-segment-formwork-local]");

        const includeRebarSegment = includeReinforcementGlobal && !!includeRebarNode?.checked;
        const useGlobalRebar = !!useGlobalRebarNode?.checked;
        const includeFormworkSegment = includeFormworkGlobal && !!includeFormworkNode?.checked;
        const useGlobalFormwork = !!useGlobalFormworkNode?.checked;
        const isFirstSegment = segmentNode.dataset.segmentIndex === "0";

        if (useGlobalRebarNode) {
            const shouldDisableUseGlobalRebar =
                isFirstSegment || !includeReinforcementGlobal || !includeRebarSegment;
            useGlobalRebarNode.disabled = shouldDisableUseGlobalRebar;
            if (shouldDisableUseGlobalRebar) {
                useGlobalRebarNode.checked = true;
            }
        }
        if (useGlobalFormworkNode) {
            const shouldDisableUseGlobalFormwork =
                isFirstSegment || !includeFormworkGlobal || !includeFormworkSegment;
            useGlobalFormworkNode.disabled = shouldDisableUseGlobalFormwork;
            if (shouldDisableUseGlobalFormwork) {
                useGlobalFormworkNode.checked = true;
            }
        }

        toggleVisibility(rebarSettings, !isFirstSegment && includeReinforcementGlobal);
        toggleVisibility(
            rebarLocal,
            includeReinforcementGlobal &&
            includeRebarSegment &&
            !isFirstSegment &&
            !useGlobalRebarNode?.checked
        );
        toggleVisibility(formworkSettings, !isFirstSegment && includeFormworkGlobal);
        toggleVisibility(
            formworkLocal,
            includeFormworkGlobal &&
            includeFormworkSegment &&
            !isFirstSegment &&
            !useGlobalFormworkNode?.checked
        );
    }


    export function syncStripFoundationGroups(form) {
        const calculator = form.querySelector('[name="calculator"]')?.value;
        if (calculator !== "strip_foundation" && calculator !== "pile_foundation") {
            return;
        }

        const mode = form.querySelector('[name="mode"]')?.value || "perimeter";
        const includeGrillage = calculator === "pile_foundation"
            ? !!form.querySelector('[name="includeGrillage"]')?.checked
            : true;
        const includeReinforcement = includeGrillage && !!form.querySelector('[name="includeReinforcement"]')?.checked;
        const includeFormwork = includeGrillage && !!form.querySelector('[name="includeFormwork"]')?.checked;
        const perimeterGroup = form.querySelector('[data-field-group="strip-perimeter"]');
        const houseGroup = form.querySelector('[data-field-group="strip-house"]');
        const segmentsGroup = form.querySelector('[data-field-group="strip-segments"]');
        const globalRebarGroup = form.querySelector('[data-field-group="strip-reinforcement-global"]');
        const globalFormworkGroup = form.querySelector('[data-field-group="strip-formwork-global"]');
        const pileTypeGroup = form.querySelector('[data-field-group="pile-type"]');
        const pilePrimaryRowGroup = form.querySelector('[data-field-group="pile-primary-row"]');
        const pilePrimaryGrid = form.querySelector("[data-pile-primary-grid]");
        const pileShaftDiameterCell = form.querySelector('[data-pile-primary-cell="shaft-diameter"]');
        const pileShaftHeightCell = form.querySelector('[data-pile-primary-cell="shaft-height"]');
        const pileBaseToggleGroup = form.querySelector('[data-field-group="pile-base-toggle"]');
        const pileBaseFieldsGroup = form.querySelector('[data-field-group="pile-base-fields"]');
        const pileReinforcementToggleGroup = form.querySelector('[data-field-group="pile-reinforcement-toggle"]');
        const pileReinforcementFieldsGroup = form.querySelector('[data-field-group="pile-reinforcement-fields"]');
        const reinforcementToggleRow = form.querySelector('[data-toggle-field="strip-reinforcement"]');
        const formworkToggleRow = form.querySelector('[data-toggle-field="strip-formwork"]');
        const modeField = form.querySelector('[data-field-group="estimator-mode"]');

        if (calculator === "pile_foundation") {
            const includePiles = !!form.querySelector('[name="includePiles"]')?.checked;
            const pileType = form.querySelector('[name="pileType"]')?.value || "bored";
            const includePileBase = !!form.querySelector('[name="includePileBase"]')?.checked;
            const includePileReinforcement = !!form.querySelector('[name="includePileReinforcement"]')?.checked;
            const isBored = pileType === "bored";

            toggleVisibility(pileTypeGroup, includePiles);
            toggleVisibility(pilePrimaryRowGroup, includePiles);
            toggleVisibility(pileShaftDiameterCell, includePiles && isBored);
            toggleVisibility(pileShaftHeightCell, includePiles && isBored);
            if (pilePrimaryGrid) {
                pilePrimaryGrid.classList.toggle("is-bored-layout", includePiles && isBored);
            }
            toggleVisibility(modeField, includeGrillage);
            toggleVisibility(pileBaseToggleGroup, includePiles && isBored);
            toggleVisibility(pileBaseFieldsGroup, includePiles && isBored && includePileBase);
            toggleVisibility(pileReinforcementToggleGroup, includePiles && isBored);
            toggleVisibility(
                pileReinforcementFieldsGroup,
                includePiles && isBored && includePileReinforcement
            );
            toggleVisibility(reinforcementToggleRow, includeGrillage);
            toggleVisibility(formworkToggleRow, includeGrillage);

            const pilesPanel = form.querySelector('[data-pile-panel="piles"]');
            const grillagePanels = form.querySelectorAll('[data-pile-panel="grillage"]');
            if (pilesPanel instanceof HTMLDetailsElement) {
                pilesPanel.hidden = !includePiles;
            }
            grillagePanels.forEach((panel) => {
                if (panel instanceof HTMLDetailsElement) {
                    panel.hidden = !includeGrillage;
                }
            });
        }

        toggleVisibility(perimeterGroup, includeGrillage && mode === "perimeter");
        toggleVisibility(houseGroup, includeGrillage && mode === "house");
        toggleVisibility(segmentsGroup, includeGrillage && mode === "segments");
        toggleVisibility(globalRebarGroup, includeReinforcement);
        toggleVisibility(globalFormworkGroup, includeFormwork);

        const segmentNodes = form.querySelectorAll("[data-strip-segment-item]");
        segmentNodes.forEach((segmentNode) => {
            syncStripSegmentVisibility(segmentNode, includeReinforcement, includeFormwork);
        });
    }


    export function initStripFoundationForm(form) {
        const calculator = form.querySelector('[name="calculator"]')?.value;
        if (calculator !== "strip_foundation" && calculator !== "pile_foundation") {
            return;
        }

        const modeSelect = form.querySelector('[name="mode"]');
        const includeReinforcement = form.querySelector('[name="includeReinforcement"]');
        const includeFormwork = form.querySelector('[name="includeFormwork"]');
        const includePiles = form.querySelector('[name="includePiles"]');
        const includeGrillage = form.querySelector('[name="includeGrillage"]');
        const pileType = form.querySelector('[name="pileType"]');
        const includePileBase = form.querySelector('[name="includePileBase"]');
        const includePileReinforcement = form.querySelector('[name="includePileReinforcement"]');
        const addSegmentButton = form.querySelector("[data-strip-add-segment]");
        const segmentsList = form.querySelector("[data-strip-segments-list]");

        const refresh = () => {
            reindexStripSegments(form);
            syncStripFoundationGroups(form);
        };

        [modeSelect, includeReinforcement, includeFormwork, includePiles, includeGrillage, pileType, includePileBase, includePileReinforcement].forEach((node) => {
            if (!node) {
                return;
            }
            node.addEventListener("change", () => {
                clearErrors(form);
                markResultStale(form);
                refresh();
            });
        });

        if (addSegmentButton && segmentsList) {
            addSegmentButton.addEventListener("click", () => {
                clearErrors(form);
                markResultStale(form);
                const nextIndex = segmentsList.querySelectorAll("[data-strip-segment-item]").length;
                segmentsList.insertAdjacentHTML("beforeend", createStripSegmentMarkup(nextIndex));
                refresh();
                initTooltips(form);
            });

            segmentsList.addEventListener("click", (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }
                const removeButton = target.closest("[data-strip-remove-segment]");
                if (!removeButton) {
                    return;
                }
                const segmentNodes = segmentsList.querySelectorAll("[data-strip-segment-item]");
                if (segmentNodes.length <= 1) {
                    return;
                }
                const segmentNode = removeButton.closest("[data-strip-segment-item]");
                if (!segmentNode) {
                    return;
                }
                segmentNode.remove();
                clearErrors(form);
                markResultStale(form);
                refresh();
            });

            segmentsList.addEventListener("change", (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }
                if (
                    target.matches("[data-segment-include-reinforcement]") ||
                    target.matches("[data-segment-use-global-rebar]") ||
                    target.matches("[data-segment-include-formwork]") ||
                    target.matches("[data-segment-use-global-formwork]")
                ) {
                    clearErrors(form);
                    markResultStale(form);
                    refresh();
                }
            });
        }

        refresh();
    }

export function buildPayload(form, formData) {
    const calculator = readTrimmed(formData, "calculator") || "strip_foundation";
    const mode = readTrimmed(formData, "mode");
    const payload = {
      calculator,
      mode,
    };

    let isValid = validateBaseFields(form, payload);


            const includeReinforcement = formData.get("includeReinforcement") !== null;
            const includeFormwork = formData.get("includeFormwork") !== null;
            payload.includeReinforcement = includeReinforcement;
            payload.includeFormwork = includeFormwork;

            if (mode === "perimeter") {
                payload.totalLengthM = readTrimmed(formData, "totalLengthM");
                payload.widthM = readTrimmed(formData, "widthM");
                payload.heightM = readTrimmed(formData, "heightM");
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "totalLengthM",
                        "Общая длина ленты должна быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "widthM",
                        "Ширина ленты должна быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "heightM",
                        "Высота ленты должна быть больше 0."
                    ) && isValid;
            } else if (mode === "house") {
                payload.houseLengthM = readTrimmed(formData, "houseLengthM");
                payload.houseWidthM = readTrimmed(formData, "houseWidthM");
                payload.widthM = String(form.querySelector("[data-strip-house-width-input]")?.value || "").trim();
                payload.heightM = String(form.querySelector("[data-strip-house-height-input]")?.value || "").trim();
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "houseLengthM",
                        "Длина дома должна быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "houseWidthM",
                        "Ширина дома должна быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "widthM",
                        "Ширина ленты должна быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "heightM",
                        "Высота ленты должна быть больше 0."
                    ) && isValid;
            } else if (mode === "segments") {
                const segmentNodes = form.querySelectorAll("[data-strip-segment-item]");
                payload.segments = Array.from(segmentNodes).map((segmentNode) =>
                    buildStripSegmentPayload(segmentNode, includeReinforcement, includeFormwork)
                );
                if (!payload.segments.length) {
                    setFieldError(form, "segments", "Добавьте хотя бы один участок.");
                    isValid = false;
                }
                payload.segments.forEach((segment, index) => {
                    const lengthField = `segments.${index}.segmentLengthM`;
                    const widthField = `segments.${index}.segmentWidthM`;
                    const heightField = `segments.${index}.segmentHeightM`;
                    if (!isPositiveNumber(segment.segmentLengthM)) {
                        setFieldError(form, lengthField, "Длина участка должна быть больше 0.");
                        isValid = false;
                    }
                    if (!isPositiveNumber(segment.segmentWidthM)) {
                        setFieldError(form, widthField, "Ширина участка должна быть больше 0.");
                        isValid = false;
                    }
                    if (!isPositiveNumber(segment.segmentHeightM)) {
                        setFieldError(form, heightField, "Высота участка должна быть больше 0.");
                        isValid = false;
                    }

                    if (includeReinforcement && segment.segmentIncludeReinforcement && segment.segmentUseGlobalRebarParams === false) {
                        if (!isPositiveNumber(segment.segmentLongitudinalBarsCount)) {
                            setFieldError(
                                form,
                                `segments.${index}.segmentLongitudinalBarsCount`,
                                "Количество продольных стержней должно быть больше 0."
                            );
                            isValid = false;
                        }
                        if (!isPositiveNumber(segment.segmentLongitudinalDiameterMm)) {
                            setFieldError(
                                form,
                                `segments.${index}.segmentLongitudinalDiameterMm`,
                                "Диаметр продольной арматуры должен быть больше 0."
                            );
                            isValid = false;
                        }
                        if (!isPositiveNumber(segment.segmentTransverseDiameterMm)) {
                            setFieldError(
                                form,
                                `segments.${index}.segmentTransverseDiameterMm`,
                                "Диаметр поперечной арматуры должен быть больше 0."
                            );
                            isValid = false;
                        }
                        if (!isPositiveNumber(segment.segmentTransverseStepMm)) {
                            setFieldError(
                                form,
                                `segments.${index}.segmentTransverseStepMm`,
                                "Шаг поперечной арматуры должен быть больше 0."
                            );
                            isValid = false;
                        }
                    }

                    if (includeFormwork && segment.segmentIncludeFormwork && segment.segmentUseGlobalFormworkParams === false) {
                        if (!isPositiveNumber(segment.segmentFormworkHeightM)) {
                            setFieldError(
                                form,
                                `segments.${index}.segmentFormworkHeightM`,
                                "Высота опалубки участка должна быть больше 0."
                            );
                            isValid = false;
                        }
                    }
                });
            } else {
                setFieldError(form, "mode", "Выберите режим perimeter, house или segments.");
                isValid = false;
            }

            if (includeReinforcement) {
                payload.longitudinalBarsCount = readTrimmed(formData, "longitudinalBarsCount");
                payload.longitudinalDiameterMm = readTrimmed(formData, "longitudinalDiameterMm");
                payload.longitudinalReservePercent = readTrimmed(formData, "longitudinalReservePercent");
                payload.transverseDiameterMm = readTrimmed(formData, "transverseDiameterMm");
                payload.transverseStepMm = readTrimmed(formData, "transverseStepMm");
                payload.transverseReservePercent = readTrimmed(formData, "transverseReservePercent");

                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "longitudinalBarsCount",
                        "Количество продольных стержней должно быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "longitudinalDiameterMm",
                        "Диаметр продольной арматуры должен быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "longitudinalReservePercent",
                        "Запас продольной арматуры должен быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "transverseDiameterMm",
                        "Диаметр поперечной арматуры должен быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "transverseStepMm",
                        "Шаг поперечной арматуры должен быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "transverseReservePercent",
                        "Запас поперечной арматуры должен быть больше 0."
                    ) && isValid;
            }

            if (includeFormwork) {
                payload.formworkHeightM = readTrimmed(formData, "formworkHeightM");
                payload.formworkReservePercent = readTrimmed(formData, "formworkReservePercent");

                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "formworkHeightM",
                        "Высота опалубки должна быть больше 0."
                    ) && isValid;
                isValid =
                    validatePositiveField(
                        form,
                        payload,
                        "formworkReservePercent",
                        "Запас опалубки должен быть больше 0."
                    ) && isValid;
            }

            const mixtureResult = buildMixturePayload(form, formData, "", {
                allowDryReady: false,
                includeGravel: true,
            });
            payload.mixture = mixtureResult.mixture;
            isValid = mixtureResult.isValid && isValid;

    return {
      isValid,
      payload,
    };
  }

const calculatorModule = {
  calculator: "strip_foundation",
  init: initStripFoundationForm,
  buildPayload,
  showResult: showStripResult,
};

initEstimateForms(calculatorModule);
