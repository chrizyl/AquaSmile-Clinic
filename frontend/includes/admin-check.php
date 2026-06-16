<?php

require_once __DIR__ . '/session-init.php';

function isAdmin() {
    return session_is_admin();
}

function requireAdminPage()
{
    if (!session_is_logged_in()) {
        header('Location: login.php');
        exit;
    }

    if (!session_is_admin()) {
        header('Location: index.php');
        exit;
    }
}

function requirePatientPage()
{
    if (!session_is_logged_in()) {
        header('Location: login.php');
        exit;
    }

    if (!session_is_patient()) {
        header('Location: index.php');
        exit;
    }
}

function getAdminClass() {
    return isAdmin() ? 'admin-disabled' : '';
}

function getAdminDisabled() {
    return isAdmin() ? 'disabled' : '';
}

?>
