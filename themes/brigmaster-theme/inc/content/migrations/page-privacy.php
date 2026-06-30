<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Privacy_Migration
{
    private const MIGRATION_VERSION = 'privacy-v8';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_privacy_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Privacy page not found.');
        }

        $content = self::build_privacy_page_content();

        // wp_slash so escaped block-attribute JSON and prose HTML survive
        // wp_update_post()'s wp_unslash(). See PAGES_INTEGRATION_TRACKER.
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

    public static function build_privacy_page_content(): string
    {
        $blocks = [
            Constructly_Migration_Helpers::block('constructly/page-hero', [
                'titleId' => 'privacy-hero-title',
                'image' => 'assets/src/images/illustrations/hero-privacy.jpg',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Политика конфиденциальности'],
                ],
                'title' => 'Политика конфиденциальности',
                'lead' => 'Мы ценим вашу конфиденциальность и стремимся защищать персональные данные. На этой странице объясняем, какие данные собираем и как используем их.',
            ]),
            Constructly_Migration_Helpers::block(
                'constructly/content-layout',
                [
                    'sidebarTitle' => 'Есть вопросы?',
                    'sidebarText' => 'Если что-то непонятно, напишите нам через страницу контактов.',
                    'buttonLabel' => 'Перейти к контактам',
                    'buttonUrl' => '/kontakty/',
                    'sidebarImage' => 'assets/src/images/illustrations/question_bg.jpg',
                ],
                self::prose_blocks()
            ),
        ];

        return implode("\n\n", $blocks);
    }

    /**
     * Privacy policy body as native Gutenberg (core) blocks. The styled callout
     * (.bm-info-block) is a core/html block; everything else is headings, paragraphs
     * and lists. Heading ids feed the auto-generated table of contents (toc.js).
     */
    private static function prose_blocks(): string
    {
        return <<<'HTML'
<!-- wp:heading {"anchor":"privacy-general"} -->
<h2 class="wp-block-heading" id="privacy-general">1. Общие положения</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Настоящая Политика конфиденциальности применяется к сайту Brigmaster и описывает, какие данные могут обрабатываться при использовании страниц, форм обратной связи и онлайн-калькуляторов.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Оператор сайта — частное лицо. Реквизиты юридического лица не публикуются, поскольку деятельность ведётся без его образования.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Используя сайт, вы соглашаетесь с условиями обработки данных, описанными в этом документе. Если вы не согласны с условиями, пожалуйста, не отправляйте данные через формы сайта.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"anchor":"privacy-data"} -->
<h2 class="wp-block-heading" id="privacy-data">2. Какие данные мы собираем</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Мы можем получать следующие данные:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>данные, переданные через форму обратной связи: имя, адрес электронной почты (email) и текст сообщения;</li><!-- /wp:list-item --><!-- wp:list-item --><li>технические данные: IP-адрес, тип браузера, устройство и действия на сайте;</li><!-- /wp:list-item --><!-- wp:list-item --><li>данные, которые вы вводите в калькуляторы для выполнения расчёта;</li><!-- /wp:list-item --><!-- wp:list-item --><li>cookies и похожие технологии, если они включены в вашем браузере.</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>Данные формы обратной связи (имя, адрес электронной почты, текст сообщения) собираются исключительно с целью ответа на ваше обращение. Мы не передаём эти данные третьим лицам и не продаём их. Для удаления ваших данных направьте запрос на <a href="mailto:info@brigmaster.ru">info@brigmaster.ru</a> — данные будут удалены в разумный срок.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"anchor":"privacy-use"} -->
<h2 class="wp-block-heading" id="privacy-use">3. Как мы используем данные</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Данные используются для следующих целей:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>обработки сообщений и обратной связи;</li><!-- /wp:list-item --><!-- wp:list-item --><li>улучшения работы сайта и пользовательского опыта;</li><!-- /wp:list-item --><!-- wp:list-item --><li>аналитики посещаемости и качества страниц;</li><!-- /wp:list-item --><!-- wp:list-item --><li>обеспечения безопасности и предотвращения злоупотреблений.</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading {"anchor":"privacy-transfer"} -->
<h2 class="wp-block-heading" id="privacy-transfer">4. Передача данных третьим лицам</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Мы не продаём персональные данные третьим лицам. Передача возможна только в ограниченных случаях:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>если это требуется законом;</li><!-- /wp:list-item --><!-- wp:list-item --><li>для технической поддержки сайта, хостинга, аналитики или почтовых сервисов;</li><!-- /wp:list-item --><!-- wp:list-item --><li>при наличии вашего согласия.</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading {"anchor":"privacy-storage"} -->
<h2 class="wp-block-heading" id="privacy-storage">5. Хранение данных</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Данные хранятся столько, сколько необходимо для целей обработки, ответа на запросы и соблюдения применимых требований. Технические журналы и аналитические данные могут храниться ограниченный срок в обезличенном или агрегированном виде.</p>
<!-- /wp:paragraph -->

<!-- wp:constructly/info-block {"text":"Данные расчётов, введённые в публичных калькуляторах, используются для показа результата в текущей сессии и не являются проектной документацией."} /-->


<!-- wp:heading {"anchor":"privacy-rights"} -->
<h2 class="wp-block-heading" id="privacy-rights">6. Ваши права</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Вы можете обратиться к нам, чтобы:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>запросить доступ к своим данным;</li><!-- /wp:list-item --><!-- wp:list-item --><li>уточнить или исправить неточные данные;</li><!-- /wp:list-item --><!-- wp:list-item --><li>отозвать согласие на обработку;</li><!-- /wp:list-item --><!-- wp:list-item --><li>запросить удаление данных, если это применимо.</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading {"anchor":"privacy-cookies"} -->
<h2 class="wp-block-heading" id="privacy-cookies">7. Cookies</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Мы используем cookies для корректной работы сайта, запоминания настроек, аналитики и улучшения пользовательского опыта. Вы можете ограничить cookies в настройках браузера, но часть функций сайта может работать некорректно.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"anchor":"privacy-updates"} -->
<h2 class="wp-block-heading" id="privacy-updates">8. Изменения в политике</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Мы можем обновлять Политику конфиденциальности. Новая редакция вступает в силу после публикации на сайте. Рекомендуем периодически проверять эту страницу.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"anchor":"privacy-contacts"} -->
<h2 class="wp-block-heading" id="privacy-contacts">9. Контакты</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>По вопросам обработки персональных данных, а также для запроса доступа или удаления данных свяжитесь по электронной почте: <a href="mailto:info@brigmaster.ru">info@brigmaster.ru</a>. Это единственный канал связи для обращений по данной теме.</p>
<!-- /wp:paragraph -->
HTML;
    }
}
