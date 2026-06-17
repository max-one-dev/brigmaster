<?php
declare(strict_types=1);

/**
 * Fallback template (search results, unconfigured blog index): reuse the
 * knowledge-base archive layout for a consistent look.
 */
get_header();
get_template_part('template-parts/archive-layout');
get_footer();
