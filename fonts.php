<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location:index.php'); exit; }
$allowedFonts=[
  "'Lato', sans-serif"           =>['label'=>'Lato',          'desc'=>'Clean & Modern',     'sample'=>'The quick brown fox jumps over the lazy dog.'],
  "'Playfair Display', serif"    =>['label'=>'Playfair',       'desc'=>'Elegant & Serif',    'sample'=>'The quick brown fox jumps over the lazy dog.'],
  "'Merriweather', serif"        =>['label'=>'Merriweather',   'desc'=>'Classic & Readable', 'sample'=>'The quick brown fox jumps over the lazy dog.'],
  "'Dancing Script', cursive"    =>['label'=>'Dancing Script', 'desc'=>'Handwritten Style',  'sample'=>'The quick brown fox jumps over the lazy dog.'],
  "'Source Code Pro', monospace" =>['label'=>'Source Code',    'desc'=>'Typewriter Mono',    'sample'=>'The quick brown fox jumps over the lazy dog.'],
  "Georgia, serif"               =>['label'=>'Georgia',        'desc'=>'Newspaper Classic',  'sample'=>'The quick brown fox jumps over the lazy dog.'],
  "Impact, sans-serif"           =>['label'=>'Impact',         'desc'=>'Bold & Punchy',      'sample'=>'THE QUICK BROWN FOX JUMPS OVER THE LAZY DOG.'],
];
if(isset($_GET['font'])&&array_key_exists($_GET['font'],$allowedFonts)){
  $_SESSION['font']=$_GET['font'];
  header('Location: diary.php'); exit;
}
$current=$_SESSION['font']??"'Lato', sans-serif";
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Fonts — Digital Diary</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&family=Merriweather:ital,wght@0,400;1,400&family=Source+Code+Pro:wght@400&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
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
  .page-title{font-family:'Playfair Display',serif;font-size:24px;color:var(--ink);margin-bottom:6px}
  .page-sub{font-size:13.5px;color:#a08060;margin-bottom:28px}
  .fonts-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px}
  .font-card{background:#fff;border-radius:10px;border:2px solid rgba(139,94,60,.12);overflow:hidden;cursor:pointer;transition:all .25s;animation:fadeUp .4s ease both}
  .font-card:hover{border-color:var(--gold);transform:translateY(-3px);box-shadow:0 8px 24px rgba(200,146,42,.15)}
  .font-card.active{border-color:var(--gold);box-shadow:0 0 0 3px rgba(200,146,42,.2)}
  @keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
  .card-top{height:5px;background:linear-gradient(90deg,var(--gold),var(--gold2))}
  .card-body{padding:20px 22px}
  .font-label{font-size:11px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase;color:var(--gold);margin-bottom:4px}
  .font-desc{font-size:12px;color:#a08060;margin-bottom:14px}
  .font-sample{font-size:18px;color:var(--ink);line-height:1.5;min-height:52px}
  .card-footer-row{display:flex;justify-content:space-between;align-items:center;padding:10px 22px 14px}
  .active-badge{background:rgba(200,146,42,.15);color:var(--gold);border-radius:20px;padding:3px 12px;font-size:11px;font-weight:700}
  .select-btn{padding:6px 16px;background:linear-gradient(135deg,var(--gold),var(--gold2));border:none;border-radius:4px;font-size:12px;font-weight:700;color:var(--ink);cursor:pointer;text-decoration:none;transition:filter .2s}
  .select-btn:hover{filter:brightness(1.1)}
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
    <a href="fonts.php" class="active"><span class="icon-s">🔤</span> Fonts &amp; Style</a>
    <a href="features.php"><span class="icon-s">⚙️</span> Features</a>
  </div>
  <div class="sidebar-bottom"><a href="logout.php">🚪 Sign Out</a></div>
</nav>
<div class="main">
  <div class="topbar"><h3>🔤 Fonts &amp; Typography</h3></div>
  <div class="content">
    <h1 class="page-title">Choose Your Font</h1>
    <p class="page-sub">Your selected font will be used in the diary writing area.</p>
    <div class="fonts-grid">
      <?php foreach($allowedFonts as $fv=>$info): $isActive=($fv===$current); ?>
      <div class="font-card <?=$isActive?'active':''?>">
        <div class="card-top"></div>
        <div class="card-body">
          <div class="font-label"><?=htmlspecialchars($info['label'])?></div>
          <div class="font-desc"><?=htmlspecialchars($info['desc'])?></div>
          <div class="font-sample" style="font-family:<?=$fv?>"><?=htmlspecialchars($info['sample'])?></div>
        </div>
        <div class="card-footer-row">
          <?php if($isActive): ?><span class="active-badge">✓ Active</span><?php else: ?><span></span><?php endif; ?>
          <a class="select-btn" href="?font=<?=urlencode($fv)?>"><?=$isActive?'Selected':'Use This Font'?></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</body>
</html>