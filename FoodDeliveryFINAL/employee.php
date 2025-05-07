<?php
require_once 'functions.php';
require_once 'classes/Employee.php';
session_start();

// Security check
if (!isset($_SESSION['user']) || !$_SESSION['user']->isEmployee()) {
    header("Location: index.php");
    exit;
}

// Cast the session user as an Employee object
$employee = new Employee($_SESSION['user']->id, $_SESSION['user']->name);

// Load delivery drivers
$deliveryDrivers = loadDeliveryPersons();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $index = $_POST['order_index'] ?? null;

    if (isset($_POST['delete_order'])) {
        $employee->deleteOrder($index);
        header("Location: employee.php");
        exit;
    } elseif (isset($_POST['assign_driver'])) {
        $driverName = $_POST['driver_name'];
        $employee->assignDeliveryDriver($index, $driverName);
    }

    header("Location: employee.php");
    exit;
}

$orders = $employee->getOrders();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Panel</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Employee Panel</h1>
    <a href="logout.php">Logout</a>
    <h3>Manage Orders</h3>

    <!-- Pending Orders -->
    <h4 style="color: orange;">Pending Orders</h4>
    <ul>
        <?php 
        $hasPendingOrders = false;
        $totalPendingAmount = 0;
        foreach ($orders as $index => $order): 
            if ($order['deliveryStatus'] === 'Pending'): 
                $hasPendingOrders = true;

                // Calculate the total price of the order
                $totalPrice = 0;
                foreach ($order['items'] as $item) {
                    $totalPrice += $item['product']['price'] * $item['quantity'];
                }
                $totalPendingAmount += $totalPrice;
        ?>
            <li>
                <strong>Order ID:</strong> <?php echo $order['orderId']; ?> <br>
                <strong>Customer:</strong> <?php echo $order['username']; ?> <br>
                <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['paymentMethod']); ?> <br>
                <strong>Payment Status:</strong> <?php echo htmlspecialchars($order['paymentStatus']); ?> <br>
                <strong>Delivery Status:</strong> <?php echo htmlspecialchars($order['deliveryStatus']); ?> <br>
                <strong>Total Price:</strong> $<?php echo number_format($totalPrice, 2); ?> <br>
                <strong>Items:</strong>
                <ul>
                    <?php foreach ($order['items'] as $item): ?>
                        <li>
                            <?php echo "{$item['product']['name']} - Qty: {$item['quantity']} - $" . number_format($item['product']['price'] * $item['quantity'], 2); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <form method="POST">
                    <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                    <label for="driver_name">Assign Delivery Driver:</label>
                    <select name="driver_name" required>
                        <option value="">Select Driver</option>
                        <?php foreach ($deliveryDrivers as $driver): ?>
                            <option value="<?php echo htmlspecialchars($driver['name']); ?>">
                                <?php echo htmlspecialchars($driver['name']) . " - " . htmlspecialchars($driver['vehicleType']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="assign_driver">Assign Driver</button>
                </form>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                    <button type="submit" name="delete_order" onclick="return confirm('Are you sure you want to delete this order?');">Delete Order</button>
                </form>
            </li>
            <br>
        <?php 
            endif; 
        endforeach; 
        ?>
        <?php if (!$hasPendingOrders): ?>
            <li>No Pending Orders</li>
        <?php endif; ?>
    </ul>

    <!-- Assigned Orders -->
    <h4 style="color: purple;">Assigned Orders</h4>
    <ul>
        <?php 
        $hasAssignedOrders = false;
        foreach ($orders as $index => $order): 
            // Ensure the condition matches the exact value in orders.json
            if ($order['deliveryStatus'] === 'Assigned to Driver'): 
                $hasAssignedOrders = true;

                $totalPrice = 0;
                foreach ($order['items'] as $item) {
                    $totalPrice += $item['product']['price'] * $item['quantity'];
                }
                $totalPendingAmount += $totalPrice;
        ?>
            <li>
                <strong>Order ID:</strong> <?php echo $order['orderId']; ?> <br>
                <strong>Customer:</strong> <?php echo $order['username']; ?> <br>
                <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['paymentMethod']); ?> <br>
                <strong>Payment Status:</strong> <?php echo htmlspecialchars($order['paymentStatus']); ?> <br>
                <strong>Delivery Status:</strong> <?php echo htmlspecialchars($order['deliveryStatus']); ?> <br>
                <strong>Total Price:</strong> $<?php echo number_format($totalPrice, 2); ?> <br>
                <strong>Items:</strong>
                <ul>
                    <?php foreach ($order['items'] as $item): ?>
                        <li>
                            <?php echo "{$item['product']['name']} - Qty: {$item['quantity']}"; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <strong>Assigned Driver:</strong> <?php echo htmlspecialchars($order['deliveryDriver']); ?> <br>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                    <button type="submit" name="delete_order" onclick="return confirm('Are you sure you want to delete this order?');">Delete Order</button>
                </form>
            </li>
            <br>
        <?php 
            endif; 
        endforeach; 
        ?>
        <?php if (!$hasAssignedOrders): ?>
            <li>No Assigned Orders</li>
        <?php endif; ?>
    </ul>

    <!-- Out for Delivery Orders -->
    <h4 style="color: blue;">Out for Delivery Orders</h4>
    <ul>
        <?php 
        $hasOutForDeliveryOrders = false;
        foreach ($orders as $index => $order): 
            if ($order['deliveryStatus'] === 'Out for Delivery'): 
                $hasOutForDeliveryOrders = true;

                $totalPrice = 0;
                foreach ($order['items'] as $item) {
                    $totalPrice += $item['product']['price'] * $item['quantity'];
                }
                $totalPendingAmount += $totalPrice;
        ?>
            <li>
                <strong>Order ID:</strong> <?php echo $order['orderId']; ?> <br>
                <strong>Customer:</strong> <?php echo $order['username']; ?> <br>
                <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['paymentMethod']); ?> <br>
                <strong>Payment Status:</strong> <?php echo htmlspecialchars($order['paymentStatus']); ?> <br>
                <strong>Delivery Status:</strong> <?php echo htmlspecialchars($order['deliveryStatus']); ?> <br>
                <strong>Total Price:</strong> $<?php echo number_format($totalPrice, 2); ?> <br>
                <strong>Items:</strong>
                <ul>
                    <?php foreach ($order['items'] as $item): ?>
                        <li>
                            <?php echo "{$item['product']['name']} - Qty: {$item['quantity']}"; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                    <button type="submit" name="delete_order" onclick="return confirm('Are you sure you want to delete this order?');">Delete Order</button>
                </form>
            </li>
            <br>
        <?php 
            endif; 
        endforeach; 
        ?>
        <?php if (!$hasOutForDeliveryOrders): ?>
            <li>No Out for Delivery Orders</li>
        <?php endif; ?>
    </ul>

    <!-- Delivered Orders -->
    <h4 style="color: green;">Delivered Orders</h4>
    <ul>
        <?php 
        $hasDeliveredOrders = false;
        $totalDeliveredAmount = 0;
        foreach ($orders as $index => $order): 
            if ($order['deliveryStatus'] === 'Delivered'): 
                $hasDeliveredOrders = true;

                // Calculate the total price of the order
                $totalPrice = 0;
                foreach ($order['items'] as $item) {
                    $totalPrice += $item['product']['price'] * $item['quantity'];
                }
                $totalDeliveredAmount += $totalPrice;
        ?>
            <li>
                <strong>Order ID:</strong> <?php echo $order['orderId']; ?> <br>
                <strong>Customer:</strong> <?php echo $order['username']; ?> <br>
                <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['paymentMethod']); ?> <br>
                <strong>Payment Status:</strong> <?php echo htmlspecialchars($order['paymentStatus']); ?> <br>
                <strong>Delivery Status:</strong> <?php echo htmlspecialchars($order['deliveryStatus']); ?> <br>
                <strong>Total Price:</strong> $<?php echo number_format($totalPrice, 2); ?> <br>
                <strong>Items:</strong>
                <ul>
                    <?php foreach ($order['items'] as $item): ?>
                        <li>
                            <?php echo "{$item['product']['name']} - Qty: {$item['quantity']}"; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (isset($order['review']) && isset($order['rating'])): ?>
                    <p><strong>Customer Review:</strong> <?php echo htmlspecialchars($order['review']); ?></p>
                    <p><strong>Customer Rating:</strong> <?php echo htmlspecialchars($order['rating']); ?> / 5</p>
                <?php else: ?>
                    <p><strong>Customer Review:</strong> No review provided.</p>
                <?php endif; ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                    <button type="submit" name="delete_order" onclick="return confirm('Are you sure you want to delete this order?');">Delete Order</button>
                </form>
            </li>
            <br>
        <?php 
            endif; 
        endforeach; 
        ?>
        <?php if (!$hasDeliveredOrders): ?>
            <li>No Delivered Orders</li>
        <?php endif; ?>
    </ul>

</body>
</html>