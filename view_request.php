<?php
require_once 'includes/functions.php';

$requestId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$requestId) {
    setFlash('Invalid request.', 'danger');
    redirect(baseUrl() . '/index.php');
}

$stmt = $pdo->prepare("
    SELECT br.*, hp.hospital_name, hp.phone as hospital_phone, hp.city as h_city, hp.state as h_state
    FROM blood_requests br
    JOIN hospital_profiles hp ON br.hospital_id = hp.user_id
    WHERE br.id = ?
");
$stmt->execute([$requestId]);
$request = $stmt->fetch();

if (!$request) {
    setFlash('Blood request not found.', 'danger');
    redirect(baseUrl() . '/index.php');
}

// Find compatible donors
$compatTypes = getCompatibleDonorBloodTypes($request['patient_blood_type']);
$matches = [];
if (!empty($compatTypes)) {
    $in = implode(',', array_fill(0, count($compatTypes), '?'));
    $sql = "SELECT dp.*, u.email FROM donor_profiles dp JOIN users u ON dp.user_id = u.id WHERE u.is_active = 1 AND dp.is_available = 1 AND dp.blood_type IN ($in)";
    $params = $compatTypes;
    // Optional location filter if request city/state provided
    if (!empty($request['city'])) {
        $sql .= " AND dp.city LIKE ?";
        $params[] = '%' . $request['city'] . '%';
    }
    $sql .= " ORDER BY dp.city, dp.full_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $matches = $stmt->fetchAll();
}

// Record match rows for tracking if hospital views (optional)
if (isHospital() || isAdmin()) {
    foreach ($matches as $m) {
        $check = $pdo->prepare("SELECT id FROM donor_matches WHERE request_id = ? AND donor_id = ?");
        $check->execute([$requestId, $m['user_id']]);
        if (!$check->fetch()) {
            $ins = $pdo->prepare("INSERT IGNORE INTO donor_matches (request_id, donor_id) VALUES (?, ?)");
            $ins->execute([$requestId, $m['user_id']]);
        }
    }
}

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h1>Blood Request #<?php echo $requestId; ?></h1>
            <p style="margin:0;color:#6b7280;">Posted by <?php echo htmlspecialchars($request['hospital_name']); ?> &middot; <?php echo date('M d, Y', strtotime($request['created_at'])); ?></p>
        </div>
        <span class="badge" style="
            display:inline-block;padding:6px 12px;border-radius:5px;font-size:0.85rem;font-weight:700;
            <?php echo $request['urgency'] === 'critical' ? 'background:#fee2e2;color:#991b1b;' : ($request['urgency'] === 'urgent' ? 'background:#fef3c7;color:#92400e;' : 'background:#dbeafe;color:#1e40af;'); ?>
        ">
            <?php echo ucfirst($request['urgency']); ?>
        </span>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <div>
            <strong>Patient Blood Type</strong>
            <p style="font-size:1.3rem; color:#b91c1c; margin:4px 0;"><?php echo htmlspecialchars($request['patient_blood_type']); ?></p>
        </div>
        <div>
            <strong>Units Needed</strong>
            <p style="margin:4px 0;"><?php echo (int)$request['units_needed']; ?></p>
        </div>
        <div>
            <strong>Required By</strong>
            <p style="margin:4px 0;"><?php echo $request['required_date'] ? htmlspecialchars($request['required_date']) : 'ASAP'; ?></p>
        </div>
        <div>
            <strong>Location</strong>
            <p style="margin:4px 0;"><?php echo htmlspecialchars(($request['city'] ? $request['city'] . ', ' : '') . $request['state']); ?></p>
        </div>
    </div>
    <?php if ($request['notes']): ?>
        <div style="margin-top: 12px;">
            <strong>Additional Notes</strong>
            <p style="margin:4px 0;"><?php echo nl2br(htmlspecialchars($request['notes'])); ?></p>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Compatible Donors (<?php echo count($matches); ?>)</h2>
    <p style="margin-top:4px; color:#6b7280; font-size:0.9rem;">
        Donors with blood types compatible for a <strong><?php echo htmlspecialchars($request['patient_blood_type']); ?></strong> patient.
    </p>

    <?php if (count($matches) > 0): ?>
        <div style="overflow-x:auto; margin-top: 16px;">
        <table>
            <thead>
                <tr>
                    <th>Donor Name</th>
                    <th>Blood Type</th>
                    <th>Location</th>
                    <th>Last Donation</th>
                    <?php if (isLoggedIn()): ?>
                        <th>Phone</th>
                        <th>Email</th>
                    <?php else: ?>
                        <th>Contact</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matches as $m): ?>
                <tr>
                    <td><?php echo htmlspecialchars($m['full_name']); ?></td>
                    <td><strong><?php echo htmlspecialchars($m['blood_type']); ?></strong></td>
                    <td><?php echo htmlspecialchars(($m['city'] ? $m['city'] . ', ' : '') . $m['state']); ?></td>
                    <td><?php echo $m['last_donation_date'] ? htmlspecialchars($m['last_donation_date']) : 'N/A'; ?></td>
                    <?php if (isLoggedIn()): ?>
                        <td><?php echo htmlspecialchars($m['phone']); ?></td>
                        <td><?php echo htmlspecialchars($m['email']); ?></td>
                    <?php else: ?>
                        <td><em>Login to view</em></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if (!isLoggedIn()): ?>
            <p class="mt-2" style="font-size:0.9rem;color:#6b7280;"><em>Hospitals and registered users can view full donor contact information after logging in.</em></p>
        <?php endif; ?>
    <?php else: ?>
        <p style="margin-top: 12px;">No compatible donors found for this request at the moment.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
