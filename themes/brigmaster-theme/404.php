<?php
declare(strict_types=1);

get_header();
?>
<main id="primary" class="site-main bm-404" role="main">
    <div class="bm-container">
        <div class="bm-404__inner">
            <p class="bm-404__code" aria-hidden="true">404</p>
<h1 class="bm-404__title">Страница не найдена</h1>
            <p class="bm-404__text">Возможно, страница удалена или адрес введён неверно. Начните с главной или откройте калькуляторы.</p>
            <div class="bm-404__actions">
                <a class="bm-button bm-button--primary" href="<?php echo esc_url(home_url('/')); ?>">На главную</a>
                <a class="bm-button bm-button--secondary" href="<?php echo esc_url(home_url('/kalkulyatory/')); ?>">Открыть калькуляторы</a>
                <a class="bm-button bm-button--secondary" href="<?php echo esc_url(home_url('/baza-znaniy/')); ?>">База знаний</a>
            </div>
        </div>
    </div>
</main>
<?php
get_footer();
