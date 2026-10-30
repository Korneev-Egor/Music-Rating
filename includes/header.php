<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

$headerUserName = '';

if (isUserLoggedIn()) {
    $headerUserName = getUserName();

    try {
        $columnCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'display_name'");

        if ($columnCheck->fetch()) {
            $userQuery = $pdo->prepare("SELECT username, display_name FROM users WHERE id = ?");
            $userQuery->execute([getCurrentUserId()]);
            $headerUser = $userQuery->fetch();

            if ($headerUser) {
                $headerUserName = !empty($headerUser['display_name'])
                    ? $headerUser['display_name']
                    : $headerUser['username'];
            }
        }
    } catch (PDOException $e) {
        $headerUserName = getUserName();
    }
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Music Rating</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >
</head>

<body>

<header class="header">

    <div class="logo">
        <a href="index.php">
            Music Rating
        </a>
    </div>

    <nav class="nav">

        <a href="index.php">
            Главная
        </a>

        <?php if (isUserLoggedIn()): ?>

            <a href="profile.php" class="nav-user-link">
                <?= htmlspecialchars($headerUserName); ?>
            </a>

            <?php if (isAdmin()): ?>
                <a href="add_song.php" class="add-song-link">
                    Добавить композицию
                </a>
            <?php endif; ?>

            <a href="logout.php">
                Выйти
            </a>

        <?php else: ?>

            <a href="login.php">
                Вход
            </a>

            <a href="register.php">
                Регистрация
            </a>

        <?php endif; ?>

    </nav>

</header>

<div class="container">
