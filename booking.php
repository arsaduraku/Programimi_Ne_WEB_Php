<?php
class Booking {
    protected $tour;
    protected $persons;
    protected $total;

    public function __construct($tour, $persons, $total) {
        $this->tour = $tour;
        $this->persons = $persons;
        $this->total = $total;
    }

    public function getTour() { return $this->tour; }
    public function getPersons() { return $this->persons; }
    public function getTotal() { return $this->total; }
}
?>
