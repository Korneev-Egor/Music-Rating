<?php

function isUserLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

function cleanInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function requireAuth()
{
    if (!isUserLoggedIn()) {
        redirect('login.php');
    }
}

function getUserName()
{
    return $_SESSION['username'] ?? 'Гость';
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAdmin()
{
    if (!isAdmin()) {
        die('Доступ запрещен');
    }
}