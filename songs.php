<?php

require_once 'config.php';
require_once 'includes/functions.php';

$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$genreId = isset($_GET['genre_id']) ? (int)$_GET['genre_id'] : 0;
$artistId = isset($_GET['artist_id']) ? (int)$_GET['artist_id'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'new';
$fromIndex = isset($_GET['from']) && $_GET['from'] === 'index';

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

$sql = "
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
    WHERE 1
";

$params = [];

if ($search !== '') {
    $sql .= "
        AND (
            songs.title LIKE ?
            OR artists.name LIKE ?
            OR genres.name LIKE ?
        )
    ";

    $searchValue = '%' . $search . '%';
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}

if ($genreId > 0) {
    $sql .= " AND genres.id = ?";
    $params[] = $genreId;
}

if ($artistId > 0) {
    $sql .= " AND artists.id = ?";
    $params[] = $artistId;
}

switch ($sort) {
    case 'old':
        $sql .= " ORDER BY songs.upload_date ASC";
        break;
    case 'rating_desc':
        $sql .= " ORDER BY songs.average_rating DESC";
        break;
    case 'rating_asc':
        $sql .= " ORDER BY songs.average_rating ASC";
        break;
    case 'title_asc':
        $sql .= " ORDER BY songs.title ASC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY songs.title DESC";
        break;
    default:
        $sql .= " ORDER BY songs.upload_date DESC";
        break;
}

$query = $pdo->prepare($sql);
$query->execute($params);
$songs = $query->fetchAll();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<main class="content">
    <h1>Музыкальные композиции</h1>

    <form method="GET" class="filter-form">
        <?php if ($fromIndex): ?>
            <input type="hidden" name="from" value="index">
        <?php endif; ?>

        <input
            type="text"
            name="search"
            placeholder="Поиск по названию, исполнителю или жанру"
            value="<?= $search; ?>"
        >

        <select name="genre_id">
            <option value="0">Все жанры</option>

            <?php foreach ($genres as $genre): ?>
                <option value="<?= $genre['id']; ?>" <?= $genreId === (int)$genre['id'] ? 'selected' : ''; ?>>
                    <?= $genre['name']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="artist_id">
            <option value="0">Все исполнители</option>

            <?php foreach ($artists as $artist): ?>
                <option value="<?= $artist['id']; ?>" <?= $artistId === (int)$artist['id'] ? 'selected' : ''; ?>>
                    <?= $artist['name']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="sort">
            <option value="new" <?= $sort === 'new' ? 'selected' : ''; ?>>Сначала новые</option>
            <option value="old" <?= $sort === 'old' ? 'selected' : ''; ?>>Сначала старые</option>
            <option value="rating_desc" <?= $sort === 'rating_desc' ? 'selected' : ''; ?>>Рейтинг по убыванию</option>
            <option value="rating_asc" <?= $sort === 'rating_asc' ? 'selected' : ''; ?>>Рейтинг по возрастанию</option>
            <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : ''; ?>>Название А-Я</option>
            <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : ''; ?>>Название Я-А</option>
        </select>

        <button type="submit">Применить</button>

        <?php if ($search !== '' || $genreId > 0 || $artistId > 0 || $sort !== 'new'): ?>
            <a href="<?= $fromIndex ? 'index.php' : 'songs.php'; ?>" class="reset-link">
                Сбросить
            </a>
        <?php endif; ?>
    </form>

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
                        <img src="<?= $song['image_path']; ?>" class="card-image" alt="Обложка композиции">
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

                    <div class="card-actions">
                        <a href="song.php?id=<?= $song['id']; ?>" class="button">
                            Подробнее
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
            <p>Композиции не найдены.</p>
        <?php endif; ?>
    </div>
</main>

<?php

require_once 'includes/footer.php';

?>