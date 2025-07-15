<?php
session_start();
include('includes/product-data.php');

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$foundProduct = null;

foreach ($products as $product) {
  if ($product['id'] === $productId) {
    $foundProduct = $product;
    break;
  }
}

if (!$foundProduct) {
  header('Location: products.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($foundProduct['name']); ?> - Pawfect Supplies</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .product-image {
      width: 100%;
      height: 400px;
      object-fit: cover;
      border-radius: 8px;
      cursor: pointer;
      transition: transform 0.3s ease;
    }
    .product-image:hover {
      transform: scale(1.02);
    }
    .image-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.9);
      z-index: 1000;
      cursor: pointer;
    }
    .modal-image {
      max-width: 90%;
      max-height: 90vh;
      margin: auto;
      display: block;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }
    .product-details {
      padding: 20px;
    }
    .price-tag {
      font-size: 1.5rem;
      color: #28a745;
      font-weight: bold;
    }
    .action-buttons {
      margin-top: 20px;
    }
    .action-buttons .btn {
      margin-right: 10px;
    }
  </style>
</head>
<body>
  <?php include('includes/header.php'); ?>

  <main class="container py-5">
    <div class="row">
      <div class="col-md-6">
        <img src="<?php echo htmlspecialchars($foundProduct['image']); ?>" 
             class="product-image" 
             alt="<?php echo htmlspecialchars($foundProduct['name']); ?>"
             onclick="openImageModal(this.src)">
      </div>
      <div class="col-md-6 product-details">
        <h2 class="mb-3"><?php echo htmlspecialchars($foundProduct['name']); ?></h2>
        <p class="price-tag mb-4">₱<?php echo number_format($foundProduct['price'], 2); ?></p>
        <p class="mb-4">This is a premium quality product made especially for your pet's comfort and joy. Our products are carefully selected to ensure the highest quality and safety standards for your beloved pets.</p>
        
        <div class="action-buttons">
          <form action="add-to-cart.php" method="post" class="d-inline">
            <input type="hidden" name="product_id" value="<?php echo $foundProduct['id']; ?>">
            <button class="btn btn-success btn-lg">Add to Cart</button>
          </form>
          <form action="checkout-entry.php" method="post" class="d-inline">
            <input type="hidden" name="product_id" value="<?php echo $foundProduct['id']; ?>">
            <input type="hidden" name="quantity" value="1">
            <button class="btn btn-primary btn-lg">Order Now</button>
          </form>
        </div>
      </div>
    </div>
  </main>

  <!-- Image Modal -->
  <div id="imageModal" class="image-modal" onclick="closeImageModal()">
    <img id="modalImage" class="modal-image" src="" alt="Enlarged product image">
  </div>

  <?php include('includes/footer.php'); ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function openImageModal(imageSrc) {
      const modal = document.getElementById('imageModal');
      const modalImg = document.getElementById('modalImage');
      modal.style.display = "block";
      modalImg.src = imageSrc;
    }

    function closeImageModal() {
      document.getElementById('imageModal').style.display = "none";
    }

    // Close modal when pressing Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === "Escape") {
        closeImageModal();
      }
    });
  </script>
</body>
</html>
