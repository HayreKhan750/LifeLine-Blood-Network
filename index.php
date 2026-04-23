<?php
require_once 'includes/functions.php';
$pageTitle = 'Home';

// Stats
$donorCount = $pdo->query("SELECT COUNT(*) FROM donor_profiles")->fetchColumn();
$hospitalCount = $pdo->query("SELECT COUNT(*) FROM hospital_profiles")->fetchColumn();
$requestCount = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'open'")->fetchColumn();
$fulfilledCount = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'fulfilled'")->fetchColumn();

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

// Featured donors for spotlight
$donorImages = ['donor-avatar-1.jpg', 'donor-avatar-2.jpg', 'donor-avatar-3.jpg', 'donor-avatar-4.jpg', 'donor-avatar-5.jpg'];
$featuredDonors = $pdo->query("
    SELECT dp.full_name, dp.blood_type, dp.city, dp.is_available
    FROM donor_profiles dp JOIN users u ON dp.user_id = u.id
    WHERE u.is_active = 1 AND dp.is_available = 1
    ORDER BY COALESCE(dp.last_donation_date, '1970-01-01') DESC
    LIMIT 5
")->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <h1>Every Drop Counts.<br>Save a Life Today.</h1>
    <p>Connecting hospitals with voluntary blood donors across India. Find compatible donors, create urgent requests, and save lives in emergencies.</p>
    <div style="margin-top: 24px; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; position: relative;">
        <a href="<?php echo baseUrl(); ?>/find_donors.php" class="btn btn-large">&#128269; Find Donors</a>
        <a href="<?php echo baseUrl(); ?>/register.php" class="btn btn-large btn-outline" style="color:#fff;border-color:rgba(255,255,255,0.5);">&#10084; Become a Donor</a>
        <a href="<?php echo baseUrl(); ?>/emergency.php" class="btn btn-large" style="background:linear-gradient(135deg,#d63031,#c0392b);">&#9888; Emergency SOS</a>
    </div>
</section>

<!-- Trust Bar -->
<section class="trust-bar">
    <div class="trust-item">
        <div class="trust-icon">&#128101;</div>
        <div class="trust-value"><?php echo (int)$donorCount; ?>+</div>
        <div class="trust-label">Registered Donors</div>
    </div>
    <div class="trust-item">
        <div class="trust-icon">&#127973;</div>
        <div class="trust-value"><?php echo (int)$hospitalCount; ?>+</div>
        <div class="trust-label">Partner Hospitals</div>
    </div>
    <div class="trust-item">
        <div class="trust-icon">&#128257;</div>
        <div class="trust-value"><?php echo (int)$requestCount; ?></div>
        <div class="trust-label">Open Requests</div>
    </div>
    <div class="trust-item">
        <div class="trust-icon">&#10084;</div>
        <div class="trust-value"><?php echo (int)$fulfilledCount; ?>+</div>
        <div class="trust-label">Lives Saved</div>
    </div>
</section>

<!-- How It Works -->
<div class="section-divider"><h2>How It Works</h2></div>
<section style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <div class="feature-card">
        <div class="feature-icon">&#128221;</div>
        <h3>1. Register</h3>
        <p>Sign up as a donor or hospital. Build your profile with blood type and location details.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">&#128228;</div>
        <h3>2. Request</h3>
        <p>Hospitals submit urgent blood requests with patient blood type and location.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">&#128269;</div>
        <h3>3. Match</h3>
        <p>Our system instantly finds compatible donors nearby and displays their contact details.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">&#128154;</div>
        <h3>4. Save Lives</h3>
        <p>Donors respond, blood is donated, and lives are saved. It's that simple.</p>
    </div>
</section>

<!-- Blood Type Explorer -->
<div class="section-divider"><h2>Blood Type Compatibility</h2></div>
<section class="card" style="margin-bottom: 40px;">
    <div class="blood-explorer">
        <div>
            <h3 style="margin-bottom: 16px; color: var(--primary-dark);">Select a Blood Type</h3>
            <div class="blood-type-grid" id="bloodTypeGrid">
                <button class="blood-type-btn" onclick="showCompat('A+')"><span class="type-label">A</span><span class="type-rh">Positive</span></button>
                <button class="blood-type-btn" onclick="showCompat('A-')"><span class="type-label">A</span><span class="type-rh">Negative</span></button>
                <button class="blood-type-btn" onclick="showCompat('B+')"><span class="type-label">B</span><span class="type-rh">Positive</span></button>
                <button class="blood-type-btn" onclick="showCompat('B-')"><span class="type-label">B</span><span class="type-rh">Negative</span></button>
                <button class="blood-type-btn" onclick="showCompat('AB+')"><span class="type-label">AB</span><span class="type-rh">Positive</span></button>
                <button class="blood-type-btn" onclick="showCompat('AB-')"><span class="type-label">AB</span><span class="type-rh">Negative</span></button>
                <button class="blood-type-btn" onclick="showCompat('O+')"><span class="type-label">O</span><span class="type-rh">Positive</span></button>
                <button class="blood-type-btn" onclick="showCompat('O-')"><span class="type-label">O</span><span class="type-rh">Negative</span></button>
            </div>
        </div>
        <div class="compat-panel" id="compatPanel">
            <h3>&#9764; Click a blood type to see compatibility</h3>
            <p style="color: var(--gray-500);">Learn which blood types can donate to and receive from each other.</p>
        </div>
    </div>
</section>

<!-- Recent Urgent Requests -->
<div class="section-divider"><h2>Urgent Blood Requests</h2></div>
<section class="card">
    <div class="card-header">
        <h2>Recent Requests</h2>
        <a href="<?php echo baseUrl(); ?>/find_donors.php" class="btn btn-small">View All</a>
    </div>
    <?php if (count($recentRequests) > 0): ?>
        <div class="table-wrapper">
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
                        <span class="badge badge-<?php echo $req['urgency']; ?>">
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
        </div>
    <?php else: ?>
        <p>No open blood requests at the moment.</p>
    <?php endif; ?>
</section>

<!-- Donor Spotlight -->
<?php if (count($featuredDonors) > 0): ?>
<div class="section-divider"><h2>Donor Spotlight</h2></div>
<section class="donor-spotlight">
    <?php foreach ($featuredDonors as $i => $d): ?>
    <div class="donor-card">
        <img src="<?php echo baseUrl(); ?>/assets/images/<?php echo $donorImages[$i % count($donorImages)]; ?>" alt="Donor" class="donor-avatar">
        <h4><?php echo htmlspecialchars($d['full_name']); ?></h4>
        <span class="blood-badge"><?php echo htmlspecialchars($d['blood_type']); ?></span>
        <p style="font-size: 0.85rem; color: var(--gray-500); margin:0;"><?php echo htmlspecialchars($d['city']); ?></p>
    </div>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- Register CTA -->
<section class="card" style="text-align: center; padding: 48px 32px; background: linear-gradient(135deg, var(--primary), var(--primary-darker)); color: #fff; border: none;">
    <h2 style="color: #fff; font-family: 'Playfair Display', serif; font-size: 2rem;">Ready to Save a Life?</h2>
    <p style="color: rgba(255,255,255,0.9); max-width: 500px; margin: 12px auto 24px;">Join thousands of donors who are making a difference. Registration takes less than 2 minutes.</p>
    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
        <a href="<?php echo baseUrl(); ?>/register.php" class="btn btn-large" style="background: #fff; color: var(--primary-dark);">&#10084; Register as Donor</a>
        <a href="<?php echo baseUrl(); ?>/register.php" class="btn btn-large btn-outline" style="color:#fff;border-color:rgba(255,255,255,0.5);">&#127973; Register Hospital</a>
    </div>
</section>

<script>
const compatData = {
    'A+':  { donateTo: ['A+', 'AB+'], receiveFrom: ['A+', 'A-', 'O+', 'O-'] },
    'A-':  { donateTo: ['A+', 'A-', 'AB+', 'AB-'], receiveFrom: ['A-', 'O-'] },
    'B+':  { donateTo: ['B+', 'AB+'], receiveFrom: ['B+', 'B-', 'O+', 'O-'] },
    'B-':  { donateTo: ['B+', 'B-', 'AB+', 'AB-'], receiveFrom: ['B-', 'O-'] },
    'AB+': { donateTo: ['AB+'], receiveFrom: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] },
    'AB-': { donateTo: ['AB+', 'AB-'], receiveFrom: ['A-', 'B-', 'AB-', 'O-'] },
    'O+':  { donateTo: ['A+', 'B+', 'AB+', 'O+'], receiveFrom: ['O+', 'O-'] },
    'O-':  { donateTo: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], receiveFrom: ['O-'] }
};

