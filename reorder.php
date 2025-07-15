<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
  header("Location: auth/login.php");
  exit();
}

// Check for items in POST data
if (!isset($_POST['items'])) {
  $_SESSION['message'] = "Invalid reorder request.";
  header("Location: orders.php");
  exit();
}

// Decode the items JSON
$items = json_decode($_POST['items'], true);
if (!$items || !is_array($items)) {
  $_SESSION['message'] = "Invalid items data.";
  header("Location: orders.php");
  exit();
}

// Store items in session for checkout
$_SESSION['reorder_items'] = $items;

// Redirect to checkout-entry
header("Location: checkout-entry.php?reorder=1");
exit();
?>

