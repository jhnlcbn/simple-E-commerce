<?php
session_start();

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user'])) {
  header('Location: auth/login.php');
  exit();
}

$userEmail = $_SESSION['user']['email'];
$ordersFile = 'orders.json';
$orders = file_exists($ordersFile) ? json_decode(file_get_contents($ordersFile), true) : [];

// Process order statuses
foreach ($orders as $key => $order) {
    if ($order['user'] === $userEmail) {
        $orderTime = strtotime($order['date']);
        $currentTime = time();
        $timeDiff = $currentTime - $orderTime;
        
        // If order is pending and 2 minutes have passed, change to processing
        if (!isset($order['status']) && $timeDiff >= 120) { // 120 seconds = 2 minutes
            $orders[$key]['status'] = 'processing';
        }
        
        // If order is processing and 2 more minutes have passed, change to to_ship
        if (isset($order['status']) && $order['status'] === 'processing' && $timeDiff >= 240) { // 240 seconds = 4 minutes (2 + 2)
            $orders[$key]['status'] = 'to_ship';
            $orders[$key]['ship_time'] = date('Y-m-d H:i:s');
        }
        
        // If order is to_ship and 5 minutes have passed, change to delivered
        if (isset($order['status']) && $order['status'] === 'to_ship' && isset($order['ship_time'])) {
            $shipTime = strtotime($order['ship_time']);
            $shipTimeDiff = $currentTime - $shipTime;
            if ($shipTimeDiff >= 300) { // 300 seconds = 5 minutes
                $orders[$key]['status'] = 'delivered';
                // Add delivery_time if not set
                if (!isset($orders[$key]['delivery_time'])) {
                    $orders[$key]['delivery_time'] = date('Y-m-d H:i:s', $shipTime + 300);
                }
            }
        }

        // For orders that are already marked as delivered but missing ship_time
        if (isset($order['status']) && $order['status'] === 'delivered' && !isset($order['ship_time'])) {
            $orders[$key]['ship_time'] = date('Y-m-d H:i:s', $orderTime + 240); // Set ship_time to 4 minutes after order
            if (!isset($orders[$key]['delivery_time'])) {
                $orders[$key]['delivery_time'] = date('Y-m-d H:i:s', $orderTime + 540); // Set delivery_time to 9 minutes after order
            }
        }
    }
}

// Save updated orders
if (!empty($orders)) {
    file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT));
}

// Filter user orders and reindex the array
$userOrders = array_values(array_filter($orders, function($order) use ($userEmail) {
    return $order['user'] === $userEmail;
}));

