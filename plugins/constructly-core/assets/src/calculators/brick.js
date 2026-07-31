import { escapeHtml, formatNumber, hasMeaningfulNumber, normalizePurchaseWeight } from "../core/formatters.js";
import { clearErrors, finalizeSuccessfulResult, getEstimatorShell, markResultStale, readTrimmed, setFieldError, toggleVisibility } from "../core/form-state.js";
import { isPositiveInteger, isPositiveNumber, validateBaseFields, validatePositiveField, validateSelectedValue } from "../core/validation.js";
import { syncMixtureUnitFields } from "../core/mixture.js";

import { initEstimateForms } from "../core/bootstrap.js";


    export function showBrickResult(form, payload) {
        const resultNode = getEstimatorShell(form)?.querySelector("[data-result]");
        if (!resultNode) {
            return;
        }

        const summary = payload?.summary || {};
        const brick = payload?.brick || {};
        const mortar = payload?.mortar || {};
        const mesh = payload?.mesh || {};
        const lintels = payload?.lintels || {};
        const costs = payload?.costs || {};

        // Helper: build one material row HTML
        const row = (label, value, note) => {
            const noteHtml = note ? `<span class="bm-calculator-result__material-note">${escapeHtml(String(note))}</span>` : '';
            return `<div class="bm-calculator-result__material"><span class="bm-calculator-result__material-head"><span>${escapeHtml(String(label))}</span><strong>${value}</strong></span>${noteHtml}</div>`;
        };

        // Helper: row with info tooltip icon next to label
        const infoRow = (label, tooltipText, value, note) => {
            const noteHtml = note ? `<span class="bm-calculator-result__material-note">${escapeHtml(String(note))}</span>` : '';
            const labelHtml = `${escapeHtml(String(label))} <button type="button" class="bm-tooltip-trigger" data-bm-tooltip="${escapeHtml(String(tooltipText))}" aria-label="Подсказка: ${escapeHtml(String(label))}" aria-expanded="false">i</button>`;
            return `<div class="bm-calculator-result__material"><span class="bm-calculator-result__material-head">${labelHtml}<strong>${value}</strong></span>${noteHtml}</div>`;
        };

        // Helper: regenerate full card structure (h3 + list) — robust against clearResult wiping innerHTML
        const fillCard = (card, title, rows, extraHtml) => {
            if (!card) { return; }
            card.innerHTML = `<h3 class="bm-calculator-result__section-title">${escapeHtml(String(title))}</h3><div class="bm-calculator-result__list">${rows.join('')}</div>${extraHtml || ''}`;
        };

        // Build purchase note for mortar components
        const buildPurchaseNote = (component) => {
            const unit = String(component?.purchaseUnit || '');
            const weight = formatNumber(component?.displayUnitWeight);
            if (unit === 'bag') {
                return `${formatNumber(component?.requiredUnits)} меш., к покупке ${formatNumber(component?.roundedUnits)} меш. по ${weight} кг`;
            }
            return `${formatNumber(component?.requiredUnits)} т, к покупке ${formatNumber(component?.roundedUnits)} т`;
        };

        // --- brick-summary (always visible) ---
        const summaryCard = resultNode.querySelector('[data-result-card="brick-summary"]');
        if (summaryCard) {
            const rows = [
                row('Количество без запаса', `${formatNumber(brick.countExact)} шт`),
                row('Количество с запасом', `${formatNumber(brick.countWithReserve)} шт`),
                row('К покупке рекомендовано', `${formatNumber(brick.countToBuy)} шт`),
            ];
            if (Number.isFinite(Number(brick.weightPerUnitKg))) {
                rows.push(row('Масса 1 кирпича', `${formatNumber(brick.weightPerUnitKg)} кг`));
            }
            rows.push(row('Общий вес', `${formatNumber(brick.massWithReserveKg)} кг`));
            fillCard(summaryCard, 'Кирпич', rows);
        }

        // --- brick-geometry (always visible) ---
        const geometryCard = resultNode.querySelector('[data-result-card="brick-geometry"]');
        if (geometryCard) {
            fillCard(geometryCard, 'Геометрия кладки', [
                row('Чистая площадь', `${formatNumber(summary.netAreaM2)} м²`),
                row('Объём стены', `${formatNumber(summary.wallVolumeM3)} м³`),
                row('Толщина', `${escapeHtml(summary.wallThicknessLabel || '-')} (${formatNumber(summary.wallThicknessMm)} мм)`),
                row('Рядов кладки', formatNumber(summary.rowsCount)),
                infoRow('Ориентировочная нагрузка', 'Суммарный вес: кирпич с запасом + раствор', `${formatNumber(summary.estimatedFoundationLoadTonnes)} т`),
            ]);
        }

        // --- brick-mortar (always visible) ---
        const mortarCard = resultNode.querySelector('[data-result-card="brick-mortar"]');
        if (mortarCard) {
            fillCard(mortarCard, 'Раствор', [
                row('Объём раствора', `${formatNumber(mortar.volumeM3)} м³`),
                row('Пропорция', escapeHtml(mortar?.ratio?.display || '-')),
                row('Цемент', `${formatNumber(mortar?.cement?.weightKg)} кг`, buildPurchaseNote(mortar?.cement)),
                row('Песок', `${formatNumber(mortar?.sand?.weightKg)} кг`, buildPurchaseNote(mortar?.sand)),
                row('Вода', `${formatNumber(mortar.waterLiters)} л`),
            ]);
        }

        // --- brick-mesh (conditional) ---
        const meshCard = resultNode.querySelector('[data-result-card="brick-mesh"]');
        if (meshCard) {
            const hasMesh = mesh && Number.isFinite(Number(mesh.meshLengthM)) && Number(mesh.meshLengthM) > 0;
            if (hasMesh) {
                meshCard.hidden = false;
                fillCard(meshCard, 'Кладочная сетка', [
                    row('Частота', `каждый ${formatNumber(mesh.frequencyRows)} ряд`),
                    row('Армированных рядов', formatNumber(mesh.reinforcedRows)),
                    row('Ориентир по длине', `${formatNumber(mesh.meshLengthM)} м`),
                    row('Площадь сетки', `${formatNumber(mesh.meshAreaM2)} м²`),
                ]);
            } else {
                meshCard.hidden = true;
            }
        }

        // --- brick-lintels (conditional) ---
        const lintelsCard = resultNode.querySelector('[data-result-card="brick-lintels"]');
        if (lintelsCard) {
            const lintelItems = Array.isArray(lintels?.items) ? lintels.items : [];
            if (lintelItems.length > 0) {
                lintelsCard.hidden = false;
                const supportM = lintels.recommendedSupportLengthM ?? 0.25;
                const rows = lintelItems.map((item) => {
                    const widthNote = Number.isFinite(Number(item.widthM))
                        ? `Проём ${formatNumber(item.widthM)} м + ${formatNumber(supportM * 2)} м опирание`
                        : `Мин. длина: ${formatNumber(item.recommendedLengthM)} м`;
                    return row(
                        `${escapeHtml(item.label || 'Проём')} × ${formatNumber(item.count)} шт`,
                        `≥ ${formatNumber(item.recommendedLengthM)} м`,
                        widthNote
                    );
                });
                const noteText = 'Купить по 1 перемычке на каждый проём. Длина = ширина проёма + 0.25 м опирание с каждой стороны. Тип и схему армирования — по проекту.';
                fillCard(lintelsCard, `Перемычки (${formatNumber(lintels.totalCount)} шт)`, rows, `<p class="bm-calculator-result__material-note">${escapeHtml(noteText)}</p>`);
            } else {
                lintelsCard.hidden = true;
            }
        }

        // --- brick-costs (conditional) ---
        const costsCard = resultNode.querySelector('[data-result-card="brick-costs"]');
        if (costsCard) {
            const hasCosts = [costs?.brickExact, costs?.cementExact, costs?.sandExact, costs?.totalExact]
                .some((v) => hasMeaningfulNumber(v));
            if (hasCosts) {
                costsCard.hidden = false;
                const rows = [];
                if (hasMeaningfulNumber(costs.brickExact)) {
                    rows.push(row('Кирпич', `${formatNumber(costs.brickExact)} / ${formatNumber(costs.brickRounded)}`));
                }
                if (hasMeaningfulNumber(costs.cementExact)) {
                    rows.push(row('Цемент', `${formatNumber(costs.cementExact)} / ${formatNumber(costs.cementRounded)}`));
                }
                if (hasMeaningfulNumber(costs.sandExact)) {
                    rows.push(row('Песок', `${formatNumber(costs.sandExact)} / ${formatNumber(costs.sandRounded)}`));
                }
                rows.push(row('Итого', `${formatNumber(costs.totalExact)} / ${formatNumber(costs.totalRounded)}`));
                fillCard(costsCard, 'Стоимость', rows, '<p class="bm-calculator-result__material-note">Сначала показана точная оценка, затем ориентир по закупке.</p>');
            } else {
                costsCard.hidden = true;
            }
        }

        resultNode.hidden = false;
        resultNode.classList.add("is-success");
        finalizeSuccessfulResult(form);
    }


    export function buildBrickRepeatItems(form, groupName) {
        const list = form.querySelector(`[data-brick-repeat-list="${groupName}"]`);
        if (!list) {
            return [];
        }

        return Array.from(list.querySelectorAll("[data-brick-repeat-item]")).map((itemNode) => ({
            widthM: String(itemNode.querySelector('[data-brick-repeat-input="widthM"]')?.value || "").trim(),
            heightM: String(itemNode.querySelector('[data-brick-repeat-input="heightM"]')?.value || "").trim(),
            count: String(itemNode.querySelector('[data-brick-repeat-input="count"]')?.value || "").trim(),
        }));
    }


    export function validateBrickRepeatItems(form, groupName, items, title) {
        let isValid = true;

        items.forEach((item, index) => {
            if (!isPositiveNumber(item.widthM)) {
                setFieldError(form, `${groupName}.${index}.widthM`, `${title}: ширина должна быть больше 0.`);
                isValid = false;
            }
            if (!isPositiveNumber(item.heightM)) {
                setFieldError(form, `${groupName}.${index}.heightM`, `${title}: высота должна быть больше 0.`);
                isValid = false;
            }
            if (!isPositiveInteger(item.count)) {
                setFieldError(form, `${groupName}.${index}.count`, `${title}: количество должно быть целым числом больше 0.`);
                isValid = false;
            }
        });

        return isValid;
    }


    export function createBrickRepeatMarkup(groupName, type, index) {
        const titleMap = {
            windows: "Окно",
            doors: "Дверь",
            gables: "Фронтон",
        };
        const defaults = {
            window: { width: "1.2", height: "1.4", count: "1" },
            door: { width: "0.9", height: "2.1", count: "1" },
            gable: { width: "8", height: "2", count: "1" },
        };
        const config = defaults[type] || defaults.window;
        const title = titleMap[groupName] || "Элемент";

        return `
      <article class="brigmaster-estimator__segment-card" data-brick-repeat-item data-brick-item-type="${escapeHtml(type)}" data-brick-group="${escapeHtml(groupName)}">
        <div class="brigmaster-estimator__segment-head">
          <h4 class="brigmaster-estimator__segment-title">${escapeHtml(title)} ${index + 1}</h4>
          <button type="button" class="brigmaster-estimator__segment-remove" data-brick-remove-item>Удалить</button>
        </div>
        <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three">
          <div class="brigmaster-estimator__field">
            <label for="">Ширина (м)</label>
            <input type="number" min="0.01" step="0.01" value="${config.width}" data-brick-repeat-input="widthM">
            <div class="brigmaster-estimator__error" data-brick-error-field="widthM" aria-live="polite"></div>
          </div>
          <div class="brigmaster-estimator__field">
            <label for="">Высота (м)</label>
            <input type="number" min="0.01" step="0.01" value="${config.height}" data-brick-repeat-input="heightM">
            <div class="brigmaster-estimator__error" data-brick-error-field="heightM" aria-live="polite"></div>
          </div>
          <div class="brigmaster-estimator__field">
            <label for="">Количество</label>
            <input type="number" min="1" step="1" value="${config.count}" data-brick-repeat-input="count">
            <div class="brigmaster-estimator__error" data-brick-error-field="count" aria-live="polite"></div>
          </div>
        </div>
      </article>
    `;
    }


    export function reindexBrickRepeatList(listNode) {
        if (!listNode) {
            return;
        }

        const groupName = listNode.getAttribute("data-brick-repeat-list") || "items";
        const titleMap = {
            windows: "Окно",
            doors: "Дверь",
            gables: "Фронтон",
        };
        const title = titleMap[groupName] || "Элемент";
        const items = listNode.querySelectorAll("[data-brick-repeat-item]");
        items.forEach((itemNode, index) => {
            const titleNode = itemNode.querySelector(".brigmaster-estimator__segment-title");
            if (titleNode) {
                titleNode.textContent = `${title} ${index + 1}`;
            }
            const removeButton = itemNode.querySelector("[data-brick-remove-item]");
            if (removeButton) {
                removeButton.disabled = items.length === 1;
            }
            itemNode.querySelectorAll("[data-brick-error-field]").forEach((errorNode) => {
                const field = errorNode.getAttribute("data-brick-error-field");
                if (field) {
                    errorNode.setAttribute("data-field-error", `${groupName}.${index}.${field}`);
                }
            });
            ["widthM", "heightM", "count"].forEach((fieldKey) => {
                const input = itemNode.querySelector(`[data-brick-repeat-input="${fieldKey}"]`);
                const label = input?.closest(".brigmaster-estimator__field")?.querySelector("label");
                const normalizedFieldKey = fieldKey.replace(/M$/, "").replace(/[A-Z]/g, (char) => `-${char.toLowerCase()}`);
                const inputId = `${groupName}-${index}-${normalizedFieldKey}`;
                if (input) {
                    input.id = inputId;
                    input.name = `${groupName}[${index}][${fieldKey}]`;
                }
                if (label) {
                    label.setAttribute("for", inputId);
                }
            });
        });
    }


    export function syncBrickFormGroups(form) {
        const calculator = form.querySelector('[name="calculator"]')?.value;
        if (calculator !== "brick") {
            return;
        }

        const mode = form.querySelector('[name="mode"]')?.value || "dimensions";
        const includeOpenings = form.querySelector('[name="includeOpenings"]')?.checked === true;
        const includeGables = form.querySelector('[name="includeGables"]')?.checked === true;
        const includeMesh = form.querySelector('[name="includeMasonryMesh"]')?.checked === true;
        const dimensionsGroup = form.querySelector('[data-field-group="brick-geometry-dimensions"]');
        const areaGroup = form.querySelector('[data-field-group="brick-geometry-area"]');
        const openingsRoot = form.querySelector('[data-toggle-target="brick-openings"]');
        const gablesRoot = form.querySelector('[data-toggle-target="brick-gables"]');
        const meshFrequencyField = form.querySelector('[data-field-group="brick-mesh"]');

        toggleVisibility(dimensionsGroup, mode === "dimensions");
        toggleVisibility(areaGroup, mode === "area");
        toggleVisibility(openingsRoot, includeOpenings);
        toggleVisibility(gablesRoot, includeGables);
        toggleVisibility(meshFrequencyField, includeMesh);
    }


    export function syncBrickFormatFields(form) {
        const calculator = form.querySelector('[name="calculator"]')?.value;
        if (calculator !== "brick") {
            return;
        }

        const select = form.querySelector("[data-brick-format-select]");
        const lengthInput = form.querySelector('[data-brick-size-input="length"]');
        const widthInput = form.querySelector('[data-brick-size-input="width"]');
        const heightInput = form.querySelector('[data-brick-size-input="height"]');
        if (!(select instanceof HTMLSelectElement) || !lengthInput || !widthInput || !heightInput) {
            return;
        }

        const option = select.selectedOptions[0];
        const isCustom = select.value === "custom";
        [lengthInput, widthInput, heightInput].forEach((input) => {
            input.readOnly = !isCustom;
        });

        if (isCustom || !option) {
            return;
        }

        const length = option.getAttribute("data-brick-length");
        const width = option.getAttribute("data-brick-width");
        const height = option.getAttribute("data-brick-height");
        if (length) {
            lengthInput.value = length;
        }
        if (width) {
            widthInput.value = width;
        }
        if (height) {
            heightInput.value = height;
        }
    }


    export function initBrickForm(form) {
        const calculator = form.querySelector('[name="calculator"]')?.value;
        if (calculator !== "brick") {
            return;
        }

        const modeSelect = form.querySelector('[name="mode"]');
        const openingsToggle = form.querySelector('[name="includeOpenings"]');
        const gablesToggle = form.querySelector('[name="includeGables"]');
        const meshToggle = form.querySelector('[name="includeMasonryMesh"]');
        const formatSelect = form.querySelector("[data-brick-format-select]");
        ["cement", "sand"].forEach((materialKey) => {
            syncMixtureUnitFields(form, materialKey);
            const unitSelect = form.querySelector(`[name="${materialKey}PurchaseUnit"]`);
            if (unitSelect) {
                unitSelect.addEventListener("change", () => {
                    clearErrors(form);
                    markResultStale(form);
                    syncMixtureUnitFields(form, materialKey);
                });
            }
        });

        const refresh = () => {
            form.querySelectorAll("[data-brick-repeat-list]").forEach((listNode) => {
                reindexBrickRepeatList(listNode);
            });
            syncBrickFormatFields(form);
            syncBrickFormGroups(form);
        };

        [modeSelect, openingsToggle, gablesToggle, meshToggle, formatSelect].forEach((node) => {
            if (!node) {
                return;
            }
            node.addEventListener("change", () => {
                clearErrors(form);
                markResultStale(form);
                refresh();
            });
        });

        form.querySelectorAll("[data-brick-add-item]").forEach((button) => {
            button.addEventListener("click", () => {
                const groupName = button.getAttribute("data-brick-add-item");
                const listNode = groupName
                    ? form.querySelector(`[data-brick-repeat-list="${groupName}"]`)
                    : null;
                const type = listNode?.getAttribute("data-brick-item-type") || "window";
                if (!listNode) {
                    return;
                }
                const nextIndex = listNode.querySelectorAll("[data-brick-repeat-item]").length;
                listNode.insertAdjacentHTML("beforeend", createBrickRepeatMarkup(groupName, type, nextIndex));
                clearErrors(form);
                markResultStale(form);
                refresh();
            });
        });

        form.querySelectorAll("[data-brick-repeat-list]").forEach((listNode) => {
            listNode.addEventListener("click", (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }
                const removeButton = target.closest("[data-brick-remove-item]");
                if (!removeButton) {
                    return;
                }
                const itemNodes = listNode.querySelectorAll("[data-brick-repeat-item]");
                if (itemNodes.length <= 1) {
                    return;
                }
                const itemNode = removeButton.closest("[data-brick-repeat-item]");
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
    const calculator = readTrimmed(formData, "calculator") || "brick";
    const mode = readTrimmed(formData, "mode");
    const payload = {
      calculator,
      mode,
    };

    let isValid = validateBaseFields(form, payload);


            payload.brickFormat = readTrimmed(formData, "brickFormat") || "single_nf";
            payload.brickLengthMm = readTrimmed(formData, "brickLengthMm");
            payload.brickWidthMm = readTrimmed(formData, "brickWidthMm");
            payload.brickHeightMm = readTrimmed(formData, "brickHeightMm");
            payload.jointThicknessMm = readTrimmed(formData, "jointThicknessMm");
            payload.wallThicknessType = readTrimmed(formData, "wallThicknessType") || "one_and_half_bricks";
            payload.wallHeightM = readTrimmed(formData, "wallHeightM");
            payload.reservePercent = readTrimmed(formData, "reservePercent");
            const brickWeightKg = readTrimmed(formData, "brickWeightKg");
            const brickPricePerUnit = readTrimmed(formData, "brickPricePerUnit");
            payload.includeOpenings = formData.get("includeOpenings") !== null;
            payload.includeGables = formData.get("includeGables") !== null;
            payload.includeMasonryMesh = formData.get("includeMasonryMesh") !== null;
            payload.cementShare = readTrimmed(formData, "cementShare");
            payload.sandShare = readTrimmed(formData, "sandShare");
            payload.cementPurchaseUnit = readTrimmed(formData, "cementPurchaseUnit");
            payload.cementUnitWeightKg = normalizePurchaseWeight(
                readTrimmed(formData, "cementUnitWeightKg"),
                payload.cementPurchaseUnit
            );
            payload.cementUnitPrice = readTrimmed(formData, "cementUnitPrice");
            payload.sandPurchaseUnit = readTrimmed(formData, "sandPurchaseUnit");
            payload.sandUnitWeightKg = normalizePurchaseWeight(
                readTrimmed(formData, "sandUnitWeightKg"),
                payload.sandPurchaseUnit
            );
            payload.sandUnitPrice = readTrimmed(formData, "sandUnitPrice");

            if (brickWeightKg) {
                payload.brickWeightKg = brickWeightKg;
            }
            if (brickPricePerUnit) {
                payload.brickPricePerUnit = brickPricePerUnit;
            }
            isValid = validatePositiveField(form, payload, "brickLengthMm", "Длина кирпича должна быть больше 0.") && isValid;
            isValid = validatePositiveField(form, payload, "brickWidthMm", "Ширина кирпича должна быть больше 0.") && isValid;
            isValid = validatePositiveField(form, payload, "brickHeightMm", "Высота кирпича должна быть больше 0.") && isValid;
            isValid = validatePositiveField(form, payload, "jointThicknessMm", "Толщина шва должна быть больше 0.") && isValid;
            isValid = validatePositiveField(form, payload, "wallHeightM", "Высота стен должна быть больше 0.") && isValid;
            isValid = validatePositiveField(form, payload, "reservePercent", "Запас должен быть больше 0.") && isValid;

            if (mode === "dimensions") {
                payload.wallLengthM = readTrimmed(formData, "wallLengthM");
                isValid = validatePositiveField(form, payload, "wallLengthM", "Общая длина стен должна быть больше 0.") && isValid;
            } else if (mode === "area") {
                payload.area = readTrimmed(formData, "area");
                isValid = validatePositiveField(form, payload, "area", "Площадь стен должна быть больше 0.") && isValid;
            } else {
                setFieldError(form, "mode", "Выберите режим dimensions или area.");
                isValid = false;
            }

            if (payload.brickWeightKg && !isPositiveNumber(payload.brickWeightKg)) {
                setFieldError(form, "brickWeightKg", "Масса кирпича должна быть больше 0.");
                isValid = false;
            }
            if (payload.brickPricePerUnit && !isPositiveNumber(payload.brickPricePerUnit)) {
                setFieldError(form, "brickPricePerUnit", "Цена кирпича должна быть больше 0.");
                isValid = false;
            }
            if (payload.includeOpenings) {
                payload.windows = buildBrickRepeatItems(form, "windows");
                payload.doors = buildBrickRepeatItems(form, "doors");
                isValid = validateBrickRepeatItems(form, "windows", payload.windows, "Окна") && isValid;
                isValid = validateBrickRepeatItems(form, "doors", payload.doors, "Двери") && isValid;
            }

            if (payload.includeGables) {
                payload.gables = buildBrickRepeatItems(form, "gables");
                isValid = validateBrickRepeatItems(form, "gables", payload.gables, "Фронтоны") && isValid;
            }

            payload.masonryMeshFrequencyRows = readTrimmed(formData, "masonryMeshFrequencyRows");
            if (payload.includeMasonryMesh && !isPositiveInteger(payload.masonryMeshFrequencyRows)) {
                setFieldError(form, "masonryMeshFrequencyRows", "Шаг сетки должен быть целым числом больше 0.");
                isValid = false;
            }

            isValid = validatePositiveField(form, payload, "cementShare", "Доля цемента должна быть больше 0.") && isValid;
            isValid = validateSelectedValue(form, "cementPurchaseUnit", payload.cementPurchaseUnit, ["bag", "tonne"], "Выберите единицу покупки цемента.") && isValid;
            isValid = validatePositiveField(form, payload, "cementUnitWeightKg", "Вес единицы цемента должен быть больше 0.") && isValid;
            if (payload.cementUnitPrice && !isPositiveNumber(payload.cementUnitPrice)) {
                setFieldError(form, "cementUnitPrice", "Цена цемента должна быть больше 0.");
                isValid = false;
            }
            isValid = validatePositiveField(form, payload, "sandShare", "Доля песка должна быть больше 0.") && isValid;
            isValid = validateSelectedValue(form, "sandPurchaseUnit", payload.sandPurchaseUnit, ["bag", "tonne"], "Выберите единицу покупки песка.") && isValid;
            isValid = validatePositiveField(form, payload, "sandUnitWeightKg", "Вес единицы песка должен быть больше 0.") && isValid;
            if (payload.sandUnitPrice && !isPositiveNumber(payload.sandUnitPrice)) {
                setFieldError(form, "sandUnitPrice", "Цена песка должна быть больше 0.");
                isValid = false;
            }

    return {
      isValid,
      payload,
    };
  }

const calculatorModule = {
  calculator: "brick",
  init: initBrickForm,
  buildPayload,
  showResult: showBrickResult,
};

initEstimateForms(calculatorModule);
