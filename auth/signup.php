<?php
session_start();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $isAdmin = isset($_POST['is_admin']) ? true : false;

  if ($name === '' || $email === '' || $password === '') {
    $errors[] = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
  } else {
    $users = file_exists('users.json') ? json_decode(file_get_contents('users.json'), true) : [];
    foreach ($users as $user) {
      if ($user['email'] === $email) {
        $errors[] = "Email already exists.";
        break;
      }
    }

    if (empty($errors)) {
      $users[] = [
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'is_admin' => $isAdmin
      ];
      file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
      $success = true;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - Pawfect Supplies</title>
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
    .signup-container {
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
    .alert-success {
      background-color: #f0fff4;
      color: #28a745;
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
    .password-requirements {
      font-size: 0.875rem;
      color: #6c757d;
      margin-top: 0.5rem;
    }
    .password-requirements ul {
      list-style: none;
      padding-left: 0;
      margin-bottom: 0;
    }
    .password-requirements li {
      margin-bottom: 0.25rem;
      display: flex;
      align-items: center;
    }
    .password-requirements li i {
      margin-right: 0.5rem;
      width: 16px;
    }
    .requirement-met {
      color: #28a745;
    }
    .requirement-unmet {
      color: #dc3545;
    }
    .password-match {
      font-size: 0.875rem;
      margin-top: 0.5rem;
      display: flex;
      align-items: center;
    }
    .password-match i {
      margin-right: 0.5rem;
      width: 16px;
    }
    .password-match.match {
      color: #28a745;
    }
    .password-match.mismatch {
      color: #dc3545;
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
        <div class="signup-container">
          <h2 class="mb-4 text-center">Create Your Account</h2>
          <p class="text-center text-muted mb-4">Join our community of pet lovers</p>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                  <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php elseif ($success): ?>
            <div class="alert alert-success">
              <i class="fas fa-check-circle me-2"></i>
              Signup successful! <a href="login.php" class="alert-link">Login here</a>.
            </div>
          <?php endif; ?>

          <form method="post" id="signupForm">
            <div class="mb-4">
              <label for="name" class="form-label">Full Name</label>
              <input type="text" name="name" id="name" class="form-control" required
                     placeholder="Enter your full name">
            </div>
            <div class="mb-4">
              <label for="email" class="form-label">Email Address</label>
              <input type="email" name="email" id="email" class="form-control" required
                     placeholder="Enter your email">
            </div>
            <div class="mb-4">
              <label for="password" class="form-label">Password</label>
              <div class="password-container">
                <input type="password" name="password" id="password" class="form-control" required
                       placeholder="Create a password">
                <button type="button" class="password-toggle" onclick="togglePassword()">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <div class="password-requirements">
                <ul>
                  <li id="length"><i class="fas fa-times"></i> At least 8 characters</li>
                  <li id="uppercase"><i class="fas fa-times"></i> One uppercase letter</li>
                  <li id="lowercase"><i class="fas fa-times"></i> One lowercase letter</li>
                  <li id="number"><i class="fas fa-times"></i> One number</li>
                  <li id="special"><i class="fas fa-times"></i> One special character</li>
                </ul>
              </div>
            </div>
            <div class="mb-4">
              <label for="confirm_password" class="form-label">Confirm Password</label>
              <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required
                       placeholder="Confirm your password">
                <button type="button" class="password-toggle" onclick="toggleConfirmPassword()">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <div id="passwordMatch" class="password-match">
                <i class="fas fa-times"></i>
                <span>Passwords must match</span>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">
              <i class="fas fa-user-plus me-2"></i>Create Account
            </button>
          </form>

          <p class="mt-4 text-center">Already have an account? <a href="login.php" class="text-primary">Login</a></p>
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

    function toggleConfirmPassword() {
      const confirmPasswordInput = document.getElementById('confirm_password');
      const toggleButton = document.querySelectorAll('.password-toggle')[1].querySelector('i');
      
      if (confirmPasswordInput.type === 'password') {
        confirmPasswordInput.type = 'text';
        toggleButton.classList.remove('fa-eye');
        toggleButton.classList.add('fa-eye-slash');
      } else {
        confirmPasswordInput.type = 'password';
        toggleButton.classList.remove('fa-eye-slash');
        toggleButton.classList.add('fa-eye');
      }
    }

    // Password validation
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const passwordMatch = document.getElementById('passwordMatch');
    const requirements = {
      length: /.{8,}/,
      uppercase: /[A-Z]/,
      lowercase: /[a-z]/,
      number: /[0-9]/,
      special: /[!@#$%^&*(),.?":{}|<>]/
    };

    password.addEventListener('input', function() {
      const value = this.value;
      
      for (const requirement in requirements) {
        const element = document.getElementById(requirement);
        const icon = element.querySelector('i');
        
        if (requirements[requirement].test(value)) {
          element.classList.add('requirement-met');
          element.classList.remove('requirement-unmet');
          icon.classList.remove('fa-times');
          icon.classList.add('fa-check');
        } else {
          element.classList.remove('requirement-met');
          element.classList.add('requirement-unmet');
          icon.classList.remove('fa-check');
          icon.classList.add('fa-times');
        }
      }
    });

    // Password match validation
    function checkPasswordMatch() {
      const passwordValue = password.value;
      const confirmPasswordValue = confirmPassword.value;
      const icon = passwordMatch.querySelector('i');
      const text = passwordMatch.querySelector('span');

      if (!confirmPasswordValue) {
        passwordMatch.className = 'password-match';
        icon.className = 'fas fa-times';
        text.textContent = 'Passwords must match';
        return false;
      }

      if (passwordValue === confirmPasswordValue) {
        passwordMatch.className = 'password-match match';
        icon.className = 'fas fa-check';
        text.textContent = 'Passwords match';
        return true;
      } else {
        passwordMatch.className = 'password-match mismatch';
        icon.className = 'fas fa-times';
        text.textContent = 'Passwords do not match';
        return false;
      }
    }

    confirmPassword.addEventListener('input', checkPasswordMatch);
    password.addEventListener('input', checkPasswordMatch);

    // Form validation
    document.getElementById('signupForm').addEventListener('submit', function(e) {
      const name = document.getElementById('name').value.trim();
      const email = document.getElementById('email').value.trim();
      const passwordValue = password.value;
      const confirmPasswordValue = confirmPassword.value;
      
      if (!name || !email || !passwordValue || !confirmPasswordValue) {
        e.preventDefault();
        alert('Please fill in all fields');
        return;
      }

      // Check if all password requirements are met
      for (const requirement in requirements) {
        if (!requirements[requirement].test(passwordValue)) {
          e.preventDefault();
          alert('Please ensure your password meets all requirements');
          return;
        }
      }

      // Check if passwords match
      if (passwordValue !== confirmPasswordValue) {
        e.preventDefault();
        alert('Passwords do not match');
        return;
      }

      // Email validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address');
        return;
      }
    });
  </script>
</body>
</html>