// Sort orders by date in descending order (newest first)
usort($userOrders, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Function to format date consistently
function formatDateTime($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y g:i A');
}
?>
<?php include('includes/header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders - Pawfect Supplies</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .status-badge {
      font-size: 0.9rem;
      padding: 0.5em 1em;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      border-radius: 20px;
      font-weight: 500;
    }
    .status-pending {
      background-color: #fff3cd;
      color: #856404;
      border: 1px solid #ffeeba;
    }
    .status-processing {
      background-color: #cff4fc;
      color: #055160;
      border: 1px solid #b6effb;
    }
    .status-to-ship {
      background-color: #e2d9f3;
      color: #6f42c1;
      border: 1px solid #d8c7f0;
    }
    .status-delivered {
      background-color: #d1e7dd;
      color: #0f5132;
      border: 1px solid #badbcc;
    }
    .status-review {
      background-color: #f8d7da;
      color: #842029;
      border: 1px solid #f5c2c7;
    }
    .rating-stars {
      color: #ffc107;
      cursor: pointer;
    }
    .rating-stars:hover {
      color: #ffdb4d;
    }
    .timer {
      font-size: 0.9rem;
      color: #6f42c1;
      font-weight: bold;
    }
    .order-card {
      transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
      border: none;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      will-change: transform;
    }
    .order-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .tracking-timeline {
      position: relative;
      padding: 20px 0;
    }
    .tracking-timeline::before {
      content: '';
      position: absolute;
      top: 0;
      left: 50%;
      width: 2px;
      height: 100%;
      background: #e9ecef;
    }
    .tracking-step {
      position: relative;
      margin-bottom: 30px;
    }
    .tracking-step::before {
      content: '';
      position: absolute;
      left: 50%;
      width: 20px;
      height: 20px;
      background: #fff;
      border: 2px solid #e9ecef;
      border-radius: 50%;
      transform: translateX(-50%);
    }
    .tracking-step.active::before {
      background: #6f42c1;
      border-color: #6f42c1;
    }
    .tracking-step.completed::before {
      background: #198754;
      border-color: #198754;
    }
    .search-box {
      max-width: 300px;
    }
    .order-actions {
      opacity: 0;
      transition: opacity 0.2s;
    }
    .order-card:hover .order-actions {
      opacity: 1;
    }
  </style>
</head>
<body>
  <main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">My Orders</h2>
      <div class="d-flex gap-3">
        <div class="search-box">
          <input type="text" class="form-control" id="orderSearch" placeholder="Search orders...">
        </div>
        <select class="form-select" id="orderSort" style="width: 200px;">
          <option value="date_desc">Newest First</option>
          <option value="date_asc">Oldest First</option>
          <option value="amount_desc">Highest Amount</option>
          <option value="amount_asc">Lowest Amount</option>
          <option value="status">By Status</option>
        </select>
      </div>
    </div>

    <?php if (!empty($_SESSION['message'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (empty($userOrders)): ?>
      <div class="alert alert-info text-center">You haven't placed any orders yet.</div>
    <?php else: ?>
      <?php foreach ($userOrders as $index => $order): ?>
        <div class="card order-card mb-4">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
              <strong><?php echo formatDateTime($order['date']); ?></strong>
              <span class="text-muted">Order #<?php echo str_pad($index + 1, 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="d-flex align-items-center gap-3">
              <?php if (isset($order['status']) && $order['status'] === 'to_ship'): ?>
                <span class="timer me-3" id="timer<?php echo $index; ?>">
                  <i class="fas fa-clock me-1"></i>
                  <span class="countdown"></span>
                </span>
              <?php endif; ?>
              <span class="badge status-badge 
                <?php 
                  if (!isset($order['status'])) {
                    echo 'status-pending';
                  } elseif ($order['status'] === 'processing') {
                    echo 'status-processing';
                  } elseif ($order['status'] === 'to_ship') {
                    echo 'status-to-ship';
                  } elseif ($order['status'] === 'delivered') {
                    echo 'status-delivered';
                  } elseif ($order['status'] === 'review') {
                    echo 'status-review';
                  }
                ?>">
                <i class="fas <?php 
                  if (!isset($order['status'])) {
                    echo 'fa-clock';
                  } elseif ($order['status'] === 'processing') {
                    echo 'fa-cog fa-spin';
                  } elseif ($order['status'] === 'to_ship') {
                    echo 'fa-truck';
                  } elseif ($order['status'] === 'delivered') {
                    echo 'fa-check-circle';
                  } elseif ($order['status'] === 'review') {
                    echo 'fa-exclamation-circle';
                  }
                ?>"></i>
                <?php 
                  if (!isset($order['status'])) {
                    echo 'Pending';
                  } elseif ($order['status'] === 'processing') {
                    echo 'Processing';
                  } elseif ($order['status'] === 'to_ship') {
                    echo 'To Ship';
                  } elseif ($order['status'] === 'delivered') {
                    echo 'Delivered';
                  } elseif ($order['status'] === 'review') {
                    echo 'Cancellation Review';
                  }
                ?>
              </span>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-8">
                <div class="tracking-timeline mb-4">
                  <div class="tracking-step <?php echo !isset($order['status']) ? 'active' : 'completed'; ?>">
                    <div class="row">
                      <div class="col-6 text-end pe-4">
                        <h6 class="mb-1">Order Placed</h6>
                        <small class="text-muted"><?php echo formatDateTime($order['date']); ?></small>
                      </div>
                      <div class="col-6 text-start ps-4">
                        <h6 class="mb-1">Processing</h6>
                        <small class="text-muted"><?php echo isset($order['status']) && $order['status'] !== 'pending' ? formatDateTime(date('Y-m-d H:i:s', strtotime($order['date']) + 120)) : 'Pending'; ?></small>
                      </div>
                    </div>
                  </div>
                  <div class="tracking-step <?php echo isset($order['status']) && ($order['status'] === 'to_ship' || $order['status'] === 'delivered') ? 'completed' : (isset($order['status']) && $order['status'] === 'processing' ? 'active' : ''); ?>">
                    <div class="row">
                      <div class="col-6 text-end pe-4">
                        <h6 class="mb-1">Ready to Ship</h6>
                        <small class="text-muted"><?php echo isset($order['ship_time']) ? formatDateTime($order['ship_time']) : 'Pending'; ?></small>
                      </div>
                      <div class="col-6 text-start ps-4">
                        <h6 class="mb-1">Delivered</h6>
                        <small class="text-muted"><?php echo isset($order['status']) && $order['status'] === 'delivered' && isset($order['delivery_time']) ? formatDateTime($order['delivery_time']) : 'Pending'; ?></small>
                      </div>
                    </div>
                  </div>
                </div>
                <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                <p><strong>Payment Method:</strong> <?php echo strtoupper($order['payment']); ?></p>
                <p><strong>Total:</strong> ₱<?php echo number_format($order['total'], 2); ?></p>
              </div>
              <div class="col-md-4">
                <h6>Items:</h6>
                <ul class="list-group mb-3">
                  <?php foreach ($order['items'] as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                        <small class="text-muted d-block">Quantity: <?php echo $item['quantity']; ?></small>
                      </div>
                      <span class="badge bg-primary rounded-pill">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
            <div class="d-flex justify-content-end gap-2 order-actions">
              <?php if (!isset($order['status']) || $order['status'] === 'processing' || $order['status'] === 'to_ship'): ?>
                <button type="button" 
                        class="btn btn-outline-danger" 
                        data-bs-toggle="modal" 
                        data-bs-target="#cancelModal<?php echo $index; ?>"
                        style="width: 100px;">
                  <i class="fas fa-times-circle me-1"></i>
                  Cancel
                </button>
              <?php elseif ($order['status'] === 'delivered'): ?>
                <form action="reorder.php" method="post" class="d-inline">
                  <input type="hidden" name="items" value="<?php echo htmlspecialchars(json_encode($order['items'])); ?>">
                  <button type="submit" class="btn btn-outline-primary" style="width: 100px;">
                    <i class="fas fa-redo me-1"></i>
                    Reorder
                  </button>
                </form>
                <button type="button" 
                        class="btn btn-outline-success" 
                        data-bs-toggle="modal" 
                        data-bs-target="#rateModal<?php echo $index; ?>"
                        style="width: 100px;">
                  <i class="fas fa-star me-1"></i>
                  Rate
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Cancel Order Modal -->
        <div class="modal fade" id="cancelModal<?php echo $index; ?>" tabindex="-1" aria-labelledby="cancelModalLabel<?php echo $index; ?>" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-light">
                <h5 class="modal-title" id="cancelModalLabel<?php echo $index; ?>">
                  <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                  Cancel Order
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form action="cancel-order.php" method="post">
                <div class="modal-body">
                  <input type="hidden" name="order_key" value="<?php echo array_search($order, $orders); ?>">
                  
                  <div class="mb-4">
                    <label for="cancelReason<?php echo $index; ?>" class="form-label fw-bold">Reason for Cancellation</label>
                    <textarea class="form-control" 
                              id="cancelReason<?php echo $index; ?>" 
                              name="reason" 
                              rows="3" 
                              placeholder="Please explain why you want to cancel this order..."
                              required></textarea>
                  </div>

                  <div class="mb-4">
                    <label class="form-label fw-bold">What would you prefer instead?</label>
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="radio" name="alternative" id="refund<?php echo $index; ?>" value="refund" required>
                      <label class="form-check-label" for="refund<?php echo $index; ?>">
                        Get a refund
                      </label>
                    </div>
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="radio" name="alternative" id="exchange<?php echo $index; ?>" value="exchange">
                      <label class="form-check-label" for="exchange<?php echo $index; ?>">
                        Exchange for a different product
                      </label>
                    </div>
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="radio" name="alternative" id="reschedule<?php echo $index; ?>" value="reschedule">
                      <label class="form-check-label" for="reschedule<?php echo $index; ?>">
                        Reschedule the delivery
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="alternative" id="payment<?php echo $index; ?>" value="payment">
                      <label class="form-check-label" for="payment<?php echo $index; ?>">
                        Need to change payment method
                      </label>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="contactPreference<?php echo $index; ?>" class="form-label fw-bold">How should we contact you?</label>
                    <select class="form-select" id="contactPreference<?php echo $index; ?>" name="contact_preference" required>
                      <option value="">Select your preferred contact method</option>
                      <option value="email">Email</option>
                      <option value="phone">Phone</option>
                      <option value="both">Both Email and Phone</option>
                    </select>
                  </div>
                </div>
                <div class="modal-footer bg-light">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times-circle me-1"></i>
                    Submit Cancellation
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Rate Order Modal -->
        <div class="modal fade" id="rateModal<?php echo $index; ?>" tabindex="-1" aria-labelledby="rateModalLabel<?php echo $index; ?>" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-light">
                <h5 class="modal-title" id="rateModalLabel<?php echo $index; ?>">
                  <i class="fas fa-star text-warning me-2"></i>
                  Rate Order
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form action="rate-order.php" method="post">
                <div class="modal-body">
                  <input type="hidden" name="order_index" value="<?php echo $index; ?>">
                  
                  <div class="mb-4">
                    <label class="form-label fw-bold">Your Rating</label>
                    <div class="rating-stars fs-2">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star" data-rating="<?php echo $i; ?>"></i>
                      <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="ratingValue<?php echo $index; ?>" required>
                  </div>

                  <div class="mb-3">
                    <label for="review<?php echo $index; ?>" class="form-label fw-bold">Your Review</label>
                    <textarea class="form-control" 
                              id="review<?php echo $index; ?>" 
                              name="review" 
                              rows="3" 
                              placeholder="Share your experience with this order..."
                              required></textarea>
                  </div>
                </div>
                <div class="modal-footer bg-light">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane me-1"></i>
                    Submit Review
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- To Ship Timer -->
        <?php if (isset($order['status']) && $order['status'] === 'to_ship'): ?>
          <script>
            // Set up countdown timer for To Ship status
            (function() {
              const shipTime = new Date('<?php echo $order['ship_time']; ?>').getTime();
              const timerElement = document.querySelector('#timer<?php echo $index; ?> .countdown');
              
              function updateTimer() {
                const now = new Date().getTime();
                const timeLeft = shipTime + (10 * 60 * 1000) - now; // 10 minutes in milliseconds
                
                if (timeLeft <= 0) {
                  timerElement.textContent = 'Delivering...';
                  location.reload(); // Reload to update status
                  return;
                }
                
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                
                timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
              }
              
              updateTimer();
              setInterval(updateTimer, 1000);
            })();
          </script>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <?php include('includes/footer.php'); ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Rating stars functionality
    document.querySelectorAll('.rating-stars').forEach(container => {
      const stars = container.querySelectorAll('.fa-star');
      const ratingInput = container.parentElement.querySelector('input[name="rating"]');
      
      stars.forEach(star => {
        star.addEventListener('mouseover', function() {
          const rating = this.dataset.rating;
          stars.forEach(s => {
            s.classList.toggle('text-warning', s.dataset.rating <= rating);
          });
        });
        
        star.addEventListener('click', function() {
          const rating = this.dataset.rating;
          ratingInput.value = rating;
          stars.forEach(s => {
            s.classList.toggle('text-warning', s.dataset.rating <= rating);
          });
        });
      });
      
      container.addEventListener('mouseleave', function() {
        const rating = ratingInput.value;
        stars.forEach(s => {
          s.classList.toggle('text-warning', s.dataset.rating <= rating);
        });
      });
    });

    // Search functionality
    document.getElementById('orderSearch').addEventListener('input', function(e) {
      const searchTerm = e.target.value.toLowerCase();
      const orderCards = document.querySelectorAll('.order-card');
      
      orderCards.forEach(card => {
        const orderText = card.textContent.toLowerCase();
        card.style.display = orderText.includes(searchTerm) ? 'block' : 'none';
      });
    });

    // Sorting functionality
    document.getElementById('orderSort').addEventListener('change', function(e) {
      const sortBy = e.target.value;
      const orderCards = Array.from(document.querySelectorAll('.order-card'));
      
      orderCards.sort((a, b) => {
        switch(sortBy) {
          case 'date_desc':
            return new Date(b.querySelector('.card-header strong').textContent) - 
                   new Date(a.querySelector('.card-header strong').textContent);
          case 'date_asc':
            return new Date(a.querySelector('.card-header strong').textContent) - 
                   new Date(b.querySelector('.card-header strong').textContent);
          case 'amount_desc':
            return parseFloat(b.querySelector('.card-body strong:last-child').textContent.replace('₱', '').replace(',', '')) - 
                   parseFloat(a.querySelector('.card-body strong:last-child').textContent.replace('₱', '').replace(',', ''));
          case 'amount_asc':
            return parseFloat(a.querySelector('.card-body strong:last-child').textContent.replace('₱', '').replace(',', '')) - 
                   parseFloat(b.querySelector('.card-body strong:last-child').textContent.replace('₱', '').replace(',', ''));
          case 'status':
            return a.querySelector('.status-badge').textContent.trim().localeCompare(
              b.querySelector('.status-badge').textContent.trim()
            );
        }
      });
      
      const container = document.querySelector('main .container');
      orderCards.forEach(card => container.appendChild(card));
    });
  </script>
</body>
</html>
