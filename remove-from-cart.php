<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to manage your cart'
    ]);
    exit;
}

// Check if product_id is provided
if (!isset($_POST['product_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Product ID is required'
    ]);
    exit;
}

$product_id = $_POST['product_id'];

// Check if cart exists
if (!isset($_SESSION['cart'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Cart is empty'
    ]);
    exit;
}

// Find and remove the item
$found = false;
foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['id'] == $product_id) {
        unset($_SESSION['cart'][$key]);
        $found = true;
        break;
    }
}

// Reindex array to remove gaps
$_SESSION['cart'] = array_values($_SESSION['cart']);

if ($found) {
    echo json_encode([
        'success' => true,
        'message' => 'Item removed successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Item not found in cart'
    ]);
}
