

export function formatNumber(value) {
        const numericValue = Number(value);
        if (!Number.isFinite(numericValue)) {
            return "-";
        }

        const normalized = numericValue.toFixed(3).replace(/\.?0+$/, "");
        const parts = normalized.split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
        return parts.join(".");
    }

    export function escapeHtml(text) {
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }


    export function hasMeaningfulNumber(value) {
        return value !== null && value !== "" && Number.isFinite(Number(value));
    }


    export function normalizePurchaseWeight(rawValue, purchaseUnit) {
        const numericValue = Number(rawValue);
        if (!Number.isFinite(numericValue) || numericValue <= 0) {
            return rawValue;
        }

        return purchaseUnit === "tonne"
            ? String(numericValue * 1000)
            : String(numericValue);
    }
