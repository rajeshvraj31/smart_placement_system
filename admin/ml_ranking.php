<?php
session_start();
include '../config.php';
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }

if(isset($_POST['update_status'])){
    $appId  = (int)$_POST['app_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn,"UPDATE applications SET status='$status' WHERE id='$appId'");
    header("Location: ml_ranking.php?msg=updated");
    exit();
}

$msg = isset($_GET['msg']) ? "Status updated successfully." : '';

$res = mysqli_query($conn,"
    SELECT a.id app_id, a.status,
           s.id s_id, s.name, s.skills, s.resume,
           j.id j_id, j.role, j.company, j.skills job_skills
    FROM applications a
    JOIN students s ON s.id = a.student_id
    JOIN jobs     j ON j.id = a.job_id
    ORDER BY j.company, j.id
");

$companies = []; 

while($row = mysqli_fetch_assoc($res)){
    $resume_file = $row['resume'] ?? '';
    $resume_path = "../uploads/resumes/" . $resume_file;
    $score       = null;

    if(!empty($resume_file) && file_exists($resume_path)){
        $job_text = $row['role'] . " " . $row['job_skills'] . " " . $row['skills'];
        $ch   = curl_init();
        $data = [
            'resume' => new CURLFile(realpath($resume_path)),
            'job'    => $job_text
        ];
        curl_setopt($ch, CURLOPT_URL,            "https://smart-placement-api-iax1.onrender.com/rank");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $data);
        curl_setopt($ch, CURLOPT_TIMEOUT,        5);
        $response = curl_exec($ch);
        curl_close($ch);

        if($response !== false){
            $result = json_decode($response, true);
            if(isset($result['score'])){
                $score = round((float)$result['score'], 1);
            }
        }
    }

    $row['score'] = $score;
    $company      = $row['company'];
    $role         = $row['role'];

    $companies[$company][$role][] = $row;
}

foreach($companies as $company => &$roles){
    foreach($roles as $role => &$rows){
        usort($rows, function($a, $b){
            if($a['score'] === null && $b['score'] === null) return 0;
            if($a['score'] === null) return 1;
            if($b['score'] === null) return -1;
            return $b['score'] <=> $a['score'];
        });
    }
}
unset($roles, $rows);

function barClass($score){
    if($score === null) return 'low';
    return $score >= 70 ? 'high' : ($score >= 40 ? 'mid' : 'low');
}

$totalCandidates = array_sum(array_map(fn($roles) =>
    array_sum(array_map('count', $roles)), $companies));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ML Resume Ranking — PlaceSync</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    .company-block   { margin-bottom: 36px; }
    .company-heading {
      display: flex; align-items: center; gap: 14px;
      background: linear-gradient(135deg, var(--navy), var(--blue));
      color: #fff; padding: 14px 20px; border-radius: 10px 10px 0 0;
      margin-bottom: 0;
    }
    .company-heading .co-icon {
      width: 40px; height: 40px; border-radius: 50%;
      background: rgba(255,255,255,0.15);
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; font-weight: 700; flex-shrink: 0;
    }
    .company-heading h2 { margin: 0; font-size: 17px; font-weight: 700; }
    .company-heading .co-meta { font-size: 12px; color: rgba(255,255,255,0.6); margin-top: 2px; }
    .role-block      { border: 1px solid var(--border); border-top: none; }
    .role-block:last-child { border-radius: 0 0 10px 10px; }
    .role-heading    {
      background: var(--light); padding: 10px 20px;
      display: flex; align-items: center; gap: 10px;
      border-bottom: 1px solid var(--border);
    }
    .role-heading .role-tag {
      background: var(--blue); color: #fff;
      font-size: 12px; font-weight: 600;
      padding: 3px 12px; border-radius: 20px;
    }
    .role-heading .role-count {
      font-size: 12px; color: var(--muted);
    }
  </style>
</head>
<body>
<div class="dash-layout">

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
      <div class="nav-section-label">AI Features</div>
      <a href="ml_ranking.php"         class="nav-item active"><i class="fas fa-robot"></i> ML Ranking</a>
      <a href="eligibility_report.php" class="nav-item"><i class="fas fa-filter"></i> Eligibility Report</a>
    </nav>
    <div class="sidebar-bottom">
      <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </aside>

  <main class="dash-main">
    <div class="dash-header">
      <div>
        <h1>ML Resume Ranking</h1>
        <p class="breadcrumb">Admin &rarr; AI Features &rarr; Ranking</p>
      </div>
      <div style="display:flex;align-items:center;gap:12px">
        <span style="font-size:13px;color:var(--muted)">
          <i class="fas fa-building" style="margin-right:4px"></i>
          <?= count($companies) ?> compan<?= count($companies) !== 1 ? 'ies' : 'y' ?>
          &nbsp;·&nbsp;
          <i class="fas fa-users" style="margin-right:4px"></i>
          <?= $totalCandidates ?> candidate<?= $totalCandidates !== 1 ? 's' : '' ?>
        </span>
      </div>
    </div>

    <div class="dash-content">

      <?php if($msg): ?>
        <div class="alert alert-success animate"><i class="fas fa-check-circle"></i> <?= $msg ?></div>
      <?php endif; ?>

      <div class="card" style="margin-bottom:24px;background:linear-gradient(135deg,var(--navy),var(--blue));border:none">
        
      </div>

      <?php if(empty($companies)): ?>
        <div class="card">
          <div style="text-align:center;padding:60px 20px;color:var(--muted)">
            <i class="fas fa-inbox" style="font-size:40px;margin-bottom:12px;display:block"></i>
            <strong>No applications yet.</strong><br>
            Students must apply to drives before ML ranking is available.
          </div>
        </div>

      <?php else: ?>

        <?php foreach($companies as $company => $roles): ?>
          <?php
            $totalInCo = array_sum(array_map('count', $roles));
            $roleCount = count($roles);
          ?>
          <div class="company-block">

            <div class="company-heading">
              <div class="co-icon"><?= strtoupper(substr($company, 0, 1)) ?></div>
              <div>
                <h2><?= htmlspecialchars($company) ?></h2>
                <div class="co-meta">
                  <?= $roleCount ?> role<?= $roleCount !== 1 ? 's' : '' ?>
                  &nbsp;&bull;&nbsp;
                  <?= $totalInCo ?> applicant<?= $totalInCo !== 1 ? 's' : '' ?>
                </div>
              </div>
            </div>

            <?php foreach($roles as $role => $rows): ?>
            <div class="role-block">
              <div class="role-heading">
                <i class="fas fa-briefcase" style="color:var(--blue);font-size:13px"></i>
                <span class="role-tag"><?= htmlspecialchars($role) ?></span>
                <span class="role-count"><?= count($rows) ?> applicant<?= count($rows) !== 1 ? 's' : '' ?> — sorted high &rarr; low</span>
              </div>

              <div style="overflow-x:auto">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th style="width:48px">#</th>
                      <th>Student</th>
                      <th>Skills</th>
                      <th>Current Status</th>
                      <th>ML Score</th>
                      <th>Update Status</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach($rows as $i => $row):
                    $rank  = $i + 1;
                    $score = $row['score'];
                    $bc    = barClass($score);
                    $cls   = ['Applied'=>'badge-applied','Shortlisted'=>'badge-shortlist','Selected'=>'badge-selected','Rejected'=>'badge-rejected'];
                    $c     = $cls[$row['status']] ?? 'badge-applied';
                  ?>
                  <tr>

                    <td style="text-align:center;font-weight:700;font-size:13px;color:var(--muted)">
                      <?php if($rank===1): ?>
                        <span style="color:#f59e0b;font-size:17px" title="Top Candidate">🏆</span>
                      <?php elseif($rank===2): ?>
                        <span style="font-size:16px">🥈</span>
                      <?php elseif($rank===3): ?>
                        <span style="font-size:16px">🥉</span>
                      <?php else: ?>
                        <?= $rank ?>
                      <?php endif; ?>
                    </td>

                    <td>
                      <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--navy),var(--blue));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0">
                          <?= strtoupper(substr($row['name'],0,1)) ?>
                        </div>
                        <div>
                          <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($row['name']) ?></div>
                          <?php if($rank===1): ?>
                            <span style="font-size:10px;background:#fef3c7;color:#92400e;padding:1px 8px;border-radius:10px;font-weight:600">⭐ Top for this role</span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>

                    <td style="font-size:12px;color:var(--muted);max-width:180px">
                      <?= htmlspecialchars(substr($row['skills'] ?? '—', 0, 50)) ?><?= strlen($row['skills'] ?? '') > 50 ? '…' : '' ?>
                    </td>

                    <td><span class="badge <?= $c ?>"><?= $row['status'] ?></span></td>

                    <td>
                      <div style="display:flex;align-items:center;gap:10px;min-width:160px">
                        <div class="score-bar-wrap" style="flex:1">
                          <div class="score-bar <?= $bc ?>" style="width:<?= $score ?? 0 ?>%"></div>
                        </div>
                        <?php if($score !== null): ?>
                          <span style="font-weight:700;font-size:13px;color:var(--navy);min-width:42px;text-align:right"><?= $score ?>%</span>
                        <?php elseif(empty($row['resume'])): ?>
                          <span style="font-size:11px;color:var(--muted)">No resume</span>
                        <?php else: ?>
                          <span style="font-size:11px;color:var(--muted)">API off</span>
                        <?php endif; ?>
                      </div>
                    </td>

                    <td>
                      <form method="POST" style="display:flex;gap:6px;align-items:center">
                        <input type="hidden" name="app_id" value="<?= $row['app_id'] ?>">
                        <select name="status" style="padding:6px 10px;border-radius:7px;border:1.5px solid var(--border);font-family:'DM Sans',sans-serif;font-size:13px;background:var(--light)">
                          <option <?= $row['status']=='Applied'     ? 'selected':'' ?>>Applied</option>
                          <option <?= $row['status']=='Shortlisted' ? 'selected':'' ?>>Shortlisted</option>
                          <option <?= $row['status']=='Selected'    ? 'selected':'' ?>>Selected</option>
                          <option <?= $row['status']=='Rejected'    ? 'selected':'' ?>>Rejected</option>
                        </select>
                        <button type="submit" name="update_status" class="btn-sm primary">
                          <i class="fas fa-save"></i>
                        </button>
                      </form>
                    </td>

                  </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <?php endforeach; ?>

          </div>
        <?php endforeach; ?>

      <?php endif; ?>

    </div>
  </main>
</div>
</body>
</html>
