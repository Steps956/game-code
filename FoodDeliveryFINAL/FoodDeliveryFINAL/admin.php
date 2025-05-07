<?php
require_once 'functions.php';
require_once 'classes/Admin.php';
// Ensure this contains Product and FoodProduct classes

session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']->isAdmin()) {
    header("Location: index.php");
    exit;
}

// Re-initialize the Admin object to ensure full class functionality
$admin = $_SESSION['user'] = new Admin($_SESSION['user']->id, $_SESSION['user']->name);

$inventory = $admin->getInventory();
$orders = $admin->getOrders();
$foodProducts = $admin->getFoodProducts();
$salesReport = $admin->calculateSalesReport();

// Handle adding an item
if (isset($_POST['add_item'])) {
    $name = $_POST['item_name'];
    $price = $_POST['item_price'];
    $stock = $_POST['item_stock'];

    $admin->addProduct(new Product(uniqid(), $name, $price, $stock));
    header("Location: admin.php");
    exit;
}

// Handle adding a food product
if (isset($_POST['add_food_product'])) {
    $name = $_POST['item_name'];
    $price = $_POST['item_price'];
    $category = $_POST['item_category'];
    $description = $_POST['item_description'];

    $admin->addFoodProduct(new FoodProduct(uniqid(), $name, $price, $category, $description));
    header("Location: admin.php");
    exit;
}

// Handle removing an item
if (isset($_POST['remove_item'])) {
    $productId = $_POST['product_id'];
    $admin->removeProduct($productId);
    header("Location: admin.php");
    exit;
}

// Handle removing a food product
if (isset($_POST['remove_food_product'])) {
    $productId = $_POST['product_id'];
    $admin->removeFoodProduct($productId);
    header("Location: admin.php");
    exit;
}

// Handle updating stock
if (isset($_POST['update_stock'])) {
    $productId = $_POST['product_id'];
    $additionalStock = (int)$_POST['additional_stock'];

    $admin->updateStock($productId, $additionalStock);
    header("Location: admin.php");
    exit;
}

// Handle adding a delivery person
if (isset($_POST['add_delivery_person'])) {
    $name = $_POST['delivery_name'];
    $vehicleType = $_POST['vehicle_type'];
    addDeliveryPerson(uniqid(), $name, $vehicleType);
    header("Location: admin.php");
    exit;
}

