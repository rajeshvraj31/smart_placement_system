<?php
session_start();
include '../config.php';
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }

/* ── Fetch all student × job combinations with eligibility ─────────────── */
$res = mysqli_query($conn,"
    SELECT s.name, s.email, s.cgpa, s.department,
           j.company, j.role, j.cgpa_required
    FROM students s, jobs j
    ORDER BY j.company, j.role, s.name
");

/* ── Count totals for stat cards ────────────────────────────────────────── */
$all = [];
while($row = mysqli_fetch_assoc($res)) $all[] = $row;

$total      = count($all);
$eligible   = count(array_filter($all, fn($r) => $r['cgpa'] >= $r['cgpa_required']));
$notElig    = $total - $eligible;
$eligPct    = $total > 0 ? round(($eligible / $total) * 100, 1) : 0;

/* ── Search / filter ────────────────────────────────────────────────────── */
$search  = isset($_GET['search'])  ? trim($_GET['search'])  : '';
$filter  = isset($_GET['filter'])  ? $_GET['filter']        : 'all';   // all | eligible | not

$rows = array_filter($all, function($r) use ($search, $filter) {
    $isElig = $r['cgpa'] >= $r['cgpa_required'];
    if($filter === 'eligible'  && !$isElig) return false;
    if($filter === 'not'       && $isElig)  return false;
    if($search !== '') {
        $hay = strtolower($r['name'] . $r['company'] . $r['role'] . $r['department']);
        if(strpos($hay, strtolower($search)) === false) return false;
    }
    return true;
});
$rows = array_values($rows);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Eligibility Report — PlaceSync</title>
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
      <div class="user-info">
        <div class="name">Admin Panel</div>
        <div class="role">Placement Officer</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Overview</div>
      <a href="dashboard.php"  class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
      <a href="analytics.php"  class="nav-item"><i class="fas fa-chart-bar"></i> Analytics</a>
      <div class="nav-section-label">Manage</div>
      <a href="students.php"   class="nav-item"><i class="fas fa-users"></i> Students</a>
      <a href="add_job.php"    class="nav-item"><i class="fas fa-plus-circle"></i> Add Job Drive</a>
      <a href="jobs.php"       class="nav-item"><i class="fas fa-briefcase"></i> Job Drives</a>
      <div class="nav-section-label">Placement</div>
      <a href="applications.php" class="nav-item"><i class="fas fa-file-alt"></i> Applications</a>
      <a href="shortlist.php"    class="nav-item"><i class="fas fa-user-check"></i> Shortlisting</a>
      <a href="view_resumes.php" class="nav-item"><i class="fas fa-file-pdf"></i> Resumes</a>
      <div class="nav-section-label">AI Features</div>
      <a href="ml_ranking.php"          class="nav-item"><i class="fas fa-robot"></i> ML Ranking</a>
      <a href="eligibility_report.php"  class="nav-item active"><i class="fas fa-filter"></i> Eligibility Report</a>
    </nav>
    <div class="sidebar-bottom">
      <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </aside>

  <!-- ── MAIN ─────────────────────────────────────────────────────────── -->
  <main class="dash-main">
    <div class="dash-header">
      <div>
        <h1>Eligibility Report</h1>
        <p class="breadcrumb">Admin &rarr; AI Features &rarr; Eligibility Report</p>
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
            <div class="stat-number"><?= $total ?></div>
            <div class="stat-label">Total Combinations</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background:linear-gradient(135deg,#16a34a,#22c55e)">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number" style="color:#16a34a"><?= $eligible ?></div>
            <div class="stat-label">Eligible</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background:linear-gradient(135deg,#dc2626,#ef4444)">
            <i class="fas fa-times-circle"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number" style="color:#dc2626"><?= $notElig ?></div>
            <div class="stat-label">Not Eligible</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)">
            <i class="fas fa-percentage"></i>
          </div>
          <div class="stat-info">
            <div class="stat-number" style="color:#d97706"><?= $eligPct ?>%</div>
            <div class="stat-label">Eligibility Rate</div>
          </div>
        </div>

      </div>

      <!-- ── Search & Filter bar ─────────────────────────────────────── -->
      <div class="card" style="margin-bottom:20px">
        <div class="card-body" style="padding:16px 20px">
          <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <div style="flex:1;min-width:220px;position:relative">
              <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px"></i>
              <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                     placeholder="Search student, company, role…"
                     style="width:100%;padding:9px 12px 9px 34px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;background:var(--light);box-sizing:border-box">
            </div>
            <select name="filter"
                    style="padding:9px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;background:var(--light)">
              <option value="all"     <?= $filter==='all'     ? 'selected':'' ?>>All</option>
              <option value="eligible"<?= $filter==='eligible'? 'selected':'' ?>>Eligible Only</option>
              <option value="not"     <?= $filter==='not'     ? 'selected':'' ?>>Not Eligible Only</option>
            </select>
            <button type="submit" class="btn-sm primary"><i class="fas fa-search"></i> Filter</button>
            <?php if($search || $filter !== 'all'): ?>
              <a href="eligibility_report.php" class="btn-sm" style="background:var(--light);color:var(--muted);border:1.5px solid var(--border)">
                <i class="fas fa-times"></i> Clear
              </a>
            <?php endif; ?>
            <span style="font-size:12px;color:var(--muted);margin-left:auto">
              Showing <strong><?= count($rows) ?></strong> of <strong><?= $total ?></strong> records
            </span>
          </form>
        </div>
      </div>

      <!-- ── Table ───────────────────────────────────────────────────── -->
      <div class="card">
        <div class="card-header">
          <h3>
            <i class="fas fa-filter" style="color:var(--blue);margin-right:8px"></i>
            Student × Job Eligibility Matrix
          </h3>
        </div>
        <div style="overflow-x:auto">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Student</th>
                <th>Student CGPA</th>
                <th>Company</th>
                <th>Role</th>
                <th>Required CGPA</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
            <?php if(empty($rows)): ?>
              <tr>
                <td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">
                  <i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px"></i>
                  No records match your search.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach($rows as $i => $row):
                $isElig = $row['cgpa'] >= $row['cgpa_required'];
              ?>
              <tr>

                <!-- # -->
                <td style="color:var(--muted);font-size:13px;text-align:center"><?= $i+1 ?></td>

                <!-- Student -->
                <td>
                  <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--navy),var(--blue));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0">
                      <?= strtoupper(substr($row['name'],0,1)) ?>
                    </div>
                    <div>
                      <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($row['name']) ?></div>
                      <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($row['department'] ?? '') ?></div>
                    </div>
                  </div>
                </td>

                <!-- Student CGPA -->
                <td>
                  <strong style="color:<?= $isElig ? '#16a34a' : '#dc2626' ?>;font-size:14px">
                    <?= $row['cgpa'] ?>
                  </strong>
                </td>

                <!-- Company -->
                <td style="font-size:13px;font-weight:500"><?= htmlspecialchars($row['company']) ?></td>

                <!-- Role -->
                <td style="font-size:13px"><?= htmlspecialchars($row['role']) ?></td>

                <!-- Required CGPA -->
                <td style="font-size:13px;color:var(--muted)"><?= $row['cgpa_required'] ?></td>

                <!-- Status badge -->
                <td>
                  <?php if($isElig): ?>
                    <span class="badge badge-selected">
                      <i class="fas fa-check" style="margin-right:4px;font-size:10px"></i>Eligible
                    </span>
                  <?php else: ?>
                    <span class="badge badge-rejected">
                      <i class="fas fa-times" style="margin-right:4px;font-size:10px"></i>Not Eligible
                    </span>
                  <?php endif; ?>
                </td>

              </tr>
              <?php endforeach; ?>
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