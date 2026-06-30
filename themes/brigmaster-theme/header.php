<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="bm-skip-link skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'brigmaster-theme'); ?></a>
<header class="bm-header" role="banner" data-bm-component="header-menu">
    <div class="bm-container bm-header__row">
        <a class="bm-header__brand" href="<?php echo esc_url(home_url('/')); ?>">
            <img
                class="bm-header__logo"
                src="<?php echo esc_url(get_theme_file_uri('assets/src/images/logo_header.png')); ?>"
                width="168"
                height="40"
                alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
            >
        </a>

        <button
            type="button"
            class="bm-header__toggle"
            aria-expanded="false"
            aria-controls="bm-header-panel"
            aria-label="<?php esc_attr_e('Открыть меню', 'brigmaster-theme'); ?>"
        >
            <span class="bm-header__toggle-bar" aria-hidden="true"></span>
            <span class="bm-header__toggle-bar" aria-hidden="true"></span>
            <span class="bm-header__toggle-bar" aria-hidden="true"></span>
        </button>

        <div id="bm-header-panel" class="bm-header__panel">
            <?php if (has_nav_menu('primary')) : ?>
                <nav class="bm-nav-primary" aria-label="<?php esc_attr_e('Основная навигация', 'brigmaster-theme'); ?>" data-bm-component="nav-dropdown">
                    <?php
                    wp_nav_menu(
                        [
                            'theme_location' => 'primary',
                            'menu_class' => 'menu',
                            'menu_id' => 'primary-navigation',
                            'container' => false,
                            'depth' => 3,
                            'fallback_cb' => false,
                        ]
                    );
                    ?>
                </nav>
            <?php endif; ?>

            <div class="bm-header__actions">
                <a class="bm-button bm-button--primary" href="<?php echo esc_url(home_url('/kalkulyatory/')); ?>">
                    <?php esc_html_e('Открыть калькуляторы', 'brigmaster-theme'); ?>
                </a>
            </div>
        </div>
    </div>
    <div class="bm-header__overlay" aria-hidden="true"></div>
</header>
<div id="page" class="site">
