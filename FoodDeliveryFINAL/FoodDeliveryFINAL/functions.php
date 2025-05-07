<?php
require_once 'classes/Product.php';
require_once 'classes/Inventory.php';
require_once 'classes/Order.php';
require_once 'classes/Foodproduct.php';

// File paths for JSON storage
define('INVENTORY_FILE', 'data/inventory.json');
define('ORDERS_FILE', 'data/orders.json');
define('FOOD_PRODUCTS_FILE', 'data/foodproducts.json');
define('DELIVERY_PERSONS_FILE', 'data/deliverypersons.json');

// Load inventory from JSON
function loadInventory() {
    $inventory = new Inventory();

    // Load regular inventory products
    if (file_exists(INVENTORY_FILE)) {
        $data = json_decode(file_get_contents(INVENTORY_FILE), true);
        foreach ($data as $item) {
            $inventory->addProduct(new Product(
                $item['id'],
                $item['name'],
                $item['price'],
                isset($item['stock']) ? $item['stock'] : 0,
                'inventory'
            ));
        }
    }

    return $inventory;
}

// Save inventory to JSON
function saveInventory($inventory) {
    $data = [];
    foreach ($inventory->listProducts() as $product) {
        $data[] = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'stock' => isset($product->stock) ? $product->stock : 0 // Ensure stock is always written
        ];
    }
    file_put_contents(INVENTORY_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

// Load orders from JSON
function loadOrders() {
    if (file_exists(ORDERS_FILE)) {
        $data = json_decode(file_get_contents(ORDERS_FILE), true);
        foreach ($data as &$order) {
            foreach ($order['items'] as &$item) {
                if (isset($item['product']) && is_array($item['product'])) {
                    $item['product'] = new Product(
                        $item['product']['id'],
                        $item['product']['name'],
                        $item['product']['price'],
                        isset($item['product']['stock']) ? $item['product']['stock'] : 0 // Default stock to 0 if missing
                    );
                } else {
                    $item['product'] = null;
                }
            }
        }
        return $data;
    }
    return [];
}

// Save orders to JSON
function saveOrders($orders) {
    file_put_contents('data/orders.json', json_encode($orders, JSON_PRETTY_PRINT));
}

// Load users from JSON
function loadUsers() {
    if (file_exists('data/users.json')) {
        return json_decode(file_get_contents('data/users.json'), true);
    }
    return [];
}

// Save users to JSON
function saveUsers($users) {
    file_put_contents('data/users.json', json_encode($users, JSON_PRETTY_PRINT));
}

// Load food products from JSON
function loadFoodProducts() {
    $foodProducts = [];

    if (file_exists(FOOD_PRODUCTS_FILE)) {
        $data = json_decode(file_get_contents(FOOD_PRODUCTS_FILE), true);
        foreach ($data as $item) {
            $foodProducts[] = new FoodProduct(
                $item['id'],
                $item['name'],
                $item['price'],
                $item['category'],
                $item['description']
            );
        }
    }

    return $foodProducts;
}

// Save food products to JSON
function saveFoodProducts($foodProducts) {
    $data = [];
    foreach ($foodProducts as $product) {
        $data[] = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'category' => $product->category,
            'description' => $product->description,
        ];
    }
    file_put_contents(FOOD_PRODUCTS_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

// Load delivery persons from JSON
function loadDeliveryPersons() {
    if (file_exists(DELIVERY_PERSONS_FILE)) {
        $data = json_decode(file_get_contents(DELIVERY_PERSONS_FILE), true);
        return is_array($data) ? $data : [];
    }
    return [];
}

// Save delivery persons to JSON
function saveDeliveryPersons($deliveryPersons) {
    file_put_contents(DELIVERY_PERSONS_FILE, json_encode($deliveryPersons, JSON_PRETTY_PRINT));
}

// Add a delivery person
function addDeliveryPerson($id, $name, $vehicleType) {
    $deliveryPersons = loadDeliveryPersons();
    $deliveryPersons[] = [
        'id' => $id,
        'name' => $name,
        'vehicleType' => $vehicleType
    ];
    saveDeliveryPersons($deliveryPersons);
}

// Remove a delivery person
function removeDeliveryPerson($id) {
    $deliveryPersons = loadDeliveryPersons();
    $deliveryPersons = array_filter($deliveryPersons, function ($person) use ($id) {
        return $person['id'] !== $id;
    });
    saveDeliveryPersons($deliveryPersons);
}

// Save customers to JSON
function saveCustomers($customers) {
    file_put_contents('data/customers.json', json_encode($customers, JSON_PRETTY_PRINT));
}

// Load customers from JSON
function loadCustomers() {
    if (file_exists('data/customers.json')) {
        return json_decode(file_get_contents('data/customers.json'), true);
    }
    return [];
}
?>