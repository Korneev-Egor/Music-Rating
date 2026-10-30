<?php

require_once 'config.php';
require_once 'includes/functions.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

$error = '';
$name = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imagePath = null;

    if ($name === '') {
        $error = 'Введите имя исполнителя.';
    } else {
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/uploads/covers/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extension, $allowed, true)) {
                $error = 'Можно загрузить только JPG, PNG или WEBP.';
            } else {
                $fileName = 'artist_' . uniqid('', true) . '.' . $extension;
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imagePath = 'uploads/covers/' . $fileName;
                } else {
                    $error = 'Не удалось загрузить изображение.';
                }
            }
        }

        if ($error === '') {
            $query = $pdo->prepare("INSERT INTO artists (name, image_path, description) VALUES (?, ?, ?)");
            $query->execute([$name, $imagePath, $description !== '' ? $description : null]);

            header('Location: artists.php');
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
        <h1>Добавить исполнителя</h1>

        <?php if ($error): ?>
            <p class="error-message"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form artist-form">
            <label>
                Имя исполнителя
                <input type="text" name="name" value="<?= htmlspecialchars($name); ?>" required>
            </label>

            <label>
                Описание
                <textarea name="description" placeholder="Кратко об исполнителе"><?= htmlspecialchars($description); ?></textarea>
            </label>

            <label>
                Обложка / фото исполнителя
                <input type="file" name="image" accept="image/*">
            </label>

            <button type="submit">Сохранить исполнителя</button>
        </form>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>
