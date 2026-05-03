<?php
session_start();
include '../config.php';
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }

$totalStudents = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM students"))['c'];
$totalJobs     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM jobs"))['c'];
$totalApps     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications"))['c'];
$selected      = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications WHERE status='Selected'"))['c'];
$shortlisted   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications WHERE status='Shortlisted'"))['c'];
$rejected      = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications WHERE status='Rejected'"))['c'];
$applied_only  = $totalApps - $shortlisted - $selected - $rejected;

$successRate = $totalApps > 0 ? round(($selected / $totalApps) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics — PlaceSync</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
</head>
<body>
<div class="dash-layout">
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand"><div class="icon"><i class="fas fa-graduation-cap"></i></div><span>PlaceSync</span></a>
    <div class="sidebar-user"><div class="user-avatar" style="background:linear-gradient(135deg,#1A3A2A,#1F5C3A)">A</div><div class="user-info"><div class="name">Admin Panel</div><div class="role">Placement Officer</div></div></div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Overview</div>
      <a href="dashboard.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
      <a href="analytics.php" class="nav-item active"><i class="fas fa-chart-bar"></i> Analytics</a>
      <div class="nav-section-label">Manage</div>
      <a href="students.php" class="nav-item"><i class="fas fa-users"></i> Students</a>
      <a href="add_job.php" class="nav-item"><i class="fas fa-plus-circle"></i> Add Job Drive</a>
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
      <div><h1>Analytics Dashboard</h1><p class="breadcrumb">Admin &rarr; Analytics &rarr; Overview</p></div>
    </div>
    <div class="dash-content">

      <!-- TOP STATS -->
      <div class="stats-row animate">
        <div class="stat-card blue">
          <div class="stat-icon"><i class="fas fa-users"></i></div>
          <div class="stat-label">Registered Students</div>
          <div class="stat-value"><?= $totalStudents ?></div>
        </div>
        <div class="stat-card amber">
          <div class="stat-icon"><i class="fas fa-paper-plane"></i></div>
          <div class="stat-label">Total Applications</div>
          <div class="stat-value"><?= $totalApps ?></div>
        </div>
        <div class="stat-card green">
          <div class="stat-icon"><i class="fas fa-trophy"></i></div>
          <div class="stat-label">Placement Rate</div>
          <div class="stat-value"><?= $successRate ?>%</div>
        </div>
        <div class="stat-card red">
          <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
          <div class="stat-label">Active Drives</div>
          <div class="stat-value"><?= $totalJobs ?></div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
        <!-- STATUS BAR CHART -->
        <div class="card">
          <div class="card-header"><h3><i class="fas fa-chart-bar" style="color:var(--blue);margin-right:8px"></i>Application Status Breakdown</h3></div>
          <div class="card-body"><canvas id="barChart" height="220"></canvas></div>
        </div>
        <!-- PIE CHART -->
        <div class="card">
          <div class="card-header"><h3><i class="fas fa-chart-pie" style="color:var(--blue);margin-right:8px"></i>Placement Outcome</h3></div>
          <div class="card-body" style="display:flex;justify-content:center"><canvas id="pieChart" width="240" height="240"></canvas></div>
        </div>
      </div>

      <!-- SUMMARY TABLE -->
      <div class="card">
        <div class="card-header"><h3><i class="fas fa-table" style="color:var(--blue);margin-right:8px"></i>Placement Summary</h3></div>
        <div style="overflow-x:auto">
          <table class="data-table">
            <thead><tr><th>Metric</th><th>Count</th><th>Percentage</th></tr></thead>
            <tbody>
              <tr><td>Total Students</td><td><?= $totalStudents ?></td><td>—</td></tr>
              <tr><td>Total Job Drives</td><td><?= $totalJobs ?></td><td>—</td></tr>
              <tr><td>Total Applications</td><td><?= $totalApps ?></td><td>100%</td></tr>
              <tr><td>Applied (In Review)</td><td><?= $applied_only ?></td><td><?= $totalApps ? round($applied_only/$totalApps*100,1) : 0 ?>%</td></tr>
              <tr><td>Shortlisted</td><td><?= $shortlisted ?></td><td><?= $totalApps ? round($shortlisted/$totalApps*100,1) : 0 ?>%</td></tr>
              <tr><td style="color:var(--success);font-weight:700">Selected (Placed)</td><td style="color:var(--success);font-weight:700"><?= $selected ?></td><td style="color:var(--success);font-weight:700"><?= $successRate ?>%</td></tr>
              <tr><td>Rejected</td><td><?= $rejected ?></td><td><?= $totalApps ? round($rejected/$totalApps*100,1) : 0 ?>%</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
<script>
const bar = document.getElementById('barChart');
if(bar){
  new Chart(bar, {
    type: 'bar',
    data: {
      labels: ['Applied', 'Shortlisted', 'Selected', 'Rejected'],
      datasets: [{
        label: 'Students',
        data: [<?= $applied_only ?>, <?= $shortlisted ?>, <?= $selected ?>, <?= $rejected ?>],
        backgroundColor: ['#DBEAFE','#FEF3C7','#D1FAE5','#FEE2E2'],
        borderColor: ['#3B82F6','#F59E0B','#22C55E','#EF4444'],
        borderWidth: 2, borderRadius: 8
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
  });
}
const pie = document.getElementById('pieChart');
if(pie){
  new Chart(pie, {
    type: 'doughnut',
    data: {
      labels: ['Applied', 'Shortlisted', 'Selected', 'Rejected'],
      datasets: [{
        data: [<?= $applied_only ?>, <?= $shortlisted ?>, <?= $selected ?>, <?= $rejected ?>],
        backgroundColor: ['#3B82F6','#F59E0B','#22C55E','#EF4444'],
        borderWidth: 3, borderColor: '#fff'
      }]
    },
    options: {
      responsive: false, cutout: '60%',
      plugins: { legend: { position: 'bottom', labels: { font: { family: 'DM Sans', size: 12 } } } }
    }
  });
}
</script>
</body>
</html>
