<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function isAdmin() {
    return !empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin';
}

function getAdminClass() {
    return isAdmin() ? 'admin-disabled' : '';
}

function getAdminDisabled() {
    return isAdmin() ? 'disabled' : '';
}

?>
