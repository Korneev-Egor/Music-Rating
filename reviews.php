<?php

require_once 'config.php';
require_once 'includes/functions.php';

$authorId = isset($_GET['author_id']) ? (int)$_GET['author_id'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'new';

$authors = $pdo->query("
    SELECT DISTINCT users.id, users.username
    FROM users
    INNER JOIN reviews ON reviews.user_id = users.id
    ORDER BY users.username
")->fetchAll();

$sql = "
    SELECT
        reviews.id,
        reviews.review_title,
        reviews.review_text,
        reviews.created_at,
        users.id AS user_id,
        users.username,
        songs.id AS song_id,
        songs.title AS song_title,
        songs.image_path
    FROM reviews
    INNER JOIN users ON reviews.user_id = users.id
    INNER JOIN songs ON reviews.song_id = songs.id
    WHERE 1
";

$params = [];

if ($authorId > 0) {
    $sql .= " AND users.id = ?";
    $params[] = $authorId;
}

if ($sort === 'old') {
    $sql .= " ORDER BY reviews.created_at ASC";
} else {
    $sql .= " ORDER BY reviews.created_at DESC";
}

$query = $pdo->prepare($sql);
$query->execute($params);
$reviews = $query->fetchAll();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<main class="content">
    <h1>Рецензии пользователей</h1>

    <form method="GET" class="filter-form">
        <select name="author_id">
            <option value="0">Все авторы</option>

            <?php foreach ($authors as $author): ?>
                <option value="<?= (int)$author['id']; ?>" <?= $authorId === (int)$author['id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($author['username']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="sort">
            <option value="new" <?= $sort === 'new' ? 'selected' : ''; ?>>Сначала новые</option>
            <option value="old" <?= $sort === 'old' ? 'selected' : ''; ?>>Сначала старые</option>
        </select>

        <button type="submit">Применить</button>

        <?php if ($authorId > 0 || $sort !== 'new'): ?>
            <a href="reviews.php" class="reset-link">Сбросить</a>
        <?php endif; ?>
    </form>

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

                    <h3><?= htmlspecialchars($review['review_title']); ?></h3>
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

                    <div class="card-actions">

<a href="song.php?id=<?= (int)$review['song_id']; ?>#reviews" class="button">
    Перейти к композиции
</a>

<?php if (isAdmin()): ?>

    <form
        action="delete_review.php"
        method="POST"
        onsubmit="return confirm('Удалить рецензию?');"
    >
        <input
            type="hidden"
            name="review_id"
            value="<?= (int)$review['id']; ?>"
        >

        <button type="submit" class="delete-icon">
            🗑
        </button>
    </form>

<?php endif; ?>

</div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Рецензии пока не добавлены.</p>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
