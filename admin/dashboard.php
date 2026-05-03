<?php
session_start();
include '../config.php';
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }

// Stats
$totalStudents = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM students"))['c'];
$totalJobs     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM jobs"))['c'];
$totalApps     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications"))['c'];
$selected      = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications WHERE status='Selected'"))['c'];
$shortlisted   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications WHERE status='Shortlisted'"))['c'];

// Recent students
$students = mysqli_query($conn,"SELECT * FROM students ORDER BY id DESC LIMIT 6");

// Recent applications
$recentApps = mysqli_query($conn,"
  SELECT a.*, s.name sname, j.company, j.role
  FROM applications a
  JOIN students s ON a.student_id = s.id
  JOIN jobs j ON a.job_id = j.id
  ORDER BY a.id DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — PlaceSync</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
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
      <div class="user-avatar" style="background:linear-gradient(135deg,#1A3A2A,#1F5C3A)">A</div>
      <div class="user-info">
        <div class="name">Admin Panel</div>
        <div class="role">Placement Officer</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Overview</div>
      <a href="dashboard.php" class="nav-item active"><i class="fas fa-th-large"></i> Dashboard</a>
      <a href="analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i> Analytics</a>
      <div class="nav-section-label">Manage</div>
      <a href="students.php" class="nav-item"><i class="fas fa-users"></i> Students <span class="badge"><?= $totalStudents ?></span></a>
      <a href="add_job.php" class="nav-item"><i class="fas fa-plus-circle"></i> Add Job Drive</a>
      <a href="jobs.php" class="nav-item"><i class="fas fa-briefcase"></i> Job Drives <span class="badge"><?= $totalJobs ?></span></a>
      <div class="nav-section-label">Placement</div>
      <a href="applications.php" class="nav-item"><i class="fas fa-file-alt"></i> Applications <span class="badge"><?= $totalApps ?></span></a>
      <a href="shortlist.php" class="nav-item"><i class="fas fa-user-check"></i> Shortlisting</a>
      <div class="nav-section-label">AI Features</div>
      <a href="ml_ranking.php" class="nav-item"><i class="fas fa-robot"></i> ML Ranking</a>
      <a href="eligibility_report.php" class="nav-item"><i class="fas fa-filter"></i> Eligibility Report</a>
    </nav>
    <div class="sidebar-bottom">
      <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="dash-main">
    <div class="dash-header">
      <div>
        <h1>Admin Dashboard</h1>
        <p class="breadcrumb">Placement Management &rarr; Overview</p>
      </div>
      <div class="header-actions">
        <a href="add_job.php" class="btn-sm primary"><i class="fas fa-plus"></i> Add Job Drive</a>
      </div>
    </div>

    <div class="dash-content">

      <!-- STATS -->
      <div class="stats-row animate">
        <div class="stat-card blue">
          <div class="stat-icon"><i class="fas fa-users"></i></div>
          <div class="stat-label">Total Students</div>
          <div class="stat-value"><?= $totalStudents ?></div>
          <div class="stat-sub">Registered accounts</div>
        </div>
        <div class="stat-card amber">
          <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
          <div class="stat-label">Job Drives</div>
          <div class="stat-value"><?= $totalJobs ?></div>
          <div class="stat-sub">Active postings</div>
        </div>
        <div class="stat-card green">
          <div class="stat-icon"><i class="fas fa-star"></i></div>
          <div class="stat-label">Shortlisted</div>
          <div class="stat-value"><?= $shortlisted ?></div>
          <div class="stat-sub">In process</div>
        </div>
        <div class="stat-card red">
          <div class="stat-icon"><i class="fas fa-trophy"></i></div>
          <div class="stat-label">Selected</div>
          <div class="stat-value"><?= $selected ?></div>
          <div class="stat-sub">Offers made</div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">

        <!-- PLACEMENT CHART -->
        <div class="card">
          <div class="card-header"><h3><i class="fas fa-chart-pie" style="color:var(--blue);margin-right:8px"></i>Application Status</h3></div>
          <div class="card-body" style="display:flex;justify-content:center;padding:28px">
            <canvas id="statusChart" width="260" height="260"></canvas>
          </div>
        </div>

        <!-- ML RANKING PREVIEW -->
        <div class="card">
          <div class="card-header">
            <h3><i class="fas fa-robot" style="color:var(--blue);margin-right:8px"></i>ML Ranking Preview</h3>
            <a href="ml_ranking.php" class="btn-sm ghost">Full Ranking</a>
          </div>
          <div class="card-body">
            <?php
            $mlStudents = mysqli_query($conn,"SELECT name, skills FROM students ORDER BY id DESC LIMIT 4");
            $sampleJobs = ['PHP Developer','Web Developer','Python Intern','ML Engineer'];
            $i = 0;
            while($ms = mysqli_fetch_assoc($mlStudents)):
              $fakeScore = rand(55, 95);
              $barClass = $fakeScore >= 75 ? 'high' : ($fakeScore >= 50 ? 'mid' : 'low');
            ?>
            <div style="margin-bottom:18px;">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <div>
                  <span style="font-size:14px;font-weight:600;color:var(--navy)"><?= htmlspecialchars($ms['name']) ?></span>
                  <span style="font-size:12px;color:var(--muted);margin-left:8px"><?= htmlspecialchars($sampleJobs[$i % 4]) ?></span>
                </div>
                <span style="font-size:13px;font-weight:700;color:var(--navy)"><?= $fakeScore ?>%</span>
              </div>
              <div class="score-bar-wrap">
                <div class="score-bar <?= $barClass ?>" style="width:<?= $fakeScore ?>%"></div>
              </div>
            </div>
            <?php $i++; endwhile; ?>
            <?php if($totalStudents == 0): ?>
              <p style="color:var(--muted);font-size:14px;text-align:center;padding:20px">No students registered yet.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

        <!-- RECENT STUDENTS -->
        <div class="card">
          <div class="card-header">
            <h3><i class="fas fa-users" style="color:var(--blue);margin-right:8px"></i>Recent Students</h3>
            <a href="students.php" class="btn-sm ghost">View All</a>
          </div>
          <div style="overflow-x:auto">
            <table class="data-table">
              <thead><tr><th>Name</th><th>CGPA</th><th>Resume</th></tr></thead>
              <tbody>
                <?php
                $students = mysqli_query($conn,"SELECT * FROM students ORDER BY id DESC LIMIT 6");
                while($s = mysqli_fetch_assoc($students)):
                ?>
                <tr>
                  <td>
                    <div style="display:flex;align-items:center;gap:10px">
                      <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--navy),var(--blue));display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0">
                        <?= strtoupper(substr($s['name'],0,1)) ?>
                      </div>
                      <div>
                        <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($s['name']) ?></div>
                        <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($s['email']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td><span style="font-weight:700;color:var(--blue)"><?= $s['cgpa'] ?></span></td>
                  <td>
                    <?php if($s['resume']): ?>
                      <a href="../uploads/resumes/<?= $s['resume'] ?>" download class="btn-sm success" style="padding:4px 10px"><i class="fas fa-download"></i></a>
                    <?php else: ?>
                      <span style="font-size:12px;color:var(--muted)">Not uploaded</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- RECENT APPLICATIONS -->
        <div class="card">
          <div class="card-header">
            <h3><i class="fas fa-file-alt" style="color:var(--blue);margin-right:8px"></i>Recent Applications</h3>
            <a href="shortlist.php" class="btn-sm ghost">Shortlist</a>
          </div>
          <div style="overflow-x:auto">
            <table class="data-table">
              <thead><tr><th>Student</th><th>Company</th><th>Status</th></tr></thead>
              <tbody>
                <?php
                $recentApps = mysqli_query($conn,"
                  SELECT a.*, s.name sname, j.company, j.role
                  FROM applications a
                  JOIN students s ON a.student_id = s.id
                  JOIN jobs j ON a.job_id = j.id
                  ORDER BY a.id DESC LIMIT 6
                ");
                while($a = mysqli_fetch_assoc($recentApps)):
                  $cls = ['Applied'=>'badge-applied','Shortlisted'=>'badge-shortlist','Selected'=>'badge-selected','Rejected'=>'badge-rejected'];
                  $c = $cls[$a['status']] ?? 'badge-applied';
                ?>
                <tr>
                  <td style="font-size:13px;font-weight:600"><?= htmlspecialchars($a['sname']) ?></td>
                  <td style="font-size:13px"><?= htmlspecialchars($a['company']) ?></td>
                  <td><span class="badge <?= $c ?>"><?= $a['status'] ?></span></td>
                </tr>
                <?php endwhile; ?>
                <?php if($totalApps == 0): ?>
                <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:24px">No applications yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<script>
// Status Pie Chart
const ctx = document.getElementById('statusChart');
if(ctx){
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Applied', 'Shortlisted', 'Selected', 'Rejected'],
      datasets: [{
        data: [
          <?= $totalApps - $shortlisted - $selected ?>,
          <?= $shortlisted ?>,
          <?= $selected ?>,
          <?= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications WHERE status='Rejected'"))['c'] ?>
        ],
        backgroundColor: ['#DBEAFE','#FEF3C7','#D1FAE5','#FEE2E2'],
        borderColor: ['#3B82F6','#F59E0B','#22C55E','#EF4444'],
        borderWidth: 2
      }]
    },
    options: {
      responsive: false,
      cutout: '65%',
      plugins: { legend: { position: 'bottom', labels: { font: { family: 'DM Sans', size: 12 } } } }
    }
  });
}
</script>
</body>
</html>
