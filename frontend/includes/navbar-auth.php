<?php

require_once __DIR__ . '/session-init.php';

function nav_user_name()
{
    return trim((string) ($_SESSION['user_name'] ?? 'AquaSmile User'));
}

function nav_user_initials()
{
    $parts = preg_split('/\s+/', nav_user_name(), -1, PREG_SPLIT_NO_EMPTY);
    if (!$parts) {
        return 'AS';
    }

    $first = substr($parts[0], 0, 1);
    $last = count($parts) > 1 ? substr($parts[count($parts) - 1], 0, 1) : '';
    return strtoupper($first . $last);
}

function nav_is_patient()
{
    return session_is_patient();
}

function render_nav_auth()
{
    if (nav_is_patient()) {
        $name = htmlspecialchars(nav_user_name(), ENT_QUOTES, 'UTF-8');
        $initials = htmlspecialchars(nav_user_initials(), ENT_QUOTES, 'UTF-8');
        ?>
        <span id="nav-auth-state" data-authenticated="patient" hidden></span>
        <div class="notify-wrap" id="notify-wrap" data-owner="server">
          <button class="notify-btn" type="button" onclick="AquaNotify.toggle()" aria-label="Notifications">
            <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 10-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
            <span class="notify-count" id="notify-count">0</span>
          </button>
          <div class="notify-panel" id="notify-panel"></div>
        </div>
        <div id="nav-user-info" class="account-nav">
          <a class="account-nav-link" href="user.php" aria-label="Open <?php echo $name; ?>'s patient account">
            <span class="account-nav-avatar" aria-hidden="true"><?php echo $initials; ?></span>
            <span class="account-nav-name"><?php echo $name; ?></span>
          </a>
        </div>
        <button class="nav-btn pill-aqua" id="nav-logout-btn" onclick="window.location.href='logout.php'">Logout</button>
        <?php
        return;
    }

    if (session_is_admin()) {
        $name = htmlspecialchars(nav_user_name(), ENT_QUOTES, 'UTF-8');
        ?>
        <span id="nav-auth-state" data-authenticated="admin" hidden></span>
        <div id="nav-user-info"><?php echo $name; ?></div>
        <button class="nav-btn pill-aqua" id="nav-logout-btn" onclick="window.location.href='logout.php'">Logout</button>
        <?php
        return;
    }
    ?>
    <span id="nav-auth-state" data-authenticated="guest" hidden></span>
    <button class="nav-btn pill" id="nav-login-btn" onclick="window.location.href='login.php'">Log In</button>
    <?php
}
