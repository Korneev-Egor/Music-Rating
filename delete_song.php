<?php

require_once 'config.php';
require_once 'includes/functions.php';

requireAuth();

if (!isAdmin()) {
    die('Доступ запрещен');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$songId = isset($_POST['song_id']) ? (int)$_POST['song_id'] : 0;

$query = $pdo->prepare("
    SELECT image_path, song_path, artist_id
    FROM songs
    WHERE id = ?
");
$query->execute([$songId]);
$song = $query->fetch();

if (!$song) {
    redirect('index.php');
}

$pdo->prepare("DELETE FROM ratings WHERE song_id = ?")->execute([$songId]);
$pdo->prepare("DELETE FROM reviews WHERE song_id = ?")->execute([$songId]);
$pdo->prepare("DELETE FROM songs WHERE id = ?")->execute([$songId]);

if (!empty($song['image_path']) && file_exists($song['image_path'])) {
    unlink($song['image_path']);
}

if (!empty($song['song_path']) && file_exists($song['song_path'])) {
    unlink($song['song_path']);
}

$checkArtist = $pdo->prepare("
    SELECT COUNT(*) 
    FROM songs 
    WHERE artist_id = ?
");
$checkArtist->execute([$song['artist_id']]);

if ($checkArtist->fetchColumn() == 0) {
    $pdo->prepare("DELETE FROM artists WHERE id = ?")->execute([$song['artist_id']]);
}

redirect('index.php');