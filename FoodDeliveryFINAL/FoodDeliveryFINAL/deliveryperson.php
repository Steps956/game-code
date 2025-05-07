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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="delivery.css">
    
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                <i class="fas fa-truck"></i> Delivery Dashboard
            </div>
            <div class="user-info">
                <span class="user-name">
                    <i class="fas fa-user-circle"></i> 
                    <?php echo htmlspecialchars($deliveryPerson->name); ?>
                </span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <div class="container main-content">
        <h1 class="page-title">Your Delivery Dashboard</h1>
        
        <div class="dashboard-card">
            <h2 class="card-title">
                <i class="fas fa-clipboard-list"></i> Assigned Orders
            </h2>
            
            <ul class="order-list">
                <?php if (!empty($assignedOrders)): ?>
                    <?php foreach ($assignedOrders as $index => $order): ?>
                        <li class="order-item">
                            <div class="order-header">
                                <div class="order-id">Order #<?php echo $order['orderId']; ?></div>
                                <?php 
                                    $statusClass = '';
                                    switch($order['deliveryStatus']) {
                                        case 'Pending':
                                            $statusClass = 'status-pending';
                                            break;
                                        case 'Out for Delivery':
                                            $statusClass = 'status-out-for-delivery';
                                            break;
                                        case 'Delivered':
                                            $statusClass = 'status-delivered';
                                            break;
                                    }
                                ?>
                                <div class="order-status <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($order['deliveryStatus']); ?>
                                </div>
                            </div>
                            
                            <div class="order-details">
                                <div>
                                    <div class="detail-group">
                                        <div class="detail-label">
                                            <i class="fas fa-user"></i> Customer
                                        </div>
                                        <div class="detail-value">
                                            <?php echo htmlspecialchars($order['username']); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-group">
                                        <div class="detail-label">
                                            <i class="fas fa-phone"></i> Contact Number
                                        </div>
                                        <div class="detail-value">
                                            <?php echo htmlspecialchars($order['phoneNumber']); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="detail-group">
                                        <div class="detail-label">
                                            <i class="fas fa-credit-card"></i> Payment Method
                                        </div>
                                        <div class="detail-value">
                                            <?php echo htmlspecialchars($order['paymentMethod']); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-group">
                                        <div class="detail-label">
                                            <i class="fas fa-money-bill-wave"></i> Payment Status
                                        </div>
                                        <div class="detail-value" style="color: <?php echo $order['paymentStatus'] === 'Paid' ? 'green' : 'red'; ?>">
                                            <?php echo htmlspecialchars($order['paymentStatus']); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="detail-group">
                                        <div class="detail-label">
                                            <i class="fas fa-map-marker-alt"></i> Delivery Address
                                        </div>
                                        <div class="detail-value">
                                            <?php echo htmlspecialchars($order['address']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="items-section">
                                <div class="detail-label"><i class="fas fa-shopping-bag"></i> Order Items</div>
                                <ul class="items-list">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li class="item">
                                            <span><?php echo htmlspecialchars($item['product']['name']); ?></span>
                                            <span>Qty: <?php echo $item['quantity']; ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <div class="action-buttons">
                                <?php if ($order['deliveryStatus'] !== 'Delivered'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                                        <?php if ($order['deliveryStatus'] === 'Pending'): ?>
                                            <button type="submit" name="set_out_for_delivery" class="btn btn-primary">
                                                <i class="fas fa-truck"></i> Set Out for Delivery
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($order['deliveryStatus'] !== 'Delivered'): ?>
                                            <button type="submit" name="set_delivered" class="btn btn-success">
                                                <i class="fas fa-check-circle"></i> Set Delivered
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($order['paymentStatus'] === 'Not Paid'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                                        <button type="submit" name="mark_as_paid" class="btn btn-warning">
                                            <i class="fas fa-dollar-sign"></i> Mark as Paid
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <p>No orders assigned to you yet.</p>
                        <p>New orders will appear here when they are assigned.</p>
                    </div>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <script>
        // Add a small interaction for better UX
        document.addEventListener('DOMContentLoaded', function() {
            const orderItems = document.querySelectorAll('.order-item');
            orderItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Prevent clicking buttons from triggering this
                    if (e.target.tagName !== 'BUTTON' && !e.target.closest('button')) {
                        this.classList.toggle('active');
                    }
                });
            });
        });
    </script>
</body>
</html>