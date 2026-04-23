<?php
require_once 'includes/functions.php';
require_once 'includes/email_service.php';

if (isLoggedIn()) {
    redirect(baseUrl() . '/index.php');
}

$role = $_GET['role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    $role = $_POST['role'] ?? '';
    $email = sanitizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('Please provide a valid email address.', 'danger');
        redirect(baseUrl() . '/register.php');
    }
    
    // Validate password strength
    $passwordErrors = validatePassword($password);
    if (!empty($passwordErrors)) {
        setFlash(implode('<br>', $passwordErrors), 'danger');
        redirect(baseUrl() . '/register.php?role=' . urlencode($role));
    }
    
    if ($password !== $confirm) {
        setFlash('Passwords do not match.', 'danger');
        redirect(baseUrl() . '/register.php?role=' . urlencode($role));
    }

    // Check email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        setFlash('An account with this email already exists.', 'danger');
        redirect(baseUrl() . '/register.php?role=' . urlencode($role));
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$email, $hash, $role]);
    $userId = $pdo->lastInsertId();

    if ($role === 'donor') {
        $stmt = $pdo->prepare("
            INSERT INTO donor_profiles
            (user_id, full_name, phone, blood_type, address, city, state, country, date_of_birth, gender)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            trim($_POST['full_name'] ?? ''),
            trim($_POST['phone'] ?? ''),
            $_POST['blood_type'] ?? '',
            trim($_POST['address'] ?? ''),
            trim($_POST['city'] ?? ''),
            trim($_POST['state'] ?? ''),
            trim($_POST['country'] ?? 'India'),
            $_POST['date_of_birth'] ?: null,
            $_POST['gender'] ?? null
        ]);
    } elseif ($role === 'hospital') {
        $stmt = $pdo->prepare("
            INSERT INTO hospital_profiles
            (user_id, hospital_name, phone, address, city, state, country, license_number)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            trim($_POST['hospital_name'] ?? ''),
            trim($_POST['phone'] ?? ''),
            trim($_POST['address'] ?? ''),
            trim($_POST['city'] ?? ''),
            trim($_POST['state'] ?? ''),
            trim($_POST['country'] ?? 'India'),
            trim($_POST['license_number'] ?? '')
        ]);
    }

    // Auto-login
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $role;
    $_SESSION['email'] = $email;
    session_regenerate_id(true);
    
    // Send welcome email
    if ($role === 'donor') {
        $donorName = trim($_POST['full_name'] ?? '');
        EmailService::sendDonorWelcome($email, $donorName);
    } else {
        $hospitalName = trim($_POST['hospital_name'] ?? '');
        EmailService::sendHospitalWelcome($email, $hospitalName);
    }
    
    setFlash('Registration successful! Welcome.', 'success');
    if ($role === 'donor') {
        redirect(baseUrl() . '/donor/dashboard.php');
    } else {
        redirect(baseUrl() . '/hospital/dashboard.php');
    }
}

include 'includes/header.php';
?>

<?php if (!in_array($role, ['donor', 'hospital'])): ?>
<div class="card" style="max-width: 600px; margin: 40px auto;">
    <h1 class="text-center">Register</h1>
    <p class="text-center">Choose your account type to get started.</p>
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
        <a href="<?php echo baseUrl(); ?>/register.php?role=donor" class="card text-center" style="text-decoration:none; color:inherit;">
            <h2>Donor</h2>
            <p>Register as a voluntary blood donor and help save lives in your community.</p>
            <span class="btn">Register as Donor</span>
        </a>
        <a href="<?php echo baseUrl(); ?>/register.php?role=hospital" class="card text-center" style="text-decoration:none; color:inherit;">
            <h2>Hospital</h2>
            <p>Register your hospital to create emergency blood requests and find donors.</p>
            <span class="btn">Register as Hospital</span>
        </a>
    </div>
</div>
<?php else: ?>
<div class="card" style="max-width: 600px; margin: 40px auto;">
    <h1 class="text-center">Register as <?php echo ucfirst($role); ?></h1>
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
        <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="8" placeholder="Min 8 characters">
            <small style="color: #6b7280; display: block; margin-top: 5px;">
                Must contain: 8+ chars, uppercase, lowercase, number, special character
            </small>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <?php if ($role === 'donor'): ?>
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" required placeholder="e.g. +91 98765 43210">
        </div>
        <div class="form-group">
            <label for="blood_type">Blood Type</label>
            <select id="blood_type" name="blood_type" required>
                <option value="">-- Select --</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>
        </div>
        <div class="form-group">
            <label for="date_of_birth">Date of Birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth">
        </div>
        <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
                <option value="">-- Select --</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" placeholder="Street address"></textarea>
        </div>
        <div class="form-group">
            <label for="city">City</label>
            <input type="text" id="city" name="city">
        </div>
        <div class="form-group">
            <label for="state">State / Province</label>
            <input type="text" id="state" name="state">
        </div>
        <div class="form-group">
            <label for="country">Country</label>
            <input type="text" id="country" name="country" value="India">
        </div>
        <?php else: ?>
        <div class="form-group">
            <label for="hospital_name">Hospital Name</label>
            <input type="text" id="hospital_name" name="hospital_name" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" required>
        </div>
        <div class="form-group">
            <label for="license_number">License Number</label>
            <input type="text" id="license_number" name="license_number" required>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address"></textarea>
        </div>
        <div class="form-group">
            <label for="city">City</label>
            <input type="text" id="city" name="city">
        </div>
        <div class="form-group">
            <label for="state">State / Province</label>
            <input type="text" id="state" name="state">
        </div>
        <div class="form-group">
            <label for="country">Country</label>
            <input type="text" id="country" name="country" value="India">
        </div>
        <?php endif; ?>

        <button type="submit" class="btn" style="width:100%;">Create Account</button>
        <p class="text-center mt-2"><a href="<?php echo baseUrl(); ?>/register.php">&larr; Back to selection</a></p>
    </form>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
