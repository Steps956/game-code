<?php
// filepath: c:\xampp\htdocs\tamayo\FinalNagidNi\classes\Foodproduct.php

class FoodProduct {
    public $id;
    public $name;
    public $price;
    public $category; // e.g., Appetizer, Main Course, Dessert
    public $description; // Optional description of the food item
    public function __construct($id, $name, $price, $category = null, $description = null) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->category = $category;
        $this->description = $description;
    }
}