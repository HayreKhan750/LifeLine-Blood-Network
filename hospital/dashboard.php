<?php
require_once '../includes/functions.php';
requireHospital();

$userId = $_SESSION['user_id'];
$profile = getHospitalProfile($pdo, $userId);

$stmt = $pdo->prepare("
    SELECT * FROM blood_requests
    WHERE hospital_id = ?
    ORDER BY status = 'open' DESC, urgency = 'critical' DESC, created_at DESC
");
$stmt->execute([$userId]);
$requests = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h1><?php echo htmlspecialchars($profile['hospital_name']); ?></h1>
            <p style="margin:0;color:#6b7280;">Hospital Dashboard</p>
        </div>
        <a href="<?php echo baseUrl(); ?>/hospital/create_request.php" class="btn">+ New Blood Request</a>
    </div>
    <div style="margin-top: 10px;">
        <p><strong>License:</strong> <?php echo htmlspecialchars($profile['license_number']); ?> &middot; <strong>Phone:</strong> <?php echo htmlspecialchars($profile['phone']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars(($profile['address'] ? $profile['address'] . ', ' : '') . $profile['city'] . ', ' . $profile['state']); ?></p>
    </div>
</div>

<div class="card">
    <h2>Your Blood Requests</h2>
    <?php if (count($requests) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient Type</th>
                    <th>Units</th>
                    <th>Urgency</th>
                    <th>Status</th>
                    <th>Required By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                <tr>
                    <td>#<?php echo (int)$req['id']; ?></td>
                    <td><?php echo htmlspecialchars($req['patient_blood_type']); ?></td>
                    <td><?php echo (int)$req['units_needed']; ?></td>
                    <td>
                        <span style="
                            display:inline-block;padding:4px 8px;border-radius:4px;font-size:0.8rem;font-weight:600;
                            <?php echo $req['urgency'] === 'critical' ? 'background:#fee2e2;color:#991b1b;' : ($req['urgency'] === 'urgent' ? 'background:#fef3c7;color:#92400e;' : 'background:#dbeafe;color:#1e40af;'); ?>
                        ">
                            <?php echo ucfirst($req['urgency']); ?>
                        </span>
                    </td>
                    <td>
                        <span style="font-weight:600; <?php echo $req['status']==='open'?'color:#b91c1c;':($req['status']==='fulfilled'?'color:#15803d;':'color:#6b7280;'); ?>">
                            <?php echo ucfirst($req['status']); ?>
                        </span>
                    </td>
                    <td><?php echo $req['required_date'] ? htmlspecialchars($req['required_date']) : 'ASAP'; ?></td>
                    <td>
                        <a href="<?php echo baseUrl(); ?>/view_request.php?id=<?php echo (int)$req['id']; ?>" class="btn btn-small">View</a>
                        <a href="<?php echo baseUrl(); ?>/hospital/request_matches.php?request_id=<?php echo (int)$req['id']; ?>" class="btn btn-small btn-secondary">Matches</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="margin-top: 10px;">You have not created any blood requests yet. <a href="<?php echo baseUrl(); ?>/hospital/create_request.php">Create one now</a>.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
