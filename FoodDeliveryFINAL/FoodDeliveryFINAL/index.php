<?php
require_once 'functions.php';
require_once 'classes/User.php';
require_once 'classes/DeliveryPerson.php';

session_start();

if (isset($_SESSION['user'])) {
    if ($_SESSION['user']->isAdmin()) {
        header("Location: admin.php");
    } elseif ($_SESSION['user']->isEmployee()) {
        header("Location: employee.php");
    } elseif ($_SESSION['user']->isCustomer()) {
        header("Location: customer.php");
    } elseif ($_SESSION['user']->isDeliveryPerson()) {
        header("Location: deliveryperson.php");
    }
    exit;
}

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $role = $_POST['role'];

    $users = loadUsers();
    $existingUser = null;

    foreach ($users as $user) {
        if ($user['username'] === $username) {
            $existingUser = $user;
            break;
        }
    }

    if ($existingUser) {
        if ($existingUser['role'] === 'delivery_person') {
            $_SESSION['user'] = new DeliveryPerson(
                $existingUser['userId'],
                $existingUser['username'],
                $existingUser['vehicleType']
            );
        } else {
            $_SESSION['user'] = new User(
                $existingUser['userId'],
                $existingUser['username'],
                $existingUser['role']
            );
        }
    } else {
        $newUser = [
            'userId' => uniqid(),
            'username' => $username,
            'role' => $role,
        ];

        if ($role === 'delivery_person') {
            $newUser['vehicleType'] = 'Unknown'; // Default vehicle type
        }

        $users[] = $newUser;
        saveUsers($users);

        if ($role === 'delivery_person') {
            $_SESSION['user'] = new DeliveryPerson(
                $newUser['userId'],
                $newUser['username'],
                $newUser['vehicleType']
            );
        } else {
            $_SESSION['user'] = new User(
                $newUser['userId'],
                $newUser['username'],
                $newUser['role']
            );
        }
    }

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Food Ordering System - Login</title>
  <link rel="stylesheet" href="style.css" >
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</head>
<body>
  <nav class="navbar"></nav>
  <div class="login-page">
    <div class="login-container">
      <div class="login-header">
        <i class="fas fa-utensils login-icon"></i>
        <h1>Food Ordering System</h1>
      </div>
      
      <div class="login-card">
        <form class="login-form" method="POST">
          <h2></i> Log In</h2>
          <p class="login-subtitle">Enter your details to continue</p>
          
          <div class="form-group">
            <label for="username">
              <i class="fas fa-user"></i>
              Username:
            </label>
            <input 
              type="text" 
              name="username" 
              id="username"
              placeholder="Enter your username" 
              required 
            />
          </div>

          <div class="form-group">
            <label for="role">
              <i class="fas fa-user-tag"></i>
              Role:
            </label>
            <select style="width:395px" name="role" id="role" required>
              <option value="customer">Customer</option>
              <option value="admin">Admin</option>
              <option value="employee">Employee</option>
              <option value="delivery_person">Delivery Driver</option>
            </select>
          </div>

          <button type="submit" name="login" class="login-button">
            <i class="fas fa-arrow-right"></i> Login
          </button>
          
          <div class="login-info">
            <p>New users will be automatically registered</p>
          </div>
        </form>
      </div>
    </div>
    
    <div class="login-footer">
      <p>&copy; 2025  | M a l o n </p>
    </div>
  </div>
</body>
</html>