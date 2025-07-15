<?php
session_start();
include('includes/product-data.php');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = "Please login to continue with checkout.";
    header('Location: auth/login.php');
    exit();
}

// Handle cart items case
if (isset($_POST['cart_items'])) {
    $items = json_decode($_POST['cart_items'], true);
    if (!$items) {
        $_SESSION['message'] = "Invalid cart items.";
        header('Location: cart.php');
        exit();
    }
    $total = $_POST['total_amount'] ?? 0;
} 
// Handle reorder case
else if (isset($_GET['reorder']) && isset($_SESSION['reorder_items'])) {
    $items = $_SESSION['reorder_items'];
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
} else {
    // Regular order case
    if (!isset($_POST['product_id'])) {
        $_SESSION['message'] = "Please select a product to checkout.";
        header('Location: products.php');
        exit();
    }

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

    $items = [[
        'id' => $productId,
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $quantity
    ]];
    $total = $product['price'] * $quantity;
}

// Get user data from session
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - Pawfect Supplies</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .quantity-control {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .quantity-btn {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 1px solid #dee2e6;
      background: white;
      font-size: 1.2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s;
    }
    .quantity-btn:hover {
      background: #f8f9fa;
    }
    .quantity-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    .quantity-input {
      width: 60px;
      text-align: center;
      font-size: 1.1rem;
    }
    #totalAmount {
      font-size: 1.2rem;
      font-weight: bold;
      color: #198754;
    }
  </style>
</head>
<body>
<?php include('includes/header.php'); ?>

<main class="container py-5">
  <h2 class="mb-4 text-center">Checkout</h2>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <form action="checkout.php" method="post" id="checkoutForm">
        <?php if (isset($_POST['cart_items'])): ?>
          <input type="hidden" name="cart_items" value="<?php echo htmlspecialchars($_POST['cart_items']); ?>">
          <input type="hidden" name="total_amount" value="<?php echo htmlspecialchars($_POST['total_amount']); ?>">
        <?php elseif (isset($_GET['reorder'])): ?>
          <input type="hidden" name="reorder" value="1">
          <input type="hidden" name="items" value="<?php echo htmlspecialchars(json_encode($items)); ?>">
        <?php else: ?>
          <input type="hidden" name="product_id" value="<?php echo $items[0]['id']; ?>">
          <input type="hidden" name="quantity" id="quantityInput" value="<?php echo $items[0]['quantity']; ?>">
        <?php endif; ?>

        <div class="mb-3">
          <label class="form-label">Items</label>
          <div class="list-group mb-3">
            <?php foreach ($items as $item): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                  <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                </div>
                <span class="badge bg-primary rounded-pill">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Total Amount</label>
          <input type="text" class="form-control" id="totalAmount" value="₱<?php echo number_format($total, 2); ?>" disabled>
        </div>

        <div class="mb-3">
          <label for="name" class="form-label">Full Name</label>
          <input type="text" class="form-control" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>

        <div class="mb-3">
          <label for="address" class="form-label">Shipping Address</label>
          <textarea class="form-control" name="address" id="address" rows="3" required></textarea>
        </div>

        <div class="mb-3">
          <label for="payment" class="form-label">Payment Method</label>
          <select class="form-select" name="payment" id="payment" required>
            <option value="">Select Payment Method</option>
            <option value="cod">Cash on Delivery</option>
            <option value="gcash">GCash</option>
            <option value="card">Credit/Debit Card</option>
          </select>
        </div>

        <div class="alert alert-info">
          <i class="bi bi-info-circle"></i> Please complete all required fields to proceed with your order.
        </div>

        <button type="submit" class="btn btn-success w-100">Place Order</button>
      </form>
    </div>
  </div>
</main>

<?php include('includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!isset($_GET['reorder'])): ?>
<script>
  const productPrice = <?php echo $items[0]['price']; ?>;
  let currentQuantity = <?php echo $items[0]['quantity']; ?>;
  const minQuantity = 1;

  function updateQuantity(change) {
    const newQuantity = currentQuantity + change;
    
    if (newQuantity >= minQuantity) {
      currentQuantity = newQuantity;
      document.getElementById('quantityDisplay').value = currentQuantity;
      document.getElementById('quantityInput').value = currentQuantity;
      updateTotal();
    }

    // Update button states
    document.getElementById('decreaseBtn').disabled = currentQuantity <= minQuantity;
  }

  function updateTotal() {
    const total = productPrice * currentQuantity;
    document.getElementById('totalAmount').value = '₱' + total.toFixed(2);
  }

  // Initialize button states
  document.getElementById('decreaseBtn').disabled = currentQuantity <= minQuantity;
</script>
<?php endif; ?>
</body>
</html>
