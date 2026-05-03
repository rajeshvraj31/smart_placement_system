<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Placement Management System | Pondicherry University</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --navy:   #0B1D3A;
      --blue:   #1A4A8A;
      --accent: #E8A020;
      --light:  #F4F7FF;
      --white:  #FFFFFF;
      --muted:  #6B7A99;
      --card-bg:#FFFFFF;
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--light);
      color: var(--navy);
      overflow-x: hidden;
    }

    /* ── NAVBAR ── */
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      display: flex; align-items: center; justify-content: space-between;
      padding: 18px 60px;
      background: rgba(11,29,58,0.97);
      backdrop-filter: blur(12px);
      box-shadow: 0 2px 24px rgba(0,0,0,0.18);
    }
    .nav-logo {
      display: flex; align-items: center; gap: 12px;
      text-decoration: none;
    }
    .nav-logo .icon {
      width: 40px; height: 40px; background: var(--accent);
      border-radius: 10px; display: flex; align-items: center; justify-content: center;
      font-size: 18px; color: var(--navy);
    }
    .nav-logo span {
      font-family: 'Playfair Display', serif;
      font-size: 18px; color: #fff; font-weight: 700;
      letter-spacing: 0.3px;
    }
    .nav-links { display: flex; gap: 8px; }
    .nav-links a {
      padding: 9px 20px; border-radius: 8px;
      font-size: 14px; font-weight: 500; text-decoration: none;
      transition: all 0.2s;
    }
    .nav-links a.ghost { color: rgba(255,255,255,0.75); }
    .nav-links a.ghost:hover { color: #fff; background: rgba(255,255,255,0.08); }
    .nav-links a.primary {
      background: var(--accent); color: var(--navy); font-weight: 600;
    }
    .nav-links a.primary:hover { background: #f5b53a; transform: translateY(-1px); }

    /* ── HERO ── */
    .hero {
      min-height: 100vh;
      background:
        radial-gradient(ellipse 80% 60% at 70% 40%, rgba(26,74,138,0.35) 0%, transparent 70%),
        linear-gradient(135deg, #0B1D3A 0%, #132954 60%, #1A3F7A 100%);
      display: flex; align-items: center;
      padding: 120px 60px 80px;
      position: relative; overflow: hidden;
    }
    .hero::before {
      content: '';
      position: absolute; inset: 0;
      background-image: radial-gradient(rgba(255,255,255,0.04) 1px, transparent 1px);
      background-size: 40px 40px;
    }
    .hero-content { position: relative; z-index: 1; max-width: 640px; }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(232,160,32,0.15); border: 1px solid rgba(232,160,32,0.4);
      color: var(--accent); padding: 6px 16px; border-radius: 50px;
      font-size: 13px; font-weight: 600; letter-spacing: 0.5px;
      margin-bottom: 28px;
    }
    .hero h1 {
      font-family: 'Playfair Display', serif;
      font-size: clamp(40px, 5vw, 64px);
      line-height: 1.1; color: #fff; margin-bottom: 24px;
    }
    .hero h1 span { color: var(--accent); }
    .hero p {
      font-size: 17px; line-height: 1.7;
      color: rgba(255,255,255,0.68); margin-bottom: 40px;
    }
    .hero-cta { display: flex; gap: 16px; flex-wrap: wrap; }
    .btn-hero {
      padding: 14px 32px; border-radius: 10px;
      font-size: 15px; font-weight: 600; text-decoration: none;
      transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-hero.primary {
      background: var(--accent); color: var(--navy);
      box-shadow: 0 4px 20px rgba(232,160,32,0.35);
    }
    .btn-hero.primary:hover { background: #f5b53a; transform: translateY(-2px); box-shadow: 0 8px 28px rgba(232,160,32,0.45); }
    .btn-hero.outline {
      border: 1.5px solid rgba(255,255,255,0.3); color: #fff;
    }
    .btn-hero.outline:hover { border-color: rgba(255,255,255,0.7); background: rgba(255,255,255,0.08); }

    .hero-stats {
      position: absolute; right: 60px; bottom: 80px;
      display: flex; gap: 32px; z-index: 1;
    }
    .stat-item { text-align: center; }
    .stat-item .num {
      font-family: 'Playfair Display', serif;
      font-size: 40px; color: var(--accent); font-weight: 900; line-height: 1;
    }
    .stat-item .lbl { font-size: 13px; color: rgba(255,255,255,0.5); margin-top: 4px; }

    /* ── FEATURES ── */
    .features {
      padding: 100px 60px;
      background: var(--white);
    }
    .section-label {
      text-align: center; font-size: 13px; font-weight: 600;
      letter-spacing: 2px; text-transform: uppercase;
      color: var(--blue); margin-bottom: 16px;
    }
    .section-title {
      text-align: center;
      font-family: 'Playfair Display', serif;
      font-size: 38px; color: var(--navy); margin-bottom: 60px;
    }
    .features-grid {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 28px;
      max-width: 1200px; margin: 0 auto;
    }
    .feature-card {
      background: var(--light); border-radius: 16px; padding: 36px 32px;
      border: 1px solid rgba(26,74,138,0.08);
      transition: all 0.3s; position: relative; overflow: hidden;
    }
    .feature-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
      background: linear-gradient(90deg, var(--blue), var(--accent));
      transform: scaleX(0); transform-origin: left; transition: transform 0.3s;
    }
    .feature-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(11,29,58,0.1); }
    .feature-card:hover::before { transform: scaleX(1); }
    .feature-icon {
      width: 54px; height: 54px; border-radius: 14px;
      background: linear-gradient(135deg, var(--navy), var(--blue));
      display: flex; align-items: center; justify-content: center;
      font-size: 22px; color: var(--accent); margin-bottom: 20px;
    }
    .feature-card h3 { font-size: 18px; font-weight: 600; color: var(--navy); margin-bottom: 10px; }
    .feature-card p { font-size: 14px; line-height: 1.7; color: var(--muted); }

    /* ── ROLES ── */
    .roles { padding: 100px 60px; background: var(--light); }
    .roles-grid {
      display: grid; grid-template-columns: repeat(3,1fr); gap: 24px;
      max-width: 1000px; margin: 0 auto;
    }
    .role-card {
      border-radius: 20px; padding: 48px 36px; text-align: center;
      position: relative; overflow: hidden; text-decoration: none;
      transition: all 0.3s; display: block;
    }
    .role-card.student  { background: linear-gradient(145deg, #0B1D3A, #1A4A8A); }
    .role-card.admin    { background: linear-gradient(145deg, #1A2E4A, #243F6A); }
    .role-card.recruiter{ background: linear-gradient(145deg, #1A3A2A, #1F5C3A); }
    .role-card:hover { transform: translateY(-8px); box-shadow: 0 24px 48px rgba(0,0,0,0.2); }
    .role-icon { font-size: 48px; margin-bottom: 20px; }
    .role-card h3 { font-size: 22px; font-weight: 700; color: #fff; margin-bottom: 10px; }
    .role-card p { font-size: 14px; color: rgba(255,255,255,0.65); line-height: 1.6; margin-bottom: 28px; }
    .role-btn {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);
      color: #fff; padding: 10px 24px; border-radius: 50px;
      font-size: 14px; font-weight: 600; transition: all 0.2s;
    }
    .role-card:hover .role-btn { background: rgba(255,255,255,0.25); }

    /* ── FOOTER ── */
    footer {
      background: var(--navy); padding: 40px 60px;
      display: flex; align-items: center; justify-content: space-between;
    }
    footer p { font-size: 13px; color: rgba(255,255,255,0.4);}
   

    /* ── ANIMATIONS ── */
    @keyframes fadeUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
    .hero-content > * { animation: fadeUp 0.7s ease both; }
    .hero-badge   { animation-delay: 0.1s; }
    .hero h1      { animation-delay: 0.2s; }
    .hero p       { animation-delay: 0.3s; }
    .hero-cta     { animation-delay: 0.4s; }
    .hero-stats   { animation: fadeUp 0.7s 0.5s ease both; }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <a href="index.php" class="nav-logo">
    <div class="icon"><i class="fas fa-graduation-cap"></i></div>
    <span>PlaceSync</span>
  </a>
  <div class="nav-links">
    <a href="#features" class="ghost">Features</a>
    <a href="#roles" class="ghost">Portal</a>
    <a href="student/login.php" class="ghost">Student Login</a>
    <a href="admin/login.php" class="primary">Admin Panel</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    
    <h1>Smart <span>Placement</span><br>Management<br>System</h1>
    <p>An intelligent campus placement portal for Pondicherry University. Automate eligibility checks, rank resumes with AI, and manage drives — all in one place.</p>
    <div class="hero-cta">
      <a href="student/register.php" class="btn-hero primary">
        <i class="fas fa-user-plus"></i> Student Register
      </a>
      <a href="student/login.php" class="btn-hero outline">
        <i class="fas fa-sign-in-alt"></i> Login Portal
      </a>
    </div>
  </div>
  
</section>

<!-- FEATURES -->
<section class="features" id="features">
  <div class="section-label">What We Offer</div>
  <h2 class="section-title">Everything Placement Needs</h2>
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-filter"></i></div>
      <h3>Auto Eligibility Filter</h3>
      <p>Students only see drives they qualify for — based on CGPA, department, and skills. No manual checking needed.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-robot"></i></div>
      <h3>ML Resume Ranking</h3>
      <p>TF-IDF and Cosine Similarity algorithms score and rank resumes against job descriptions automatically.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
      <h3>Analytics Dashboard</h3>
      <p>Real-time charts showing placement statistics, department performance, company offers, and selection ratios.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-file-pdf"></i></div>
      <h3>Resume Management</h3>
      <p>Students upload PDF resumes securely. Admins can download and review all resumes from one panel.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-building"></i></div>
      <h3>Drive Management</h3>
      <p>Admins create and manage company drives with eligibility criteria, roles, and application deadlines.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-check-double"></i></div>
      <h3>Shortlisting Panel</h3>
      <p>One-click shortlisting, selection, and rejection for all applicants — with instant database updates.</p>
    </div>
  </div>
</section>

<!-- ROLES -->
<section class="roles" id="roles">
  <div class="section-label">Access Portal</div>
  <h2 class="section-title">Choose Your Role</h2>
  <div class="roles-grid">
    <a href="student/login.php" class="role-card student">
      <div class="role-icon">🎓</div>
      <h3>Student</h3>
      <p>Register, upload resume, apply for eligible drives, and track your placement status in real-time.</p>
      <div class="role-btn">Enter Portal <i class="fas fa-arrow-right"></i></div>
    </a>
    <a href="admin/login.php" class="role-card admin">
      <div class="role-icon">🛠️</div>
      <h3>Placement Officer</h3>
      <p>Manage students, companies, drives, shortlisting, and analytics from one powerful dashboard.</p>
      <div class="role-btn">Enter Portal <i class="fas fa-arrow-right"></i></div>
    </a>
    <a href="recruiter/login.php" class="role-card recruiter">
      <div class="role-icon">🏢</div>
      <h3>Recruiter</h3>
      <p>View ML-ranked candidates, post job drives, and select the best talent for your company.</p>
      <div class="role-btn">Enter Portal <i class="fas fa-arrow-right"></i></div>
    </a>
  </div>
</section>

<!-- FOOTER -->
<footer >
  <p>© 2026 Smart Placement Management System — Pondicherry University</p>
</footer>

</body>
</html>
