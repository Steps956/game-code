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
    <title>Admin Panel</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Admin Panel</h1>
    <hr><br>
    <h2>Welcome <?= htmlspecialchars($admin->name) ?></h2>
    <a href="logout.php">Logout</a><br>

    <br><hr>

    <h3>Daily Sales Report</h3>
    <p><strong>Total Sales:</strong> $<?= number_format($salesReport['totalSales'], 2); ?></p>
    <p><strong>Total Items Sold:</strong> <?= $salesReport['totalItemsSold']; ?></p>

    <h3>Items Sold</h3>
    <ul>
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

    <br><hr><br>

    <h3>Add Item to Inventory</h3>
    <form method="POST">
        <label>Item Name:</label>
        <input type="text" name="item_name" required>
        <label>Price:</label>
        <input type="number" step="0.01" name="item_price" required>
        <label>Stock:</label>
        <input type="number" name="item_stock" required>
        <button type="submit" name="add_item">Add Item</button>
    </form>

    <h3>Inventory</h3>
    <ul>
        <?php foreach ($inventory->listProducts() as $product): ?>
            <li>
                <?= htmlspecialchars($product->name); ?> - $<?= number_format($product->price, 2); ?> (Stock: <?= $product->stock; ?>)
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="product_id" value="<?= $product->id; ?>">
                    <label>Add Stock:</label>
                    <input type="number" name="additional_stock" required>
                    <button type="submit" name="update_stock">Update</button>
                </form>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="product_id" value="<?= $product->id; ?>">
                    <button type="submit" name="remove_item">Remove</button>
                </form>
            </li>
            <br>
        <?php endforeach; ?>
    </ul>

    <h3>Add Food Product</h3>
    <form method="POST">
        <label>Food Name:</label>
        <input type="text" name="item_name" required>
        <label>Price:</label>
        <input type="number" step="0.01" name="item_price" required>
        <label>Category:</label>
        <input type="text" name="item_category" required>
        <label>Description:</label>
        <textarea name="item_description" required></textarea>
        <button type="submit" name="add_food_product">Add Food Product</button>
    </form>

    <h3>Food Products</h3>
    <ul>
        <?php foreach ($foodProducts as $product): ?>
            <li>
                <strong><?= htmlspecialchars($product->name); ?></strong> - $<?= number_format($product->price, 2); ?><br>
                <em>Category:</em> <?= htmlspecialchars($product->category); ?><br>
                <em>Description:</em> <?= htmlspecialchars($product->description); ?><br>
                <form method="POST">
                    <input type="hidden" name="product_id" value="<?= $product->id; ?>">
                    <button type="submit" name="remove_food_product">Remove</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <h3>Add Delivery Person</h3>
    <form method="POST">
        <label for="delivery_name">Name:</label>
        <input type="text" name="delivery_name" required>
        <label for="vehicle_type">Vehicle Type:</label>
        <input type="text" name="vehicle_type" required>
        <button type="submit" name="add_delivery_person">Add Delivery Person</button>
    </form>

    <h3>Delivery Persons</h3>
    <ul>
        <?php $deliveryPersons = loadDeliveryPersons(); ?>
        <?php foreach ($deliveryPersons as $person): ?>
            <li>
                <strong><?= htmlspecialchars($person['name']); ?></strong> - <?= htmlspecialchars($person['vehicleType']); ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="delivery_id" value="<?= $person['id']; ?>">
                    <button type="submit" name="remove_delivery_person">Remove</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
