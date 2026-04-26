<?php
include 'config.php';
include 'functions.php';
require_once 'tour.php';

// Vetem admini ka qasje
requireAdmin();

$theme = $_COOKIE['theme'] ?? 'light';
$message = '';
$error = '';

// Shto tour te ri
if(isset($_POST['add_tour'])) {
    $name = trim($_POST['name']);
    $hours = (float)$_POST['hours'];
    $price = (float)$_POST['price'];
    $spots = (int)$_POST['spots'];
    
    if(empty($name)) {
        $error = "Emri i turit është i detyrueshëm!";
    } elseif($hours <= 0 || $price <= 0 || $spots <= 0) {
        $error = "Të gjitha vlerat duhet të jenë pozitive!";
    } else {
        $newId = count($_SESSION['tours']) + 1;
        $_SESSION['tours'][] = [
            'id' => $newId,
            'name' => $name,
            'hours' => $hours,
            'price' => $price,
            'spots' => $spots
        ];
        $tours = $_SESSION['tours'];
        $message = "Turi '{$name}' u shtua me sukses!";
    }
}

// Fshi tour
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    foreach($_SESSION['tours'] as $key => $tour) {
        if($tour['id'] == $id) {
            unset($_SESSION['tours'][$key]);
            $_SESSION['tours'] = array_values($_SESSION['tours']);
            $tours = $_SESSION['tours'];
            $message = "Turi u fshi me sukses!";
            break;
        }
    }
}

// Ndrysho tour
if(isset($_POST['edit_tour'])) {
    $id = (int)$_POST['tour_id'];
    $name = trim($_POST['name']);
    $hours = (float)$_POST['hours'];
    $price = (float)$_POST['price'];
    $spots = (int)$_POST['spots'];
    
    foreach($_SESSION['tours'] as $key => $tour) {
        if($tour['id'] == $id) {
            $_SESSION['tours'][$key] = [
                'id' => $id,
                'name' => $name,
                'hours' => $hours,
                'price' => $price,
                'spots' => $spots
            ];
            $tours = $_SESSION['tours'];
            $message = "Turi '{$name}' u ndryshua me sukses!";
            break;
        }
    }
}

$tours = $_SESSION['tours'];
?>