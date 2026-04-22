<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect(baseUrl() . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        setFlash('Please enter both email and password.', 'danger');
        redirect(baseUrl() . '/login.php');
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        session_regenerate_id(true);
        setFlash('Login successful. Welcome back!', 'success');
        if ($user['role'] === 'admin') {
            redirect(baseUrl() . '/admin/dashboard.php');
        } elseif ($user['role'] === 'hospital') {
            redirect(baseUrl() . '/hospital/dashboard.php');
        } else {
            redirect(baseUrl() . '/donor/dashboard.php');
        }
    } else {
        setFlash('Invalid email or password.', 'danger');
        redirect(baseUrl() . '/login.php');
    }
}

include 'includes/header.php';
?>

<div class="card" style="max-width: 480px; margin: 40px auto;">
    <h1 class="text-center">Login</h1>
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="you@example.com">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter password">
        </div>
        <button type="submit" class="btn" style="width:100%;">Login</button>
        <p class="text-center mt-2">Don't have an account? <a href="<?php echo baseUrl(); ?>/register.php">Register</a></p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
