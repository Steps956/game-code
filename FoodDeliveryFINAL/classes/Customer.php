<?php
require_once 'User.php';

class Customer extends User {
    private $address;
    public $phoneNumber; 

    public function __construct($id, $name, $address = null) {
        parent::__construct($id, $name, 'customer');
        $this->address = $address;
    }

    public function setAddress($address) {
        $this->address = $address;
    }
    public function setPhoneNumber($phoneNumber) {
        $this->phoneNumber = $phoneNumber;
    }
    public function getAddress() {
        return $this->address;
    }
    public function getPhoneNumber() {
        return $this->phoneNumber;
    }

    public function isCustomer() {
        return $this->role === 'customer';
    }

    public function addToCart(&$cart, $product, $quantity) {
        if ($product === null) {
            error_log("Product not found.");
            return;
        }

        foreach ($cart as &$item) {
            if ($item['product']->id === $product->id) {
                $item['quantity'] += $quantity;
                return;
            }
        }

        $cart[] = [
            'product' => $product,
            'quantity' => $quantity
        ];
    }

    public function removeFromCart(&$cart, $index) {
        if (isset($cart[$index])) {
            unset($cart[$index]);
            $cart = array_values($cart); // Reindex the cart array
        }
    }

    public function placeOrder(&$orders, &$cart, &$inventory, $paymentStatus, $paymentMethod) {
        $orderId = uniqid();
        $orderItems = [];

        foreach ($cart as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];

            // Deduct stock only for inventory products
            if ($product->type === 'inventory') {
                $inventoryProduct = $inventory->getProduct($product->id);
                if ($inventoryProduct !== null) {
                    $inventoryProduct->stock -= $quantity;
                }
            }

            $orderItems[] = [
                'product' => $product,
                'quantity' => $quantity
            ];
        }

        $orders[] = [
            'orderId' => $orderId,
            'username' => $this->name,
            'items' => $orderItems,
            'deliveryStatus' => 'Pending',
            'paymentStatus' => $paymentStatus,
            'paymentMethod' => $paymentMethod,
            'address' => $this->address,
            'phoneNumber' => $this->phoneNumber,
            'deliveryDriver' => null
        ];

        // Clear the cart
        $cart = [];
        saveOrders($orders);
        saveInventory($inventory);
    }

    public function submitReview(&$orders, $index, $review, $rating) {
        if (isset($orders[$index])) {
            $orders[$index]['review'] = $review;
            $orders[$index]['rating'] = $rating;
            saveOrders($orders);
        }
    }

    public function saveToJSON() {
        $customers = loadCustomers();

        // Check if the customer already exists
        foreach ($customers as &$customer) {
            if ($customer['id'] === $this->id) {
                $customer['address'] = $this->address;
                $customer['phoneNumber'] = $this->phoneNumber;
                saveCustomers($customers);
                return;
            }
        }

        // Add a new customer if not found
        $customers[] = [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'phoneNumber' => $this->phoneNumber
        ];
        saveCustomers($customers);
    }

    public function loadFromJSON() {
        $customers = loadCustomers();

        foreach ($customers as $customer) {
            if ($customer['id'] === $this->id) {
                $this->address = $customer['address'];
                $this->phoneNumber = $customer['phoneNumber'];
                return;
            }
        }
    }
}
