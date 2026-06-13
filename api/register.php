<?php

// =====================================================

// api/register.php  —  POST  Register new user

// =====================================================

// Body (JSON): { name, email, phone, password, location, language_pref }

// =====================================================

require_once __DIR__ . '/../config/db.php';

apiHeaders();

startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    jsonResponse(false, null, 'Method not allowed.', 405);

}

$body = json_decode(file_get_contents('php://input'), true);

$name     = clean($body['name']     ?? '');

$email    = filter_var(trim($body['email'] ?? ''), FILTER_SANITIZE_EMAIL);

$phone    = clean($body['phone']    ?? '');

$password = $body['password']       ?? '';

$location = clean($body['location'] ?? '');

$lang     = in_array($body['language_pref'] ?? '', ['english','isizulu','sesotho','afrikaans'])

            ? $body['language_pref'] : 'english';

// Validate

if (!$name || !$email || !$password) {

    jsonResponse(false, null, 'Name, email and password are required.', 422);

}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    jsonResponse(false, null, 'Invalid email address.', 422);

}

if (strlen($password) < 6) {

    jsonResponse(false, null, 'Password must be at least 6 characters.', 422);

}

$db = getDB();

// Check duplicate email

$stmt = $db->prepare('SELECT id FROM users WHERE email = ?');

$stmt->execute([$email]);

if ($stmt->fetch()) {

    jsonResponse(false, null, 'Email already registered.', 409);

}

// Insert

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $db->prepare(

    'INSERT INTO users (name, email, phone, password_hash, location, language_pref)

     VALUES (?, ?, ?, ?, ?, ?)'

);

$stmt->execute([$name, $email, $phone, $hash, $location, $lang]);

$userId = (int)$db->lastInsertId();

// Start session

$_SESSION['user_id']   = $userId;

$_SESSION['user_name'] = $name;

$_SESSION['user_lang'] = $lang;

jsonResponse(true, [

    'id'            => $userId,

    'name'          => $name,

    'email'         => $email,

    'location'      => $location,

    'language_pref' => $lang,

    'avatar'        => 'uploads/avatars/default.png',

]);