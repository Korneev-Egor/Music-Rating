<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
    <a class="<?= $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php">Главная</a>

    <a class="<?= $currentPage === 'songs.php' ? 'active' : ''; ?>" href="songs.php">Композиции</a>

    <a class="<?= $currentPage === 'rating.php' ? 'active' : ''; ?>" href="rating.php">Рейтинг</a>

    <a class="<?= $currentPage === 'reviews.php' ? 'active' : ''; ?>" href="reviews.php">Рецензии</a>

    <a class="<?= $currentPage === 'artists.php' ? 'active' : ''; ?>" href="artists.php">Исполнители</a>

    <a class="<?= $currentPage === 'faq.php' ? 'active' : ''; ?>" href="faq.php">Часто задаваемые вопросы
</a>
    
    <a class="<?= $currentPage === 'profile.php' ? 'active' : ''; ?>" href="profile.php">Профиль</a>
</aside>