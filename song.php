<?php

require_once 'config.php';
require_once 'includes/functions.php';

$songId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$error = '';
$success = '';
$reviewError = '';
$reviewSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rate_song'])) {
    requireAuth();

    $userId = getCurrentUserId();

    $lyricsScore = (int) $_POST['lyrics_score'];
    $rhythmScore = (int) $_POST['rhythm_score'];
    $styleScore = (int) $_POST['style_score'];
    $individualityScore = (int) $_POST['individuality_score'];
    $atmosphereScore = (int) $_POST['atmosphere_score'];

    $scores = [$lyricsScore, $rhythmScore, $styleScore, $individualityScore, $atmosphereScore];

    if (min($scores) < 1 || max($scores) > 5) {
        $error = 'Выберите оценку от 1 до 5 по каждому критерию';
    } else {
        $averageScore = round(array_sum($scores) / count($scores), 2);

        $checkQuery = $pdo->prepare("SELECT id FROM ratings WHERE user_id = ? AND song_id = ?");
        $checkQuery->execute([$userId, $songId]);
        $existingRating = $checkQuery->fetch();

        if ($existingRating) {
            $ratingQuery = $pdo->prepare("
                UPDATE ratings
                SET lyrics_score = ?, rhythm_score = ?, style_score = ?,
                    individuality_score = ?, atmosphere_score = ?, average_score = ?
                WHERE user_id = ? AND song_id = ?
            ");

            $ratingQuery->execute([
                $lyricsScore,
                $rhythmScore,
                $styleScore,
                $individualityScore,
                $atmosphereScore,
                $averageScore,
                $userId,
                $songId
            ]);
        } else {
            $ratingQuery = $pdo->prepare("
                INSERT INTO ratings
                (user_id, song_id, lyrics_score, rhythm_score, style_score, individuality_score, atmosphere_score, average_score)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $ratingQuery->execute([
                $userId,
                $songId,
                $lyricsScore,
                $rhythmScore,
                $styleScore,
                $individualityScore,
                $atmosphereScore,
                $averageScore
            ]);
        }

        $averageQuery = $pdo->prepare("SELECT AVG(average_score) AS average_rating FROM ratings WHERE song_id = ?");
        $averageQuery->execute([$songId]);
        $average = $averageQuery->fetch();

        $newAverageRating = round($average['average_rating'], 2);

        $updateSongQuery = $pdo->prepare("UPDATE songs SET average_rating = ? WHERE id = ?");
        $updateSongQuery->execute([$newAverageRating, $songId]);

        $success = 'Оценка успешно сохранена';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
    requireAuth();

    $reviewTitle = trim($_POST['review_title']);
    $reviewText = trim($_POST['review_text']);
    $userId = getCurrentUserId();

    if (mb_strlen($reviewTitle) < 3) {
        $reviewError = 'Заголовок рецензии слишком короткий';
    } elseif (mb_strlen($reviewText) < 5) {
        $reviewError = 'Рецензия слишком короткая';
    } else {
        $checkReviewQuery = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND song_id = ?");
        $checkReviewQuery->execute([$userId, $songId]);
        $existingReview = $checkReviewQuery->fetch();

        if ($existingReview) {
            $reviewQuery = $pdo->prepare("
                UPDATE reviews
                SET review_title = ?, review_text = ?
                WHERE user_id = ? AND song_id = ?
            ");

            $reviewQuery->execute([$reviewTitle, $reviewText, $userId, $songId]);
            $reviewSuccess = 'Рецензия успешно обновлена';
        } else {
            $reviewQuery = $pdo->prepare("
                INSERT INTO reviews
                (user_id, song_id, review_title, review_text)
                VALUES (?, ?, ?, ?)
            ");

            $reviewQuery->execute([$userId, $songId, $reviewTitle, $reviewText]);
            $reviewSuccess = 'Рецензия успешно добавлена';
        }
    }
}

$query = $pdo->prepare("
    SELECT
        songs.id,
        songs.title,
        songs.description,
        songs.song_path,
        songs.image_path,
        songs.average_rating,
        songs.plays,
        artists.name AS artist_name,
        genres.name AS genre_name
    FROM songs
    INNER JOIN artists ON songs.artist_id = artists.id
    INNER JOIN genres ON songs.genre_id = genres.id
    WHERE songs.id = ?
");
$query->execute([$songId]);
$song = $query->fetch();

if (!$song) {
    die('Композиция не найдена');
}

$criteriaQuery = $pdo->prepare("
    SELECT
        AVG(lyrics_score) AS lyrics_avg,
        AVG(rhythm_score) AS rhythm_avg,
        AVG(style_score) AS style_avg,
        AVG(individuality_score) AS individuality_avg,
        AVG(atmosphere_score) AS atmosphere_avg
    FROM ratings
    WHERE song_id = ?
");
$criteriaQuery->execute([$songId]);
$criteria = $criteriaQuery->fetch();

$lyricsAvg = $criteria['lyrics_avg'] !== null ? round($criteria['lyrics_avg'], 2) : 0;
$rhythmAvg = $criteria['rhythm_avg'] !== null ? round($criteria['rhythm_avg'], 2) : 0;
$styleAvg = $criteria['style_avg'] !== null ? round($criteria['style_avg'], 2) : 0;
$individualityAvg = $criteria['individuality_avg'] !== null ? round($criteria['individuality_avg'], 2) : 0;
$atmosphereAvg = $criteria['atmosphere_avg'] !== null ? round($criteria['atmosphere_avg'], 2) : 0;

$criteriaItems = [
    ['name' => 'Рифмы', 'full' => 'Рифмы и образы', 'value' => $lyricsAvg, 'class' => 'circle-pink'],
    ['name' => 'Ритм', 'full' => 'Структура и ритмика', 'value' => $rhythmAvg, 'class' => 'circle-blue'],
    ['name' => 'Стиль', 'full' => 'Реализация стиля', 'value' => $styleAvg, 'class' => 'circle-orange'],
    ['name' => 'Харизма', 'full' => 'Индивидуальность и харизма', 'value' => $individualityAvg, 'class' => 'circle-purple'],
    ['name' => 'Атмосфера', 'full' => 'Атмосфера', 'value' => $atmosphereAvg, 'class' => 'circle-gold']
];

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

$userReviewTitle = '';
$userReviewText = '';

if (isUserLoggedIn()) {
    $userReviewQuery = $pdo->prepare("
        SELECT review_title, review_text
        FROM reviews
        WHERE user_id = ? AND song_id = ?
    ");

    $userReviewQuery->execute([getCurrentUserId(), $songId]);
    $userReview = $userReviewQuery->fetch();

    if ($userReview) {
        $userReviewTitle = $userReview['review_title'];
        $userReviewText = $userReview['review_text'];
    }
}

$reviewsQuery = $pdo->prepare("
    SELECT
        reviews.review_title,
        reviews.review_text,
        reviews.created_at,
        users.username
    FROM reviews
    INNER JOIN users ON reviews.user_id = users.id
    WHERE reviews.song_id = ?
    ORDER BY reviews.created_at DESC
");
$reviewsQuery->execute([$songId]);
$songReviews = $reviewsQuery->fetchAll();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<main class="content">
    <section class="song-premium">

        <div class="song-hero-card">
            <?php if (!empty($song['image_path'])): ?>
                <div class="song-cover-wrap">
                    <img src="<?= htmlspecialchars($song['image_path']); ?>" alt="Обложка композиции" class="song-cover-premium">
                </div>
            <?php endif; ?>

            <div class="song-main-info">
                <p class="song-label">Музыкальная композиция</p>

                <h1><?= htmlspecialchars($song['title']); ?></h1>

                <div class="song-meta">
                    <span><?= htmlspecialchars($song['artist_name']); ?></span>
                    <span><?= htmlspecialchars($song['genre_name']); ?></span>
                    <span><?= (int)$song['plays']; ?> прослушиваний</span>
                </div>

                <?php if (!empty($song['description'])): ?>
                    <p class="song-description-premium">
                        <?= nl2br(htmlspecialchars($song['description'])); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($song['song_path'])): ?>
                    <div class="custom-player">
                        <audio id="audioPlayer" src="<?= htmlspecialchars($song['song_path']); ?>"></audio>

                        <button type="button" class="player-play" id="playButton">▶</button>

                        <div class="player-info">
                            <div class="player-title"><?= htmlspecialchars($song['title']); ?></div>
                            <div class="player-artist"><?= htmlspecialchars($song['artist_name']); ?></div>

                            <div class="player-progress-wrap">
                                <span id="currentTime">0:00</span>

                                <input
                                    type="range"
                                    id="progressBar"
                                    class="player-progress"
                                    value="0"
                                    min="0"
                                    max="100"
                                >

                                <span id="durationTime">0:00</span>
                            </div>
                        </div>

                        <div class="volume-control">
                            <button type="button" class="player-volume" id="volumeButton">🔊</button>

                            <input
                                type="range"
                                id="volumeRange"
                                class="volume-range"
                                min="0"
                                max="1"
                                step="0.01"
                                value="1"
                            >
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="song-rating-side">
                <div class="rating-circle <?= $ratingClass; ?>" style="--score-percent: <?= $percent; ?>%;">
                    <span><?= number_format($rating, 1); ?></span>
                </div>

                <p>Общий рейтинг</p>
            </div>
        </div>

        <section class="song-analytics">
            <div class="analytics-card">
                <h2>Детальная оценка</h2>

                <div class="progress-list">
                    <?php foreach ($criteriaItems as $item): ?>
                        <?php $itemPercent = ($item['value'] / 5) * 100; ?>

                        <div class="progress-item">
                            <div class="progress-head">
                                <span><?= $item['full']; ?></span>
                                <b><?= number_format($item['value'], 2); ?></b>
                            </div>

                            <div class="progress-track">
                                <div class="progress-fill" style="width: <?= $itemPercent; ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="analytics-card circle-map-card">
                <h2>Карта оценки</h2>

                <div class="circle-score-map">
                    <div class="center-score">
                        <strong><?= number_format($rating, 1); ?></strong>
                        <span>общий</span>
                    </div>

                    <?php foreach ($criteriaItems as $index => $item): ?>
                        <?php
                            $itemPercent = ($item['value'] / 5) * 100;
                            $positionClass = 'score-dot-' . ($index + 1);
                        ?>

                        <div class="score-dot <?= $positionClass; ?> <?= $item['class']; ?>">
                            <div class="mini-circle" style="--mini-percent: <?= $itemPercent; ?>%;">
                                <span><?= number_format($item['value'], 1); ?></span>
                            </div>

                            <p><?= $item['name']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="rating-block premium-block">
            <h2>Оценить композицию</h2>

            <?php if ($error): ?>
                <p class="error-message"><?= $error; ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <p class="success-message"><?= $success; ?></p>
            <?php endif; ?>

            <?php if (isUserLoggedIn()): ?>
                <form method="POST" class="criteria-form">
                    <label>Рифмы и образы
                        <select name="lyrics_score" required>
                            <option value="">Оценка</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </label>

                    <label>Структура и ритмика
                        <select name="rhythm_score" required>
                            <option value="">Оценка</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </label>

                    <label>Реализация стиля
                        <select name="style_score" required>
                            <option value="">Оценка</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </label>

                    <label>Индивидуальность и харизма
                        <select name="individuality_score" required>
                            <option value="">Оценка</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </label>

                    <label>Атмосфера
                        <select name="atmosphere_score" required>
                            <option value="">Оценка</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </label>

                    <button type="submit" name="rate_song">Сохранить оценку</button>
                </form>
            <?php else: ?>
                <p>Чтобы оценить композицию, необходимо войти в аккаунт.</p>
            <?php endif; ?>
        </section>

        <section class="reviews-block premium-block">
            <h2 id="reviews">Рецензии</h2>

            <?php if ($reviewError): ?>
                <p class="error-message"><?= $reviewError; ?></p>
            <?php endif; ?>

            <?php if ($reviewSuccess): ?>
                <p class="success-message"><?= $reviewSuccess; ?></p>
            <?php endif; ?>

            <?php if (isUserLoggedIn()): ?>
                <details class="review-editor">
                    <summary><?= $userReviewText ? 'Изменить рецензию' : 'Написать рецензию'; ?></summary>

                    <form method="POST" class="review-form">
                        <input
                            type="text"
                            name="review_title"
                            placeholder="Заголовок рецензии"
                            value="<?= htmlspecialchars($userReviewTitle); ?>"
                            required
                        >

                        <textarea
                            name="review_text"
                            placeholder="Напишите рецензию..."
                            required
                        ><?= htmlspecialchars($userReviewText); ?></textarea>

                        <button type="submit" name="add_review">
                            <?= $userReviewText ? 'Обновить рецензию' : 'Опубликовать'; ?>
                        </button>
                    </form>
                </details>
            <?php endif; ?>

            <div class="reviews-list">
                <?php if ($songReviews): ?>
                    <?php foreach ($songReviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <strong><?= htmlspecialchars($review['username']); ?></strong>
                                <span><?= $review['created_at']; ?></span>
                            </div>

                            <h3><?= htmlspecialchars($review['review_title']); ?></h3>

                            <p class="review-short">
                                <?= nl2br(htmlspecialchars(mb_substr($review['review_text'], 0, 260))); ?>
                                <?= mb_strlen($review['review_text']) > 260 ? '...' : ''; ?>
                            </p>

                            <?php if (mb_strlen($review['review_text']) > 260): ?>
                                <details class="review-full">
                                    <summary>Читать далее</summary>
                                    <p><?= nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                </details>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Рецензий пока нет.</p>
                <?php endif; ?>
            </div>
        </section>

    </section>
</main>

<script>
const audio = document.getElementById('audioPlayer');
const playButton = document.getElementById('playButton');
const progressBar = document.getElementById('progressBar');
const currentTimeText = document.getElementById('currentTime');
const durationTimeText = document.getElementById('durationTime');
const volumeButton = document.getElementById('volumeButton');
const volumeRange = document.getElementById('volumeRange');

let playCounted = false;

function formatTime(seconds) {
    if (isNaN(seconds)) {
        return '0:00';
    }

    const minutes = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60).toString().padStart(2, '0');

    return minutes + ':' + secs;
}

function updateVolumeStyle() {
    const volumePercent = volumeRange.value * 100;
    volumeRange.style.setProperty('--volume-progress', volumePercent + '%');
}

if (audio) {
    audio.volume = 1;
    updateVolumeStyle();

    playButton.addEventListener('click', function () {
        if (audio.paused) {
            audio.play();
            playButton.textContent = '❚❚';
        } else {
            audio.pause();
            playButton.textContent = '▶';
        }
    });

    audio.addEventListener('play', function () {
        if (!playCounted) {
            playCounted = true;

            fetch('update_play.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'song_id=<?= (int)$song['id']; ?>'
            });
        }
    });

    audio.addEventListener('loadedmetadata', function () {
        durationTimeText.textContent = formatTime(audio.duration);
    });

    audio.addEventListener('timeupdate', function () {
        const progress = (audio.currentTime / audio.duration) * 100;

        progressBar.value = progress || 0;
        progressBar.style.setProperty('--player-progress', progress + '%');
        currentTimeText.textContent = formatTime(audio.currentTime);
    });

    progressBar.addEventListener('input', function () {
        audio.currentTime = (progressBar.value / 100) * audio.duration;
    });

    volumeRange.addEventListener('input', function () {
        audio.volume = volumeRange.value;

        audio.muted = audio.volume == 0;
        volumeButton.textContent = audio.muted ? '🔇' : '🔊';

        updateVolumeStyle();
    });

    volumeButton.addEventListener('click', function () {
        audio.muted = !audio.muted;

        if (audio.muted) {
            volumeButton.textContent = '🔇';
        } else {
            volumeButton.textContent = '🔊';

            if (audio.volume == 0) {
                audio.volume = 0.5;
                volumeRange.value = 0.5;
                updateVolumeStyle();
            }
        }
    });

    audio.addEventListener('ended', function () {
        playButton.textContent = '▶';
        progressBar.value = 0;
        progressBar.style.setProperty('--player-progress', '0%');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>