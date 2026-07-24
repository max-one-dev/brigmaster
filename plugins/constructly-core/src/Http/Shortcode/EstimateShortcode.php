<?php

declare(strict_types=1);

namespace Brigmaster\Http\Shortcode;

use Brigmaster\Presentation\Estimator\Fields\BrickEstimatorFields;
use Brigmaster\Presentation\Estimator\Fields\ConcreteMixtureFields;
use Brigmaster\Presentation\Estimator\Fields\DrywallEstimatorFields;
use Brigmaster\Presentation\Estimator\Fields\ScreedFields;
use Brigmaster\Presentation\Estimator\Fields\SlabFoundationFields;
use Brigmaster\Presentation\Estimator\Fields\StripPileFields;
use Brigmaster\Presentation\Estimator\Fields\TileEstimatorFields;
use Brigmaster\Presentation\Estimator\ResultTemplate\BrickResultTemplate;
use Brigmaster\Presentation\Estimator\ResultTemplate\PileResultTemplate;
use Brigmaster\Presentation\Estimator\ResultTemplate\ScreedResultTemplate;
use Brigmaster\Presentation\Estimator\ResultTemplate\SlabResultTemplate;
use Brigmaster\Presentation\Estimator\ResultTemplate\StripResultTemplate;
use Brigmaster\Presentation\Estimator\ResultTemplate\DrywallResultTemplate;
use Brigmaster\Presentation\Estimator\ResultTemplate\TileResultTemplate;
use Brigmaster\Presentation\Html\MarkupHelpers;

final class EstimateShortcode
{
    private readonly EstimatorAssetEnqueuer $assetEnqueuer;

    public function __construct(
        private readonly string $pluginFilePath
    ) {
        $this->assetEnqueuer = new EstimatorAssetEnqueuer($pluginFilePath);
    }

    public function registerShortcodes(): void
    {
        add_filter('rank_math/json_ld', [new FoundationHubFaqSchema(), 'addFoundationHubFaqSchema'], 20, 2);

        add_shortcode('brigmaster_concrete_estimator', [$this, 'renderConcreteShortcode']);
        add_shortcode('brigmaster_strip_foundation_estimator', [$this, 'renderStripFoundationShortcode']);
        add_shortcode('brigmaster_pile_foundation_estimator', [$this, 'renderPileFoundationShortcode']);
        add_shortcode('brigmaster_brick_estimator', [$this, 'renderBrickShortcode']);
        add_shortcode('brigmaster_screed_estimator', [$this, 'renderScreedShortcode']);
        add_shortcode('brigmaster_drywall_estimator', [$this, 'renderDrywallShortcode']);
        add_shortcode('brigmaster_tile_estimator', [$this, 'renderTileShortcode']);
        add_shortcode('brigmaster_foundation_hub', [$this, 'renderFoundationHubShortcode']);
    }

    public function renderConcreteShortcode(array $attributes = [], ?string $content = null, string $shortcodeTag = ''): string
    {
        return $this->renderEstimator('slab_foundation', $this->resolveEstimatorTitle($attributes, $shortcodeTag));
    }

    public function renderStripFoundationShortcode(array $attributes = [], ?string $content = null, string $shortcodeTag = ''): string
    {
        return $this->renderEstimator('strip_foundation', $this->resolveEstimatorTitle($attributes, $shortcodeTag));
    }

    public function renderPileFoundationShortcode(array $attributes = [], ?string $content = null, string $shortcodeTag = ''): string
    {
        return $this->renderEstimator('pile_foundation', $this->resolveEstimatorTitle($attributes, $shortcodeTag));
    }

    public function renderScreedShortcode(array $attributes = [], ?string $content = null, string $shortcodeTag = ''): string
    {
        return $this->renderEstimator('screed', $this->resolveEstimatorTitle($attributes, $shortcodeTag));
    }

    public function renderBrickShortcode(array $attributes = [], ?string $content = null, string $shortcodeTag = ''): string
    {
        return $this->renderEstimator('brick', $this->resolveEstimatorTitle($attributes, $shortcodeTag));
    }

    public function renderDrywallShortcode(array $attributes = [], ?string $content = null, string $shortcodeTag = ''): string
    {
        return $this->renderEstimator('drywall', $this->resolveEstimatorTitle($attributes, $shortcodeTag));
    }

    public function renderTileShortcode(array $attributes = [], ?string $content = null, string $shortcodeTag = ''): string
    {
        return $this->renderEstimator('tile', $this->resolveEstimatorTitle($attributes, $shortcodeTag));
    }

    /**
     * @param array<string, string> $attributes
     */
    private function resolveEstimatorTitle(array $attributes, string $shortcodeTag): string
    {
        $atts = shortcode_atts(['title' => ''], $attributes, $shortcodeTag);

        return trim((string) $atts['title']);
    }

    public function renderFoundationHubShortcode(array $attributes = [], ?string $content = null, string $shortcodeTag = ''): string
    {
        if (wp_style_is('bm-core-hub', 'registered')) {
            wp_enqueue_style('bm-core-hub');
        }

        return (string) apply_filters('constructly_render_foundation_hub', '');
    }

