<?php

require_once 'config.php';
require_once 'includes/functions.php';

requireAuth();

$userId = getCurrentUserId();

function ensureDisplayNameColumn($pdo) {
    try {
        $check = $pdo->query("SHOW COLUMNS FROM users LIKE 'display_name'");
        if (!$check->fetch()) {
            $pdo->exec("ALTER TABLE users ADD display_name VARCHAR(80) NULL AFTER username");
        }
    } catch (PDOException $e) {
        // Если прав на ALTER нет, сайт продолжит работать по username.
    }
}

ensureDisplayNameColumn($pdo);

function getProfileRatingClass($rating) {
    if ($rating >= 4.5) {
        return 'rating-gold';
    }

    if ($rating >= 4.0) {
        return 'rating-blue';
    }

    if ($rating >= 3.0) {
        return 'rating-bronze';
    }

    if ($rating >= 1.5) {
        return 'rating-purple';
    }

    return 'rating-low';
}


$error = '';
$success = '';

$userQuery = $pdo->prepare("SELECT id, username, email, display_name FROM users WHERE id = ?");
$userQuery->execute([$userId]);
$user = $userQuery->fetch();

if (!$user) {
    die('Пользователь не найден');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $displayName = cleanInput($_POST['display_name'] ?? '');

    if ($displayName === '') {
        $error = 'Введите отображаемое имя';
    } elseif (mb_strlen($displayName, 'UTF-8') < 2) {
        $error = 'Имя должно быть не короче 2 символов';
    } elseif (mb_strlen($displayName, 'UTF-8') > 40) {
        $error = 'Имя должно быть не длиннее 40 символов';
    } else {
        $updateQuery = $pdo->prepare("UPDATE users SET display_name = ? WHERE id = ?");
        $updateQuery->execute([$displayName, $userId]);
        $success = 'Профиль обновлен';

        $userQuery->execute([$userId]);
        $user = $userQuery->fetch();
    }
}

$displayName = !empty($user['display_name']) ? $user['display_name'] : $user['username'];
$profileLetter = mb_substr($displayName, 0, 1, 'UTF-8');

