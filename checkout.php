<?php
session_start();
include('includes/product-data.php');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = "Please log in to complete your order.";
    header('Location: auth/login.php');
    exit();
}

// Validate required fields
if (!isset($_POST['name']) || !isset($_POST['address']) || !isset($_POST['payment'])) {
    $_SESSION['message'] = "Please complete all required fields.";
    header('Location: cart.php');
    exit();
}

// Get cart items from POST data
$cartItems = [];
if (isset($_POST['cart_items'])) {
    $cartItems = json_decode($_POST['cart_items'], true);
    if (!$cartItems || !is_array($cartItems)) {
        $_SESSION['message'] = "Invalid cart data.";
        header('Location: cart.php');
        exit();
    }
} else if (isset($_POST['product_id'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    
    $product = null;
    foreach ($products as $item) {
        if ($item['id'] === $productId) {
            $product = $item;
            break;
        }
    }
    
    if (!$product) {
        $_SESSION['message'] = "Product not found.";
        header('Location: products.php');
        exit();
    }
    
    $cartItems = [[
        'id' => $productId,
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $quantity
    ]];
}

if (empty($cartItems)) {
    $_SESSION['message'] = "No items in cart.";
    header('Location: cart.php');
    exit();
}

$name = trim($_POST['name']);
$address = trim($_POST['address']);
$payment = trim($_POST['payment']);
$total = 0;

foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Create order
$order = [
    'user' => $_SESSION['user']['email'],
    'name' => $name,
    'address' => $address,
    'payment' => $payment,
    'items' => $cartItems,
    'total' => $total,
    'date' => date('Y-m-d H:i:s')
];

// Save order to orders.json
$ordersFile = 'orders.json';
$existingOrders = [];
if (file_exists($ordersFile)) {
    $existingOrders = json_decode(file_get_contents($ordersFile), true) ?? [];
}
$existingOrders[] = $order;

// Ensure the directory is writable
if (!is_writable(dirname($ordersFile))) {
    $_SESSION['message'] = "Error: Cannot save order. Please contact support.";
    header('Location: cart.php');
    exit();
}

// Save the orders
if (file_put_contents($ordersFile, json_encode($existingOrders, JSON_PRETTY_PRINT)) === false) {
    $_SESSION['message'] = "Error: Failed to save order. Please try again.";
    header('Location: cart.php');
    exit();
}

// Remove ordered items from cart
if (isset($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
    foreach ($cartItems as $orderedItem) {
        foreach ($cart as $key => $cartItem) {
            if ($cartItem['id'] === $orderedItem['id']) {
                unset($cart[$key]);
                break;
            }
        }
    }
    $_SESSION['cart'] = array_values($cart); // Reindex array
}

$_SESSION['message'] = "Your order has been placed successfully!";
header('Location: orders.php');
exit();
