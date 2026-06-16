<?php

declare(strict_types=1);

namespace Brigmaster\Http\Shortcode;

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
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="slab-dimensions">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($lengthFieldId); ?>">Длина (м)</label>
                            <input id="<?php echo esc_attr($lengthFieldId); ?>" type="number" name="length" min="0.01" step="0.01" value="8" aria-describedby="<?php echo esc_attr($lengthHintId); ?>">
                            <p id="<?php echo esc_attr($lengthHintId); ?>" class="brigmaster-estimator__hint">Введите длину в метрах.</p>
                            <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
                        </div>

                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($widthFieldId); ?>">Ширина (м)</label>
                            <input id="<?php echo esc_attr($widthFieldId); ?>" type="number" name="width" min="0.01" step="0.01" value="6" aria-describedby="<?php echo esc_attr($widthHintId); ?>">
                            <p id="<?php echo esc_attr($widthHintId); ?>" class="brigmaster-estimator__hint">Введите ширину в метрах.</p>
                            <div class="brigmaster-estimator__error" data-field-error="width" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden brigmaster-estimator__field-grid" data-field-group="slab-area">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($areaFieldId); ?>">Площадь (м²)</label>
                            <input id="<?php echo esc_attr($areaFieldId); ?>" type="number" name="area" min="0.01" step="0.01" value="48" aria-describedby="<?php echo esc_attr($areaHintId); ?>">
                            <p id="<?php echo esc_attr($areaHintId); ?>" class="brigmaster-estimator__hint">Введите площадь в м² (например, 48).</p>
                            <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
                        </div>

                        <div id="<?php echo esc_attr($areaModeNoticeId); ?>" class="brigmaster-estimator__notice brigmaster-estimator__field-group--hidden" data-area-mode-notice>
                            Для расчета арматуры и опалубки дополнительно нужны длина и ширина.
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid" data-field-group="slab-height">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($heightFieldId); ?>">Высота (м)</label>
                            <input id="<?php echo esc_attr($heightFieldId); ?>" type="number" name="height" min="0.001" step="0.001" value="0.25" aria-describedby="<?php echo esc_attr($heightHintId); ?>">
                            <p id="<?php echo esc_attr($heightHintId); ?>" class="brigmaster-estimator__hint">Введите высоту в метрах.</p>
                            <div class="brigmaster-estimator__error" data-field-error="height" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="reinforcement">
                            <input id="<?php echo esc_attr($includeReinforcementFieldId); ?>" type="checkbox" name="includeReinforcement" value="1" checked>
                            <label for="<?php echo esc_attr($includeReinforcementFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Учитывать арматуру</span>
                                <span class="brigmaster-estimator__tooltip-anchor brigmaster-estimator__tooltip-anchor--hidden">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger data-mode-lock-trigger aria-label="Подсказка: арматура недоступна в режиме по площади" aria-expanded="false" aria-controls="<?php echo esc_attr($reinforcementModeLockTooltipId); ?>">i</button>
                                    <div id="<?php echo esc_attr($reinforcementModeLockTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Для расчета арматуры и опалубки нужны длина и ширина. Переключитесь в режим расчета по длине и ширине.
                                    </div>
                                </span>
                            </label>
                            <div class="brigmaster-estimator__error" data-field-error="includeReinforcement" aria-live="polite"></div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four" data-field-group="slab-reinforcement">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($rebarDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Диаметр арматуры (мм)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarDiameterTooltipId); ?>">i</button>
                                    <div id="<?php echo esc_attr($rebarDiameterTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Диаметр влияет на массу арматуры. Чем больше диаметр, тем больше итоговый вес.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($rebarDiameterFieldId); ?>" type="number" name="rebarDiameterMm" min="1" step="1" value="12" aria-describedby="<?php echo esc_attr($rebarDiameterHintId); ?>">
                            <p id="<?php echo esc_attr($rebarDiameterHintId); ?>" class="brigmaster-estimator__hint">Стандартно для частного дома обычно 10-14 мм.</p>
                            <div class="brigmaster-estimator__error" data-field-error="rebarDiameterMm" aria-live="polite"></div>
                        </div>

                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($rebarStepFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Шаг арматуры (мм)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: шаг арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarStepTooltipId); ?>">i</button>
                                    <div id="<?php echo esc_attr($rebarStepTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Шаг сетки между стержнями, обычно 150-250 мм.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($rebarStepFieldId); ?>" type="number" name="rebarStepMm" min="50" step="10" value="200" aria-describedby="<?php echo esc_attr($rebarStepHintId); ?>">
                            <p id="<?php echo esc_attr($rebarStepHintId); ?>" class="brigmaster-estimator__hint">Чем меньше шаг, тем плотнее сетка и выше расход.</p>
                            <div class="brigmaster-estimator__error" data-field-error="rebarStepMm" aria-live="polite"></div>
                        </div>

                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($rebarLayersFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Слои арматуры</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: слои арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarLayersTooltipId); ?>">i</button>
                                    <div id="<?php echo esc_attr($rebarLayersTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        1 слой для легких нагрузок, 2 слоя - стандарт для жилых домов.
                                    </div>
                                </span>
                            </label>
                            <select id="<?php echo esc_attr($rebarLayersFieldId); ?>" name="rebarLayers" aria-describedby="<?php echo esc_attr($rebarLayersHintId); ?>">
                                <option value="1">1 слой</option>
                                <option value="2" selected>2 слоя</option>
                            </select>
                            <p id="<?php echo esc_attr($rebarLayersHintId); ?>" class="brigmaster-estimator__hint">Для монолитной плиты чаще используют 2 слоя.</p>
                            <div class="brigmaster-estimator__error" data-field-error="rebarLayers" aria-live="polite"></div>
                        </div>

                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($rebarReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Запас арматуры (%)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: запас арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarReserveTooltipId); ?>">i</button>
                                    <div id="<?php echo esc_attr($rebarReserveTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Рекомендуемый запас 5-15% в зависимости от сложности схемы.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($rebarReserveFieldId); ?>" type="number" name="rebarReservePercent" min="1" step="1" value="10" aria-describedby="<?php echo esc_attr($rebarReserveHintId); ?>">
                            <p id="<?php echo esc_attr($rebarReserveHintId); ?>" class="brigmaster-estimator__hint">Компенсирует подрезку и нахлесты.</p>
                            <div class="brigmaster-estimator__error" data-field-error="rebarReservePercent" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="formwork">
                            <input id="<?php echo esc_attr($includeFormworkFieldId); ?>" type="checkbox" name="includeFormwork" value="1" checked>
                            <label for="<?php echo esc_attr($includeFormworkFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Учитывать опалубку</span>
                                <span class="brigmaster-estimator__tooltip-anchor brigmaster-estimator__tooltip-anchor--hidden">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger data-mode-lock-trigger aria-label="Подсказка: опалубка недоступна в режиме по площади" aria-expanded="false" aria-controls="<?php echo esc_attr($formworkModeLockTooltipId); ?>">i</button>
                                    <div id="<?php echo esc_attr($formworkModeLockTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Для расчета арматуры и опалубки нужны длина и ширина. Переключитесь в режим расчета по длине и ширине.
                                    </div>
                                </span>
                            </label>
                            <div class="brigmaster-estimator__error" data-field-error="includeFormwork" aria-live="polite"></div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="slab-formwork">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($formworkHeightFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Высота опалубки (м)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: высота опалубки" aria-expanded="false" aria-controls="<?php echo esc_attr($formworkHeightTooltipId); ?>">i</button>
                                    <div id="<?php echo esc_attr($formworkHeightTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Опалубка считается по периметру плиты и указанной высоте.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($formworkHeightFieldId); ?>" type="number" name="formworkHeightM" min="0.01" step="0.01" value="0.30" aria-describedby="<?php echo esc_attr($formworkHeightHintId); ?>">
                            <p id="<?php echo esc_attr($formworkHeightHintId); ?>" class="brigmaster-estimator__hint">Высота бокового щита, обычно равна высоте заливки.</p>
                            <div class="brigmaster-estimator__error" data-field-error="formworkHeightM" aria-live="polite"></div>
                        </div>

                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($formworkReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Запас опалубки (%)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: запас опалубки" aria-expanded="false" aria-controls="<?php echo esc_attr($formworkReserveTooltipId); ?>">i</button>
                                    <div id="<?php echo esc_attr($formworkReserveTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Рекомендуется 5-15% в зависимости от типа щитов и геометрии.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($formworkReserveFieldId); ?>" type="number" name="formworkReservePercent" min="1" step="1" value="10" aria-describedby="<?php echo esc_attr($formworkReserveHintId); ?>">
                            <p id="<?php echo esc_attr($formworkReserveHintId); ?>" class="brigmaster-estimator__hint">Запас на подрезку и стыковки.</p>
                            <div class="brigmaster-estimator__error" data-field-error="formworkReservePercent" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group">
                        <?php echo $this->renderConcreteMixtureFields($instanceId, 'base', 'Тип смеси', false, true); ?>
                    </div>
                <?php endif; ?>

                <?php if ($calculator === 'pile_foundation') : ?>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="pile-toggles">
                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                            <input id="<?php echo esc_attr($includePilesFieldId); ?>" type="checkbox" name="includePiles" value="1" checked>
                            <label for="<?php echo esc_attr($includePilesFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Рассчитать сваи</span>
                            </label>
                            <div class="brigmaster-estimator__error" data-field-error="includePiles" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                            <input id="<?php echo esc_attr($includeGrillageFieldId); ?>" type="checkbox" name="includeGrillage" value="1" checked>
                            <label for="<?php echo esc_attr($includeGrillageFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Рассчитать ростверк</span>
                            </label>
                            <div class="brigmaster-estimator__error" data-field-error="includeGrillage" aria-live="polite"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (in_array($calculator, ['strip_foundation', 'pile_foundation'], true)) : ?>
                    <div class="brigmaster-estimator__accordions" data-estimator-accordions>
                    <?php if ($calculator === 'pile_foundation') : ?>
                        <details class="brigmaster-estimator__accordion" open data-pile-panel="piles">
                            <summary class="brigmaster-estimator__accordion-summary">Сваи<?php echo $this->accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">
                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field" data-field-group="pile-type">
                                <label for="<?php echo esc_attr($pileTypeFieldId); ?>">Тип свай</label>
                                <select id="<?php echo esc_attr($pileTypeFieldId); ?>" name="pileType">
                                    <option value="bored">Буронабивные</option>
                                    <option value="screw">Винтовые</option>
                                    <option value="driven">Забивные</option>
                                </select>
                                <p class="brigmaster-estimator__hint" data-pile-type-note>
                                    Для винтовых и забивных свай бетон не требуется. Для буронабивных выполняется расчет бетона.
                                </p>
                                <div class="brigmaster-estimator__error" data-field-error="pileType" aria-live="polite"></div>
                        </div>

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-field-group="pile-base-toggle">
                                <input id="<?php echo esc_attr($includePileBaseFieldId); ?>" type="checkbox" name="includePileBase" value="1" checked>
                                <label for="<?php echo esc_attr($includePileBaseFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Учитывать уширение сваи (пяту)</span>
                                </label>
                                <div class="brigmaster-estimator__error" data-field-error="includePileBase" aria-live="polite"></div>
                        </div>

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-field-group="pile-reinforcement-toggle">
                                <input id="<?php echo esc_attr($includePileReinforcementFieldId); ?>" type="checkbox" name="includePileReinforcement" value="1">
                                <label for="<?php echo esc_attr($includePileReinforcementFieldId); ?>" class="brigmaster-estimator__label-row">
                                    <span>Учитывать арматуру свай</span>
                                </label>
                                <div class="brigmaster-estimator__error" data-field-error="includePileReinforcement" aria-live="polite"></div>
                        </div>

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--pile-primary" data-field-group="pile-primary-row" data-pile-primary-grid>
                                <div class="brigmaster-estimator__field brigmaster-estimator__field--pile-count">
                                    <label for="<?php echo esc_attr($pilesCountFieldId); ?>" class="brigmaster-estimator__label-row">
                                        <span>Количество свай</span>
                                        <span class="brigmaster-estimator__tooltip-anchor">
                                            <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: количество свай" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'pile-count-tooltip'); ?>">i</button>
                                            <div id="<?php echo esc_attr($instanceId . 'pile-count-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                                Количество свай определяют по нагрузке здания и несущей способности одной сваи по геологии участка. Для точного подбора используйте проект.
                                            </div>
                                        </span>
                                    </label>
                                    <input id="<?php echo esc_attr($pilesCountFieldId); ?>" type="number" name="pilesCount" min="1" step="1" value="20" inputmode="numeric">
                                    <div class="brigmaster-estimator__error" data-field-error="pilesCount" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field" data-pile-primary-cell="shaft-diameter">
                                    <label for="<?php echo esc_attr($pileShaftDiameterFieldId); ?>">Диаметр ствола сваи (м)</label>
                                    <input id="<?php echo esc_attr($pileShaftDiameterFieldId); ?>" type="number" name="pileShaftDiameterM" min="0.01" step="0.01" value="0.3">
                                    <div class="brigmaster-estimator__error" data-field-error="pileShaftDiameterM" aria-live="polite"></div>
                                </div>
                                <div class="brigmaster-estimator__field" data-pile-primary-cell="shaft-height">
                                    <label for="<?php echo esc_attr($pileShaftHeightFieldId); ?>">Высота ствола сваи (м)</label>
                                    <input id="<?php echo esc_attr($pileShaftHeightFieldId); ?>" type="number" name="pileShaftHeightM" min="0.01" step="0.01" value="2">
                                    <div class="brigmaster-estimator__error" data-field-error="pileShaftHeightM" aria-live="polite"></div>
                                </div>
                        </div>

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="pile-base-fields">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileBaseDiameterFieldId); ?>">Диаметр уширения (м)</label>
                                <input id="<?php echo esc_attr($pileBaseDiameterFieldId); ?>" type="number" name="pileBaseDiameterM" min="0.01" step="0.01" value="0.5">
                                <div class="brigmaster-estimator__error" data-field-error="pileBaseDiameterM" aria-live="polite"></div>
                            </div>
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileBaseHeightFieldId); ?>">Высота уширения (м)</label>
                                <input id="<?php echo esc_attr($pileBaseHeightFieldId); ?>" type="number" name="pileBaseHeightM" min="0.01" step="0.01" value="0.3">
                                <div class="brigmaster-estimator__error" data-field-error="pileBaseHeightM" aria-live="polite"></div>
                            </div>
                        </div>

                        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="pile-reinforcement-fields">
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileReinforcementBarsCountFieldId); ?>">Кол-во стержней в свае</label>
                                <input id="<?php echo esc_attr($pileReinforcementBarsCountFieldId); ?>" type="number" name="pileReinforcementBarsCount" min="1" step="1" value="4">
                                <div class="brigmaster-estimator__error" data-field-error="pileReinforcementBarsCount" aria-live="polite"></div>
                            </div>
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileReinforcementDiameterFieldId); ?>">Диаметр арматуры свай (мм)</label>
                                <input id="<?php echo esc_attr($pileReinforcementDiameterFieldId); ?>" type="number" name="pileReinforcementDiameterMm" min="1" step="1" value="12">
                                <div class="brigmaster-estimator__error" data-field-error="pileReinforcementDiameterMm" aria-live="polite"></div>
                            </div>
                            <div class="brigmaster-estimator__field">
                                <label for="<?php echo esc_attr($pileReinforcementReserveFieldId); ?>">Запас арматуры свай (%)</label>
                                <input id="<?php echo esc_attr($pileReinforcementReserveFieldId); ?>" type="number" name="pileReinforcementReservePercent" min="1" step="1" value="10">
                                <div class="brigmaster-estimator__error" data-field-error="pileReinforcementReservePercent" aria-live="polite"></div>
                            </div>
                        </div>

                            </div>
                        </details>
                    <?php endif; ?>
                    <?php $stripLabel = $calculator === 'pile_foundation' ? 'ростверка' : 'ленты'; ?>
                        <details class="brigmaster-estimator__accordion" open<?php echo $calculator === 'pile_foundation' ? ' data-pile-panel="grillage"' : ''; ?>>
                            <summary class="brigmaster-estimator__accordion-summary"><?php echo $calculator === 'pile_foundation' ? 'Геометрия ростверка' : 'Геометрия ленты'; ?><?php echo $this->accordionChevronMarkup(); ?></summary>
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
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-include-rebar'); ?>" type="checkbox" checked data-segment-include-reinforcement data-checkbox-key="segment-include-rebar">
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
                                            <input id="<?php echo esc_attr($instanceId . 'segment-0-include-formwork'); ?>" type="checkbox" checked data-segment-include-formwork data-checkbox-key="segment-include-formwork">
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
                            </div>
                        </details>
                        <details class="brigmaster-estimator__accordion" open<?php echo $calculator === 'pile_foundation' ? ' data-pile-panel="grillage"' : ''; ?>>
                            <summary class="brigmaster-estimator__accordion-summary"><?php echo $calculator === 'pile_foundation' ? 'Арматура ростверка' : 'Арматура'; ?><?php echo $this->accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="strip-reinforcement">
                            <input id="<?php echo esc_attr($instanceId . 'strip-include-reinforcement'); ?>" type="checkbox" name="includeReinforcement" value="1" checked>
                            <label for="<?php echo esc_attr($instanceId . 'strip-include-reinforcement'); ?>" class="brigmaster-estimator__label-row">
                                <span><?php echo $calculator === 'pile_foundation' ? 'Учитывать арматуру ростверга' : 'Учитывать арматуру'; ?></span>
                            </label>
                    </div>

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
                            </div>
                        </details>
                        <details class="brigmaster-estimator__accordion" open<?php echo $calculator === 'pile_foundation' ? ' data-pile-panel="grillage"' : ''; ?>>
                            <summary class="brigmaster-estimator__accordion-summary"><?php echo $calculator === 'pile_foundation' ? 'Опалубка ростверка' : 'Опалубка'; ?><?php echo $this->accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="strip-formwork">
                            <input id="<?php echo esc_attr($instanceId . 'strip-include-formwork'); ?>" type="checkbox" name="includeFormwork" value="1" checked>
                            <label for="<?php echo esc_attr($instanceId . 'strip-include-formwork'); ?>" class="brigmaster-estimator__label-row">
                                <span><?php echo $calculator === 'pile_foundation' ? 'Учитывать опалубку ростверга' : 'Учитывать опалубку'; ?></span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: учитывать опалубку" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'strip-formwork-height-tooltip'); ?>">i</button>
                                    <div id="<?php echo esc_attr($instanceId . 'strip-formwork-height-tooltip'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        В расчёт попадают только боковые щиты ленты (высота задаётся ниже). Распорки, подкосы и крепёж не учитываются.
                                    </div>
                                </span>
                            </label>
                    </div>

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
                            </div>
                        </details>
                    </div>
                <?php endif; ?>

                <?php if ($calculator === 'strip_foundation') : ?>
                    <div class="brigmaster-estimator__field-group">
                        <?php echo $this->renderConcreteMixtureFields($instanceId, 'base', 'Тип смеси', false, true); ?>
                    </div>
                <?php endif; ?>

                <?php if ($calculator === 'pile_foundation') : ?>
                    <div class="brigmaster-estimator__accordions">
                        <details class="brigmaster-estimator__accordion" open>
                            <summary class="brigmaster-estimator__accordion-summary">Тип смеси<?php echo $this->accordionChevronMarkup(); ?></summary>
                            <div class="brigmaster-estimator__accordion-body">
                                <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle">
                                    <input id="<?php echo esc_attr($instanceId . 'use-unified-concrete-mixture'); ?>" type="checkbox" name="useUnifiedConcreteMixtureSettings" value="1" checked>
                                    <label for="<?php echo esc_attr($instanceId . 'use-unified-concrete-mixture'); ?>" class="brigmaster-estimator__label-row">
                                        <span>Использовать один тип смеси для свай и ростверка</span>
                                    </label>
                                    <div class="brigmaster-estimator__error" data-field-error="useUnifiedConcreteMixtureSettings" aria-live="polite"></div>
                                </div>
                                <div data-pile-mixture-block="shared">
                                    <?php echo $this->renderConcreteMixtureFields($instanceId, 'base', 'Общий тип смеси', false, true); ?>
                                </div>
                                <div class="brigmaster-estimator__field-group--hidden" data-pile-mixture-block="pile">
                                    <?php echo $this->renderConcreteMixtureFields($instanceId, 'pile', 'Тип смеси для буронабивных свай', false, true); ?>
                                </div>
                                <div class="brigmaster-estimator__field-group--hidden" data-pile-mixture-block="grillage">
                                    <?php echo $this->renderConcreteMixtureFields($instanceId, 'grillage', 'Тип смеси для ростверка', false, true); ?>
                                </div>
                            </div>
                        </details>
                    </div>
                <?php endif; ?>

                <?php if ($calculator === 'brick') : ?>
                    <?php echo $this->renderBrickEstimatorFields($instanceId); ?>
                <?php endif; ?>

                <?php if ($calculator === 'drywall') : ?>
                    <?php echo $this->renderDrywallEstimatorFields($instanceId); ?>
                <?php endif; ?>

                <?php if ($calculator === 'screed') : ?>
                    <?php
                    $screedIncludeReinforcementFieldId = $instanceId . 'screed-include-reinforcement';
                    ?>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="screed-dimensions">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($lengthFieldId); ?>">Длина (м)</label>
                            <input id="<?php echo esc_attr($lengthFieldId); ?>" type="number" name="length" min="0.01" step="0.01" value="6" aria-describedby="<?php echo esc_attr($lengthHintId); ?>">
                            <p id="<?php echo esc_attr($lengthHintId); ?>" class="brigmaster-estimator__hint">Введите длину помещения в метрах.</p>
                            <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($widthFieldId); ?>">Ширина (м)</label>
                            <input id="<?php echo esc_attr($widthFieldId); ?>" type="number" name="width" min="0.01" step="0.01" value="4" aria-describedby="<?php echo esc_attr($widthHintId); ?>">
                            <p id="<?php echo esc_attr($widthHintId); ?>" class="brigmaster-estimator__hint">Введите ширину помещения в метрах.</p>
                            <div class="brigmaster-estimator__error" data-field-error="width" aria-live="polite"></div>
                        </div>
                    </div>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden brigmaster-estimator__field-grid" data-field-group="screed-area">
                        <label for="<?php echo esc_attr($areaFieldId); ?>">Площадь (м²)</label>
                        <input id="<?php echo esc_attr($areaFieldId); ?>" type="number" name="area" min="0.01" step="0.01" value="24" aria-describedby="<?php echo esc_attr($areaHintId); ?>">
                        <p id="<?php echo esc_attr($areaHintId); ?>" class="brigmaster-estimator__hint">Введите площадь в м².</p>
                        <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="screed-height">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($heightFieldId); ?>">Высота стяжки (м)</label>
                            <input id="<?php echo esc_attr($heightFieldId); ?>" type="number" name="height" min="0.001" step="0.001" value="0.05" aria-describedby="<?php echo esc_attr($heightHintId); ?>">
                            <p id="<?php echo esc_attr($heightHintId); ?>" class="brigmaster-estimator__hint">Укажите среднюю высоту стяжки по всей площади. В метрах: 0.05 = 5 см.</p>
                            <div class="brigmaster-estimator__error" data-field-error="height" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-toggle-field="screed-reinforcement">
                        <input id="<?php echo esc_attr($screedIncludeReinforcementFieldId); ?>" type="checkbox" name="includeReinforcement" value="1">
                        <label for="<?php echo esc_attr($screedIncludeReinforcementFieldId); ?>" class="brigmaster-estimator__label-row">
                            <span>Учитывать арматуру</span>
                            <span class="brigmaster-estimator__tooltip-anchor brigmaster-estimator__tooltip-anchor--hidden">
                                <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger data-mode-lock-trigger aria-label="Подсказка: арматура недоступна в режиме по площади" aria-expanded="false" aria-controls="<?php echo esc_attr($instanceId . 'screed-rebar-info'); ?>">i</button>
                                <div id="<?php echo esc_attr($instanceId . 'screed-rebar-info'); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                    Для расчёта арматуры нужны длина и ширина. Переключитесь в режим расчета по длине и ширине.
                                </div>
                            </span>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includeReinforcement" aria-live="polite"></div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four" data-field-group="screed-reinforcement">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($rebarDiameterFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Диаметр арматуры (мм)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: диаметр арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarDiameterTooltipId); ?>">i</button>
                                    <div id="<?php echo esc_attr($rebarDiameterTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Диаметр влияет на массу арматуры. Чем больше диаметр, тем больше итоговый вес.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($rebarDiameterFieldId); ?>" type="number" name="rebarDiameterMm" min="1" step="1" value="12" aria-describedby="<?php echo esc_attr($rebarDiameterHintId); ?>">
                            <p id="<?php echo esc_attr($rebarDiameterHintId); ?>" class="brigmaster-estimator__hint">Обычно 10–14 мм.</p>
                            <div class="brigmaster-estimator__error" data-field-error="rebarDiameterMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($rebarStepFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Шаг арматуры (мм)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: шаг арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarStepTooltipId); ?>">i</button>
                                    <div id="<?php echo esc_attr($rebarStepTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Шаг сетки между стержнями, обычно 150–250 мм.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($rebarStepFieldId); ?>" type="number" name="rebarStepMm" min="50" step="10" value="200" aria-describedby="<?php echo esc_attr($rebarStepHintId); ?>">
                            <p id="<?php echo esc_attr($rebarStepHintId); ?>" class="brigmaster-estimator__hint">Чем меньше шаг, тем плотнее сетка.</p>
                            <div class="brigmaster-estimator__error" data-field-error="rebarStepMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($rebarLayersFieldId); ?>">Слои арматуры</label>
                            <select id="<?php echo esc_attr($rebarLayersFieldId); ?>" name="rebarLayers" aria-describedby="<?php echo esc_attr($rebarLayersHintId); ?>">
                                <option value="1">1 слой</option>
                                <option value="2" selected>2 слоя</option>
                            </select>
                            <p id="<?php echo esc_attr($rebarLayersHintId); ?>" class="brigmaster-estimator__hint">Для стяжки часто 1 слой сетки.</p>
                            <div class="brigmaster-estimator__error" data-field-error="rebarLayers" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($rebarReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Запас арматуры (%)</span>
                                <span class="brigmaster-estimator__tooltip-anchor">
                                    <button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка: запас арматуры" aria-expanded="false" aria-controls="<?php echo esc_attr($rebarReserveTooltipId); ?>">i</button>
                                    <div id="<?php echo esc_attr($rebarReserveTooltipId); ?>" class="brigmaster-estimator__tooltip" role="tooltip" hidden>
                                        Рекомендуемый запас 5–15% на подрезку и нахлёсты.
                                    </div>
                                </span>
                            </label>
                            <input id="<?php echo esc_attr($rebarReserveFieldId); ?>" type="number" name="rebarReservePercent" min="1" step="1" value="10" aria-describedby="<?php echo esc_attr($rebarReserveHintId); ?>">
                            <p id="<?php echo esc_attr($rebarReserveHintId); ?>" class="brigmaster-estimator__hint">На подрезку и нахлёсты.</p>
                            <div class="brigmaster-estimator__error" data-field-error="rebarReservePercent" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group">
                        <?php echo $this->renderConcreteMixtureFields($instanceId, 'base', 'Тип смеси', true, false); ?>
                    </div>
                <?php endif; ?>

                <?php if ($calculator === 'tile') : ?>
                    <?php echo $this->renderTileEstimatorFields($instanceId); ?>
                <?php endif; ?>

                <div class="brigmaster-estimator__validation-summary" data-validation-summary role="alert" hidden></div>
                <button type="submit">Рассчитать</button>
                <div class="brigmaster-estimator__error" data-field-error="general" aria-live="assertive"></div>
            </form>

            <div class="brigmaster-estimator__result" data-result hidden aria-live="polite" aria-atomic="true" tabindex="-1">
                <p class="brigmaster-estimator__result-stale-notice" data-result-stale-notice hidden>Результат устарел — нажмите «Рассчитать» снова.</p>
                <?php if ($calculator === 'slab_foundation') : ?>
                    <div class="brigmaster-estimator__result-grid">
                        <section class="brigmaster-estimator__result-card" data-result-card="concrete">
                            <h3>Бетон</h3>
                            <p><strong>Объем:</strong> <span data-result-concrete-volume>-</span> м3</p>
                            <p><strong>Площадь:</strong> <span data-result-concrete-area>-</span> м2</p>
                            <p><strong>Высота:</strong> <span data-result-concrete-height>-</span> м</p>
                        </section>
                        <section class="brigmaster-estimator__result-card" data-result-card="reinforcement" hidden></section>
                        <section class="brigmaster-estimator__result-card" data-result-card="formwork" hidden></section>
                        <section class="brigmaster-estimator__result-card" data-result-card="mixture" hidden></section>
                    </div>
                    <div class="brigmaster-estimator__scheme" data-slab-scheme></div>
                <?php elseif ($calculator === 'strip_foundation') : ?>
                    <div class="bm-calculator-result__head">
                        <h2 class="bm-calculator-result__title">Результаты расчёта</h2>
                        <span class="bm-calculator-result__status">Готово</span>
                    </div>
                    <div class="bm-calculator-result__sections">
                        <section class="bm-calculator-result__breakdown" data-result-card="strip-concrete">
                            <h3 class="bm-calculator-result__section-title">Бетон</h3>
                            <div class="bm-calculator-result__list">
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Общая длина ленты</span>
                                        <strong><span data-result-strip-concrete-length>-</span> м</strong>
                                    </span>
                                </div>
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Объём бетона
                                            <button class="bm-tooltip" type="button" data-tooltip="Объём с учётом технологического запаса на потери при заливке." aria-label="Подсказка: объём бетона">
                                                <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-info-circle"></use></svg>
                                            </button>
                                        </span>
                                        <strong><span data-result-strip-concrete-volume>-</span> м³</strong>
                                    </span>
                                </div>
                                <div data-result-card="mixture" hidden></div>
                            </div>
                        </section>
                        <section class="bm-calculator-result__breakdown" data-result-card="strip-reinforcement" hidden></section>
                        <section class="bm-calculator-result__breakdown" data-result-card="strip-formwork" hidden>
                            <h3 class="bm-calculator-result__section-title">Опалубка</h3>
                            <div class="bm-calculator-result__list">
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Суммарная площадь щитов
                                            <button class="bm-tooltip" type="button" data-tooltip="Площадь боковых поверхностей ленты с запасом на раскрой пиломатериала." aria-label="Подсказка: площадь опалубки">
                                                <svg class="bm-icon" aria-hidden="true"><use href="#bm-icon-info-circle"></use></svg>
                                            </button>
                                        </span>
                                        <strong><span data-result-strip-formwork-area>-</span> м²</strong>
                                    </span>
                                </div>
                                <div class="bm-calculator-result__material">
                                    <span class="bm-calculator-result__material-head">
                                        <span>Погонные метры</span>
                                        <strong><span data-result-strip-formwork-linear>-</span> м</strong>
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
                            <span>Для точного проектирования выполните проект и геологические изыскания.</span>
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
                <?php elseif ($calculator === 'pile_foundation') : ?>
                    <div class="brigmaster-estimator__result-grid brigmaster-estimator__result-grid--pile-foundation">
                        <div class="brigmaster-estimator__result-section" data-result-section="pile-header" hidden>
                            <h3 class="brigmaster-estimator__result-section-title">Сваи</h3>
                            <p class="brigmaster-estimator__result-section-subtitle">Параметры свай</p>
                            <p><strong>Тип:</strong> <span data-result-pile-type>-</span></p>
                            <p><strong>Количество:</strong> <span data-result-pile-count>-</span> шт</p>
                            <p data-result-pile-note-row hidden><strong>Примечание:</strong> <span data-result-pile-note>-</span></p>
                        </div>
                        <section class="brigmaster-estimator__result-card" data-result-card="pile-concrete" hidden>
                            <h4>Бетон свай</h4>
                            <p><strong>Объем бетона:</strong> <span data-result-pile-concrete-volume>-</span> м3</p>
                            <p data-result-pile-per-pile-row hidden><strong>На 1 сваю:</strong> <span data-result-pile-concrete-per-pile>-</span> м3</p>
                        </section>
                        <section class="brigmaster-estimator__result-card" data-result-card="pile-reinforcement" hidden></section>
                        <section class="brigmaster-estimator__result-card" data-result-card="pile-foundation-mixture" hidden></section>
                        <div class="brigmaster-estimator__result-section" data-result-section="grillage-header" hidden>
                            <h3 class="brigmaster-estimator__result-section-title">Ростверк</h3>
                        </div>
                        <section class="brigmaster-estimator__result-card" data-result-card="strip-concrete" hidden>
                            <h4>Бетон ростверка</h4>
                            <p><strong>Общая длина:</strong> <span data-result-strip-concrete-length>-</span> м</p>
                            <p><strong>Объем бетона:</strong> <span data-result-strip-concrete-volume>-</span> м3</p>
                        </section>
                        <section class="brigmaster-estimator__result-card" data-result-card="strip-reinforcement" hidden></section>
                        <section class="brigmaster-estimator__result-card" data-result-card="grillage-mixture" hidden></section>
                        <section class="brigmaster-estimator__result-card" data-result-card="strip-formwork" hidden>
                            <h4>Опалубка ростверка</h4>
                            <p><strong>Суммарная площадь щитов:</strong> <span data-result-strip-formwork-area>-</span> м2</p>
                            <p><strong>Погонные метры:</strong> <span data-result-strip-formwork-linear>-</span> м</p>
                        </section>
                    </div>
                <?php elseif ($calculator === 'screed') : ?>
                    <div class="brigmaster-estimator__result-grid brigmaster-estimator__result-grid--screed">
                        <section class="brigmaster-estimator__result-card">
                            <h3>Стяжка</h3>
                            <p data-result-screed-area-row><strong>Площадь:</strong> <span data-result-screed-area>-</span> м²</p>
                            <p><strong>Объём смеси:</strong> <span data-result-volume>-</span> м³</p>
                            <p><strong>Высота:</strong> <span data-result-screed-height>-</span> м</p>
                        </section>
                        <section class="brigmaster-estimator__result-card" data-result-card="screed-reinforcement" hidden>
                            <h3>Арматура (сетка, ориентир)</h3>
                            <p><strong>Масса с запасом:</strong> <span data-result-screed-rebar-mass>-</span> кг</p>
                            <p><strong>Общая длина с запасом:</strong> <span data-result-screed-rebar-length>-</span> м</p>
                        </section>
                        <section class="brigmaster-estimator__result-card" data-result-card="mixture" hidden></section>
                    </div>
                <?php elseif ($calculator === 'brick') : ?>
                    <?php echo $this->renderBrickResultTemplate(); ?>
                <?php elseif ($calculator === 'tile') : ?>
                    <?php echo $this->renderTileResultTemplate(); ?>
                <?php elseif ($calculator === 'drywall') : ?>
                    <?php echo $this->renderDrywallResultTemplate(); ?>
                <?php endif; ?>
            </div>
            <div class="brigmaster-estimator__tooltip-backdrop" data-tooltip-backdrop hidden></div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    private function renderConcreteMixtureFields(
        string $instanceId,
        string $scope,
        string $title,
        bool $allowDryReady,
        bool $includeGravel
    ): string {
        $namePrefix = match ($scope) {
            'pile' => 'pile',
            'grillage' => 'grillage',
            default => '',
        };
        $errorPrefix = match ($scope) {
            'pile' => 'pileMixture',
            'grillage' => 'grillageMixture',
            default => 'mixture',
        };

        $buildName = static function (string $suffix) use ($namePrefix): string {
            if ($namePrefix === '') {
                return $suffix;
            }

            return $namePrefix . ucfirst($suffix);
        };

        $scopeSlug = $scope === '' ? 'base' : $scope;
        $typeFieldId = $instanceId . '-' . $scopeSlug . '-mixture-type';
        $priceFieldId = $instanceId . '-' . $scopeSlug . '-ready-price';
        $dryWeightFieldId = $instanceId . '-' . $scopeSlug . '-dry-bag-weight';
        $dryPriceFieldId = $instanceId . '-' . $scopeSlug . '-dry-bag-price';
        $cementShareFieldId = $instanceId . '-' . $scopeSlug . '-cement-share';
        $cementUnitTypeFieldId = $instanceId . '-' . $scopeSlug . '-cement-unit-type';
        $cementUnitWeightFieldId = $instanceId . '-' . $scopeSlug . '-cement-unit-weight';
        $cementUnitPriceFieldId = $instanceId . '-' . $scopeSlug . '-cement-unit-price';
        $sandShareFieldId = $instanceId . '-' . $scopeSlug . '-sand-share';
        $sandUnitTypeFieldId = $instanceId . '-' . $scopeSlug . '-sand-unit-type';
        $sandUnitWeightFieldId = $instanceId . '-' . $scopeSlug . '-sand-unit-weight';
        $sandUnitPriceFieldId = $instanceId . '-' . $scopeSlug . '-sand-unit-price';
        $gravelShareFieldId = $instanceId . '-' . $scopeSlug . '-gravel-share';
        $gravelUnitTypeFieldId = $instanceId . '-' . $scopeSlug . '-gravel-unit-type';
        $gravelUnitWeightFieldId = $instanceId . '-' . $scopeSlug . '-gravel-unit-weight';
        $gravelUnitPriceFieldId = $instanceId . '-' . $scopeSlug . '-gravel-unit-price';

        ob_start();
        ?>
        <div class="brigmaster-estimator__mixture-block" data-mixture-scope="<?php echo esc_attr($scopeSlug); ?>" data-mixture-has-gravel="<?php echo $includeGravel ? '1' : '0'; ?>">
            <div class="brigmaster-estimator__field-group brigmaster-estimator__field">
                <label for="<?php echo esc_attr($typeFieldId); ?>"><?php echo esc_html($title); ?></label>
                <select id="<?php echo esc_attr($typeFieldId); ?>" name="<?php echo esc_attr($buildName('mixtureType')); ?>" data-mixture-type-select>
                    <option value="ready">Готовая</option>
                    <?php if ($allowDryReady) : ?>
                        <option value="dry_ready">Готовая, сухая</option>
                    <?php endif; ?>
                    <option value="self_mix">Самомесная</option>
                </select>
                <p class="brigmaster-estimator__hint">Для самомесной смеси доли компонентов принимаются по объёму.</p>
                <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.type'); ?>" aria-live="polite"></div>
            </div>

            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-mixture-panel="ready">
                <div class="brigmaster-estimator__field">
                    <label for="<?php echo esc_attr($priceFieldId); ?>">Цена раствора за м³</label>
                    <input id="<?php echo esc_attr($priceFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('readyConcretePricePerM3')); ?>" min="1" step="1" value="7000">
                    <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.readyConcretePricePerM3'); ?>" aria-live="polite"></div>
                </div>
            </div>

            <?php if ($allowDryReady) : ?>
                <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two brigmaster-estimator__field-group--hidden" data-mixture-panel="dry_ready">
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($dryWeightFieldId); ?>">Вес мешка (кг)</label>
                        <input id="<?php echo esc_attr($dryWeightFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('dryMixBagWeightKg')); ?>" min="1" step="1" value="25">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.dryMixBagWeightKg'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($dryPriceFieldId); ?>">Цена мешка</label>
                        <input id="<?php echo esc_attr($dryPriceFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('dryMixBagPrice')); ?>" min="1" step="1" value="350">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.dryMixBagPrice'); ?>" aria-live="polite"></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-mixture-panel="self_mix">
                <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($cementShareFieldId); ?>">Доля цемента</label>
                        <input id="<?php echo esc_attr($cementShareFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('cementShare')); ?>" min="0.1" step="0.1" value="1">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.cementShare'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($cementUnitTypeFieldId); ?>">Единица покупки цемента</label>
                        <select id="<?php echo esc_attr($cementUnitTypeFieldId); ?>" name="<?php echo esc_attr($buildName('cementPurchaseUnit')); ?>">
                            <option value="bag">Мешок</option>
                            <option value="tonne">Тонна</option>
                        </select>
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.cementPurchaseUnit'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($cementUnitWeightFieldId); ?>" data-mixture-unit-label="cement">Вес единицы цемента (кг)</label>
                        <input id="<?php echo esc_attr($cementUnitWeightFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('cementUnitWeightKg')); ?>" min="0.001" step="0.001" value="50" data-mixture-unit-input="cement">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.cementUnitWeightKg'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($cementUnitPriceFieldId); ?>" data-mixture-price-label="cement">Цена единицы цемента</label>
                        <input id="<?php echo esc_attr($cementUnitPriceFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('cementUnitPrice')); ?>" min="1" step="1" value="500">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.cementUnitPrice'); ?>" aria-live="polite"></div>
                    </div>
                </div>

                <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($sandShareFieldId); ?>">Доля песка</label>
                        <input id="<?php echo esc_attr($sandShareFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('sandShare')); ?>" min="0.1" step="0.1" value="2">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.sandShare'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($sandUnitTypeFieldId); ?>">Единица покупки песка</label>
                        <select id="<?php echo esc_attr($sandUnitTypeFieldId); ?>" name="<?php echo esc_attr($buildName('sandPurchaseUnit')); ?>">
                            <option value="tonne">Тонна</option>
                            <option value="bag">Мешок</option>
                        </select>
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.sandPurchaseUnit'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($sandUnitWeightFieldId); ?>" data-mixture-unit-label="sand">Вес единицы песка (т)</label>
                        <input id="<?php echo esc_attr($sandUnitWeightFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('sandUnitWeightKg')); ?>" min="0.001" step="0.001" value="1" data-mixture-unit-input="sand">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.sandUnitWeightKg'); ?>" aria-live="polite"></div>
                    </div>
                    <div class="brigmaster-estimator__field">
                        <label for="<?php echo esc_attr($sandUnitPriceFieldId); ?>" data-mixture-price-label="sand">Цена единицы песка</label>
                        <input id="<?php echo esc_attr($sandUnitPriceFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('sandUnitPrice')); ?>" min="1" step="1" value="1200">
                        <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.sandUnitPrice'); ?>" aria-live="polite"></div>
                    </div>
                </div>

                <?php if ($includeGravel) : ?>
                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($gravelShareFieldId); ?>">Доля щебня</label>
                            <input id="<?php echo esc_attr($gravelShareFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('gravelShare')); ?>" min="0.1" step="0.1" value="4">
                            <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.gravelShare'); ?>" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($gravelUnitTypeFieldId); ?>">Единица покупки щебня</label>
                            <select id="<?php echo esc_attr($gravelUnitTypeFieldId); ?>" name="<?php echo esc_attr($buildName('gravelPurchaseUnit')); ?>">
                                <option value="tonne">Тонна</option>
                                <option value="bag">Мешок</option>
                            </select>
                            <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.gravelPurchaseUnit'); ?>" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($gravelUnitWeightFieldId); ?>" data-mixture-unit-label="gravel">Вес единицы щебня (т)</label>
                            <input id="<?php echo esc_attr($gravelUnitWeightFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('gravelUnitWeightKg')); ?>" min="0.001" step="0.001" value="1" data-mixture-unit-input="gravel">
                            <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.gravelUnitWeightKg'); ?>" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($gravelUnitPriceFieldId); ?>" data-mixture-price-label="gravel">Цена единицы щебня</label>
                            <input id="<?php echo esc_attr($gravelUnitPriceFieldId); ?>" type="number" name="<?php echo esc_attr($buildName('gravelUnitPrice')); ?>" min="1" step="1" value="1800">
                            <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($errorPrefix . '.gravelUnitPrice'); ?>" aria-live="polite"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <p class="brigmaster-estimator__hint">В расчёте используются справочные насыпные плотности: цемент 1300 кг/м³, песок 1600 кг/м³, щебень 1400 кг/м³. Вода считается по В/Ц = 0.5.</p>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    private function renderBrickEstimatorFields(string $instanceId): string
    {
        $brickFormatFieldId = $instanceId . 'brick-format';
        $brickLengthFieldId = $instanceId . 'brick-length-mm';
        $brickWidthFieldId = $instanceId . 'brick-width-mm';
        $brickHeightFieldId = $instanceId . 'brick-height-mm';
        $brickWeightFieldId = $instanceId . 'brick-weight-kg';
        $brickPriceFieldId = $instanceId . 'brick-price';
        $wallLengthFieldId = $instanceId . 'wall-length-m';
        $wallHeightFieldId = $instanceId . 'wall-height-m';
        $brickAreaFieldId = $instanceId . 'brick-area';
        $jointThicknessFieldId = $instanceId . 'joint-thickness-mm';
        $wallThicknessFieldId = $instanceId . 'wall-thickness-type';
        $reserveFieldId = $instanceId . 'reserve-percent';
        $includeOpeningsFieldId = $instanceId . 'include-openings';
        $includeGablesFieldId = $instanceId . 'include-gables';
        $includeMeshFieldId = $instanceId . 'include-masonry-mesh';
        $meshFrequencyFieldId = $instanceId . 'masonry-mesh-frequency';
        $cementShareFieldId = $instanceId . 'brick-cement-share';
        $sandShareFieldId = $instanceId . 'brick-sand-share';
        $cementUnitTypeFieldId = $instanceId . 'brick-cement-unit-type';
        $cementUnitWeightFieldId = $instanceId . 'brick-cement-unit-weight';
        $cementUnitPriceFieldId = $instanceId . 'brick-cement-unit-price';
        $sandUnitTypeFieldId = $instanceId . 'brick-sand-unit-type';
        $sandUnitWeightFieldId = $instanceId . 'brick-sand-unit-weight';
        $sandUnitPriceFieldId = $instanceId . 'brick-sand-unit-price';

        ob_start();
        ?>
        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="brick-main">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($brickFormatFieldId); ?>">Формат кирпича</label>
                <select id="<?php echo esc_attr($brickFormatFieldId); ?>" name="brickFormat" data-brick-format-select>
                    <option value="single_nf" data-brick-length="250" data-brick-width="120" data-brick-height="65">1 НФ (250×120×65 мм)</option>
                    <option value="one_and_half_nf" data-brick-length="250" data-brick-width="120" data-brick-height="88">1.4 НФ (250×120×88 мм)</option>
                    <option value="double_nf" data-brick-length="250" data-brick-width="120" data-brick-height="140">2.1 НФ (250×120×140 мм)</option>
                    <option value="euro_nf" data-brick-length="250" data-brick-width="85" data-brick-height="65">Евро (250×85×65 мм)</option>
                    <option value="custom">Свой размер</option>
                </select>
                <p class="brigmaster-estimator__hint">Стандартные размеры подставляются автоматически. Свой формат можно ввести вручную.</p>
                <div class="brigmaster-estimator__error" data-field-error="brickFormat" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($wallThicknessFieldId); ?>">Толщина стены</label>
                <select id="<?php echo esc_attr($wallThicknessFieldId); ?>" name="wallThicknessType">
                    <option value="half_brick">В 0.5 кирпича</option>
                    <option value="one_brick">В 1 кирпич</option>
                    <option value="one_and_half_bricks" selected>В 1.5 кирпича</option>
                    <option value="two_bricks">В 2 кирпича</option>
                    <option value="two_and_half_bricks">В 2.5 кирпича</option>
                </select>
                <p class="brigmaster-estimator__hint">Толщина автоматически учитывает выбранный формат кирпича и растворный шов.</p>
                <div class="brigmaster-estimator__error" data-field-error="wallThicknessType" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="brick-size">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($brickLengthFieldId); ?>">Длина кирпича (мм)</label>
                <input id="<?php echo esc_attr($brickLengthFieldId); ?>" type="number" name="brickLengthMm" min="1" step="1" value="250" data-brick-size-input="length">
                <div class="brigmaster-estimator__error" data-field-error="brickLengthMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($brickWidthFieldId); ?>">Ширина кирпича (мм)</label>
                <input id="<?php echo esc_attr($brickWidthFieldId); ?>" type="number" name="brickWidthMm" min="1" step="1" value="120" data-brick-size-input="width">
                <div class="brigmaster-estimator__error" data-field-error="brickWidthMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($brickHeightFieldId); ?>">Высота кирпича (мм)</label>
                <input id="<?php echo esc_attr($brickHeightFieldId); ?>" type="number" name="brickHeightMm" min="1" step="1" value="65" data-brick-size-input="height">
                <div class="brigmaster-estimator__error" data-field-error="brickHeightMm" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="brick-geometry-dimensions">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($wallLengthFieldId); ?>">Общая длина стен (м)</label>
                <input id="<?php echo esc_attr($wallLengthFieldId); ?>" type="number" name="wallLengthM" min="0.01" step="0.01" value="30">
                <p class="brigmaster-estimator__hint">Сумма длин всех стен. Например: 7 + 7 + 8 + 8 = 30 м.</p>
                <div class="brigmaster-estimator__error" data-field-error="wallLengthM" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-group--hidden" data-field-group="brick-geometry-area">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($brickAreaFieldId); ?>">Площадь стен (м²)</label>
                <input id="<?php echo esc_attr($brickAreaFieldId); ?>" type="number" name="area" min="0.01" step="0.01" value="90">
                <p class="brigmaster-estimator__hint">Указывайте полную площадь стен без вычета проёмов, если планируете учесть их ниже.</p>
                <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="brick-common-geometry">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($jointThicknessFieldId); ?>">Толщина шва (мм)</label>
                <input id="<?php echo esc_attr($jointThicknessFieldId); ?>" type="number" name="jointThicknessMm" min="1" step="1" value="10">
                <p class="brigmaster-estimator__hint">Чаще всего 8-12 мм. Шов влияет на количество кирпича и объём раствора.</p>
                <div class="brigmaster-estimator__error" data-field-error="jointThicknessMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($instanceId . 'wall-height-common'); ?>">Средняя высота стен (м)</label>
                <input id="<?php echo esc_attr($instanceId . 'wall-height-common'); ?>" type="number" name="wallHeightM" min="0.01" step="0.01" value="3" data-brick-wall-height>
                <p class="brigmaster-estimator__hint">Если высота стен разная, укажите среднее значение по всем стенам.</p>
                <div class="brigmaster-estimator__error" data-field-error="wallHeightM" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="brick-economics">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($reserveFieldId); ?>">Запас (%)</label>
                <input id="<?php echo esc_attr($reserveFieldId); ?>" type="number" name="reservePercent" min="1" step="1" value="5">
                <p class="brigmaster-estimator__hint">Обычно 5-10% на бой, подрезку и подгонку кладки.</p>
                <div class="brigmaster-estimator__error" data-field-error="reservePercent" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($brickWeightFieldId); ?>">Масса 1 кирпича (кг)</label>
                <input id="<?php echo esc_attr($brickWeightFieldId); ?>" type="number" name="brickWeightKg" min="0.1" step="0.1" placeholder="Например, 3.5">
                <p class="brigmaster-estimator__hint">Необязательно. Если поле пустое, калькулятор покажет ориентир по габаритам кирпича.</p>
                <div class="brigmaster-estimator__error" data-field-error="brickWeightKg" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($brickPriceFieldId); ?>">Цена 1 кирпича</label>
                <input id="<?php echo esc_attr($brickPriceFieldId); ?>" type="number" name="brickPricePerUnit" min="0.01" step="0.01" placeholder="Необязательно">
                <p class="brigmaster-estimator__hint">Если цена не указана, блок со стоимостью кирпича не выводится.</p>
                <div class="brigmaster-estimator__error" data-field-error="brickPricePerUnit" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__accordions brigmaster-estimator__accordions--brick" data-estimator-accordions>
            <details class="brigmaster-estimator__accordion" open>
                <summary class="brigmaster-estimator__accordion-summary">Проёмы и фронтоны<?php echo $this->accordionChevronMarkup(); ?></summary>
                <div class="brigmaster-estimator__accordion-body">
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two">
                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                            <input id="<?php echo esc_attr($includeOpeningsFieldId); ?>" type="checkbox" name="includeOpenings" value="1">
                            <label for="<?php echo esc_attr($includeOpeningsFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Вычесть окна и двери</span>
                            </label>
                            <div class="brigmaster-estimator__error" data-field-error="includeOpenings" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                            <input id="<?php echo esc_attr($includeGablesFieldId); ?>" type="checkbox" name="includeGables" value="1">
                            <label for="<?php echo esc_attr($includeGablesFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Учесть фронтоны</span>
                            </label>
                            <div class="brigmaster-estimator__error" data-field-error="includeGables" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-brick-openings-root>
                        <?php echo $this->renderBrickRepeatableGroup($instanceId, 'windows', 'Окна', 'window'); ?>
                        <?php echo $this->renderBrickRepeatableGroup($instanceId, 'doors', 'Двери', 'door'); ?>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-brick-gables-root>
                        <p class="brigmaster-estimator__hint brigmaster-estimator__hint--accordion">
                            Фронтон считается как треугольник по формуле `0.5 x ширина x высота`. Угол отдельно не задаётся: он автоматически определяется этими двумя размерами.
                        </p>
                        <?php echo $this->renderBrickRepeatableGroup($instanceId, 'gables', 'Фронтоны', 'gable'); ?>
                    </div>
                </div>
            </details>

            <details class="brigmaster-estimator__accordion" open>
                <summary class="brigmaster-estimator__accordion-summary">Раствор и кладочная сетка<?php echo $this->accordionChevronMarkup(); ?></summary>
                <div class="brigmaster-estimator__accordion-body">
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two">
                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                            <input id="<?php echo esc_attr($includeMeshFieldId); ?>" type="checkbox" name="includeMasonryMesh" value="1">
                            <label for="<?php echo esc_attr($includeMeshFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Учитывать кладочную сетку</span>
                            </label>
                            <div class="brigmaster-estimator__error" data-field-error="includeMasonryMesh" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($meshFrequencyFieldId); ?>">Шаг сетки по рядам</label>
                            <select id="<?php echo esc_attr($meshFrequencyFieldId); ?>" name="masonryMeshFrequencyRows">
                                <option value="1">Каждый ряд</option>
                                <option value="2">Каждый 2 ряд</option>
                                <option value="3" selected>Каждый 3 ряд</option>
                                <option value="4">Каждый 4 ряд</option>
                                <option value="5">Каждый 5 ряд</option>
                            </select>
                            <p class="brigmaster-estimator__hint">Для частного дома чаще применяют армирование через 3-5 рядов.</p>
                            <div class="brigmaster-estimator__error" data-field-error="masonryMeshFrequencyRows" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four" data-brick-mortar-ratio-fields>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($cementShareFieldId); ?>">Доля цемента</label>
                            <input id="<?php echo esc_attr($cementShareFieldId); ?>" type="number" name="cementShare" min="0.1" step="0.1" value="1">
                            <div class="brigmaster-estimator__error" data-field-error="cementShare" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($cementUnitTypeFieldId); ?>">Единица покупки цемента</label>
                            <select id="<?php echo esc_attr($cementUnitTypeFieldId); ?>" name="cementPurchaseUnit">
                                <option value="bag">Мешок</option>
                                <option value="tonne">Тонна</option>
                            </select>
                            <div class="brigmaster-estimator__error" data-field-error="cementPurchaseUnit" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($cementUnitWeightFieldId); ?>" data-mixture-unit-label="cement">Вес единицы цемента (кг)</label>
                            <input id="<?php echo esc_attr($cementUnitWeightFieldId); ?>" type="number" name="cementUnitWeightKg" min="0.001" step="0.001" value="50" data-mixture-unit-input="cement">
                            <div class="brigmaster-estimator__error" data-field-error="cementUnitWeightKg" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($cementUnitPriceFieldId); ?>" data-mixture-price-label="cement">Цена мешка цемента</label>
                            <input id="<?php echo esc_attr($cementUnitPriceFieldId); ?>" type="number" name="cementUnitPrice" min="0.01" step="0.01" placeholder="Необязательно">
                            <div class="brigmaster-estimator__error" data-field-error="cementUnitPrice" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($sandShareFieldId); ?>">Доля песка</label>
                            <input id="<?php echo esc_attr($sandShareFieldId); ?>" type="number" name="sandShare" min="0.1" step="0.1" value="4">
                            <div class="brigmaster-estimator__error" data-field-error="sandShare" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($sandUnitTypeFieldId); ?>">Единица покупки песка</label>
                            <select id="<?php echo esc_attr($sandUnitTypeFieldId); ?>" name="sandPurchaseUnit">
                                <option value="tonne">Тонна</option>
                                <option value="bag">Мешок</option>
                            </select>
                            <div class="brigmaster-estimator__error" data-field-error="sandPurchaseUnit" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($sandUnitWeightFieldId); ?>" data-mixture-unit-label="sand">Вес единицы песка (т)</label>
                            <input id="<?php echo esc_attr($sandUnitWeightFieldId); ?>" type="number" name="sandUnitWeightKg" min="0.001" step="0.001" value="1" data-mixture-unit-input="sand">
                            <div class="brigmaster-estimator__error" data-field-error="sandUnitWeightKg" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($sandUnitPriceFieldId); ?>" data-mixture-price-label="sand">Цена тонны песка</label>
                            <input id="<?php echo esc_attr($sandUnitPriceFieldId); ?>" type="number" name="sandUnitPrice" min="0.01" step="0.01" placeholder="Необязательно">
                            <div class="brigmaster-estimator__error" data-field-error="sandUnitPrice" aria-live="polite"></div>
                        </div>
                    </div>
                </div>
            </details>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    private function renderBrickRepeatableGroup(string $instanceId, string $group, string $title, string $type): string
    {
        $addButtonId = $instanceId . $group . '-add';
        $listId = $instanceId . $group . '-list';
        $defaultWidth = $type === 'door' ? '0.9' : '1.2';
        $defaultHeight = $type === 'door' ? '2.1' : '1.4';
        $defaultCount = '1';
        $itemTitle = match ($group) {
            'windows' => 'Окно',
            'doors' => 'Дверь',
            'gables' => 'Фронтон',
            default => 'Элемент',
        };

        ob_start();
        ?>
        <div class="brigmaster-estimator__repeatable-group">
            <div class="brigmaster-estimator__segment-head">
                <h3 class="brigmaster-estimator__segment-title"><?php echo esc_html($title); ?></h3>
                <button id="<?php echo esc_attr($addButtonId); ?>" type="button" class="brigmaster-estimator__segment-add" data-brick-add-item="<?php echo esc_attr($group); ?>">
                    Добавить размер
                </button>
            </div>
            <div id="<?php echo esc_attr($listId); ?>" class="brigmaster-estimator__segment-list" data-brick-repeat-list="<?php echo esc_attr($group); ?>" data-brick-item-type="<?php echo esc_attr($type); ?>">
                <article class="brigmaster-estimator__segment-card" data-brick-repeat-item data-brick-item-type="<?php echo esc_attr($type); ?>" data-brick-group="<?php echo esc_attr($group); ?>">
                    <div class="brigmaster-estimator__segment-head">
                        <h4 class="brigmaster-estimator__segment-title"><?php echo esc_html($itemTitle); ?> 1</h4>
                        <button type="button" class="brigmaster-estimator__segment-remove" data-brick-remove-item>Удалить</button>
                    </div>
                    <div class="brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($listId . '-0-width'); ?>">Ширина (м)</label>
                            <input id="<?php echo esc_attr($listId . '-0-width'); ?>" type="number" min="0.01" step="0.01" value="<?php echo esc_attr($defaultWidth); ?>" data-brick-repeat-input="widthM" name="<?php echo esc_attr($group . '[0][widthM]'); ?>">
                            <div class="brigmaster-estimator__error" data-brick-error-field="widthM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($listId . '-0-height'); ?>">Высота (м)</label>
                            <input id="<?php echo esc_attr($listId . '-0-height'); ?>" type="number" min="0.01" step="0.01" value="<?php echo esc_attr($defaultHeight); ?>" data-brick-repeat-input="heightM" name="<?php echo esc_attr($group . '[0][heightM]'); ?>">
                            <div class="brigmaster-estimator__error" data-brick-error-field="heightM" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($listId . '-0-count'); ?>">Количество</label>
                            <input id="<?php echo esc_attr($listId . '-0-count'); ?>" type="number" min="1" step="1" value="<?php echo esc_attr($defaultCount); ?>" data-brick-repeat-input="count" name="<?php echo esc_attr($group . '[0][count]'); ?>">
                            <div class="brigmaster-estimator__error" data-brick-error-field="count" aria-live="polite"></div>
                        </div>
                    </div>
                </article>
            </div>
            <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($group); ?>" aria-live="polite"></div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    private function renderBrickResultTemplate(): string
    {
        ob_start();
        ?>
        <div class="brigmaster-estimator__result-grid brigmaster-estimator__result-grid--brick">
            <section class="brigmaster-estimator__result-card" data-result-card="brick-summary"></section>
            <section class="brigmaster-estimator__result-card" data-result-card="brick-geometry"></section>
            <section class="brigmaster-estimator__result-card" data-result-card="brick-mortar"></section>
            <section class="brigmaster-estimator__result-card" data-result-card="brick-mesh" hidden></section>
            <section class="brigmaster-estimator__result-card" data-result-card="brick-lintels" hidden></section>
            <section class="brigmaster-estimator__result-card" data-result-card="brick-costs" hidden></section>
        </div>
        <section class="brigmaster-estimator__result-card brigmaster-estimator__result-card--wide" data-result-card="brick-armopoyas-note">
            <h3>Армопояс</h3>
            <p>Для кирпичных стен армопояс удобнее считать в калькуляторе ленточного фундамента.</p>
            <p>
                <a href="<?php echo esc_url(home_url('/kalkulyator-lentochnogo-fundamenta/')); ?>">Открыть калькулятор ленточного фундамента</a>
            </p>
            <p class="brigmaster-estimator__result-note">Укажите периметр стен, ширину по толщине стены и высоту пояса 0.2-0.3 м.</p>
        </section>
        <?php

        return (string) ob_get_clean();
    }

    private function renderDrywallEstimatorFields(string $instanceId): string
    {
        $targetFieldId = $instanceId . 'drywall-target';
        $sheetFormatFieldId = $instanceId . 'drywall-sheet-format';
        $sheetLengthFieldId = $instanceId . 'drywall-sheet-length';
        $sheetWidthFieldId = $instanceId . 'drywall-sheet-width';
        $sheetThicknessFieldId = $instanceId . 'drywall-sheet-thickness';
        $layersFieldId = $instanceId . 'drywall-layers';
        $stepFieldId = $instanceId . 'drywall-step';
        $profileWidthFieldId = $instanceId . 'drywall-profile-width';
        $reserveFieldId = $instanceId . 'drywall-reserve';
        $fastenerReserveFieldId = $instanceId . 'drywall-fastener-reserve';
        $includeOpeningsFieldId = $instanceId . 'drywall-include-openings';
        $includeEndCladdingFieldId = $instanceId . 'drywall-include-end-cladding';
        $includeFinishingFieldId = $instanceId . 'drywall-include-finishing';
        $includeCostsFieldId = $instanceId . 'drywall-include-costs';
        $sheetPriceFieldId = $instanceId . 'drywall-sheet-price';
        $profilePriceFieldId = $instanceId . 'drywall-profile-price';
        $fastenerPriceFieldId = $instanceId . 'drywall-fastener-price';
        $primerPriceFieldId = $instanceId . 'drywall-primer-price';
        $jointPuttyPriceFieldId = $instanceId . 'drywall-joint-putty-price';
        $finishPuttyPriceFieldId = $instanceId . 'drywall-finish-putty-price';
        $tapePriceFieldId = $instanceId . 'drywall-tape-price';

        ob_start();
        ?>
        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($targetFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Тип конструкции</span>
                    <?php echo $this->renderEstimatorTooltip($instanceId . 'drywall-target-tooltip', 'Стена — облицовка по одной плоскости. Потолок — подвесной каркас. Перегородка — двусторонняя конструкция из профиля и листов.'); ?>
                </label>
                <select id="<?php echo esc_attr($targetFieldId); ?>" name="drywallTarget" data-drywall-target-select>
                    <option value="wall">Стена</option>
                    <option value="ceiling">Потолок</option>
                    <option value="partition">Перегородка</option>
                </select>
                <div class="brigmaster-estimator__error" data-field-error="drywallTarget" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($sheetFormatFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Формат листа</span>
                    <?php echo $this->renderEstimatorTooltip($instanceId . 'drywall-sheet-format-tooltip', 'Можно выбрать стандартный размер листа или задать свой. Это влияет на количество листов и количество поперечных перемычек.'); ?>
                </label>
                <select id="<?php echo esc_attr($sheetFormatFieldId); ?>" data-drywall-sheet-format-select>
                    <option value="2500x1200" data-sheet-length="2500" data-sheet-width="1200">2500×1200 мм</option>
                    <option value="3000x1200" data-sheet-length="3000" data-sheet-width="1200">3000×1200 мм</option>
                    <option value="2000x1200" data-sheet-length="2000" data-sheet-width="1200">2000×1200 мм</option>
                    <option value="custom">Свой размер</option>
                </select>
            </div>
        </div>

        <div class="brigmaster-estimator__accordions" data-estimator-accordions>
            <details class="brigmaster-estimator__accordion" open>
                <summary class="brigmaster-estimator__accordion-summary">Геометрия<?php echo $this->accordionChevronMarkup(); ?></summary>
                <div class="brigmaster-estimator__accordion-body">
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two" data-field-group="drywall-wall-dimensions">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($instanceId . 'drywall-length'); ?>" class="brigmaster-estimator__label-row">
                                <span data-drywall-length-label>Длина стены (м)</span>
                                <?php echo $this->renderEstimatorTooltip($instanceId . 'drywall-length-tooltip', 'Если нужно посчитать комнату целиком, сложите длины всех стен и введите общую сумму.'); ?>
                            </label>
                            <input id="<?php echo esc_attr($instanceId . 'drywall-length'); ?>" type="number" name="drywallLength" min="0.01" step="0.01" value="6">
                            <p class="brigmaster-estimator__hint" data-drywall-length-hint>Для комнаты можно указать суммарную длину всех стен.</p>
                            <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($instanceId . 'drywall-height'); ?>">Высота (м)</label>
                            <input id="<?php echo esc_attr($instanceId . 'drywall-height'); ?>" type="number" name="drywallHeight" min="0.01" step="0.01" value="2.7">
                            <div class="brigmaster-estimator__error" data-field-error="height" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two brigmaster-estimator__field-group--hidden" data-field-group="drywall-ceiling-dimensions">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($instanceId . 'drywall-ceiling-length'); ?>">Длина помещения (м)</label>
                            <input id="<?php echo esc_attr($instanceId . 'drywall-ceiling-length'); ?>" type="number" name="drywallCeilingLength" min="0.01" step="0.01" value="6">
                            <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($instanceId . 'drywall-ceiling-width'); ?>">Ширина помещения (м)</label>
                            <input id="<?php echo esc_attr($instanceId . 'drywall-ceiling-width'); ?>" type="number" name="drywallCeilingWidth" min="0.01" step="0.01" value="4">
                            <div class="brigmaster-estimator__error" data-field-error="width" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="drywall-area">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($instanceId . 'drywall-area'); ?>" class="brigmaster-estimator__label-row">
                                <span>Площадь конструкции (м²)</span>
                                <?php echo $this->renderEstimatorTooltip($instanceId . 'drywall-area-tooltip', 'В режиме по площади калькулятор точно считает листы и отделку, но не считает профили и крепёж по каркасу.'); ?>
                            </label>
                            <input id="<?php echo esc_attr($instanceId . 'drywall-area'); ?>" type="number" name="drywallArea" min="0.01" step="0.01" value="20">
                            <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle" data-field-group="drywall-openings-toggle">
                        <input id="<?php echo esc_attr($includeOpeningsFieldId); ?>" type="checkbox" name="includeOpenings" value="1">
                        <label for="<?php echo esc_attr($includeOpeningsFieldId); ?>" class="brigmaster-estimator__label-row">
                            <span>Учесть проёмы</span>
                            <?php echo $this->renderEstimatorTooltip($instanceId . 'drywall-openings-tooltip', 'Проёмы уменьшают чистую площадь обшивки. Для потолка блок скрывается, потому что геометрия там другая.'); ?>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="includeOpenings" aria-live="polite"></div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-drywall-openings-root>
                        <?php echo $this->renderDrywallRepeatableGroup($instanceId, 'drywallOpenings', 'Окна и двери'); ?>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field brigmaster-estimator__toggle brigmaster-estimator__field-group--hidden" data-field-group="drywall-end-cladding-toggle">
                        <input id="<?php echo esc_attr($includeEndCladdingFieldId); ?>" type="checkbox" name="drywallIncludeEndCladding" value="1">
                        <label for="<?php echo esc_attr($includeEndCladdingFieldId); ?>" class="brigmaster-estimator__label-row">
                            <span>Учесть облицовку торцов проёмов</span>
                            <?php echo $this->renderEstimatorTooltip($instanceId . 'drywall-end-cladding-tooltip', 'Актуально для перегородок. В расчёт добавляются полосы ГКЛ по толщине перегородки на боковые и верхние откосы.'); ?>
                        </label>
                        <div class="brigmaster-estimator__error" data-field-error="drywallIncludeEndCladding" aria-live="polite"></div>
                    </div>
                </div>
            </details>

            <details class="brigmaster-estimator__accordion" open>
                <summary class="brigmaster-estimator__accordion-summary">Листы и каркас<?php echo $this->accordionChevronMarkup(); ?></summary>
                <div class="brigmaster-estimator__accordion-body">
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($sheetLengthFieldId); ?>">Длина листа (мм)</label>
                            <input id="<?php echo esc_attr($sheetLengthFieldId); ?>" type="number" name="drywallSheetLengthMm" min="1" step="1" value="2500" data-drywall-sheet-length>
                            <div class="brigmaster-estimator__error" data-field-error="drywallSheetLengthMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($sheetWidthFieldId); ?>">Ширина листа (мм)</label>
                            <input id="<?php echo esc_attr($sheetWidthFieldId); ?>" type="number" name="drywallSheetWidthMm" min="1" step="1" value="1200" data-drywall-sheet-width>
                            <div class="brigmaster-estimator__error" data-field-error="drywallSheetWidthMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($sheetThicknessFieldId); ?>">Толщина листа (мм)</label>
                            <input id="<?php echo esc_attr($sheetThicknessFieldId); ?>" type="number" name="drywallSheetThicknessMm" min="0.1" step="0.1" value="12.5">
                            <div class="brigmaster-estimator__error" data-field-error="drywallSheetThicknessMm" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($layersFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Слоёв обшивки</span>
                                <?php echo $this->renderEstimatorTooltip($instanceId . 'drywall-layers-tooltip', 'Для перегородки значение применяется к каждой стороне. Один слой подходит для простых задач, два — когда нужна более жёсткая конструкция.'); ?>
                            </label>
                            <select id="<?php echo esc_attr($layersFieldId); ?>" name="drywallLayers">
                                <option value="1">1 слой</option>
                                <option value="2">2 слоя</option>
                            </select>
                            <div class="brigmaster-estimator__error" data-field-error="drywallLayers" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($stepFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Шаг профиля (мм)</span>
                                <?php echo $this->renderEstimatorTooltip($instanceId . 'drywall-step-tooltip', 'Чем меньше шаг, тем жёстче каркас и выше расход профиля. Для большинства бытовых конструкций берут 400 или 600 мм.'); ?>
                            </label>
                            <select id="<?php echo esc_attr($stepFieldId); ?>" name="drywallFrameStepMm">
                                <option value="600">600</option>
                                <option value="400">400</option>
                            </select>
                            <div class="brigmaster-estimator__error" data-field-error="drywallFrameStepMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field brigmaster-estimator__field-group--hidden" data-field-group="drywall-profile-width">
                            <label for="<?php echo esc_attr($profileWidthFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Ширина профиля перегородки (мм)</span>
                                <?php echo $this->renderEstimatorTooltip($instanceId . 'drywall-profile-width-tooltip', 'Профиль определяет базовую толщину каркаса. В результате калькулятор также покажет ориентировочную итоговую толщину перегородки с учётом слоёв ГКЛ.'); ?>
                            </label>
                            <select id="<?php echo esc_attr($profileWidthFieldId); ?>" name="drywallProfileWidthMm">
                                <option value="50">50</option>
                                <option value="75">75</option>
                                <option value="100">100</option>
                            </select>
                            <div class="brigmaster-estimator__error" data-field-error="drywallProfileWidthMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($reserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Запас на листы и профиль (%)</span>
                                <?php echo $this->renderEstimatorTooltip($instanceId . 'drywall-reserve-tooltip', 'Запас компенсирует подрезку, подгонку листов и добор профиля на сложных участках.'); ?>
                            </label>
                            <input id="<?php echo esc_attr($reserveFieldId); ?>" type="number" name="reservePercent" min="1" step="1" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="reservePercent" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($fastenerReserveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Запас на метизы (%)</span>
                                <?php echo $this->renderEstimatorTooltip($instanceId . 'drywall-fastener-reserve-tooltip', 'Запас применяется ко всем штучным позициям: саморезам, дюбелям, подвесам и соединителям.'); ?>
                            </label>
                            <input id="<?php echo esc_attr($fastenerReserveFieldId); ?>" type="number" name="drywallFastenerReservePercent" min="1" step="1" value="10">
                            <div class="brigmaster-estimator__error" data-field-error="drywallFastenerReservePercent" aria-live="polite"></div>
                        </div>
                    </div>
                </div>
            </details>

            <details class="brigmaster-estimator__accordion" open>
                <summary class="brigmaster-estimator__accordion-summary">Отделка и стоимость<?php echo $this->accordionChevronMarkup(); ?></summary>
                <div class="brigmaster-estimator__accordion-body">
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two">
                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                            <input id="<?php echo esc_attr($includeFinishingFieldId); ?>" type="checkbox" name="drywallIncludeFinishing" value="1">
                            <label for="<?php echo esc_attr($includeFinishingFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Учесть отделку</span>
                                <?php echo $this->renderEstimatorTooltip($instanceId . 'drywall-finishing-tooltip', 'Добавляет ориентир по грунтовке, шпатлёвке для швов, финишной шпатлёвке и армирующей ленте.'); ?>
                            </label>
                            <div class="brigmaster-estimator__error" data-field-error="drywallIncludeFinishing" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                            <input id="<?php echo esc_attr($includeCostsFieldId); ?>" type="checkbox" name="drywallIncludeCosts" value="1">
                            <label for="<?php echo esc_attr($includeCostsFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Посчитать стоимость</span>
                                <?php echo $this->renderEstimatorTooltip($instanceId . 'drywall-costs-tooltip', 'Все ценовые поля необязательны. Если заполнить только часть из них, в результате появятся только эти строки.'); ?>
                            </label>
                            <div class="brigmaster-estimator__error" data-field-error="drywallIncludeCosts" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group--hidden" data-drywall-costs-root>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($sheetPriceFieldId); ?>">Цена листа ГКЛ</label>
                            <input id="<?php echo esc_attr($sheetPriceFieldId); ?>" type="number" name="drywallSheetPrice" min="0.01" step="0.01" placeholder="Необязательно">
                            <div class="brigmaster-estimator__error" data-field-error="drywallSheetPrice" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($profilePriceFieldId); ?>">Цена профиля за м</label>
                            <input id="<?php echo esc_attr($profilePriceFieldId); ?>" type="number" name="drywallProfilePricePerLm" min="0.01" step="0.01" placeholder="Необязательно">
                            <div class="brigmaster-estimator__error" data-field-error="drywallProfilePricePerLm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($fastenerPriceFieldId); ?>">Цена метизов за 100 шт</label>
                            <input id="<?php echo esc_attr($fastenerPriceFieldId); ?>" type="number" name="drywallFastenerPricePer100" min="0.01" step="0.01" placeholder="Необязательно">
                            <div class="brigmaster-estimator__error" data-field-error="drywallFastenerPricePer100" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group--hidden" data-drywall-finishing-costs-root>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($primerPriceFieldId); ?>">Цена грунтовки за кг</label>
                            <input id="<?php echo esc_attr($primerPriceFieldId); ?>" type="number" name="drywallPrimerPricePerKg" min="0.01" step="0.01" placeholder="Необязательно">
                            <div class="brigmaster-estimator__error" data-field-error="drywallPrimerPricePerKg" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($jointPuttyPriceFieldId); ?>">Цена шпатлёвки для швов за кг</label>
                            <input id="<?php echo esc_attr($jointPuttyPriceFieldId); ?>" type="number" name="drywallJointPuttyPricePerKg" min="0.01" step="0.01" placeholder="Необязательно">
                            <div class="brigmaster-estimator__error" data-field-error="drywallJointPuttyPricePerKg" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($finishPuttyPriceFieldId); ?>">Цена финишной шпатлёвки за кг</label>
                            <input id="<?php echo esc_attr($finishPuttyPriceFieldId); ?>" type="number" name="drywallFinishPuttyPricePerKg" min="0.01" step="0.01" placeholder="Необязательно">
                            <div class="brigmaster-estimator__error" data-field-error="drywallFinishPuttyPricePerKg" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($tapePriceFieldId); ?>">Цена ленты за м</label>
                            <input id="<?php echo esc_attr($tapePriceFieldId); ?>" type="number" name="drywallTapePricePerLm" min="0.01" step="0.01" placeholder="Необязательно">
                            <div class="brigmaster-estimator__error" data-field-error="drywallTapePricePerLm" aria-live="polite"></div>
                        </div>
                    </div>
                </div>
            </details>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    private function renderDrywallRepeatableGroup(string $instanceId, string $group, string $title): string
    {
        $addButtonId = $instanceId . '-' . $group . '-add';
        $listId = $instanceId . '-' . $group . '-list';

        ob_start();
        ?>
        <div class="brigmaster-estimator__repeatable-group">
            <div class="brigmaster-estimator__segment-head">
                <h3 class="brigmaster-estimator__segment-title"><?php echo esc_html($title); ?></h3>
                <button id="<?php echo esc_attr($addButtonId); ?>" type="button" class="brigmaster-estimator__segment-add" data-drywall-add-item="<?php echo esc_attr($group); ?>">
                    Добавить проём
                </button>
            </div>
            <p class="brigmaster-estimator__hint">Используйте этот блок для окон и дверей. Для перегородки можно добавить и оконные, и дверные проёмы.</p>
            <div id="<?php echo esc_attr($listId); ?>" class="brigmaster-estimator__segment-list" data-drywall-repeat-list="<?php echo esc_attr($group); ?>"></div>
            <div class="brigmaster-estimator__error" data-field-error="windows" aria-live="polite"></div>
            <div class="brigmaster-estimator__error" data-field-error="doors" aria-live="polite"></div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    private function renderDrywallResultTemplate(): string
    {
        ob_start();
        ?>
        <div class="brigmaster-estimator__result-grid brigmaster-estimator__result-grid--drywall">
            <section class="brigmaster-estimator__result-card" data-result-card="drywall-geometry"></section>
            <section class="brigmaster-estimator__result-card" data-result-card="drywall-sheets"></section>
            <section class="brigmaster-estimator__result-card" data-result-card="drywall-profiles"></section>
            <section class="brigmaster-estimator__result-card" data-result-card="drywall-fasteners"></section>
            <section class="brigmaster-estimator__result-card" data-result-card="drywall-finishing" hidden></section>
            <section class="brigmaster-estimator__result-card" data-result-card="drywall-costs" hidden></section>
        </div>
        <section class="brigmaster-estimator__result-card brigmaster-estimator__result-card--wide" data-result-card="drywall-notes"></section>
        <?php

        return (string) ob_get_clean();
    }

    private function renderTileEstimatorFields(string $instanceId): string
    {
        $tileTargetFieldId = $instanceId . 'tile-target';
        $tilePatternFieldId = $instanceId . 'tile-pattern';
        $tileOffsetFieldId = $instanceId . 'tile-offset-percent';
        $tileLengthFieldId = $instanceId . 'tile-length-mm';
        $tileWidthFieldId = $instanceId . 'tile-width-mm';
        $tileThicknessFieldId = $instanceId . 'tile-thickness-mm';
        $tileJointFieldId = $instanceId . 'tile-joint-mm';
        $reserveFieldId = $instanceId . 'tile-reserve-percent';
        $tilePriceFieldId = $instanceId . 'tile-price-m2';
        $tileIncludeOpeningsFieldId = $instanceId . 'tile-include-openings';
        $tileIncludeCutoutsFieldId = $instanceId . 'tile-include-cutouts';
        $tileIncludeAdhesiveFieldId = $instanceId . 'tile-include-adhesive';
        $tileIncludeGroutFieldId = $instanceId . 'tile-include-grout';
        $tileAdhesiveConsumptionFieldId = $instanceId . 'tile-adhesive-consumption';
        $tileAdhesiveLayerFieldId = $instanceId . 'tile-adhesive-layer';
        $tileAdhesiveBagWeightFieldId = $instanceId . 'tile-adhesive-bag-weight';
        $tileAdhesiveBagPriceFieldId = $instanceId . 'tile-adhesive-bag-price';
        $tileGroutDensityFieldId = $instanceId . 'tile-grout-density';
        $tileGroutPackWeightFieldId = $instanceId . 'tile-grout-pack-weight';
        $tileGroutPackPriceFieldId = $instanceId . 'tile-grout-pack-price';

        ob_start();
        ?>
        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileTargetFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Что облицовываем</span>
                    <?php echo $this->renderTileTooltip($instanceId . 'tile-target-tooltip', 'Подбирает набор полей под пол или стены. В версии v1 ориентировочная раскладка рассчитана для прямоугольной зоны.'); ?>
                </label>
                <select id="<?php echo esc_attr($tileTargetFieldId); ?>" name="tileTarget" data-tile-target-select>
                    <option value="floor">Пол</option>
                    <option value="wall">Стены</option>
                </select>
                <div class="brigmaster-estimator__error" data-field-error="tileTarget" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tilePatternFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Способ укладки</span>
                    <?php echo $this->renderTileTooltip($instanceId . 'tile-pattern-tooltip', 'Прямая укладка обычно требует меньшего запаса. Смещение и диагональ повышают количество подрезки, поэтому калькулятор предлагает больший запас по умолчанию.'); ?>
                </label>
                <select id="<?php echo esc_attr($tilePatternFieldId); ?>" name="tileLayingPattern" data-tile-pattern-select>
                    <option value="direct">Прямая</option>
                    <option value="offset">Со смещением</option>
                    <option value="diagonal">Диагональная</option>
                </select>
                <div class="brigmaster-estimator__error" data-field-error="tileLayingPattern" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three" data-field-group="tile-dimensions">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($instanceId . 'tile-room-length'); ?>" class="brigmaster-estimator__label-row">
                    <span data-tile-length-label>Длина помещения (м)</span>
                    <?php echo $this->renderTileTooltip($instanceId . 'tile-length-tooltip', 'Для пола это длина пола, для стен это длина комнаты. При расчёте стен по размерам калькулятор строит прямоугольную развёртку по периметру.'); ?>
                </label>
                <input id="<?php echo esc_attr($instanceId . 'tile-room-length'); ?>" type="number" name="length" min="0.01" step="0.01" value="6">
                <div class="brigmaster-estimator__error" data-field-error="length" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($instanceId . 'tile-room-width'); ?>" class="brigmaster-estimator__label-row">
                    <span data-tile-width-label>Ширина помещения (м)</span>
                    <?php echo $this->renderTileTooltip($instanceId . 'tile-width-tooltip', 'Для стен нужна ширина комнаты, чтобы получить прямоугольный периметр. Для сложной формы помещения используйте результат как ориентир по материалам.'); ?>
                </label>
                <input id="<?php echo esc_attr($instanceId . 'tile-room-width'); ?>" type="number" name="width" min="0.01" step="0.01" value="4">
                <div class="brigmaster-estimator__error" data-field-error="width" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field brigmaster-estimator__field-group--hidden" data-field-group="tile-wall-height">
                <label for="<?php echo esc_attr($instanceId . 'tile-wall-height'); ?>" class="brigmaster-estimator__label-row">
                    <span>Высота стен (м)</span>
                    <?php echo $this->renderTileTooltip($instanceId . 'tile-height-tooltip', 'Используется только для стен. Для простой модели v1 все стены считаются как прямоугольная полоса по периметру.'); ?>
                </label>
                <input id="<?php echo esc_attr($instanceId . 'tile-wall-height'); ?>" type="number" name="height" min="0.01" step="0.01" value="2.7">
                <div class="brigmaster-estimator__error" data-field-error="height" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-field-group="tile-area">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($instanceId . 'tile-area'); ?>" class="brigmaster-estimator__label-row">
                    <span>Площадь облицовки (м²)</span>
                    <?php echo $this->renderTileTooltip($instanceId . 'tile-area-tooltip', 'Если точные размеры неизвестны, можно считать по площади. В этом режиме калькулятор точно считает ориентир по материалам, а ориентировочная раскладка отключается.'); ?>
                </label>
                <input id="<?php echo esc_attr($instanceId . 'tile-area'); ?>" type="number" name="area" min="0.01" step="0.01" value="24">
                <div class="brigmaster-estimator__error" data-field-error="area" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four">
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileLengthFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Длина плитки (мм)</span>
                    <?php echo $this->renderTileTooltip($instanceId . 'tile-size-length-tooltip', 'Размер одной плитки без шва. В расчёте количества и раскладки шов учитывается отдельно.'); ?>
                </label>
                <input id="<?php echo esc_attr($tileLengthFieldId); ?>" type="number" name="tileLengthMm" min="1" step="1" value="600">
                <div class="brigmaster-estimator__error" data-field-error="tileLengthMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileWidthFieldId); ?>">Ширина плитки (мм)</label>
                <input id="<?php echo esc_attr($tileWidthFieldId); ?>" type="number" name="tileWidthMm" min="1" step="1" value="600">
                <div class="brigmaster-estimator__error" data-field-error="tileWidthMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileThicknessFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Толщина плитки (мм)</span>
                    <?php echo $this->renderTileTooltip($instanceId . 'tile-thickness-tooltip', 'Нужна в первую очередь для расчёта затирки. Для стен по умолчанию подставляется 8 мм, для пола 9 мм.'); ?>
                </label>
                <input id="<?php echo esc_attr($tileThicknessFieldId); ?>" type="number" name="tileThicknessMm" min="1" step="1" value="9" data-tile-thickness-input>
                <div class="brigmaster-estimator__error" data-field-error="tileThicknessMm" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tileJointFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Ширина шва (мм)</span>
                    <?php echo $this->renderTileTooltip($instanceId . 'tile-joint-tooltip', 'Шов влияет и на ориентировочную раскладку, и на расход затирки. В расчёте количества плиток шов участвует как часть модуля раскладки.'); ?>
                </label>
                <input id="<?php echo esc_attr($tileJointFieldId); ?>" type="number" name="tileJointMm" min="1" step="0.1" value="2">
                <div class="brigmaster-estimator__error" data-field-error="tileJointMm" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three">
            <div class="brigmaster-estimator__field brigmaster-estimator__field-group--hidden" data-field-group="tile-offset">
                <label for="<?php echo esc_attr($tileOffsetFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Смещение (% длины плитки)</span>
                    <?php echo $this->renderTileTooltip($instanceId . 'tile-offset-tooltip', 'Нужно только для укладки со смещением. Значение 50% соответствует классическому сдвигу на половину плитки.'); ?>
                </label>
                <input id="<?php echo esc_attr($tileOffsetFieldId); ?>" type="number" name="tileOffsetPercent" min="1" step="1" value="50">
                <div class="brigmaster-estimator__error" data-field-error="tileOffsetPercent" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($reserveFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Запас (%)</span>
                    <?php echo $this->renderTileTooltip($instanceId . 'tile-reserve-tooltip', 'Рекомендуемый запас зависит от способа укладки: прямая обычно 5%, смещение 7%, диагональ 10% и выше. Значение можно изменить под свою задачу.'); ?>
                </label>
                <input id="<?php echo esc_attr($reserveFieldId); ?>" type="number" name="reservePercent" min="1" step="1" value="5" data-tile-reserve-input>
                <div class="brigmaster-estimator__error" data-field-error="reservePercent" aria-live="polite"></div>
            </div>
            <div class="brigmaster-estimator__field">
                <label for="<?php echo esc_attr($tilePriceFieldId); ?>" class="brigmaster-estimator__label-row">
                    <span>Цена плитки за м²</span>
                    <?php echo $this->renderTileTooltip($instanceId . 'tile-price-tooltip', 'Поле необязательно. Если цена не указана, карточка стоимости по плитке не выводится.'); ?>
                </label>
                <input id="<?php echo esc_attr($tilePriceFieldId); ?>" type="number" name="tilePricePerM2" min="0.01" step="0.01" placeholder="Необязательно">
                <div class="brigmaster-estimator__error" data-field-error="tilePricePerM2" aria-live="polite"></div>
            </div>
        </div>

        <div class="brigmaster-estimator__accordions brigmaster-estimator__accordions--tile" data-estimator-accordions>
            <details class="brigmaster-estimator__accordion" open>
                <summary class="brigmaster-estimator__accordion-summary">Проёмы, вырезы и отверстия<?php echo $this->accordionChevronMarkup(); ?></summary>
                <div class="brigmaster-estimator__accordion-body">
                    <p class="brigmaster-estimator__hint brigmaster-estimator__hint--accordion">
                        Проёмы используйте для окон и дверей на стенах. Вырезы и отверстия нужны для труб, розеток, лючков, трапов и других мест, где плитка подрезается внутри.
                    </p>
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two">
                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle" data-field-group="tile-openings-toggle">
                            <input id="<?php echo esc_attr($tileIncludeOpeningsFieldId); ?>" type="checkbox" name="tileIncludeOpenings" value="1">
                            <label for="<?php echo esc_attr($tileIncludeOpeningsFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Учесть окна и двери</span>
                                <?php echo $this->renderTileTooltip($instanceId . 'tile-openings-toggle-tooltip', 'Проёмы уменьшают чистую площадь облицовки. Подрезка вокруг проёмов отдельно не моделируется по координатам, поэтому запас всё равно нужен.'); ?>
                            </label>
                            <div class="brigmaster-estimator__error" data-field-error="tileIncludeOpenings" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                            <input id="<?php echo esc_attr($tileIncludeCutoutsFieldId); ?>" type="checkbox" name="tileIncludeCutouts" value="1">
                            <label for="<?php echo esc_attr($tileIncludeCutoutsFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Учесть вырезы и отверстия</span>
                                <?php echo $this->renderTileTooltip($instanceId . 'tile-cutouts-toggle-tooltip', 'Вырез уменьшает площадь, но часто съедает целую плитку. Поэтому калькулятор дополнительно прибавляет ориентир по потерям на каждый вырез.'); ?>
                            </label>
                            <div class="brigmaster-estimator__error" data-field-error="tileIncludeCutouts" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-tile-openings-root>
                        <?php echo $this->renderTileRepeatableGroup($instanceId, 'tileOpenings', 'Окна и двери', 'opening'); ?>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-group--hidden" data-tile-cutouts-root>
                        <?php echo $this->renderTileRepeatableGroup($instanceId, 'tileCutouts', 'Вырезы и отверстия в плитке', 'cutout'); ?>
                    </div>
                </div>
            </details>

            <details class="brigmaster-estimator__accordion" open>
                <summary class="brigmaster-estimator__accordion-summary">Клей и затирка<?php echo $this->accordionChevronMarkup(); ?></summary>
                <div class="brigmaster-estimator__accordion-body">
                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--two">
                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                            <input id="<?php echo esc_attr($tileIncludeAdhesiveFieldId); ?>" type="checkbox" name="tileIncludeAdhesive" value="1">
                            <label for="<?php echo esc_attr($tileIncludeAdhesiveFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Рассчитать клей</span>
                                <?php echo $this->renderTileTooltip($instanceId . 'tile-adhesive-tooltip', 'Расход клея справочный. Он зависит от размера плитки, основания, размера зуба шпателя и толщины слоя.'); ?>
                            </label>
                            <div class="brigmaster-estimator__error" data-field-error="tileIncludeAdhesive" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field brigmaster-estimator__toggle">
                            <input id="<?php echo esc_attr($tileIncludeGroutFieldId); ?>" type="checkbox" name="tileIncludeGrout" value="1">
                            <label for="<?php echo esc_attr($tileIncludeGroutFieldId); ?>" class="brigmaster-estimator__label-row">
                                <span>Рассчитать затирку</span>
                                <?php echo $this->renderTileTooltip($instanceId . 'tile-grout-tooltip', 'Затирка считается ориентировочно по размерам плитки, толщине плитки, ширине шва и плотности смеси.'); ?>
                            </label>
                            <div class="brigmaster-estimator__error" data-field-error="tileIncludeGrout" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--four brigmaster-estimator__field-group--hidden" data-tile-adhesive-fields>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($tileAdhesiveConsumptionFieldId); ?>">Расход клея (кг/м²)</label>
                            <input id="<?php echo esc_attr($tileAdhesiveConsumptionFieldId); ?>" type="number" name="tileAdhesiveConsumptionKgPerM2" min="0.01" step="0.01" value="3.5">
                            <div class="brigmaster-estimator__error" data-field-error="tileAdhesiveConsumptionKgPerM2" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($tileAdhesiveLayerFieldId); ?>">Толщина слоя клея (мм)</label>
                            <input id="<?php echo esc_attr($tileAdhesiveLayerFieldId); ?>" type="number" name="tileAdhesiveLayerMm" min="0.1" step="0.1" value="3">
                            <div class="brigmaster-estimator__error" data-field-error="tileAdhesiveLayerMm" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($tileAdhesiveBagWeightFieldId); ?>">Вес мешка клея (кг)</label>
                            <input id="<?php echo esc_attr($tileAdhesiveBagWeightFieldId); ?>" type="number" name="tileAdhesiveBagWeightKg" min="0.1" step="0.1" value="25">
                            <div class="brigmaster-estimator__error" data-field-error="tileAdhesiveBagWeightKg" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($tileAdhesiveBagPriceFieldId); ?>">Цена мешка клея</label>
                            <input id="<?php echo esc_attr($tileAdhesiveBagPriceFieldId); ?>" type="number" name="tileAdhesiveBagPrice" min="0.01" step="0.01" placeholder="Необязательно">
                            <div class="brigmaster-estimator__error" data-field-error="tileAdhesiveBagPrice" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="brigmaster-estimator__field-group brigmaster-estimator__field-grid brigmaster-estimator__field-grid--three brigmaster-estimator__field-group--hidden" data-tile-grout-fields>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($tileGroutDensityFieldId); ?>">Плотность затирки (кг/м³)</label>
                            <input id="<?php echo esc_attr($tileGroutDensityFieldId); ?>" type="number" name="tileGroutDensityKgPerM3" min="0.1" step="0.1" value="1600">
                            <div class="brigmaster-estimator__error" data-field-error="tileGroutDensityKgPerM3" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($tileGroutPackWeightFieldId); ?>">Вес упаковки затирки (кг)</label>
                            <input id="<?php echo esc_attr($tileGroutPackWeightFieldId); ?>" type="number" name="tileGroutPackWeightKg" min="0.1" step="0.1" value="2">
                            <div class="brigmaster-estimator__error" data-field-error="tileGroutPackWeightKg" aria-live="polite"></div>
                        </div>
                        <div class="brigmaster-estimator__field">
                            <label for="<?php echo esc_attr($tileGroutPackPriceFieldId); ?>">Цена упаковки затирки</label>
                            <input id="<?php echo esc_attr($tileGroutPackPriceFieldId); ?>" type="number" name="tileGroutPackPrice" min="0.01" step="0.01" placeholder="Необязательно">
                            <div class="brigmaster-estimator__error" data-field-error="tileGroutPackPrice" aria-live="polite"></div>
                        </div>
                    </div>
                </div>
            </details>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    private function renderTileRepeatableGroup(string $instanceId, string $group, string $title, string $type): string
    {
        $addButtonId = $instanceId . '-' . $group . '-add';
        $listId = $instanceId . '-' . $group . '-list';
        $addButtonLabel = $type === 'cutout' ? 'Добавить вырез или отверстие' : 'Добавить окно или дверь';
        $description = $type === 'cutout'
            ? 'Используйте этот блок для труб, розеток, люков, трапов и других мест, где приходится вырезать плитку.'
            : 'Используйте этот блок только для окон, дверей и других полноразмерных проёмов на стенах.';

        ob_start();
        ?>
        <div class="brigmaster-estimator__repeatable-group">
            <div class="brigmaster-estimator__segment-head">
                <h3 class="brigmaster-estimator__segment-title"><?php echo esc_html($title); ?></h3>
                <button id="<?php echo esc_attr($addButtonId); ?>" type="button" class="brigmaster-estimator__segment-add" data-tile-add-item="<?php echo esc_attr($group); ?>">
                    <?php echo esc_html($addButtonLabel); ?>
                </button>
            </div>
            <p class="brigmaster-estimator__hint"><?php echo esc_html($description); ?></p>
            <div id="<?php echo esc_attr($listId); ?>" class="brigmaster-estimator__segment-list" data-tile-repeat-list="<?php echo esc_attr($group); ?>" data-tile-item-type="<?php echo esc_attr($type); ?>"></div>
            <div class="brigmaster-estimator__error" data-field-error="<?php echo esc_attr($group); ?>" aria-live="polite"></div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    private function renderEstimatorTooltip(string $tooltipId, string $content): string
    {
        return '<span class="brigmaster-estimator__tooltip-anchor">'
            . '<button type="button" class="brigmaster-estimator__tooltip-trigger" data-tooltip-trigger aria-label="Подсказка" aria-expanded="false" aria-controls="' . esc_attr($tooltipId) . '">i</button>'
            . '<div id="' . esc_attr($tooltipId) . '" class="brigmaster-estimator__tooltip" role="tooltip" hidden>' . esc_html($content) . '</div>'
            . '</span>';
    }

    private function renderTileTooltip(string $tooltipId, string $content): string
    {
        return $this->renderEstimatorTooltip($tooltipId, $content);
    }

    private function renderTileResultTemplate(): string
    {
        ob_start();
        ?>
        <div class="brigmaster-estimator__result-grid brigmaster-estimator__result-grid--tile">
            <section class="brigmaster-estimator__result-card" data-result-card="tile-summary"></section>
            <section class="brigmaster-estimator__result-card" data-result-card="tile-layout"></section>
            <section class="brigmaster-estimator__result-card" data-result-card="tile-adhesive" hidden></section>
            <section class="brigmaster-estimator__result-card" data-result-card="tile-grout" hidden></section>
            <section class="brigmaster-estimator__result-card" data-result-card="tile-costs" hidden></section>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * Accordion summary chevron (SVG in markup; child theme animates via scaleY).
     */
    private function accordionChevronMarkup(): string
    {
        return '<span class="brigmaster-estimator__accordion-chevron" aria-hidden="true">'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none" focusable="false">'
            . '<path d="M2.25 4.25L6 7.75L9.75 4.25" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>'
            . '</svg></span>';
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
