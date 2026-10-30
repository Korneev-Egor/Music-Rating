<?php

require_once 'config.php';
require_once 'includes/functions.php';

if (!isAdmin()) {
    header('Location: reviews.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reviewId = (int)($_POST['review_id'] ?? 0);

    if ($reviewId > 0) {

        $query = $pdo->prepare("
            DELETE FROM reviews
            WHERE id = ?
        ");

        $query->execute([$reviewId]);
    }
}

header('Location: reviews.php');
exit;