<?php
session_start();
include '../config.php';
if(!isset($_SESSION['student'])){ header("Location: login.php"); exit(); }
$email = $_SESSION['student'];
$student = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM students WHERE email='$email'"));
$initial = strtoupper(substr($student['name'],0,1));
$msg = $err = '';
if(isset($_FILES['resume']) && $_FILES['resume']['error'] == 0){
    $ext = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
    if(strtolower($ext) === 'pdf'){
        $fname = 'resume_' . $student['id'] . '_' . time() . '.pdf';
        $dest = '../uploads/resumes/' . $fname;
        if(move_uploaded_file($_FILES['resume']['tmp_name'], $dest)){
            mysqli_query($conn,"UPDATE students SET resume='$fname' WHERE id='{$student['id']}'");
            $msg = "Resume uploaded successfully!";
            $student['resume'] = $fname;
        } else { $err = "Upload failed. Check folder permissions."; }
    } else { $err = "Only PDF files are allowed."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Upload Resume — PlaceSync</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
.upload-zone { border: 2px dashed var(--border); border-radius: 16px; padding: 48px; text-align: center; transition: all 0.2s; cursor: pointer; }
.upload-zone:hover { border-color: var(--blue); background: rgba(26,74,138,0.03); }
.upload-zone i { font-size: 48px; color: var(--blue); opacity: 0.4; display: block; margin-bottom: 16px; }
</style>
</head>
<body>
<div class="dash-layout">
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand"><div class="icon"><i class="fas fa-graduation-cap"></i></div><span>PlaceSync</span></a>
    <div class="sidebar-user"><div class="user-avatar"><?= $initial ?></div><div class="user-info"><div class="name"><?= htmlspecialchars($student['name']) ?></div><div class="role">Student Portal</div></div></div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>
      <a href="dashboard.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
      <a href="eligible_jobs.php" class="nav-item"><i class="fas fa-briefcase"></i> Job Drives</a>
      <a href="my_applications.php" class="nav-item"><i class="fas fa-file-alt"></i> My Applications</a>
      <div class="nav-section-label">Profile</div>
      <a href="upload_resume.php" class="nav-item active"><i class="fas fa-upload"></i> Upload Resume</a>
    </nav>
    <div class="sidebar-bottom"><a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
  </aside>
  <main class="dash-main">
    <div class="dash-header"><div><h1>Upload Resume</h1><p class="breadcrumb">Student &rarr; Profile &rarr; Resume</p></div></div>
    <div class="dash-content">
      <div style="max-width:560px">
        <?php if($msg): ?><div class="alert alert-success animate"><i class="fas fa-check-circle"></i> <?= $msg ?></div><?php endif; ?>
        <?php if($err): ?><div class="alert alert-danger animate"><i class="fas fa-exclamation-circle"></i> <?= $err ?></div><?php endif; ?>
        <div class="card">
          <div class="card-header"><h3><i class="fas fa-upload" style="color:var(--blue);margin-right:8px"></i>Upload Your CV / Resume</h3></div>
          <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
              <label class="upload-zone" for="resume-input">
                <i class="fas fa-file-pdf"></i>
                <h4 style="color:var(--navy);margin-bottom:8px">Click to choose PDF file</h4>
                <p style="font-size:13px;color:var(--muted)">Only PDF format allowed. Max 5MB.</p>
              </label>
              <input type="file" name="resume" id="resume-input" accept=".pdf" style="display:none" onchange="document.getElementById('fname').textContent = this.files[0]?.name || ''">
              <p id="fname" style="font-size:13px;color:var(--blue);margin:10px 0;font-weight:600;text-align:center"></p>
              <button type="submit" class="btn-primary" style="margin-top:12px"><i class="fas fa-cloud-upload-alt"></i> Upload Resume</button>
            </form>
          </div>
        </div>
        <?php if($student['resume']): ?>
        <div class="card">
          <div class="card-header"><h3>Current Resume</h3></div>
          <div class="card-body" style="display:flex;align-items:center;gap:16px">
            <i class="fas fa-file-pdf" style="font-size:36px;color:#EF4444"></i>
            <div style="flex:1"><div style="font-weight:600;color:var(--navy)"><?= $student['resume'] ?></div><div style="font-size:12px;color:var(--muted);margin-top:3px">Uploaded successfully</div></div>
            <a href="../uploads/resumes/<?= $student['resume'] ?>" download class="btn-sm success"><i class="fas fa-download"></i> Download</a>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
</body></html>
