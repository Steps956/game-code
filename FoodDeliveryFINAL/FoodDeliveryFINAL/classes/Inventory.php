<?php
class Inventory {
    private $products = [];

    public function addProduct($product) {
        $this->products[$product->id] = $product;
    }

    public function listProducts() {
        return $this->products;
    }

    public function getProduct($productId) {
        return $this->products[$productId] ?? null;
    }

    public function removeProduct($productId) {
        if (isset($this->products[$productId])) {
            unset($this->products[$productId]);
        }
    }
}