import { escapeHtml, formatNumber, hasMeaningfulNumber } from "../core/formatters.js";
import { clearErrors, closeAllTooltips, finalizeSuccessfulResult, getEstimatorShell, initTooltips, isMobileTooltipViewport, markResultStale, openTooltip, positionTooltipWithinViewport, readTrimmed, setFieldError, setModeLockState, setTooltipBackdropVisible, toggleTooltip, toggleVisibility } from "../core/form-state.js";
import { isPositiveInteger, isPositiveNumber, validateBaseFields, validatePositiveField, validateSelectedValue } from "../core/validation.js";
import { buildMixturePayload, syncPileMixtureBlocks } from "../core/mixture.js";
import { renderMixtureCard, renderStripReinforcementCard } from "../ui/result-panel.js";
import { initEstimateForms } from "../core/bootstrap.js";


export function showDrywallResult(form, payload) {
    const resultNode = getEstimatorShell(form)?.querySelector("[data-result]");
    if (!resultNode) { return; }

    const geometry  = payload?.geometry  || {};
    const sheets    = payload?.sheets    || {};
    const profiles  = payload?.profiles  || {};
    const fasteners = payload?.fasteners || {};
    const finishing = payload?.finishing || {};
    const costs     = payload?.costs     || {};
    const notes     = payload?.notes     || {};

    // Helper: one material row
    const row = (label, value, note) => {
        const noteHtml = note ? `<span class="bm-calculator-result__material-note">${escapeHtml(String(note))}</span>` : '';
        return `<div class="bm-calculator-result__material"><span class="bm-calculator-result__material-head"><span>${escapeHtml(String(label))}</span><strong>${value}</strong></span>${noteHtml}</div>`;
    };

    // Helper: rebuild full card innerHTML
    const fillCard = (card, title, rows, extraHtml) => {
        if (!card) { return; }
        card.innerHTML = `<h3 class="bm-calculator-result__section-title">${escapeHtml(String(title))}</h3><div class="bm-calculator-result__list">${rows.join('')}</div>${extraHtml || ''}`;
    };

    // --- drywall-geometry (always visible) ---
    const geometryCard = resultNode.querySelector('[data-result-card="drywall-geometry"]');
    if (geometryCard) {
        const rows = [row('Общая площадь', `${formatNumber(geometry.grossAreaM2)} м²`)];
        if (Number(geometry.openingsAreaM2) > 0) {
            rows.push(row('Проёмы', `${formatNumber(geometry.openingsAreaM2)} м²`));
        }
        rows.push(row('Чистая площадь', `${formatNumber(geometry.netAreaM2)} м²`));
        rows.push(row('Площадь обшивки без запаса', `${formatNumber(geometry.boardAreaExactM2)} м²`));
        rows.push(row('Площадь обшивки с запасом', `${formatNumber(geometry.boardAreaWithReserveM2)} м²`));
        if (Number(geometry.partitionThicknessMm) > 0) {
            rows.push(row('Толщина перегородки', `${formatNumber(geometry.partitionThicknessMm)} мм`));
        }
        if (Number(geometry.endCladdingAreaM2) > 0) {
            rows.push(row('Торцы проёмов', `${formatNumber(geometry.endCladdingAreaM2)} м²`));
        }
        const methodNote = notes.method
            ? `<p class="bm-calculator-result__material-note">${escapeHtml(notes.method)}</p>`
            : '';
        fillCard(geometryCard, 'Геометрия', rows, methodNote);
    }

    // --- drywall-sheets (always visible) ---
    const sheetsCard = resultNode.querySelector('[data-result-card="drywall-sheets"]');
    if (sheetsCard) {
        fillCard(sheetsCard, 'Листы ГКЛ', [
            row('Формат листа', `${formatNumber(sheets.sheetLengthMm)}×${formatNumber(sheets.sheetWidthMm)} мм`),
            row('Толщина', `${formatNumber(sheets.sheetThicknessMm)} мм`),
            row('Слоёв обшивки', formatNumber(sheets.layers)),
            row('Листов без запаса', `${formatNumber(sheets.countExact)} шт`),
            row('Листов с запасом', `${formatNumber(sheets.countWithReserve)} шт`),
            row('К покупке', `${formatNumber(sheets.countToBuy)} шт`),
        ]);
    }

    // --- drywall-profiles (always visible, content differs by mode) ---
    const profilesCard = resultNode.querySelector('[data-result-card="drywall-profiles"]');
    if (profilesCard) {
        if (profiles.enabled) {
            const rows = [];
            [profiles.guide, profiles.main, profiles.cross].forEach((item) => {
                if (!item || Number(item.lengthM) <= 0) { return; }
                rows.push(row(item.label || 'Профиль', `${formatNumber(item.lengthToBuyM)} м <span class="bm-result-with-reserve">(с запасом)</span>`, `по расчёту ${formatNumber(item.lengthM)} м`));
            });
            fillCard(profilesCard, 'Профили', rows);
        } else {
            const noteText = notes.profilesAreaMode || 'Для профилей и крепежа нужен режим по размерам.';
            fillCard(profilesCard, 'Профили', [],
                `<p class="bm-calculator-result__material-note">${escapeHtml(noteText)}</p>`);
        }
    }

    // --- drywall-fasteners (always visible, content differs by mode) ---
    const fastenersCard = resultNode.querySelector('[data-result-card="drywall-fasteners"]');
    if (fastenersCard) {
        const rows = [];
        [fasteners.boardScrews, fasteners.connectorScrews, fasteners.dowels, fasteners.hangers, fasteners.crabs].forEach((item) => {
            if (!item || Number(item.countBase) <= 0) { return; }
            rows.push(row(item.label || 'Позиция', `${formatNumber(item.countWithReserve)} шт <span class="bm-result-with-reserve">(с запасом)</span>`, `по расчёту ${formatNumber(item.countBase)} шт`));
        });
        if (rows.length) {
            fillCard(fastenersCard, 'Метизы и крепёж', rows);
        } else {
            const noteText = notes.profilesAreaMode || 'Крепёж по каркасу считается только в режиме по размерам.';
            fillCard(fastenersCard, 'Метизы и крепёж', [],
                `<p class="bm-calculator-result__material-note">${escapeHtml(noteText)}</p>`);
        }
    }

    // --- drywall-finishing (conditional) ---
    const finishingCard = resultNode.querySelector('[data-result-card="drywall-finishing"]');
    if (finishingCard) {
        if (finishing.enabled) {
            finishingCard.hidden = false;
            fillCard(finishingCard, 'Отделка', [
                row('Грунтовка', `${formatNumber(finishing.primerKg)} кг`),
                row('Шпатлёвка для швов', `${formatNumber(finishing.jointPuttyKg)} кг`),
                row('Финишная шпатлёвка', `${formatNumber(finishing.finishPuttyKg)} кг`),
                row('Армирующая лента', `${formatNumber(finishing.tapeLm)} м`),
            ]);
        } else {
            finishingCard.hidden = true;
        }
    }

    // --- drywall-costs (conditional) ---
    const costsCard = resultNode.querySelector('[data-result-card="drywall-costs"]');
    if (costsCard) {
        const costRows = [];
        if (hasMeaningfulNumber(costs.sheetCost))      { costRows.push(row('Листы ГКЛ',              formatNumber(Math.round(costs.sheetCost)))); }
        if (hasMeaningfulNumber(costs.profileCost))    { costRows.push(row('Профили',                formatNumber(Math.round(costs.profileCost)))); }
        if (hasMeaningfulNumber(costs.fastenersCost))  { costRows.push(row('Метизы',                 formatNumber(Math.round(costs.fastenersCost)))); }
        if (hasMeaningfulNumber(costs.primerCost))     { costRows.push(row('Грунтовка',              formatNumber(Math.round(costs.primerCost)))); }
        if (hasMeaningfulNumber(costs.jointPuttyCost)) { costRows.push(row('Шпатлёвка для швов',     formatNumber(Math.round(costs.jointPuttyCost)))); }
        if (hasMeaningfulNumber(costs.finishPuttyCost)){ costRows.push(row('Финишная шпатлёвка',     formatNumber(Math.round(costs.finishPuttyCost)))); }
        if (hasMeaningfulNumber(costs.tapeCost))       { costRows.push(row('Армирующая лента',        formatNumber(Math.round(costs.tapeCost)))); }
        if (hasMeaningfulNumber(costs.total))          { costRows.push(row('Итого',                  formatNumber(Math.round(costs.total)))); }

        if (costRows.length) {
            costsCard.hidden = false;
            fillCard(costsCard, 'Стоимость', costRows);
        } else {
            costsCard.hidden = true;
        }
    }

    resultNode.hidden = false;
    resultNode.classList.add("is-success");
    finalizeSuccessfulResult(form);
}


    export function buildDrywallRepeatItems(form) {
        const list = form.querySelector('[data-drywall-repeat-list="drywallOpenings"]');
        if (!list) {
            return [];
        }

        return Array.from(list.querySelectorAll("[data-drywall-repeat-item]")).map((itemNode) => ({
            type: String(itemNode.querySelector('[data-drywall-repeat-input="type"]')?.value || "").trim(),
            widthM: String(itemNode.querySelector('[data-drywall-repeat-input="widthM"]')?.value || "").trim(),
            heightM: String(itemNode.querySelector('[data-drywall-repeat-input="heightM"]')?.value || "").trim(),
            count: String(itemNode.querySelector('[data-drywall-repeat-input="count"]')?.value || "").trim(),
        }));
    }


    export function validateDrywallOpenings(form, items) {
        let isValid = true;

        items.forEach((item, index) => {
            if (!["window", "door"].includes(item.type)) {
                setFieldError(form, `drywallOpenings.${index}.type`, "Выберите тип проёма.");
                isValid = false;
            }
            if (!isPositiveNumber(item.widthM)) {
                setFieldError(form, `drywallOpenings.${index}.widthM`, "Проём: ширина должна быть больше 0.");
                isValid = false;
            }
            if (!isPositiveNumber(item.heightM)) {
                setFieldError(form, `drywallOpenings.${index}.heightM`, "Проём: высота должна быть больше 0.");
                isValid = false;
            }
            if (!isPositiveInteger(item.count)) {
                setFieldError(form, `drywallOpenings.${index}.count`, "Проём: количество должно быть целым числом больше 0.");
                isValid = false;
            }
        });

        return isValid;
    }


    export function splitDrywallOpenings(items) {
        const windows = [];
        const doors = [];

        items.forEach((item) => {
            const normalizedItem = {
                widthM: item.widthM,
                heightM: item.heightM,
                count: item.count,
            };

            if (item.type === "door") {
                doors.push(normalizedItem);
            } else {
                windows.push(normalizedItem);
            }
        });

        return { windows, doors };
    }


    export function createDrywallRepeatMarkup(index) {
        return `
      <article class="brigmaster-estimator__segment-card" data-drywall-repeat-item>
        <div class="brigmaster-estimator__segment-head">
          <h4 class="brigmaster-estimator__segment-title">Проём ${index + 1}</h4>
          <button type="button" class="brigmaster-estimator__segment-remove" data-drywall-remove-item>Удалить</button>
        </div>
        <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
          <div class="brigmaster-estimator__field">
            <label>Тип проёма</label>
            <select data-drywall-repeat-input="type">
              <option value="window">Окно</option>
              <option value="door">Дверь</option>
            </select>
            <div class="brigmaster-estimator__error" data-field-error="drywallOpenings.${index}.type" aria-live="polite"></div>
          </div>
          <div class="brigmaster-estimator__field">
            <label>Ширина (м)</label>
            <input type="number" min="0.01" step="0.01" value="0.9" data-drywall-repeat-input="widthM">
            <div class="brigmaster-estimator__error" data-field-error="drywallOpenings.${index}.widthM" aria-live="polite"></div>
          </div>
          <div class="brigmaster-estimator__field">
            <label>Высота (м)</label>
            <input type="number" min="0.01" step="0.01" value="2.1" data-drywall-repeat-input="heightM">
            <div class="brigmaster-estimator__error" data-field-error="drywallOpenings.${index}.heightM" aria-live="polite"></div>
          </div>
          <div class="brigmaster-estimator__field">
            <label>Количество</label>
            <input type="number" min="1" step="1" value="1" data-drywall-repeat-input="count">
            <div class="brigmaster-estimator__error" data-field-error="drywallOpenings.${index}.count" aria-live="polite"></div>
          </div>
        </div>
      </article>
    `;
    }


    export function reindexDrywallRepeatList(listNode) {
        const listId = listNode.id || "drywall-openings";
        const items = listNode.querySelectorAll("[data-drywall-repeat-item]");
        items.forEach((itemNode, index) => {
            const baseId = `${listId}-${index}`;
            const titleNode = itemNode.querySelector(".brigmaster-estimator__segment-title");
            if (titleNode) {
                titleNode.textContent = `Проём ${index + 1}`;
            }
            const removeButton = itemNode.querySelector("[data-drywall-remove-item]");
            if (removeButton) {
                removeButton.disabled = items.length === 1;
            }
            itemNode.querySelectorAll("[data-drywall-repeat-input]").forEach((inputNode) => {
                const fieldKey = inputNode.getAttribute("data-drywall-repeat-input") || "value";
                const inputId = `${baseId}-${fieldKey}`;
                inputNode.id = inputId;
                const fieldNode = inputNode.closest(".brigmaster-estimator__field");
                const labelNode = fieldNode?.querySelector("label");
                if (labelNode) {
                    labelNode.setAttribute("for", inputId);
                }
            });
        });
    }


    export function syncDrywallSheetFormat(form) {
        const formatSelect = form.querySelector("[data-drywall-sheet-format-select]");
        const lengthInput = form.querySelector("[data-drywall-sheet-length]");
        const widthInput = form.querySelector("[data-drywall-sheet-width]");
        if (!formatSelect || !lengthInput || !widthInput) {
            return;
        }

        const selected = formatSelect.options[formatSelect.selectedIndex];
        const isCustom = formatSelect.value === "custom";
        lengthInput.readOnly = !isCustom;
        widthInput.readOnly = !isCustom;

        if (isCustom || !selected) {
            return;
        }

        const length = selected.getAttribute("data-sheet-length");
        const width = selected.getAttribute("data-sheet-width");
        if (length) {
            lengthInput.value = length;
        }
        if (width) {
            widthInput.value = width;
        }
    }


    export function syncDrywallFormGroups(form) {
        const calculator = form.querySelector('[name="calculator"]')?.value;
        if (calculator !== "drywall") {
            return;
        }

        const mode = form.querySelector('[name="mode"]')?.value || "dimensions";
        const target = form.querySelector('[name="drywallTarget"]')?.value || "wall";
        const includeOpenings = form.querySelector('[name="includeOpenings"]')?.checked;
        const includeFinishing = form.querySelector('[name="drywallIncludeFinishing"]')?.checked;
        const includeCosts = form.querySelector('[name="drywallIncludeCosts"]')?.checked;

        toggleVisibility(
            form.querySelector('[data-field-group="drywall-wall-dimensions"]'),
            mode === "dimensions" && target !== "ceiling"
        );
        toggleVisibility(
            form.querySelector('[data-field-group="drywall-ceiling-dimensions"]'),
            mode === "dimensions" && target === "ceiling"
        );
        toggleVisibility(
            form.querySelector('[data-field-group="drywall-area"]'),
            mode === "area"
        );
        toggleVisibility(
            form.querySelector('[data-field-group="drywall-openings-toggle"]'),
            target !== "ceiling"
        );
        toggleVisibility(
            form.querySelector("[data-drywall-openings-root]"),
            target !== "ceiling" && !!includeOpenings
        );
        toggleVisibility(
            form.querySelector('[data-field-group="drywall-profile-width"]'),
            target === "partition"
        );
        toggleVisibility(
            form.querySelector('[data-field-group="drywall-end-cladding-toggle"]'),
            target === "partition" && !!includeOpenings
        );
        toggleVisibility(
            form.querySelector("[data-drywall-costs-root]"),
            !!includeCosts
        );
        toggleVisibility(
            form.querySelector("[data-drywall-finishing-costs-root]"),
            !!includeCosts && !!includeFinishing
        );

        form.querySelectorAll("[data-drywall-length-label]").forEach((node) => {
            node.textContent =
                target === "partition" ? "Длина перегородки (м)" : "Длина стены (м)";
        });
    }


    export function initDrywallForm(form) {
        const calculator = form.querySelector('[name="calculator"]')?.value;
        if (calculator !== "drywall") {
            return;
        }

        const modeSelect = form.querySelector('[name="mode"]');
        const targetSelect = form.querySelector('[name="drywallTarget"]');
        const openingsToggle = form.querySelector('[name="includeOpenings"]');
        const finishingToggle = form.querySelector('[name="drywallIncludeFinishing"]');
        const costsToggle = form.querySelector('[name="drywallIncludeCosts"]');
        const formatSelect = form.querySelector("[data-drywall-sheet-format-select]");

        const refresh = () => {
            form.querySelectorAll("[data-drywall-repeat-list]").forEach((listNode) => {
                reindexDrywallRepeatList(listNode);
            });
            syncDrywallSheetFormat(form);
            syncDrywallFormGroups(form);
        };

        [modeSelect, targetSelect, openingsToggle, finishingToggle, costsToggle, formatSelect].forEach(
            (node) => {
                node?.addEventListener("change", () => {
                    clearErrors(form);
                    markResultStale(form);
                    refresh();
                });
            }
        );

        form.querySelectorAll("[data-drywall-add-item]").forEach((button) => {
            button.addEventListener("click", () => {
                const listNode = form.querySelector('[data-drywall-repeat-list="drywallOpenings"]');
                if (!listNode) {
                    return;
                }
                const nextIndex = listNode.querySelectorAll("[data-drywall-repeat-item]").length;
                listNode.insertAdjacentHTML("beforeend", createDrywallRepeatMarkup(nextIndex));
                window.bmEnhanceEstimatorSelects?.(listNode);
                clearErrors(form);
                markResultStale(form);
                refresh();
            });
        });

        form.querySelectorAll("[data-drywall-repeat-list]").forEach((listNode) => {
            if (!listNode.querySelector("[data-drywall-repeat-item]")) {
                listNode.insertAdjacentHTML("beforeend", createDrywallRepeatMarkup(0));
                window.bmEnhanceEstimatorSelects?.(listNode);
            }

            listNode.addEventListener("click", (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }
                const removeButton = target.closest("[data-drywall-remove-item]");
                if (!removeButton) {
                    return;
                }
                const items = listNode.querySelectorAll("[data-drywall-repeat-item]");
                if (items.length <= 1) {
                    return;
                }
                const itemNode = removeButton.closest("[data-drywall-repeat-item]");
                if (!itemNode) {
                    return;
                }
                itemNode.remove();
                clearErrors(form);
                markResultStale(form);
                refresh();
            });
        });

        refresh();
    }

