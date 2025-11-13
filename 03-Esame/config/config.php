<?php
// 🔧 CONFIGURAZIONE DATABASE MAMP
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 8889);
define('DB_NAME', '03-Esame');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// 🔐 SESSIONE + TOKEN CSRF (per sicurezza)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('psess');
session_start();

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_field() {
  $t = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
  echo '<input type="hidden" name="csrf_token" value="'.$t.'">';
}

function check_csrf() {
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(400);
    exit('CSRF non valida');
  }
}
