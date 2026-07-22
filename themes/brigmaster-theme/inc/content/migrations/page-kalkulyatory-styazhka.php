<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Screed_Migration
{
    private const MIGRATION_VERSION = 'screed-v3';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_screed_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Screed calculator page not found.');
        }

        $content = self::build_screed_page_content();

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

    public static function build_screed_page_content(): string
    {
        $links = Constructly_Migration_Helpers::default_links();

        $blocks = [
            Constructly_Migration_Helpers::block('constructly/calculator-hero', [
                'titleId' => 'calculator-title',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Калькуляторы', 'url' => '/kalkulyatory/'],
                    ['label' => 'Стяжка пола'],
                ],
                'title' => 'Калькулятор стяжки пола',
                'lead' => 'Рассчитайте объём раствора и количество сухой смеси или цемента и песка для стяжки пола по площади и толщине слоя. Получите предварительную смету материалов.',
                'features' => [
                    ['icon' => 'info-circle', 'text' => 'Ориентировочный расчёт'],
                    ['icon' => 'shield-check', 'text' => 'Основано на нормативных данных'],
                    ['icon' => '2fas-auth', 'text' => 'Без регистрации'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/context-intro', [
                'title' => 'Когда устраивают стяжку пола',
                'body' => '<p>Стяжку пола используют для выравнивания основания под финишное покрытие, повышения жёсткости конструкции пола и размещения систем тёплого пола. Она актуальна в новостройках с неровными плитами перекрытия, при капитальном ремонте с заменой покрытия, а также в помещениях с перепадами высот более 5 мм. Мокрая и полусухая стяжки применяются в жилых и коммерческих помещениях; наливной состав — там, где требуется минимальная толщина слоя.</p>',
            ]),
            Constructly_Migration_Helpers::block('constructly/calculator-estimator', [
                'shortcodeTag' => 'brigmaster_screed_estimator',
                'shortcodeTitle' => 'Параметры стяжки',
                'infoTitle' => 'Как работает калькулятор',
                'infoBody' => '<p>Калькулятор рассчитывает материалы для стяжки пола: объём раствора и количество смеси по площади помещения и толщине слоя.</p>'
                    . '<h3>Методика расчёта</h3>'
                    . '<ol>'
                    . '<li>Объём раствора вычисляется как произведение площади пола на толщину стяжки.</li>'
                    . '<li>Для готовой сухой смеси количество мешков определяется по расходу 19 кг/м² при толщине 10 мм, пересчитанному на заданную толщину; плотность смеси принята 1900 кг/м³.</li>'
                    . '<li>Для самомесного раствора без щебня сухой объём увеличивается на коэффициент 1,33; цемент и песок распределяются по выбранной пропорции с учётом плотностей 1300 и 1600 кг/м³ соответственно; вода рассчитывается по водоцементному отношению В/Ц = 0,5.</li>'
                    . '<li>Армирующая сетка рассчитывается опционально: Ø 12 мм, шаг 200 мм, 2 слоя, запас 10 %.</li>'
                    . '</ol>'
                    . '<h3>Таблица расхода материалов на 1 м² стяжки толщиной 10 мм</h3>'
                    . '<table>'
                    . '<thead>'
                    . '<tr><th>Материал</th><th>Расход</th></tr>'
                    . '</thead>'
                    . '<tbody>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="Диапазон 18–20 кг/м² при толщине 10 мм; плотность смеси 1900 кг/м³; количество мешков округляется вверх до целого" tabindex="0">Готовая сухая смесь (мешок 25 кг)</span></td><td>19 кг/м²</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="Пропорция 1:3 (цемент:песок); плотность цемента 1300 кг/м³; сухой объём = объём раствора × 1,33" tabindex="0">Цемент (самомесный раствор)</span></td><td>0,25 объёма сухой части × 1300 кг/м³</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="Пропорция 1:3 (цемент:песок); плотность песка 1600 кг/м³; сухой объём = объём раствора × 1,33" tabindex="0">Песок (самомесный раствор)</span></td><td>0,75 объёма сухой части × 1600 кг/м³</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="Водоцементное отношение В/Ц = 0,5 к массе цемента; вода добавляется после замеса сухих компонентов" tabindex="0">Вода</span></td><td>В/Ц = 0,5 от массы цемента</td></tr>'
                    . '<tr><td><span class="bm-tooltip" data-tooltip="Опциональный параметр; Ø 12 мм, шаг 200 мм, 2 слоя; итоговый расход умножается на коэффициент запаса 1,1" tabindex="0">Армирующая сетка (опционально)</span></td><td>запас 10 % к расчётной площади</td></tr>'
                    . '</tbody>'
                    . '</table>'
                    . '<h3>Числовой пример расчёта</h3>'
                    . '<p>Условие: <strong>помещение 4 × 5 м = 20 м²</strong>, толщина 40 мм (0,04 м), пропорция 1:3.</p>'
                    . '<ul>'
                    . '<li><strong>Объём раствора:</strong> 20 м² × 0,04 м = <strong>0,8 м³</strong>.</li>'
                    . '<li><strong>Готовая сухая смесь:</strong> 0,8 м³ × 1900 кг/м³ = 1520 кг ÷ 25 кг = 60,8 → <strong>61 мешок</strong>.</li>'
                    . '<li><strong>Самомесный раствор — сухой объём:</strong> 0,8 м³ × 1,33 = <strong>1,064 м³</strong>.</li>'
                    . '<li><strong>Цемент (1 часть из 4):</strong> 1,064 × 0,25 = 0,266 м³ × 1300 кг/м³ = <strong>346 кг</strong>.</li>'
                    . '<li><strong>Песок (3 части из 4):</strong> 1,064 × 0,75 = 0,798 м³ × 1600 кг/м³ = <strong>1277 кг</strong>.</li>'
                    . '<li><strong>Вода:</strong> 346 кг × 0,5 = <strong>173 л</strong>.</li>'
                    . '</ul>'
                    . '<h3>Типичные ошибки при расчёте стяжки</h3>'
                    . '<ul>'
                    . '<li><strong>Площадь без учёта ниш и выступов.</strong> Замер «по прямоугольнику» занижает или завышает площадь; при сложной планировке измеряйте каждую зону отдельно.</li>'
                    . '<li><strong>Толщина меньше минимально допустимой.</strong> Для цементно-песчаной стяжки минимум — 30 мм; при меньшей толщине слой трескается и отслаивается.</li>'
                    . '<li><strong>Игнорирование коэффициента уплотнения.</strong> Раствор при укладке даёт усадку; расчёт «в ноль» без коэффициента 1,33 приведёт к нехватке материала.</li>'
                    . '<li><strong>Неверное водоцементное отношение.</strong> Избыток воды (В/Ц выше 0,6) резко снижает прочность стяжки; ориентир — В/Ц = 0,5.</li>'
                    . '<li><strong>Отсутствие деформационных швов.</strong> При площади более 30 м² или вблизи стен необходимы температурные швы; их отсутствие вызывает растрескивание.</li>'
                    . '</ul>'
                    . '<h3>Нормативные документы</h3>'
                    . '<ul>'
                    . '<li>СП 29.13330.2011 «Полы» (актуализированная редакция СНиП 2.03.13-88) — основной свод правил для устройства полов и стяжек.</li>'
                    . '<li>ГОСТ 31358-2019 «Смеси сухие строительные напольные на цементном вяжущем. Технические условия» — стандарт на готовые сухие смеси для стяжки пола.</li>'
                    . '<li>ГОСТ 28013-98 «Растворы строительные. Общие технические условия» — требования к составу, приготовлению и свойствам строительных растворов.</li>'
                    . '</ul>',
                'noteText' => 'Подробнее о расчётах — в разделе методологии.',
                'noteLinkLabel' => 'Как мы считаем материалы',
                'noteLinkUrl' => '/metodologiya/',
                'resultTitle' => 'Результаты расчёта',
                'resultStatus' => 'Заполните форму',
                'resultText' => 'После расчёта здесь появится сводка по раствору и материалам стяжки.',
            ]),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'calculator-faq',
                'titleId' => 'calculator-faq-title',
                'variant' => 'calculator',
                'title' => 'Часто задаваемые вопросы',
                'items' => [
                    ['question' => 'Какую толщину стяжки выбрать?', 'answer' => 'Цементно-песчаную стяжку обычно делают толщиной 30–50 мм. Минимальная толщина зависит от основания и типа смеси.'],
                    ['question' => 'Готовая смесь или цемент с песком?', 'answer' => 'Калькулятор поддерживает оба варианта: количество мешков готовой смеси или объёмы цемента и песка по пропорции.'],
                    ['question' => 'Какая пропорция раствора нужна?', 'answer' => 'Для стяжки часто используют пропорцию 1:3 или 1:4 (цемент:песок). Пропорция выбирается в форме.'],
                    ['question' => 'Нужно ли армировать стяжку?', 'answer' => 'Армирование сеткой рекомендуется при больших площадях и толстых слоях; на расход раствора оно влияет незначительно.'],
                    ['question' => 'Учитываются ли потери материалов?', 'answer' => 'Да, если в дополнительных параметрах указан процент запаса.'],
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
                    ['image' => 'assets/src/images/cards/calc-cover-brick.jpg', 'title' => 'Кирпич', 'text' => 'Количество кирпича и раствора для кладки стен и перегородок.', 'href' => $links['brick'], 'cta' => 'Рассчитать'],
                    ['image' => 'assets/src/images/cards/calc-cover-tile.jpg', 'title' => 'Плитка', 'text' => 'Площадь, клей, затирка и раскладка плитки для стен и пола.', 'href' => $links['tile'], 'cta' => 'Рассчитать'],
                    ['image' => 'assets/src/images/cards/calc-cover-drywall.jpg', 'title' => 'Гипсокартон', 'text' => 'Расчёт листов, профилей, крепежа и материалов для монтажа.', 'href' => $links['drywall'], 'cta' => 'Рассчитать'],
                ],
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
