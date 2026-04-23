<?php
require_once 'includes/functions.php';
$pageTitle = 'Donor Eligibility Check';
include 'includes/header.php';
?>

<section class="hero" style="padding: 48px 32px;">
    <h1>&#9989; Donor Eligibility Checker</h1>
    <p>Check if you're eligible to donate blood. Answer a few quick questions to find out.</p>
</section>

<div class="card" style="max-width: 700px; margin: 0 auto;">
    <form id="eligibilityForm" onsubmit="return checkEligibility()">
        <h3 style="margin-bottom: 20px; color: var(--primary-dark);">Answer the following questions:</h3>

        <div class="form-group">
            <label>1. Are you between 18 and 65 years of age?</label>
            <select name="age" required>
                <option value="">Select...</option>
                <option value="yes">Yes</option>
                <option value="no">No (Under 18 or Over 65)</option>
            </select>
        </div>

        <div class="form-group">
            <label>2. Do you weigh at least 50 kg (110 lbs)?</label>
            <select name="weight" required>
                <option value="">Select...</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>
        </div>

        <div class="form-group">
            <label>3. Is your hemoglobin level 12.5 g/dL or above?</label>
            <select name="hemoglobin" required>
                <option value="">Select...</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
                <option value="unknown">I don't know</option>
            </select>
        </div>

        <div class="form-group">
            <label>4. Have you donated blood in the last 3 months?</label>
            <select name="recent_donation" required>
                <option value="">Select...</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>
        </div>

        <div class="form-group">
            <label>5. Are you currently on antibiotics or medication for an infection?</label>
            <select name="medication" required>
                <option value="">Select...</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>
        </div>

        <div class="form-group">
            <label>6. Have you had a tattoo or body piercing in the last 6 months?</label>
            <select name="tattoo" required>
                <option value="">Select...</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>
        </div>

        <div class="form-group">
            <label>7. Have you been diagnosed with HIV, Hepatitis B/C, or Syphilis?</label>
            <select name="disease" required>
                <option value="">Select...</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>
        </div>

        <div class="form-group">
            <label>8. Are you pregnant or have you given birth in the last 6 months?</label>
            <select name="pregnant" required>
                <option value="">Select...</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
                <option value="na">Not applicable</option>
            </select>
        </div>

        <div class="form-group">
            <label>9. Have you consumed alcohol in the last 24 hours?</label>
            <select name="alcohol" required>
                <option value="">Select...</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>
        </div>

        <div class="form-group">
            <label>10. Do you have any chronic illness (diabetes, heart disease, cancer)?</label>
            <select name="chronic" required>
                <option value="">Select...</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>
        </div>

        <button type="submit" class="btn btn-large" style="width: 100%;">Check My Eligibility</button>
    </form>

    <div id="eligibilityResult" style="display: none;"></div>
</div>

<div class="card" style="max-width: 700px; margin: 24px auto 0;">
    <h3 style="color: var(--primary-dark); margin-bottom: 12px;">General Eligibility Guidelines</h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
            <h4 style="color: var(--success); font-size: 0.95rem;">&#10004; You CAN donate if:</h4>
            <ul style="padding-left: 20px; color: var(--gray-600); font-size: 0.9rem;">
                <li>Age 18-65 years</li>
                <li>Weight 50+ kg</li>
                <li>Hemoglobin 12.5+ g/dL</li>
                <li>No donation in last 3 months</li>
                <li>Free from infections</li>
                <li>No tattoo/piercing in 6 months</li>
                <li>Not pregnant (last 6 months)</li>
            </ul>
        </div>
        <div>
            <h4 style="color: var(--danger); font-size: 0.95rem;">&#10008; You CANNOT donate if:</h4>
            <ul style="padding-left: 20px; color: var(--gray-600); font-size: 0.9rem;">
                <li>Under 18 or over 65</li>
                <li>Weight below 50 kg</li>
                <li>HIV, Hepatitis B/C, Syphilis</li>
                <li>On antibiotics currently</li>
                <li>Recent tattoo or piercing</li>
                <li>Pregnant or recent childbirth</li>
                <li>Alcohol in last 24 hours</li>
            </ul>
        </div>
    </div>
</div>

<script>
function checkEligibility() {
    const form = document.getElementById('eligibilityForm');
    const fd = new FormData(form);
    const result = document.getElementById('eligibilityResult');

    const disqualifying = ['age', 'weight', 'disease'];
    const cautionary = ['hemoglobin', 'recent_donation', 'medication', 'tattoo', 'pregnant', 'alcohol', 'chronic'];

    let eligible = true;
    let reasons = [];

    // Hard disqualifiers
    for (const q of disqualifying) {
        if (fd.get(q) === 'no') {
            eligible = false;
            const labels = {
                age: 'You must be between 18 and 65 years of age',
                weight: 'You must weigh at least 50 kg',
                disease: 'Diagnosed with HIV/Hepatitis/Syphilis disqualifies donation'
            };
            reasons.push(labels[q]);
        }
    }

    // Cautionary checks
    for (const q of cautionary) {
        if (fd.get(q) === 'yes') {
            eligible = false;
            const labels = {
                hemoglobin: 'Hemoglobin below 12.5 g/dL may disqualify you',
                recent_donation: 'Must wait 3 months between donations',
                medication: 'Current infection/antibiotics disqualify temporarily',
                tattoo: 'Must wait 6 months after tattoo/piercing',
                pregnant: 'Pregnancy and 6 months post-childbirth disqualify',
                alcohol: 'No alcohol 24 hours before donation',
                chronic: 'Chronic illness may disqualify - consult doctor'
            };
            reasons.push(labels[q]);
        }
    }

    if (fd.get('hemoglobin') === 'unknown') {
        reasons.push('Get your hemoglobin checked before donating');
    }

    let html = '';
    if (eligible && reasons.length === 0) {
        html = '<div class="eligibility-result eligible"><h3>&#10004; You Appear Eligible!</h3><p style="color:#155724;">Based on your answers, you may be eligible to donate blood. Please visit a blood bank for final confirmation.</p><a href="' + '<?php echo baseUrl(); ?>/register.php" class="btn btn-success" style="margin-top:12px;">Register as a Donor</a></div>';
    } else {
        html = '<div class="eligibility-result ineligible"><h3>&#10008; You May Not Be Eligible</h3><p style="color:#721c24;">Based on your answers:</p><ul style="text-align:left;color:#721c24;padding-left:20px;">';
        for (const r of reasons) {
            html += '<li>' + r + '</li>';
        }
        html += '</ul><p style="color:#856404;margin-top:12px;font-size:0.9rem;">Consult a medical professional for definitive eligibility assessment.</p></div>';
    }

    result.innerHTML = html;
    result.style.display = 'block';
    result.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return false;
}
</script>

<?php include 'includes/footer.php'; ?>
