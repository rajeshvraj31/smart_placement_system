<?php
session_start();
include '../config.php';
$success = $error = '';
if(isset($_POST['register'])){
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $cgpa     = (float)$_POST['cgpa'];
    $dept     = mysqli_real_escape_string($conn, $_POST['department']);
    $skills   = mysqli_real_escape_string($conn, $_POST['skills']);

    $chk = mysqli_query($conn,"SELECT id FROM students WHERE email='$email'");
    if(mysqli_num_rows($chk) > 0){
        $error = "This email is already registered. Please login.";
    } else {
        mysqli_query($conn,"INSERT INTO students(name,email,password,cgpa,department,skills)
            VALUES('$name','$email','$password','$cgpa','$dept','$skills')");
        $success = "Registration successful! You can now login.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Register — PlaceSync</title>
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
      <h2>Join the<br><span>Placement</span><br>Portal</h2>
      <p>Create your student profile to start applying for company drives.</p>
      <div class="auth-features">
        <div class="auth-feature"><i class="fas fa-check-circle"></i> Free to register — takes 2 minutes</div>
        <div class="auth-feature"><i class="fas fa-check-circle"></i> Automatic eligibility matching</div>
        <div class="auth-feature"><i class="fas fa-check-circle"></i> ML-based resume ranking</div>
        <div class="auth-feature"><i class="fas fa-check-circle"></i> Track all applications in one place</div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="auth-right">
    <div class="auth-form-box animate">
      <h3>Create Account</h3>
      <p class="subtitle">Fill in your details to get started.</p>

      <?php if($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
      <?php endif; ?>
      <?php if($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" placeholder="Enter your full name" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="your@email.com" required>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Min. 6 characters" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>CGPA (out of 10)</label>
            <input type="number" name="cgpa" placeholder="e.g. 8.5" step="0.01" min="0" max="10" required>
          </div>
          <div class="form-group">
            <label>Department</label>
            <input type="text" name="department" placeholder="Enter your department" required>
          </div>
        </div>
        <div class="form-group">
          <label>Skills (comma separated)</label>
          <textarea name="skills" placeholder="e.g. PHP, MySQL, Python, HTML, CSS, JavaScript"></textarea>
        </div>
        <button type="submit" name="register" class="btn-primary">
          <i class="fas fa-user-plus"></i> Create My Account
        </button>
      </form>

      <p class="form-link">Already have an account? <a href="login.php">Login here</a></p>
      <p class="form-link"><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
    </div>
  </div>
</div>
</body>
</html>
