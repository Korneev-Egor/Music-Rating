<?php

require_once 'config.php';

$songId = isset($_POST['song_id']) ? (int) $_POST['song_id'] : 0;

if ($songId > 0) {
    $query = $pdo->prepare("
        UPDATE songs
        SET plays = plays + 1
        WHERE id = ?
    ");

    $query->execute([$songId]);

    echo 'ok';
}