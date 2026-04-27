<?php
require_once 'booking.php';

class SpecialBooking extends Booking {
    private $discount = 0.1;

    public function getTotal() {
        return $this->total - ($this->total * $this->discount);
    }
}
?>