<?php
require_once 'User.php';
require_once 'Product.php';
require_once 'FoodProduct.php';

class Admin extends User {
    private $inventory;
    private $orders;
    private $foodProducts;

    public function __construct($id, $name) {
        parent::__construct($id, $name, 'admin');
        $this->inventory = $this->loadInventory();
        $this->orders = $this->loadOrders();
        $this->foodProducts = $this->loadFoodProducts();
    }

    private function loadInventory() {
        return loadInventory();
    }

    private function loadOrders() {
        return loadOrders();
    }

    private function loadFoodProducts() {
        return loadFoodProducts();
    }

    public function getInventory() {
        return $this->inventory;
    }

    public function getOrders() {
        return $this->orders;
    }

    public function getFoodProducts() {
        return $this->foodProducts;
    }

    public function saveInventory() {
        saveInventory($this->inventory);
    }

    public function saveFoodProducts() {
        saveFoodProducts($this->foodProducts);
    }

    public function addProduct($product) {
        $this->inventory->addProduct($product);
        $this->saveInventory();
    }

    public function removeProduct($productId) {
        $this->inventory->removeProduct($productId);
        $this->saveInventory();
    }

    public function updateStock($productId, $amount) {
        $product = $this->inventory->getProduct($productId);
        if ($product) {
            $product->stock += $amount;
            $this->saveInventory();
        }
    }

    public function addFoodProduct($foodProduct) {
        $this->foodProducts[] = $foodProduct;
        $this->saveFoodProducts();
    }

    public function removeFoodProduct($productId) {
        $this->foodProducts = array_filter($this->foodProducts, function ($product) use ($productId) {
            return $product->id !== $productId;
        });
        $this->saveFoodProducts();
    }

    public function calculateSalesReport() {
        $orders = loadOrders();
        $salesReport = [
            'totalSales' => 0,
            'totalItemsSold' => 0,
            'itemsSold' => []
        ];

        foreach ($orders as $order) {
            if ($order['deliveryStatus'] === 'Delivered') { // Only count delivered orders
                foreach ($order['items'] as $item) {
                    $productName = $item['product']->name;
                    $quantity = $item['quantity'];
                    $totalPrice = $item['product']->price * $quantity;

                    // Update total sales and total items sold
                    $salesReport['totalSales'] += $totalPrice;
                    $salesReport['totalItemsSold'] += $quantity;

                    // Update the itemsSold breakdown
                    if (!isset($salesReport['itemsSold'][$productName])) {
                        $salesReport['itemsSold'][$productName] = [
                            'quantity' => 0,
                            'totalPrice' => 0
                        ];
                    }
                    $salesReport['itemsSold'][$productName]['quantity'] += $quantity;
                    $salesReport['itemsSold'][$productName]['totalPrice'] += $totalPrice;
                }
            }
        }

        return $salesReport;
    }
}
?>