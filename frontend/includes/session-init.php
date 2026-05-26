<?php

// Initialize session for admin restriction checking
if (!isset($_SESSION)) {
    session_start();
}

// If using cookies, sync to session (bridge from old cookie system)
if (!isset($_SESSION['role']) && isset($_COOKIE['currentUser'])) {
    $user = json_decode($_COOKIE['currentUser'], true);
    if (isset($user['role'])) {
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['id'] ?? null;
        $_SESSION['user_email'] = $user['email'] ?? null;
    }
}

// Alternative: If you have a database connection, you can also fetch from DB
// $user_id = $_SESSION['user_id'] ?? null;
// if ($user_id) {
//     $user = fetch_one('SELECT role FROM users WHERE id = ?', [$user_id]);
//     if ($user) {
//         $_SESSION['role'] = $user['role'];
//     }
// }

?>
