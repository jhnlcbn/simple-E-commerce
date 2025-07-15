<?php
session_start();

if (!isset($_POST['product_id'])) {
  header('Location: products.php');
  exit();
}

$productId = (int) $_POST['product_id'];
include('includes/product-data.php');

// Find the product by ID
$selectedProduct = null;
foreach ($products as $product) {
  if ($product['id'] === $productId) {
    $selectedProduct = $product;
    break;
  }
}

if ($selectedProduct === null) {
  header('Location: products.php');
  exit();
}

// Initialize cart session if not set
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// If product already exists in cart, increment quantity
if (isset($_SESSION['cart'][$productId])) {
  $_SESSION['cart'][$productId]['quantity'] += 1;
} else {
  $_SESSION['cart'][$productId] = [
    'id' => $selectedProduct['id'],
    'name' => $selectedProduct['name'],
    'price' => $selectedProduct['price'],
    'image' => $selectedProduct['image'],
    'quantity' => 1
  ];
}

// Set success message
$_SESSION['cart_message'] = "Product successfully added to cart!";

// Redirect back to the products page
header('Location: products.php');
exit();
