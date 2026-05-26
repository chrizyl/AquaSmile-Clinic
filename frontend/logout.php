<?php

session_start();
session_destroy();

setcookie('currentUser', '', time() - 3600, '/');
setcookie('currentAdmin', '', time() - 3600, '/');

header('Location: index.php');
exit;
