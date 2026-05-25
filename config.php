<?php
require_once 'tour.php';
require_once 'db_connect.php'; 
session_start();

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

// Merr turet nga DB
global $conn;
if (isset($conn) && !$conn->connect_error) {
    $res   = $conn->query("SELECT * FROM tours ORDER BY id ASC");
    $tours = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) $tours[] = $row;
    }
    $_SESSION['tours'] = $tours;
} else {
    // Fallback nese DB nuk lidhet
    $tours_default = [
        ['id'=>1,'name'=>'Observatori','hours'=>2,  'price'=>2,   'spots'=>20],
        ['id'=>2,'name'=>'Muzeu',      'hours'=>3,  'price'=>7,   'spots'=>50],
        ['id'=>3,'name'=>'Katedralja', 'hours'=>1.5,'price'=>1.5, 'spots'=>25],
        ['id'=>4,'name'=>'Deep Space', 'hours'=>2,  'price'=>10,  'spots'=>20]
    ];
    if(!isset($_SESSION['tours'])) 
    $_SESSION['tours'] = $tours_default;
    $tours = $_SESSION['tours'];
}

// Inicializo rezervimet në session
if(!isset($_SESSION['bookings'])) $_SESSION['bookings'] = [];
?>