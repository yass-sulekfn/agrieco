<?php
$pageTitle = $pageTitle ?? 'AgriEco';
$metaDescription = $metaDescription ?? 'AgriEco - Du champ au client, sans intermédiaire.';
$bodyClass = $bodyClass ?? 'front-office';
$stylesheetPath = dirname(__DIR__, 2) . '/assets/css/style.css';
$stylesheetVersion = is_file($stylesheetPath) ? (string) filemtime($stylesheetPath) : '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="<?= htmlspecialchars((string) $metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="theme-color" content="#1F3A2B">
    <title><?= htmlspecialchars((string) $pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%233E8E5A' d='M20 4C10 4 4 9 4 15c0 2 1 4 3 5 0-5 3-9 9-11-4 3-6 6-6.500 11C15 20 20 15 20 4z'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Domine:wght@400;500;600;700&amp;family=Nunito+Sans:wght@300;400;600;700;800&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL . '/assets/css/style.css?v=' . $stylesheetVersion, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body id="top" class="<?= htmlspecialchars((string) $bodyClass, ENT_QUOTES, 'UTF-8') ?>">