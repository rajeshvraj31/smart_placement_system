<?php
session_start();
include '../config.php';
$error = '';
if(isset($_POST['login'])){
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']);
    $q = mysqli_query($conn,"SELECT * FROM admin WHERE username='$user' AND password='$pass'");
    if(mysqli_num_rows($q) > 0){
        $_SESSION['admin'] = $user;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — PlaceSync</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    .auth-left { background: linear-gradient(145deg, #0B1D3A, #1A3A2A); }
  </style>
</head>
<body>
<div class="auth-page">
  <div class="auth-left">
    <div class="auth-left-content">
      <a href="../index.php" class="auth-logo">
        <div class="icon"><i class="fas fa-graduation-cap"></i></div>
        <span>PlaceSync</span>
      </a>
      <h2>Placement<br>Officer <span>Control</span><br>Panel</h2>
      <p>Manage all students, job drives, eligibility rules, shortlisting, and analytics from one powerful dashboard.</p>
      <div class="auth-features">
        <div class="auth-feature"><i class="fas fa-check-circle"></i> Manage all job drives and companies</div>
        <div class="auth-feature"><i class="fas fa-check-circle"></i> Auto-shortlist with ML ranking</div>
        <div class="auth-feature"><i class="fas fa-check-circle"></i> Download & review all resumes</div>
        <div class="auth-feature"><i class="fas fa-check-circle"></i> Analytics & placement reports</div>
      </div>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-form-box animate">
      <h3>Admin Login</h3>
      <p class="subtitle">Placement Officer access only.</p>

      <?php if($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" placeholder="admin" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" name="login" class="btn-primary" style="background:linear-gradient(135deg,#1A3A2A,#1F5C3A)">
          <i class="fas fa-lock"></i> Login to Admin Panel
        </button>
      </form>

      <p class="form-link"><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
    </div>
  </div>
</div>
</body>
</html>
