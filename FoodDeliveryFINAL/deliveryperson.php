<?php
// filepath: c:\xampp\htdocs\tamayo\FinalNagidNi\deliveryperson.php

require_once 'functions.php';
require_once 'classes/DeliveryPerson.php';
session_start();

// Security check: Ensure the user is a delivery person
if (!isset($_SESSION['user']) || $_SESSION['user']->role !== 'delivery_person') {
    header("Location: index.php");
    exit;
}

// Re-initialize the DeliveryPerson object
$deliveryPerson = new DeliveryPerson(
    $_SESSION['user']->id,
    $_SESSION['user']->name,
    $_SESSION['user']->vehicleType ?? 'Unknown' // Default to 'Unknown' if vehicleType is not set
);

// Load orders and filter those assigned to the logged-in delivery person
$orders = $deliveryPerson->getOrders();
$assignedOrders = array_filter($orders, function ($order) use ($deliveryPerson) {
    return isset($order['deliveryDriver']) && $order['deliveryDriver'] === $deliveryPerson->name;
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $index = $_POST['order_index'] ?? null;

    if (isset($_POST['set_out_for_delivery'])) {
        $deliveryPerson->setOrderStatusToOutForDelivery($index);
    } elseif (isset($_POST['set_delivered'])) {
        $deliveryPerson->setOrderStatusToDelivered($index);
    } elseif (isset($_POST['mark_as_paid'])) {
        $orders = $deliveryPerson->getOrders();
        if (isset($orders[$index]) && $orders[$index]['paymentStatus'] === 'Not Paid') {
            $orders[$index]['paymentStatus'] = 'Paid';
            saveOrders($orders); // still okay here if saveOrders() is a global helper
        }
    }

    header("Location: deliveryperson.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Person Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($deliveryPerson->name); ?>!</h1>
    <a href="logout.php">Logout</a>
    <h2>Your Assigned Orders</h2>

    <ul>
        <?php if (!empty($assignedOrders)): ?>
            <?php foreach ($assignedOrders as $index => $order): ?>
                <li>
                    <strong>Order ID:</strong> <?php echo $order['orderId']; ?><br>
                    <strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?><br>
                    <strong>Contact Number:</strong> <?= htmlspecialchars($order['phoneNumber']); ?><br>
                    <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['paymentMethod']); ?><br>
                    <strong>Payment Status:</strong> <?php echo htmlspecialchars($order['paymentStatus']); ?><br>
                    <strong>Delivery Address:</strong> <?= htmlspecialchars($order['address']); ?><br>
                    <strong>Delivery Status:</strong> <?php echo htmlspecialchars($order['deliveryStatus']); ?><br>
                    <strong>Items:</strong>
                    <ul>
                        <?php foreach ($order['items'] as $item): ?>
                            <li>
                                <?php echo htmlspecialchars($item['product']['name']) . " - Qty: " . $item['quantity']; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <form method="POST">
                        <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                        <button type="submit" name="set_out_for_delivery">Set Out for Delivery</button>
                        <button type="submit" name="set_delivered">Set Delivered</button>
                    </form>
                    <?php if ($order['paymentStatus'] === 'Not Paid'): ?>
                        <form method="POST">
                            <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                            <button type="submit" name="mark_as_paid">Mark as Paid</button>
                        </form>
                    <?php endif; ?>
                </li>
                <br>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No orders assigned to you.</li>
        <?php endif; ?>
    </ul>
</body>
</html>