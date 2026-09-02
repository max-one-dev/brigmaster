<?php

declare(strict_types=1);

namespace Brigmaster\Http\Shortcode;

/**
 * Registers and enqueues the per-calculator estimator front-end script as an ES module,
 * with localized REST endpoint + Yandex Metrika config.
 *
 * Extracted verbatim from EstimateShortcode without behavior changes.
 */
final class EstimatorAssetEnqueuer
{
    public function __construct(
        private readonly string $pluginFilePath
    ) {
    }

    public function enqueue(string $calculator): void
    {
        $baseUrl = plugin_dir_url($this->pluginFilePath);
        $basePath = plugin_dir_path($this->pluginFilePath);
        $calculatorEntryMap = [
            'slab_foundation' => 'slab',
            'strip_foundation' => 'strip',
            'pile_foundation' => 'pile',
            'brick' => 'brick',
            'screed' => 'screed',
            'drywall' => 'drywall',
            'tile' => 'tile',
        ];
        $entryName = $calculatorEntryMap[$calculator] ?? null;
        if (!is_string($entryName) || $entryName === '') {
            return;
        }

        $scriptRelativePath = 'assets/dist/calculators/' . $entryName . '.js';
        $scriptAbsolutePath = $basePath . $scriptRelativePath;
        if (!file_exists($scriptAbsolutePath)) {
            return;
        }
        $scriptHandle = 'brigmaster-estimate-form-' . $entryName;
        $assetVersion = (string) filemtime($scriptAbsolutePath);

        wp_register_script(
            $scriptHandle,
            $baseUrl . $scriptRelativePath,
            [],
            $assetVersion,
            true
        );
        wp_script_add_data($scriptHandle, 'type', 'module');
        add_filter('script_loader_tag', [$this, 'renderEstimateModuleScriptTag'], 10, 3);

        $metrika = $this->getYandexMetrikaFrontendConfig();

        wp_localize_script(
            $scriptHandle,
            'brigmasterEstimateFormData',
            [
                'endpoint'             => esc_url_raw(rest_url('brigmaster/v1/estimate')),
                'networkErrorMessage'  => 'Не удалось выполнить запрос. Проверьте подключение и попробуйте снова.',
                'metrikaCounterId'     => $metrika['counterId'],
                'metrikaEnabled'       => $metrika['enabled'],
                'ga4MeasurementId'     => $metrika['ga4MeasurementId'],
                'ga4Enabled'           => $metrika['ga4Enabled'],
                'isProduction'         => $metrika['isProduction'],
            ]
        );

        wp_enqueue_script($scriptHandle);
    }

    public function renderEstimateModuleScriptTag(string $tag, string $handle, string $src): string
    {
        if (!str_starts_with($handle, 'brigmaster-estimate-form-')) {
            return $tag;
        }

        return '<script type="module" src="' . esc_url($src) . '" id="' . esc_attr($handle) . '-js"></script>' . "\n";
    }

    /**
     * Yandex Metrika + GA4 frontend config for localized data.
     * Production detection delegates to brigmaster_is_production() (mu-plugin).
     * Filter 'brigmaster_is_production_for_yandex_goals' preserved for back-compat.
     *
     * @return array{counterId: int, enabled: bool, ga4MeasurementId: string, ga4Enabled: bool, isProduction: bool}
     */
    private function getYandexMetrikaFrontendConfig(): array
    {
        // Single source of truth; falls back to wp_get_environment_type() if mu-plugin absent.
        $isProduction = function_exists('brigmaster_is_production')
            ? brigmaster_is_production()
            : wp_get_environment_type() === 'production';
        // Back-compat filter kept intentionally.
        $isProduction = (bool) apply_filters('brigmaster_is_production_for_yandex_goals', $isProduction);

        // Metrika counter.
        $counterId = 0;
        if (defined('BRIGMASTER_YANDEX_METRIKA_COUNTER_ID')) {
            $counterId = (int) BRIGMASTER_YANDEX_METRIKA_COUNTER_ID;
        }
        $counterId = (int) apply_filters('brigmaster_yandex_metrika_counter_id', $counterId);
        $enabled = $isProduction && $counterId > 0;

        // GA4 measurement id.
        $ga4Id = '';
        if (defined('BRIGMASTER_GA4_MEASUREMENT_ID') && '' !== BRIGMASTER_GA4_MEASUREMENT_ID) {
            $ga4Id = (string) BRIGMASTER_GA4_MEASUREMENT_ID;
        }
        $ga4Id     = (string) apply_filters('brigmaster_ga4_measurement_id', $ga4Id);
        $ga4Enabled = $isProduction && '' !== $ga4Id;

        return [
            'counterId'       => $enabled ? $counterId : 0,
            'enabled'         => $enabled,
            'ga4MeasurementId' => $ga4Enabled ? $ga4Id : '',
            'ga4Enabled'      => $ga4Enabled,
            'isProduction'    => $isProduction,
        ];
    }
}
