<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = "Please log in to cancel an order.";
    header('Location: auth/login.php');
    exit();
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Invalid request method.";
    header('Location: orders.php');
    exit();
}

// Sanitize and validate input
$orderKey = filter_input(INPUT_POST, 'order_key', FILTER_SANITIZE_STRING);
$reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
$alternative = filter_input(INPUT_POST, 'alternative', FILTER_SANITIZE_STRING);
$contactPreference = filter_input(INPUT_POST, 'contact_preference', FILTER_SANITIZE_STRING);

// Validate all required fields
if (empty($orderKey)) {
    $_SESSION['message'] = "Invalid order selection.";
    header('Location: orders.php');
    exit();
}

if (empty($reason) || strlen($reason) < 10) {
    $_SESSION['message'] = "Please provide a detailed reason for cancellation (minimum 10 characters).";
    header('Location: orders.php');
    exit();
}

if (empty($alternative) || !in_array($alternative, ['refund', 'exchange', 'reschedule', 'payment'])) {
    $_SESSION['message'] = "Please select a valid alternative option.";
    header('Location: orders.php');
    exit();
}

if (empty($contactPreference) || !in_array($contactPreference, ['email', 'phone', 'both'])) {
    $_SESSION['message'] = "Please select a valid contact preference.";
    header('Location: orders.php');
    exit();
}

// Load orders
$ordersFile = 'orders.json';
if (!file_exists($ordersFile)) {
    $_SESSION['message'] = "Error: Orders file not found.";
    header('Location: orders.php');
    exit();
}

$orders = json_decode(file_get_contents($ordersFile), true);
if ($orders === null) {
    $_SESSION['message'] = "Error: Invalid orders data.";
    header('Location: orders.php');
    exit();
}

// Verify the order exists and belongs to the user
$userEmail = $_SESSION['user']['email'];
if (!isset($orders[$orderKey]) || $orders[$orderKey]['user'] !== $userEmail) {
    $_SESSION['message'] = "Order not found or unauthorized.";
    header('Location: orders.php');
    exit();
}

$order = $orders[$orderKey];

// Check if order is already under review
if (isset($order['status']) && $order['status'] === 'review') {
    $_SESSION['message'] = "This order is already under review.";
    header('Location: orders.php');
    exit();
}

// Check if order is in a state where it can be cancelled
if (isset($order['status']) && $order['status'] === 'delivered') {
    $_SESSION['message'] = "This order cannot be cancelled as it has already been delivered.";
    header('Location: orders.php');
    exit();
}

// Store cancellation details before removing the order
$cancellationDetails = [
    'reason' => $reason,
    'alternative' => $alternative,
    'contact_preference' => $contactPreference,
    'date' => date('Y-m-d H:i:s'),
    'user_email' => $userEmail,
    'order_details' => $order // Store the complete order details for reference
];

// Remove the order from the orders array
unset($orders[$orderKey]);

// Save the updated orders
if (file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT)) === false) {
    $_SESSION['message'] = "Error: Unable to save cancellation request.";
    header('Location: orders.php');
    exit();
}

// Save cancellation details to a separate file for admin reference
$cancellationsFile = 'cancellations.json';
$cancellations = [];
if (file_exists($cancellationsFile)) {
    $cancellations = json_decode(file_get_contents($cancellationsFile), true) ?? [];
}
$cancellations[] = $cancellationDetails;
file_put_contents($cancellationsFile, json_encode($cancellations, JSON_PRETTY_PRINT));

$_SESSION['message'] = "Your order has been cancelled successfully.";
header('Location: orders.php');
exit();
