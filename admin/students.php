<?php
session_start();
include '../config.php';
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }

/* ── Search & Filter ─────────────────────────────────────────────────────── */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$cgpa   = isset($_GET['cgpa'])   ? $_GET['cgpa']         : 'all'; // all | high | mid | low
$dept   = isset($_GET['dept'])   ? trim($_GET['dept'])    : '';

/* Build WHERE clause */
$where = [];
if($search !== '')
    $where[] = "(name LIKE '%" . mysqli_real_escape_string($conn,$search) . "%'
              OR email LIKE '%" . mysqli_real_escape_string($conn,$search) . "%'
              OR skills LIKE '%" . mysqli_real_escape_string($conn,$search) . "%')";

if($cgpa === 'high') $where[] = "cgpa >= 8.0";
if($cgpa === 'mid')  $where[] = "cgpa >= 6.0 AND cgpa < 8.0";
if($cgpa === 'low')  $where[] = "cgpa < 6.0";

if($dept !== '')
    $where[] = "department LIKE '%" . mysqli_real_escape_string($conn,$dept) . "%'";

$sql = "SELECT * FROM students" . (count($where) ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY id DESC";

$students   = mysqli_query($conn, $sql);
$total      = mysqli_num_rows($students);

/* Stats (always from full table) */
$allStudents = mysqli_query($conn,"SELECT cgpa FROM students");
$totalAll    = mysqli_num_rows($allStudents);
$withResume  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM students WHERE resume IS NOT NULL AND resume != ''"))['c'];

/* Unique departments for filter dropdown */
$deptRes  = mysqli_query($conn,"SELECT DISTINCT department FROM students WHERE department IS NOT NULL AND department != '' ORDER BY department");
$deptList = [];
while($dr = mysqli_fetch_assoc($deptRes)) $deptList[] = $dr['department'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Students — PlaceSync</title>
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
      <a href="dashboard.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
      <a href="analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i> Analytics</a>
      <div class="nav-section-label">Manage</div>
      <a href="students.php"  class="nav-item active"><i class="fas fa-users"></i> Students</a>
      <a href="add_job.php"   class="nav-item"><i class="fas fa-plus-circle"></i> Add Job Drive</a>
      <a href="jobs.php"      class="nav-item"><i class="fas fa-briefcase"></i> Job Drives</a>
      <div class="nav-section-label">Placement</div>
      <a href="applications.php" class="nav-item"><i class="fas fa-file-alt"></i> Applications</a>
      <a href="shortlist.php"    class="nav-item"><i class="fas fa-user-check"></i> Shortlisting</a>
      <div class="nav-section-label">AI Features</div>
      <a href="ml_ranking.php"        class="nav-item"><i class="fas fa-robot"></i> ML Ranking</a>
      <a href="eligibility_report.php"class="nav-item"><i class="fas fa-filter"></i> Eligibility Report</a>
    </nav>
    <div class="sidebar-bottom">
      <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </aside>

  <!-- ── MAIN ─────────────────────────────────────────────────────────── -->
  <main class="dash-main">
    <div class="dash-header">
      <div>
        <h1>All Students</h1>
        <p class="breadcrumb">Admin &rarr; Students (<?= $totalAll ?> registered)</p>
      </div>
    </div>

    <div class="dash-content">

      <!-- ── Stat cards ──────────────────────────────────────────────── -->
      <div class="stats-grid" style="margin-bottom:24px">
        <div class="stat-card">
          <div class="stat-icon" style="background:linear-gradient(135deg,var(--navy),var(--blue))">
            <i class="fas fa-users"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number"><?= $totalAll ?></div>
            <div class="stat-label">Total Students</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:linear-gradient(135deg,#16a34a,#22c55e)">
            <i class="fas fa-file-pdf"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number" style="color:#16a34a"><?= $withResume ?></div>
            <div class="stat-label">Resume Uploaded</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)">
            <i class="fas fa-search"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number" style="color:#d97706"><?= $total ?></div>
            <div class="stat-label">Showing Now</div>
          </div>
        </div>
      </div>

      <!-- ── Search & Filter bar ─────────────────────────────────────── -->
      <div class="card" style="margin-bottom:20px">
        <div class="card-body" style="padding:16px 20px">
          <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">

            <!-- Search input -->
            <div style="flex:1;min-width:220px;position:relative">
              <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px"></i>
              <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                     placeholder="Search by name, email or skill…"
                     style="width:100%;padding:9px 12px 9px 34px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;background:var(--light);box-sizing:border-box">
            </div>

            <!-- CGPA filter -->
            <select name="cgpa"
                    style="padding:9px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;background:var(--light)">
              <option value="all"  <?= $cgpa==='all'  ? 'selected':'' ?>>All CGPA</option>
              <option value="high" <?= $cgpa==='high' ? 'selected':'' ?>>High  (&ge; 8.0)</option>
              <option value="mid"  <?= $cgpa==='mid'  ? 'selected':'' ?>>Mid   (6.0 – 7.9)</option>
              <option value="low"  <?= $cgpa==='low'  ? 'selected':'' ?>>Low   (&lt; 6.0)</option>
            </select>

            <!-- Department filter -->
            <select name="dept"
                    style="padding:9px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;background:var(--light)">
              <option value="">All Departments</option>
              <?php foreach($deptList as $d): ?>
                <option value="<?= htmlspecialchars($d) ?>" <?= $dept===$d ? 'selected':'' ?>>
                  <?= htmlspecialchars($d) ?>
                </option>
              <?php endforeach; ?>
            </select>

            <!-- Buttons -->
            <button type="submit" class="btn-sm primary">
              <i class="fas fa-search"></i> Search
            </button>
            <?php if($search !== '' || $cgpa !== 'all' || $dept !== ''): ?>
              <a href="students.php"
                 style="padding:7px 14px;border-radius:8px;background:var(--light);color:var(--muted);border:1.5px solid var(--border);font-size:13px;text-decoration:none;font-family:'DM Sans',sans-serif">
                <i class="fas fa-times"></i> Clear
              </a>
            <?php endif; ?>

            <span style="font-size:12px;color:var(--muted);margin-left:auto">
              Showing <strong><?= $total ?></strong> of <strong><?= $totalAll ?></strong> students
            </span>

          </form>
        </div>
      </div>

      <!-- ── Table ───────────────────────────────────────────────────── -->
      <div class="card">
        <div class="card-header">
          <h3><i class="fas fa-users" style="color:var(--blue);margin-right:8px"></i>Registered Students</h3>
        </div>
        <div style="overflow-x:auto">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>CGPA</th>
                <th>Skills</th>
                <th>Resume</th>
              </tr>
            </thead>
            <tbody>
            <?php if($total === 0): ?>
              <tr>
                <td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">
                  <i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px"></i>
                  No students match your search.
                  <a href="students.php" style="color:var(--blue);display:block;margin-top:6px;font-size:13px">Clear filters</a>
                </td>
              </tr>
            <?php else: ?>
              <?php $i = 1; while($s = mysqli_fetch_assoc($students)): ?>
              <tr>
                <td style="color:var(--muted);font-size:13px;text-align:center"><?= $i++ ?></td>

                <!-- Name -->
                <td>
                  <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--navy),var(--blue));display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0">
                      <?= strtoupper(substr($s['name'],0,1)) ?>
                    </div>
                    <span style="font-weight:600;font-size:13px"><?= htmlspecialchars($s['name']) ?></span>
                  </div>
                </td>

                <!-- Email -->
                <td style="font-size:13px;color:var(--muted)"><?= htmlspecialchars($s['email']) ?></td>

                <!-- Department -->
                <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($s['department'] ?? '—') ?></td>

                <!-- CGPA -->
                <td>
                  <?php
                    $cgpaVal = (float)$s['cgpa'];
                    $cgpaColor = $cgpaVal >= 8.0 ? '#16a34a' : ($cgpaVal >= 6.0 ? '#d97706' : '#dc2626');
                  ?>
                  <strong style="color:<?= $cgpaColor ?>"><?= $s['cgpa'] ?></strong>
                </td>

                <!-- Skills -->
                <td style="font-size:12px;color:var(--muted)">
                  <?= htmlspecialchars(substr($s['skills'] ?? '—', 0, 50)) ?><?= strlen($s['skills'] ?? '') > 50 ? '…' : '' ?>
                </td>

                <!-- Resume -->
                <td>
                  <?php if($s['resume']): ?>
                    <a href="../uploads/resumes/<?= htmlspecialchars($s['resume']) ?>" download class="btn-sm success">
                      <i class="fas fa-download"></i>
                    </a>
                  <?php else: ?>
                    <span style="font-size:12px;color:var(--muted)">—</span>
                  <?php endif; ?>
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