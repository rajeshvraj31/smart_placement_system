<?php
session_start();
include '../config.php';
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }
$success = '';
if(isset($_POST['add_job'])){
    $company  = mysqli_real_escape_string($conn, $_POST['company']);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);
    $cgpa_req = (float)$_POST['cgpa_required'];
    $skills   = mysqli_real_escape_string($conn, $_POST['skills']);
    mysqli_query($conn,"INSERT INTO jobs(company,role,cgpa_required,skills) VALUES('$company','$role','$cgpa_req','$skills')");
    $success = "Job drive added successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Job Drive — PlaceSync</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="dash-layout">
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand"><div class="icon"><i class="fas fa-graduation-cap"></i></div><span>PlaceSync</span></a>
    <div class="sidebar-user"><div class="user-avatar" style="background:linear-gradient(135deg,#1A3A2A,#1F5C3A)">A</div><div class="user-info"><div class="name">Admin Panel</div><div class="role">Placement Officer</div></div></div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Overview</div>
      <a href="dashboard.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
      <a href="analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i> Analytics</a>
      <div class="nav-section-label">Manage</div>
      <a href="students.php" class="nav-item"><i class="fas fa-users"></i> Students</a>
      <a href="add_job.php" class="nav-item active"><i class="fas fa-plus-circle"></i> Add Job Drive</a>
      <a href="jobs.php" class="nav-item"><i class="fas fa-briefcase"></i> Job Drives</a>
      <div class="nav-section-label">Placement</div>
      <a href="applications.php" class="nav-item"><i class="fas fa-file-alt"></i> Applications</a>
      <a href="shortlist.php" class="nav-item"><i class="fas fa-user-check"></i> Shortlisting</a>
      <div class="nav-section-label">AI Features</div>
      <a href="ml_ranking.php" class="nav-item"><i class="fas fa-robot"></i> ML Ranking</a>
      <a href="eligibility_report.php" class="nav-item"><i class="fas fa-filter"></i> Eligibility Report</a>
    </nav>
    <div class="sidebar-bottom"><a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
  </aside>
  <main class="dash-main">
    <div class="dash-header">
      <div><h1>Add Job Drive</h1><p class="breadcrumb">Admin &rarr; Job Drives &rarr; New</p></div>
    </div>
    <div class="dash-content">
      <div style="max-width:640px">
        <?php if($success): ?>
          <div class="alert alert-success animate"><i class="fas fa-check-circle"></i> <?= $success ?></div>
        <?php endif; ?>
        <div class="card">
          <div class="card-header"><h3><i class="fas fa-plus-circle" style="color:var(--blue);margin-right:8px"></i>New Job Drive Details</h3></div>
          <div class="card-body">
            <form method="POST">
              <div class="form-group"><label>Company Name</label><input type="text" name="company" placeholder="e.g. TCS, Infosys, Wipro" required></div>
              <div class="form-row">
                <div class="form-group"><label>Job Role / Position</label><input type="text" name="role" placeholder="e.g. Software Engineer" required></div>
                <div class="form-group"><label>Minimum CGPA Required</label><input type="number" name="cgpa_required" placeholder="e.g. 7.5" step="0.1" min="0" max="10" required></div>
              </div>
              <div class="form-group"><label>Required Skills (comma separated)</label><textarea name="skills" placeholder="e.g. PHP, MySQL, JavaScript, HTML, CSS"></textarea></div>
              <button type="submit" name="add_job" class="btn-primary"><i class="fas fa-plus-circle"></i> Add Job Drive</button>
            </form>
          </div>
        </div>
        <div class="card" style="margin-top:0">
          <div class="card-header"><h3>Existing Job Drives</h3><a href="jobs.php" class="btn-sm ghost">View All</a></div>
          <div style="overflow-x:auto">
            <table class="data-table">
              <thead><tr><th>#</th><th>Company</th><th>Role</th><th>Min CGPA</th></tr></thead>
              <tbody>
                <?php $jobs = mysqli_query($conn,"SELECT * FROM jobs ORDER BY id DESC LIMIT 5");
                while($j = mysqli_fetch_assoc($jobs)): ?>
                <tr>
                  <td style="color:var(--muted);font-size:13px"><?= $j['id'] ?></td>
                  <td style="font-weight:600"><?= htmlspecialchars($j['company']) ?></td>
                  <td><?= htmlspecialchars($j['role']) ?></td>
                  <td><span style="font-weight:700;color:var(--blue)"><?= $j['cgpa_required'] ?>+</span></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
</body>
</html>
