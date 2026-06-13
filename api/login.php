<?php

// =====================================================

// api/login.php  —  POST  Login

// =====================================================

// Body (JSON): { email, password }

// =====================================================

require_once __DIR__ . '/../config/db.php';

apiHeaders();

startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    jsonResponse(false, null, 'Method not allowed.', 405);

}

$body     = json_decode(file_get_contents('php://input'), true);

$email    = filter_var(trim($body['email']    ?? ''), FILTER_SANITIZE_EMAIL);

$password = $body['password'] ?? '';

if (!$email || !$password) {

    jsonResponse(false, null, 'Email and password are required.', 422);

}

$db   = getDB();

$stmt = $db->prepare('SELECT id, name, password_hash, avatar, location, language_pref FROM users WHERE email = ? AND is_active = 1');

$stmt->execute([$email]);

$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {

    jsonResponse(false, null, 'Incorrect email or password.', 401);

}

// Regenerate session ID for security

session_regenerate_id(true);

$_SESSION['user_id']   = $user['id'];

$_SESSION['user_name'] = $user['name'];

$_SESSION['user_lang'] = $user['language_pref'];

jsonResponse(true, [

    'id'            => $user['id'],

    'name'          => $user['name'],

    'avatar'        => $user['avatar'],

    'location'      => $user['location'],

    'language_pref' => $user['language_pref'],

]);