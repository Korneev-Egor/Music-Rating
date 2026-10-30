<?php

require_once 'config.php';
require_once 'includes/functions.php';

$songsQuery = $pdo->query("
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
    ORDER BY songs.upload_date DESC
    LIMIT 6
");

$songs = $songsQuery->fetchAll();

$topQuery = $pdo->query("
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
    LIMIT 3
");

$topSongs = $topQuery->fetchAll();

$songsCount = $pdo->query("SELECT COUNT(*) FROM songs")->fetchColumn();
$artistsCount = $pdo->query("SELECT COUNT(*) FROM artists")->fetchColumn();
$ratingsCount = $pdo->query("SELECT COUNT(*) FROM ratings")->fetchColumn();
$reviewsCount = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();


$genres = $pdo->query("
    SELECT id, name
    FROM genres
    ORDER BY name
")->fetchAll();

$artists = $pdo->query("
    SELECT DISTINCT artists.id, artists.name
    FROM artists
    INNER JOIN songs ON songs.artist_id = artists.id
    ORDER BY artists.name
")->fetchAll();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<main class="content index-premium">

    <section class="index-hero">
        <div class="index-hero-content">
            <h1>Музыка.<br>Оценки.<br>Смысл<span>.</span></h1>

            <p>
                Площадка, где музыкальные композиции получают честные оценки,
                подробные рецензии и внимание слушателей.
            </p>

            <div class="index-hero-actions">
                <a href="songs.php" class="button">
                    Найти композицию
                </a>

                <?php if (isUserLoggedIn()): ?>
                    <a href="add_song.php" class="hero-second-button">
                        Добавить композицию
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>


    <section class="index-search-panel">
        <div class="index-search-head">
            <h2>Быстрый поиск композиции</h2>
            <p>Введите название, исполнителя или жанр, а при необходимости уточните параметры поиска.</p>
        </div>

        <form action="songs.php" method="GET" class="index-filter-form">
            <input type="hidden" name="from" value="index">

            <input
                type="text"
                name="search"
                placeholder="Поиск по названию, исполнителю или жанру"
            >

            <select name="genre_id">
                <option value="0">Все жанры</option>
                <?php foreach ($genres as $genre): ?>
                    <option value="<?= $genre['id']; ?>">
                        <?= htmlspecialchars($genre['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="artist_id">
                <option value="0">Все исполнители</option>
                <?php foreach ($artists as $artist): ?>
                    <option value="<?= $artist['id']; ?>">
                        <?= htmlspecialchars($artist['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="sort">
                <option value="new">Сначала новые</option>
                <option value="old">Сначала старые</option>
                <option value="rating_desc">Рейтинг по убыванию</option>
                <option value="rating_asc">Рейтинг по возрастанию</option>
                <option value="title_asc">Название А-Я</option>
                <option value="title_desc">Название Я-А</option>
            </select>

            <button type="submit">Найти</button>
        </form>
    </section>

    <section class="index-stats">
        <h2>Статистика сайта</h2>

        <div class="stats-grid">
            <div class="stat-card">
                <span>♪</span>
                <strong><?= $songsCount; ?></strong>
                <p>Композиции</p>
            </div>

            <div class="stat-card">
                <span>♟</span>
                <strong><?= $artistsCount; ?></strong>
                <p>Исполнители</p>
            </div>

            <div class="stat-card">
                <span>★</span>
                <strong><?= $ratingsCount; ?></strong>
                <p>Оценки</p>
            </div>

            <div class="stat-card">
                <span>✎</span>
                <strong><?= $reviewsCount; ?></strong>
                <p>Рецензии</p>
            </div>
        </div>
    </section>

    <section class="index-section">
        <div class="section-head">
            <h2>Топ композиций</h2>
            <a href="rating.php">Смотреть рейтинг →</a>
        </div>

        <div class="top-grid">
            <?php if ($topSongs): ?>
                <?php $place = 1; ?>

                <?php foreach ($topSongs as $song): ?>
                    <?php
                        $rating = floatval($song['average_rating']);
                        $percent = ($rating / 5) * 100;

                        $ratingClass = 'rating-low';

                        if ($rating >= 4.5) {
                            $ratingClass = 'rating-gold';
                        } elseif ($rating >= 4.0) {
                            $ratingClass = 'rating-blue';
                        } elseif ($rating >= 3.0) {
                            $ratingClass = 'rating-bronze';
                        } elseif ($rating >= 1.5) {
                            $ratingClass = 'rating-purple';
                        }
                    ?>

                    <a href="song.php?id=<?= $song['id']; ?>" class="top-card">
                        <div class="top-place"><?= $place; ?></div>

                        <?php if (!empty($song['image_path'])): ?>
                            <img src="<?= $song['image_path']; ?>" alt="Обложка">
                        <?php endif; ?>

                        <div class="top-info">
                            <h3><?= htmlspecialchars($song['title']); ?></h3>
                            <p>Исполнитель: <?= htmlspecialchars($song['artist_name']); ?></p>
                            <span><?= htmlspecialchars($song['genre_name']); ?></span>
                        </div>

                        <div class="rating-circle <?= $ratingClass; ?>" style="--score-percent: <?= $percent; ?>%;">
                            <span><?= number_format($rating, 1); ?></span>
                        </div>
                    </a>

                    <?php $place++; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Композиции пока не добавлены.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="index-section">
        <div class="section-head">
            <h2>Последние добавленные композиции</h2>
            <a href="songs.php">Смотреть все →</a>
        </div>

        <div class="cards">
            <?php if ($songs): ?>
                <?php foreach ($songs as $song): ?>
                    <?php
                        $rating = floatval($song['average_rating']);
                        $percent = ($rating / 5) * 100;

                        $ratingClass = 'rating-low';

                        if ($rating >= 4.5) {
                            $ratingClass = 'rating-gold';
                        } elseif ($rating >= 4.0) {
                            $ratingClass = 'rating-blue';
                        } elseif ($rating >= 3.0) {
                            $ratingClass = 'rating-bronze';
                        } elseif ($rating >= 1.5) {
                            $ratingClass = 'rating-purple';
                        }
                    ?>

                    <div class="card">
                        <?php if (!empty($song['image_path'])): ?>
                            <img src="<?= $song['image_path']; ?>" alt="Обложка композиции" class="card-image">
                        <?php endif; ?>

                        <h3><?= htmlspecialchars($song['title']); ?></h3>

                        <p>Исполнитель: <?= htmlspecialchars($song['artist_name']); ?></p>
                        <p>Жанр: <?= htmlspecialchars($song['genre_name']); ?></p>

                        <div class="rating-circle <?= $ratingClass; ?>" style="--score-percent: <?= $percent; ?>%;">
                            <span><?= number_format($rating, 1); ?></span>
                        </div>

                        <div class="card-actions">
                            <a href="song.php?id=<?= $song['id']; ?>" class="button">
                                Оценить
                            </a>

                            <?php if (isAdmin()): ?>
                                <a href="edit_song.php?id=<?= $song['id']; ?>" class="edit-button">
                                    Редактировать
                                </a>

                                <form
                                    action="delete_song.php"
                                    method="POST"
                                    onsubmit="return confirm('Удалить композицию?');"
                                >
                                    <input type="hidden" name="song_id" value="<?= $song['id']; ?>">

                                    <button type="submit" class="delete-icon">
                                        🗑
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Музыкальные композиции пока не добавлены.</p>
            <?php endif; ?>
        </div>
    </section>

</main>

<?php require_once 'includes/footer.php'; ?>  