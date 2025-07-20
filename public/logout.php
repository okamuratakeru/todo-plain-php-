<?php
require_once __DIR__ . '/../includes/functions.php';
init_session();
logout();
header('Location: login.php');
exit;