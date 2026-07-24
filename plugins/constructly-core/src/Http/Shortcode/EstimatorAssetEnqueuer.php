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
                'endpoint' => esc_url_raw(rest_url('brigmaster/v1/estimate')),
                'networkErrorMessage' => 'Не удалось выполнить запрос. Проверьте подключение и попробуйте снова.',
                'metrikaCounterId' => $metrika['counterId'],
                'metrikaEnabled' => $metrika['enabled'],
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
     * Yandex Metrika reachGoal when production (child theme {@see constructly_is_production_site()} if present)
     * and {@see BRIGMASTER_YANDEX_METRIKA_COUNTER_ID} is set. Counter ID: wp-config constant + filter.
     *
     * @return array{counterId: int, enabled: bool}
     */
    private function getYandexMetrikaFrontendConfig(): array
    {
        $isProduction = function_exists('constructly_is_production_site')
            ? constructly_is_production_site()
            : wp_get_environment_type() === 'production';
        $isProduction = (bool) apply_filters('brigmaster_is_production_for_yandex_goals', $isProduction);

        $counterId = 0;
        if (defined('BRIGMASTER_YANDEX_METRIKA_COUNTER_ID')) {
            $counterId = (int) BRIGMASTER_YANDEX_METRIKA_COUNTER_ID;
        }

        $counterId = (int) apply_filters('brigmaster_yandex_metrika_counter_id', $counterId);

        $enabled = $isProduction && $counterId > 0;

        return [
            'counterId' => $enabled ? $counterId : 0,
            'enabled' => $enabled,
        ];
    }
}
