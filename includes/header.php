<?php
require_once __DIR__ . '/functions.php';
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Blood Donor & Emergency Matching System</title>
    <link rel="stylesheet" href="<?php echo baseUrl(); ?>/assets/css/style.css">
</head>
<body>
<header>
    <div class="container nav-container">
        <a href="<?php echo baseUrl(); ?>/index.php" class="logo">LifeLine Blood Network</a>
        <nav>
            <a href="<?php echo baseUrl(); ?>/index.php">Home</a>
            <a href="<?php echo baseUrl(); ?>/find_donors.php">Find Donors</a>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="<?php echo baseUrl(); ?>/admin/dashboard.php">Admin Dashboard</a>
                <?php elseif (isDonor()): ?>
                    <a href="<?php echo baseUrl(); ?>/donor/dashboard.php">My Dashboard</a>
                <?php elseif (isHospital()): ?>
                    <a href="<?php echo baseUrl(); ?>/hospital/dashboard.php">Hospital Dashboard</a>
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
