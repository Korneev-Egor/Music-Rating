<?php

require_once 'config.php';
require_once 'includes/functions.php';


$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];

 if (empty($username) || empty($email) || empty($password)) {
    $error = 'Заполните все поля';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Введите корректный email';
} elseif (strlen($password) < 8) {
    $error = 'Пароль должен содержать минимум 8 символов';
} else {

        $checkQuery = $pdo->prepare("
            SELECT id 
            FROM users 
            WHERE email = ? OR username = ?
        ");

        $checkQuery->execute([$email, $username]);

        if ($checkQuery->fetch()) {

            $error = 'Пользователь уже существует';

        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            try {
                $query = $pdo->prepare("
                    INSERT INTO users 
                    (username, display_name, email, password)
                    VALUES (?, ?, ?, ?)
                ");

                $query->execute([
                    $username,
                    $username,
                    $email,
                    $hashedPassword
                ]);
            } catch (PDOException $e) {
                $query = $pdo->prepare("
                    INSERT INTO users 
                    (username, email, password)
                    VALUES (?, ?, ?)
                ");

                $query->execute([
                    $username,
                    $email,
                    $hashedPassword
                ]);
            }

            $success = 'Регистрация успешно завершена';
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<main class="content">

    <h1>Регистрация</h1>

    <?php if ($error): ?>

        <p class="error-message">
            <?= htmlspecialchars($error); ?>
        </p>

    <?php endif; ?>

    <?php if ($success): ?>

        <p class="success-message">
            <?= htmlspecialchars($success); ?>
        </p>

    <?php endif; ?>

    <form method="POST" class="form">

        <input type="text" name="username" placeholder="Логин">

        <input 
            type="email" 
            name="email" 
            placeholder="Email"
        >

        <input 
            type="password" 
            name="password" 
            placeholder="Пароль"
        >

        <button type="submit">
            Зарегистрироваться
        </button>

    </form>

</main>

<?php require_once 'includes/footer.php'; ?>
