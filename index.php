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
  <title>Pet Supply Store</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <style>
    body {
      min-height: 100vh;
      padding-bottom: 60px;
      position: relative;
    }
    .featured-products {
      margin-bottom: 2rem;
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
  <!-- Hero Section -->
  <section class="hero bg-warning text-center text-white py-5">
    <div class="container">
      <h2 class="display-4">Happy Pets, Happy Life</h2>
      <p class="lead">Premium food, toys, and accessories for your furry friends.</p>
      <a href="products.php" class="btn btn-dark btn-lg mt-3">Shop Now</a>
    </div>
  </section>

  <!-- Featured Products -->
  <section class="featured-products py-5">
    <div class="container">
      <h3 class="text-center mb-4">Featured Products</h3>
      <div class="row g-4">
        <?php foreach (array_slice($products, 0, 3) as $product): ?>
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
    </div>
  </section>

  <?php include('includes/footer.php'); ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/script.js"></script>
</body>
</html>