    private function renderEstimator(string $calculator, string $title): string
    {
        if (!in_array($calculator, ['slab_foundation', 'strip_foundation', 'pile_foundation', 'brick', 'screed', 'drywall', 'tile'], true)) {
            return '';
        }

        $this->assetEnqueuer->enqueue($calculator);

        $heading = trim($title);

        $instanceId = wp_unique_id('brigmaster-' . $calculator . '-');
        $modeFieldId = $instanceId . 'mode';
        $modeHintId = $instanceId . 'mode-hint';
        $estimatorModifierClass = in_array($calculator, ['strip_foundation', 'pile_foundation', 'brick', 'tile', 'drywall'], true)
            ? ' brigmaster-estimator--with-accordions'
            : '';
        if ($calculator === 'screed') {
            $estimatorModifierClass .= ' brigmaster-estimator--screed-compact';
        }
        ob_start();
        ?>
        <div class="brigmaster-estimator brigmaster-estimator--<?php echo esc_attr(str_replace('_', '-', $calculator)); ?><?php echo esc_attr($estimatorModifierClass); ?>" data-calculator="<?php echo esc_attr($calculator); ?>">
            <?php if ($heading !== '') : ?>
            <h2 class="brigmaster-estimator__title"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>
            <form class="brigmaster-estimate-form" novalidate>
                <input type="hidden" name="calculator" value="<?php echo esc_attr($calculator); ?>">

                <?php if ($calculator !== 'pile_foundation') : ?>
                    <div class="brigmaster-estimator__field" data-field-group="estimator-mode">
                        <label for="<?php echo esc_attr($modeFieldId); ?>">Режим расчета</label>
                        <select id="<?php echo esc_attr($modeFieldId); ?>" name="mode" required aria-describedby="<?php echo esc_attr($modeHintId); ?>">
                            <?php if (in_array($calculator, ['slab_foundation', 'screed', 'brick', 'tile', 'drywall'], true)) : ?>
                                <option value="dimensions">По длине и ширине</option>
                                <option value="area">По площади</option>
                            <?php elseif ($calculator === 'strip_foundation') : ?>
                                <option value="perimeter">По общей длине ленты</option>
                                <option value="house">По параметрам дома</option>
                                <option value="segments">По участкам ленты</option>
                            <?php endif; ?>
                        </select>
                        <p id="<?php echo esc_attr($modeHintId); ?>" class="brigmaster-estimator__mode-hint" data-mode-hint aria-live="polite"></p>
                        <div class="brigmaster-estimator__error" data-field-error="mode" aria-live="polite"></div>
                    </div>
                <?php endif; ?>

                <?php if ($calculator === 'slab_foundation') : ?>
                    <?php echo SlabFoundationFields::render($instanceId); ?>
                <?php endif; ?>

                <?php echo StripPileFields::render($instanceId, $calculator); ?>

                <?php if ($calculator === 'brick') : ?>
                    <?php echo BrickEstimatorFields::render($instanceId); ?>
                <?php endif; ?>

                <?php if ($calculator === 'drywall') : ?>
                    <?php echo DrywallEstimatorFields::render($instanceId); ?>
                <?php endif; ?>

                <?php if ($calculator === 'screed') : ?>
                    <?php echo ScreedFields::render($instanceId); ?>
                <?php endif; ?>

                <?php if ($calculator === 'tile') : ?>
                    <?php echo TileEstimatorFields::render($instanceId); ?>
                <?php endif; ?>

                <div class="brigmaster-estimator__validation-summary" data-validation-summary role="alert" hidden></div>
                <button type="submit">Рассчитать</button>
                <div class="brigmaster-estimator__error" data-field-error="general" aria-live="assertive"></div>
            </form>

            <div class="brigmaster-estimator__result" data-result hidden aria-live="polite" aria-atomic="true" tabindex="-1">
                <p class="brigmaster-estimator__result-stale-notice" data-result-stale-notice hidden>Результат устарел — нажмите «Рассчитать» снова.</p>
                <?php if ($calculator === 'slab_foundation') : ?>
                    <?php echo SlabResultTemplate::render(); ?>
                <?php elseif ($calculator === 'strip_foundation') : ?>
                    <?php echo StripResultTemplate::render(); ?>
                <?php elseif ($calculator === 'pile_foundation') : ?>
                    <?php echo PileResultTemplate::render(); ?>
                <?php elseif ($calculator === 'screed') : ?>
                    <?php echo ScreedResultTemplate::render(); ?>
                <?php elseif ($calculator === 'brick') : ?>
                    <?php echo BrickResultTemplate::render(); ?>
                <?php elseif ($calculator === 'tile') : ?>
                    <?php echo TileResultTemplate::render(); ?>
                <?php elseif ($calculator === 'drywall') : ?>
                    <?php echo DrywallResultTemplate::render(); ?>
                <?php endif; ?>
            </div>
            <div class="brigmaster-estimator__tooltip-backdrop" data-tooltip-backdrop hidden></div>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
