<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
require_once __DIR__ . '/connect.php';
$name = $_SESSION['name'] ?? $_SESSION['username'];
$h = (int)date('H');
if ($h < 12)     $greeting = 'Good Morning';
elseif ($h < 17) $greeting = 'Good Afternoon';
else             $greeting = 'Good Evening';
$dayFull  = date('l');
$dateFull = date('F j, Y');
$uid = $_SESSION['user_id'];
$totalEntries = $db->querySingle("SELECT COUNT(*) FROM diary_entries WHERE user_id=".(int)$uid);
$thisMonth    = $db->querySingle("SELECT COUNT(*) FROM diary_entries WHERE user_id=".(int)$uid." AND strftime('%Y-%m',entry_date)='".date('Y-m')."'");
$streakDays   = $db->querySingle("SELECT COUNT(DISTINCT entry_date) FROM diary_entries WHERE user_id=".(int)$uid." AND entry_date >= date('now','-30 days')");
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Digital Diary — Home</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  :root{--ink:#1c1410;--gold:#c8922a;--gold2:#e8b84b;--cream:#fdf6e3;--warm:#8b5e3c;--sidebar:#1e1208}
  body{font-family:'Lato',sans-serif;background:var(--cream);min-height:100vh;display:flex}
  /* SIDEBAR */
  .sidebar{width:220px;min-height:100vh;background:var(--sidebar);display:flex;flex-direction:column;border-right:2px solid rgba(200,146,42,.25);position:fixed;left:0;top:0;bottom:0;z-index:100}
  .sidebar-logo{padding:28px 20px 22px;border-bottom:1px solid rgba(200,146,42,.2);text-align:center}
  .sidebar-logo .icon{font-size:32px;display:block;margin-bottom:8px}
  .sidebar-logo h2{font-family:'Playfair Display',serif;font-size:17px;color:var(--cream)}
  .sidebar-logo p{font-size:10.5px;color:rgba(253,246,227,.4);letter-spacing:1.5px;text-transform:uppercase;margin-top:2px}
  .sidebar-user{margin:18px 16px;background:rgba(200,146,42,.12);border-radius:8px;padding:12px;border:1px solid rgba(200,146,42,.2)}
  .sidebar-user .avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--gold2));display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:var(--ink);margin-bottom:8px}
  .sidebar-user .uname{font-size:13.5px;color:var(--cream);font-weight:700}
  .sidebar-user .urole{font-size:11px;color:rgba(253,246,227,.45);margin-top:1px}
  .nav{flex:1;padding:8px 0}
  .nav a{display:flex;align-items:center;gap:12px;padding:11px 20px;color:rgba(253,246,227,.65);text-decoration:none;font-size:14px;transition:background .2s,color .2s;border-left:3px solid transparent}
  .nav a:hover{background:rgba(200,146,42,.1);color:var(--cream);border-left-color:var(--gold)}
  .nav a.active{background:rgba(200,146,42,.15);color:var(--gold2);border-left-color:var(--gold);font-weight:700}
  .icon-s{font-size:18px;width:22px;text-align:center}
  .nav-sep{height:1px;background:rgba(200,146,42,.12);margin:8px 20px}
  .sidebar-bottom{padding:16px;border-top:1px solid rgba(200,146,42,.15)}
  .sidebar-bottom a{display:flex;align-items:center;gap:10px;color:rgba(253,246,227,.5);text-decoration:none;font-size:13.5px;padding:8px 4px;transition:color .2s}
  .sidebar-bottom a:hover{color:#e57373}
  /* MAIN */
  .main{margin-left:220px;flex:1;min-height:100vh}
  .topbar{height:56px;background:#fff;display:flex;align-items:center;justify-content:space-between;padding:0 32px;border-bottom:1px solid rgba(139,94,60,.15);box-shadow:0 2px 8px rgba(0,0,0,.05);position:sticky;top:0;z-index:50}
  .topbar-left h3{font-size:17px;color:var(--ink);font-family:'Playfair Display',serif}
  .topbar-left span{font-size:12.5px;color:#a08060}
  .topbar-btn{padding:7px 18px;border-radius:4px;font-size:13px;font-weight:700;text-decoration:none;background:linear-gradient(135deg,var(--gold),var(--gold2));color:var(--ink);border:none;cursor:pointer;box-shadow:0 2px 8px rgba(200,146,42,.3);transition:filter .2s}
  .topbar-btn:hover{filter:brightness(1.1)}
  .content{padding:32px}
  /* GREETING */
  .greeting-card{background:linear-gradient(135deg,#2b1d0e 0%,#4a3015 60%,#3b240e 100%);border-radius:12px;padding:36px 40px;display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;box-shadow:0 8px 32px rgba(28,20,16,.25);border:1px solid rgba(200,146,42,.2);position:relative;overflow:hidden;animation:fadeUp .6s ease both}
  .greeting-card::before{content:'';position:absolute;top:-40px;right:-40px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(200,146,42,.2) 0%,transparent 70%)}
  @keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
  .greeting-text .sub{font-size:11.5px;letter-spacing:2px;text-transform:uppercase;color:var(--gold);font-weight:700;margin-bottom:8px}
  .greeting-text h2{font-family:'Playfair Display',serif;font-size:30px;color:var(--cream);margin-bottom:6px}
  .greeting-text p{font-size:14px;color:rgba(253,246,227,.55)}
  .greeting-date{text-align:right}
  .greeting-date .day-name{font-family:'Playfair Display',serif;font-size:42px;color:rgba(200,146,42,.25);font-style:italic;line-height:1}
  .greeting-date .full-date{font-size:13px;color:rgba(253,246,227,.5);margin-top:4px}
  /* STATS */
  .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px}
  .stat-card{background:#fff;border-radius:10px;padding:22px 24px;border:1px solid rgba(139,94,60,.12);box-shadow:0 2px 12px rgba(0,0,0,.06);transition:transform .2s,box-shadow .2s;animation:fadeUp .6s ease both}
  .stat-card:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(0,0,0,.1)}
  .stat-icon{font-size:28px;margin-bottom:10px}
  .stat-val{font-size:32px;font-weight:700;color:var(--ink);font-family:'Playfair Display',serif;margin-bottom:4px}
  .stat-label{font-size:12px;color:#a08060;letter-spacing:.5px;text-transform:uppercase;font-weight:700}
  /* ACTIONS */
  .section-title{font-family:'Playfair Display',serif;font-size:20px;color:var(--ink);margin-bottom:16px}
  .section-title span{color:var(--gold);font-style:italic}
  .actions{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:28px}
  .action-card{background:#fff;border-radius:10px;padding:24px;border:1.5px solid rgba(139,94,60,.1);text-decoration:none;color:var(--ink);transition:all .25s;display:flex;align-items:center;gap:18px;box-shadow:0 2px 12px rgba(0,0,0,.06);animation:fadeUp .6s ease both}
  .action-card:hover{border-color:var(--gold);transform:translateY(-3px);box-shadow:0 8px 24px rgba(200,146,42,.15)}
  .action-icon{width:52px;height:52px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:24px}
  .action-icon.amber{background:linear-gradient(135deg,#fef3c7,#fde68a)}
  .action-icon.blue{background:linear-gradient(135deg,#dbeafe,#bfdbfe)}
  .action-icon.green{background:linear-gradient(135deg,#d1fae5,#a7f3d0)}
  .action-icon.rose{background:linear-gradient(135deg,#ffe4e6,#fecdd3)}
  .action-info h4{font-size:15px;font-weight:700;color:var(--ink);margin-bottom:3px}
  .action-info p{font-size:12.5px;color:#a08060}
  /* TIPS */
  .tips-card{background:linear-gradient(135deg,#fff9f0,#fdf6e3);border:1.5px solid rgba(200,146,42,.25);border-radius:10px;padding:24px 28px}
  .tips-card h3{font-family:'Playfair Display',serif;font-size:18px;color:var(--warm);margin-bottom:14px}
  .tip{display:flex;gap:12px;margin-bottom:10px}
  .tip-bullet{color:var(--gold);font-size:18px;flex-shrink:0;line-height:1.4}
  .tip p{font-size:13.5px;color:#5a3e2b;line-height:1.6}
</style>
</head>
<body>
<nav class="sidebar">
  <div class="sidebar-logo">
    <span class="icon">📖</span>
    <h2>Digital Diary</h2>
    <p>Personal Journal</p>
  </div>
  <div class="sidebar-user">
    <div class="avatar"><?= strtoupper(substr($name,0,1)) ?></div>
    <div class="uname"><?= htmlspecialchars($name) ?></div>
    <div class="urole">Diary Author</div>
  </div>
  <div class="nav">
    <a href="start.php" class="active"><span class="icon-s">🏠</span> Dashboard</a>
    <a href="diary.php"><span class="icon-s">✍️</span> Write Entry</a>
    <a href="entries.php"><span class="icon-s">📋</span> Past Entries</a>
    <div class="nav-sep"></div>
    <a href="fonts.php"><span class="icon-s">🔤</span> Fonts &amp; Style</a>
    <a href="features.php"><span class="icon-s">⚙️</span> Features</a>
  </div>
  <div class="sidebar-bottom">
    <a href="logout.php">🚪 Sign Out</a>
  </div>
</nav>
<div class="main">
  <div class="topbar">
    <div class="topbar-left">
      <h3>My Dashboard</h3>
      <span><?= $dayFull ?>, <?= $dateFull ?></span>
    </div>
    <a href="diary.php" class="topbar-btn">+ New Entry</a>
  </div>
  <div class="content">
    <div class="greeting-card">
      <div class="greeting-text">
        <div class="sub">✨ Welcome back</div>
        <h2><?= $greeting ?>, <?= htmlspecialchars(explode(' ',$name)[0]) ?>!</h2>
        <p>Ready to capture today's thoughts and memories?</p>
      </div>
      <div class="greeting-date">
        <div class="day-name"><?= date('D') ?></div>
        <div class="full-date"><?= $dateFull ?></div>
      </div>
    </div>
    <div class="stats">
      <div class="stat-card">
        <div class="stat-icon">📝</div>
        <div class="stat-val"><?= $totalEntries ?></div>
        <div class="stat-label">Total Entries</div>
      </div>
      <div class="stat-card" style="animation-delay:.1s">
        <div class="stat-icon">📅</div>
        <div class="stat-val"><?= $thisMonth ?></div>
        <div class="stat-label">This Month</div>
      </div>
      <div class="stat-card" style="animation-delay:.2s">
        <div class="stat-icon">🔥</div>
        <div class="stat-val"><?= $streakDays ?></div>
        <div class="stat-label">Active Days (30d)</div>
      </div>
    </div>
    <h2 class="section-title">Quick <span>Actions</span></h2>
    <div class="actions">
      <a href="diary.php" class="action-card">
        <div class="action-icon amber">✍️</div>
        <div class="action-info"><h4>Write Today's Entry</h4><p>Capture your thoughts, feelings &amp; memories</p></div>
      </a>
      <a href="entries.php" class="action-card" style="animation-delay:.1s">
        <div class="action-icon blue">📚</div>
        <div class="action-info"><h4>Browse Past Entries</h4><p>Revisit your journey through time</p></div>
      </a>
      <a href="fonts.php" class="action-card" style="animation-delay:.2s">
        <div class="action-icon green">🎨</div>
        <div class="action-info"><h4>Customize Style</h4><p>Change fonts and diary appearance</p></div>
      </a>
      <a href="features.php" class="action-card" style="animation-delay:.3s">
        <div class="action-icon rose">⭐</div>
        <div class="action-info"><h4>All Features</h4><p>Explore everything your diary can do</p></div>
      </a>
    </div>
    <div class="tips-card">
      <h3>📌 Journaling Tips</h3>
      <div class="tip"><span class="tip-bullet">✦</span><p>Write every day, even just a few lines — consistency builds a beautiful record of your life.</p></div>
      <div class="tip"><span class="tip-bullet">✦</span><p>Be honest with yourself. Your diary is your private sanctuary — no judgment here.</p></div>
      <div class="tip"><span class="tip-bullet">✦</span><p>Note small moments too. The little details make memories come alive years later.</p></div>
    </div>
  </div>
</div>
</body>
</html>