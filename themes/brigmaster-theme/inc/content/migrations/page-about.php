<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

final class Constructly_About_Migration
{
    private const MIGRATION_VERSION = 'about-v1';

    /**
     * @return array{post_id:int, content:string, migration:string}
     */
    public static function migrate_about_page(int $page_id): array
    {
        $page = get_post($page_id);

        if (!$page instanceof WP_Post || $page->post_type !== 'page') {
            throw new InvalidArgumentException('About page not found.');
        }

        $content = self::build_about_page_content();

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

    public static function build_about_page_content(): string
    {
        $blocks = [
            Constructly_Migration_Helpers::block('constructly/page-hero', [
                'titleId' => 'about-hero-title',
                'image' => 'assets/src/images/illustrations/hero-about.jpg',
                'breadcrumbs' => [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'О проекте'],
                ],
                'title' => 'О проекте Brigmaster',
                'lead' => 'Мы создаём понятные и точные инструменты для расчётов в строительстве и ремонте.',
                'paragraphs' => [
                    ['text' => 'Brigmaster — это онлайн-платформа строительных калькуляторов и информационных материалов, где каждый пользователь и профессионал найдёт полезные инструменты и рекомендации.'],
                    ['text' => 'Наша цель — помочь вам принимать обоснованные решения, избегать ошибок и экономить время на каждом этапе строительства.'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/feature-cards', [
                'titleId' => 'about-mission-title',
                'sectionTitle' => 'Наша миссия',
                'columns' => '3',
                'items' => [
                    [
                        'icon' => 'shield-check',
                        'title' => 'Доступность',
                        'text' => 'Делаем сложные расчёты простыми и понятными для каждого.',
                    ],
                    [
                        'icon' => 'calculator',
                        'title' => 'Точность',
                        'text' => 'Расчёты основаны на актуальных нормах и проверенных формулах.',
                    ],
                    [
                        'icon' => 'briefcase',
                        'title' => 'Практичность',
                        'text' => 'Наши инструменты помогают в реальных задачах на стройке и в ремонте.',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/text-media', [
                'titleId' => 'about-story-title',
                'title' => 'Как всё начиналось',
                'mediaPosition' => 'right',
                'image' => 'assets/src/images/illustrations/hero-article.jpg',
                'imageAlt' => 'Рабочий стол с чертежами и калькулятором',
                'paragraphs' => [
                    ['text' => 'Brigmaster появился из практики. Мы часто сталкивались с трудностями в расчётах и понимали, как важно иметь под рукой надёжный инструмент.'],
                    ['text' => 'Так родилась идея собрать лучшие подходы и сделать их доступными онлайн.'],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/feature-cards', [
                'titleId' => 'about-principles-title',
                'sectionTitle' => 'Наши принципы',
                'columns' => '4',
                'variant' => 'stacked',
                'items' => [
                    [
                        'icon' => 'check-circle',
                        'title' => 'Честность',
                        'text' => 'Мы открыто показываем, как работает расчёт и на каких данных он основан.',
                    ],
                    [
                        'icon' => 'document-add',
                        'title' => 'Прозрачность',
                        'text' => 'Пишем короткие формулы и открыто говорим об ограничениях.',
                    ],
                    [
                        'icon' => 'shield-check',
                        'title' => 'Надёжность',
                        'text' => 'Постоянно обновляем данные и улучшаем сервис на основе обратной связи.',
                    ],
                    [
                        'icon' => 'target',
                        'title' => 'Развитие',
                        'text' => 'Расширяем возможности платформы и создаём новые полезные инструменты.',
                    ],
                ],
            ]),
            Constructly_Migration_Helpers::block('constructly/final-cta', [
                'titleId' => 'about-cta-title',
                'variant' => 'soft',
                'title' => 'Мы на связи',
                'text' => 'Есть идея, предложение или вопрос? Напишите нам — мы всегда открыты к диалогу.',
                'buttonLabel' => 'Перейти к контактам',
                'buttonUrl' => '/kontakty/',
                'image' => 'assets/src/images/illustrations/cta-about-light.jpg',
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
