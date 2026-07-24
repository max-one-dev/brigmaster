<?php

declare(strict_types=1);

namespace Brigmaster\Http\Shortcode;

use Brigmaster\Presentation\Estimator\Fields\BrickEstimatorFields;
use Brigmaster\Presentation\Estimator\Fields\ConcreteMixtureFields;
use Brigmaster\Presentation\Estimator\Fields\DrywallEstimatorFields;
use Brigmaster\Presentation\Estimator\Fields\TileEstimatorFields;
use Brigmaster\Presentation\Estimator\ResultTemplate\BrickResultTemplate;
use Brigmaster\Presentation\Estimator\ResultTemplate\PileResultTemplate;
use Brigmaster\Presentation\Estimator\ResultTemplate\SlabResultTemplate;
use Brigmaster\Presentation\Estimator\ResultTemplate\StripResultTemplate;
use Brigmaster\Presentation\Estimator\ResultTemplate\DrywallResultTemplate;
use Brigmaster\Presentation\Estimator\ResultTemplate\TileResultTemplate;
use Brigmaster\Presentation\Html\MarkupHelpers;

final class EstimateShortcode
{
    public function __construct(
        private readonly string $pluginFilePath
    ) {
    }

    public function registerShortcodes(): void
    {
        add_filter('rank_math/json_ld', [$this, 'addFoundationHubFaqSchema'], 20, 2);

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

    /**
     * Injects FAQPage for pages that only render [brigmaster_foundation_hub] (Rank Math merges into WebPage).
     *
     * @param array<string, mixed> $data
     * @param mixed                $jsonLd Unused; signature matches rank_math/json_ld.
     * @return array<string, mixed>
     */
    public function addFoundationHubFaqSchema(array $data, mixed $jsonLd): array
    {
        if (!is_singular()) {
            return $data;
        }

        global $post;
        if (!$post instanceof \WP_Post) {
            return $data;
        }

        $postContent = (string) $post->post_content;

        if (has_block('rank-math/faq-block', $postContent)) {
            return $data;
        }

        if (
            !has_block('constructly/foundation-hub', $postContent)
            && !has_shortcode($postContent, 'brigmaster_foundation_hub')
        ) {
            return $data;
        }

        $pairs = [
            [
                'id' => 'foundation-hub-faq-1',
                'name' => 'Что делает этот раздел, а что не делает?',
                'text' => 'Хаб помогает перейти к нужному фундаментному калькулятору, но не выбирает тип основания автоматически и не заменяет решение проектировщика.',
            ],
            [
                'id' => 'foundation-hub-faq-2',
                'name' => 'Можно ли ориентироваться только на онлайн-калькулятор?',
                'text' => 'Нет. Каждый фундаментный калькулятор дает предварительную оценку материалов, но не заменяет проект, геологию и проверку несущей способности.',
            ],
            [
                'id' => 'foundation-hub-faq-3',
                'name' => 'Как выбрать между плитой, лентой и сваями?',
                'text' => 'Сначала определите конструктивную схему по грунту, нагрузкам и условиям участка. После этого используйте соответствующий калькулятор, чтобы оценить материалы внутри выбранного варианта.',
            ],
            [
                'id' => 'foundation-hub-faq-4',
                'name' => 'Почему результаты на разных страницах отличаются?',
                'text' => 'Плитный, ленточный и свайный фундамент рассчитываются по разным моделям и с разным набором полей, поэтому итоговые показатели не совпадают между собой.',
            ],
        ];

        if (!isset($data['faqs'])) {
            $data['faqs'] = [
                '@type' => 'FAQPage',
                'mainEntity' => [],
            ];
        }

        $permalinkBase = get_permalink($post) . '#';
        foreach ($pairs as $row) {
            $data['faqs']['mainEntity'][] = [
                '@type' => 'Question',
                'url' => esc_url($permalinkBase . $row['id']),
                'name' => $row['name'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $row['text'],
                ],
            ];
        }

        return $data;
    }

    private function renderEstimator(string $calculator, string $title): string
    {
        if (!in_array($calculator, ['slab_foundation', 'strip_foundation', 'pile_foundation', 'brick', 'screed', 'drywall', 'tile'], true)) {
            return '';
        }

        $this->enqueueAssets($calculator);

        $heading = trim($title);

        $instanceId = wp_unique_id('brigmaster-' . $calculator . '-');
        $modeFieldId = $instanceId . 'mode';
        $areaFieldId = $instanceId . 'area';
        $lengthFieldId = $instanceId . 'length';
        $widthFieldId = $instanceId . 'width';
        $heightFieldId = $instanceId . 'height';
        $includeReinforcementFieldId = $instanceId . 'include-reinforcement';
        $includeFormworkFieldId = $instanceId . 'include-formwork';
        $totalLengthFieldId = $instanceId . 'total-length-m';
        $widthMFieldId = $instanceId . 'width-m';
        $heightMFieldId = $instanceId . 'height-m';
        $houseLengthFieldId = $instanceId . 'house-length-m';
        $houseWidthFieldId = $instanceId . 'house-width-m';
        $houseStripWidthFieldId = $instanceId . 'strip-section-width-m';
        $longitudinalBarsCountFieldId = $instanceId . 'longitudinal-bars-count';
        $longitudinalDiameterFieldId = $instanceId . 'longitudinal-diameter-mm';
        $longitudinalReserveFieldId = $instanceId . 'longitudinal-reserve-percent';
        $transverseDiameterFieldId = $instanceId . 'transverse-diameter-mm';
        $transverseStepFieldId = $instanceId . 'transverse-step-mm';
        $transverseReserveFieldId = $instanceId . 'transverse-reserve-percent';
        $includePilesFieldId = $instanceId . 'include-piles';
        $includeGrillageFieldId = $instanceId . 'include-grillage';
        $pileTypeFieldId = $instanceId . 'pile-type';
        $pilesCountFieldId = $instanceId . 'piles-count';
        $pileShaftDiameterFieldId = $instanceId . 'pile-shaft-diameter-m';
        $pileShaftHeightFieldId = $instanceId . 'pile-shaft-height-m';
        $includePileBaseFieldId = $instanceId . 'include-pile-base';
        $pileBaseDiameterFieldId = $instanceId . 'pile-base-diameter-m';
        $pileBaseHeightFieldId = $instanceId . 'pile-base-height-m';
        $includePileReinforcementFieldId = $instanceId . 'include-pile-reinforcement';
        $pileReinforcementBarsCountFieldId = $instanceId . 'pile-reinforcement-bars-count';
        $pileReinforcementDiameterFieldId = $instanceId . 'pile-reinforcement-diameter-mm';
        $pileReinforcementReserveFieldId = $instanceId . 'pile-reinforcement-reserve-percent';
        $rebarDiameterFieldId = $instanceId . 'rebar-diameter-mm';
        $rebarStepFieldId = $instanceId . 'rebar-step-mm';
        $rebarLayersFieldId = $instanceId . 'rebar-layers';
        $rebarReserveFieldId = $instanceId . 'rebar-reserve-percent';
        $formworkHeightFieldId = $instanceId . 'formwork-height-m';
        $formworkReserveFieldId = $instanceId . 'formwork-reserve-percent';
        $tileLengthFieldId = $instanceId . 'tile-length-cm';
        $tileWidthFieldId = $instanceId . 'tile-width-cm';
        $areaHintId = $instanceId . 'area-hint';
        $lengthHintId = $instanceId . 'length-hint';
        $widthHintId = $instanceId . 'width-hint';
        $heightHintId = $instanceId . 'height-hint';
        $areaModeNoticeId = $instanceId . 'area-mode-notice';
        $rebarDiameterHintId = $instanceId . 'rebar-diameter-hint';
        $rebarStepHintId = $instanceId . 'rebar-step-hint';
        $rebarLayersHintId = $instanceId . 'rebar-layers-hint';
        $rebarReserveHintId = $instanceId . 'rebar-reserve-hint';
        $formworkHeightHintId = $instanceId . 'formwork-height-hint';
        $formworkReserveHintId = $instanceId . 'formwork-reserve-hint';
        $tileLengthHintId = $instanceId . 'tile-length-hint';
        $tileWidthHintId = $instanceId . 'tile-width-hint';
        $rebarDiameterTooltipId = $instanceId . 'rebar-diameter-tooltip';
        $rebarStepTooltipId = $instanceId . 'rebar-step-tooltip';
        $rebarLayersTooltipId = $instanceId . 'rebar-layers-tooltip';
        $rebarReserveTooltipId = $instanceId . 'rebar-reserve-tooltip';
        $formworkHeightTooltipId = $instanceId . 'formwork-height-tooltip';
        $formworkReserveTooltipId = $instanceId . 'formwork-reserve-tooltip';
        $reinforcementModeLockTooltipId = $instanceId . 'reinforcement-mode-lock-tooltip';
        $formworkModeLockTooltipId = $instanceId . 'formwork-mode-lock-tooltip';
        $modeHintId = $instanceId . 'mode-hint';
        $grillageModeHintId = $instanceId . 'grillage-mode-hint';
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
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four" data-autofit-row>
                        <div class="brigmaster-estimator__field-group" data-field-group="slab-dimensions">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($lengthFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Длина (м)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: длина" aria-expanded="false" aria-controls="<?php echo esc_attr($lengthFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($lengthFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите длину в метрах.</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($lengthFieldId); ?>" type="number" name="length" min="0.01" step="0.01" value="8">
                                <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group" data-field-group="slab-dimensions">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($widthFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Ширина (м)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: ширина" aria-expanded="false" aria-controls="<?php echo esc_attr($widthFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($widthFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите ширину в метрах.</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($widthFieldId); ?>" type="number" name="width" min="0.01" step="0.01" value="6">
                                <div class="brigmaster-estimator__error" data-field-error="width" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="slab-area">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($areaFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Площадь (м²)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: площадь" aria-expanded="false" aria-controls="<?php echo esc_attr($areaFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($areaFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите площадь в м² (например, 48).</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($areaFieldId); ?>" type="number" name="area" min="0.01" step="0.01" value="48">
                                <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group" data-field-group="slab-height">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($heightFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Высота (м)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: высота" aria-expanded="false" aria-controls="<?php echo esc_attr($heightFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($heightFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите высоту в метрах.</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($heightFieldId); ?>" type="number" name="height" min="0.001" step="0.001" value="0.25">
                                <div class="brigmaster-estimator__error" data-field-error="height" aria-live="polite"></div>
                            </div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group">
                        <?php echo ConcreteMixtureFields::render($instanceId, 'base', 'Тип смеси', false, true); ?>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="reinforcement">
                        <input id="<?php echo esc_attr($includeReinforcementFieldId); ?>" type="checkbox" name="includeReinforcement" value="1">
                        <label for="<?php echo esc_attr($includeReinforcementFieldId); ?>" class="brigmaster-estimator__label-row">
                            <span>Учитывать арматуру</span>
                            <span class="brigmaster-estimator__tooltip-anchor brigmaster-estimator__tooltip-anchor--mode-lock">
                                <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger data-mode-lock-trigger aria-label="Подсказка: арматура недоступна в режиме по площади" aria-expanded="false" aria-controls="<?php echo esc_attr($reinforcementModeLockTooltipId); ?>">i</button>
                                <div id="<?php echo esc_attr($reinforcementModeLockTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Для расчета арматуры нужны длина и ширина. Переключитесь в режим расчета по длине и ширине.</div>
                            </span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includeReinforcement" aria-live="polite"></div>
                        <p class="brigmaster-estimator__hint">Доступно при режиме расчёта по длине и ширине.</p>
                    </div>

                    <details class="brigmaster-estimator__accordion" open data-toggle-target="reinforcement">
                        <summary class="brigmaster-estimator__accordion-summary">Армирование<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                        <div class="brigmaster-estimator__accordion-body">
                            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Диаметр арматуры (мм)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarDiameterTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($rebarDiameterTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Диаметр влияет на массу арматуры. Стандартно для частного дома 10–14 мм.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarDiameterFieldId); ?>" type="number" name="rebarDiameterMm" min="1" step="1" value="12">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarDiameterMm" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarStepFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Шаг арматуры (мм)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: шаг арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarStepTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($rebarStepTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Шаг сетки между стержнями, обычно 150–250 мм. Чем меньше шаг, тем выше расход.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarStepFieldId); ?>" type="number" name="rebarStepMm" min="50" step="10" value="200">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarStepMm" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarLayersFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Слои арматуры</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: слои арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarLayersTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($rebarLayersTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>1 слой для лёгких нагрузок, 2 слоя — стандарт для жилых домов.</div>
                                        </span>
                                    </label>
                                    <select id="<?php echo esc_attr($rebarLayersFieldId); ?>" name="rebarLayers">
                                        <option value="1">1 слой</option>
                                        <option value="2" selected>2 слоя</option>
                                    </select>
                                    <div class="brigmaster-estimator__error" data-field-error="rebarLayers" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Запас арматуры (%)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: запас арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarReserveTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($rebarReserveTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Рекомендуемый запас 5–15% на подрезку и нахлёсты.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarReserveFieldId); ?>" type="number" name="rebarReservePercent" min="1" step="1" value="10">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarReservePercent" aria-live="polite"></div>
                                </div>
                            </div>
                        </div>
                    </details>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="formwork">
                        <input id="<?php echo esc_attr($includeFormworkFieldId); ?>" type="checkbox" name="includeFormwork" value="1">
                        <label for="<?php echo esc_attr($includeFormworkFieldId); ?>" class="brigmaster-estimator__label-row">
                            <span>Учитывать опалубку</span>
                            <span class="brigmaster-estimator__tooltip-anchor brigmaster-estimator__tooltip-anchor--mode-lock">
                                <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger data-mode-lock-trigger aria-label="Подсказка: опалубка недоступна в режиме по площади" aria-expanded="false" aria-controls="<?php echo esc_attr($formworkModeLockTooltipId); ?>">i</button>
                                <div id="<?php echo esc_attr($formworkModeLockTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Для расчета опалубки нужны длина и ширина. Переключитесь в режим расчета по длине и ширине.</div>
                            </span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includeFormwork" aria-live="polite"></div>
                        <p class="brigmaster-estimator__hint">Доступно при режиме расчёта по длине и ширине.</p>
                    </div>

                    <details class="brigmaster-estimator__accordion" open data-toggle-target="formwork">
                        <summary class="brigmaster-estimator__accordion-summary">Опалубка<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                        <div class="brigmaster-estimator__accordion-body">
                            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two">
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($formworkHeightFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Высота опалубки (м)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: высота опалубки" aria-expanded="false" aria-controls="<?php echo esc_attr($formworkHeightTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($formworkHeightTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Опалубка считается по периметру плиты и указанной высоте. Обычно равна высоте заливки.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($formworkHeightFieldId); ?>" type="number" name="formworkHeightM" min="0.01" step="0.01" value="0.30">
                                    <div class="brigmaster-estimator__error" data-field-error="formworkHeightM" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($formworkReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Запас опалубки (%)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: запас опалубки" aria-expanded="false" aria-controls="<?php echo esc_attr($formworkReserveTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($formworkReserveTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Рекомендуется 5–15% на подрезку и стыковки.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($formworkReserveFieldId); ?>" type="number" name="formworkReservePercent" min="1" step="1" value="10">
                                    <div class="brigmaster-estimator__error" data-field-error="formworkReservePercent" aria-live="polite"></div>
                                </div>
                            </div>
                        </div>
                    </details>
                <?php endif; ?>

                <?php if ($calculator === 'pile_foundation') : ?>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-field-group="grillage-toggle">
                        <input id="<?php echo esc_attr($includeGrillageFieldId); ?>" type="checkbox" name="includeGrillage" value="1" checked>
                        <label for="<?php echo esc_attr($includeGrillageFieldId); ?>" class="brigmaster-estimator__label-row">
                            <span>Рассчитать ростверк</span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includeGrillage" aria-live="polite"></div>
                    </div>
                <?php endif; ?>

                <?php if (in_array($calculator, ['strip_foundation', 'pile_foundation'], true)) : ?>
                    <?php $stripLabel = $calculator === 'pile_foundation' ? 'ростверка' : 'ленты'; ?>

                    <?php if ($calculator === 'strip_foundation') : ?>
                    <!-- П.1: Геометрия ленты — flat, всегда видима (вне аккордеона) -->
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="strip-perimeter">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($totalLengthFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Общая длина ленты (м)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: общая длина" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'total-length-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'total-length-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Суммарная длина всех участков. От нее напрямую зависит общий объем бетона и расход материалов.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($totalLengthFieldId); ?>" type="number" name="totalLengthM" min="0.01" step="0.01" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="totalLengthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($widthMFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Ширина ленты (м)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: ширина" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'width-m-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'width-m-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Ширина поперечного сечения. При увеличении ширины объем бетона растет линейно.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($widthMFieldId); ?>" type="number" name="widthM" min="0.01" step="0.01" value="0.4">
                            <div class="brigmaster-estimator__error" data-field-error="widthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($heightMFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Высота ленты (м)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: высота" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'height-m-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'height-m-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Высота сечения. Вместе с шириной и длиной определяет объем бетона.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($heightMFieldId); ?>" type="number" name="heightM" min="0.01" step="0.01" value="1">
                            <div class="brigmaster-estimator__error" data-field-error="heightM" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group--hidden" data-field-group="strip-house">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($houseLengthFieldId); ?>">Длина дома (м)</label>
                            <input id="<?php echo esc_attr($houseLengthFieldId); ?>" type="number" name="houseLengthM" min="0.01" step="0.01" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="houseLengthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($houseWidthFieldId); ?>">Ширина дома (м)</label>
                            <input id="<?php echo esc_attr($houseWidthFieldId); ?>" type="number" name="houseWidthM" min="0.01" step="0.01" value="8">
                            <div class="brigmaster-estimator__error" data-field-error="houseWidthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($houseStripWidthFieldId); ?>">Ширина ленты (м)</label>
                            <input id="<?php echo esc_attr($houseStripWidthFieldId); ?>" type="number" name="houseModeWidthM" min="0.01" step="0.01" value="0.4" data-strip-house-width-input>
                            <div class="brigmaster-estimator__error" data-field-error="widthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($instanceId . 'house-height-m'); ?>">Высота ленты (м)</label>
                            <input id="<?php echo esc_attr($instanceId . 'house-height-m'); ?>" type="number" name="houseModeHeightM" min="0.01" step="0.01" value="1" data-strip-house-height-input>
                            <div class="brigmaster-estimator__error" data-field-error="heightM" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="strip-segments">
                        <div class="brigmaster-estimator__segment-list" data-strip-segments-list>
                            <article class="brigmaster-estimator__segment-card" data-strip-segment-item data-segment-index="0">
                                <div class="brigmaster-estimator__segment-head">
                                    <h3 class="brigmaster-estimator__segment-title">Участок 1</h3>
                                    <button type="button" class="brigmaster-estimator__segment-remove" data-strip-remove-segment disabled>Удалить</button>
                                </div>
                                <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three">
                                    <div class="brigmaster-estimator__field">
                                        <label for="<?php echo esc_attr($instanceId . 'segment-0-length'); ?>">Длина участка ленты (м)</label>
                                        <input id="<?php echo esc_attr($instanceId . 'segment-0-length'); ?>" type="number" min="0.01" step="0.01" value="10" data-segment-input="segmentLengthM">
                                        <div class="brigmaster-estimator__error" data-segment-error-field="segmentLengthM" data-field-error="segments.0.segmentLengthM" aria-live="polite"></div>
                                    </div>
                                    <div class="brigmaster-estimator__field">
                                        <label for="<?php echo esc_attr($instanceId . 'segment-0-width'); ?>">Ширина участка ленты (м)</label>
                                        <input id="<?php echo esc_attr($instanceId . 'segment-0-width'); ?>" type="number" min="0.01" step="0.01" value="0.4" data-segment-input="segmentWidthM">
                                        <div class="brigmaster-estimator__error" data-segment-error-field="segmentWidthM" data-field-error="segments.0.segmentWidthM" aria-live="polite"></div>
                                    </div>
                                    <div class="brigmaster-estimator__field">
                                        <label for="<?php echo esc_attr($instanceId . 'segment-0-height'); ?>">Высота участка ленты (м)</label>
                                        <input id="<?php echo esc_attr($instanceId . 'segment-0-height'); ?>" type="number" min="0.01" step="0.01" value="1" data-segment-input="segmentHeightM">
                                        <div class="brigmaster-estimator__error" data-segment-error-field="segmentHeightM" data-field-error="segments.0.segmentHeightM" aria-live="polite"></div>
                                    </div>
                                </div>
                                <div class="brigmaster-estimator__segment-section" data-segment-rebar-root>
                                    <div class="brigmaster-estimator__segment-toggles">
                                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-include-rebar'); ?>" type="checkbox" data-segment-include-reinforcement data-checkbox-key="segment-include-rebar">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-include-rebar'); ?>" class="brigmaster-estimator__label-row" data-label-for-checkbox="segment-include-rebar">
                                                <span>Учитывать арматуру для этого участка</span>
                                            </label>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentIncludeReinforcement" data-field-error="segments.0.segmentIncludeReinforcement" aria-live="polite"></div>
                                        </div>
                                    </div>
                                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-segment-rebar-local>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-bars-count'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Кол-во продольных стержней</span>
                                                <span class="brigmaster-estimator__tooltip-anchor">
                                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: количество продольных стержней" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'segment-0-seg-long-bars-tooltip'); ?>">i</button>
                                                    <div id="<?php echo esc_attr($instanceId . 'segment-0-seg-long-bars-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                                        Число рабочих стержней в сечении этого участка. Обычно 4–6. Больше стержней — выше расход арматуры.
                                                    </div>
                                                </span>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-bars-count'); ?>" type="number" min="1" step="1" value="4" data-segment-input="segmentLongitudinalBarsCount">
                                            <p class="brigmaster-estimator__hint">Обычно 4-6 стержней для частного дома.</p>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentLongitudinalBarsCount" data-field-error="segments.0.segmentLongitudinalBarsCount" aria-live="polite"></div>
                                        </div>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-diameter'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Диаметр продольной (мм)</span>
                                                <span class="brigmaster-estimator__tooltip-anchor">
                                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр продольной арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'segment-0-seg-long-diameter-tooltip'); ?>">i</button>
                                                    <div id="<?php echo esc_attr($instanceId . 'segment-0-seg-long-diameter-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                                        Диаметр рабочих стержней в мм. Типично 10–14 мм. Чем больше диаметр, тем выше масса и прочность.
                                                    </div>
                                                </span>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-diameter'); ?>" type="number" min="1" step="1" value="12" data-segment-input="segmentLongitudinalDiameterMm">
                                            <p class="brigmaster-estimator__hint">Чаще всего 10-14 мм.</p>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentLongitudinalDiameterMm" data-field-error="segments.0.segmentLongitudinalDiameterMm" aria-live="polite"></div>
                                        </div>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-transverse-diameter'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Диаметр поперечной (мм)</span>
                                                <span class="brigmaster-estimator__tooltip-anchor">
                                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр поперечной арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'segment-0-seg-transverse-diameter-tooltip'); ?>">i</button>
                                                    <div id="<?php echo esc_attr($instanceId . 'segment-0-seg-transverse-diameter-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                                        Диаметр хомутов в мм. Обычно 6–10 мм. Влияет на массу поперечной арматуры.
                                                    </div>
                                                </span>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-transverse-diameter'); ?>" type="number" min="1" step="1" value="8" data-segment-input="segmentTransverseDiameterMm">
                                            <p class="brigmaster-estimator__hint">Обычно 6-10 мм для хомутов.</p>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentTransverseDiameterMm" data-field-error="segments.0.segmentTransverseDiameterMm" aria-live="polite"></div>
                                        </div>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-transverse-step'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Шаг поперечной (мм)</span>
                                                <span class="brigmaster-estimator__tooltip-anchor">
                                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: шаг поперечной арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'segment-0-seg-transverse-step-tooltip'); ?>">i</button>
                                                    <div id="<?php echo esc_attr($instanceId . 'segment-0-seg-transverse-step-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                                        Расстояние между хомутами в мм. Типично 200–400 мм. Меньший шаг — больше хомутов и расход стали.
                                                    </div>
                                                </span>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-transverse-step'); ?>" type="number" min="10" step="10" value="300" data-segment-input="segmentTransverseStepMm">
                                            <p class="brigmaster-estimator__hint">Меньше шаг = больше хомутов и расход стали.</p>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentTransverseStepMm" data-field-error="segments.0.segmentTransverseStepMm" aria-live="polite"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="brigmaster-estimator__segment-section" data-segment-formwork-root>
                                    <div class="brigmaster-estimator__segment-toggles">
                                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-include-formwork'); ?>" type="checkbox" data-segment-include-formwork data-checkbox-key="segment-include-formwork">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-include-formwork'); ?>" class="brigmaster-estimator__label-row" data-label-for-checkbox="segment-include-formwork">
                                                <span>Учитывать опалубку для этого участка</span>
                                            </label>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentIncludeFormwork" data-field-error="segments.0.segmentIncludeFormwork" aria-live="polite"></div>
                                        </div>
                                    </div>
                                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-segment-formwork-local>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-formwork-height'); ?>">Высота опалубки участка (м)</label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-formwork-height'); ?>" type="number" min="0.01" step="0.01" value="0.8" data-segment-input="segmentFormworkHeightM">
                                            <p class="brigmaster-estimator__hint">Считаются только боковые щиты участка.</p>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentFormworkHeightM" data-field-error="segments.0.segmentFormworkHeightM" aria-live="polite"></div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <button type="button" class="brigmaster-estimator__segment-add" data-strip-add-segment>
                            Добавить участок ленты
                        </button>
                        <div class="brigmaster-estimator__error" data-field-error="segments" aria-live="polite"></div>
                    </div>

                    <!-- П.4: Смесь сразу после геометрии ленты -->
                    <div class="brigmaster-estimator__field-group">
                        <?php echo ConcreteMixtureFields::render($instanceId, 'base', 'Тип смеси', false, true); ?>
                    </div>
                    <?php endif; /* strip_foundation geometry + mixture */ ?>

                    <?php if ($calculator === 'pile_foundation') : ?>
                    <?php $stripLabel = $calculator === 'pile_foundation' ? 'ростверка' : 'ленты'; ?>
                        <details class="brigmaster-estimator__accordion" open<?php echo $calculator === 'pile_foundation' ? ' data-pile-panel="grillage"' : ''; ?>>
                            <summary class="brigmaster-estimator__accordion-summary"><?php echo $calculator === 'pile_foundation' ? 'Геометрия ростверка' : 'Геометрия ленты'; ?><?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">
                    <?php if ($calculator === 'pile_foundation') : ?>
                        <div class="brigmaster-estimator__field" data-field-group="estimator-mode">
                            <label for="<?php echo esc_attr($modeFieldId); ?>">Режим расчета ростверка</label>
                            <select id="<?php echo esc_attr($modeFieldId); ?>" name="mode" required aria-describedby="<?php echo esc_attr($grillageModeHintId); ?>">
                                <option value="perimeter">По общей длине ростверка</option>
                                <option value="house">По параметрам дома</option>
                                <option value="segments">По участкам ростверка</option>
                            </select>
                            <p id="<?php echo esc_attr($grillageModeHintId); ?>" class="brigmaster-estimator__mode-hint" data-mode-hint aria-live="polite"></p>
                            <div class="brigmaster-estimator__error" data-field-error="mode" aria-live="polite"></div>
                        </div>
                    <?php endif; ?>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="strip-perimeter">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($totalLengthFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Общая длина <?php echo esc_html($stripLabel); ?> (м)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: общая длина" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'total-length-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'total-length-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Суммарная длина всех участков. От нее напрямую зависит общий объем бетона и расход материалов.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($totalLengthFieldId); ?>" type="number" name="totalLengthM" min="0.01" step="0.01" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="totalLengthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($widthMFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Ширина <?php echo esc_html($stripLabel); ?> (м)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: ширина" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'width-m-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'width-m-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Ширина поперечного сечения. При увеличении ширины объем бетона растет линейно.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($widthMFieldId); ?>" type="number" name="widthM" min="0.01" step="0.01" value="0.4">
                            <div class="brigmaster-estimator__error" data-field-error="widthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($heightMFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Высота <?php echo esc_html($stripLabel); ?> (м)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: высота" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'height-m-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'height-m-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Высота сечения. Вместе с шириной и длиной определяет объем бетона.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($heightMFieldId); ?>" type="number" name="heightM" min="0.01" step="0.01" value="1">
                            <div class="brigmaster-estimator__error" data-field-error="heightM" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group--hidden" data-field-group="strip-house">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($houseLengthFieldId); ?>">Длина дома (м)</label>
                            <input id="<?php echo esc_attr($houseLengthFieldId); ?>" type="number" name="houseLengthM" min="0.01" step="0.01" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="houseLengthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($houseWidthFieldId); ?>">Ширина дома (м)</label>
                            <input id="<?php echo esc_attr($houseWidthFieldId); ?>" type="number" name="houseWidthM" min="0.01" step="0.01" value="8">
                            <div class="brigmaster-estimator__error" data-field-error="houseWidthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($houseStripWidthFieldId); ?>">Ширина <?php echo esc_html($stripLabel); ?> (м)</label>
                            <input id="<?php echo esc_attr($houseStripWidthFieldId); ?>" type="number" name="houseModeWidthM" min="0.01" step="0.01" value="0.4" data-strip-house-width-input>
                            <div class="brigmaster-estimator__error" data-field-error="widthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($instanceId . 'house-height-m'); ?>">Высота <?php echo esc_html($stripLabel); ?> (м)</label>
                            <input id="<?php echo esc_attr($instanceId . 'house-height-m'); ?>" type="number" name="houseModeHeightM" min="0.01" step="0.01" value="1" data-strip-house-height-input>
                            <div class="brigmaster-estimator__error" data-field-error="heightM" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="strip-segments">
                        <div class="brigmaster-estimator__segment-list" data-strip-segments-list>
                            <article class="brigmaster-estimator__segment-card" data-strip-segment-item data-segment-index="0">
                                <div class="brigmaster-estimator__segment-head">
                                    <h3 class="brigmaster-estimator__segment-title">Участок 1</h3>
                                    <button type="button" class="brigmaster-estimator__segment-remove" data-strip-remove-segment disabled>Удалить</button>
                                </div>
                                <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three">
                                    <div class="brigmaster-estimator__field">
                                        <label for="<?php echo esc_attr($instanceId . 'segment-0-length'); ?>">Длина участка <?php echo esc_html($stripLabel); ?> (м)</label>
                                        <input id="<?php echo esc_attr($instanceId . 'segment-0-length'); ?>" type="number" min="0.01" step="0.01" value="10" data-segment-input="segmentLengthM">
                                        <div class="brigmaster-estimator__error" data-segment-error-field="segmentLengthM" data-field-error="segments.0.segmentLengthM" aria-live="polite"></div>
                                    </div>
                                    <div class="brigmaster-estimator__field">
                                        <label for="<?php echo esc_attr($instanceId . 'segment-0-width'); ?>">Ширина участка <?php echo esc_html($stripLabel); ?> (м)</label>
                                        <input id="<?php echo esc_attr($instanceId . 'segment-0-width'); ?>" type="number" min="0.01" step="0.01" value="0.4" data-segment-input="segmentWidthM">
                                        <div class="brigmaster-estimator__error" data-segment-error-field="segmentWidthM" data-field-error="segments.0.segmentWidthM" aria-live="polite"></div>
                                    </div>
                                    <div class="brigmaster-estimator__field">
                                        <label for="<?php echo esc_attr($instanceId . 'segment-0-height'); ?>">Высота участка <?php echo esc_html($stripLabel); ?> (м)</label>
                                        <input id="<?php echo esc_attr($instanceId . 'segment-0-height'); ?>" type="number" min="0.01" step="0.01" value="1" data-segment-input="segmentHeightM">
                                        <div class="brigmaster-estimator__error" data-segment-error-field="segmentHeightM" data-field-error="segments.0.segmentHeightM" aria-live="polite"></div>
                                    </div>
                                </div>
                                <div class="brigmaster-estimator__segment-section" data-segment-rebar-root>
                                    <div class="brigmaster-estimator__segment-toggles">
                                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-include-rebar'); ?>" type="checkbox" data-segment-include-reinforcement data-checkbox-key="segment-include-rebar">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-include-rebar'); ?>" class="brigmaster-estimator__label-row" data-label-for-checkbox="segment-include-rebar">
                                                <span>Учитывать арматуру для этого участка</span>
                                            </label>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentIncludeReinforcement" data-field-error="segments.0.segmentIncludeReinforcement" aria-live="polite"></div>
                                        </div>
                                    </div>
                                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-segment-rebar-local>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-bars-count'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Кол-во продольных стержней</span>
                                                <span class="brigmaster-estimator__tooltip-anchor">
                                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: количество продольных стержней" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'segment-0-seg-long-bars-tooltip'); ?>">i</button>
                                                    <div id="<?php echo esc_attr($instanceId . 'segment-0-seg-long-bars-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                                        Число рабочих стержней в сечении этого участка. Обычно 4–6. Больше стержней — выше расход арматуры.
                                                    </div>
                                                </span>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-bars-count'); ?>" type="number" min="1" step="1" value="4" data-segment-input="segmentLongitudinalBarsCount">
                                            <p class="brigmaster-estimator__hint">Обычно 4-6 стержней для частного дома.</p>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentLongitudinalBarsCount" data-field-error="segments.0.segmentLongitudinalBarsCount" aria-live="polite"></div>
                                        </div>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-diameter'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Диаметр продольной (мм)</span>
                                                <span class="brigmaster-estimator__tooltip-anchor">
                                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр продольной арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'segment-0-seg-long-diameter-tooltip'); ?>">i</button>
                                                    <div id="<?php echo esc_attr($instanceId . 'segment-0-seg-long-diameter-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                                        Диаметр рабочих стержней в мм. Типично 10–14 мм. Чем больше диаметр, тем выше масса и прочность.
                                                    </div>
                                                </span>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-longitudinal-diameter'); ?>" type="number" min="1" step="1" value="12" data-segment-input="segmentLongitudinalDiameterMm">
                                            <p class="brigmaster-estimator__hint">Чаще всего 10-14 мм.</p>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentLongitudinalDiameterMm" data-field-error="segments.0.segmentLongitudinalDiameterMm" aria-live="polite"></div>
                                        </div>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-transverse-diameter'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Диаметр поперечной (мм)</span>
                                                <span class="brigmaster-estimator__tooltip-anchor">
                                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр поперечной арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'segment-0-seg-transverse-diameter-tooltip'); ?>">i</button>
                                                    <div id="<?php echo esc_attr($instanceId . 'segment-0-seg-transverse-diameter-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                                        Диаметр хомутов в мм. Обычно 6–10 мм. Влияет на массу поперечной арматуры.
                                                    </div>
                                                </span>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-transverse-diameter'); ?>" type="number" min="1" step="1" value="8" data-segment-input="segmentTransverseDiameterMm">
                                            <p class="brigmaster-estimator__hint">Обычно 6-10 мм для хомутов.</p>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentTransverseDiameterMm" data-field-error="segments.0.segmentTransverseDiameterMm" aria-live="polite"></div>
                                        </div>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-transverse-step'); ?>" class="brigmaster-estimator__label-row">
                                                <span>Шаг поперечной (мм)</span>
                                                <span class="brigmaster-estimator__tooltip-anchor">
                                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: шаг поперечной арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'segment-0-seg-transverse-step-tooltip'); ?>">i</button>
                                                    <div id="<?php echo esc_attr($instanceId . 'segment-0-seg-transverse-step-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                                        Расстояние между хомутами в мм. Типично 200–400 мм. Меньший шаг — больше хомутов и расход стали.
                                                    </div>
                                                </span>
                                            </label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-transverse-step'); ?>" type="number" min="10" step="10" value="300" data-segment-input="segmentTransverseStepMm">
                                            <p class="brigmaster-estimator__hint">Меньше шаг = больше хомутов и расход стали.</p>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentTransverseStepMm" data-field-error="segments.0.segmentTransverseStepMm" aria-live="polite"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="brigmaster-estimator__segment-section" data-segment-formwork-root>
                                    <div class="brigmaster-estimator__segment-toggles">
                                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-include-formwork'); ?>" type="checkbox" data-segment-include-formwork data-checkbox-key="segment-include-formwork">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-include-formwork'); ?>" class="brigmaster-estimator__label-row" data-label-for-checkbox="segment-include-formwork">
                                                <span>Учитывать опалубку для этого участка</span>
                                            </label>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentIncludeFormwork" data-field-error="segments.0.segmentIncludeFormwork" aria-live="polite"></div>
                                        </div>
                                    </div>
                                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-segment-formwork-local>
                                        <div class="brigmaster-estimator__field">
                                            <label for="<?php echo esc_attr($instanceId . 'segment-0-formwork-height'); ?>">Высота опалубки участка (м)</label>
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-formwork-height'); ?>" type="number" min="0.01" step="0.01" value="0.8" data-segment-input="segmentFormworkHeightM">
                                            <p class="brigmaster-estimator__hint">Считаются только боковые щиты участка.</p>
                                            <div class="brigmaster-estimator__error" data-segment-error-field="segmentFormworkHeightM" data-field-error="segments.0.segmentFormworkHeightM" aria-live="polite"></div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <button type="button" class="brigmaster-estimator__segment-add" data-strip-add-segment>
                            <?php echo $calculator === 'pile_foundation' ? 'Добавить участок ростверка' : 'Добавить участок ленты'; ?>
                        </button>
                        <div class="brigmaster-estimator__error" data-field-error="segments" aria-live="polite"></div>
                    </div>
                    <?php endif; /* pile_foundation geometry accordion: продолжается, закрывается после опалубки */ ?>
                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="strip-reinforcement">
                            <input id="<?php echo esc_attr($instanceId . 'strip-include-reinforcement'); ?>" type="checkbox" name="includeReinforcement" value="1">
                            <label for="<?php echo esc_attr($instanceId . 'strip-include-reinforcement'); ?>" class="brigmaster-estimator__label-row">
                                <span><?php echo $calculator === 'pile_foundation' ? 'Учитывать арматуру ростверка' : 'Учитывать арматуру'; ?></span>
                            </label>
                        </div>
                        <?php if ($calculator !== 'pile_foundation') : ?>
                        <details class="brigmaster-estimator__accordion" open data-toggle-target="strip-reinforcement">
                            <summary class="brigmaster-estimator__accordion-summary">Арматура<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">
                        <?php endif; ?>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="strip-reinforcement-global">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($longitudinalBarsCountFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Кол-во продольных стержней</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: количество продольных стержней" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'strip-long-bars-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'strip-long-bars-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Число рабочих стержней в поперечном сечении ленты. Для частного дома обычно 4–6. Больше стержней — выше расход арматуры.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($longitudinalBarsCountFieldId); ?>" type="number" name="longitudinalBarsCount" min="1" step="1" value="4">
                            <div class="brigmaster-estimator__error" data-field-error="longitudinalBarsCount" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($longitudinalDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Диаметр продольной (мм)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр продольной арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'strip-long-diameter-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'strip-long-diameter-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Диаметр рабочих стержней в мм. Типично 10–14 мм. Чем больше диаметр, тем выше масса и прочность.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($longitudinalDiameterFieldId); ?>" type="number" name="longitudinalDiameterMm" min="1" step="1" value="12">
                            <div class="brigmaster-estimator__error" data-field-error="longitudinalDiameterMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($longitudinalReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Запас продольной (%)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: запас продольной арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'strip-long-reserve-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'strip-long-reserve-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Процент запаса на нахлёсты и подрезку. Рекомендуется 5–15%.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($longitudinalReserveFieldId); ?>" type="number" name="longitudinalReservePercent" min="1" step="1" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="longitudinalReservePercent" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($transverseDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Диаметр поперечной (мм)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр поперечной арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'strip-transverse-diameter-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'strip-transverse-diameter-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Диаметр хомутов в мм. Обычно 6–10 мм. Влияет на массу поперечной арматуры.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($transverseDiameterFieldId); ?>" type="number" name="transverseDiameterMm" min="1" step="1" value="8">
                            <div class="brigmaster-estimator__error" data-field-error="transverseDiameterMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($transverseStepFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Шаг поперечной (мм)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: шаг поперечной арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'strip-transverse-step-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'strip-transverse-step-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Расстояние между хомутами в мм. Типично 200–400 мм. Меньший шаг — больше хомутов и расход стали.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($transverseStepFieldId); ?>" type="number" name="transverseStepMm" min="10" step="10" value="300">
                            <div class="brigmaster-estimator__error" data-field-error="transverseStepMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($transverseReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Запас поперечной (%)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: запас поперечной арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'strip-transverse-reserve-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'strip-transverse-reserve-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Процент запаса на монтаж и отходы. Рекомендуется 5–15%.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($transverseReserveFieldId); ?>" type="number" name="transverseReservePercent" min="1" step="1" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="transverseReservePercent" aria-live="polite"></div>
                        </div>
                    </div>
                        <?php if ($calculator !== 'pile_foundation') : ?>
                            </div>
                        </details>
                        <?php endif; ?>
                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="strip-formwork">
                            <input id="<?php echo esc_attr($instanceId . 'strip-include-formwork'); ?>" type="checkbox" name="includeFormwork" value="1">
                            <label for="<?php echo esc_attr($instanceId . 'strip-include-formwork'); ?>" class="brigmaster-estimator__label-row">
                                <span><?php echo $calculator === 'pile_foundation' ? 'Учитывать опалубку ростверка' : 'Учитывать опалубку'; ?></span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: учитывать опалубку" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'strip-formwork-height-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'strip-formwork-height-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        В расчёт попадают только боковые щиты ленты (высота задаётся ниже). Распорки, подкосы и крепёж не учитываются.
                                    </div>
                                </span>
                            </label>
                        </div>
                        <?php if ($calculator !== 'pile_foundation') : ?>
                        <details class="brigmaster-estimator__accordion" open data-toggle-target="strip-formwork">
                            <summary class="brigmaster-estimator__accordion-summary">Опалубка<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">
                        <?php endif; ?>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="strip-formwork-global">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($formworkHeightFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Высота опалубки (м)</span>
                            </label>
                            <input id="<?php echo esc_attr($formworkHeightFieldId); ?>" type="number" name="formworkHeightM" min="0.01" step="0.01" value="0.8">
                            <div class="brigmaster-estimator__error" data-field-error="formworkHeightM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($formworkReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Запас опалубки (%)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: запас опалубки ленты" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'strip-formwork-reserve-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'strip-formwork-reserve-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Запас учитывает подрезку и стыки щитов. В расчет входят только боковые поверхности.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($formworkReserveFieldId); ?>" type="number" name="formworkReservePercent" min="1" step="1" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="formworkReservePercent" aria-live="polite"></div>
                        </div>
                    </div>
                        <?php if ($calculator !== 'pile_foundation') : ?>
                            </div>
                        </details>
                        <?php endif; ?>
                    <?php if ($calculator === 'pile_foundation') : ?>
                            </div>
                        </details>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($calculator === 'pile_foundation') : ?>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-field-group="piles-toggle">
                        <input id="<?php echo esc_attr($includePilesFieldId); ?>" type="checkbox" name="includePiles" value="1" checked>
                        <label for="<?php echo esc_attr($includePilesFieldId); ?>" class="brigmaster-estimator__label-row">
                            <span>Рассчитать сваи</span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includePiles" aria-live="polite"></div>
                    </div>
<?php
// Схема геометрии буронабивной сваи (используется в тултипах полей геометрии).
$bmPileDiagramSvg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Схема: геометрия буронабивной сваи с уширением — Ø ствола, Ø уширения, высота H, шаг" viewBox="0 0 320 220" class="bm-diagram-demo__svg">
  <defs>
    <marker id="bm-arr" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto" markerUnits="strokeWidth"><path d="M0,0.5 L6,3 L0,5.5 Z" fill="#64748B"/></marker>
    <marker id="bm-arr-rev" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto-start-reverse" markerUnits="strokeWidth"><path d="M0,0.5 L6,3 L0,5.5 Z" fill="#64748B"/></marker>
  </defs>
  <g aria-hidden="true">
    <rect x="195" y="28" width="60" height="134" fill="none" stroke="#93C5FD" stroke-width="1.2" stroke-dasharray="5,3"/>
    <polygon points="195,162 171,190 279,190 255,162" fill="none" stroke="#93C5FD" stroke-width="1.2" stroke-dasharray="5,3"/>
    <line x1="225" y1="20" x2="225" y2="198" stroke="#93C5FD" stroke-width="0.8" stroke-dasharray="3,2"/>
  </g>
  <g>
    <rect x="70" y="28" width="60" height="134" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.6"/>
    <polygon points="70,162 46,190 154,190 130,162" fill="#EFF6FF" stroke="#2563EB" stroke-width="1.6"/>
    <line x1="46" y1="190" x2="154" y2="190" stroke="#2563EB" stroke-width="1.6"/>
  </g>
  <g aria-hidden="true">
    <line x1="0" y1="55" x2="320" y2="55" stroke="#94A3B8" stroke-width="1" stroke-dasharray="6,4"/>
    <line x1="4" y1="55" x2="-2" y2="62" stroke="#CBD5E1" stroke-width="0.7"/>
    <line x1="24" y1="55" x2="18" y2="62" stroke="#CBD5E1" stroke-width="0.7"/>
    <line x1="44" y1="55" x2="38" y2="62" stroke="#CBD5E1" stroke-width="0.7"/>
    <line x1="64" y1="55" x2="58" y2="62" stroke="#CBD5E1" stroke-width="0.7"/>
    <line x1="84" y1="55" x2="78" y2="62" stroke="#CBD5E1" stroke-width="0.7"/>
    <line x1="104" y1="55" x2="98" y2="62" stroke="#CBD5E1" stroke-width="0.7"/>
    <line x1="124" y1="55" x2="118" y2="62" stroke="#CBD5E1" stroke-width="0.7"/>
    <line x1="144" y1="55" x2="138" y2="62" stroke="#CBD5E1" stroke-width="0.7"/>
    <text x="4" y="51" font-size="9" fill="#94A3B8" font-family="sans-serif">Ур. З.</text>
  </g>
  <line aria-hidden="true" x1="100" y1="20" x2="100" y2="198" stroke="#CBD5E1" stroke-width="0.8" stroke-dasharray="4,3"/>
  <g aria-hidden="true">
    <line x1="70" y1="33" x2="70" y2="43" stroke="#64748B" stroke-width="0.8"/>
    <line x1="130" y1="33" x2="130" y2="43" stroke="#64748B" stroke-width="0.8"/>
    <line x1="70" y1="38" x2="130" y2="38" stroke="#64748B" stroke-width="0.8" marker-start="url(#bm-arr-rev)" marker-end="url(#bm-arr)"/>
    <text x="100" y="30" text-anchor="middle" font-size="10" font-weight="600" fill="#0EA5E9" font-family="sans-serif">Ø ствола</text>
  </g>
  <g aria-hidden="true">
    <line x1="46" y1="199" x2="46" y2="209" stroke="#64748B" stroke-width="0.8"/>
    <line x1="154" y1="199" x2="154" y2="209" stroke="#64748B" stroke-width="0.8"/>
    <line x1="46" y1="204" x2="154" y2="204" stroke="#64748B" stroke-width="0.8" marker-start="url(#bm-arr-rev)" marker-end="url(#bm-arr)"/>
    <text x="100" y="215" text-anchor="middle" font-size="10" font-weight="600" fill="#0EA5E9" font-family="sans-serif">Ø уширения</text>
  </g>
  <g aria-hidden="true">
    <line x1="168" y1="28" x2="176" y2="28" stroke="#64748B" stroke-width="0.8"/>
    <line x1="168" y1="190" x2="176" y2="190" stroke="#64748B" stroke-width="0.8"/>
    <line x1="172" y1="28" x2="172" y2="190" stroke="#64748B" stroke-width="0.8" marker-start="url(#bm-arr-rev)" marker-end="url(#bm-arr)"/>
    <line x1="172" y1="109" x2="180" y2="109" stroke="#64748B" stroke-width="0.6"/>
    <text x="182" y="113" font-size="10" font-weight="600" fill="#0EA5E9" font-family="sans-serif">H (высота)</text>
  </g>
  <g aria-hidden="true">
    <line x1="100" y1="11" x2="100" y2="21" stroke="#64748B" stroke-width="0.8"/>
    <line x1="225" y1="11" x2="225" y2="21" stroke="#64748B" stroke-width="0.8"/>
    <line x1="100" y1="16" x2="225" y2="16" stroke="#64748B" stroke-width="0.8" marker-start="url(#bm-arr-rev)" marker-end="url(#bm-arr)"/>
    <text x="162" y="12" text-anchor="middle" font-size="10" font-weight="600" fill="#0EA5E9" font-family="sans-serif">Шаг</text>
  </g>
</svg>
SVG;
?>
                        <details class="brigmaster-estimator__accordion" open data-pile-panel="piles">
                            <summary class="brigmaster-estimator__accordion-summary">Сваи<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--pile-primary" data-field-group="pile-primary-row" data-pile-primary-grid data-autofit-row>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($pileTypeFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Тип свай</span>
                                        <span class="bm-tooltip-anchor">
                                            <button type="button" class="bm-tooltip-trigger" data-bm-tooltip aria-label="Подсказка: тип свай" aria-expanded="false">i</button>
                                            <template class="bm-tooltip-tpl">
                                                <p class="bm-tooltip__text">Для винтовых и забивных свай бетон не требуется. Для буронабивных выполняется расчёт бетона.</p>
                                            </template>
                                        </span>
                                    </label>
                                    <select id="<?php echo esc_attr($pileTypeFieldId); ?>" name="pileType">
                                        <option value="bored">Буронабивные</option>
                                        <option value="screw">Винтовые</option>
                                        <option value="driven">Забивные</option>
                                    </select>
                                    <div class="brigmaster-estimator__error" data-field-error="pileType" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field brigmaster-estimator__field--pile-count">
                                    <label for="<?php echo esc_attr($pilesCountFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Количество свай</span>
                                        <span class="bm-tooltip-anchor">
                                            <button type="button" class="bm-tooltip-trigger" data-bm-tooltip aria-label="Подсказка: количество свай" aria-expanded="false">i</button>
                                            <template class="bm-tooltip-tpl">
                                                <p class="bm-tooltip__text">Количество свай определяют по нагрузке здания и несущей способности одной сваи по геологии участка. Для точного подбора используйте проект.</p>
                                            </template>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($pilesCountFieldId); ?>" type="number" name="pilesCount" min="1" step="1" value="20" inputmode="numeric">
                                    <div class="brigmaster-estimator__error" data-field-error="pilesCount" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field" data-pile-primary-cell="shaft-diameter">
                                    <label for="<?php echo esc_attr($pileShaftDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Диаметр ствола сваи (м)</span>
                                        <span class="bm-tooltip-anchor">
                                            <button type="button" class="bm-tooltip-trigger" data-bm-tooltip aria-label="Подсказка: диаметр ствола сваи" aria-expanded="false">i</button>
                                            <template class="bm-tooltip-tpl">
                                                <h4 class="bm-tooltip__title">Схема размеров сваи</h4>
                                                <div class="bm-tooltip__media"><?php echo $bmPileDiagramSvg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG ?></div>
                                                <p class="bm-tooltip__text">Толщина сваи по всей длине. Для частного дома обычно 0,2–0,4 м; чем тяжелее дом и слабее грунт, тем больше диаметр.</p>
                                            </template>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($pileShaftDiameterFieldId); ?>" type="number" name="pileShaftDiameterM" min="0.01" step="0.01" value="0.3">
                                    <div class="brigmaster-estimator__error" data-field-error="pileShaftDiameterM" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field" data-pile-primary-cell="shaft-height">
                                    <label for="<?php echo esc_attr($pileShaftHeightFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Высота ствола сваи (м)</span>
                                        <span class="bm-tooltip-anchor">
                                            <button type="button" class="bm-tooltip-trigger" data-bm-tooltip aria-label="Подсказка: высота ствола сваи" aria-expanded="false">i</button>
                                            <template class="bm-tooltip-tpl">
                                                <h4 class="bm-tooltip__title">Схема размеров сваи</h4>
                                                <div class="bm-tooltip__media"><?php echo $bmPileDiagramSvg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG ?></div>
                                                <p class="bm-tooltip__text">Длина сваи от верха до пяты. Подбирается так, чтобы опереться на плотный грунт ниже глубины промерзания; для частного дома чаще 2–4 м.</p>
                                            </template>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($pileShaftHeightFieldId); ?>" type="number" name="pileShaftHeightM" min="0.01" step="0.01" value="2">
                                    <div class="brigmaster-estimator__error" data-field-error="pileShaftHeightM" aria-live="polite"></div>
                                </div>
                        </div>

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-field-group="pile-base-toggle">
                                <input id="<?php echo esc_attr($includePileBaseFieldId); ?>" type="checkbox" name="includePileBase" value="1">
                                <label for="<?php echo esc_attr($includePileBaseFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Учитывать уширение сваи (пяту)</span>
                                </label>
                                <div class="brigmaster-estimator__error" data-field-error="includePileBase" aria-live="polite"></div>
                        </div>

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="pile-base-fields">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileBaseDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Диаметр уширения (м)</span>
                                    <span class="bm-tooltip-anchor">
                                        <button type="button" class="bm-tooltip-trigger" data-bm-tooltip aria-label="Подсказка: диаметр уширения" aria-expanded="false">i</button>
                                        <template class="bm-tooltip-tpl">
                                            <h4 class="bm-tooltip__title">Схема размеров сваи</h4>
                                            <div class="bm-tooltip__media"><?php echo $bmPileDiagramSvg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG ?></div>
                                            <p class="bm-tooltip__text">Размер расширенной «пяты» внизу буронабивной сваи. Увеличивает опору на грунт; обычно в 1,5–2 раза больше диаметра ствола.</p>
                                        </template>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($pileBaseDiameterFieldId); ?>" type="number" name="pileBaseDiameterM" min="0.01" step="0.01" value="0.5">
                                <div class="brigmaster-estimator__error" data-field-error="pileBaseDiameterM" aria-live="polite"></div>
                            </div>
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileBaseHeightFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Высота уширения (м)</span>
                                    <span class="bm-tooltip-anchor">
                                        <button type="button" class="bm-tooltip-trigger" data-bm-tooltip aria-label="Подсказка: высота уширения" aria-expanded="false">i</button>
                                        <template class="bm-tooltip-tpl">
                                            <h4 class="bm-tooltip__title">Схема размеров сваи</h4>
                                            <div class="bm-tooltip__media"><?php echo $bmPileDiagramSvg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG ?></div>
                                            <p class="bm-tooltip__text">Высота самой расширенной части (пяты) у основания сваи. Обычно 0,2–0,4 м.</p>
                                        </template>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($pileBaseHeightFieldId); ?>" type="number" name="pileBaseHeightM" min="0.01" step="0.01" value="0.3">
                                <div class="brigmaster-estimator__error" data-field-error="pileBaseHeightM" aria-live="polite"></div>
                            </div>
                        </div>

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-field-group="pile-reinforcement-toggle">
                                <input id="<?php echo esc_attr($includePileReinforcementFieldId); ?>" type="checkbox" name="includePileReinforcement" value="1">
                                <label for="<?php echo esc_attr($includePileReinforcementFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Учитывать арматуру свай</span>
                                </label>
                                <div class="brigmaster-estimator__error" data-field-error="includePileReinforcement" aria-live="polite"></div>
                        </div>

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="pile-reinforcement-fields">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileReinforcementBarsCountFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Кол-во стержней в свае</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: количество стержней в свае" aria-expanded="false" aria-controls="<?php echo esc_attr($pileReinforcementBarsCountFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($pileReinforcementBarsCountFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                            Сколько продольных прутков арматуры ставится в одну сваю. Для частного дома чаще всего 3–4.
                                        </div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($pileReinforcementBarsCountFieldId); ?>" type="number" name="pileReinforcementBarsCount" min="1" step="1" value="4">
                                <div class="brigmaster-estimator__error" data-field-error="pileReinforcementBarsCount" aria-live="polite"></div>
                            </div>
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileReinforcementDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Диаметр арматуры свай (мм)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр арматуры свай" aria-expanded="false" aria-controls="<?php echo esc_attr($pileReinforcementDiameterFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($pileReinforcementDiameterFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                            Толщина продольных прутков. Обычно 10–14 мм.
                                        </div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($pileReinforcementDiameterFieldId); ?>" type="number" name="pileReinforcementDiameterMm" min="1" step="1" value="12">
                                <div class="brigmaster-estimator__error" data-field-error="pileReinforcementDiameterMm" aria-live="polite"></div>
                            </div>
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileReinforcementReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Запас арматуры свай (%)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: запас арматуры свай" aria-expanded="false" aria-controls="<?php echo esc_attr($pileReinforcementReserveFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($pileReinforcementReserveFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                            Добавка к расчётному количеству на нахлёсты и отходы при резке. Обычно 5–15%.
                                        </div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($pileReinforcementReserveFieldId); ?>" type="number" name="pileReinforcementReservePercent" min="1" step="1" value="10">
                                <div class="brigmaster-estimator__error" data-field-error="pileReinforcementReservePercent" aria-live="polite"></div>
                            </div>
                        </div>

                            </div>
                        </details>
                    <?php endif; ?>
                <?php if ($calculator === 'pile_foundation') : ?>
                        <details class="brigmaster-estimator__accordion" open>
                            <summary class="brigmaster-estimator__accordion-summary">Тип смеси<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">
                                <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-unified-mixture-toggle>
                                    <input id="<?php echo esc_attr($instanceId . 'use-unified-concrete-mixture'); ?>" type="checkbox" name="useUnifiedConcreteMixtureSettings" value="1" checked>
                                    <label for="<?php echo esc_attr($instanceId . 'use-unified-concrete-mixture'); ?>" class="brigmaster-estimator__label-row">
                                        <span>Использовать один тип смеси для свай и ростверка</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: единый тип смеси" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'use-unified-concrete-mixture-tooltip'); ?>">i</button>
                                            <div id="<?php echo esc_attr($instanceId . 'use-unified-concrete-mixture-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                                Опция доступна, только когда включены оба расчёта — и сваи, и ростверк. Если выбран один расчёт, используется единый тип смеси.
                                            </div>
                                        </span>
                                    </label>
                                    <div class="brigmaster-estimator__error" data-field-error="useUnifiedConcreteMixtureSettings" aria-live="polite"></div>
                                </div>
                                <div data-pile-mixture-block="shared">
                                    <?php echo ConcreteMixtureFields::render($instanceId, 'base', 'Общий тип смеси', false, true); ?>
                                </div>
                                <div class="brigmaster-estimator__field-group--hidden" data-pile-mixture-block="pile">
                                    <?php echo ConcreteMixtureFields::render($instanceId, 'pile', 'Тип смеси для буронабивных свай', false, true); ?>
                                </div>
                                <div class="brigmaster-estimator__field-group--hidden" data-pile-mixture-block="grillage">
                                    <?php echo ConcreteMixtureFields::render($instanceId, 'grillage', 'Тип смеси для ростверка', false, true); ?>
                                </div>
                            </div>
                        </details>
                <?php endif; ?>

                <?php if ($calculator === 'brick') : ?>
                    <?php echo BrickEstimatorFields::render($instanceId); ?>
                <?php endif; ?>

                <?php if ($calculator === 'drywall') : ?>
                    <?php echo DrywallEstimatorFields::render($instanceId); ?>
                <?php endif; ?>

                <?php if ($calculator === 'screed') : ?>
                    <?php
                    $screedIncludeReinforcementFieldId = $instanceId . 'screed-include-reinforcement';
                    ?>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four" data-autofit-row>
                        <div class="brigmaster-estimator__field-group" data-field-group="screed-dimensions">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($lengthFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Длина (м)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: длина" aria-expanded="false" aria-controls="<?php echo esc_attr($lengthFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($lengthFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите длину помещения в метрах.</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($lengthFieldId); ?>" type="number" name="length" min="0.01" step="0.01" value="6">
                                <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group" data-field-group="screed-dimensions">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($widthFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Ширина (м)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: ширина" aria-expanded="false" aria-controls="<?php echo esc_attr($widthFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($widthFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите ширину помещения в метрах.</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($widthFieldId); ?>" type="number" name="width" min="0.01" step="0.01" value="4">
                                <div class="brigmaster-estimator__error" data-field-error="width" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="screed-area">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($areaFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Площадь (м²)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: площадь" aria-expanded="false" aria-controls="<?php echo esc_attr($areaFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($areaFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Введите площадь в м².</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($areaFieldId); ?>" type="number" name="area" min="0.01" step="0.01" value="24">
                                <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="brigmaster-estimator__field-group" data-field-group="screed-height">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($heightFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Высота стяжки (м)</span>
                                    <span class="brigmaster-estimator__tooltip-anchor">
                                        <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: высота стяжки" aria-expanded="false" aria-controls="<?php echo esc_attr($heightFieldId . '-tooltip'); ?>">i</button>
                                        <div id="<?php echo esc_attr($heightFieldId . '-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Укажите среднюю высоту стяжки по всей площади. В метрах: 0.05 = 5 см.</div>
                                    </span>
                                </label>
                                <input id="<?php echo esc_attr($heightFieldId); ?>" type="number" name="height" min="0.001" step="0.001" value="0.05">
                                <div class="brigmaster-estimator__error" data-field-error="height" aria-live="polite"></div>
                            </div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group">
                        <?php echo ConcreteMixtureFields::render($instanceId, 'base', 'Тип смеси', true, false); ?>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="screed-reinforcement">
                        <input id="<?php echo esc_attr($screedIncludeReinforcementFieldId); ?>" type="checkbox" name="includeReinforcement" value="1">
                        <label for="<?php echo esc_attr($screedIncludeReinforcementFieldId); ?>" class="brigmaster-estimator__label-row">
                            <span>Учитывать арматуру</span>
                            <span class="brigmaster-estimator__tooltip-anchor brigmaster-estimator__tooltip-anchor--hidden">
                                <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger data-mode-lock-trigger aria-label="Подсказка: арматура недоступна в режиме по площади" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'screed-rebar-info'); ?>">i</button>
                                <div id="<?php echo esc_attr($instanceId . 'screed-rebar-info'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Для расчёта арматуры нужны длина и ширина. Переключитесь в режим расчета по длине и ширине.</div>
                            </span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includeReinforcement" aria-live="polite"></div>
                        <p class="brigmaster-estimator__hint">Доступно при режиме расчёта по длине и ширине.</p>
                    </div>

                    <details class="brigmaster-estimator__accordion" open data-toggle-target="screed-reinforcement">
                        <summary class="brigmaster-estimator__accordion-summary">Армирование<?php echo MarkupHelpers::accordionChevronMarkup(); ?></summary>
                        <div class="brigmaster-estimator__accordion-body">
                            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Диаметр арматуры (мм)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarDiameterTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($rebarDiameterTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Диаметр влияет на массу арматуры. Обычно 10–14 мм.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarDiameterFieldId); ?>" type="number" name="rebarDiameterMm" min="1" step="1" value="12">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarDiameterMm" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarStepFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Шаг арматуры (мм)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: шаг арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarStepTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($rebarStepTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Шаг сетки между стержнями, обычно 150–250 мм. Чем меньше шаг, тем плотнее сетка.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarStepFieldId); ?>" type="number" name="rebarStepMm" min="50" step="10" value="200">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarStepMm" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarLayersFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Слои арматуры</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: слои арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'screed-rebar-layers-tooltip'); ?>">i</button>
                                            <div id="<?php echo esc_attr($instanceId . 'screed-rebar-layers-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Для стяжки часто 1 слой сетки.</div>
                                        </span>
                                    </label>
                                    <select id="<?php echo esc_attr($rebarLayersFieldId); ?>" name="rebarLayers">
                                        <option value="1">1 слой</option>
                                        <option value="2" selected>2 слоя</option>
                                    </select>
                                    <div class="brigmaster-estimator__error" data-field-error="rebarLayers" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field">
                                    <label for="<?php echo esc_attr($rebarReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Запас арматуры (%)</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: запас арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarReserveTooltipId); ?>">i</button>
                                            <div id="<?php echo esc_attr($rebarReserveTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>Рекомендуемый запас 5–15% на подрезку и нахлёсты.</div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($rebarReserveFieldId); ?>" type="number" name="rebarReservePercent" min="1" step="1" value="10">
                                    <div class="brigmaster-estimator__error" data-field-error="rebarReservePercent" aria-live="polite"></div>
                                </div>
                            </div>
                        </div>
                    </details>
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
                    <div class="bm-calculator-result__head">
                        <h2 class="bm-calculator-result__title">Результаты расчёта</h2>
                        <span class="bm-calculator-result__status">Готово</span>
                    </div>
                    <div class="bm-calculator-result__sections">
                        <section class="bm-calculator-result__breakdown" data-result-card="screed-concrete">
                            <h3 class="bm-calculator-result__section-title">Стяжка</h3>
                            <div class="bm-calculator-result__list">
                                <div class="bm-calculator-result__material" data-result-screed-area-row>
                                    <span class="bm-calculator-result__material-head">
                                        <span>Площадь</span>
                                        <strong><span data-result-screed-area>-</span> м²</strong>
                                    </span>
                                </div>
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Объём смеси
                                            <button class="bm-tooltip" type="button" data-tooltip="Объём раствора с учётом технологического запаса." aria-label="Подсказка: объём смеси">
                                                <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-info-circle"></use></svg>
                                            </button>
                                        </span>
                                        <strong><span data-result-volume>-</span> м³</strong>
                                    </span>
                                </div>
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Высота</span>
                                        <strong><span data-result-screed-height>-</span> м</strong>
                                    </span>
                                </div>
                                <div data-result-card="mixture" hidden></div>
                            </div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="screed-reinforcement" hidden>
                            <h3 class="bm-calculator-result__section-title">Арматура (сетка, ориентир)</h3>
                            <div class="bm-calculator-result__list">
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Масса с запасом</span>
                                        <strong><span data-result-screed-rebar-mass>-</span> кг</strong>
                                    </span>
                                </div>
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Общая длина с запасом</span>
                                        <strong><span data-result-screed-rebar-length>-</span> м</strong>
                                    </span>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="bm-calculator-result__note">
                        <span class="bm-calculator-result__note-icon" aria-hidden="true">
                            <svg class="bm-icon"><use href="#bm-icon-info-circle"></use></svg>
                        </span>
                        <span class="bm-calculator-result__note-content">
                            <strong>Расчёт ориентировочный</strong>
                            <span>Фактический расход зависит от основания, влажности и условий работ.</span>
                        </span>
                    </div>
                    <div class="bm-calculator-result__actions">
                        <button type="button" class="bm-button bm-button--primary" data-result-action="download">
                            <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-download"></use></svg>
                            <span>Открыть PDF</span>
                        </button>
                        <button type="button" class="bm-button bm-button--secondary" data-result-action="copy-link">
                            <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-link"></use></svg>
                            <span>Скопировать ссылку</span>
                        </button>
                        <button type="button" class="bm-button bm-button--secondary" data-result-action="print">
                            <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-print"></use></svg>
                            <span>Распечатать</span>
                        </button>
                    </div>
                    <p class="bm-calculator-result__save">Сохранение результата в проект появится позже.</p>
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

    private function enqueueAssets(string $calculator): void
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
