<?php

// Initialize PHP session before any authentication checks or navbar rendering.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function no_cache_headers()
{
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

function session_is_logged_in()
{
    return !empty($_SESSION['user_id']);
}

function session_is_admin()
{
    return session_is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

function session_is_patient()
{
    return session_is_logged_in() && ($_SESSION['role'] ?? '') !== 'admin';
}

?>
