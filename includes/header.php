<?php
require_once __DIR__ . '/functions.php';
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LifeLine Blood Network - Connect blood donors with hospitals in emergencies. Find compatible donors, create urgent requests, save lives.">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>LifeLine Blood Network</title>
    <link rel="stylesheet" href="<?php echo baseUrl(); ?>/assets/css/style.css">
</head>
<body>
<header>
    <div class="container nav-container">
        <a href="<?php echo baseUrl(); ?>/index.php" class="logo">
            <span class="logo-icon">&#9764;</span>
            LifeLine
        </a>
        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <nav class="nav-links">
            <a href="<?php echo baseUrl(); ?>/index.php">Home</a>
            <a href="<?php echo baseUrl(); ?>/find_donors.php">Find Donors</a>
            <a href="<?php echo baseUrl(); ?>/blood_banks.php">Blood Banks</a>
            <a href="<?php echo baseUrl(); ?>/eligibility.php">Eligibility</a>
            <a href="<?php echo baseUrl(); ?>/emergency.php">Emergency SOS</a>
            <a href="<?php echo baseUrl(); ?>/leaderboard.php">Leaderboard</a>
            <a href="<?php echo baseUrl(); ?>/testimonials.php">Stories</a>
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo baseUrl(); ?>/messages.php">Messages</a>
                <?php if (isAdmin()): ?>
                    <a href="<?php echo baseUrl(); ?>/admin/dashboard.php">Admin</a>
                <?php elseif (isDonor()): ?>
                    <a href="<?php echo baseUrl(); ?>/donor/dashboard.php">Dashboard</a>
                <?php elseif (isHospital()): ?>
                    <a href="<?php echo baseUrl(); ?>/hospital/dashboard.php">Dashboard</a>
                <?php endif; ?>
                <a href="<?php echo baseUrl(); ?>/logout.php">Logout</a>
            <?php else: ?>
                <a href="<?php echo baseUrl(); ?>/login.php">Login</a>
                <a href="<?php echo baseUrl(); ?>/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>
