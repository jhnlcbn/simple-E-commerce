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

// Check if required parameters are provided
if (!isset($_POST['product_id']) || !isset($_POST['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

$product_id = $_POST['product_id'];
$action = $_POST['action'];

// Check if cart exists
if (!isset($_SESSION['cart'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Cart is empty'
    ]);
    exit;
}

// Find the item in cart
$item_key = null;
foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['id'] == $product_id) {
        $item_key = $key;
        break;
    }
}

if ($item_key === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Item not found in cart'
    ]);
    exit;
}

// Update quantity based on action
if ($action === 'increase') {
    $_SESSION['cart'][$item_key]['quantity']++;
} elseif ($action === 'decrease') {
    if ($_SESSION['cart'][$item_key]['quantity'] > 1) {
        $_SESSION['cart'][$item_key]['quantity']--;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Quantity cannot be less than 1'
        ]);
        exit;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    exit;
}

// Calculate new total
$item = $_SESSION['cart'][$item_key];
$new_total = $item['price'] * $item['quantity'];

// Calculate cart total
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}

echo json_encode([
    'success' => true,
    'message' => 'Quantity updated successfully',
    'data' => [
        'quantity' => $item['quantity'],
        'item_total' => $new_total,
        'cart_total' => $cart_total
    ]
]); 