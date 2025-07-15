<?php
session_start();

if (!isset($_SESSION['user'])) {
  header('Location: auth/login.php');
  exit();
}

$user = $_SESSION['user'];

// Function to censor email
function censorEmail($email) {
    $parts = explode('@', $email);
    $username = $parts[0];
    $domain = $parts[1];
    
    // Show first 3 characters of username, rest as asterisks
    $censoredUsername = substr($username, 0, 3) . str_repeat('*', strlen($username) - 3);
    
    // Show first character of domain, rest as asterisks
    $censoredDomain = substr($domain, 0, 1) . str_repeat('*', strlen($domain) - 1);
    
    return $censoredUsername . '@' . $censoredDomain;
}

// Get user's orders
$orders = [];
if (file_exists('orders.json')) {
    $ordersData = json_decode(file_get_contents('orders.json'), true);
    foreach ($ordersData as $order) {
        if ($order['user'] === $user['email']) {
            $orders[] = $order;
        }
    }
}

// Get total spent
$totalSpent = 0;
foreach ($orders as $order) {
    $totalSpent += $order['total'];
}

// Get order counts by status
$orderCounts = [
    'pending' => 0,
    'processing' => 0,
    'to_ship' => 0,
    'delivered' => 0
];

foreach ($orders as $order) {
    if (isset($order['status'])) {
        $orderCounts[$order['status']]++;
    } else {
        $orderCounts['pending']++;
    }
}
?>
<?php include('includes/header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - Pawfect Supplies</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
  <style>
    .profile-header {
      background: linear-gradient(135deg, #6B8DD6 0%, #4A6FDC 100%);
      color: white;
      padding: 2rem 0;
      margin-bottom: 2rem;
    }
    .stat-card {
      border-radius: 10px;
      transition: transform 0.2s;
    }
    .stat-card:hover {
      transform: translateY(-5px);
    }
    .order-status {
      padding: 0.25rem 0.5rem;
      border-radius: 15px;
      font-size: 0.875rem;
    }
    .status-pending { background-color: #fff3cd; color: #856404; }
    .status-processing { background-color: #cce5ff; color: #004085; }
    .status-to_ship { background-color: #d4edda; color: #155724; }
    .status-delivered { background-color: #e2e3e5; color: #383d41; }
  </style>
</head>
<body>
  <div class="profile-header">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-12">
          <h1 class="mb-3">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
          <p class="lead mb-0">Manage your account and track your orders</p>
        </div>
      </div>
    </div>
  </div>

  <main class="container py-4">
    <!-- Stats Overview -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card stat-card h-100">
          <div class="card-body">
            <h6 class="card-subtitle mb-2 text-muted">Total Orders</h6>
            <h2 class="card-title mb-0"><?php echo count($orders); ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card h-100">
          <div class="card-body">
            <h6 class="card-subtitle mb-2 text-muted">Total Spent</h6>
            <h2 class="card-title mb-0">₱<?php echo number_format($totalSpent, 2); ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card h-100">
          <div class="card-body">
            <h6 class="card-subtitle mb-2 text-muted">Pending Orders</h6>
            <h2 class="card-title mb-0"><?php echo $orderCounts['pending']; ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card h-100">
          <div class="card-body">
            <h6 class="card-subtitle mb-2 text-muted">Delivered Orders</h6>
            <h2 class="card-title mb-0"><?php echo $orderCounts['delivered']; ?></h2>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Profile Information -->
      <div class="col-md-4 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">Profile Information</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <p class="form-control-static"><?php echo htmlspecialchars($user['name']); ?></p>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <p class="form-control-static"><?php echo censorEmail($user['email']); ?></p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateProfileModal">
              <i class="bi bi-pencil"></i> Update Profile
            </button>
          </div>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Recent Orders</h5>
            <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
          </div>
          <div class="card-body">
            <?php if (empty($orders)): ?>
              <p class="text-muted">No orders found.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Order ID</th>
                      <th>Date</th>
                      <th>Total</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    $recentOrders = array_slice(array_reverse($orders), 0, 5);
                    foreach ($recentOrders as $order): 
                    ?>
                    <tr>
                      <td>#<?php echo array_search($order, $ordersData) + 1; ?></td>
                      <td><?php echo date('M d, Y', strtotime($order['date'])); ?></td>
                      <td>₱<?php echo number_format($order['total'], 2); ?></td>
                      <td>
                        <span class="order-status status-<?php echo isset($order['status']) ? $order['status'] : 'pending'; ?>">
                          <?php echo isset($order['status']) ? ucfirst($order['status']) : 'Pending'; ?>
                        </span>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Update Profile Modal -->
  <div class="modal fade" id="updateProfileModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Update Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="updateProfileForm" action="update-profile.php" method="POST">
            <div class="mb-3">
              <label for="name" class="form-label">Name</label>
              <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
              <label for="current_password" class="form-label">Current Password</label>
              <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            <div class="mb-3">
              <label for="new_password" class="form-label">New Password (leave blank to keep current)</label>
              <input type="password" class="form-control" id="new_password" name="new_password">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" form="updateProfileForm" class="btn btn-primary">Save Changes</button>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
