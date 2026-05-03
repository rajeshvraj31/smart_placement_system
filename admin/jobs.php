<?php
session_start();
include '../config.php';
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }

/* ── Delete job ─────────────────────────────────────────────────────────── */
if(isset($_GET['delete'])){
    $jid = (int)$_GET['delete'];
    mysqli_query($conn,"DELETE FROM jobs WHERE id=$jid");
    header("Location: jobs.php?msg=deleted");
    exit();
}

$msg = '';
if(isset($_GET['msg'])){
    if($_GET['msg'] === 'deleted') $msg = 'Job drive deleted successfully.';
}

/* ── Search & Filter ─────────────────────────────────────────────────────── */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$cgpa   = isset($_GET['cgpa'])   ? $_GET['cgpa']         : 'all';

$where = [];
if($search !== '')
    $where[] = "(company LIKE '%" . mysqli_real_escape_string($conn,$search) . "%'
              OR role    LIKE '%" . mysqli_real_escape_string($conn,$search) . "%'
              OR skills  LIKE '%" . mysqli_real_escape_string($conn,$search) . "%')";

if($cgpa === 'low')  $where[] = "cgpa_required < 6.0";
if($cgpa === 'mid')  $where[] = "cgpa_required >= 6.0 AND cgpa_required < 8.0";
if($cgpa === 'high') $where[] = "cgpa_required >= 8.0";

$sql  = "SELECT * FROM jobs" . (count($where) ? " WHERE ".implode(" AND ",$where) : "") . " ORDER BY id DESC";
$jobs = mysqli_query($conn, $sql);
$total= mysqli_num_rows($jobs);

