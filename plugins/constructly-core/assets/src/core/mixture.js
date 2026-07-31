import { normalizePurchaseWeight } from "./formatters.js";
import { buildPrefixedFieldName, clearErrors, getMixtureErrorPrefix, markResultStale, readTrimmed, toggleVisibility } from "./form-state.js";
import { validatePositiveValue, validateSelectedValue } from "./validation.js";


    export function buildMixturePayload(form, formData, prefix = "", options = {}) {
        const allowDryReady = options.allowDryReady === true;
        const includeGravel = options.includeGravel !== false;
        const errorPrefix = getMixtureErrorPrefix(prefix);
        const nameOf = (suffix) => buildPrefixedFieldName(prefix, suffix);
        const mixtureType = readTrimmed(formData, nameOf("mixtureType"));
        const allowedTypes = allowDryReady
            ? ["ready", "dry_ready", "self_mix"]
            : ["ready", "self_mix"];

        let isValid = validateSelectedValue(
            form,
            `${errorPrefix}.type`,
            mixtureType,
            allowedTypes,
            "Выберите корректный тип смеси."
        );

        const mixture = {
            type: mixtureType,
        };

        if (mixtureType === "ready") {
            mixture.readyConcretePricePerM3 = readTrimmed(
                formData,
                nameOf("readyConcretePricePerM3")
            );
            if (mixture.readyConcretePricePerM3 !== "") {
                isValid =
                    validatePositiveValue(
                        form,
                        `${errorPrefix}.readyConcretePricePerM3`,
                        mixture.readyConcretePricePerM3,
                        "Цена раствора за м³ должна быть больше 0."
                    ) && isValid;
            }
            return { mixture, isValid };
        }

        if (mixtureType === "dry_ready") {
            mixture.dryMixBagWeightKg = readTrimmed(formData, nameOf("dryMixBagWeightKg"));
            mixture.dryMixBagPrice = readTrimmed(formData, nameOf("dryMixBagPrice"));
            isValid =
                validatePositiveValue(
                    form,
                    `${errorPrefix}.dryMixBagWeightKg`,
                    mixture.dryMixBagWeightKg,
                    "Вес мешка должен быть больше 0."
                ) && isValid;
            if (mixture.dryMixBagPrice !== "") {
                isValid =
                    validatePositiveValue(
                        form,
                        `${errorPrefix}.dryMixBagPrice`,
                        mixture.dryMixBagPrice,
                        "Цена мешка должна быть больше 0."
                    ) && isValid;
            }
            return { mixture, isValid };
        }

        mixture.cementShare = readTrimmed(formData, nameOf("cementShare"));
        mixture.cementPurchaseUnit = readTrimmed(formData, nameOf("cementPurchaseUnit"));
        mixture.cementUnitWeightKg = normalizePurchaseWeight(
            readTrimmed(formData, nameOf("cementUnitWeightKg")),
            mixture.cementPurchaseUnit
        );
        mixture.cementUnitPrice = readTrimmed(formData, nameOf("cementUnitPrice"));
        mixture.sandShare = readTrimmed(formData, nameOf("sandShare"));
        mixture.sandPurchaseUnit = readTrimmed(formData, nameOf("sandPurchaseUnit"));
        mixture.sandUnitWeightKg = normalizePurchaseWeight(
            readTrimmed(formData, nameOf("sandUnitWeightKg")),
            mixture.sandPurchaseUnit
        );
        mixture.sandUnitPrice = readTrimmed(formData, nameOf("sandUnitPrice"));

        isValid =
            validatePositiveValue(
                form,
                `${errorPrefix}.cementShare`,
                mixture.cementShare,
                "Доля цемента должна быть больше 0."
            ) && isValid;
        isValid =
            validateSelectedValue(
                form,
                `${errorPrefix}.cementPurchaseUnit`,
                mixture.cementPurchaseUnit,
                ["bag", "tonne"],
                "Выберите единицу покупки цемента."
            ) && isValid;
        isValid =
            validatePositiveValue(
                form,
                `${errorPrefix}.cementUnitWeightKg`,
                mixture.cementUnitWeightKg,
                "Вес единицы цемента должен быть больше 0."
            ) && isValid;
        if (mixture.cementUnitPrice !== "") {
            isValid =
                validatePositiveValue(
                    form,
                    `${errorPrefix}.cementUnitPrice`,
                    mixture.cementUnitPrice,
                    "Цена цемента должна быть больше 0."
                ) && isValid;
        }
        isValid =
            validatePositiveValue(
                form,
                `${errorPrefix}.sandShare`,
                mixture.sandShare,
                "Доля песка должна быть больше 0."
            ) && isValid;
        isValid =
            validateSelectedValue(
                form,
                `${errorPrefix}.sandPurchaseUnit`,
                mixture.sandPurchaseUnit,
                ["bag", "tonne"],
                "Выберите единицу покупки песка."
            ) && isValid;
        isValid =
            validatePositiveValue(
                form,
                `${errorPrefix}.sandUnitWeightKg`,
                mixture.sandUnitWeightKg,
                "Вес единицы песка должен быть больше 0."
            ) && isValid;
        if (mixture.sandUnitPrice !== "") {
            isValid =
                validatePositiveValue(
                    form,
                    `${errorPrefix}.sandUnitPrice`,
                    mixture.sandUnitPrice,
                    "Цена песка должна быть больше 0."
                ) && isValid;
        }

        if (includeGravel) {
            mixture.gravelShare = readTrimmed(formData, nameOf("gravelShare"));
            mixture.gravelPurchaseUnit = readTrimmed(
                formData,
                nameOf("gravelPurchaseUnit")
            );
            mixture.gravelUnitWeightKg = normalizePurchaseWeight(
                readTrimmed(formData, nameOf("gravelUnitWeightKg")),
                mixture.gravelPurchaseUnit
            );
            mixture.gravelUnitPrice = readTrimmed(formData, nameOf("gravelUnitPrice"));

            isValid =
                validatePositiveValue(
                    form,
                    `${errorPrefix}.gravelShare`,
                    mixture.gravelShare,
                    "Доля щебня должна быть больше 0."
                ) && isValid;
            isValid =
                validateSelectedValue(
                    form,
                    `${errorPrefix}.gravelPurchaseUnit`,
                    mixture.gravelPurchaseUnit,
                    ["bag", "tonne"],
                    "Выберите единицу покупки щебня."
                ) && isValid;
            isValid =
                validatePositiveValue(
                    form,
                    `${errorPrefix}.gravelUnitWeightKg`,
                    mixture.gravelUnitWeightKg,
                    "Вес единицы щебня должен быть больше 0."
                ) && isValid;
            if (mixture.gravelUnitPrice !== "") {
                isValid =
                    validatePositiveValue(
                        form,
                        `${errorPrefix}.gravelUnitPrice`,
                        mixture.gravelUnitPrice,
                        "Цена щебня должна быть больше 0."
                    ) && isValid;
            }
        }

        return { mixture, isValid };
    }


    export function syncMixtureBlock(block) {
        if (!(block instanceof Element)) {
            return;
        }

        const select = block.querySelector("[data-mixture-type-select]");
        const type = select instanceof HTMLSelectElement ? select.value : "ready";
        const panels = block.querySelectorAll("[data-mixture-panel]");
        panels.forEach((panel) => {
            const panelType = panel.getAttribute("data-mixture-panel");
            const isVisible = panelType === type;
            toggleVisibility(panel, isVisible);
            panel
                .querySelectorAll("input, select")
                .forEach((control) => {
                    if (
                        control instanceof HTMLInputElement ||
                        control instanceof HTMLSelectElement
                    ) {
                        control.disabled = !isVisible;
                    }
                });
        });
    }


    export function syncMixtureUnitFields(block, materialKey) {
        if (!(block instanceof Element)) {
            return;
        }

        const unitSelect = block.querySelector(`[name$="${materialKey}PurchaseUnit"]`);
        const weightLabel = block.querySelector(
            `[data-mixture-unit-label="${materialKey}"]`
        );
        const priceLabel = block.querySelector(
            `[data-mixture-price-label="${materialKey}"]`
        );
        const weightInput = block.querySelector(
            `[data-mixture-unit-input="${materialKey}"]`
        );

        if (!(unitSelect instanceof HTMLSelectElement)) {
            return;
        }

        const isTonne = unitSelect.value === "tonne";
        const unitText = isTonne ? "т" : "кг";
        const purchaseText = isTonne ? "тонны" : "мешка";

        if (weightLabel) {
            weightLabel.textContent = `Вес единицы ${materialKey === "cement" ? "цемента" : materialKey === "sand" ? "песка" : "щебня"} (${unitText})`;
        }
        if (priceLabel) {
            priceLabel.textContent = `Цена ${purchaseText} ${
                materialKey === "cement" ? "цемента" : materialKey === "sand" ? "песка" : "щебня"
            }`;
        }
        if (weightInput instanceof HTMLInputElement) {
            const previousUnit = weightInput.dataset.displayUnit || unitText;
            const currentValue = Number(weightInput.value);
            if (Number.isFinite(currentValue) && currentValue > 0 && previousUnit !== unitText) {
                weightInput.value = previousUnit === "кг" && unitText === "т"
                    ? String(currentValue / 1000)
                    : previousUnit === "т" && unitText === "кг"
                        ? String(currentValue * 1000)
                        : weightInput.value;
            }
            if (isTonne) {
                weightInput.step = "0.001";
                weightInput.min = "0.001";
            } else {
                weightInput.step = "1";
                weightInput.min = "1";
            }
            weightInput.dataset.displayUnit = unitText;
        }
    }


    export function syncPileMixtureBlocks(form) {
        const calculator = form.querySelector('[name="calculator"]')?.value;
        if (calculator !== "pile_foundation") {
            return;
        }

        const unifiedToggle = form.querySelector('[name="useUnifiedConcreteMixtureSettings"]');
        const includePiles = !!form.querySelector('[name="includePiles"]')?.checked;
        const includeGrillage = !!form.querySelector('[name="includeGrillage"]')?.checked;
        const bothSelected = includePiles && includeGrillage;

        if (unifiedToggle) {
            unifiedToggle.disabled = !bothSelected;
            if (!bothSelected) {
                unifiedToggle.checked = true;
            }
            const toggleRow = unifiedToggle.closest(".brigmaster-estimator__toggle");
            if (toggleRow) {
                toggleRow.classList.toggle("is-disabled", !bothSelected);
            }
        }

        const useUnified = unifiedToggle ? unifiedToggle.checked : true;
        const sharedBlock = form.querySelector('[data-pile-mixture-block="shared"]');
        const pileBlock = form.querySelector('[data-pile-mixture-block="pile"]');
        const grillageBlock = form.querySelector(
            '[data-pile-mixture-block="grillage"]'
        );

        toggleVisibility(sharedBlock, useUnified);
        toggleVisibility(pileBlock, !useUnified);
        toggleVisibility(grillageBlock, !useUnified);
    }


    export function initMixtureFields(form) {
        const mixtureBlocks = form.querySelectorAll("[data-mixture-scope]");
        mixtureBlocks.forEach((block) => {
            syncMixtureBlock(block);
            ["cement", "sand", "gravel"].forEach((materialKey) => {
                syncMixtureUnitFields(block, materialKey);
                const unitSelect = block.querySelector(
                    `[name$="${materialKey}PurchaseUnit"]`
                );
                if (unitSelect) {
                    unitSelect.addEventListener("change", () => {
                        clearErrors(form);
                        markResultStale(form);
                        syncMixtureUnitFields(block, materialKey);
                    });
                }
            });
            const select = block.querySelector("[data-mixture-type-select]");
            if (select) {
                select.addEventListener("change", () => {
                    clearErrors(form);
                    markResultStale(form);
                    syncMixtureBlock(block);
                });
            }
        });

        const pileUnifiedToggle = form.querySelector(
            '[name="useUnifiedConcreteMixtureSettings"]'
        );
        if (pileUnifiedToggle) {
            syncPileMixtureBlocks(form);
            pileUnifiedToggle.addEventListener("change", () => {
                clearErrors(form);
                markResultStale(form);
                syncPileMixtureBlocks(form);
            });
            ["includePiles", "includeGrillage"].forEach((name) => {
                const calcToggle = form.querySelector(`[name="${name}"]`);
                if (calcToggle) {
                    calcToggle.addEventListener("change", () => {
                        syncPileMixtureBlocks(form);
                    });
                }
            });
        }
    }
