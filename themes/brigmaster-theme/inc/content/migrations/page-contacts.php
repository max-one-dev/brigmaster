<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_Contacts_Migration
{
    private const MIGRATION_VERSION = 'contacts-v2';

    private const CONTACT_FORM_SHORTCODE = '[contact-form-7 id="3b0d792" title="Контактная форма"]';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_contacts_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('Contacts page not found.');
        }

        $content = self::build_contacts_page_content();

        // wp_update_post() runs wp_unslash() on the data, which would strip the
        // backslash from serialized block attributes (e.g. the CF7 shortcode's
        // " quotes), corrupting them to "u0022". Slash the content so the
        // escaping survives the round-trip. See block attribute serialization.
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

    public static function build_contacts_page_content(): string
    {
        $blocks = [
            Constructly_Migration_Helpers::block('constructly/page-hero', [
                'titleId' => 'contacts-hero-title',
                'image' => 'assets/src/images/illustrations/hero-contacts.jpg',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Контакты'],
                ],
                'title' => 'Контакты',
                'lead' => 'Мы всегда рады вашим вопросам, предложениям и сотрудничеству.',
            ]),
            Constructly_Migration_Helpers::block('constructly/contact-form', [
                'titleId' => 'contacts-form-title',
                'sectionTitle' => 'Свяжитесь с нами',
                'text' => 'Напишите, если хотите уточнить расчёт, предложить улучшение или обсудить сотрудничество. Мы отвечаем по рабочим каналам и стараемся быстро возвращаться с понятным ответом.',
                'shortcode' => self::CONTACT_FORM_SHORTCODE,
                'channels' => [
                    [
                        'icon' => 'mail',
                        'title' => 'Email',
                        'value' => '<a href="mailto:info@brigmaster.ru">info@brigmaster.ru</a>',
                        'note' => 'Для вопросов, предложений и партнёрств.',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/faq', [
                'sectionId' => 'contacts-faq',
                'titleId' => 'contacts-faq-title',
                'title' => 'Часто задаваемые вопросы',
                'items' => [
                    [
                        'question' => 'Как быстро вы отвечаете на сообщения?',
                        'answer' => 'Обычно мы отвечаем в течение рабочего дня, если вопрос не требует дополнительной проверки.',
                    ],
                    [
                        'question' => 'Можно ли предложить новый калькулятор?',
                        'answer' => 'Да, опишите задачу и пример расчёта. Мы рассмотрим идею при планировании новых инструментов.',
                    ],
                    [
                        'question' => 'Вы работаете с организациями?',
                        'answer' => 'Да, мы открыты к партнёрствам, экспертным материалам и совместным улучшениям сервиса.',
                    ],
                    [
                        'question' => 'Куда отправить замечание по формуле?',
                        'answer' => 'Напишите на email или через форму и укажите страницу, входные данные и ожидаемый результат.',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/final-cta', [
                'titleId' => 'contacts-cta-title',
                'variant' => 'soft',
                'title' => 'Есть вопрос по расчёту?',
                'text' => 'Посмотрите методологию — там собраны основные принципы, источники данных и ограничения.',
                'buttonLabel' => 'Открыть методологию',
                'buttonUrl' => '/metodologiya/',
                'image' => '',
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
