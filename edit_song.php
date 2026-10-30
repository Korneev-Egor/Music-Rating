<?php

require_once 'config.php';
require_once 'includes/functions.php';

requireAuth();
requireAdmin();

$songId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = $pdo->prepare("
    SELECT *
    FROM songs
    WHERE id = ?
");
$query->execute([$songId]);
$song = $query->fetch();

if (!$song) {
    die('Композиция не найдена');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = cleanInput($_POST['title']);
    $description = cleanInput($_POST['description']);
    $artistName = cleanInput($_POST['artist']);
    $genreName = cleanInput($_POST['genre']);

    if (empty($title) || empty($artistName) || empty($genreName)) {
        $error = 'Заполните название, исполнителя и жанр';
    } else {
        $artistQuery = $pdo->prepare("SELECT id FROM artists WHERE name = ?");
        $artistQuery->execute([$artistName]);
        $artist = $artistQuery->fetch();

        if ($artist) {
            $artistId = $artist['id'];
        } else {
            $artistInsert = $pdo->prepare("INSERT INTO artists (name) VALUES (?)");
            $artistInsert->execute([$artistName]);
            $artistId = $pdo->lastInsertId();
        }

        $genreQuery = $pdo->prepare("SELECT id FROM genres WHERE name = ?");
        $genreQuery->execute([$genreName]);
        $genre = $genreQuery->fetch();

        if ($genre) {
            $genreId = $genre['id'];
        } else {
            $genreInsert = $pdo->prepare("INSERT INTO genres (name) VALUES (?)");
            $genreInsert->execute([$genreName]);
            $genreId = $pdo->lastInsertId();
        }

        $imagePath = $song['image_path'];

        if (!empty($_FILES['cover']['name'])) {
            $coverName = time() . '_' . basename($_FILES['cover']['name']);
            $coverPath = 'uploads/covers/' . $coverName;

            if (move_uploaded_file($_FILES['cover']['tmp_name'], $coverPath)) {
                $imagePath = $coverPath;
            }
        }

        $updateQuery = $pdo->prepare("
            UPDATE songs
            SET
                title = ?,
                description = ?,
                artist_id = ?,
                genre_id = ?,
                image_path = ?
            WHERE id = ?
        ");

        $updateQuery->execute([
            $title,
            $description,
            $artistId,
            $genreId,
            $imagePath,
            $songId
        ]);

        $success = 'Композиция успешно обновлена';

        $query->execute([$songId]);
        $song = $query->fetch();
    }
}

$artistQuery = $pdo->prepare("SELECT name FROM artists WHERE id = ?");
$artistQuery->execute([$song['artist_id']]);
$artist = $artistQuery->fetch();

$genreQuery = $pdo->prepare("SELECT name FROM genres WHERE id = ?");
$genreQuery->execute([$song['genre_id']]);
$genre = $genreQuery->fetch();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<main class="content">
    <h1>Редактирование композиции</h1>

    <?php if ($success): ?>
        <p class="success-message"><?= $success; ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="error-message"><?= $error; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form">
        <input
            type="text"
            name="title"
            value="<?= $song['title']; ?>"
            placeholder="Название композиции"
        >

        <input
            type="text"
            name="artist"
            value="<?= $artist['name'] ?? ''; ?>"
            placeholder="Исполнитель"
        >

        <input
            type="text"
            name="genre"
            value="<?= $genre['name'] ?? ''; ?>"
            placeholder="Жанр"
        >

        <textarea name="description" placeholder="Описание композиции"><?= $song['description']; ?></textarea>

        <?php if (!empty($song['image_path'])): ?>
            <img src="<?= $song['image_path']; ?>" class="edit-preview" alt="Обложка композиции">
        <?php endif; ?>

        <label>Новая обложка композиции</label>
        <input type="file" name="cover" accept="image/*">

        <button type="submit">Сохранить изменения</button>
    </form>
</main>

<?php

require_once 'includes/footer.php';

?>