function showCompat(type) {
    const data = compatData[type];
    const panel = document.getElementById('compatPanel');
    const btns = document.querySelectorAll('.blood-type-btn');
    btns.forEach(b => b.classList.remove('active'));
    event.currentTarget.classList.add('active');

    panel.innerHTML = `
        <h3 style="color:var(--primary);">Blood Type ${type}</h3>
        <div class="compat-section-title">Can Donate To</div>
        <div class="compat-list">${data.donateTo.map(t => '<span class="compat-tag">' + t + '</span>').join('')}</div>
        <div class="compat-section-title">Can Receive From</div>
        <div class="compat-list">${data.receiveFrom.map(t => '<span class="compat-tag">' + t + '</span>').join('')}</div>
        <div style="margin-top:16px;padding:12px;background:rgba(196,75,75,0.05);border-radius:8px;font-size:0.85rem;color:var(--gray-600);">
            ${type === 'O-' ? '&#11088; Universal Donor — can donate to all blood types!' : ''}
            ${type === 'AB+' ? '&#11088; Universal Recipient — can receive from all blood types!' : ''}
            ${type === 'AB-' ? 'Rare type — only 1% of the population.' : ''}
            ${type === 'O+' ? 'Most common blood type — 37% of the population.' : ''}
        </div>
    `;
}
</script>

<?php include 'includes/footer.php'; ?>
