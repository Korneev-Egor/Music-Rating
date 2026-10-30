<?php

require_once 'config.php';
require_once 'includes/functions.php';

$query = $pdo->query("
    SELECT
        artists.id,
        artists.name,
        artists.image_path,
        COUNT(songs.id) AS songs_count,
        COALESCE(ROUND(AVG(songs.average_rating), 2), 0) AS average_rating
    FROM artists
    LEFT JOIN songs ON songs.artist_id = artists.id
    GROUP BY artists.id
    ORDER BY average_rating DESC, songs_count DESC
");

$artists = $query->fetchAll();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<main class="content">
    <h1>Исполнители</h1>

    <div class="cards artists-grid">
        <?php if ($artists): ?>
            <?php foreach ($artists as $artist): ?>
                <div class="card artist-card">

                    <?php if (!empty($artist['image_path'])): ?>
                        <img src="<?= htmlspecialchars($artist['image_path']); ?>" class="artist-card-image" alt="Фото исполнителя">
                    <?php else: ?>
                        <div class="artist-placeholder artist-card-image">
                            <?= mb_substr(htmlspecialchars($artist['name']), 0, 1, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <h3><?= htmlspecialchars($artist['name']); ?></h3>

                    <p>Композиции: <?= (int)$artist['songs_count']; ?></p>
                    <p>Средний рейтинг: <?= number_format((float)$artist['average_rating'], 2); ?></p>

                    <a href="artist.php?id=<?= (int)$artist['id']; ?>" class="button">
                        Подробнее
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Исполнители пока не добавлены.</p>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>