<?php
session_start();

$errors = [];

// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
  header("location: index.php");
  exit;
}

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '' || $password === '') {
    $errors[] = "All fields are required.";
  } else {
    $users = file_exists('users.json') ? json_decode(file_get_contents('users.json'), true) : [];
    $userFound = false;

    foreach ($users as $user) {
      if ($user['email'] === $email && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
          'name' => $user['name'],
          'email' => $user['email'],
          'is_admin' => $user['is_admin'] ?? false //store admin status
        ];
        $userFound = true;

        //Redirect based on role
        if ($_SESSION['user']['is_admin']) {
          header('Location: ../admin-dashboard.php');
        } else {
          header('Location: ../index.php');
        }
        exit();
      }
    }

    if (!$userFound) {
      $errors[] = "Invalid email or password.";
    }
  }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Pawfect Supplies</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" type="image/png" href="../pictures/paw-logo.png">
  <style>
    body {
      min-height: 100vh;
      padding-bottom: 60px;
      position: relative;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    .navbar {
      background-color: #f8f9fa;
      padding: 15px 0;
      border-bottom: 1px solid #dee2e6;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .logo {
      max-width: 150px;
      transition: transform 0.3s ease;
    }
    .logo:hover {
      transform: scale(1.05);
    }
    .logo img {
      width: 100%;
      height: auto;
    }
    .login-container {
      background: white;
      border-radius: 15px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      padding: 2rem;
      margin-top: 2rem;
    }
    .form-control {
      border-radius: 8px;
      padding: 0.75rem 1rem;
      border: 1px solid #dee2e6;
      transition: all 0.3s ease;
    }
    .form-control:focus {
      box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
      border-color: #86b7fe;
    }
    .btn-primary {
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .password-container {
      position: relative;
    }
    .password-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #6c757d;
      cursor: pointer;
      padding: 0;
    }
    .password-toggle:hover {
      color: #495057;
    }
    .alert {
      border-radius: 8px;
      border: none;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .alert-danger {
      background-color: #fff5f5;
      color: #dc3545;
    }
    .alert ul {
      margin-bottom: 0;
    }
    .alert li {
      margin-bottom: 0.25rem;
    }
    .alert li:last-child {
      margin-bottom: 0;
    }
  </style>
</head>
<body class="bg-light">
  <nav class="navbar">
    <div class="container">
      <a href="../index.php" class="logo">
        <img src="../pictures/paw-logo.png" alt="Pawfect Supplies Logo" class="img-fluid">
      </a>
    </div>
  </nav>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="login-container">
          <h2 class="mb-4 text-center">Welcome Back!</h2>
          <p class="text-center text-muted mb-4">Please login to your account to continue</p>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                  <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="post" action="" id="loginForm">
            <div class="mb-4">
              <label for="email" class="form-label">Email Address</label>
              <input type="email" name="email" id="email" class="form-control" required 
                     placeholder="Enter your email">
            </div>
            <div class="mb-4">
              <label for="password" class="form-label">Password</label>
              <div class="password-container">
                <input type="password" name="password" id="password" class="form-control" required
                       placeholder="Enter your password">
                <button type="button" class="password-toggle" onclick="togglePassword()">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">
              <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
          </form>

          <p class="mt-4 text-center">Don't have an account? <a href="signup.php" class="text-primary">Sign Up</a></p>
        </div>
      </div>
    </div>
  </div>
  <?php include('../includes/footer.php'); ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const toggleButton = document.querySelector('.password-toggle i');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.classList.remove('fa-eye');
        toggleButton.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        toggleButton.classList.remove('fa-eye-slash');
        toggleButton.classList.add('fa-eye');
      }
    }

    // Form validation
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value.trim();
      
      if (!email || !password) {
        e.preventDefault();
        alert('Please fill in all fields');
      }
    });
  </script>
</body>
</html>
