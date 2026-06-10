<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();

setcookie('aqsmile_currentUser', '', time() - 3600, '/');
setcookie('aqsmile_currentAdmin', '', time() - 3600, '/');

header('Location: index.php');
exit;
