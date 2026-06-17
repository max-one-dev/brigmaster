<?php
declare(strict_types=1);

/**
 * Category / tag / date / author archives: same knowledge-base layout as the posts
 * index. The query is already filtered by WordPress for the current archive term.
 */
get_header();
get_template_part('template-parts/archive-layout');
get_footer();
