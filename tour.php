<?php
class Tour {
    private $name;
    private $price;
    private $hours;    
    private $spots;

    public function __construct($name, $price, $hours, $spots) {
        $this->name = $name;
        $this->price = $price;
        $this->hours = $hours;
        $this->spots = $spots;
    }

    public function getName() { return $this->name; }
    public function getPrice() { return $this->price; }
    public function getHours() { return $this->hours; }
    public function getSpots() { return $this->spots; }
}
?>