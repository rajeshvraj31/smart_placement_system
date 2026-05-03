<?php
session_start();
include '../config.php';
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }

/* ── Handle status update ───────────────────────────────────────────────── */
if(isset($_POST['update_status'])){
    $appId  = (int)$_POST['app_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn,"UPDATE applications SET status='$status' WHERE id='$appId'");
    header("Location: shortlist.php?msg=updated");
    exit();
}

$msg = isset($_GET['msg']) ? "Status updated successfully." : '';

/* ── Search & Filter ─────────────────────────────────────────────────────── */
$search  = isset($_GET['search'])  ? trim($_GET['search'])  : '';
$status  = isset($_GET['status'])  ? $_GET['status']        : 'all';
$company = isset($_GET['company']) ? trim($_GET['company'])  : '';

/* Build WHERE */
$where = [];
if($search !== '')
    $where[] = "(s.name  LIKE '%" . mysqli_real_escape_string($conn,$search) . "%'
              OR s.email LIKE '%" . mysqli_real_escape_string($conn,$search) . "%'
              OR j.role  LIKE '%" . mysqli_real_escape_string($conn,$search) . "%')";

if($status !== 'all' && in_array($status,['Applied','Shortlisted','Selected','Rejected']))
    $where[] = "a.status = '" . mysqli_real_escape_string($conn,$status) . "'";

if($company !== '')
    $where[] = "j.company LIKE '%" . mysqli_real_escape_string($conn,$company) . "%'";

$sql = "
  SELECT a.id, a.status,
         s.name sname, s.email, s.cgpa, s.resume,
         j.company, j.role, j.cgpa_required
  FROM applications a
  JOIN students s ON a.student_id = s.id
  JOIN jobs j     ON a.job_id     = j.id
  " . (count($where) ? "WHERE " . implode(" AND ", $where) : "") . "
  ORDER BY j.company, a.id DESC
";
$applications = mysqli_query($conn, $sql);
$total        = mysqli_num_rows($applications);

/* ── Stats (always full table) ──────────────────────────────────────────── */
$totalAll     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications"))['c'];
$shortlisted  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications WHERE status='Shortlisted'"))['c'];
$selected     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications WHERE status='Selected'"))['c'];
$rejected     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM applications WHERE status='Rejected'"))['c'];

