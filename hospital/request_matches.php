<?php
require_once '../includes/functions.php';
requireHospital();

$userId = $_SESSION['user_id'];
$requestId = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;

$stmt = $pdo->prepare("
    SELECT * FROM blood_requests
    WHERE id = ? AND hospital_id = ?
");
$stmt->execute([$requestId, $userId]);
$request = $stmt->fetch();

if (!$request) {
    setFlash('Request not found or access denied.', 'danger');
    redirect(baseUrl() . '/hospital/dashboard.php');
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    // Update match status
    if (isset($_POST['match_id']) && isset($_POST['match_status'])) {
        $stmt = $pdo->prepare("UPDATE donor_matches SET status = ? WHERE id = ? AND request_id = ?");
        $stmt->execute([$_POST['match_status'], (int)$_POST['match_id'], $requestId]);
        setFlash('Match status updated.', 'success');
        redirect(baseUrl() . '/hospital/request_matches.php?request_id=' . $requestId);
    }

    // Update request status
    if (isset($_POST['request_status'])) {
        $stmt = $pdo->prepare("UPDATE blood_requests SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['request_status'], $requestId]);
        setFlash('Request status updated.', 'success');
        redirect(baseUrl() . '/hospital/request_matches.php?request_id=' . $requestId);
    }
}

// Compatible donors
$compatTypes = getCompatibleDonorBloodTypes($request['patient_blood_type']);
$matches = [];
if (!empty($compatTypes)) {
    $in = implode(',', array_fill(0, count($compatTypes), '?'));
    $sql = "SELECT dp.*, u.email, dm.id as match_id, dm.status as match_status
            FROM donor_profiles dp
            JOIN users u ON dp.user_id = u.id
            LEFT JOIN donor_matches dm ON dm.donor_id = dp.user_id AND dm.request_id = ?
            WHERE u.is_active = 1 AND dp.is_available = 1 AND dp.blood_type IN ($in)";
    $params = [$requestId];
    $params = array_merge($params, $compatTypes);
    if (!empty($request['city'])) {
        $sql .= " AND dp.city LIKE ?";
        $params[] = '%' . $request['city'] . '%';
    }
    $sql .= " ORDER BY dp.city, dp.full_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $matches = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h1>Manage Matches for Request #<?php echo $requestId; ?></h1>
            <p style="margin:0;color:#6b7280;">Patient needs <strong><?php echo htmlspecialchars($request['patient_blood_type']); ?></strong> &middot; Urgency: <?php echo ucfirst($request['urgency']); ?></p>
        </div>
        <form method="POST" action="" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <select name="request_status" class="form-control" style="padding:8px;border-radius:5px;border:1px solid #d1d5db;">
                <option value="open" <?php echo $request['status']==='open'?'selected':''; ?>>Open</option>
                <option value="fulfilled" <?php echo $request['status']==='fulfilled'?'selected':''; ?>>Fulfilled</option>
                <option value="cancelled" <?php echo $request['status']==='cancelled'?'selected':''; ?>>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-small">Update Status</button>
        </form>
    </div>
    <p><strong>Required By:</strong> <?php echo $request['required_date'] ? htmlspecialchars($request['required_date']) : 'ASAP'; ?> &middot; <strong>Units:</strong> <?php echo (int)$request['units_needed']; ?></p>
</div>

<div class="card">
    <h2>Compatible Donors (<?php echo count($matches); ?>)</h2>
    <?php if (count($matches) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Donor</th>
                    <th>Blood Type</th>
                    <th>Location</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Match Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matches as $m): ?>
                <tr>
                    <td><?php echo htmlspecialchars($m['full_name']); ?></td>
                    <td><strong><?php echo htmlspecialchars($m['blood_type']); ?></strong></td>
                    <td><?php echo htmlspecialchars(($m['city'] ? $m['city'] . ', ' : '') . $m['state']); ?></td>
                    <td><?php echo htmlspecialchars($m['phone']); ?></td>
                    <td><?php echo htmlspecialchars($m['email']); ?></td>
                    <td>
                        <?php $status = $m['match_status'] ?: 'pending'; ?>
                        <span style="
                            display:inline-block;padding:4px 8px;border-radius:4px;font-size:0.8rem;font-weight:600;
                            <?php echo $status === 'confirmed' ? 'background:#dcfce7;color:#166534;' : ($status === 'declined' ? 'background:#fee2e2;color:#991b1b;' : ($status === 'contacted' ? 'background:#dbeafe;color:#1e40af;' : 'background:#f3f4f6;color:#374151;')); ?>
                        ">
                            <?php echo ucfirst($status); ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="" style="display:flex;gap:6px;">
                            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                            <input type="hidden" name="match_id" value="<?php echo (int)($m['match_id'] ?? 0); ?>">
                            <select name="match_status" style="padding:6px;border-radius:4px;border:1px solid #d1d5db;">
                                <option value="pending" <?php echo $status==='pending'?'selected':''; ?>>Pending</option>
                                <option value="contacted" <?php echo $status==='contacted'?'selected':''; ?>>Contacted</option>
                                <option value="confirmed" <?php echo $status==='confirmed'?'selected':''; ?>>Confirmed</option>
                                <option value="declined" <?php echo $status==='declined'?'selected':''; ?>>Declined</option>
                            </select>
                            <button type="submit" class="btn btn-small">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No compatible donors found for this request at the moment.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
