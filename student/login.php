<?php
session_start();
include '../config.php';
$error = '';
if(isset($_POST['login'])){
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $q = mysqli_query($conn,"SELECT * FROM students WHERE email='$email' AND password='$password'");
    if(mysqli_num_rows($q) > 0){
        $_SESSION['student'] = $email;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Login — PlaceSync</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="auth-page">

  <!-- LEFT PANEL -->
  <div class="auth-left">
    <div class="auth-left-content">
      <a href="../index.php" class="auth-logo">
        <div class="icon"><i class="fas fa-graduation-cap"></i></div>
        <span>PlaceSync</span>
      </a>
      <h2>Your Career<br>Journey <span>Starts Here</span></h2>
      <p>Access job drives, upload your resume, and track your placement status — all from one smart dashboard.</p>
      <div class="auth-features">
        <div class="auth-feature"><i class="fas fa-check-circle"></i> View only drives you're eligible for</div>
        <div class="auth-feature"><i class="fas fa-check-circle"></i> AI-powered resume scoring</div>
        <div class="auth-feature"><i class="fas fa-check-circle"></i> Real-time application status</div>
        <div class="auth-feature"><i class="fas fa-check-circle"></i> Secure PDF resume upload</div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="auth-right">
    <div class="auth-form-box animate">
      <h3>Student Login</h3>
      <p class="subtitle">Welcome back! Enter your credentials to continue.</p>

      <?php if($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="your@email.com" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" name="login" class="btn-primary">
          <i class="fas fa-sign-in-alt"></i> Login to Dashboard
        </button>
      </form>

      <p class="form-link">Don't have an account? <a href="register.php">Register here</a></p>
      <p class="form-link"><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
    </div>
  </div>
</div>
</body>
</html>
