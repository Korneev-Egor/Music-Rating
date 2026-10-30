<?php

require_once 'config.php';
require_once 'includes/functions.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

$artistId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = $pdo->prepare("SELECT * FROM artists WHERE id = ?");
$query->execute([$artistId]);
$artist = $query->fetch();

if (!$artist) {
    die('Исполнитель не найден');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    $imagePath = $artist['image_path'];

    if ($name === '') {
        $error = 'Введите имя исполнителя';
    } else {
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = 'uploads/covers/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extension, $allowed, true)) {
                $error = 'Можно загрузить только JPG, PNG или WEBP';
            } else {
                $fileName = 'artist_' . uniqid('', true) . '.' . $extension;
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imagePath = $targetPath;
                } else {
                    $error = 'Не удалось загрузить изображение';
                }
            }
        }

        if ($error === '') {
            $updateQuery = $pdo->prepare("
                UPDATE artists
                SET name = ?, description = ?, image_path = ?
                WHERE id = ?
            ");

            $updateQuery->execute([
                $name,
                $description !== '' ? $description : null,
                $imagePath,
                $artistId
            ]);

            header('Location: artist.php?id=' . $artistId);
            exit;
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<main class="content artist-form-page">
    <section class="artist-form-card">
        <p class="section-label">Админ-панель</p>
        <h1>Редактировать исполнителя</h1>

        <?php if ($error): ?>
            <p class="error-message"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form artist-form">
            <label>
                Имя исполнителя
                <input
                    type="text"
                    name="name"
                    value="<?= htmlspecialchars($artist['name']); ?>"
                    required
                >
            </label>

            <label>
                Описание
                <textarea name="description"><?= htmlspecialchars($artist['description'] ?? ''); ?></textarea>
            </label>

            <?php if (!empty($artist['image_path'])): ?>
                <div>
                    <p>Текущее фото</p>
                    <img src="<?= htmlspecialchars($artist['image_path']); ?>" alt="Фото исполнителя" class="edit-preview">
                </div>
            <?php endif; ?>

            <label>
                Новая обложка / фото
                <input type="file" name="image" accept="image/*">
            </label>

            <button type="submit">Сохранить изменения</button>
        </form>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>