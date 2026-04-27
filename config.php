<?php
require_once 'tour.php';
session_start();

$users = [
    'admin' => ['pass' => 'admin123', 'role' => 'admin', 'name' => 'Administratori'],
    'user'  => ['pass' => 'user123',  'role' => 'user',  'name' => 'Klienti']
];

// Cookie per display
if(!isset($_COOKIE['theme'])) {
    setcookie('theme', 'light', time() + 86400 * 30);
}

// Funksionet baze
function isLogged() { return isset($_SESSION['user']); }
function hasRole($r) { return isLogged() && $_SESSION['user']['role'] == $r; }
function requireLogin() { if(!isLogged()) header('Location: login.php'); }
function requireAdmin() { 
    if(!isLogged() || !hasRole('admin')) {
        header('Location: dashboard.php');
        exit;
    }
}

// array multidimensionale
$tours = [
    ['id'=>1, 'name'=>'Observatori', 'hours'=>2, 'price'=>2, 'spots'=>20],
    ['id'=>2, 'name'=>'Muzeu', 'hours'=>3, 'price'=>7, 'spots'=>50],
    ['id'=>3, 'name'=>'Katedralja', 'hours'=>1.5, 'price'=>1.5, 'spots'=>25],
    ['id'=>4, 'name'=>'Deep Space', 'hours'=>2, 'price'=>10, 'spots'=>20]
];

// Ruhen ne session per t'i mbajtur ndryshimet
if(!isset($_SESSION['tours'])) {
    $_SESSION['tours'] = $tours;
} else {
    $tours = $_SESSION['tours'];
}

// Inicializo rezervimet në session
if(!isset($_SESSION['bookings'])) {
    $_SESSION['bookings'] = [];
}
?>