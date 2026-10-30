<?php

require_once 'config.php';
require_once 'includes/functions.php';

$artistId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = $pdo->prepare("
    SELECT
        artists.id,
        artists.name,
        artists.image_path,
        artists.description,
        COUNT(songs.id) AS songs_count,
        COALESCE(ROUND(AVG(songs.average_rating), 2), 0) AS artist_rating
    FROM artists
    LEFT JOIN songs ON songs.artist_id = artists.id
    WHERE artists.id = ?
    GROUP BY artists.id
");

$query->execute([$artistId]);
$artist = $query->fetch();

if (!$artist) {
    die('Исполнитель не найден');
}

$songsQuery = $pdo->prepare("
    SELECT
        songs.id,
        songs.title,
        songs.image_path,
        songs.average_rating,
        genres.name AS genre_name
    FROM songs
    LEFT JOIN genres ON songs.genre_id = genres.id
    WHERE songs.artist_id = ?
    ORDER BY songs.average_rating DESC, songs.upload_date DESC
    ");

$songsQuery->execute([$artistId]);
$songs = $songsQuery->fetchAll();

$reviewsQuery = $pdo->prepare("
    SELECT
        reviews.review_title,
        reviews.review_text,
        reviews.created_at,
        users.username,
        songs.title AS song_title,
        songs.id AS song_id,
        songs.image_path
    FROM reviews
    INNER JOIN users ON reviews.user_id = users.id
    INNER JOIN songs ON reviews.song_id = songs.id
    WHERE songs.artist_id = ?
    ORDER BY reviews.created_at DESC
    LIMIT 6
    ");

$reviewsQuery->execute([$artistId]);
$reviews = $reviewsQuery->fetchAll();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<main class="content artist-detail-page">

    <section class="artist-detail-hero">
        <div class="artist-detail-photo">
            <?php if (!empty($artist['image_path'])): ?>
                <img src="<?= htmlspecialchars($artist['image_path']); ?>" alt="Фото исполнителя">
            <?php else: ?>
                <div class="artist-placeholder artist-placeholder-large">
                    <?= mb_substr(htmlspecialchars($artist['name']), 0, 1, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="artist-detail-info">
            <p class="section-label">Профиль исполнителя</p>
            <h1><?= htmlspecialchars($artist['name']); ?></h1>

            <p>
                <?= !empty($artist['description'])
                    ? nl2br(htmlspecialchars($artist['description']))
                    : 'Исполнитель музыкального сайта. Здесь отображаются его композиции, оценки и последние рецензии слушателей.'; ?>
            </p>

            <?php if (isAdmin()): ?>
                <div class="artist-detail-admin">
                    <a href="edit_artist.php?id=<?= (int)$artist['id']; ?>" class="button">Редактировать</a>

                    <form action="delete_artist.php" method="POST" onsubmit="return confirm('Удалить исполнителя? Если у него есть композиции, сначала удалите или перенесите их.');">
                        <input type="hidden" name="artist_id" value="<?= (int)$artist['id']; ?>">
                        <button type="submit" class="ghost-danger-button">Удалить</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="artist-detail-stats">
            <div>
                <span><?= (int)$artist['songs_count']; ?></span>
                <p>Композиции</p>
            </div>

            <div>
                <span><?= number_format((float)$artist['artist_rating'], 2); ?></span>
                <p>Средний рейтинг</p>
            </div>
        </div>
    </section>

    <section class="index-section">
        <div class="section-head">
            <h2>Релизы исполнителя</h2>
            <?php if (isAdmin()): ?>
                <a href="add_song.php">Добавить композицию →</a>
            <?php endif; ?>
        </div>

        <div class="cards">
            <?php if ($songs): ?>
                <?php foreach ($songs as $song): ?>
                    <div class="card">
                        <?php if (!empty($song['image_path'])): ?>
                            <img src="<?= htmlspecialchars($song['image_path']); ?>" class="card-image" alt="Обложка композиции">
                        <?php endif; ?>

                        <h3><?= htmlspecialchars($song['title']); ?></h3>
                        <p>Жанр: <?= htmlspecialchars($song['genre_name']); ?></p>
                        <p>Рейтинг: <?= number_format((float)$song['average_rating'], 2); ?></p>

                        <div class="card-actions">
                            <a href="song.php?id=<?= (int)$song['id']; ?>" class="button">Перейти</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>У этого исполнителя пока нет композиций.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="index-section">
        <div class="section-head">
            <h2>Последние рецензии</h2>
            <a href="reviews.php">Все рецензии →</a>
        </div>

        <div class="reviews-grid">
    <?php if ($reviews): ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review-card-new">

                <div class="review-top">
                    <div>
                        <div class="review-user"><?= htmlspecialchars($review['username']); ?></div>
                        <div class="review-date"><?= htmlspecialchars($review['created_at']); ?></div>
                    </div>

                    <?php if (!empty($review['image_path'])): ?>
                        <img src="<?= htmlspecialchars($review['image_path']); ?>" alt="Обложка" class="review-cover">
                    <?php endif; ?>
                </div>

                <h3><?= htmlspecialchars($review['review_title'] ?: $review['song_title']); ?></h3>
                <h4 class="review-song-title"><?= htmlspecialchars($review['song_title']); ?></h4>

                <p class="review-preview">
                    <?= nl2br(htmlspecialchars($review['review_text'])); ?>
                </p>

                <details class="review-details">
                    <summary>Показать полностью</summary>

                    <p>
                        <?= nl2br(htmlspecialchars($review['review_text'])); ?>
                    </p>
                </details>

                <a href="song.php?id=<?= (int)$review['song_id']; ?>#reviews" class="button">
                    Открыть композицию
                </a>

            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Рецензий на композиции исполнителя пока нет.</p>
    <?php endif; ?>
</div>
    </section>

</main>

<?php require_once 'includes/footer.php'; ?>
