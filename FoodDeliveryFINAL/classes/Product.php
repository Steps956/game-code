<?php

class Product {
    public $id;
    public $name;
    public $price;
    public $stock; // Optional for food products
    public $type; // 'inventory' or 'food'

    public function __construct($id, $name, $price, $stock = null, $type = 'inventory') {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->stock = $stock;
        $this->type = $type;
    }

    public function isAvailable() {
        return $this->stock > 0;
    }

    public function reduceStock($quantity) {
        if ($this->stock >= $quantity) {
            $this->stock -= $quantity;
        }
    }
}