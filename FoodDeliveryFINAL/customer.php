<?php
require_once 'functions.php';
require_once 'classes/Customer.php';

session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']->isCustomer()) {
    header("Location: index.php");
    exit;
}

// Cast the session user as a Customer object
$user = new Customer($_SESSION['user']->id, $_SESSION['user']->name);

$inventory = loadInventory();
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$orders = loadOrders();
$foodProducts = loadFoodProducts();

if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $isFoodProduct = isset($_POST['is_food_product']); // Determine if it's a food product

    $product = null;
    if ($isFoodProduct) {
        foreach (loadFoodProducts() as $foodProduct) {
            if ($foodProduct->id === $productId) {
                $product = $foodProduct;
                break;
            }
        }
    } else {
        $product = loadInventory()->getProduct($productId);
    }

    if ($product) {
        $user->addToCart($cart, $product, $quantity);
        $_SESSION['cart'] = $cart;
    } else {
        error_log("Product with ID $productId not found.");
    }

    header("Location: customer.php");
    exit;
}

if (isset($_POST['remove_from_cart'])) {
    $index = $_POST['cart_index'];
    $user->removeFromCart($cart, $index);
    $_SESSION['cart'] = $cart;
    header("Location: customer.php");
    exit;
}

if (isset($_POST['place_order'])) {
    $paymentMethod = $_POST['payment_method'];
    $paymentStatus = $paymentMethod === 'online_payment' ? 'Paid' : 'Not Paid';

    $user->placeOrder($orders, $cart, $inventory, $paymentStatus, $paymentMethod);
    $_SESSION['cart'] = $cart;
    header("Location: customer.php");
    exit;
}

if (isset($_POST['submit_review'])) {
    $index = $_POST['order_index'];
    $review = $_POST['review'];
    $rating = $_POST['rating'];
    $user->submitReview($orders, $index, $review, $rating);
    header("Location: customer.php");
    exit;
}

