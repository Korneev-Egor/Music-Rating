<?php

require_once 'config.php';
require_once 'includes/functions.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: artists.php');
    exit;
}

$artistId = isset($_POST['artist_id']) ? (int)$_POST['artist_id'] : 0;

$countQuery = $pdo->prepare("SELECT COUNT(*) FROM songs WHERE artist_id = ?");
$countQuery->execute([$artistId]);
$songsCount = (int)$countQuery->fetchColumn();

if ($songsCount > 0) {
    header('Location: artists.php?error=artist_has_songs');
    exit;
}

$delete = $pdo->prepare("DELETE FROM artists WHERE id = ?");
$delete->execute([$artistId]);

header('Location: artists.php');
exit;
