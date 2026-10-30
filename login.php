<?php

require_once 'config.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $query = $pdo->prepare("
            SELECT *
            FROM users
            WHERE username = ?
        ");

        $query->execute([$username]);
        $user = $query->fetch();

      if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

            redirect('index.php');
        } else {
            $error = 'Неверный логин или пароль';
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

?>

<main class="content">
    <h1>Вход</h1>

    <?php if ($error): ?>
        <p class="error-message">
            <?= $error; ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="form">
        <input type="text" name="username" placeholder="Имя пользователя">
        <input type="password" name="password" placeholder="Пароль">
        <button type="submit">Войти</button>
    </form>
</main>

<?php

require_once 'includes/footer.php';

?>