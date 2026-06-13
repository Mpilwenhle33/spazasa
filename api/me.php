<?php

// =====================================================

// api/me.php  —  GET  Return current session user

// =====================================================

require_once __DIR__ . '/../config/db.php';

apiHeaders();

startSession();

if (empty($_SESSION['user_id'])) {

    jsonResponse(false, null, 'Not logged in.', 401);

}

$db   = getDB();

$stmt = $db->prepare('SELECT id, name, email, phone, avatar, location, language_pref FROM users WHERE id = ?');

$stmt->execute([$_SESSION['user_id']]);

$user = $stmt->fetch();

if (!$user) {

    session_destroy();

    jsonResponse(false, null, 'User not found.', 404);

}

jsonResponse(true, $user);