<?php

require_once 'config.php';
require_once 'includes/functions.php';

$query = $pdo->query("
    SELECT
        songs.id,
        songs.title,
        songs.image_path,
        songs.average_rating,
        artists.name AS artist_name,
        genres.name AS genre_name
    FROM songs
    INNER JOIN artists ON songs.artist_id = artists.id
    INNER JOIN genres ON songs.genre_id = genres.id
    ORDER BY songs.average_rating DESC, songs.upload_date DESC
");

$songs = $query->fetchAll();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<main class="content">
    <h1>Рейтинг композиций</h1>

    <p class="page-description">
        Лучшие композиции сайта по среднему рейтингу пользователей.
    </p>

    <div class="cards rating-grid">
        <?php if ($songs): ?>
            <?php $place = 1; ?>

            <?php foreach ($songs as $song): ?>
                <?php
                    $rating = (float)$song['average_rating'];
                    $percent = min(100, max(0, $rating * 20));

                    $ratingClass = 'rating-low';

                    if ($rating >= 5) {
                        $ratingClass = 'rating-gold';
                    } elseif ($rating >= 4) {
                        $ratingClass = '';
                    } elseif ($rating >= 3) {
                        $ratingClass = 'rating-bronze';
                    } elseif ($rating >= 1) {
                        $ratingClass = 'rating-purple';
                    }
                ?>

                <div class="card rating-card-grid">

                    <div class="place-badge">
                        #<?= $place; ?>
                    </div>

                    <?php if (!empty($song['image_path'])): ?>
                        <img src="<?= $song['image_path']; ?>" alt="Обложка композиции" class="card-image">
                    <?php endif; ?>

                    <h3><?= $song['title']; ?></h3>

                    <p>Исполнитель: <?= $song['artist_name']; ?></p>
                    <p>Жанр: <?= $song['genre_name']; ?></p>

                    <div
                        class="rating-circle <?= $ratingClass; ?>"
                        style="--score-percent: <?= $percent; ?>%;"
                    >
                        <span><?= number_format($rating, 1); ?></span>
                    </div>

                    <a href="song.php?id=<?= $song['id']; ?>" class="button">
                        Подробнее
                    </a>

                </div>

                <?php $place++; ?>
            <?php endforeach; ?>

        <?php else: ?>
            <p>Композиции пока не добавлены.</p>
        <?php endif; ?>
    </div>
</main>

<?php

require_once 'includes/footer.php';

?>