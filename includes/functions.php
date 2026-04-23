<?php
require_once __DIR__ . '/db.php';

// Rate limiting for login attempts
function isRateLimited(string $identifier): bool {
    $maxAttempts = Config::getInt('MAX_LOGIN_ATTEMPTS', 5);
    $lockoutMinutes = Config::getInt('LOGIN_LOCKOUT_MINUTES', 15);
    
    $key = 'login_attempts_' . md5($identifier);
    $lockoutKey = 'login_lockout_' . md5($identifier);
    
    // Check if currently locked out
    if (isset($_SESSION[$lockoutKey]) && $_SESSION[$lockoutKey] > time()) {
        return true;
    }
    
    // Clear expired lockout
    if (isset($_SESSION[$lockoutKey]) && $_SESSION[$lockoutKey] <= time()) {
        unset($_SESSION[$lockoutKey]);
        unset($_SESSION[$key]);
    }
    
    return false;
}

function recordLoginAttempt(string $identifier): void {
    $maxAttempts = Config::getInt('MAX_LOGIN_ATTEMPTS', 5);
    $lockoutMinutes = Config::getInt('LOGIN_LOCKOUT_MINUTES', 15);
    
    $key = 'login_attempts_' . md5($identifier);
    $lockoutKey = 'login_lockout_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
    }
    
    $_SESSION[$key]['count']++;
    $_SESSION[$key]['last_attempt'] = time();
    
    // Lock out after max attempts
    if ($_SESSION[$key]['count'] >= $maxAttempts) {
        $_SESSION[$lockoutKey] = time() + ($lockoutMinutes * 60);
        error_log("Rate limit triggered for: " . $identifier);
    }
}

function clearLoginAttempts(string $identifier): void {
    $key = 'login_attempts_' . md5($identifier);
    $lockoutKey = 'login_lockout_' . md5($identifier);
    unset($_SESSION[$key]);
    unset($_SESSION[$lockoutKey]);
}

function getRateLimitRemaining(string $identifier): array {
    $maxAttempts = Config::getInt('MAX_LOGIN_ATTEMPTS', 5);
    $lockoutMinutes = Config::getInt('LOGIN_LOCKOUT_MINUTES', 15);
    
    $key = 'login_attempts_' . md5($identifier);
    $lockoutKey = 'login_lockout_' . md5($identifier);
    
    if (isset($_SESSION[$lockoutKey]) && $_SESSION[$lockoutKey] > time()) {
        $remaining = $_SESSION[$lockoutKey] - time();
        return [
            'locked' => true,
            'minutes_remaining' => ceil($remaining / 60),
            'attempts_remaining' => 0
        ];
    }
    
    $attempts = $_SESSION[$key]['count'] ?? 0;
    return [
        'locked' => false,
        'minutes_remaining' => 0,
        'attempts_remaining' => max(0, $maxAttempts - $attempts)
    ];
}

// Password validation
function validatePassword(string $password): array {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }
    
    return $errors;
}

// Input sanitization helpers
function sanitizeEmail(string $email): string {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

function sanitizeString(string $text): string {
    return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
}

function validatePhone(string $phone): bool {
    // Basic international phone validation
    return preg_match('/^[\+]?[\d\s\-\(\)]{8,20}$/', $phone) === 1;
}

// Pagination helper
function getPaginationParams(int $defaultPerPage = 25): array {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = isset($_GET['per_page']) ? max(5, min(100, (int)$_GET['per_page'])) : $defaultPerPage;
    $offset = ($page - 1) * $perPage;
    
    return [
        'page' => $page,
        'per_page' => $perPage,
        'offset' => $offset
    ];
}

function renderPagination(int $currentPage, int $totalPages, int $perPage, string $baseUrl): string {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 20px; flex-wrap: wrap;">';
    
    // Previous button
    if ($currentPage > 1) {
        $prevUrl = $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'page=' . ($currentPage - 1) . '&per_page=' . $perPage;
        $html .= '<a href="' . $prevUrl . '" class="btn btn-small btn-secondary">&larr; Previous</a>';
    }
    
    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    if ($startPage > 1) {
        $html .= '<span style="padding: 6px 12px;">...</span>';
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i === $currentPage) {
            $html .= '<span class="btn btn-small" style="background: #b91c1c; cursor: default;">' . $i . '</span>';
        } else {
            $pageUrl = $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'page=' . $i . '&per_page=' . $perPage;
            $html .= '<a href="' . $pageUrl . '" class="btn btn-small btn-secondary">' . $i . '</a>';
        }
    }
    
    if ($endPage < $totalPages) {
        $html .= '<span style="padding: 6px 12px;">...</span>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $nextUrl = $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'page=' . ($currentPage + 1) . '&per_page=' . $perPage;
        $html .= '<a href="' . $nextUrl . '" class="btn btn-small btn-secondary">Next &rarr;</a>';
    }
    
    $html .= '</div>';
    $html .= '<p style="text-align: center; color: #6b7280; font-size: 0.9rem; margin-top: 10px;">Page ' . $currentPage . ' of ' . $totalPages . '</p>';
    
    return $html;
}

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

// Geolocation: Convert city/state to lat/lng using Nominatim (OpenStreetMap)
function geocodeLocation(string $city, string $state, string $country = 'India'): ?array {
    $query = trim($city . ', ' . $state . ', ' . $country);
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'q' => $query,
        'format' => 'json',
        'limit' => 1,
        'countrycodes' => 'in'
    ]);
    
    $opts = [
        'http' => [
            'header' => "User-Agent: LifeLineBloodNetwork/1.0\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        return null;
    }
    
    $data = json_decode($response, true);
    if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
        return [
            'latitude' => (float)$data[0]['lat'],
            'longitude' => (float)$data[0]['lon']
        ];
    }
    
    return null;
}

// Calculate distance between two points using Haversine formula (km)
function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $earthRadius = 6371; // km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

// Audit logging
function auditLog(PDO $pdo, string $action, string $entityType, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null): void {
    $userId = $_SESSION['user_id'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $ipAddress,
            substr($userAgent, 0, 500)
        ]);
    } catch (Exception $e) {
        error_log("Audit log failed: " . $e->getMessage());
    }
}

// CSV export helper
function exportToCsv(array $headers, array $rows, string $filename): void {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    // BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    fputcsv($output, $headers);
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}