export function buildPayload(form, formData) {
    const calculator = readTrimmed(formData, "calculator") || "drywall";
    const mode = readTrimmed(formData, "mode");
    const payload = {
      calculator,
      mode,
    };

    let isValid = validateBaseFields(form, payload);


            payload.drywallTarget = readTrimmed(formData, "drywallTarget") || "wall";
            payload.drywallSheetLengthMm = readTrimmed(formData, "drywallSheetLengthMm");
            payload.drywallSheetWidthMm = readTrimmed(formData, "drywallSheetWidthMm");
            payload.drywallSheetThicknessMm = readTrimmed(formData, "drywallSheetThicknessMm");
            payload.drywallLayers = readTrimmed(formData, "drywallLayers");
            payload.drywallFrameStepMm = readTrimmed(formData, "drywallFrameStepMm");
            payload.drywallProfileWidthMm = readTrimmed(formData, "drywallProfileWidthMm");
            payload.reservePercent = readTrimmed(formData, "reservePercent");
            payload.drywallFastenerReservePercent = readTrimmed(
                formData,
                "drywallFastenerReservePercent"
            );
            payload.includeOpenings =
                payload.drywallTarget !== "ceiling" &&
                formData.get("includeOpenings") !== null;
            payload.drywallIncludeEndCladding =
                payload.drywallTarget === "partition" &&
                formData.get("drywallIncludeEndCladding") !== null;
            payload.drywallIncludeFinishing =
                formData.get("drywallIncludeFinishing") !== null;
            payload.drywallIncludeCosts = formData.get("drywallIncludeCosts") !== null;

            isValid =
                validateSelectedValue(
                    form,
                    "drywallTarget",
                    payload.drywallTarget,
                    ["wall", "ceiling", "partition"],
                    "Выберите тип конструкции."
                ) && isValid;
            isValid =
                validateSelectedValue(
                    form,
                    "drywallLayers",
                    payload.drywallLayers,
                    ["1", "2"],
                    "Выберите количество слоёв."
                ) && isValid;
            isValid =
                validateSelectedValue(
                    form,
                    "drywallFrameStepMm",
                    payload.drywallFrameStepMm,
                    ["400", "600"],
                    "Выберите шаг профиля."
                ) && isValid;
            if (payload.drywallTarget === "partition") {
                isValid =
                    validateSelectedValue(
                        form,
                        "drywallProfileWidthMm",
                        payload.drywallProfileWidthMm,
                        ["50", "75", "100"],
                        "Выберите ширину профиля перегородки."
                    ) && isValid;
            }

            if (mode === "dimensions") {
                if (payload.drywallTarget === "ceiling") {
                    payload.length = readTrimmed(formData, "drywallCeilingLength");
                    payload.width = readTrimmed(formData, "drywallCeilingWidth");
                    isValid =
                        validatePositiveField(
                            form,
                            payload,
                            "length",
                            "Длина помещения должна быть больше 0."
                        ) && isValid;
                    isValid =
                        validatePositiveField(
                            form,
                            payload,
                            "width",
                            "Ширина помещения должна быть больше 0."
                        ) && isValid;
                } else {
                    payload.length = readTrimmed(formData, "drywallLength");
                    payload.height = readTrimmed(formData, "drywallHeight");
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
                            "height",
                            "Высота должна быть больше 0."
                        ) && isValid;
                }
            } else if (mode === "area") {
                payload.area = readTrimmed(formData, "drywallArea");
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

            isValid =
                validatePositiveField(
                    form,
                    payload,
                    "drywallSheetLengthMm",
                    "Длина листа должна быть больше 0."
                ) && isValid;
            isValid =
                validatePositiveField(
                    form,
                    payload,
                    "drywallSheetWidthMm",
                    "Ширина листа должна быть больше 0."
                ) && isValid;
            isValid =
                validatePositiveField(
                    form,
                    payload,
                    "drywallSheetThicknessMm",
                    "Толщина листа должна быть больше 0."
                ) && isValid;
            isValid =
                validatePositiveField(
                    form,
                    payload,
                    "reservePercent",
                    "Запас на листы и профиль должен быть больше 0."
                ) && isValid;
            isValid =
                validatePositiveField(
                    form,
                    payload,
                    "drywallFastenerReservePercent",
                    "Запас на метизы должен быть больше 0."
                ) && isValid;

            if (payload.includeOpenings) {
                const openings = buildDrywallRepeatItems(form);
                isValid = validateDrywallOpenings(form, openings) && isValid;
                const split = splitDrywallOpenings(openings);
                payload.windows = split.windows;
                payload.doors = split.doors;
            }

            if (payload.drywallIncludeCosts) {
                payload.drywallSheetPrice = readTrimmed(formData, "drywallSheetPrice");
                payload.drywallProfilePricePerLm = readTrimmed(
                    formData,
                    "drywallProfilePricePerLm"
                );
                payload.drywallFastenerPricePer100 = readTrimmed(
                    formData,
                    "drywallFastenerPricePer100"
                );
                payload.drywallPrimerPricePerKg = readTrimmed(
                    formData,
                    "drywallPrimerPricePerKg"
                );
                payload.drywallJointPuttyPricePerKg = readTrimmed(
                    formData,
                    "drywallJointPuttyPricePerKg"
                );
                payload.drywallFinishPuttyPricePerKg = readTrimmed(
                    formData,
                    "drywallFinishPuttyPricePerKg"
                );
                payload.drywallTapePricePerLm = readTrimmed(formData, "drywallTapePricePerLm");

                [
                    "drywallSheetPrice",
                    "drywallProfilePricePerLm",
                    "drywallFastenerPricePer100",
                    "drywallPrimerPricePerKg",
                    "drywallJointPuttyPricePerKg",
                    "drywallFinishPuttyPricePerKg",
                    "drywallTapePricePerLm",
                ].forEach((field) => {
                    if (payload[field] && !isPositiveNumber(payload[field])) {
                        setFieldError(form, field, "Цена должна быть больше 0.");
                        isValid = false;
                    }
                });
            }

    return {
      isValid,
      payload,
    };
  }

const calculatorModule = {
  calculator: "drywall",
  init: initDrywallForm,
  buildPayload,
  showResult: showDrywallResult,
};

initEstimateForms(calculatorModule);
