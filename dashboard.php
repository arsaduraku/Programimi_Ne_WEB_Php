<?php
include 'config.php';
include 'functions.php';
requireLogin();

$theme = $_COOKIE['theme'] ?? 'light';
if(isset($_POST['theme'])) {
    setcookie('theme', $_POST['theme'], time() + 86400 * 30);
    header('Location: dashboard.php');
    exit;
}

$myBookings = getUserBookings($_SESSION['user']['username']);
usort($myBookings, function($a, $b) {
    return $b['total'] <=> $a['total'];
});

$totalSpent = 0;
foreach($myBookings as $b) $totalSpent += $b['total'];

// Për admin-in, merr të gjitha rezervimet
$allBookings = [];
if(hasRole('admin')) {
    $allBookings = getAllBookings();
}

// Ndrysho statusin e rezervimit (vetëm admin)
if(isset($_POST['update_status']) && hasRole('admin')) {
    $bookingIndex = $_POST['booking_index'];
    $newStatus = $_POST['status'];
    if(updateBookingStatus($bookingIndex, $newStatus)) {
        header('Location: dashboard.php');
        exit;
    }
}
?>