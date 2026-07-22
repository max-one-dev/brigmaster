<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Drywall_Migration
{
    private const MIGRATION_VERSION = 'drywall-v5';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_drywall_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Drywall calculator page not found.');
        }

        $content = self::build_drywall_page_content();

        // Block attribute JSON escapes < > " & to \uXXXX; wp_update_post() runs
        // wp_unslash() which would strip those backslashes and corrupt the markup.
        // Slash the content so the escaping survives.
        wp_update_post([
            'ID' => $page_id,
            'post_content' => wp_slash($content),
        ]);

        update_post_meta($page_id, '_constructly_content_migration', self::MIGRATION_VERSION);

        return [
            'post_id' => $page_id,
            'content' => $content,
            'migration' => self::MIGRATION_VERSION,
        ];
    }

    public static function build_drywall_page_content(): string
    {
        $links = Constructly_Migration_Helpers::default_links();

        $blocks = [
            Constructly_Migration_Helpers::block('constructly/calculator-hero', [
                'titleId' => 'calculator-title',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Калькуляторы', 'url' => '/kalkulyatory/'],
                    ['label' => 'Гипсокартон'],
                ],
                'title' => 'Калькулятор гипсокартона',
                'lead' => 'Рассчитайте количество листов гипсокартона, профилей, крепежа и сопутствующих материалов для стен, перегородок и потолков. Получите предварительную смету материалов.',
                'features' => [
                    ['icon' => 'info-circle', 'text' => 'Ориентировочный расчёт'],
                    ['icon' => 'shield-check', 'text' => 'Основано на нормативных данных'],
                    ['icon' => '2fas-auth', 'text' => 'Без регистрации'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/context-intro', [
                'title' => 'Когда применяют гипсокартон',
                'body' => '<p>Гипсокартон выбирают для выравнивания стен и потолков, возведения перегородок и встроенных конструкций в жилых, коммерческих и офисных помещениях. Он подходит для сухих условий монтажа: там, где важна скорость работ, минимальный «мокрый» процесс и возможность скрыть инженерные коммуникации. Влагостойкий ГКЛ используют в кухнях и ванных комнатах с нормальным уровнем влажности.</p>',
            ]),
            Constructly_Migration_Helpers::block('constructly/calculator-estimator', [
                'shortcodeTag' => 'brigmaster_drywall_estimator',
                'shortcodeTitle' => 'Параметры конструкции',
                'infoTitle' => 'Как работает калькулятор',
                'infoBody' => '<p>Калькулятор рассчитывает материалы каркасной обшивки гипсокартоном: листы ГКЛ, профили, крепёж и сопутствующие материалы по площади конструкции.</p>'
                    . '<h3>Методика расчёта</h3>'
                    . '<ol>'
                    . '<li>Количество листов определяется делением площади обшивки на площадь одного листа с учётом слоёв и запаса на подрезку.</li>'
                    . '<li>Длина профилей рассчитывается по шагу стоек и направляющих для выбранного типа каркаса.</li>'
                    . '<li>Количество саморезов, дюбелей и крепежа принимается по нормативному расходу на квадратный метр.</li>'
                    . '</ol>'
                    . '<h3>Таблица расхода материалов на 1 м² обшивки ГКЛ</h3>'
                    . '<table>'
                    . '<thead>'
                    . '<tr><th>Материал</th><th>Расход на 1 м²</th></tr>'
                    . '</thead>'
                    . '<tbody>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="Площадь с запасом 10 % ÷ 3,0 м²; для перегородки ×2 стороны × число слоёв" tabindex="0">Лист ГКЛ 2500×1200×12,5 мм (3,0 м²)</span></td><td>≈ 0,37 листа</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="Шаг стоек 600 или 400 мм; длина = число стоек × высота" tabindex="0">Профиль стоечный ПП 60×27 (стена/потолок)</span></td><td>зависит от шага и высоты</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="Верхняя и нижняя обвязка; покупается с округлением до 1 м" tabindex="0">Профиль направляющий ПН 28×27 (стена/потолок)</span></td><td>длина стены × 2</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="Для перегородки ×2 (обе стороны); запас 10 %" tabindex="0">Саморезы для ГКЛ</span></td><td>34 шт. (1 слой) / 50 шт. (2 слоя)</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="Потолок: 2 на подвес + 8 на «краб»; запас 10 %" tabindex="0">Саморезы по металлу</span></td><td>4 шт. на стойку (стена/перегородка)</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="Крепление направляющих и прямых подвесов; запас 10 %" tabindex="0">Дюбель-гвозди</span></td><td>шаг направляющих 0,5 м + 2 на каждый подвес</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="Число рядов по высоте задаётся шагом 0,8 м; запас 10 %" tabindex="0">Прямые подвесы</span></td><td>шаг 0,8 м по стойке</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="На стыки листов (при включённой отделке)" tabindex="0">Лента армирующая (серпянка)</span></td><td>1,2 п. м</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="При включённой отделке" tabindex="0">Шпаклёвка финишная</span></td><td>1,2 кг</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="При включённой отделке" tabindex="0">Шпаклёвка швов</span></td><td>0,4 кг</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="При включённой отделке" tabindex="0">Грунтовка</span></td><td>0,1 кг</td></tr>'
                    . '</tbody>'
                    . '</table>'
                    . '<h3>Числовой пример расчёта</h3>'
                    . '<p>Условие: <strong>стена 4 м × 3 м = 12 м²</strong>, 1 слой, шаг стоек 600 мм, лист 2500×1200 мм (3,0 м²), без проёмов.</p>'
                    . '<ul>'
                    . '<li><strong>Листы ГКЛ:</strong> 12 м² × 1,1 (запас 10 %) = 13,2 м² ÷ 3,0 м² = 4,4 → <strong>5 листов</strong>.</li>'
                    . '<li><strong>Стоечный профиль ПП 60×27:</strong> число стоек = 4 ÷ 0,6 (округляя вверх) + 1 = 8 шт., длина 8 × 3 = <strong>24 п. м</strong>.</li>'
                    . '<li><strong>Направляющий ПН 28×27:</strong> 4 м × 2 (верх + низ) = <strong>8 п. м</strong>.</li>'
                    . '<li><strong>Перемычки ПП 60×27:</strong> один ряд стыков по высоте (лист 2,5 м короче стены 3 м), длина <strong>4 п. м</strong>.</li>'
                    . '<li><strong>Прямые подвесы:</strong> 8 стоек × 3 ряда по высоте (шаг 0,8 м) = <strong>24 шт.</strong></li>'
                    . '<li><strong>Саморезы для ГКЛ:</strong> 12 м² × 34 = 408, плюс запас 10 % = <strong>449 шт.</strong></li>'
                    . '<li><strong>Саморезы по металлу:</strong> 8 стоек × 4 = 32, плюс запас 10 % = <strong>36 шт.</strong></li>'
                    . '<li><strong>Дюбель-гвозди:</strong> направляющие 8 п. м ÷ 0,5 м = 16 шт., подвесы 24 × 2 = 48 шт., итого 64, плюс запас 10 % = <strong>71 шт.</strong></li>'
                    . '</ul>'
                    . '<h3>Числовой пример: потолок</h3>'
                    . '<p>Условие: <strong>потолок 4 м × 5 м = 20 м²</strong>, 1 слой, шаг несущих профилей 600 мм, шаг CD-профиля 600 мм, лист 2500×1200 мм (3,0 м²), без проёмов.</p>'
                    . '<ul>'
                    . '<li><strong>Листы ГКЛ:</strong> 20 м² × 1,1 = 22,0 м² ÷ 3,0 м² = 7,3 → <strong>8 листов</strong>.</li>'
                    . '<li><strong>Профиль CD 60×27 (несущий):</strong> ряды через 600 мм по ширине 4 м = 8 рядов × 5 м = <strong>40 п. м</strong>.</li>'
                    . '<li><strong>Профиль UD 28×27 (пристенный):</strong> периметр (4 + 5) × 2 = <strong>18 п. м</strong>.</li>'
                    . '<li><strong>Прямые подвесы:</strong> 8 рядов × (5 ÷ 0,8 округл. вверх) = 8 × 7 = 56, плюс запас 10 % = <strong>62 шт.</strong></li>'
                    . '<li><strong>Саморезы для ГКЛ:</strong> 20 м² × 34 × 1,1 = <strong>748 шт.</strong></li>'
                    . '<li><strong>Дюбель-гвозди для подвесов:</strong> 56 подвесов × 2 = 112, плюс запас 10 % = <strong>124 шт.</strong></li>'
                    . '</ul>'
                    . '<h3>Типичные ошибки при расчёте гипсокартона</h3>'
                    . '<ul>'
                    . '<li><strong>Нет запаса на подрезку.</strong> Расчёт «в ноль» без 5–10 % запаса приводит к нехватке листов при первом же угловом стыке или нестандартном проёме.</li>'
                    . '<li><strong>Неверный шаг профиля.</strong> Шаг 600 мм — стандарт для однослойной обшивки; при двухслойной или при требованиях по звукоизоляции шаг уменьшают до 400 мм, что резко увеличивает расход профиля.</li>'
                    . '<li><strong>Экономия на крепеже.</strong> Увеличение шага саморезов сверх 250 мм снижает жёсткость обшивки и нарушает требования СП 163.1325800.2014.</li>'
                    . '<li><strong>Игнорирование количества слоёв при звукоизоляции.</strong> Двойная обшивка удваивает расход листов и саморезов; при расчёте однослойного варианта звукоизоляционный эффект существенно ниже нормируемого.</li>'
                    . '<li><strong>Неучтённые перемычки и усиления.</strong> Над дверными и оконными проёмами устанавливаются горизонтальные перемычки из ПС-профиля — они не входят в типовую формулу по шагу стоек и требуют отдельного учёта.</li>'
                    . '</ul>'
                    . '<h3>Нормативные документы</h3>'
                    . '<ul>'
                    . '<li>СП 163.1325800.2014 «Конструкции с применением гипсокартонных и гипсоволокнистых листов. Правила проектирования и монтажа» — основной свод правил для каркасно-обшивочных конструкций из ГКЛ.</li>'
                    . '<li>ГОСТ Р 6266-2022 «Листы гипсокартонные. Технические условия» — действующий стандарт на номенклатуру, размеры и характеристики листов ГКЛ и ГКЛВ.</li>'
                    . '</ul>',
                'noteText' => 'Подробнее о расчётах — в разделе методологии.',
                'noteLinkLabel' => 'Как мы считаем материалы',
                'noteLinkUrl' => '/metodologiya/',
                'resultTitle' => 'Результаты расчёта',
                'resultStatus' => 'Заполните форму',
                'resultText' => 'После расчёта здесь появится сводка по листам, профилям и крепежу.',
            ]),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'calculator-faq',
                'titleId' => 'calculator-faq-title',
                'variant' => 'calculator',
                'title' => 'Часто задаваемые вопросы',
                'items' => [
                    ['question' => 'В один или два слоя обшивать?', 'answer' => 'Двухслойная обшивка повышает прочность и звукоизоляцию, но удваивает расход листов. Количество слоёв выбирается в форме.'],
                    ['question' => 'Какой шаг профилей выбрать?', 'answer' => 'Стоечные профили обычно ставят с шагом 600 мм, при повышенных требованиях — 400 мм. Шаг влияет на длину профиля.'],
                    ['question' => 'Какой гипсокартон выбрать?', 'answer' => 'Для сухих помещений — обычный ГКЛ, для влажных — влагостойкий ГКЛВ. На расчёт количества листов тип не влияет.'],
                    ['question' => 'Учитывается ли крепёж?', 'answer' => 'Да, количество саморезов и дюбелей рассчитывается по нормативному расходу на квадратный метр.'],
                    ['question' => 'Учитываются ли потери материалов?', 'answer' => 'Да, если в дополнительных параметрах указан процент запаса на подрезку.'],
                    ['question' => 'Нужна ли регистрация для использования?', 'answer' => 'Нет, расчёт можно выполнить без регистрации.'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/foundation-hub-type-cards', [
                'anchorId' => 'related-calculators',
                'titleId' => 'related-calculators-title',
                'columns' => '4',
                'sectionTitle' => 'Быстрый доступ к калькуляторам',
                'linkLabel' => 'Все калькуляторы',
                'linkUrl' => '/kalkulyatory/',
                'cards' => [
                    ['image' => 'assets/src/images/cards/calc-cover-foundation.jpg', 'title' => 'Фундамент', 'text' => 'Расчёт объёма бетона, опалубки, арматуры и материалов.', 'href' => '/kalkulyatory/fundament/', 'cta' => 'Рассчитать'],
                    ['image' => 'assets/src/images/cards/calc-cover-screed.jpg', 'title' => 'Стяжка пола', 'text' => 'Цементно-песчаная стяжка, наливные полы и другие типы.', 'href' => $links['screed'], 'cta' => 'Рассчитать'],
                    ['image' => 'assets/src/images/cards/calc-cover-brick.jpg', 'title' => 'Кирпич', 'text' => 'Количество кирпича и раствора для кладки стен и перегородок.', 'href' => $links['brick'], 'cta' => 'Рассчитать'],
                    ['image' => 'assets/src/images/cards/calc-cover-tile.jpg', 'title' => 'Плитка', 'text' => 'Площадь, клей, затирка и раскладка плитки для стен и пола.', 'href' => $links['tile'], 'cta' => 'Рассчитать'],
                ],
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
