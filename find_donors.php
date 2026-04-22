<?php
require_once 'includes/functions.php';

$bloodType = $_GET['blood_type'] ?? '';
$city = trim($_GET['city'] ?? '');
$state = trim($_GET['state'] ?? '');

$results = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($bloodType || $city || $state)) {
    $sql = "SELECT dp.*, u.email FROM donor_profiles dp JOIN users u ON dp.user_id = u.id WHERE u.is_active = 1 AND dp.is_available = 1";
    $params = [];
    if ($bloodType) {
        $sql .= " AND dp.blood_type = ?";
        $params[] = $bloodType;
    }
    if ($city) {
        $sql .= " AND dp.city LIKE ?";
        $params[] = "%$city%";
    }
    if ($state) {
        $sql .= " AND dp.state LIKE ?";
        $params[] = "%$state%";
    }
    $sql .= " ORDER BY dp.city, dp.full_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="card">
    <h1>Find Blood Donors</h1>
    <form method="GET" action="" class="flex flex-wrap gap-2 items-center" style="margin-top: 10px;">
        <div class="form-group" style="flex:1; min-width:180px; margin-bottom:0;">
            <label for="blood_type">Blood Type</label>
            <select id="blood_type" name="blood_type">
                <option value="">Any</option>
                <option value="A+" <?php echo $bloodType === 'A+' ? 'selected' : ''; ?>>A+</option>
                <option value="A-" <?php echo $bloodType === 'A-' ? 'selected' : ''; ?>>A-</option>
                <option value="B+" <?php echo $bloodType === 'B+' ? 'selected' : ''; ?>>B+</option>
                <option value="B-" <?php echo $bloodType === 'B-' ? 'selected' : ''; ?>>B-</option>
                <option value="AB+" <?php echo $bloodType === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                <option value="AB-" <?php echo $bloodType === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                <option value="O+" <?php echo $bloodType === 'O+' ? 'selected' : ''; ?>>O+</option>
                <option value="O-" <?php echo $bloodType === 'O-' ? 'selected' : ''; ?>>O-</option>
            </select>
        </div>
        <div class="form-group" style="flex:1; min-width:180px; margin-bottom:0;">
            <label for="city">City</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>" placeholder="e.g. Mumbai">
        </div>
        <div class="form-group" style="flex:1; min-width:180px; margin-bottom:0;">
            <label for="state">State</label>
            <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($state); ?>" placeholder="e.g. Maharashtra">
        </div>
        <div style="margin-top: 22px;">
            <button type="submit" class="btn">Search</button>
        </div>
    </form>
</div>

<?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($bloodType || $city || $state)): ?>
<div class="card">
    <h2>Search Results (<?php echo count($results); ?> found)</h2>
    <?php if (count($results) > 0): ?>
        <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Blood Type</th>
                    <th>Location</th>
                    <th>Availability</th>
                    <?php if (isLoggedIn()): ?>
                        <th>Phone</th>
                        <th>Email</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $d): ?>
                <tr>
                    <td><?php echo htmlspecialchars($d['full_name']); ?></td>
                    <td><strong><?php echo htmlspecialchars($d['blood_type']); ?></strong></td>
                    <td><?php echo htmlspecialchars(($d['city'] ? $d['city'] . ', ' : '') . $d['state']); ?></td>
                    <td><?php echo $d['is_available'] ? '<span style="color:#15803d;font-weight:600;">Available</span>' : '<span style="color:#6b7280;">Unavailable</span>'; ?></td>
                    <?php if (isLoggedIn()): ?>
                        <td><?php echo htmlspecialchars($d['phone']); ?></td>
                        <td><?php echo htmlspecialchars($d['email']); ?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if (!isLoggedIn()): ?>
            <p class="mt-2" style="font-size:0.9rem;color:#6b7280;"><em>Login to view donor contact details.</em></p>
        <?php endif; ?>
    <?php else: ?>
        <p>No donors found matching your criteria.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
