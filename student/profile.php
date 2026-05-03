<?php
session_start();
include '../config.php';
if(!isset($_SESSION['student'])){ header("Location: login.php"); exit(); }

$email   = $_SESSION['student'];
$student = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM students WHERE email='$email'"));
$sid     = $student['id'];
$initial = strtoupper(substr($student['name'], 0, 1));

/* ── Stats for sidebar badges ────────────────────────────────────────────── */
$totalJobs = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM jobs"))['c'];
$applied   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications WHERE student_id='$sid'"))['c'];

/* ── Handle form submit ─────────────────────────────────────────────────── */
$success = $error = '';

if(isset($_POST['update_profile'])){

    $name  = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $dept  = trim(mysqli_real_escape_string($conn, $_POST['department']));
    $cgpa  = (float)$_POST['cgpa'];
    $skills= trim(mysqli_real_escape_string($conn, $_POST['skills']));

    /* Validate */
    if($name === ''){
        $error = 'Name cannot be empty.';
    } elseif($cgpa < 0 || $cgpa > 10){
        $error = 'CGPA must be between 0.00 and 10.00.';
    } else {
        mysqli_query($conn,"
            UPDATE students
            SET name='$name', department='$dept', cgpa='$cgpa', skills='$skills'
            WHERE id='$sid'
        ");
        $success = 'Profile updated successfully!';
        /* Refresh student data */
        $student = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM students WHERE id='$sid'"));
        $initial = strtoupper(substr($student['name'], 0, 1));
    }
}

/* ── Handle password change ─────────────────────────────────────────────── */
if(isset($_POST['change_password'])){
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if($current !== $student['password']){
        $error = 'Current password is incorrect.';
    } elseif(strlen($new) < 6){
        $error = 'New password must be at least 6 characters.';
    } elseif($new !== $confirm){
        $error = 'New passwords do not match.';
    } else {
        $safePass = mysqli_real_escape_string($conn, $new);
        mysqli_query($conn,"UPDATE students SET password='$safePass' WHERE id='$sid'");
        $success = 'Password changed successfully!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Edit Profile — PlaceSync</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    .profile-avatar-lg {
      width: 80px; height: 80px; border-radius: 50%;
      background: linear-gradient(135deg, var(--navy), var(--blue));
      display: flex; align-items: center; justify-content: center;
      font-size: 32px; font-weight: 700; color: #fff;
      margin: 0 auto 12px;
    }
    .form-group { margin-bottom: 18px; }
    .form-group label {
      display: block; font-size: 13px; font-weight: 600;
      color: var(--navy); margin-bottom: 6px;
    }
    .form-group input,
    .form-group textarea,
    .form-group select {
      width: 100%; padding: 10px 14px;
      border: 1.5px solid var(--border); border-radius: 8px;
      font-size: 13px; font-family: 'DM Sans', sans-serif;
      background: var(--light); color: var(--navy);
      box-sizing: border-box; transition: border .2s;
    }
    .form-group input:focus,
    .form-group textarea:focus {
      outline: none; border-color: var(--blue);
      background: #fff;
    }
    .form-group textarea { resize: vertical; min-height: 80px; }
    .form-hint { font-size: 11px; color: var(--muted); margin-top: 4px; }
    .section-title {
      font-size: 14px; font-weight: 700; color: var(--navy);
      margin-bottom: 16px; padding-bottom: 10px;
      border-bottom: 2px solid var(--border);
      display: flex; align-items: center; gap: 8px;
    }
    .info-row {
      display: flex; justify-content: space-between;
      padding: 10px 0; border-bottom: 1px solid var(--border);
      font-size: 13px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row .label { color: var(--muted); }
    .info-row .value { font-weight: 600; color: var(--navy); }
  </style>
</head>
<body>
<div class="dash-layout">

  <!-- ── SIDEBAR ──────────────────────────────────────────────────────── -->
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand">
      <div class="icon"><i class="fas fa-graduation-cap"></i></div><span>PlaceSync</span>
    </a>
    <div class="sidebar-user">
      <div class="user-avatar"><?= $initial ?></div>
      <div class="user-info">
        <div class="name"><?= htmlspecialchars($student['name']) ?></div>
        <div class="role">Student Portal</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>
      <a href="dashboard.php"      class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
      <a href="eligible_jobs.php"  class="nav-item"><i class="fas fa-briefcase"></i> Job Drives <span class="badge"><?= $totalJobs ?></span></a>
      <a href="my_applications.php"class="nav-item"><i class="fas fa-file-alt"></i> My Applications <span class="badge"><?= $applied ?></span></a>
      <div class="nav-section-label">Profile</div>
      <a href="upload_resume.php"  class="nav-item"><i class="fas fa-upload"></i> Upload Resume</a>
      <a href="profile.php"        class="nav-item active"><i class="fas fa-user-edit"></i> Edit Profile</a>
    </nav>
    <div class="sidebar-bottom">
      <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </aside>

  <!-- ── MAIN ─────────────────────────────────────────────────────────── -->
  <main class="dash-main">
    <div class="dash-header">
      <div>
        <h1>Edit Profile</h1>
        <p class="breadcrumb">Student &rarr; Profile &rarr; Edit</p>
      </div>
    </div>

    <div class="dash-content">

      <!-- Alert messages -->
      <?php if($success): ?>
        <div class="alert alert-success animate">
          <i class="fas fa-check-circle"></i> <?= $success ?>
        </div>
      <?php endif; ?>
      <?php if($error): ?>
        <div class="alert" style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;padding:12px 18px;border-radius:10px;margin-bottom:20px;font-size:13px">
          <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start">

        <!-- ── LEFT: Profile summary card ──────────────────────────────── -->
        <div style="display:flex;flex-direction:column;gap:16px">

          <!-- Avatar card -->
          <div class="card">
            <div class="card-body" style="padding:28px 20px;text-align:center">
              <div class="profile-avatar-lg"><?= $initial ?></div>
              <div style="font-size:16px;font-weight:700;color:var(--navy)"><?= htmlspecialchars($student['name']) ?></div>
              <div style="font-size:12px;color:var(--muted);margin-top:4px"><?= htmlspecialchars($student['email']) ?></div>
              <div style="margin-top:12px">
                <?php
                  $cgpaVal   = (float)$student['cgpa'];
                  $cgpaColor = $cgpaVal >= 8.0 ? '#16a34a' : ($cgpaVal >= 6.0 ? '#d97706' : '#dc2626');
                ?>
                <span style="font-size:22px;font-weight:800;color:<?= $cgpaColor ?>"><?= $student['cgpa'] ?></span>
                <span style="font-size:12px;color:var(--muted)"> / 10 CGPA</span>
              </div>
              <?php if($student['resume']): ?>
                <div style="margin-top:14px">
                  <a href="../uploads/resumes/<?= htmlspecialchars($student['resume']) ?>" download
                     class="btn-sm success" style="font-size:12px">
                    <i class="fas fa-download"></i> Download Resume
                  </a>
                </div>
              <?php else: ?>
                <div style="margin-top:14px">
                  <a href="upload_resume.php" class="btn-sm amber" style="font-size:12px">
                    <i class="fas fa-upload"></i> Upload Resume
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Account info card -->
          <div class="card">
            <div class="card-body" style="padding:20px">
              <div class="section-title">
                <i class="fas fa-info-circle" style="color:var(--blue)"></i> Account Info
              </div>
              <div class="info-row">
                <span class="label">Student ID</span>
                <span class="value">#<?= $student['id'] ?></span>
              </div>
              <div class="info-row">
                <span class="label">Email</span>
                <span class="value" style="font-size:12px"><?= htmlspecialchars($student['email']) ?></span>
              </div>
              <div class="info-row">
                <span class="label">Department</span>
                <span class="value"><?= htmlspecialchars($student['department'] ?? '—') ?></span>
              </div>
              <div class="info-row">
                <span class="label">Applications</span>
                <span class="value" style="color:var(--blue)"><?= $applied ?></span>
              </div>
              <div class="info-row">
                <span class="label">Resume</span>
                <span class="value" style="color:<?= $student['resume'] ? '#16a34a' : '#dc2626' ?>">
                  <?= $student['resume'] ? 'Uploaded ✓' : 'Not uploaded' ?>
                </span>
              </div>
            </div>
          </div>

        </div>

        <!-- ── RIGHT: Edit forms ────────────────────────────────────────── -->
        <div style="display:flex;flex-direction:column;gap:20px">

          <!-- Profile details form -->
          <div class="card">
            <div class="card-header">
              <h3>
                <i class="fas fa-user-edit" style="color:var(--blue);margin-right:8px"></i>
                Personal Information
              </h3>
            </div>
            <div class="card-body" style="padding:24px">
              <form method="POST">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

                  <div class="form-group">
                    <label><i class="fas fa-user" style="color:var(--blue);margin-right:6px"></i>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>
                  </div>

                  <div class="form-group">
                    <label><i class="fas fa-envelope" style="color:var(--blue);margin-right:6px"></i>Email Address</label>
                    <input type="email" value="<?= htmlspecialchars($student['email']) ?>" disabled
                           style="opacity:.6;cursor:not-allowed">
                    <div class="form-hint">Email cannot be changed.</div>
                  </div>

                  <div class="form-group">
                    <label><i class="fas fa-building" style="color:var(--blue);margin-right:6px"></i>Department</label>
                    <input type="text" name="department" value="<?= htmlspecialchars($student['department'] ?? '') ?>"
                           placeholder="e.g. MCA, M.Sc CS, B.Tech CSE">
                  </div>

                  <div class="form-group">
                    <label><i class="fas fa-star" style="color:var(--blue);margin-right:6px"></i>CGPA</label>
                    <input type="number" name="cgpa" value="<?= $student['cgpa'] ?>"
                           step="0.01" min="0" max="10" required>
                    <div class="form-hint">Enter on a 10-point scale (e.g. 8.50)</div>
                  </div>

                </div>

                <div class="form-group">
                  <label><i class="fas fa-code" style="color:var(--blue);margin-right:6px"></i>Skills</label>
                  <textarea name="skills" placeholder="e.g. PHP, MySQL, Python, Machine Learning, React"><?= htmlspecialchars($student['skills'] ?? '') ?></textarea>
                  <div class="form-hint">Comma-separated skills — used for ML resume ranking.</div>
                </div>

                <div style="display:flex;justify-content:flex-end">
                  <button type="submit" name="update_profile"
                          style="padding:10px 28px;background:linear-gradient(135deg,var(--navy),var(--blue));color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;font-family:'DM Sans',sans-serif;cursor:pointer">
                    <i class="fas fa-save" style="margin-right:6px"></i> Save Changes
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Change password form -->
          <div class="card">
            <div class="card-header">
              <h3>
                <i class="fas fa-lock" style="color:var(--blue);margin-right:8px"></i>
                Change Password
              </h3>
            </div>
            <div class="card-body" style="padding:24px">
              <form method="POST">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">

                  <div class="form-group">
                    <label><i class="fas fa-key" style="color:var(--blue);margin-right:6px"></i>Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter current password" required>
                  </div>

                  <div class="form-group">
                    <label><i class="fas fa-lock" style="color:var(--blue);margin-right:6px"></i>New Password</label>
                    <input type="password" name="new_password" placeholder="Min. 6 characters" required>
                  </div>

                  <div class="form-group">
                    <label><i class="fas fa-check-double" style="color:var(--blue);margin-right:6px"></i>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Repeat new password" required>
                  </div>

                </div>

                <div style="display:flex;justify-content:flex-end">
                  <button type="submit" name="change_password"
                          style="padding:10px 28px;background:linear-gradient(135deg,#dc2626,#ef4444);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;font-family:'DM Sans',sans-serif;cursor:pointer">
                    <i class="fas fa-lock" style="margin-right:6px"></i> Update Password
                  </button>
                </div>
              </form>
            </div>
          </div>

        </div>
      </div>

    </div><!-- /dash-content -->
  </main>
</div><!-- /dash-layout -->
</body>
</html>