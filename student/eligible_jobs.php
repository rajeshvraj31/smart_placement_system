<?php
session_start();
include '../config.php';
if(!isset($_SESSION['student'])){ header("Location: login.php"); exit(); }
$email = $_SESSION['student'];
$student = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM students WHERE email='$email'"));
$sid = $student['id'];
$initial = strtoupper(substr($student['name'],0,1));
$jobs = mysqli_query($conn,"SELECT * FROM jobs WHERE cgpa_required <= {$student['cgpa']}");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Job Drives — PlaceSync</title>
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
      <a href="eligible_jobs.php" class="nav-item active"><i class="fas fa-briefcase"></i> Job Drives</a>
      <a href="my_applications.php" class="nav-item"><i class="fas fa-file-alt"></i> My Applications</a>
      <div class="nav-section-label">Profile</div>
      <a href="upload_resume.php" class="nav-item"><i class="fas fa-upload"></i> Upload Resume</a>
    </nav>
    <div class="sidebar-bottom"><a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
  </aside>
  <main class="dash-main">
    <div class="dash-header"><div><h1>Eligible Job Drives</h1><p class="breadcrumb">Student &rarr; Jobs (CGPA: <?= $student['cgpa'] ?>)</p></div></div>
    <div class="dash-content">
      <div class="jobs-grid animate">
        <?php $count=0; while($job = mysqli_fetch_assoc($jobs)): $count++;
          $applied = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM applications WHERE student_id='$sid' AND job_id='{$job['id']}'"));
        ?>
        <div class="job-card">
          <div class="job-card-top">
            <div><h4><?= htmlspecialchars($job['role']) ?></h4><div class="company"><?= htmlspecialchars($job['company']) ?></div></div>
            <div class="company-logo"><i class="fas fa-building"></i></div>
          </div>
          <div class="job-meta">
            <span><i class="fas fa-star"></i> Min CGPA: <?= $job['cgpa_required'] ?></span>
            <span><i class="fas fa-code"></i> <?= htmlspecialchars(substr($job['skills'],0,40)) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
            <span class="badge badge-eligible">✓ You're Eligible</span>
            <?php if($applied): ?>
              <span class="badge badge-applied">Applied ✓</span>
            <?php else: ?>
              <a href="apply_job.php?job_id=<?= $job['id'] ?>" class="btn-sm primary">Apply Now <i class="fas fa-arrow-right"></i></a>
            <?php endif; ?>
          </div>
        </div>
        <?php endwhile; ?>
        <?php if($count===0): ?>
          <div style="text-align:center;padding:60px;color:var(--muted);grid-column:1/-1">
            <i class="fas fa-briefcase" style="font-size:48px;display:block;margin-bottom:16px;opacity:0.25"></i>
            <h3 style="color:var(--navy);margin-bottom:8px">No eligible drives found</h3>
            <p>Drives with CGPA requirement above <?= $student['cgpa'] ?> are not shown. Update your profile to unlock more.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
</body></html>