// Handle removing a delivery person
if (isset($_POST['remove_delivery_person'])) {
    $id = $_POST['delivery_id'];
    removeDeliveryPerson($id);
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        :root {
            --primary: #4a6fa5;
            --primary-dark: #345381;
            --primary-light: #eef2f8;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --light: #f8f9fa;
            --dark: #343a40;
            --border-radius: 4px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7fa;
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--primary);
            color: white;
            padding: 15px 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        header h1 {
            margin: 0;
            font-size: 28px;
        }
        
        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background-color: white;
            padding: 15px;
            border-radius: var(--border-radius);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .logout-btn {
            background-color: var(--danger);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .logout-btn:hover {
            background-color: #c82333;
        }
        
        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #f1f3f5;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .card-header h3 {
            margin: 0;
            color: var(--primary-dark);
            font-size: 18px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        form {
            display: grid;
            grid-gap: 15px;
        }
        
        @media (min-width: 768px) {
            form.multi-column {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: var(--dark);
            font-weight: 500;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 1rem;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        button {
            cursor: pointer;
            padding: 10px 15px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        button[name="add_item"],
        button[name="add_food_product"],
        button[name="add_delivery_person"] {
            background-color: var(--primary);
            color: white;
        }
        
        button[name="add_item"]:hover,
        button[name="add_food_product"]:hover,
        button[name="add_delivery_person"]:hover {
            background-color: var(--primary-dark);
        }
        
        button[name="update_stock"] {
            background-color: var(--warning);
            color: #212529;
        }
        
        button[name="update_stock"]:hover {
            background-color: #e0a800;
        }
        
        button[name="remove_item"],
        button[name="remove_food_product"],
        button[name="remove_delivery_person"] {
            background-color: var(--danger);
            color: white;
        }
        
        button[name="remove_item"]:hover,
        button[name="remove_food_product"]:hover,
        button[name="remove_delivery_person"]:hover {
            background-color: #c82333;
        }
        
        .sales-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            grid-gap: 20px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: var(--primary-dark);
            margin-bottom: 10px;
        }
        
        .stat-card p {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        
        .item-list {
            list-style: none;
        }
        
        .item-list li {
            border-bottom: 1px solid #e9ecef;
            padding: 15px 0;
        }
        
        .item-list li:last-child {
            border-bottom: none;
        }
        
        .food-item {
            background-color: #f9f9f9;
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .food-details {
            margin-bottom: 10px;
        }
        
        .inline-form {
            display: inline-block;
            margin-top: 10px;
            margin-right: 10px;
        }
        
        .inline-form input[type="number"] {
            width: 80px;
            display: inline-block;
            margin-right: 5px;
        }
        
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            grid-gap: 20px;
            margin-top: 20px;
        }
        
        .sold-items-list {
            margin-top: 15px;
        }
        
        .sold-items-list li {
            background-color: white;
            padding: 10px 15px;
            margin-bottom: 10px;
            border-radius: var(--border-radius);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        /* Tabs Styling */
        .tabs {
            margin-bottom: 25px;
        }
        
        .tab-nav {
            display: flex;
            list-style: none;
            border-bottom: 2px solid var(--primary-light);
            padding-left: 0;
            margin-bottom: 20px;
            overflow-x: auto;
        }
        
        .tab-nav li {
            margin-right: 5px;
        }
        
        .tab-btn {
            padding: 12px 20px;
            background-color: white;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 4px 4px 0 0;
            cursor: pointer;
            font-weight: 500;
            color: var(--secondary);
            transition: all 0.3s ease;
        }
        
        .tab-btn:hover {
            background-color: var(--primary-light);
        }
        
        .tab-btn.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .tab-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Responsive Tabs */
        @media (max-width: 768px) {
            .tab-nav {
                flex-wrap: wrap;
            }
            
            .tab-nav li {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Admin Panel</h1>
        </div>
    </header>
    
    <div class="container">
        <div class="user-info">
            <h2>Welcome <?= htmlspecialchars($admin->name) ?></h2>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="tabs">
            <ul class="tab-nav">
                <li><button class="tab-btn active" data-tab="sales">Daily Sales</button></li>
                <li><button class="tab-btn" data-tab="inventory">Inventory</button></li>
                <li><button class="tab-btn" data-tab="food">Food Products</button></li>
                <li><button class="tab-btn" data-tab="delivery">Delivery Persons</button></li>
                <li><button class="tab-btn" data-tab="add-items">Add Items</button></li>
            </ul>

            <!-- Daily Sales Tab -->
            <div id="sales" class="tab-content active">
                <div class="card">
                    <div class="card-header">
                        <h3>Daily Sales Report</h3>
                    </div>
                    <div class="card-body">
                        <div class="sales-stats">
                            <div class="stat-card">
                                <h3>Total Sales</h3>
                                <p>$<?= number_format($salesReport['totalSales'], 2); ?></p>
                            </div>
                            <div class="stat-card">
                                <h3>Items Sold</h3>
                                <p><?= $salesReport['totalItemsSold']; ?></p>
                            </div>
                        </div>
                        
                        <h3 class="mt-4">Items Sold</h3>
                        <ul class="sold-items-list">
                            <?php if (!empty($salesReport['itemsSold'])): ?>
                                <?php foreach ($salesReport['itemsSold'] as $itemName => $details): ?>
                                    <li>
                                        <strong><?= htmlspecialchars($itemName); ?>:</strong>
                                        <?= $details['quantity']; ?> sold,
                                        Total: $<?= number_format($details['totalPrice'], 2); ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>No items sold yet.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Inventory Tab -->
            <div id="inventory" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3>Inventory</h3>
                    </div>
                    <div class="card-body">
                        <ul class="item-list">
                            <?php foreach ($inventory->listProducts() as $product): ?>
                                <li>
                                    <div>
                                        <strong><?= htmlspecialchars($product->name); ?></strong> - 
                                        $<?= number_format($product->price, 2); ?> 
                                        (Stock: <?= $product->stock; ?>)
                                    </div>
                                    <div>
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="product_id" value="<?= $product->id; ?>">
                                            <label>Add Stock:</label>
                                            <input type="number" name="additional_stock" required>
                                            <button type="submit" name="update_stock">Update</button>
                                        </form>
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="product_id" value="<?= $product->id; ?>">
                                            <button type="submit" name="remove_item">Remove</button>
                                        </form>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Food Products Tab -->
            <div id="food" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3>Food Products</h3>
                    </div>
                    <div class="card-body">
                        <div class="food-products">
                            <?php foreach ($foodProducts as $product): ?>
                                <div class="food-item">
                                    <div class="food-details">
                                        <h4><?= htmlspecialchars($product->name); ?> - $<?= number_format($product->price, 2); ?></h4>
                                        <p><strong>Category:</strong> <?= htmlspecialchars($product->category); ?></p>
                                        <p><strong>Description:</strong> <?= htmlspecialchars($product->description); ?></p>
                                    </div>
                                    <form method="POST">
                                        <input type="hidden" name="product_id" value="<?= $product->id; ?>">
                                        <button type="submit" name="remove_food_product">Remove</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Persons Tab -->
            <div id="delivery" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3>Delivery Persons</h3>
                    </div>
                    <div class="card-body">
                        <ul class="item-list">
                            <?php $deliveryPersons = loadDeliveryPersons(); ?>
                            <?php foreach ($deliveryPersons as $person): ?>
                                <li>
                                    <div>
                                        <strong><?= htmlspecialchars($person['name']); ?></strong> - 
                                        <?= htmlspecialchars($person['vehicleType']); ?>
                                    </div>
                                    <form method="POST">
                                        <input type="hidden" name="delivery_id" value="<?= $person['id']; ?>">
                                        <button type="submit" name="remove_delivery_person">Remove</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Add Items Tab -->
            <div id="add-items" class="tab-content">
                <div class="grid-container">
                    <div class="card">
                        <div class="card-header">
                            <h3>Add Item to Inventory</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div>
                                    <label>Item Name:</label>
                                    <input type="text" name="item_name" required>
                                </div>
                                <div>
                                    <label>Price:</label>
                                    <input type="number" step="0.01" name="item_price" required>
                                </div>
                                <div>
                                    <label>Stock:</label>
                                    <input type="number" name="item_stock" required>
                                </div>
                                <div>
                                    <button type="submit" name="add_item">Add Item</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3>Add Food Product</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div>
                                    <label>Food Name:</label>
                                    <input type="text" name="item_name" required>
                                </div>
                                <div>
                                    <label>Price:</label>
                                    <input type="number" step="0.01" name="item_price" required>
                                </div>
                                <div>
                                    <label>Category:</label>
                                    <input type="text" name="item_category" required>
                                </div>
                                <div>
                                    <label>Description:</label>
                                    <textarea name="item_description" required></textarea>
                                </div>
                                <div>
                                    <button type="submit" name="add_food_product">Add Food Product</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3>Add Delivery Person</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div>
                                    <label for="delivery_name">Name:</label>
                                    <input type="text" name="delivery_name" required>
                                </div>
                                <div>
                                    <label for="vehicle_type">Vehicle Type:</label>
                                    <input type="text" name="vehicle_type" required>
                                </div>
                                <div>
                                    <button type="submit" name="add_delivery_person">Add Delivery Person</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Get tab id
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to current button and content
                    this.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>