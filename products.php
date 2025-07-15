<?php
session_start();
include('includes/product-data.php');
?>
<?php include('includes/header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop - Pawfect Supplies</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <style>
    body {
      min-height: 100vh;
      padding-bottom: 60px;
      position: relative;
    }
    .card-body .btn {
      border-radius: 12px;
    }
    .card-body .btn-outline-secondary {
      white-space: nowrap;
    }
  </style>
</head>
<body>
  <main class="container py-5">
    <h2 class="text-center mb-4">Our Products</h2>

    <?php if (isset($_SESSION['message']) || isset($_SESSION['cart_message'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        if (isset($_SESSION['message'])) {
          echo htmlspecialchars($_SESSION['message']);
          unset($_SESSION['message']);
        }
        if (isset($_SESSION['cart_message'])) {
          echo htmlspecialchars($_SESSION['cart_message']);
          unset($_SESSION['cart_message']);
        }
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <?php foreach ($products as $product): ?>
        <div class="col-md-4">
          <div class="card h-100">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <div class="card-body d-flex flex-column text-center">
              <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
              <p class="card-text">₱<?php echo number_format($product['price'], 2); ?></p>
              <a href="detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-info mb-2">View Details</a>
              <div class="mt-auto d-flex justify-content-between gap-2">
                <form action="checkout-entry.php" method="post" class="flex-grow-1">
                  <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                  <input type="hidden" name="quantity" value="1">
                  <button type="submit" class="btn btn-primary w-100">Order Now</button>
                </form>
                <form action="add-to-cart.php" method="post">
                  <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                  <button type="submit" class="btn btn-outline-secondary" style="width: 100px;">Add to Cart</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <!-- Footer -->
  <?php include('includes/footer.php'); ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/script.js"></script>
</body>
</html>
