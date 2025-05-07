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
    } elseif (isset($_POST['assign_driver'])) {
        $driverName = $_POST['driver_name'];
        $employee->assignDeliveryDriver($index, $driverName);
    }

    header("Location: employee.php");
    exit;
}

// Fetch orders
$orders = $employee->getOrders();

// Initialize variables to prevent undefined variable errors
$hasPendingOrders = false;
$hasAssignedOrders = false;
$hasOutForDeliveryOrders = false;
$totalPendingAmount = 0;
$totalDeliveredAmount = 0;

// Check for order statuses
foreach ($orders as $order) {
    if ($order['deliveryStatus'] === 'Pending') {
        $hasPendingOrders = true;
        $totalPendingAmount += $order['totalPrice'] ?? 0;
    } elseif ($order['deliveryStatus'] === 'Assigned to Driver') {
        $hasAssignedOrders = true;
    } elseif ($order['deliveryStatus'] === 'Out for Delivery') {
        $hasOutForDeliveryOrders = true;
    } elseif ($order['deliveryStatus'] === 'Delivered') {
        $totalDeliveredAmount += $order['totalPrice'] ?? 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="employee.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                <i class="fas fa-store"></i> Employee Dashboard
            </div>
            <div class="user-info">
                <span class="user-name">
                    <i class="fas fa-user-circle"></i> 
                    <?php echo htmlspecialchars($employee->name); ?>
                </span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <div class="container main-content">
        <h1 class="page-title">Order Management</h1>
        
        <!-- Orders Summary -->
        <div class="dashboard-card">
            <div class="summary-section">
                <?php
                $pendingCount = 0;
                $assignedCount = 0;
                $outForDeliveryCount = 0;
                $deliveredCount = 0;
                
                foreach ($orders as $order) {
                    if ($order['deliveryStatus'] === 'Pending') $pendingCount++;
                    elseif ($order['deliveryStatus'] === 'Assigned to Driver') $assignedCount++;
                    elseif ($order['deliveryStatus'] === 'Out for Delivery') $outForDeliveryCount++;
                    elseif ($order['deliveryStatus'] === 'Delivered') $deliveredCount++;
                }
                ?>
                <div class="summary-item">
                    <div class="summary-value" style="color: var(--pending-color);"><?php echo $pendingCount; ?></div>
                    <div class="summary-label">Pending</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value" style="color: var(--assigned-color);"><?php echo $assignedCount; ?></div>
                    <div class="summary-label">Assigned</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value" style="color: var(--delivery-color);"><?php echo $outForDeliveryCount; ?></div>
                    <div class="summary-label">Out for Delivery</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value" style="color: var(--delivered-color);"><?php echo $deliveredCount; ?></div>
                    <div class="summary-label">Delivered</div>
                </div>
                <div class="summary-item">
                    <div class="summary-total">$<?php echo number_format($totalPendingAmount + $totalDeliveredAmount, 2); ?></div>
                    <div class="summary-label">Total Sales</div>
                </div>
            </div>
        </div>
        
        <!-- Tabs Navigation -->
        <div class="tabs-container">
            <div class="section-tab tab-pending" id="tab-pending">
                <i class="fas fa-clock"></i> Pending
                <span class="title-badge badge-pending"><?php echo $pendingCount; ?></span>
            </div>
            <div class="section-tab tab-assigned" id="tab-assigned">
                <i class="fas fa-user-check"></i> Assigned
                <span class="title-badge badge-assigned"><?php echo $assignedCount; ?></span>
            </div>
            <div class="section-tab tab-delivery" id="tab-delivery">
                <i class="fas fa-truck"></i> Out for Delivery
                <span class="title-badge badge-delivery"><?php echo $outForDeliveryCount; ?></span>
            </div>
            <div class="section-tab tab-delivered" id="tab-delivered">
                <i class="fas fa-check-circle"></i> Delivered
                <span class="title-badge badge-delivered"><?php echo $deliveredCount; ?></span>
            </div>
        </div>
        
        <!-- Pending Orders -->
        <div class="dashboard-card" id="section-pending">
            <h2 class="card-title title-pending">
                <i class="fas fa-clock"></i> Pending Orders
            </h2>
            
            <ul class="order-list">
                <?php if ($hasPendingOrders): ?>
                    <?php foreach ($orders as $index => $order): ?>
                        <?php if ($order['deliveryStatus'] === 'Pending'): ?>
                            <?php
                                // Calculate the total price of the order
                                $totalPrice = 0;
                                foreach ($order['items'] as $item) {
                                    $totalPrice += $item['product']['price'] * $item['quantity'];
                                }
                            ?>
                            <li class="order-item">
                                <div class="order-header">
                                    <div class="order-id">Order #<?php echo $order['orderId']; ?></div>
                                    <div class="order-status status-pending">
                                        Pending
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
                                                <i class="fas fa-credit-card"></i> Payment Method
                                            </div>
                                            <div class="detail-value">
                                                <?php echo htmlspecialchars($order['paymentMethod']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div class="detail-group">
                                            <div class="detail-label">
                                                <i class="fas fa-money-bill-wave"></i> Payment Status
                                            </div>
                                            <div class="detail-value" style="color: <?php echo $order['paymentStatus'] === 'Paid' ? 'green' : 'red'; ?>">
                                                <?php echo htmlspecialchars($order['paymentStatus']); ?>
                                            </div>
                                        </div>
                                        
                                        <div class="detail-group">
                                            <div class="detail-label">
                                                <i class="fas fa-tag"></i> Total Price
                                            </div>
                                            <div class="detail-value">
                                                $<?php echo number_format($totalPrice, 2); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="detail-label"><i class="fas fa-shopping-bag"></i> Order Items</div>
                                <ul class="items-list">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li class="item">
                                            <span><?php echo htmlspecialchars($item['product']['name']); ?></span>
                                            <span>
                                                Qty: <?php echo $item['quantity']; ?> - 
                                                $<?php echo number_format($item['product']['price'] * $item['quantity'], 2); ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <div class="action-buttons">
                                    <form method="POST" style="width: 100%;">
                                        <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                                        <div class="select-group">
                                            <select name="driver_name" required style="flex-grow: 1;">
                                                <option value="">Select Driver</option>
                                                <?php foreach ($deliveryDrivers as $driver): ?>
                                                    <option value="<?php echo htmlspecialchars($driver['name']); ?>">
                                                        <?php echo htmlspecialchars($driver['name']) . " - " . htmlspecialchars($driver['vehicleType']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="assign_driver" class="btn btn-primary">
                                                <i class="fas fa-user-check"></i> Assign Driver
                                            </button>
                                        </div>
                                    </form>
                                    
                                    <form method="POST" style="width: 100%;">
                                        <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                                        <button type="submit" name="delete_order" class="btn btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this order?');">
                                            <i class="fas fa-trash"></i> Delete Order
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <p>No pending orders at the moment.</p>
                    </div>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- Assigned Orders -->
        <div class="dashboard-card" id="section-assigned">
            <h2 class="card-title title-assigned">
                <i class="fas fa-user-check"></i> Assigned Orders
            </h2>
            
            <ul class="order-list">
                <?php if ($hasAssignedOrders): ?>
                    <?php foreach ($orders as $index => $order): ?>
                        <?php if ($order['deliveryStatus'] === 'Assigned to Driver'): ?>
                            <?php
                                // Calculate the total price of the order
                                $totalPrice = 0;
                                foreach ($order['items'] as $item) {
                                    $totalPrice += $item['product']['price'] * $item['quantity'];
                                }
                            ?>
                            <li class="order-item">
                                <div class="order-header">
                                    <div class="order-id">Order #<?php echo $order['orderId']; ?></div>
                                    <div class="order-status status-assigned">
                                        Assigned
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
                                                <i class="fas fa-credit-card"></i> Payment Method
                                            </div>
                                            <div class="detail-value">
                                                <?php echo htmlspecialchars($order['paymentMethod']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div class="detail-group">
                                            <div class="detail-label">
                                                <i class="fas fa-money-bill-wave"></i> Payment Status
                                            </div>
                                            <div class="detail-value" style="color: <?php echo $order['paymentStatus'] === 'Paid' ? 'green' : 'red'; ?>">
                                                <?php echo htmlspecialchars($order['paymentStatus']); ?>
                                            </div>
                                        </div>
                                        
                                        <div class="detail-group">
                                            <div class="detail-label">
                                                <i class="fas fa-tag"></i> Total Price
                                            </div>
                                            <div class="detail-value">
                                                $<?php echo number_format($totalPrice, 2); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div class="detail-group">
                                            <div class="detail-label">
                                                <i class="fas fa-truck"></i> Assigned Driver
                                            </div>
                                            <div class="detail-value">
                                                <?php echo htmlspecialchars($order['deliveryDriver']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="detail-label"><i class="fas fa-shopping-bag"></i> Order Items</div>
                                <ul class="items-list">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li class="item">
                                            <span><?php echo htmlspecialchars($item['product']['name']); ?></span>
                                            <span>Qty: <?php echo $item['quantity']; ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <div class="action-buttons">
                                    <form method="POST" style="width: 100%;">
                                        <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                                        <button type="submit" name="delete_order" class="btn btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this order?');">
                                            <i class="fas fa-trash"></i> Delete Order
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <p>No assigned orders at the moment.</p>
                    </div>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- Out for Delivery Orders -->
        <div class="dashboard-card" id="section-delivery">
            <h2 class="card-title title-delivery">
                <i class="fas fa-truck"></i> Out for Delivery Orders
            </h2>
            
            <ul class="order-list">
                <?php if ($hasOutForDeliveryOrders): ?>
                    <?php foreach ($orders as $index => $order): ?>
                        <?php if ($order['deliveryStatus'] === 'Out for Delivery'): ?>
                            <?php
                                // Calculate the total price of the order
                                $totalPrice = 0;
                                foreach ($order['items'] as $item) {
                                    $totalPrice += $item['product']['price'] * $item['quantity'];
                                }
                            ?>
                            <li class="order-item">
                                <div class="order-header">
                                    <div class="order-id">Order #<?php echo $order['orderId']; ?></div>
                                    <div class="order-status status-out-for-delivery">
                                        Out for Delivery
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
                                                <i class="fas fa-credit-card"></i> Payment Method
                                            </div>
                                            <div class="detail-value">
                                                <?php echo htmlspecialchars($order['paymentMethod']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div class="detail-group">
                                            <div class="detail-label">
                                                <i class="fas fa-money-bill-wave"></i> Payment Status
                                            </div>
                                            <div class="detail-value" style="color: <?php echo $order['paymentStatus'] === 'Paid' ? 'green' : 'red'; ?>">
                                                <?php echo htmlspecialchars($order['paymentStatus']); ?>
                                            </div>
                                        </div>
                                        
                                        <div class="detail-group">
                                            <div class="detail-label">
                                                <i class="fas fa-tag"></i> Total Price
                                            </div>
                                            <div class="detail-value">
                                                $<?php echo number_format($totalPrice, 2); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="detail-label"><i class="fas fa-shopping-bag"></i> Order Items</div>
                                <ul class="items-list">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li class="item">
                                            <span><?php echo htmlspecialchars($item['product']['name']); ?></span>
                                            <span>Qty: <?php echo $item['quantity']; ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <div class="action-buttons">
                                    <form method="POST" style="width: 100%;">
                                        <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                                        <button type="submit" name="delete_order" class="btn btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this order?');">
                                            <i class="fas fa-trash"></i> Delete Order
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <p>No orders out for delivery at the moment.</p>
                    </div>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- Delivered Orders -->
        <div class="dashboard-card" id="section-delivered">
            <h2 class="card-title title-delivered">
                <i class="fas fa-check-circle"></i> Delivered Orders
            </h2>
            
            <ul class="order-list">
                <?php if ($deliveredCount > 0): ?>
                    <?php foreach ($orders as $index => $order): ?>
                        <?php if ($order['deliveryStatus'] === 'Delivered'): ?>
                            <?php
                                // Calculate the total price of the order
                                $totalPrice = 0;
                                foreach ($order['items'] as $item) {
                                    $totalPrice += $item['product']['price'] * $item['quantity'];
                                }
                            ?>
                            <li class="order-item">
                                <div class="order-header">
                                    <div class="order-id">Order #<?php echo $order['orderId']; ?></div>
                                    <div class="order-status status-delivered">
                                        Delivered
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
                                                <i class="fas fa-credit-card"></i> Payment Method
                                            </div>
                                            <div class="detail-value">
                                                <?php echo htmlspecialchars($order['paymentMethod']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div class="detail-group">
                                            <div class="detail-label">
                                                <i class="fas fa-money-bill-wave"></i> Payment Status
                                            </div>
                                            <div class="detail-value" style="color: <?php echo $order['paymentStatus'] === 'Paid' ? 'green' : 'red'; ?>">
                                                <?php echo htmlspecialchars($order['paymentStatus']); ?>
                                            </div>
                                        </div>
                                        
                                        <div class="detail-group">
                                            <div class="detail-label">
                                                <i class="fas fa-tag"></i> Total Price
                                            </div>
                                            <div class="detail-value">
                                                $<?php echo number_format($totalPrice, 2); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="detail-label"><i class="fas fa-shopping-bag"></i> Order Items</div>
                                <ul class="items-list">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li class="item">
                                            <span><?php echo htmlspecialchars($item['product']['name']); ?></span>
                                            <span>Qty: <?php echo $item['quantity']; ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <div class="action-buttons">
                                    <form method="POST" style="width: 100%;">
                                        <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                                        <button type="submit" name="delete_order" class="btn btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this order?');">
                                            <i class="fas fa-trash"></i> Delete Order
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <p>No delivered orders at the moment.</p>
                    </div>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html></div>