/* ── Company list for dropdown ──────────────────────────────────────────── */
$coRes   = mysqli_query($conn,"SELECT DISTINCT j.company FROM jobs j JOIN applications a ON a.job_id=j.id ORDER BY j.company");
$coList  = [];
while($cr = mysqli_fetch_assoc($coRes)) $coList[] = $cr['company'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shortlisting — PlaceSync</title>
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
      <a href="students.php"  class="nav-item"><i class="fas fa-users"></i> Students</a>
      <a href="add_job.php"   class="nav-item"><i class="fas fa-plus-circle"></i> Add Job Drive</a>
      <a href="jobs.php"      class="nav-item"><i class="fas fa-briefcase"></i> Job Drives</a>
      <div class="nav-section-label">Placement</div>
      <a href="applications.php" class="nav-item"><i class="fas fa-file-alt"></i> Applications</a>
      <a href="shortlist.php"    class="nav-item active"><i class="fas fa-user-check"></i> Shortlisting</a>
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
      <div><h1>Shortlisting Panel</h1><p class="breadcrumb">Admin &rarr; Placement &rarr; Shortlisting</p></div>
    </div>

    <div class="dash-content">

      <?php if($msg): ?>
        <div class="alert alert-success animate"><i class="fas fa-check-circle"></i> <?= $msg ?></div>
      <?php endif; ?>

      

      <!-- ── Search & Filter bar ─────────────────────────────────────── -->
      <div class="card" style="margin-bottom:20px">
        <div class="card-body" style="padding:16px 20px">
          <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">

            <!-- Search -->
            <div style="flex:1;min-width:220px;position:relative">
              <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px"></i>
              <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                     placeholder="Search by student name, email or role…"
                     style="width:100%;padding:9px 12px 9px 34px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;background:var(--light);box-sizing:border-box">
            </div>

            <!-- Status filter -->
            <select name="status"
                    style="padding:9px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;background:var(--light)">
              <option value="all"         <?= $status==='all'         ? 'selected':'' ?>>All Status</option>
              <option value="Applied"     <?= $status==='Applied'     ? 'selected':'' ?>>Applied</option>
              <option value="Shortlisted" <?= $status==='Shortlisted' ? 'selected':'' ?>>Shortlisted</option>
              <option value="Selected"    <?= $status==='Selected'    ? 'selected':'' ?>>Selected</option>
              <option value="Rejected"    <?= $status==='Rejected'    ? 'selected':'' ?>>Rejected</option>
            </select>

            <!-- Company filter -->
            <select name="company"
                    style="padding:9px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;background:var(--light)">
              <option value="">All Companies</option>
              <?php foreach($coList as $co): ?>
                <option value="<?= htmlspecialchars($co) ?>" <?= $company===$co ? 'selected':'' ?>>
                  <?= htmlspecialchars($co) ?>
                </option>
              <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-sm primary">
              <i class="fas fa-search"></i> Filter
            </button>

            <?php if($search !== '' || $status !== 'all' || $company !== ''): ?>
              <a href="shortlist.php"
                 style="padding:7px 14px;border-radius:8px;background:var(--light);color:var(--muted);border:1.5px solid var(--border);font-size:13px;text-decoration:none;font-family:'DM Sans',sans-serif">
                <i class="fas fa-times"></i> Clear
              </a>
            <?php endif; ?>

            <span style="font-size:12px;color:var(--muted);margin-left:auto">
              Showing <strong><?= $total ?></strong> of <strong><?= $totalAll ?></strong> applications
            </span>

          </form>
        </div>
      </div>

      <!-- ── Table ───────────────────────────────────────────────────── -->
      <div class="card">
        <div class="card-header">
          <h3><i class="fas fa-user-check" style="color:var(--blue);margin-right:8px"></i>All Applications</h3>
          <a href="ml_ranking.php" class="btn-sm amber"><i class="fas fa-robot"></i> ML Rank First</a>
        </div>
        <div style="overflow-x:auto">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Student</th>
                <th>CGPA</th>
                <th>Company</th>
                <th>Role</th>
                <th>Current Status</th>
                <th>Update Status</th>
                <th>Resume</th>
              </tr>
            </thead>
            <tbody>
            <?php if($total === 0): ?>
              <tr>
                <td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">
                  <i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px"></i>
                  No applications match your search.
                  <a href="shortlist.php" style="color:var(--blue);display:block;margin-top:6px;font-size:13px">Clear filters</a>
                </td>
              </tr>
            <?php else: ?>
              <?php $i = 1; while($app = mysqli_fetch_assoc($applications)):
                $cls = ['Applied'=>'badge-applied','Shortlisted'=>'badge-shortlist','Selected'=>'badge-selected','Rejected'=>'badge-rejected'];
                $c   = $cls[$app['status']] ?? 'badge-applied';
              ?>
              <tr>

                <!-- # -->
                <td style="color:var(--muted);font-size:13px;text-align:center"><?= $i++ ?></td>

                <!-- Student -->
                <td>
                  <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($app['sname']) ?></div>
                  <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($app['email']) ?></div>
                </td>

                <!-- CGPA -->
                <td><strong style="color:var(--blue)"><?= $app['cgpa'] ?></strong></td>

                <!-- Company -->
                <td style="font-size:13px"><?= htmlspecialchars($app['company']) ?></td>

                <!-- Role -->
                <td style="font-size:13px"><?= htmlspecialchars($app['role']) ?></td>

                <!-- Current Status -->
                <td><span class="badge <?= $c ?>"><?= $app['status'] ?></span></td>

                <!-- Update Status -->
                <td>
                  <form method="POST" style="display:flex;gap:6px;align-items:center">
                    <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                    <!-- preserve filters after save -->
                    <input type="hidden" name="search"  value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="status_f" value="<?= htmlspecialchars($status) ?>">
                    <input type="hidden" name="company" value="<?= htmlspecialchars($company) ?>">
                    <select name="status" style="padding:6px 10px;border-radius:7px;border:1.5px solid var(--border);font-family:'DM Sans',sans-serif;font-size:13px;background:var(--light)">
                      <option <?= $app['status']==='Applied'     ? 'selected':'' ?>>Applied</option>
                      <option <?= $app['status']==='Shortlisted' ? 'selected':'' ?>>Shortlisted</option>
                      <option <?= $app['status']==='Selected'    ? 'selected':'' ?>>Selected</option>
                      <option <?= $app['status']==='Rejected'    ? 'selected':'' ?>>Rejected</option>
                    </select>
                    <button type="submit" name="update_status" class="btn-sm primary">
                      <i class="fas fa-save"></i>
                    </button>
                  </form>
                </td>

                <!-- Resume -->
                <td>
                  <?php if($app['resume']): ?>
                    <a href="../uploads/resumes/<?= htmlspecialchars($app['resume']) ?>" download class="btn-sm success">
                      <i class="fas fa-download"></i>
                    </a>
                  <?php else: ?>
                    <span style="font-size:11px;color:var(--muted)">—</span>
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