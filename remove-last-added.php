<?php
session_start();

// Set header to return JSON response
header('Content-Type: application/json');

// Check if cart exists and is not empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit();
}

// Get the last added item (last key in the cart array)
$lastKey = array_key_last($_SESSION['cart']);

// Remove the last item
unset($_SESSION['cart'][$lastKey]);

// If cart is now empty, remove the cart array
if (empty($_SESSION['cart'])) {
    unset($_SESSION['cart']);
}

echo json_encode(['success' => true]);
exit(); 