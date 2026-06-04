<?php

function isAdmin() {
    if (isset($_COOKIE['aqsmile_currentUser'])) {
        $currentUser = json_decode($_COOKIE['aqsmile_currentUser'], true);
        if (isset($currentUser['role']) && $currentUser['role'] === 'admin') {
            return true;
        }
    }
    return false;
}

function getAdminClass() {
    return isAdmin() ? 'admin-disabled' : '';
}

function getAdminDisabled() {
    return isAdmin() ? 'disabled' : '';
}

?>
