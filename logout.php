<?php
require_once 'includes/session.php';
clearRememberCookie();
session_unset();
session_destroy();
header("Location: login.php");
exit;
