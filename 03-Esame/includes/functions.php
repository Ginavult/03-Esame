<?php
// Escape per sicurezza
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Validazione email
function validate_email($email){
  return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validazione campi richiesti
function validate_required($v,$min=1,$max=255){
  $len = mb_strlen(trim((string)$v));
  return $len >= $min && $len <= $max;
}

// Controllo login
function require_login(){
  if (empty($_SESSION['user_id'])) {
    header('Location: /03-Esame/login.php'); // 🔁 aggiorna con il tuo nome cartella
    exit;
  }
}
function slugify($text){
    $text = iconv('UTF-8','ASCII//TRANSLIT',$text);
    $text = preg_replace('~[^\\pL\\d]+~u','-',$text);
    $text = trim($text,'-');
    $text = strtolower($text);
    $text = preg_replace('~[^-a-z0-9]+~','', $text);
    return $text ?: 'n-a';
  }