$songsQuery = $pdo->prepare("
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
    WHERE songs.user_id = ?
    ORDER BY songs.id DESC
");

$songsQuery->execute([$userId]);
$userSongs = $songsQuery->fetchAll();

$ratingsQuery = $pdo->prepare("
    SELECT
        ratings.average_score,
        songs.id AS song_id,
        songs.title AS song_title,
        songs.image_path,
        artists.name AS artist_name,
        genres.name AS genre_name
    FROM ratings
    INNER JOIN songs ON ratings.song_id = songs.id
    INNER JOIN artists ON songs.artist_id = artists.id
    INNER JOIN genres ON songs.genre_id = genres.id
    WHERE ratings.user_id = ?
    ORDER BY ratings.created_at DESC
");

$ratingsQuery->execute([$userId]);
$userRatings = $ratingsQuery->fetchAll();

$reviewsQuery = $pdo->prepare("
    SELECT
        reviews.review_title,
        reviews.review_text,
        reviews.created_at,
        songs.id AS song_id,
        songs.title AS song_title,
        songs.image_path
    FROM reviews
    INNER JOIN songs ON reviews.song_id = songs.id
    WHERE reviews.user_id = ?
    ORDER BY reviews.created_at DESC
");

$reviewsQuery->execute([$userId]);
$userReviews = $reviewsQuery->fetchAll();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<main class="content profile-page">

    <section class="profile-hero">
        <div class="profile-avatar">
            <?= htmlspecialchars($profileLetter); ?>
        </div>

        <div class="profile-main-info">
            <p class="section-label">Личный кабинет</p>

            <div class="profile-title-row">
                <h1><?= htmlspecialchars($displayName); ?></h1>

                <?php if (isAdmin()): ?>
                    <span class="admin-badge">Администратор</span>
                <?php endif; ?>
            </div>

            <p class="profile-login">Логин: <?= htmlspecialchars($user['username']); ?></p>
            <p class="profile-email">Email: <?= htmlspecialchars($user['email']); ?></p>
        </div>

        <div class="profile-stats">

<?php if (isAdmin()): ?>
    <div>
        <strong><?= count($userSongs); ?></strong>
        <span>Композиции</span>
    </div>
<?php endif; ?>

<div>
    <strong><?= count($userRatings); ?></strong>
    <span>Оценки</span>
</div>

<div>
    <strong><?= count($userReviews); ?></strong>
    <span>Рецензии</span>
</div>

</div>
    </section>

    <section class="profile-edit-card">
        <div>
            <h2>Редактирование профиля</h2>
            <p>Здесь можно изменить имя, которое отображается в шапке сайта и профиле.</p>
        </div>

        <?php if ($error): ?>
            <p class="error-message"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success-message"><?= htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="POST" class="profile-edit-form">
            <input
                type="text"
                name="display_name"
                value="<?= htmlspecialchars($displayName); ?>"
                placeholder="Отображаемое имя"
            >

            <button type="submit">Сохранить имя</button>
        </form>
    </section>

    <?php if (isAdmin()): ?>
        <section class="main-section">
            <h2>Добавленные композиции</h2>

            <div class="cards profile-cards">
                <?php if ($userSongs): ?>
                    <?php foreach ($userSongs as $song): ?>
                        <?php
                            $rating = (float)$song['average_rating'];
                            $percent = ($rating / 5) * 100;
                            $ratingClass = getProfileRatingClass($rating);
                        ?>

                        <div class="card">
                            <?php if (!empty($song['image_path'])): ?>
                                <img src="<?= htmlspecialchars($song['image_path']); ?>" class="card-image" alt="Обложка композиции">
                            <?php endif; ?>

                            <h3><?= htmlspecialchars($song['title']); ?></h3>
                            <p>Исполнитель: <?= htmlspecialchars($song['artist_name']); ?></p>
                            <p>Жанр: <?= htmlspecialchars($song['genre_name']); ?></p>

                            <div class="rating-circle <?= $ratingClass; ?>" style="--score-percent: <?= $percent; ?>%;">
                                <span><?= number_format($rating, 1); ?></span>
                            </div>

                            <div class="card-actions">
                                <a href="song.php?id=<?= (int)$song['id']; ?>" class="button">
                                    Подробнее
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-text">Вы пока не добавили ни одной композиции.</p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="main-section">
        <h2>Мои оценки</h2>

        <div class="cards profile-cards">
            <?php if ($userRatings): ?>
                <?php foreach ($userRatings as $rating): ?>
                    <?php
                        $score = (float)$rating['average_score'];
                        $percent = ($score / 5) * 100;
                        $ratingClass = getProfileRatingClass($score);
                    ?>

                    <div class="card">
                        <?php if (!empty($rating['image_path'])): ?>
                            <img src="<?= htmlspecialchars($rating['image_path']); ?>" class="card-image" alt="Обложка композиции">
                        <?php endif; ?>

                        <h3><?= htmlspecialchars($rating['song_title']); ?></h3>
                        <p>Исполнитель: <?= htmlspecialchars($rating['artist_name']); ?></p>
                        <p>Жанр: <?= htmlspecialchars($rating['genre_name']); ?></p>

                        <div class="rating-circle <?= $ratingClass; ?>" style="--score-percent: <?= $percent; ?>%;">
                            <span><?= number_format($score, 1); ?></span>
                        </div>

                        <div class="card-actions">
                            <a href="song.php?id=<?= (int)$rating['song_id']; ?>" class="button">
                                Подробнее
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-text">Вы пока не оценивали композиции.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="main-section">
        <h2>Мои рецензии</h2>

        <div class="reviews-grid profile-reviews-grid">
            <?php if ($userReviews): ?>
                <?php foreach ($userReviews as $review): ?>
                    <div class="review-card-new">
                        <div class="review-top">
                            <div>
                                <div class="review-user"><?= htmlspecialchars($displayName); ?></div>
                                <div class="review-date"><?= htmlspecialchars($review['created_at']); ?></div>
                            </div>

                            <?php if (!empty($review['image_path'])): ?>
                                <img src="<?= htmlspecialchars($review['image_path']); ?>" alt="Обложка" class="review-cover">
                            <?php endif; ?>
                        </div>

                        <h3><?= htmlspecialchars($review['review_title'] ?: 'Без заголовка'); ?></h3>
                        <h4 class="review-song-title"><?= htmlspecialchars($review['song_title']); ?></h4>

                        <p class="review-preview">
                            <?= nl2br(htmlspecialchars($review['review_text'])); ?>
                        </p>

                        <details class="review-details">
                            <summary>Показать полностью</summary>
                            <p><?= nl2br(htmlspecialchars($review['review_text'])); ?></p>
                        </details>

                        <a href="song.php?id=<?= (int)$review['song_id']; ?>#reviews" class="button">
                            Перейти к композиции
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-text">Вы пока не писали рецензии.</p>
            <?php endif; ?>
        </div>
    </section>

</main>

<?php require_once 'includes/footer.php'; ?>
