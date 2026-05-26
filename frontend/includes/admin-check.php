<?php

function isAdmin() {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        return true;
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
