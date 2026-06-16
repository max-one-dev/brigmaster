import { clearPrintParamsSummary, updatePrintParamsSummary } from "../ui/params-summary.js";


const MODE_HINTS_SLAB = {
        dimensions:
            "Ввод по длине и ширине — нужен для опций арматуры и опалубки.",
        area:
            "Ввод по площади; арматура и опалубка — только если дополнительно указать габариты (как в форме).",
    };

const MODE_HINTS_SCREED = {
        dimensions:
            "Ввод по длине и ширине — нужен для опций арматуры.",
        area:
            "Ввод по площади; арматура доступна только в режиме по длине и ширине.",
    };

const MODE_HINTS_STRIP = {
        perimeter:
            "Считаем объём по одной общей длине и размерам сечения.",
        house:
            "Сначала получаем длину по габаритам дома, затем считаем объём по сечению.",
        segments:
            "Длина складывается из участков, объём — по суммарной длине и сечению.",
    };

const MODE_HINTS_BRICK = {
        dimensions:
            "Ввод по общей длине и средней высоте стен. Подходит, когда геометрия дома известна.",
        area:
            "Ввод по площади стен без вычета проёмов. Ниже можно дополнительно вычесть окна и двери.",
    };

const MODE_HINTS_TILE = {
        dimensions:
            "Расчёт по размерам прямоугольной зоны. В этом режиме доступны ориентировочные подрезки и раскладка.",
        area:
            "Расчёт по общей площади. Материалы считаются корректно, а ориентировочная раскладка не строится, потому что геометрия не задана.",
    };

