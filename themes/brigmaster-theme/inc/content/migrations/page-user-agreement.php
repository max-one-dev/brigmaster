<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_User_Agreement_Migration
{
    private const MIGRATION_VERSION = 'user-agreement-v2';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_user_agreement_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('User agreement page not found.');
        }

        $content = self::build_user_agreement_page_content();

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

    public static function build_user_agreement_page_content(): string
    {
        $blocks = [
            Constructly_Migration_Helpers::block('constructly/page-hero', [
                'titleId' => 'user-agreement-hero-title',
                'image' => 'assets/src/images/illustrations/hero-privacy.jpg',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Пользовательское соглашение'],
                ],
                'title' => 'Пользовательское соглашение',
                'lead' => 'Условия использования сайта Brigmaster, его страниц, форм обратной связи и онлайн-калькуляторов.',
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

    private static function prose_blocks(): string
    {
        return <<<'HTML'
<!-- wp:heading {"anchor":"agreement-general"} -->
<h2 class="wp-block-heading" id="agreement-general">Общие положения</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Настоящее Пользовательское соглашение (далее — Соглашение) регулирует порядок использования сервиса строительных калькуляторов Brigmaster (далее — Сервис), размещённого в сети Интернет, а также права и обязанности пользователей Сервиса.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Используя Сервис Brigmaster, пользователь подтверждает, что ознакомился с условиями настоящего Соглашения, понимает их и принимает их в полном объёме. В случае несогласия с условиями Соглашения пользователь обязан воздержаться от использования Сервиса.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"anchor":"agreement-status"} -->
<h2 class="wp-block-heading" id="agreement-status">Статус сервиса и характер предоставляемой информации</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Сервис Brigmaster предназначен для ориентировочного расчёта расхода строительных материалов с использованием онлайн-калькуляторов. Результаты расчётов, тексты и иные материалы на сайте носят информационно-справочный характер.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Сервис Brigmaster не является проектной организацией и не оказывает инженерные, строительные или иные специализированные услуги. Результаты расчётов не могут рассматриваться как проектная документация, техническое заключение или иная документация, имеющая юридическую силу.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"anchor":"agreement-usage"} -->
<h2 class="wp-block-heading" id="agreement-usage">Порядок использования сервиса</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Пользователь обязуется:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>использовать Сервис Brigmaster в соответствии с действующим законодательством;</li><!-- /wp:list-item --><!-- wp:list-item --><li>не предпринимать действий, способных нарушить работу Сервиса или привести к сбоям в его функционировании;</li><!-- /wp:list-item --><!-- wp:list-item --><li>не использовать полученную через Сервис информацию в целях, противоречащих закону или правам третьих лиц.</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>При заполнении форм обратной связи пользователь несёт ответственность за достоверность и актуальность предоставляемых им данных, а также за содержание направляемых сообщений.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"anchor":"agreement-ip"} -->
<h2 class="wp-block-heading" id="agreement-ip">Интеллектуальная собственность</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Все элементы Сервиса Brigmaster, включая тексты, дизайн, логотипы, программный код, базы данных и иные объекты, могут охраняться авторским правом, правами на товарные знаки и иными правами в соответствии с действующим законодательством.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Пользователь не вправе копировать, распространять, публиковать, передавать третьим лицам, изменять или иным образом использовать материалы Сервиса вне личных целей, связанных с использованием функционала Brigmaster, без предварительного согласия правообладателя, если иное прямо не предусмотрено на соответствующей странице.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"anchor":"agreement-liability"} -->
<h2 class="wp-block-heading" id="agreement-liability">Ограничение и отказ от ответственности</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Сервис Brigmaster предоставляется «как есть». Правообладатель не гарантирует безошибочную и бесперебойную работу Сервиса, а также абсолютную точность и полноту получаемых при расчётах данных.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Правообладатель не несёт ответственности за:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>любые последствия использования пользователем результатов расчётов, полученных с помощью Сервиса Brigmaster;</li><!-- /wp:list-item --><!-- wp:list-item --><li>ущерб, причинённый в результате принятия проектных, инженерных или финансовых решений исключительно на основании информации, полученной через Сервис;</li><!-- /wp:list-item --><!-- wp:list-item --><li>действия третьих лиц, а также сбои в работе линий связи, оборудования и программного обеспечения, находящихся вне разумного контроля правообладателя.</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>Пользователь самостоятельно оценивает риски, связанные с использованием Сервиса Brigmaster, и при необходимости обращается за консультацией к профильным специалистам (инженерам, конструкторам, сметчикам и т.д.).</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"anchor":"agreement-changes"} -->
<h2 class="wp-block-heading" id="agreement-changes">Изменение условий соглашения</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Правообладатель Сервиса Brigmaster вправе в одностороннем порядке изменять условия настоящего Соглашения путём публикации обновлённой версии на сайте.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Продолжение использования Сервиса после внесения изменений рассматривается как согласие пользователя с новой редакцией Соглашения. Рекомендуется периодически знакомиться с актуальной версией Соглашения.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"anchor":"agreement-final"} -->
<h2 class="wp-block-heading" id="agreement-final">Заключительные положения</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Настоящее Соглашение применяется в той части, в которой оно не противоречит императивным нормам действующего законодательства.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Вопросы, не урегулированные настоящим Соглашением, подлежат разрешению в соответствии с применимым законодательством. В случае возникновения споров стороны стремятся решить их путём переговоров, а при невозможности достижения соглашения — в порядке, установленном законом.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>По вопросам, связанным с использованием Сервиса Brigmaster и применением настоящего Соглашения, пользователь может связаться с командой через страницу «Контакты».</p>
<!-- /wp:paragraph -->
HTML;
    }
}
