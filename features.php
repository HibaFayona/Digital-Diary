<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location:index.php'); exit; }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Features — Digital Diary</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  :root{--ink:#1c1410;--gold:#c8922a;--gold2:#e8b84b;--cream:#fdf6e3;--warm:#8b5e3c;--sidebar:#1e1208}
  body{font-family:'Lato',sans-serif;background:var(--cream);min-height:100vh;display:flex}
  .sidebar{width:220px;min-height:100vh;background:var(--sidebar);display:flex;flex-direction:column;border-right:2px solid rgba(200,146,42,.25);position:fixed;left:0;top:0;bottom:0;z-index:100}
  .sidebar-logo{padding:28px 20px 22px;border-bottom:1px solid rgba(200,146,42,.2);text-align:center}
  .sidebar-logo .icon{font-size:32px;display:block;margin-bottom:8px}
  .sidebar-logo h2{font-family:'Playfair Display',serif;font-size:17px;color:var(--cream)}
  .nav a{display:flex;align-items:center;gap:12px;padding:11px 20px;color:rgba(253,246,227,.65);text-decoration:none;font-size:14px;transition:background .2s,color .2s;border-left:3px solid transparent}
  .nav a:hover{background:rgba(200,146,42,.1);color:var(--cream);border-left-color:var(--gold)}
  .nav a.active{background:rgba(200,146,42,.15);color:var(--gold2);border-left-color:var(--gold);font-weight:700}
  .icon-s{font-size:18px;width:22px;text-align:center}
  .nav-sep{height:1px;background:rgba(200,146,42,.12);margin:8px 20px}
  .sidebar-bottom{padding:16px;border-top:1px solid rgba(200,146,42,.15);margin-top:auto}
  .sidebar-bottom a{display:flex;align-items:center;gap:10px;color:rgba(253,246,227,.5);text-decoration:none;font-size:13.5px;padding:8px 4px;transition:color .2s}
  .sidebar-bottom a:hover{color:#e57373}
  .main{margin-left:220px;flex:1}
  .topbar{height:56px;background:#fff;display:flex;align-items:center;padding:0 32px;border-bottom:1px solid rgba(139,94,60,.15)}
  .topbar h3{font-family:'Playfair Display',serif;font-size:19px;color:var(--ink)}
  .content{padding:32px}
  .page-title{font-family:'Playfair Display',serif;font-size:26px;color:var(--ink);margin-bottom:6px}
  .page-sub{font-size:14px;color:#a08060;margin-bottom:28px}
  .features-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;margin-bottom:36px}
  .feature-card{background:#fff;border-radius:12px;padding:24px;border:1px solid rgba(139,94,60,.1);box-shadow:0 2px 12px rgba(0,0,0,.05);transition:transform .2s,box-shadow .2s;animation:fadeUp .4s ease both}
  .feature-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.1)}
  @keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
  .feature-icon{font-size:36px;margin-bottom:14px}
  .feature-title{font-family:'Playfair Display',serif;font-size:17px;color:var(--ink);margin-bottom:6px}
  .feature-desc{font-size:13.5px;color:#6b4e2a;line-height:1.6;margin-bottom:14px}
  .feature-link{display:inline-flex;align-items:center;gap:6px;color:var(--gold);font-size:13px;font-weight:700;text-decoration:none;transition:gap .2s}
  .feature-link:hover{gap:10px}
  .section-label{font-family:'Playfair Display',serif;font-size:19px;color:var(--ink);margin-bottom:16px}
  .quick-actions{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px}
  .qa-btn{display:flex;align-items:center;gap:14px;padding:16px 18px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;transition:all .2s}
  .qa-btn.primary{background:linear-gradient(135deg,var(--gold),var(--gold2));color:var(--ink);box-shadow:0 4px 16px rgba(200,146,42,.3)}
  .qa-btn.primary:hover{filter:brightness(1.08);transform:translateY(-2px)}
  .qa-btn.dark{background:var(--sidebar);color:var(--cream)}
  .qa-btn.dark:hover{background:#2a1a0e}
  .qa-btn.danger{background:#fff0f0;color:#c0392b;border:1.5px solid rgba(220,50,50,.2)}
  .qa-btn.danger:hover{background:#ffe5e5}
  .qa-icon{font-size:22px}
</style>
</head>
<body>
<nav class="sidebar">
  <div class="sidebar-logo"><span class="icon">📖</span><h2>Digital Diary</h2></div>
  <div class="nav">
    <a href="start.php"><span class="icon-s">🏠</span> Dashboard</a>
    <a href="diary.php"><span class="icon-s">✍️</span> Write Entry</a>
    <a href="entries.php"><span class="icon-s">📋</span> Past Entries</a>
    <div class="nav-sep"></div>
    <a href="fonts.php"><span class="icon-s">🔤</span> Fonts &amp; Style</a>
    <a href="features.php" class="active"><span class="icon-s">⚙️</span> Features</a>
  </div>
  <div class="sidebar-bottom"><a href="logout.php">🚪 Sign Out</a></div>
</nav>
<div class="main">
  <div class="topbar"><h3>⚙️ Features Overview</h3></div>
  <div class="content">
    <h1 class="page-title">Everything Your Diary Can Do</h1>
    <p class="page-sub">Discover all the ways to make your journaling experience beautiful and personal.</p>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">🎨</div>
        <h3 class="feature-title">5 Beautiful Themes</h3>
        <p class="feature-desc">Switch between Parchment, Midnight, Forest, Rose, and Ocean themes instantly from the diary page.</p>
        <a class="feature-link" href="diary.php">Open Diary →</a>
      </div>
      <div class="feature-card" style="animation-delay:.05s">
        <div class="feature-icon">✍️</div>
        <h3 class="feature-title">Rich Text Editor</h3>
        <p class="feature-desc">Bold, italic, underline, text colors, highlights, bullet lists, alignment — full formatting toolbar.</p>
        <a class="feature-link" href="diary.php">Start Writing →</a>
      </div>
      <div class="feature-card" style="animation-delay:.1s">
        <div class="feature-icon">🔤</div>
        <h3 class="feature-title">7 Font Choices</h3>
        <p class="feature-desc">Elegant Playfair, handwritten Dancing Script, clean Lato, monospace and more.</p>
        <a class="feature-link" href="fonts.php">Browse Fonts →</a>
      </div>
      <div class="feature-card" style="animation-delay:.15s">
        <div class="feature-icon">😊</div>
        <h3 class="feature-title">Mood Tracker</h3>
        <p class="feature-desc">Log your mood with every entry. Track your emotional journey over time.</p>
        <a class="feature-link" href="diary.php">Log Mood →</a>
      </div>
      <div class="feature-card" style="animation-delay:.2s">
        <div class="feature-icon">📋</div>
        <h3 class="feature-title">Entry History</h3>
        <p class="feature-desc">Browse, search, and filter all past entries. Read them in a full-screen modal view.</p>
        <a class="feature-link" href="entries.php">View Entries →</a>
      </div>
      <div class="feature-card" style="animation-delay:.25s">
        <div class="feature-icon">🖼</div>
        <h3 class="feature-title">Custom Background</h3>
        <p class="feature-desc">Upload your own photo as a background for the writing area.</p>
        <a class="feature-link" href="diary.php">Personalize →</a>
      </div>
    </div>
    <h2 class="section-label">Quick Actions</h2>
    <div class="quick-actions">
      <a href="diary.php"   class="qa-btn primary"><span class="qa-icon">✍️</span> Write Today's Entry</a>
      <a href="entries.php" class="qa-btn dark"><span class="qa-icon">📚</span> Browse Past Entries</a>
      <a href="fonts.php"   class="qa-btn dark"><span class="qa-icon">🔤</span> Change Fonts</a>
      <a href="start.php"   class="qa-btn dark"><span class="qa-icon">🏠</span> Dashboard</a>
      <a href="logout.php"  class="qa-btn danger"><span class="qa-icon">🚪</span> Log Out</a>
    </div>
  </div>
</div>
</body>
</html>