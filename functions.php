<?php
require_once 'Booking.php';
require_once 'specialBooking.php';

// Validimi me RegEx
function validEmail($email) {
    return preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email);
}

function validPhone($phone) {
    return preg_match("/^(?:\+383|0)[0-9]{8,9}$/", $phone);
}

// Rezervimi me OOP
function addBooking($userId, $tourName, $persons, $total) {
    if(!isset($_SESSION['bookings'])) $_SESSION['bookings'] = [];

    // Perdor OOP
    if($persons > 5) {
        $booking = new SpecialBooking($tourName, $persons, $total);
    } else {
        $booking = new Booking($tourName, $persons, $total);
    }

    $_SESSION['bookings'][] = [
        'username' => $userId,
        'tour' => $booking->getTour(),
        'persons' => $booking->getPersons(),
        'total' => $booking->getTotal(),
        'date' => date('Y-m-d H:i:s'),
        'status' => 'pending'
    ];
}

function getUserBookings($userId) {
    $userBookings = [];
    foreach($_SESSION['bookings'] as $booking) {
        if($booking['username'] == $userId) {
            $userBookings[] = $booking;
        }
    }
    return $userBookings;
}

function getAllBookings() {
    return isset($_SESSION['bookings']) ? $_SESSION['bookings'] : [];
}

function updateBookingStatus($bookingIndex, $status) {
    if(isset($_SESSION['bookings'][$bookingIndex])) {
        $_SESSION['bookings'][$bookingIndex]['status'] = $status;
        return true;
    }
    return false;
}
?>