// Calculate cart total
$cartTotal = 0;
foreach ($cart as $item) {
    $cartTotal += $item['product']->price * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title >Food Ordering System</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</head>
<body>
    <nav class="navbar"></nav>
    <!-- Header Section -->
    <header class="header">
        <div class="container header-content">
            <h1 style="text-align: center;">Food Ordering System</h1>
            <div class="user-info">
                <h2 style="text-align: center;">Welcome, <?= $user->name ?></h2>
                <a href="logout.php" style="margin-left: 48%;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Food Menu Section -->
        <section class="section">
            <h2 class="sectiontitle"><i class="fas fa-utensils"></i> Food Menu</h2>
            <div class="menu-container">
                <?php foreach ($foodProducts as $foodProduct): ?>
                    <div class="menu-item">
                        <div class="menu-item-content">
                            <div class="menu-item-header">
                                <span class="menu-item-name"><?= $foodProduct->name ?></span>
                                <span class="menu-item-price">$<?= number_format($foodProduct->price, 2) ?></span>
                            </div>
                            <div class="menu-item-details">
                                <span class="menu-item-category"><?= $foodProduct->category ?></span>
                            </div>
                            <p class="menu-item-description"><?= $foodProduct->description ?></p>
                            <form method="POST" class="quantity-form">
                                <input type="hidden" name="product_id" value="<?= $foodProduct->id ?>">
                                <input type="hidden" name="is_food_product" value="1">
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn decrement">-</button>
                                    <input type="number" name="quantity" class="quantity-input" value="1" min="1" required>
                                    <button type="button" class="quantity-btn increment">+</button>
                                </div>
                                <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Inventory Section -->
        <section class="section">
            <h2 class="sectiontitle"><i class="fas fa-box-open"></i> Products</h2>
            <div class="menu-container">
                <?php foreach ($inventory->listProducts() as $product): ?>
                    <div class="menu-item">
                        <div class="menu-item-content">
                            <div class="menu-item-header">
                                <span class="menu-item-name"><?= $product->name ?></span>
                                <span class="menu-item-price">$<?= number_format($product->price, 2) ?></span>
                            </div>
                            <div class="menu-item-details">
                                <span class="menu-item-stock"><i class="fas fa-layer-group"></i> In Stock: <?= $product->stock ?></span>
                            </div>
                            <form method="POST" class="quantity-form">
                                <input type="hidden" name="product_id" value="<?= $product->id ?>">
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn decrement">-</button>
                                    <input type="number" name="quantity" class="quantity-input" value="1" min="1" max="<?= $product->stock ?>" required>
                                    <button type="button" class="quantity-btn increment">+</button>
                                </div>
                                <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Cart Section -->
        <section class="section cart-section">
            <h2 class="sectiontitle"><i class="fas fa-shopping-cart"></i> Shopping Cart</h2>
            <?php if (empty($cart)): ?>
                <p class="cartempty">Your cart is empty.</p>
            <?php else: ?>
                <ul class="cartlist">
                    <?php foreach ($cart as $index => $item): 
                        $itemTotal = $item['product']->price * $item['quantity'];
                    ?>
                        <li class="cart-item">
                            <div class="con">
                                <div class="cart-item-info">
                                    <strong><?= $item['product']->name ?></strong> - 
                                    Qty: <?= $item['quantity'] ?> x $<?= number_format($item['product']->price, 2) ?> = 
                                    $<?= number_format($itemTotal, 2) ?>
                                </div>
                                <div class="cart-item-actions">
                                    <form method="POST">
                                        <input type="hidden" name="cart_index" value="<?= $index ?>">
                                        <button type="submit" name="remove_from_cart" class="removebtn">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="cart-total">
                    <strong>Total Amount:</strong> $<?= number_format($cartTotal, 2) ?>
                </div>
                <form method="POST" class="checkoutform">
                    <select name="payment_method" id="payment_method" class="payment-method" required>
                        <option value="cash_on_delivery">Cash on Delivery</option>
                        <option value="online_payment">Online Payment</option>
                    </select>
                    <button type="submit" name="place_order" class="place-order-btn">
                        <i class="fas fa-check-circle"></i> Place Order
                    </button>
                </form>
            <?php endif; ?>
        </section>

        <!-- Orders Section -->
        <section class="section order-section">
            <h2 class="sectiontitle"><i class="fas fa-history"></i> My Orders</h2>
            <?php
            $userOrders = array_filter($orders, function($order) use ($user) {
                return $order['username'] === $user->name;
            });
            
            if (empty($userOrders)):
            ?>
                <p class="orders">You haven't placed any orders yet.</p>
            <?php else: ?>
                <ul class="orders-list">
                    <?php foreach ($orders as $index => $order): ?>
                        <?php if ($order['username'] === $user->name): 
                            // Calculate the total amount for the order
                            $orderTotal = 0;
                            foreach ($order['items'] as $item) {
                                $orderTotal += $item['product']->price * $item['quantity'];
                            }
                        ?>
                            <li class="order-item">
                                <div class="order-header">
                                    <div>
                                        <strong>Order ID:</strong> <?= $order['orderId'] ?>
                                    </div>
                                    <div>
                                        <span class="status-<?= strtolower($order['deliveryStatus']) ?>">
                                            <?php if ($order['deliveryStatus'] === 'Delivered'): ?>
                                                <i class="fas fa-check-circle"></i>
                                            <?php elseif ($order['deliveryStatus'] === 'Pending'): ?>
                                                <i class="fas fa-clock"></i>
                                            <?php else: ?>
                                                <i class="fas fa-times-circle"></i>
                                            <?php endif; ?>
                                            <?= $order['deliveryStatus'] ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="totam">
                                    <strong>Total Amount:</strong> $<?= number_format($orderTotal, 2) ?>
                                    <strong>Payment Method:</strong> <?= ucwords(str_replace('_', ' ', $order['paymentMethod'])) ?>
                                    <strong>Payment Status:</strong> <?= $order['paymentStatus'] ?>
                                </div>
                                <h4>Order Items:</h4>
                                <ul class="order-items">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li>
                                            <?= $item['product']->name ?> - 
                                            Qty: <?= $item['quantity'] ?> x $<?= number_format($item['product']->price, 2) ?> = 
                                            $<?= number_format($item['product']->price * $item['quantity'], 2) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <?php if ($order['deliveryStatus'] === 'Delivered'): ?>
                                    <?php if (isset($order['review']) && isset($order['rating'])): ?>
                                        <div class="review-display">
                                            <p><strong>My Rating:</strong> 
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $order['rating']): ?>
                                                        <i class="fas fa-star" style="color: gold;"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </p>
                                            <p><strong>My Review:</strong> <?= $order['review'] ?></p>
                                        </div>
                                    <?php else: ?>
                                        <form method="POST" class="review-form">
                                            <h4><i class="fas fa-comment"></i> Add Review</h4>
                                            <input type="hidden" name="order_index" value="<?= $index ?>">
                                            <textarea name="review" placeholder="Write your review here..." required></textarea>
                                            <div>
                                            <label for="rating">Rating:</label>
                                                <select name="rating" id="rating" required>
                                                    <option value="">Select</option>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <option value="<?= $i ?>"><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <button type="submit" name="submit_review" class="submit-review-btn">
                                                <i class="fas fa-paper-plane"></i> Submit Review
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.quantity-form').forEach(form => {
                const dec = form.querySelector('.decrement');
                const inc = form.querySelector('.increment');
                const input = form.querySelector('.quantity-input');

                dec.addEventListener('click', () => {
                    const val = parseInt(input.value, 10);
                    if (val > 1) input.value = val - 1;
                });

                inc.addEventListener('click', () => {
                    const val = parseInt(input.value, 10);
                    input.value = val + 1;
                });
            });
        });
    </script>
</body>
</html>


