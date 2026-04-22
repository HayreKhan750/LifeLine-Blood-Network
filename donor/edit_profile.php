<?php
require_once '../includes/functions.php';
requireDonor();

$userId = $_SESSION['user_id'];
$profile = getDonorProfile($pdo, $userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $stmt = $pdo->prepare("
        UPDATE donor_profiles SET
            full_name = ?,
            phone = ?,
            blood_type = ?,
            address = ?,
            city = ?,
            state = ?,
            country = ?,
            date_of_birth = ?,
            gender = ?,
            last_donation_date = ?,
            is_available = ?
        WHERE user_id = ?
    ");
    $stmt->execute([
        trim($_POST['full_name'] ?? ''),
        trim($_POST['phone'] ?? ''),
        $_POST['blood_type'] ?? '',
        trim($_POST['address'] ?? ''),
        trim($_POST['city'] ?? ''),
        trim($_POST['state'] ?? ''),
        trim($_POST['country'] ?? 'India'),
        $_POST['date_of_birth'] ?: null,
        $_POST['gender'] ?? null,
        $_POST['last_donation_date'] ?: null,
        isset($_POST['is_available']) ? 1 : 0,
        $userId
    ]);

    setFlash('Profile updated successfully.', 'success');
    redirect(baseUrl() . '/donor/edit_profile.php');
}

include '../includes/header.php';
?>

<div class="card" style="max-width: 650px; margin: 30px auto;">
    <h1>Edit Profile</h1>
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">

        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($profile['full_name']); ?>">
        </div>
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" required value="<?php echo htmlspecialchars($profile['phone']); ?>">
        </div>
        <div class="form-group">
            <label for="blood_type">Blood Type</label>
            <select id="blood_type" name="blood_type" required>
                <?php $types = ['A+','A-','B+','B-','AB+','AB-','O+','O-']; ?>
                <?php foreach ($types as $t): ?>
                    <option value="<?php echo $t; ?>" <?php echo ($profile['blood_type'] === $t) ? 'selected' : ''; ?>><?php echo $t; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="date_of_birth">Date of Birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
                <option value="">-- Select --</option>
                <option value="male" <?php echo ($profile['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo ($profile['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                <option value="other" <?php echo ($profile['gender'] === 'other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="city">City</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($profile['city'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="state">State / Province</label>
            <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($profile['state'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="country">Country</label>
            <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($profile['country'] ?? 'India'); ?>">
        </div>
        <div class="form-group">
            <label for="last_donation_date">Last Donation Date</label>
            <input type="date" id="last_donation_date" name="last_donation_date" value="<?php echo htmlspecialchars($profile['last_donation_date'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_available" value="1" <?php echo $profile['is_available'] ? 'checked' : ''; ?>> I am currently available to donate
            </label>
        </div>
        <button type="submit" class="btn" style="width:100%;">Save Changes</button>
        <p class="text-center mt-2"><a href="<?php echo baseUrl(); ?>/donor/dashboard.php">&larr; Back to Dashboard</a></p>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
