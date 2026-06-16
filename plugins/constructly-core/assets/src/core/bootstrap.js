import { postEstimate } from "./api.js";
import {
  buildMetrikaBaseParams,
  clearErrors,
  clearResult,
  handleValidationErrors,
  initModeScenarioUi,
  initStaleOnFormChange,
  initTooltips,
  safeReachGoal,
  setFieldError,
  setLoadingState,
  updateValidationSummary,
} from "./form-state.js";
import { initMixtureFields } from "./mixture.js";
import { initResultActions } from "../ui/result-actions.js";

async function onSubmit(event, calculatorModule) {
  event.preventDefault();

  const form = event.currentTarget;
  const endpoint = window.brigmasterEstimateFormData?.endpoint;
  const networkErrorMessage =
    window.brigmasterEstimateFormData?.networkErrorMessage ||
    "Не удалось выполнить запрос.";

  clearErrors(form);

  if (!endpoint) {
    safeReachGoal("brigmaster_calc_fail_config", {
      ...buildMetrikaBaseParams(form, {}),
      error_kind: "config",
    });
    setFieldError(form, "general", "Не настроен endpoint для расчета.");
    return;
  }

  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const formData = new FormData(form);
  const { isValid, payload } = calculatorModule.buildPayload(form, formData);
  const submitButton = form.querySelector('button[type="submit"]');
  let requestSucceeded = false;

  if (!isValid) {
    updateValidationSummary(form);
    safeReachGoal("brigmaster_calc_fail_client", {
      ...buildMetrikaBaseParams(form, payload),
      error_kind: "client_validation",
    });
    return;
  }

  form._lastRequestPayload = payload;
  form.dataset.suspendStaleTracking = "1";
  setLoadingState(form, submitButton, true);

  const baseParams = buildMetrikaBaseParams(form, payload);
  safeReachGoal("brigmaster_calc_request", { ...baseParams });

  try {
    const response = await postEstimate(endpoint, payload);
    let data;

    try {
      data = await response.json();
    } catch (_parseError) {
      safeReachGoal("brigmaster_calc_fail_api", {
        ...baseParams,
        error_kind: "api_other",
        http_status: response.status,
      });
      setFieldError(form, "general", networkErrorMessage);
      return;
    }

    if (response.ok) {
      clearResult(form);
      calculatorModule.showResult(form, data);
      requestSucceeded = true;
      safeReachGoal("brigmaster_calc_success", { ...baseParams });
      return;
    }

    const validationErrors = data?.errors || data?.validation_error?.errors;
    handleValidationErrors(form, validationErrors);
    const hasValidationErrors =
      validationErrors &&
      typeof validationErrors === "object" &&
      Object.keys(validationErrors).length > 0;
    const failApiParams = {
      ...baseParams,
      error_kind: hasValidationErrors ? "api_validation" : "api_other",
      http_status: response.status,
    };
    if (typeof data?.code === "string" && data.code !== "") {
      failApiParams.api_error_code = data.code;
    }
    safeReachGoal("brigmaster_calc_fail_api", failApiParams);
  } catch (_error) {
    safeReachGoal("brigmaster_calc_fail_network", {
      ...baseParams,
      error_kind: "network",
    });
    setFieldError(form, "general", networkErrorMessage);
  } finally {
    setLoadingState(form, submitButton, false);
    if (!requestSucceeded) {
      delete form.dataset.suspendStaleTracking;
    }
  }
}

function initForm(form, calculatorModule) {
  calculatorModule.init(form);
  initModeScenarioUi(form);
  initStaleOnFormChange(form);
  initMixtureFields(form);
  initTooltips(form);
  form.addEventListener("submit", (event) => onSubmit(event, calculatorModule));
  initResultActions(form);
}

export function initEstimateForms(calculatorModule) {
  document.addEventListener("DOMContentLoaded", () => {
    const forms = document.querySelectorAll(".brigmaster-estimate-form");
    forms.forEach((form) => {
      const calculator = form.querySelector('[name="calculator"]')?.value;
      if (calculator === calculatorModule.calculator) {
        initForm(form, calculatorModule);
      }
    });
  });
}
