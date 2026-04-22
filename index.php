<?php
require_once 'includes/functions.php';

// Stats
$donorCount = $pdo->query("SELECT COUNT(*) FROM donor_profiles")->fetchColumn();
$hospitalCount = $pdo->query("SELECT COUNT(*) FROM hospital_profiles")->fetchColumn();
$requestCount = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'open'")->fetchColumn();

// Recent urgent requests
$stmt = $pdo->query("
    SELECT br.*, hp.hospital_name 
    FROM blood_requests br
    JOIN hospital_profiles hp ON br.hospital_id = hp.user_id
    WHERE br.status = 'open'
    ORDER BY br.created_at DESC
    LIMIT 5
");
$recentRequests = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="hero">
    <h1>Community Blood Donor & Emergency Matching System</h1>
    <p>Connecting hospitals with voluntary blood donors to save lives in emergencies.</p>
    <div style="margin-top: 20px;">
        <a href="<?php echo baseUrl(); ?>/find_donors.php" class="btn">Find Donors</a>
        <a href="<?php echo baseUrl(); ?>/register.php" class="btn btn-secondary">Become a Donor</a>
    </div>
</section>

<section class="dashboard-grid">
    <div class="stat-card">
        <h3><?php echo (int)$donorCount; ?></h3>
        <p>Registered Donors</p>
    </div>
    <div class="stat-card">
        <h3><?php echo (int)$hospitalCount; ?></h3>
        <p>Partner Hospitals</p>
    </div>
    <div class="stat-card">
        <h3><?php echo (int)$requestCount; ?></h3>
        <p>Open Blood Requests</p>
    </div>
</section>

<section class="card">
    <div class="card-header">
        <h2>Recent Urgent Blood Requests</h2>
        <a href="<?php echo baseUrl(); ?>/find_donors.php" class="btn btn-small">View All</a>
    </div>
    <?php if (count($recentRequests) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Hospital</th>
                    <th>Blood Type</th>
                    <th>Units</th>
                    <th>Urgency</th>
                    <th>Required By</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentRequests as $req): ?>
                <tr>
                    <td><?php echo htmlspecialchars($req['hospital_name']); ?></td>
                    <td><strong><?php echo htmlspecialchars($req['patient_blood_type']); ?></strong></td>
                    <td><?php echo (int)$req['units_needed']; ?></td>
                    <td>
                        <span class="badge" style="
                            display:inline-block;padding:4px 8px;border-radius:4px;font-size:0.8rem;font-weight:600;
                            <?php echo $req['urgency'] === 'critical' ? 'background:#fee2e2;color:#991b1b;' : ($req['urgency'] === 'urgent' ? 'background:#fef3c7;color:#92400e;' : 'background:#dbeafe;color:#1e40af;'); ?>
                        ">
                            <?php echo ucfirst($req['urgency']); ?>
                        </span>
                    </td>
                    <td><?php echo $req['required_date'] ? htmlspecialchars($req['required_date']) : 'ASAP'; ?></td>
                    <td><?php echo htmlspecialchars($req['city'] . ', ' . $req['state']); ?></td>
                    <td><a href="<?php echo baseUrl(); ?>/view_request.php?id=<?php echo (int)$req['id']; ?>" class="btn btn-small">View Matches</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No open blood requests at the moment.</p>
    <?php endif; ?>
</section>

<section class="card">
    <h2>How It Works</h2>
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-top: 10px;">
        <div>
            <h3>1. Register</h3>
            <p>Sign up as a donor or hospital. Build your profile with blood type and location details.</p>
        </div>
        <div>
            <h3>2. Request</h3>
            <p>Hospitals submit urgent blood requests with patient blood type and location.</p>
        </div>
        <div>
            <h3>3. Match</h3>
            <p>The system instantly finds compatible donors nearby and displays their contact details.</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
