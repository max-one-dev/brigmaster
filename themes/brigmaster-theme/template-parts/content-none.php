<?php
declare(strict_types=1);
?>
<div class="bm-no-results">
    <svg class="bm-icon bm-no-results__icon" aria-hidden="true"><use href="#bm-icon-search"></use></svg>
    <h2 class="bm-no-results__title">Ничего не найдено</h2>
    <p class="bm-no-results__text">По вашему запросу статьи не нашлись. Измените фильтр или посмотрите все статьи.</p>
    <a class="bm-button bm-button--primary bm-no-results__button" href="<?php echo esc_url((int) get_option('page_for_posts') > 0 ? (string) get_permalink((int) get_option('page_for_posts')) : home_url('/baza-znaniy/')); ?>">Все статьи</a>
</div>
