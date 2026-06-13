<?php

// =====================================================

// api/logout.php  —  POST  Logout

// =====================================================

require_once __DIR__ . '/../config/db.php';

apiHeaders();

startSession();

session_destroy();

jsonResponse(true, ['message' => 'Logged out.']);