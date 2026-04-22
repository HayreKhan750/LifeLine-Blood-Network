<?php
require_once __DIR__ . '/db.php';

// Redirect helper
function redirect(string $url): void {
    header("Location: " . $url);
    exit;
}

// Auth checks
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function isDonor(): bool {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'donor';
}

function isHospital(): bool {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'hospital';
}

function requireAuth(): void {
    if (!isLoggedIn()) {
        setFlash('Please login to continue.', 'danger');
        redirect('/blood_donor_system/login.php');
    }
}

function requireAdmin(): void {
    requireAuth();
    if (!isAdmin()) {
        setFlash('Access denied.', 'danger');
        redirect('/blood_donor_system/index.php');
    }
}

function requireDonor(): void {
    requireAuth();
    if (!isDonor()) {
        setFlash('Access denied.', 'danger');
        redirect('/blood_donor_system/index.php');
    }
}

function requireHospital(): void {
    requireAuth();
    if (!isHospital()) {
        setFlash('Access denied.', 'danger');
        redirect('/blood_donor_system/index.php');
    }
}

// Flash messages
function setFlash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// CSRF protection
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die('Invalid CSRF token');
    }
}

// Blood type compatibility: donor types that can donate TO the patient type
function getCompatibleDonorBloodTypes(string $patientType): array {
    $map = [
        'A+'  => ['A+', 'A-', 'O+', 'O-'],
        'A-'  => ['A-', 'O-'],
        'B+'  => ['B+', 'B-', 'O+', 'O-'],
        'B-'  => ['B-', 'O-'],
        'AB+' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
        'AB-' => ['A-', 'B-', 'AB-', 'O-'],
        'O+'  => ['O+', 'O-'],
        'O-'  => ['O-'],
    ];
    return $map[$patientType] ?? [];
}

// Fetch helpers
function getUserById(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function getDonorProfile(PDO $pdo, int $user_id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM donor_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function getHospitalProfile(PDO $pdo, int $user_id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM hospital_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// Base URL helper for portability
function baseUrl(): string {
    return '';
}


// Reverse map: which patient blood types can this donor donate to?
function getPatientBloodTypesForDonor(string $donorType): array {
    $map = [
        'A+'  => ['A+', 'AB+'],
        'A-'  => ['A+', 'A-', 'AB+', 'AB-'],
        'B+'  => ['B+', 'AB+'],
        'B-'  => ['B+', 'B-', 'AB+', 'AB-'],
        'AB+' => ['AB+'],
        'AB-' => ['AB+', 'AB-'],
        'O+'  => ['A+', 'B+', 'AB+', 'O+'],
        'O-'  => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
    ];
    return $map[$donorType] ?? [];
}
