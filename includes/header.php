<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="pictures/paw-logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .navbar-brand {
      max-width: 150px;
      transition: transform 0.3s ease;
    }
    .navbar-brand:hover {
      transform: scale(1.05);
    }
    .navbar-brand img {
      width: 100%;
      height: auto;
    }

    .mobile-nav-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 1.5rem;
      color: #333;
      cursor: pointer;
      padding: 0.5rem;
      transition: transform 0.3s ease;
    }
    .mobile-nav-toggle:hover {
      transform: scale(1.1);
    }

    .nav-menu {
      transition: all 0.3s ease;
    }

    .nav-link {
      position: relative;
      padding: 0.5rem 1rem;
      transition: color 0.3s ease;
    }

    .nav-link::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 0;
      left: 50%;
      background-color: #4A6FDC;
      transition: all 0.3s ease;
      transform: translateX(-50%);
    }

    .nav-link:hover::after,
    .nav-link.active::after {
      width: 100%;
    }

    .nav-link:hover {
      color: #4A6FDC !important;
    }

    .user-welcome {
      background: linear-gradient(135deg, #4A6FDC 0%, #6B8DD6 100%);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      margin: 0 0.5rem;
      transition: transform 0.3s ease;
    }

    .user-welcome:hover {
      transform: translateY(-2px);
    }

    .cart-icon {
      position: relative;
      margin-right: 1rem;
    }

    .cart-count {
      position: absolute;
      top: -8px;
      right: -8px;
      background-color: #dc3545;
      color: white;
      border-radius: 50%;
      padding: 0.2rem 0.5rem;
      font-size: 0.75rem;
      font-weight: bold;
    }

    @media (max-width: 991px) {
      .mobile-nav-toggle {
        display: block;
      }

      .nav-menu {
        display: none;
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        background: white;
        padding: 1rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 1000;
        border-radius: 0 0 15px 15px;
      }

      .nav-menu.active {
        display: block;
        animation: slideDown 0.3s ease;
      }

      @keyframes slideDown {
        from {
          transform: translateY(-10px);
          opacity: 0;
        }
        to {
          transform: translateY(0);
          opacity: 1;
        }
      }

      .nav {
        flex-direction: column;
      }

    .nav-item {
      margin: 0.5rem 0;
    }

      .nav-link {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        transition: all 0.3s ease;
      }

      .nav-link:hover {
        background-color: #f8f9fa;
      }

      .user-welcome {
        margin: 0.5rem 0;
        text-align: center;
      }
    }
  </style>
</head>
<body>
<header class="bg-light py-3 border-bottom sticky-top">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="index.php" class="navbar-brand">
      <img src="pictures/paw-logo.png" alt="Pawfect Supplies Logo" class="img-fluid">
    </a>
    
    <button class="mobile-nav-toggle" id="mobileNavToggle">
      <i class="fas fa-bars"></i>
    </button>

    <nav class="nav-menu" id="navMenu">
      <ul class="nav">
        <li class="nav-item">
          <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-home me-1"></i> Home
          </a>
        </li>
        <li class="nav-item">
          <a href="products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-bag me-1"></i> Shop
          </a>
        </li>
        <li class="nav-item">
          <a href="cart.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart me-1"></i> Cart
            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
              <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
            <?php endif; ?>
          </a>
        </li>
        <?php if (isset($_SESSION['user'])): ?>
          <li class="nav-item">
            <a href="orders.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
              <i class="fas fa-box me-1"></i> My Orders
            </a>
          </li>
          <li class="nav-item">
            <a href="profile.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
              <i class="fas fa-user me-1"></i> Profile
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link user-welcome">
              <i class="fas fa-paw me-1"></i> Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
            </a>
          </li>
          <li class="nav-item">
            <a href="auth/logout.php" class="nav-link text-danger">
              <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a href="auth/login.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>">
              <i class="fas fa-sign-in-alt me-1"></i> Login
            </a>
          </li>
          <li class="nav-item">
            <a href="auth/signup.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'signup.php' ? 'active' : ''; ?>">
              <i class="fas fa-user-plus me-1"></i> Sign Up
            </a>
          </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_admin']): ?>
          <li class="nav-item">
            <a href="admin-orders.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin-orders.php' ? 'active' : ''; ?>">
              <i class="fas fa-clipboard-list me-1"></i> Admin Orders
            </a>
          </li>
          <li class="nav-item">
            <a href="admin-dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php' ? 'active' : ''; ?>">
              <i class="fas fa-chart-line me-1"></i> Admin Dashboard
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>

<script>
  document.getElementById('mobileNavToggle').addEventListener('click', function() {
    const navMenu = document.getElementById('navMenu');
    navMenu.classList.toggle('active');
    
    // Toggle between hamburger and close icon
    const icon = this.querySelector('i');
    if (navMenu.classList.contains('active')) {
      icon.classList.remove('fa-bars');
      icon.classList.add('fa-times');
    } else {
      icon.classList.remove('fa-times');
      icon.classList.add('fa-bars');
    }
  });

  // Close menu when clicking outside
  document.addEventListener('click', function(event) {
    const navMenu = document.getElementById('navMenu');
    const mobileNavToggle = document.getElementById('mobileNavToggle');
    
    if (!navMenu.contains(event.target) && !mobileNavToggle.contains(event.target)) {
      navMenu.classList.remove('active');
      const icon = mobileNavToggle.querySelector('i');
      icon.classList.remove('fa-times');
      icon.classList.add('fa-bars');
    }
  });

  // Add active class to current page link
  document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
      if (link.getAttribute('href') === currentPage) {
        link.classList.add('active');
      }
    });
  });
</script>
</body>
</html>


