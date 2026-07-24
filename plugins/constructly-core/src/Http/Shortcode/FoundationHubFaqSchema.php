<?php

declare(strict_types=1);

namespace Brigmaster\Http\Shortcode;

/**
 * Injects FAQPage JSON-LD for pages that render the foundation hub (Rank Math merges into WebPage).
 *
 * Extracted verbatim from EstimateShortcode without behavior changes. Hooked on `rank_math/json_ld`.
 */
final class FoundationHubFaqSchema
{
    /**
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
}
