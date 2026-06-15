<?php

// Initialize session for admin restriction checking
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// If using cookies, sync to session (bridge from the client-side login state).
if (!isset($_SESSION['role']) && isset($_COOKIE['aqsmile_currentUser'])) {
    $user = json_decode($_COOKIE['aqsmile_currentUser'], true);
    if (isset($user['role'])) {
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['id'] ?? null;
        $_SESSION['user_email'] = $user['email'] ?? null;
        $_SESSION['user_name'] = $user['name'] ?? null;
    }
}

// Alternative: If you have a database connection, you can also fetch from DB
// $user_id = $_SESSION['user_id'] ?? null;
// if ($user_id) {
//     $user = fetch_one('SELECT role FROM users WHERE user_id = ?', [$user_id]);
//     if ($user) {
//         $_SESSION['role'] = $user['role'];
//     }
// }

?>
