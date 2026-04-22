<?php
require_once '../includes/functions.php';
requireDonor();

$userId = $_SESSION['user_id'];
$profile = getDonorProfile($pdo, $userId);
if (!$profile) {
    setFlash('Profile not found.', 'danger');
    redirect(baseUrl() . '/index.php');
}

// Open requests this donor can help with
$patientTypes = getPatientBloodTypesForDonor($profile['blood_type']);
$requests = [];
if (!empty($patientTypes)) {
    $in = implode(',', array_fill(0, count($patientTypes), '?'));
    $sql = "
        SELECT br.*, hp.hospital_name
        FROM blood_requests br
        JOIN hospital_profiles hp ON br.hospital_id = hp.user_id
        WHERE br.status = 'open' AND br.patient_blood_type IN ($in)
        ORDER BY br.urgency = 'critical' DESC, br.urgency = 'urgent' DESC, br.created_at DESC
        LIMIT 10
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($patientTypes);
    $requests = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h1>Welcome, <?php echo htmlspecialchars($profile['full_name']); ?></h1>
            <p style="margin:0;color:#6b7280;">Donor Dashboard</p>
        </div>
        <a href="<?php echo baseUrl(); ?>/donor/edit_profile.php" class="btn btn-secondary">Edit Profile</a>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-top: 10px;">
        <div>
            <strong>Blood Type</strong>
            <p style="font-size:1.2rem; color:#b91c1c; margin:4px 0;"><strong><?php echo htmlspecialchars($profile['blood_type']); ?></strong></p>
        </div>
        <div>
            <strong>Location</strong>
            <p style="margin:4px 0;"><?php echo htmlspecialchars(($profile['city'] ? $profile['city'] . ', ' : '') . $profile['state']); ?></p>
        </div>
        <div>
            <strong>Availability</strong>
            <p style="margin:4px 0;"><?php echo $profile['is_available'] ? '<span style="color:#15803d;font-weight:600;">Available</span>' : '<span style="color:#6b7280;">Unavailable</span>'; ?></p>
        </div>
        <div>
            <strong>Last Donation</strong>
            <p style="margin:4px 0;"><?php echo $profile['last_donation_date'] ? htmlspecialchars($profile['last_donation_date']) : 'N/A'; ?></p>
        </div>
    </div>
</div>

<div class="card">
    <h2>Open Requests You Can Help With</h2>
    <?php if (count($requests) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Hospital</th>
                    <th>Patient Type</th>
                    <th>Urgency</th>
                    <th>Required By</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                <tr>
                    <td><?php echo htmlspecialchars($req['hospital_name']); ?></td>
                    <td><?php echo htmlspecialchars($req['patient_blood_type']); ?></td>
                    <td>
                        <span style="
                            display:inline-block;padding:4px 8px;border-radius:4px;font-size:0.8rem;font-weight:600;
                            <?php echo $req['urgency'] === 'critical' ? 'background:#fee2e2;color:#991b1b;' : ($req['urgency'] === 'urgent' ? 'background:#fef3c7;color:#92400e;' : 'background:#dbeafe;color:#1e40af;'); ?>
                        ">
                            <?php echo ucfirst($req['urgency']); ?>
                        </span>
                    </td>
                    <td><?php echo $req['required_date'] ? htmlspecialchars($req['required_date']) : 'ASAP'; ?></td>
                    <td><?php echo htmlspecialchars($req['city'] . ', ' . $req['state']); ?></td>
                    <td><a href="<?php echo baseUrl(); ?>/view_request.php?id=<?php echo (int)$req['id']; ?>" class="btn btn-small">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="margin-top: 10px;">There are currently no open requests matching your blood type.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
