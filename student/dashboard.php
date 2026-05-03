<?php
session_start();
include '../config.php';
if(!isset($_SESSION['student'])){ header("Location: login.php"); exit(); }

$email   = $_SESSION['student'];
$student = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM students WHERE email='$email'"));
$sid     = $student['id'];
$initial = strtoupper(substr($student['name'], 0, 1));

// Stats
$totalJobs    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM jobs"))['c'];
$applied      = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications WHERE student_id='$sid'"))['c'];
$shortlisted  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications WHERE student_id='$sid' AND status='Shortlisted'"))['c'];
$selected     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications WHERE student_id='$sid' AND status='Selected'"))['c'];

// Eligible jobs (not yet applied)
$jobs = mysqli_query($conn,"SELECT * FROM jobs WHERE cgpa_required <= {$student['cgpa']} AND id NOT IN (SELECT job_id FROM applications WHERE student_id='$sid') LIMIT 6");

// Recent applications
$apps = mysqli_query($conn,"
  SELECT a.*, j.company, j.role FROM applications a
  JOIN jobs j ON a.job_id = j.id
  WHERE a.student_id='$sid' ORDER BY a.id DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard — PlaceSync</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="dash-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand">
      <div class="icon"><i class="fas fa-graduation-cap"></i></div>
      <span>PlaceSync</span>
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
      <a href="dashboard.php" class="nav-item active"><i class="fas fa-th-large"></i> Dashboard</a>
      <a href="eligible_jobs.php" class="nav-item"><i class="fas fa-briefcase"></i> Job Drives <span class="badge"><?= $totalJobs ?></span></a>
      <a href="my_applications.php" class="nav-item"><i class="fas fa-file-alt"></i> My Applications <span class="badge"><?= $applied ?></span></a>
      <div class="nav-section-label">Profile</div>
      <a href="upload_resume.php" class="nav-item"><i class="fas fa-upload"></i> Upload Resume</a>
      <a href="profile.php" class="nav-item"><i class="fas fa-user-edit"></i> Edit Profile</a>
    </nav>
    <div class="sidebar-bottom">
      <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="dash-main">
    <div class="dash-header">
      <div>
        <h1>Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?>, <?= htmlspecialchars(explode(' ', $student['name'])[0]) ?> 👋</h1>
        <p class="breadcrumb">Dashboard &rarr; Overview</p>
      </div>
      <div class="header-actions">
        <a href="upload_resume.php" class="icon-btn" title="Upload Resume"><i class="fas fa-upload"></i></a>
        <a href="eligible_jobs.php" class="icon-btn" title="View Jobs"><i class="fas fa-briefcase"></i></a>
      </div>
    </div>

    <div class="dash-content">

      <!-- STAT CARDS -->
      <div class="stats-row animate">
        <div class="stat-card blue">
          <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
          <div class="stat-label">Available Drives</div>
          <div class="stat-value"><?= $totalJobs ?></div>
          <div class="stat-sub">Active job drives</div>
        </div>
        <div class="stat-card amber">
          <div class="stat-icon"><i class="fas fa-paper-plane"></i></div>
          <div class="stat-label">Applied</div>
          <div class="stat-value"><?= $applied ?></div>
          <div class="stat-sub">Total applications</div>
        </div>
        <div class="stat-card green">
          <div class="stat-icon"><i class="fas fa-star"></i></div>
          <div class="stat-label">Shortlisted</div>
          <div class="stat-value"><?= $shortlisted ?></div>
          <div class="stat-sub">Rounds qualified</div>
        </div>
        <div class="stat-card red">
          <div class="stat-icon"><i class="fas fa-trophy"></i></div>
          <div class="stat-label">Selected</div>
          <div class="stat-value"><?= $selected ?></div>
          <div class="stat-sub">Offers received</div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;">

        <!-- ELIGIBLE JOBS -->
        <div>
          <div class="card">
            <div class="card-header">
              <h3><i class="fas fa-briefcase" style="color:var(--blue);margin-right:8px"></i>Eligible Job Drives</h3>
              <a href="eligible_jobs.php" class="btn-sm ghost">View All</a>
            </div>
            <div class="card-body">
              <?php if(mysqli_num_rows($jobs) > 0): ?>
              <div class="jobs-grid">
                <?php while($job = mysqli_fetch_assoc($jobs)): ?>
                <div class="job-card">
                  <div class="job-card-top">
                    <div>
                      <h4><?= htmlspecialchars($job['role']) ?></h4>
                      <div class="company"><?= htmlspecialchars($job['company']) ?></div>
                    </div>
                    <div class="company-logo"><i class="fas fa-building"></i></div>
                  </div>
                  <div class="job-meta">
                    <span><i class="fas fa-star"></i> CGPA: <?= $job['cgpa_required'] ?>+</span>
                    <span><i class="fas fa-code"></i> <?= htmlspecialchars(substr($job['skills'],0,30)) ?>...</span>
                  </div>
                  <span class="badge badge-eligible">✓ Eligible</span>
                  <a href="apply_job.php?job_id=<?= $job['id'] ?>" class="btn-sm primary" style="float:right;margin-top:-24px">Apply <i class="fas fa-arrow-right"></i></a>
                </div>
                <?php endwhile; ?>
              </div>
              <?php else: ?>
                <div style="text-align:center;padding:40px;color:var(--muted);">
                  <i class="fas fa-briefcase" style="font-size:40px;margin-bottom:12px;display:block;opacity:0.3"></i>
                  No eligible drives available at this time.
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- RECENT APPLICATIONS TABLE -->
          <div class="card">
            <div class="card-header">
              <h3><i class="fas fa-file-alt" style="color:var(--blue);margin-right:8px"></i>Recent Applications</h3>
              <a href="my_applications.php" class="btn-sm ghost">View All</a>
            </div>
            <div style="overflow-x:auto;">
              <table class="data-table">
                <thead>
                  <tr><th>Company</th><th>Role</th><th>Status</th></tr>
                </thead>
                <tbody>
                  <?php while($app = mysqli_fetch_assoc($apps)): ?>
                  <tr>
                    <td><?= htmlspecialchars($app['company']) ?></td>
                    <td><?= htmlspecialchars($app['role']) ?></td>
                    <td>
                      <?php
                        $cls = ['Applied'=>'badge-applied','Shortlisted'=>'badge-shortlist','Selected'=>'badge-selected','Rejected'=>'badge-rejected'];
                        $c = $cls[$app['status']] ?? 'badge-applied';
                      ?>
                      <span class="badge <?= $c ?>"><?= $app['status'] ?></span>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                  <?php if($applied == 0): ?>
                  <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:24px;">No applications yet. Start applying!</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- PROFILE SIDEBAR -->
        <div>
          <div class="profile-card" style="margin-bottom:20px;">
            <div class="profile-avatar"><?= $initial ?></div>
            <h3><?= htmlspecialchars($student['name']) ?></h3>
            <p class="dept"><?= htmlspecialchars($student['department'] ?? 'M.Sc. Computer Science') ?></p>
            <div class="profile-stats">
              <div class="pstat"><div class="val"><?= $student['cgpa'] ?></div><div class="lbl">CGPA</div></div>
              <div class="pstat"><div class="val"><?= $applied ?></div><div class="lbl">Applied</div></div>
            </div>
          </div>

          <div class="card">
            <div class="card-header"><h3>My Skills</h3></div>
            <div class="card-body">
              <?php foreach(explode(',', $student['skills']) as $skill): ?>
                <span style="display:inline-block;background:var(--light);border:1px solid var(--border);border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;margin:3px;color:var(--navy)"><?= trim(htmlspecialchars($skill)) ?></span>
              <?php endforeach; ?>
              <div style="margin-top:16px;">
                <a href="upload_resume.php" class="btn-sm primary" style="width:100%;justify-content:center;">
                  <?= $student['resume'] ? '<i class="fas fa-redo"></i> Update Resume' : '<i class="fas fa-upload"></i> Upload Resume' ?>
                </a>
              </div>
            </div>
          </div>

          
        </div>
      </div>

    </div>
  </main>
</div>
</body>
</html>
