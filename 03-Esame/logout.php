<?php
require_once __DIR__ . '/config/config.php'; // include la session_start()
session_destroy();
header('Location: login.php');
exit;
