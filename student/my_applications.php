<?php
session_start();
include '../config.php';
if(!isset($_SESSION['student'])){ header("Location: login.php"); exit(); }
$email = $_SESSION['student'];
$student = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM students WHERE email='$email'"));
$sid = $student['id'];
$initial = strtoupper(substr($student['name'],0,1));
$apps = mysqli_query($conn,"SELECT a.*, j.company, j.role, j.cgpa_required FROM applications a JOIN jobs j ON a.job_id=j.id WHERE a.student_id='$sid' ORDER BY a.id DESC");
$msg = isset($_GET['msg']) ? "Application submitted successfully!" : '';
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>My Applications — PlaceSync</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="dash-layout">
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand"><div class="icon"><i class="fas fa-graduation-cap"></i></div><span>PlaceSync</span></a>
    <div class="sidebar-user"><div class="user-avatar"><?= $initial ?></div><div class="user-info"><div class="name"><?= htmlspecialchars($student['name']) ?></div><div class="role">Student Portal</div></div></div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>
      <a href="dashboard.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
      <a href="eligible_jobs.php" class="nav-item"><i class="fas fa-briefcase"></i> Job Drives</a>
      <a href="my_applications.php" class="nav-item active"><i class="fas fa-file-alt"></i> My Applications</a>
      <div class="nav-section-label">Profile</div>
      <a href="upload_resume.php" class="nav-item"><i class="fas fa-upload"></i> Upload Resume</a>
    </nav>
    <div class="sidebar-bottom"><a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
  </aside>
  <main class="dash-main">
    <div class="dash-header"><div><h1>My Applications</h1><p class="breadcrumb">Student &rarr; Applications</p></div></div>
    <div class="dash-content">
      <?php if($msg): ?><div class="alert alert-success animate"><i class="fas fa-check-circle"></i> <?= $msg ?></div><?php endif; ?>
      <div class="card">
        <div class="card-header"><h3><i class="fas fa-file-alt" style="color:var(--blue);margin-right:8px"></i>All Applications</h3></div>
        <div style="overflow-x:auto">
          <table class="data-table">
            <thead><tr><th>Company</th><th>Role</th><th>Required CGPA</th><th>Status</th></tr></thead>
            <tbody>
              <?php $count = 0; while($app = mysqli_fetch_assoc($apps)): $count++;
                $cls=['Applied'=>'badge-applied','Shortlisted'=>'badge-shortlist','Selected'=>'badge-selected','Rejected'=>'badge-rejected'];
                $c=$cls[$app['status']]??'badge-applied'; ?>
              <tr>
                <td style="font-weight:600"><?= htmlspecialchars($app['company']) ?></td>
                <td><?= htmlspecialchars($app['role']) ?></td>
                <td><span style="font-weight:700;color:var(--blue)"><?= $app['cgpa_required'] ?>+</span></td>
                <td><span class="badge <?= $c ?>"><?= $app['status'] ?></span></td>
              </tr>
              <?php endwhile; ?>
              <?php if($count === 0): ?>
              <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--muted)"><i class="fas fa-file-alt" style="font-size:32px;display:block;margin-bottom:12px;opacity:0.3"></i>No applications yet. <a href="eligible_jobs.php">Browse jobs</a></td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
</body></html>
