<?php
class Order {
    public $orderId;
    public $userId;
    public $items = [];
    public $totalPrice = 0;

    public function __construct($orderId, $userId) {
        $this->orderId = $orderId;
        $this->userId = $userId;
    }

    public function addItem($product, $quantity) {
        foreach ($this->items as &$item) {
            if ($item['product']->id === $product->id) {
                // If the product already exists, update the quantity
                $item['quantity'] += $quantity;
                $this->totalPrice += $product->price * $quantity;
                return;
            }
        }
        // If the product does not exist, add it as a new item
        $this->items[] = [
            'product' => $product,
            'quantity' => $quantity,
        ];
        $this->totalPrice += $product->price * $quantity;
    }

    public function getTotalPrice() {
        return $this->totalPrice;
    }
}