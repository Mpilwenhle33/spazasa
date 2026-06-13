<?php

// =====================================================

// SpazaSa — Database Configuration

// =====================================================

// Edit these values to match your hosting environment

// =====================================================

define('DB_HOST', 'localhost');

define('DB_NAME', 'spazasa');

define('DB_USER', 'root');        // change to your MySQL username

define('DB_PASS', '');            // change to your MySQL password

define('DB_CHARSET', 'utf8mb4');

define('BASE_URL', 'http://localhost/spazasa');   // change to your domain

define('UPLOAD_DIR', __DIR__ . '/../uploads/');

define('MAX_FILE_SIZE', 5 * 1024 * 1024);         // 5 MB

define('ALLOWED_IMG_TYPES', ['image/jpeg','image/png','image/webp','image/gif']);

// ---- PDO Connection (singleton) ----

function getDB(): PDO {

    static $pdo = null;

    if ($pdo === null) {

        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [

            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES   => false,

        ];

        try {

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {

            http_response_code(500);

            echo json_encode(['success' => false, 'error' => 'Database connection failed.']);

            exit;

        }

    }

    return $pdo;

}

// ---- Session helper ----

function startSession(): void {

    if (session_status() === PHP_SESSION_NONE) {

        session_start();

    }

}

// ---- Auth check ----

function requireAuth(): array {

    startSession();

    if (empty($_SESSION['user_id'])) {

        http_response_code(401);

        echo json_encode(['success' => false, 'error' => 'Not authenticated.']);

        exit;

    }

    return ['id' => $_SESSION['user_id'], 'name' => $_SESSION['user_name']];

}

// ---- JSON response helper ----

function jsonResponse(bool $success, mixed $data = null, string $error = '', int $code = 200): void {

    http_response_code($code);

    header('Content-Type: application/json');

    $payload = ['success' => $success];

    if ($data  !== null) $payload['data']  = $data;

    if ($error !== '')   $payload['error'] = $error;

    echo json_encode($payload);

    exit;

}

// ---- CORS + JSON headers (call at top of every API file) ----

function apiHeaders(): void {

    header('Content-Type: application/json');

    header('Access-Control-Allow-Origin: *');

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

}

// ---- Input sanitizer ----

function clean(string $val): string {

    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');

}