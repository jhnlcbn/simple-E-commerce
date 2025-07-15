<?php
session_start();

if (!isset($_SESSION['user'])) {
  header('Location: auth/login.php');
  exit();
}

$cart = $_SESSION['cart'] ?? [];
?>
<?php include('includes/header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Cart - Pawfect Supplies</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .quantity-control {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    .quantity-control button {
      width: 30px;
      height: 30px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .payment-method-details {
      display: none;
      margin-top: 1rem;
      padding: 1rem;
      border: 1px solid #dee2e6;
      border-radius: 0.25rem;
    }
    .loading {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.8);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }
  </style>
</head>
<body>
  <div class="loading">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>

  <main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Your Shopping Cart</h2>
      <a href="products.php" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left"></i> Continue Shopping
      </a>
    </div>

    <?php if (empty($cart)): ?>
      <div class="alert alert-info text-center">
        <i class="bi bi-cart-x fs-1 d-block mb-3"></i>
        Your cart is currently empty.
        <div class="mt-3">
          <a href="products.php" class="btn btn-primary">Start Shopping</a>
        </div>
      </div>
    <?php else: ?>
      <div class="row">
        <div class="col-lg-8">
          <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
              <thead class="table-dark">
                <tr>
                  <th style="width: 50px;">Select</th>
                  <th>Product</th>
                  <th>Price</th>
                  <th>Quantity</th>
                  <th>Total</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php $grandTotal = 0; ?>
                <?php foreach ($cart as $item): ?>
                  <?php $total = $item['price'] * $item['quantity']; ?>
                  <?php $grandTotal += $total; ?>
                  <tr>
                    <td>
                      <div class="form-check">
                        <input class="form-check-input item-select" type="checkbox" 
                               value="<?php echo htmlspecialchars(json_encode($item)); ?>" 
                               data-price="<?php echo $total; ?>" 
                               checked>
                      </div>
                    </td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                    <td>
                      <div class="quantity-control">
                        <button class="btn btn-sm btn-outline-secondary decrease-quantity" data-id="<?php echo $item['id']; ?>">-</button>
                        <span class="quantity"><?php echo $item['quantity']; ?></span>
                        <button class="btn btn-sm btn-outline-secondary increase-quantity" data-id="<?php echo $item['id']; ?>">+</button>
                      </div>
                    </td>
                    <td>₱<?php echo number_format($total, 2); ?></td>
                    <td>
                      <div class="btn-group">
                        <button class="btn btn-danger btn-sm remove-item" data-id="<?php echo $item['id']; ?>">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Order Summary</h5>
              <div class="d-flex justify-content-between mb-2">
                <span>Subtotal</span>
                <span id="subtotal">₱<?php echo number_format($grandTotal, 2); ?></span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span>Shipping</span>
                <span>Free</span>
              </div>
              <hr>
              <div class="d-flex justify-content-between mb-3">
                <strong>Total</strong>
                <strong id="total">₱<?php echo number_format($grandTotal, 2); ?></strong>
              </div>
            </div>
          </div>
        </div>
      </div>

      <form method="post" action="checkout.php" class="mt-4" id="checkoutForm">
        <div class="row">
          <div class="col-md-6">
            <h4>Shipping Information</h4>
            <div class="mb-3">
              <label for="name" class="form-label">Full Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="address" class="form-label">Shipping Address</label>
              <textarea name="address" class="form-control" rows="3" required></textarea>
            </div>
          </div>

          <div class="col-md-6">
            <h4>Payment Information</h4>
            <div class="mb-3">
              <label for="payment" class="form-label">Payment Method</label>
              <select name="payment" class="form-select" id="paymentMethod" required>
                <option value="" disabled selected>Select a payment method</option>
                <option value="cod">Cash on Delivery</option>
                <option value="gcash">Gcash</option>
                <option value="card">Credit/Debit Card</option>
              </select>
            </div>

            <div id="gcashDetails" class="payment-method-details">
              <p>Please send payment to:</p>
              <p><strong>GCash Number:</strong> 0912-345-6789</p>
              <p><strong>Account Name:</strong> Pawfect Supplies</p>
            </div>

            <div id="cardDetails" class="payment-method-details">
              <div class="mb-3">
                <label for="cardNumber" class="form-label">Card Number</label>
                <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456">
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="expiry" class="form-label">Expiry Date</label>
                    <input type="text" class="form-control" id="expiry" placeholder="MM/YY">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="cvv" class="form-label">CVV</label>
                    <input type="text" class="form-control" id="cvv" placeholder="123">
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label for="notes" class="form-label">Order Notes (Optional)</label>
              <textarea name="notes" class="form-control" rows="2" placeholder="Special instructions for delivery..."></textarea>
            </div>

            <!-- Add hidden input for selected cart items -->
            <input type="hidden" name="cart_items" id="selectedItems" value="">
            <input type="hidden" name="total_amount" id="selectedTotal" value="<?php echo $grandTotal; ?>">

            <button type="submit" class="btn btn-success btn-lg w-100">
              <i class="bi bi-lock"></i> Place Order
            </button>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </main>

  <?php include('includes/footer.php'); ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(function () {
      // Show loading state
      function showLoading() {
        $('.loading').css('display', 'flex');
      }

      function hideLoading() {
        $('.loading').hide();
      }

      // Update totals when items are selected/deselected
      function updateTotals() {
        let subtotal = 0;
        const selectedItems = [];
        
        $('.item-select:checked').each(function() {
          subtotal += parseFloat($(this).data('price'));
          selectedItems.push(JSON.parse($(this).val()));
        });

        $('#subtotal').text('₱' + subtotal.toFixed(2));
        $('#total').text('₱' + subtotal.toFixed(2));
        $('#selectedTotal').val(subtotal);
        $('#selectedItems').val(JSON.stringify(selectedItems));
      }

      // Initialize totals
      updateTotals();

      // Handle item selection
      $('.item-select').on('change', function() {
        updateTotals();
      });

      // Payment method details toggle
      $('#paymentMethod').on('change', function() {
        $('.payment-method-details').hide();
        const method = $(this).val();
        if (method === 'gcash') {
          $('#gcashDetails').show();
        } else if (method === 'card') {
          $('#cardDetails').show();
        }
      });

      // Quantity controls
      $('.increase-quantity, .decrease-quantity').on('click', function() {
        const id = $(this).data('id');
        const isIncrease = $(this).hasClass('increase-quantity');
        const $row = $(this).closest('tr');
        const $quantitySpan = $row.find('.quantity');
        const $totalCell = $row.find('td:eq(4)');
        const $checkbox = $row.find('.item-select');
        
        showLoading();
        $.ajax({
          url: 'update-cart.php',
          method: 'POST',
          data: {
            product_id: id,
            action: isIncrease ? 'increase' : 'decrease'
          },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              // Update quantity
              $quantitySpan.text(response.data.quantity);
              
              // Update item total
              $totalCell.text('₱' + response.data.item_total.toFixed(2));
              
              // Update checkbox data
              const item = JSON.parse($checkbox.val());
              item.quantity = response.data.quantity;
              $checkbox.val(JSON.stringify(item));
              $checkbox.data('price', response.data.item_total);
              
              // Update totals if item is selected
              if ($checkbox.is(':checked')) {
                updateTotals();
              }
            } else {
              alert(response.message || 'Failed to update quantity');
            }
            hideLoading();
          },
          error: function() {
            alert('An error occurred while updating quantity');
            hideLoading();
          }
        });
      });

      // Remove item with confirmation
      $('.remove-item').on('click', function() {
        if (confirm('Are you sure you want to remove this item?')) {
          const id = $(this).data('id');
          showLoading();
          $.ajax({
            url: 'remove-from-cart.php',
            method: 'POST',
            data: { product_id: id },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                location.reload();
              } else {
                alert(response.message || 'Failed to remove item');
                hideLoading();
              }
            },
            error: function() {
              alert('An error occurred while removing the item');
              hideLoading();
            }
          });
        }
      });

      // Form validation
      $('#checkoutForm').on('submit', function(e) {
        const selectedItems = $('.item-select:checked').length;
        if (selectedItems === 0) {
          e.preventDefault();
          alert('Please select at least one item to order.');
          return false;
        }

        const name = $('input[name="name"]').val().trim();
        const address = $('textarea[name="address"]').val().trim();
        const payment = $('select[name="payment"]').val();

        if (!name || !address || !payment) {
          e.preventDefault();
          alert('Please fill in all required fields.');
          return false;
        }

        if (payment === 'card') {
          const cardNumber = $('#cardNumber').val().trim();
          const expiry = $('#expiry').val().trim();
          const cvv = $('#cvv').val().trim();

          if (!cardNumber || !expiry || !cvv) {
            e.preventDefault();
            alert('Please fill in all card details.');
            return false;
          }
        }

        showLoading();
        return true;
      });
    });
  </script>
</body>
</html>
