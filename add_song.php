<?php

require_once 'config.php';
require_once 'includes/functions.php';

requireAuth();
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = cleanInput($_POST['title']);
    $artistName = cleanInput($_POST['artist_name']);
    $genreName = cleanInput($_POST['genre_name']);
    $description = cleanInput($_POST['description']);
    $userId = getCurrentUserId();

    $audioPath = '';
    $coverPath = '';

    if (empty($title) || empty($artistName) || empty($genreName)) {
        $error = 'Заполните обязательные поля';
    } elseif (empty($_FILES['audio_file']['name'])) {
        $error = 'Загрузите аудиофайл';
    } else {
        $audioExtension = strtolower(pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION));
        $allowedAudioExtensions = ['mp3', 'wav'];

        if (!in_array($audioExtension, $allowedAudioExtensions)) {
            $error = 'Допустимые форматы аудио: MP3, WAV';
        }

        if (!$error && !empty($_FILES['cover_file']['name'])) {
            $coverExtension = strtolower(pathinfo($_FILES['cover_file']['name'], PATHINFO_EXTENSION));
            $allowedCoverExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($coverExtension, $allowedCoverExtensions)) {
                $error = 'Допустимые форматы обложки: JPG, JPEG, PNG, WEBP';
            }
        }
        
if (!$error && !empty($_FILES['cover_file']['name'])) {
    $imageInfo = @getimagesize($_FILES['cover_file']['tmp_name']);

    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if ($imageInfo === false || !in_array($imageInfo['mime'], $allowedMimeTypes, true)) {
        $error = 'Загрузите настоящее изображение JPG, PNG или WEBP';
    }
}

        if (!$error) {
            $audioFileName = uniqid('audio_', true) . '.' . $audioExtension;
            $audioUploadPath = 'uploads/audio/' . $audioFileName;

            if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $audioUploadPath)) {
                $audioPath = $audioUploadPath;
            } else {
                $error = 'Ошибка загрузки аудиофайла';
            }
        }

        if (!$error && !empty($_FILES['cover_file']['name'])) {
            $coverFileName = uniqid('cover_', true) . '.' . $coverExtension;
            $coverUploadPath = 'uploads/covers/' . $coverFileName;

            if (move_uploaded_file($_FILES['cover_file']['tmp_name'], $coverUploadPath)) {
                $coverPath = $coverUploadPath;
            } else {
                $error = 'Ошибка загрузки обложки';
            }
        }

        if (!$error) {
            $artistQuery = $pdo->prepare("
                SELECT id
                FROM artists
                WHERE name = ?
            ");
            $artistQuery->execute([$artistName]);
            $artist = $artistQuery->fetch();

            if ($artist) {
                $artistId = $artist['id'];
            } else {
                $insertArtist = $pdo->prepare("
                    INSERT INTO artists (name)
                    VALUES (?)
                ");
                $insertArtist->execute([$artistName]);
                $artistId = $pdo->lastInsertId();
            }

            $genreQuery = $pdo->prepare("
                SELECT id
                FROM genres
                WHERE name = ?
            ");
            $genreQuery->execute([$genreName]);
            $genre = $genreQuery->fetch();

            if ($genre) {
                $genreId = $genre['id'];
            } else {
                $insertGenre = $pdo->prepare("
                    INSERT INTO genres (name)
                    VALUES (?)
                ");
                $insertGenre->execute([$genreName]);
                $genreId = $pdo->lastInsertId();
            }

            $query = $pdo->prepare("
                INSERT INTO songs
                (title, artist_id, genre_id, description, song_path, image_path, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $query->execute([
                $title,
                $artistId,
                $genreId,
                $description,
                $audioPath,
                $coverPath,
                $userId
            ]);

            $success = 'Музыкальная композиция успешно добавлена';
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<main class="content">
    <h1>Добавление музыкальной композиции</h1>

    <?php if ($error): ?>
        <p class="error-message">
            <?= $error; ?>
        </p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success-message">
            <?= $success; ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="form" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Название композиции">
        <input type="text" name="artist_name" placeholder="Исполнитель">
        <input type="text" name="genre_name" placeholder="Жанр">

        <textarea name="description" placeholder="Описание композиции"></textarea>

        <label>Аудиофайл MP3 или WAV</label>
        <input type="file" name="audio_file" accept=".mp3,.wav">

        <label>Обложка композиции</label>
        <input type="file" name="cover_file" accept=".jpg,.jpeg,.png,.webp">

        <button type="submit">Добавить композицию</button>
    </form>
</main>

<?php

require_once 'includes/footer.php';

?>