/* ── Stats ───────────────────────────────────────────────────────────────── */
$totalAll  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM jobs"))['c'];
$totalApps = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications"))['c'];
$companies = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(DISTINCT company) c FROM jobs"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Job Drives — PlaceSync</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="dash-layout">

  <!-- ── SIDEBAR ──────────────────────────────────────────────────────── -->
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand">
      <div class="icon"><i class="fas fa-graduation-cap"></i></div><span>PlaceSync</span>
    </a>
    <div class="sidebar-user">
      <div class="user-avatar" style="background:linear-gradient(135deg,#1A3A2A,#1F5C3A)">A</div>
      <div class="user-info"><div class="name">Admin Panel</div><div class="role">Placement Officer</div></div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Overview</div>
      <a href="dashboard.php"  class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
      <a href="analytics.php"  class="nav-item"><i class="fas fa-chart-bar"></i> Analytics</a>
      <div class="nav-section-label">Manage</div>
      <a href="students.php"   class="nav-item"><i class="fas fa-users"></i> Students</a>
      <a href="add_job.php"    class="nav-item"><i class="fas fa-plus-circle"></i> Add Job Drive</a>
      <a href="jobs.php"       class="nav-item active"><i class="fas fa-briefcase"></i> Job Drives</a>
      <div class="nav-section-label">Placement</div>
      <a href="applications.php" class="nav-item"><i class="fas fa-file-alt"></i> Applications</a>
      <a href="shortlist.php"    class="nav-item"><i class="fas fa-user-check"></i> Shortlisting</a>
      <div class="nav-section-label">AI Features</div>
      <a href="ml_ranking.php"         class="nav-item"><i class="fas fa-robot"></i> ML Ranking</a>
      <a href="eligibility_report.php" class="nav-item"><i class="fas fa-filter"></i> Eligibility Report</a>
    </nav>
    <div class="sidebar-bottom">
      <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </aside>

  <!-- ── MAIN ─────────────────────────────────────────────────────────── -->
  <main class="dash-main">
    <div class="dash-header">
      <div>
        <h1>Job Drives</h1>
        <p class="breadcrumb">Admin &rarr; Manage &rarr; Job Drives</p>
      </div>
      <a href="add_job.php" class="btn-sm primary" style="padding:10px 20px;font-size:13px">
        <i class="fas fa-plus"></i> Add New Drive
      </a>
    </div>

    <div class="dash-content">

      <?php if($msg): ?>
        <div class="alert alert-success animate">
          <i class="fas fa-check-circle"></i> <?= $msg ?>
        </div>
      <?php endif; ?>

      <!-- ── Stat cards ──────────────────────────────────────────────── -->
      <div class="stats-grid" style="margin-bottom:24px">

        <div class="stat-card">
          <div class="stat-icon" style="background:linear-gradient(135deg,var(--navy),var(--blue))">
            <i class="fas fa-briefcase"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number"><?= $totalAll ?></div>
            <div class="stat-label">Total Drives</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background:linear-gradient(135deg,#0d9488,#14b8a6)">
            <i class="fas fa-building"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number" style="color:#0d9488"><?= $companies ?></div>
            <div class="stat-label">Companies</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
            <i class="fas fa-file-alt"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number" style="color:#7c3aed"><?= $totalApps ?></div>
            <div class="stat-label">Total Applications</div>
          </div>
        </div>

      </div>

      <!-- ── Search & Filter bar ─────────────────────────────────────── -->
      <div class="card" style="margin-bottom:20px">
        <div class="card-body" style="padding:16px 20px">
          <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">

            <!-- Search -->
            <div style="flex:1;min-width:220px;position:relative">
              <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px"></i>
              <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                     placeholder="Search by company, role or skill…"
                     style="width:100%;padding:9px 12px 9px 34px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;background:var(--light);box-sizing:border-box">
            </div>

            <!-- CGPA Required filter -->
            <select name="cgpa"
                    style="padding:9px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;background:var(--light)">
              <option value="all"  <?= $cgpa==='all'  ? 'selected':'' ?>>All CGPA</option>
              <option value="low"  <?= $cgpa==='low'  ? 'selected':'' ?>>Low  (&lt; 6.0)</option>
              <option value="mid"  <?= $cgpa==='mid'  ? 'selected':'' ?>>Mid  (6.0 – 7.9)</option>
              <option value="high" <?= $cgpa==='high' ? 'selected':'' ?>>High (&ge; 8.0)</option>
            </select>

            <button type="submit" class="btn-sm primary">
              <i class="fas fa-search"></i> Search
            </button>

            <?php if($search !== '' || $cgpa !== 'all'): ?>
              <a href="jobs.php"
                 style="padding:7px 14px;border-radius:8px;background:var(--light);color:var(--muted);border:1.5px solid var(--border);font-size:13px;text-decoration:none;font-family:'DM Sans',sans-serif">
                <i class="fas fa-times"></i> Clear
              </a>
            <?php endif; ?>

            <span style="font-size:12px;color:var(--muted);margin-left:auto">
              Showing <strong><?= $total ?></strong> of <strong><?= $totalAll ?></strong> drives
            </span>

          </form>
        </div>
      </div>

      <!-- ── Table ───────────────────────────────────────────────────── -->
      <div class="card">
        <div class="card-header">
          <h3>
            <i class="fas fa-briefcase" style="color:var(--blue);margin-right:8px"></i>
            All Job Drives
          </h3>
          <a href="add_job.php" class="btn-sm primary">
            <i class="fas fa-plus"></i> Add Drive
          </a>
        </div>
        <div style="overflow-x:auto">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Company</th>
                <th>Role</th>
                <th>Min CGPA</th>
                <th>Required Skills</th>
                <th>Applications</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if($total === 0): ?>
              <tr>
                <td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">
                  <i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px"></i>
                  No job drives found.
                  <a href="add_job.php" style="color:var(--blue);display:block;margin-top:6px;font-size:13px">
                    + Add your first drive
                  </a>
                </td>
              </tr>
            <?php else: ?>
              <?php $i = 1; while($job = mysqli_fetch_assoc($jobs)):
                /* Count applications for this job */
                $appCount = mysqli_fetch_assoc(mysqli_query($conn,
                    "SELECT COUNT(*) c FROM applications WHERE job_id={$job['id']}"))['c'];
                /* CGPA badge colour */
                $cr = (float)$job['cgpa_required'];
                $cgpaColor = $cr >= 8.0 ? '#16a34a' : ($cr >= 6.0 ? '#d97706' : '#2563eb');
              ?>
              <tr>

                <!-- # -->
                <td style="color:var(--muted);font-size:13px;text-align:center"><?= $i++ ?></td>

                <!-- Company -->
                <td>
                  <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,var(--navy),var(--blue));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0">
                      <?= strtoupper(substr($job['company'],0,1)) ?>
                    </div>
                    <span style="font-weight:600;font-size:13px"><?= htmlspecialchars($job['company']) ?></span>
                  </div>
                </td>

                <!-- Role -->
                <td style="font-size:13px;font-weight:500"><?= htmlspecialchars($job['role']) ?></td>

                <!-- Min CGPA -->
                <td>
                  <span style="font-weight:700;font-size:14px;color:<?= $cgpaColor ?>">
                    <?= $job['cgpa_required'] ?>
                  </span>
                </td>

                <!-- Skills -->
                <td style="font-size:12px;color:var(--muted)">
                  <?= htmlspecialchars(substr($job['skills'] ?? '—', 0, 50)) ?><?= strlen($job['skills'] ?? '') > 50 ? '…' : '' ?>
                </td>

                <!-- Applications count -->
                <td style="text-align:center">
                  <span style="background:var(--light);border:1.5px solid var(--border);color:var(--blue);font-size:12px;font-weight:700;padding:3px 12px;border-radius:20px">
                    <?= $appCount ?>
                  </span>
                </td>

                <!-- Actions -->
                <td>
                  <div style="display:flex;gap:6px;align-items:center">
                    <a href="shortlist.php" class="btn-sm primary" title="View Applications">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="jobs.php?delete=<?= $job['id'] ?>"
                       class="btn-sm"
                       style="background:#fee2e2;color:#dc2626;border:1.5px solid #fca5a5"
                       title="Delete Drive"
                       onclick="return confirm('Delete this job drive? This will also remove all related applications.')">
                      <i class="fas fa-trash"></i>
                    </a>
                  </div>
                </td>

              </tr>
              <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /dash-content -->
  </main>
</div><!-- /dash-layout -->
</body>
</html>