const MODE_HINTS_DRYWALL = {
        dimensions:
            "Ввод по размерам нужен для расчёта профилей, подвесов и крепежа по каркасу.",
        area:
            "Ввод по площади считает листы и отделку, но не считает каркас, потому что геометрия конструкции не задана.",
    };


    export function buildPrefixedFieldName(prefix, suffix) {
        if (!prefix) {
            return suffix;
        }

        return `${prefix}${suffix.charAt(0).toUpperCase()}${suffix.slice(1)}`;
    }


    export function getMixtureErrorPrefix(prefix) {
        if (prefix === "pile") {
            return "pileMixture";
        }
        if (prefix === "grillage") {
            return "grillageMixture";
        }

        return "mixture";
    }


    export function getEstimatorShell(form) {
        // Prefer the theme calculator layout so result lookups keep working when the
        // theme relocates [data-result] into the sticky aside (outside .brigmaster-estimator).
        // Falls back to the estimator widget when the plugin renders standalone.
        return (
            form.closest(".bm-calculator-layout") ??
            form.closest(".brigmaster-estimator") ??
            form.parentElement
        );
    }


    export function updateValidationSummary(form) {
        const box = form.querySelector("[data-validation-summary]");
        if (!box) {
            return;
        }
        const errors = [...form.querySelectorAll("[data-field-error]")].filter(
            (n) => n.textContent && String(n.textContent).trim()
        );
        if (errors.length >= 1) {
            box.textContent = "Исправьте отмеченные поля.";
            box.hidden = false;
        } else {
            box.textContent = "";
            box.hidden = true;
        }
    }


    export function markResultStale(form) {
        if (form.dataset.suspendStaleTracking === "1") {
            return;
        }

        const resultNode = getEstimatorShell(form)?.querySelector("[data-result]");
        if (
            !resultNode ||
            resultNode.hidden ||
            !resultNode.classList.contains("is-success")
        ) {
            return;
        }
        resultNode.classList.add("is-stale");
        const notice = resultNode.querySelector("[data-result-stale-notice]");
        if (notice) {
            notice.hidden = false;
        }
        Array.from(resultNode.children).forEach((child) => {
            if (child !== notice) {
                child.hidden = true;
            }
        });
    }


    // Notify the surrounding UI (e.g. the theme's mobile sticky-result bar) that the
    // result state changed, so it can react without watching DOM classes. Bubbles to
    // document; detail.success is true once a calculation succeeds, false when cleared.
    function notifyResultChange(resultNode, success) {
        if (!resultNode) {
            return;
        }
        resultNode.dispatchEvent(
            new CustomEvent("brigmaster:result-change", {
                bubbles: true,
                detail: { success },
            })
        );
    }


    export function finalizeSuccessfulResult(form) {
        const shell = getEstimatorShell(form);
        const resultNode = shell?.querySelector("[data-result]");
        if (resultNode) {
            resultNode.classList.remove("is-stale");
            const notice = resultNode.querySelector("[data-result-stale-notice]");
            if (notice) {
                notice.hidden = true;
            }
            Array.from(resultNode.children).forEach((child) => {
                if (child !== notice) {
                    child.hidden = false;
                }
            });
        }
        const el = shell?.querySelector("[data-result]");
        if (el && !el.hidden) {
            el.scrollIntoView({ behavior: "smooth", block: "start" });
            try {
                el.focus({ preventScroll: true });
            } catch (_e) {
                /* ignore */
            }
        }

        window.setTimeout(() => {
            delete form.dataset.suspendStaleTracking;
        }, 0);

        updatePrintParamsSummary(form);
        notifyResultChange(resultNode, true);
    }


    export function updateModeHintForForm(form) {
        const calculator = form.querySelector('[name="calculator"]')?.value;
        const mode = form.querySelector('[name="mode"]')?.value || "";
        const hintNodes = form.querySelectorAll("[data-mode-hint]");
        if (!hintNodes.length) {
            return;
        }
        let text = "";
        if (calculator === "brick" && MODE_HINTS_BRICK[mode]) {
            text = MODE_HINTS_BRICK[mode];
        } else if (calculator === "tile" && MODE_HINTS_TILE[mode]) {
            text = MODE_HINTS_TILE[mode];
        } else if (calculator === "drywall" && MODE_HINTS_DRYWALL[mode]) {
            text = MODE_HINTS_DRYWALL[mode];
        } else if (calculator === "screed" && MODE_HINTS_SCREED[mode]) {
            text = MODE_HINTS_SCREED[mode];
        } else if (
            calculator === "slab_foundation" &&
            MODE_HINTS_SLAB[mode]
        ) {
            text = MODE_HINTS_SLAB[mode];
        } else if (
            (calculator === "strip_foundation" || calculator === "pile_foundation") &&
            MODE_HINTS_STRIP[mode]
        ) {
            text = MODE_HINTS_STRIP[mode];
        }
        hintNodes.forEach((node) => {
            node.textContent = text;
        });
    }

    export function initModeScenarioUi(form) {
        const modeSelect = form.querySelector('[name="mode"]');
        if (modeSelect) {
            modeSelect.addEventListener("change", () => {
                updateModeHintForForm(form);
            });
        }
        updateModeHintForForm(form);
    }


    export function initStaleOnFormChange(form) {
        if (form.dataset.staleBound === "1") {
            return;
        }
        form.dataset.staleBound = "1";
        form.addEventListener("change", () => {
            markResultStale(form);
        });
        form.addEventListener("input", (event) => {
            const target = event.target;
            if (
                target instanceof HTMLInputElement ||
                target instanceof HTMLTextAreaElement
            ) {
                markResultStale(form);
            }
        });
    }


    export function clearErrors(form) {
        const errorNodes = form.querySelectorAll("[data-field-error]");
        errorNodes.forEach((node) => {
            node.textContent = "";
        });
        form.classList.remove("has-errors");
        const summary = form.querySelector("[data-validation-summary]");
        if (summary) {
            summary.textContent = "";
            summary.hidden = true;
        }
    }


    export function clearResult(form) {
        const resultNode = getEstimatorShell(form)?.querySelector("[data-result]");
        if (!resultNode) {
            return;
        }

        const concreteVolumeNode = resultNode.querySelector("[data-result-concrete-volume]");
        const concreteAreaNode = resultNode.querySelector("[data-result-concrete-area]");
        const concreteHeightNode = resultNode.querySelector("[data-result-concrete-height]");
        const reinforcementCard = resultNode.querySelector('[data-result-card="reinforcement"]');
        const formworkCard = resultNode.querySelector('[data-result-card="formwork"]');
        const schemeNode = resultNode.querySelector("[data-slab-scheme]");
        const volumeNode = resultNode.querySelector("[data-result-volume]");
        const materialNode = resultNode.querySelector("[data-result-material]");
        const stripConcreteLengthNode = resultNode.querySelector("[data-result-strip-concrete-length]");
        const stripConcreteVolumeNode = resultNode.querySelector("[data-result-strip-concrete-volume]");
        const stripRebarLongitudinalNode = resultNode.querySelector("[data-result-strip-rebar-longitudinal-mass]");
        const stripRebarTransverseNode = resultNode.querySelector("[data-result-strip-rebar-transverse-mass]");
        const stripRebarTotalNode = resultNode.querySelector("[data-result-strip-rebar-total-mass]");
        const stripFormworkAreaNode = resultNode.querySelector("[data-result-strip-formwork-area]");
        const stripFormworkLinearNode = resultNode.querySelector("[data-result-strip-formwork-linear]");
        const stripConcreteCard = resultNode.querySelector('[data-result-card="strip-concrete"]');
        const stripReinforcementCard = resultNode.querySelector('[data-result-card="strip-reinforcement"]');
        const stripFormworkCard = resultNode.querySelector('[data-result-card="strip-formwork"]');
        const calculatorValue = form.querySelector('[name="calculator"]')?.value;
        const pileReinforcementCard = resultNode.querySelector('[data-result-card="pile-reinforcement"]');
        const pileConcreteCard = resultNode.querySelector('[data-result-card="pile-concrete"]');
        const pileHeaderSection = resultNode.querySelector('[data-result-section="pile-header"]');
        const grillageHeaderSection = resultNode.querySelector('[data-result-section="grillage-header"]');
        const pileTypeNode = resultNode.querySelector("[data-result-pile-type]");
        const pileCountNode = resultNode.querySelector("[data-result-pile-count]");
        const pileConcreteVolumeNode = resultNode.querySelector("[data-result-pile-concrete-volume]");
        const pileConcretePerPileNode = resultNode.querySelector("[data-result-pile-concrete-per-pile]");
        const pilePerPileRow = resultNode.querySelector("[data-result-pile-per-pile-row]");
        const pileNoteNode = resultNode.querySelector("[data-result-pile-note]");
        const pileNoteRow = resultNode.querySelector("[data-result-pile-note-row]");
        const screedAreaNode = resultNode.querySelector("[data-result-screed-area]");
        const screedHeightNode = resultNode.querySelector("[data-result-screed-height]");
        const screedRebarCard = resultNode.querySelector('[data-result-card="screed-reinforcement"]');
        const screedRebarMass = resultNode.querySelector("[data-result-screed-rebar-mass]");
        const screedRebarLen = resultNode.querySelector("[data-result-screed-rebar-length]");
        const mixtureCard = resultNode.querySelector('[data-result-card="mixture"]');
        const pileFoundationMixtureCard = resultNode.querySelector('[data-result-card="pile-foundation-mixture"]');
        const grillageMixtureCard = resultNode.querySelector('[data-result-card="grillage-mixture"]');
        const brickSummaryCard = resultNode.querySelector('[data-result-card="brick-summary"]');
        const brickGeometryCard = resultNode.querySelector('[data-result-card="brick-geometry"]');
        const brickMortarCard = resultNode.querySelector('[data-result-card="brick-mortar"]');
        const brickMeshCard = resultNode.querySelector('[data-result-card="brick-mesh"]');
        const brickLintelsCard = resultNode.querySelector('[data-result-card="brick-lintels"]');
        const brickCostsCard = resultNode.querySelector('[data-result-card="brick-costs"]');

        if (concreteVolumeNode) {
            concreteVolumeNode.textContent = "-";
        }
        if (concreteAreaNode) {
            concreteAreaNode.textContent = "-";
        }
        if (concreteHeightNode) {
            concreteHeightNode.textContent = "-";
        }
        if (reinforcementCard) {
            reinforcementCard.hidden = true;
            reinforcementCard.innerHTML = "";
        }
        if (formworkCard) {
            formworkCard.hidden = true;
            formworkCard.innerHTML = "";
        }
        if (schemeNode) {
            schemeNode.innerHTML = "";
        }

        if (volumeNode) {
            volumeNode.textContent = "-";
        }

        if (materialNode) {
            materialNode.textContent = "-";
        }
        if (screedAreaNode) {
            screedAreaNode.textContent = "-";
        }
        if (screedHeightNode) {
            screedHeightNode.textContent = "-";
        }
        if (screedRebarCard && screedRebarMass && screedRebarLen) {
            screedRebarCard.hidden = true;
            screedRebarMass.textContent = "-";
            screedRebarLen.textContent = "-";
        }
        if (mixtureCard) {
            mixtureCard.hidden = true;
            mixtureCard.innerHTML = "";
        }
        if (pileFoundationMixtureCard) {
            pileFoundationMixtureCard.hidden = true;
            pileFoundationMixtureCard.innerHTML = "";
        }
        if (grillageMixtureCard) {
            grillageMixtureCard.hidden = true;
            grillageMixtureCard.innerHTML = "";
        }
        [brickSummaryCard, brickGeometryCard, brickMortarCard, brickMeshCard, brickLintelsCard, brickCostsCard].forEach((card) => {
            if (card) {
                card.hidden = false;
                card.innerHTML = "";
            }
        });
        if (brickMeshCard) {
            brickMeshCard.hidden = true;
        }
        if (brickLintelsCard) {
            brickLintelsCard.hidden = true;
        }
        if (brickCostsCard) {
            brickCostsCard.hidden = true;
        }
        if (stripConcreteLengthNode) {
            stripConcreteLengthNode.textContent = "-";
        }
        if (stripConcreteVolumeNode) {
            stripConcreteVolumeNode.textContent = "-";
        }
        if (stripRebarLongitudinalNode) {
            stripRebarLongitudinalNode.textContent = "-";
        }
        if (stripRebarTransverseNode) {
            stripRebarTransverseNode.textContent = "-";
        }
        if (stripRebarTotalNode) {
            stripRebarTotalNode.textContent = "-";
        }
        if (stripFormworkAreaNode) {
            stripFormworkAreaNode.textContent = "-";
        }
        if (stripFormworkLinearNode) {
            stripFormworkLinearNode.textContent = "-";
        }
        if (stripConcreteCard && calculatorValue === "pile_foundation") {
            stripConcreteCard.hidden = true;
        }
        if (stripReinforcementCard) {
            stripReinforcementCard.hidden = true;
            if (calculatorValue === "pile_foundation") {
                stripReinforcementCard.innerHTML = "";
            }
        }
        if (stripFormworkCard) {
            stripFormworkCard.hidden = true;
        }
        if (pileHeaderSection) {
            pileHeaderSection.hidden = true;
        }
        if (grillageHeaderSection) {
            grillageHeaderSection.hidden = true;
        }
        if (pileConcreteCard) {
            pileConcreteCard.hidden = true;
        }
        if (pileTypeNode) {
            pileTypeNode.textContent = "-";
        }
        if (pileCountNode) {
            pileCountNode.textContent = "-";
        }
        if (pileConcreteVolumeNode) {
            pileConcreteVolumeNode.textContent = "-";
        }
        if (pileConcretePerPileNode) {
            pileConcretePerPileNode.textContent = "-";
        }
        if (pilePerPileRow) {
            pilePerPileRow.hidden = true;
        }
        if (pileNoteNode) {
            pileNoteNode.textContent = "-";
        }
        if (pileNoteRow) {
            pileNoteRow.hidden = true;
        }
        if (pileReinforcementCard) {
            pileReinforcementCard.hidden = true;
            pileReinforcementCard.innerHTML = "";
        }

        resultNode.classList.remove("is-stale");
        const staleNotice = resultNode.querySelector("[data-result-stale-notice]");
        if (staleNotice) {
            staleNotice.hidden = true;
        }
        resultNode.hidden = true;
        resultNode.classList.remove("is-success");
        clearPrintParamsSummary(form);
        notifyResultChange(resultNode, false);
    }


    export function setFieldError(form, field, message) {
        const target = form.querySelector(`[data-field-error="${field}"]`);
        if (!target) {
            return;
        }

        target.textContent = message;
        if (message) {
            form.classList.add("has-errors");
        }
    }


    export function readTrimmed(formData, field) {
        return String(formData.get(field) || "").trim();
    }


    export function setLoadingState(form, submitButton, isLoading) {
        if (!submitButton) {
            return;
        }

        if (!submitButton.dataset.defaultText) {
            submitButton.dataset.defaultText = submitButton.textContent || "Рассчитать";
        }

        submitButton.disabled = isLoading;
        submitButton.textContent = isLoading
            ? "Расчет..."
            : submitButton.dataset.defaultText;
        form.classList.toggle("is-loading", isLoading);
    }


    export function handleValidationErrors(form, errors) {
        if (!errors || typeof errors !== "object") {
            setFieldError(form, "general", "Ошибка валидации. Проверьте данные формы.");
            return;
        }

        let hasMappedErrors = false;
        Object.entries(errors).forEach(([field, messages]) => {
            if (!Array.isArray(messages) || messages.length === 0) {
                return;
            }
            const hasField = form.querySelector(`[data-field-error="${field}"]`);
            if (hasField) {
                setFieldError(form, field, String(messages[0]));
                hasMappedErrors = true;
            } else {
                setFieldError(form, "general", String(messages[0]));
            }
        });

        if (!hasMappedErrors && !form.querySelector('[data-field-error="general"]')?.textContent) {
            setFieldError(form, "general", "Ошибка валидации. Проверьте данные формы.");
        }
        updateValidationSummary(form);
    }


    export function toggleVisibility(node, isVisible) {
        if (!node) {
            return;
        }

        node.classList.toggle("brigmaster-estimator__field-group--hidden", !isVisible);
    }


    export function setModeLockState(form, shouldLock) {
        const toggleNames = ["includeReinforcement", "includeFormwork"];
        toggleNames.forEach((name) => {
            const checkbox = form.querySelector(`[name="${name}"]`);
            if (!checkbox) {
                return;
            }
            const row = checkbox.closest("[data-toggle-field]");
            const lockTrigger = row?.querySelector("[data-mode-lock-trigger]");
            const lockAnchor = lockTrigger?.closest(".brigmaster-estimator__tooltip-anchor");
            if (shouldLock) {
                if (!Object.prototype.hasOwnProperty.call(checkbox.dataset, "previousChecked")) {
                    checkbox.dataset.previousChecked = checkbox.checked ? "1" : "0";
                }
                checkbox.checked = false;
                checkbox.disabled = true;
                checkbox.setAttribute("aria-disabled", "true");
                row?.classList.add("is-disabled");
                lockAnchor?.classList.remove("brigmaster-estimator__tooltip-anchor--hidden");
                return;
            }

            checkbox.disabled = false;
            checkbox.removeAttribute("aria-disabled");
            if (Object.prototype.hasOwnProperty.call(checkbox.dataset, "previousChecked")) {
                checkbox.checked = checkbox.dataset.previousChecked === "1";
                delete checkbox.dataset.previousChecked;
            }
            row?.classList.remove("is-disabled");
            lockAnchor?.classList.add("brigmaster-estimator__tooltip-anchor--hidden");
        });
    }


    export function isMobileTooltipViewport() {
        return window.matchMedia("(max-width: 767px)").matches;
    }


    export function setTooltipBackdropVisible(form, isVisible) {
        const backdrop = getEstimatorShell(form)?.querySelector("[data-tooltip-backdrop]");
        if (!backdrop) {
            return;
        }
        backdrop.hidden = !isVisible;
        backdrop.classList.toggle("is-visible", isVisible);
    }


    export function closeAllTooltips(form) {
        const triggers = form.querySelectorAll("[data-tooltip-trigger]");
        triggers.forEach((trigger) => {
            const tooltipId = trigger.getAttribute("aria-controls");
            if (!tooltipId) {
                return;
            }
            const tooltip = form.querySelector(`#${tooltipId}`);
            if (!tooltip) {
                return;
            }
            trigger.setAttribute("aria-expanded", "false");
            tooltip.hidden = true;
            tooltip.classList.remove("is-open");
            tooltip.style.position = "";
            tooltip.style.left = "";
            tooltip.style.right = "";
            tooltip.style.top = "";
            tooltip.style.bottom = "";
            tooltip.style.maxWidth = "";
        });
        setTooltipBackdropVisible(form, false);
    }


    export function positionTooltipWithinViewport(trigger, tooltip) {
        if (isMobileTooltipViewport()) {
            tooltip.style.position = "";
            tooltip.style.left = "";
            tooltip.style.right = "";
            tooltip.style.top = "";
            tooltip.style.bottom = "";
            tooltip.style.maxWidth = "";
            return;
        }

        const margin = 12;
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const triggerRect = trigger.getBoundingClientRect();
        tooltip.style.position = "fixed";
        tooltip.style.right = "";
        tooltip.style.bottom = "";
        tooltip.style.maxWidth = `${Math.max(220, viewportWidth - margin * 2)}px`;
        tooltip.style.top = `${Math.round(triggerRect.bottom + 8)}px`;
        tooltip.style.left = `${Math.round(triggerRect.right - tooltip.offsetWidth)}px`;

        const rect = tooltip.getBoundingClientRect();
        let left = rect.left;
        let top = rect.top;

        if (rect.left < margin) {
            left = margin;
        }
        if (rect.right > viewportWidth - margin) {
            left = Math.max(margin, viewportWidth - margin - rect.width);
        }
        if (rect.bottom > viewportHeight - margin) {
            const aboveTop = triggerRect.top - rect.height - 8;
            top = aboveTop >= margin ? aboveTop : Math.max(margin, viewportHeight - margin - rect.height);
        }
        if (top < margin) {
            top = margin;
        }

        tooltip.style.left = `${Math.round(left)}px`;
        tooltip.style.top = `${Math.round(top)}px`;
    }


    export function openTooltip(form, trigger, tooltip) {
        closeAllTooltips(form);
        trigger.setAttribute("aria-expanded", "true");
        tooltip.hidden = false;
        tooltip.classList.add("is-open");
        positionTooltipWithinViewport(trigger, tooltip);
        setTooltipBackdropVisible(form, isMobileTooltipViewport());
    }


    export function toggleTooltip(form, trigger, shouldOpen) {
        const tooltipId = trigger.getAttribute("aria-controls");
        if (!tooltipId) {
            return;
        }
        const tooltip = form.querySelector(`#${tooltipId}`);
        if (!tooltip) {
            return;
        }

        if (shouldOpen) {
            openTooltip(form, trigger, tooltip);
            return;
        }

        trigger.setAttribute("aria-expanded", "false");
        tooltip.hidden = true;
        tooltip.classList.remove("is-open");
        setTooltipBackdropVisible(form, false);
    }


    export function initTooltips(form) {
        const triggers = form.querySelectorAll("[data-tooltip-trigger]");
        if (!triggers.length) {
            return;
        }

        triggers.forEach((trigger) => {
            if (trigger.dataset.tooltipBound === "1") {
                return;
            }
            trigger.addEventListener("mouseenter", () => {
                if (!isMobileTooltipViewport()) {
                    toggleTooltip(form, trigger, true);
                }
            });
            trigger.addEventListener("mouseleave", () => {
                if (!isMobileTooltipViewport()) {
                    toggleTooltip(form, trigger, false);
                }
            });
            trigger.addEventListener("focus", () => toggleTooltip(form, trigger, true));
            trigger.addEventListener("blur", () => {
                if (!isMobileTooltipViewport()) {
                    toggleTooltip(form, trigger, false);
                }
            });
            trigger.addEventListener("click", (event) => {
                event.preventDefault();
                if (isMobileTooltipViewport()) {
                    const expanded = trigger.getAttribute("aria-expanded") === "true";
                    toggleTooltip(form, trigger, !expanded);
                } else {
                    toggleTooltip(form, trigger, true);
                }
            });
            trigger.dataset.tooltipBound = "1";
        });

        if (form.dataset.tooltipGlobalBound !== "1") {
            const backdrop = getEstimatorShell(form)?.querySelector("[data-tooltip-backdrop]");
            if (backdrop) {
                backdrop.addEventListener("click", () => {
                    closeAllTooltips(form);
                });
            }

            document.addEventListener("keydown", (event) => {
                if (event.key === "Escape") {
                    closeAllTooltips(form);
                }
            });

            window.addEventListener("resize", () => {
                const openTrigger = form.querySelector('[data-tooltip-trigger][aria-expanded="true"]');
                if (!openTrigger) {
                    return;
                }
                const tooltipId = openTrigger.getAttribute("aria-controls");
                if (!tooltipId) {
                    return;
                }
                const tooltip = form.querySelector(`#${tooltipId}`);
                if (!tooltip || tooltip.hidden) {
                    return;
                }
                positionTooltipWithinViewport(openTrigger, tooltip);
                setTooltipBackdropVisible(form, isMobileTooltipViewport());
            });

            form.dataset.tooltipGlobalBound = "1";
        }

    }


    export function normalizePagePath() {
        const path = window.location.pathname || "/";
        if (path === "/") {
            return "/";
        }
        return path.endsWith("/") ? path : `${path}/`;
    }


    export function buildMetrikaBaseParams(form, payload) {
        const calculator_type = String(
            form.querySelector('[name="calculator"]')?.value || "",
        );
        const page_path = normalizePagePath();
        const params = { calculator_type, page_path };
        if (payload && payload.mode != null && String(payload.mode) !== "") {
            params.mode = String(payload.mode);
        }
        return params;
    }


    export function safeReachGoal(goalId, params) {
        const cfg = window.brigmasterEstimateFormData;
        if (!cfg?.metrikaEnabled || !cfg?.metrikaCounterId) {
            return;
        }
        if (typeof ym !== "function") {
            return;
        }
        try {
            ym(cfg.metrikaCounterId, "reachGoal", goalId, params || {});
        } catch (_err) {
            /* ignore */
        }
    }
