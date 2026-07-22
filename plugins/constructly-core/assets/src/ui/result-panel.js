import { escapeHtml, formatNumber, hasMeaningfulNumber } from "../core/formatters.js";
import { getEstimatorShell } from "../core/form-state.js";


    export function buildMixtureResultHtml(mixture, title, options = {}) {
        if (!mixture || typeof mixture !== "object") {
            return "";
        }

        const { omitVolume = false } = options;

        const type = String(mixture.type || "").trim();
        const summaryItems = [];
        const noteLines = [];

        const buildPurchaseText = (component) => {
            const unit = String(component.purchaseUnit || "");
            const displayUnitWeight = formatNumber(component.displayUnitWeight);
            if (unit === "bag") {
                return `${formatNumber(component.requiredUnits)} шт мешков, к покупке рекомендовано ${formatNumber(
                    component.roundedUnits
                )} шт мешков по ${displayUnitWeight} кг`;
            }

            return `${formatNumber(component.requiredUnits)} т, к покупке рекомендовано ${formatNumber(
                component.roundedUnits
            )} т`;
        };

        const item = (label, value, tooltip, modifier) => {
            const labelHtml = tooltip
                ? `${escapeHtml(label)} <button class="bm-tooltip" type="button" data-tooltip="${escapeHtml(tooltip)}" aria-label="Подсказка: ${escapeHtml(label)}"><svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-info-circle"></use></svg></button>`
                : escapeHtml(label);
            const className = modifier
                ? `bm-calculator-result__material ${modifier}`
                : "bm-calculator-result__material";
            return `<div class="${className}"><span class="bm-calculator-result__material-head"><span>${labelHtml}</span><strong>${value}</strong></span></div>`;
        };
        const hasPositiveCost = (v) => hasMeaningfulNumber(v) && Number(v) > 0;
        const COST_HINT = "Слева — точный расчёт, справа — с округлением до целых единиц закупки.";
        // Renders an "exact / rounded" cost pair. The exact part is wrapped so it can be
        // hidden in print, where only the practical "to purchase" (rounded) figure matters.
        const costPair = (exact, rounded) =>
            `<span class="bm-calculator-result__cost-exact">${formatNumber(exact)} / </span>${formatNumber(rounded)}`;

        if (type === "ready") {
            summaryItems.push(item("Тип", escapeHtml(mixture.displayType || "Готовая"), null, "bm-calculator-result__material--mixture-type"));
            if (!omitVolume) {
                summaryItems.push(item("Объём", `${formatNumber(mixture.volumeM3)} м³`));
            }
            if (hasPositiveCost(mixture.pricePerM3)) {
                summaryItems.push(item("Цена за м³", formatNumber(mixture.pricePerM3)));
            }
            if (hasPositiveCost(mixture.totalCost)) {
                summaryItems.push(item("Итоговая стоимость", formatNumber(mixture.totalCost)));
            }
        } else if (type === "dry_ready") {
            summaryItems.push(
                item("Тип", escapeHtml(mixture.displayType || "Готовая, сухая"), null, "bm-calculator-result__material--mixture-type"),
                item("Ориентир расхода", `${formatNumber(mixture.consumptionKgPerM2Per10mm)} кг/м² при 10 мм`),
                item("Общий вес смеси", `${formatNumber(mixture.totalWeightKg)} кг`),
                item("Мешки", `${formatNumber(mixture.requiredBags)} шт, к покупке ${formatNumber(mixture.roundedBags)} шт по ${formatNumber(mixture.bagWeightKg)} кг`)
            );
            if (hasPositiveCost(mixture.totalCostRounded)) {
                summaryItems.push(item("Стоимость", costPair(mixture.totalCostExact, mixture.totalCostRounded), COST_HINT));
            }
        } else if (type === "self_mix") {
            summaryItems.push(item("Тип", escapeHtml(mixture.displayType || "Самомесная"), null, "bm-calculator-result__material--mixture-type"));

            const components = mixture.components || {};
            Object.values(components).forEach((component) => {
                if (!component || typeof component !== "object") {
                    return;
                }
                const costSpan = hasPositiveCost(component.totalCostRounded)
                    ? `<span class="bm-calculator-result__material-cost"><span>Стоимость</span><span>${costPair(component.totalCostExact, component.totalCostRounded)}</span></span>`
                    : "";
                summaryItems.push(
                    `<div class="bm-calculator-result__material bm-calculator-result__material--component">
            <span class="bm-calculator-result__material-head"><span>${escapeHtml(component.label || "Материал")}</span><strong>${formatNumber(component.weightKg)} кг</strong></span>
            <span class="bm-calculator-result__material-note">${buildPurchaseText(component)}</span>
            ${costSpan}
          </div>`
                );
            });

            if (!omitVolume) {
                summaryItems.push(item("Объём готовой смеси", `${formatNumber(mixture.volumeM3)} м³`));
            }
            summaryItems.push(item("Вода", `${formatNumber(mixture.waterLiters)} л`));
            if (hasPositiveCost(mixture.totalCostRounded)) {
                summaryItems.push(item("Итоговая стоимость", costPair(mixture.totalCostExact, mixture.totalCostRounded), COST_HINT));
            }
        }

        if (mixture.note) {
            noteLines.push(
                `<p class="bm-calculator-result__material-note bm-calculator-result__mixture-note">${escapeHtml(
                    String(mixture.note)
                )}</p>`
            );
        }

        return `
      ${title ? `<h3 class="bm-calculator-result__section-title">${escapeHtml(title)}</h3>` : ""}
      <div class="bm-calculator-result__list">${summaryItems.join("")}</div>
      ${noteLines.join("")}
    `;
    }


    export function renderMixtureCard(cardNode, mixture, title, options = {}) {
        if (!cardNode) {
            return;
        }

        const html = buildMixtureResultHtml(mixture, title, options);
        if (!html) {
            cardNode.hidden = true;
            cardNode.innerHTML = "";
            return;
        }

        cardNode.hidden = false;
        cardNode.innerHTML = html;
    }


    export function buildStripReinforcementHtml(reinforcement, title = "Арматура") {
        if (!reinforcement) {
            return "";
        }
        const byDiameter = Array.isArray(reinforcement?.byDiameter)
            ? reinforcement.byDiameter
            : null;
        const totalMassKg = reinforcement?.totalMassKg;

        const buildDiameterLabel = (diameter) =>
            Number.isFinite(Number(diameter)) ? `Ø${formatNumber(diameter)} мм` : "Ø-";

        const rows = [];
        let computedTotalMass = 0;
        let computedTotalLength = 0;

        const pushRow = (diameter, massKg, lengthM) => {
            if (!Number.isFinite(Number(massKg))) {
                return;
            }
            computedTotalMass += Number(massKg);
            let note = "";
            if (Number.isFinite(Number(lengthM))) {
                const lengthCeilM = Math.ceil(Number(lengthM));
                computedTotalLength += lengthCeilM;
                note = `<span class="bm-calculator-result__material-note">Длина с запасом: ${lengthCeilM} м</span>`;
            }
            rows.push(
                `<div class="bm-calculator-result__material"><span class="bm-calculator-result__material-head"><span>${buildDiameterLabel(diameter)}</span><strong>${formatNumber(massKg)} кг</strong></span>${note}</div>`
            );
        };

        if (byDiameter && byDiameter.length > 0) {
            byDiameter.forEach((item) => {
                pushRow(item?.diameterMm, item?.massKg, item?.totalLengthWithReserveM);
            });
        } else {
            // Fallback for legacy payloads without byDiameter
            const longitudinal = reinforcement?.longitudinal;
            const transverse = reinforcement?.transverse;
            if (longitudinal) {
                pushRow(longitudinal.diameterMm, longitudinal.massKg, longitudinal.totalLengthWithReserveM);
            }
            if (
                transverse &&
                String(transverse.globalDiameterMm) !== String(longitudinal?.diameterMm)
            ) {
                pushRow(transverse.globalDiameterMm, transverse.massKg, transverse.totalLengthWithReserveM);
            }
        }

        if (rows.length === 0) {
            return "";
        }

        const totalMassValue = Number.isFinite(Number(totalMassKg)) ? Number(totalMassKg) : computedTotalMass;
        rows.push(
            `<div class="bm-calculator-result__material"><span class="bm-calculator-result__material-head"><span>Всего</span><strong>${formatNumber(totalMassValue)} кг</strong></span><span class="bm-calculator-result__material-note">Общая длина с запасом: ${computedTotalLength} м</span></div>`
        );

        return `
      <h3 class="bm-calculator-result__section-title">${escapeHtml(title)}</h3>
      <div class="bm-calculator-result__list">${rows.join("")}</div>
    `;
    }


    export function renderStripReinforcementCard(reinforcementCard, reinforcement, title = "Арматура") {
        if (!reinforcementCard) {
            return;
        }
        const html = buildStripReinforcementHtml(reinforcement, title);
        if (!html) {
            reinforcementCard.hidden = true;
            reinforcementCard.innerHTML = "";
            return;
        }
        reinforcementCard.hidden = false;
        reinforcementCard.innerHTML = html;
    }
