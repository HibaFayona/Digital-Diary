<?php
session_start();
require_once "connect.php";
if(!isset($_SESSION['user_id'])){ header("Location: index.php"); exit; }

$uid = $_SESSION['user_id'];
@$db->exec("ALTER TABLE diary_entries ADD COLUMN mood TEXT DEFAULT 'neutral'");
@$db->exec("ALTER TABLE diary_entries ADD COLUMN title TEXT DEFAULT ''");

if(isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $del=$db->prepare("DELETE FROM diary_entries WHERE id=:id AND user_id=:uid");
    $del->bindValue(':id',(int)$_GET['delete'],SQLITE3_INTEGER);
    $del->bindValue(':uid',$uid,SQLITE3_INTEGER);
    $del->execute();
    header("Location: entries.php"); exit;
}

$search      = trim($_GET['q'] ?? '');
$filterMonth = $_GET['month'] ?? '';
$sql  = "SELECT * FROM diary_entries WHERE user_id=:uid";
$params = [':uid'=>$uid];
if($search){ $sql.=" AND (entry LIKE :q OR title LIKE :q)"; $params[':q']="%$search%"; }
if($filterMonth){ $sql.=" AND strftime('%Y-%m',entry_date)=:month"; $params[':month']=$filterMonth; }
$sql.=" ORDER BY entry_date DESC, id DESC";
$stmt=$db->prepare($sql);
foreach($params as $k=>$v) $stmt->bindValue($k,$v,SQLITE3_TEXT);
$res=$stmt->execute();
$entries=[];
while($row=$res->fetchArray(SQLITE3_ASSOC)) $entries[]=$row;
$moodEmoji=['happy'=>'😊','excited'=>'🤩','calm'=>'😌','sad'=>'😢','anxious'=>'😰','angry'=>'😤','love'=>'🥰','neutral'=>'😐'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Past Entries — Digital Diary</title>
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
  .main{margin-left:220px;flex:1;min-height:100vh;display:flex;flex-direction:column}
  .topbar{height:56px;background:#fff;display:flex;align-items:center;justify-content:space-between;padding:0 32px;border-bottom:1px solid rgba(139,94,60,.15);box-shadow:0 2px 8px rgba(0,0,0,.05);position:sticky;top:0;z-index:50}
  .topbar h3{font-family:'Playfair Display',serif;font-size:19px;color:var(--ink)}
  .topbar span{font-size:12.5px;color:#a08060}
  .topbar-btn{padding:7px 18px;border-radius:4px;font-size:13px;font-weight:700;text-decoration:none;background:linear-gradient(135deg,var(--gold),var(--gold2));color:var(--ink);border:none;cursor:pointer}
  .content{padding:28px 32px;flex:1}
  .search-row{display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;align-items:center}
  .search-wrap{position:relative;flex:1;min-width:200px}
  .search-wrap input{width:100%;padding:10px 16px 10px 40px;border:1.5px solid rgba(139,94,60,.2);border-radius:6px;font-family:'Lato',sans-serif;font-size:14px;color:var(--ink);background:#fff;outline:none;transition:border-color .2s}
  .search-wrap input:focus{border-color:var(--gold)}
  .search-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#a08060;font-size:15px}
  .filter-select{padding:10px 14px;border:1.5px solid rgba(139,94,60,.2);border-radius:6px;font-family:'Lato',sans-serif;font-size:13.5px;color:var(--ink);background:#fff;outline:none;cursor:pointer}
  .search-btn{padding:10px 20px;background:linear-gradient(135deg,var(--gold),var(--gold2));border:none;border-radius:6px;font-family:'Lato',sans-serif;font-size:13.5px;font-weight:700;color:var(--ink);cursor:pointer}
  .entries-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px}
  .entry-card{background:#fff;border-radius:10px;border:1px solid rgba(139,94,60,.12);box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden;transition:transform .2s,box-shadow .2s;animation:fadeUp .4s ease both}
  .entry-card:hover{transform:translateY(-4px);box-shadow:0 10px 30px rgba(0,0,0,.12)}
  @keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
  .card-top{height:6px;background:linear-gradient(90deg,var(--gold),var(--gold2))}
  .card-body{padding:18px 20px}
  .card-meta{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
  .card-date{font-size:11px;font-weight:700;color:var(--gold);letter-spacing:.5px;text-transform:uppercase}
  .card-mood{font-size:18px}
  .card-title{font-family:'Playfair Display',serif;font-size:17px;color:var(--ink);margin-bottom:8px;font-weight:700;line-height:1.3}
  .card-title em{font-style:italic;color:#a08060}
  .card-preview{font-size:13.5px;color:#6b4e2a;line-height:1.6;max-height:70px;overflow:hidden;position:relative}
  .card-preview::after{content:'';position:absolute;bottom:0;left:0;right:0;height:24px;background:linear-gradient(transparent,#fff)}
  .card-footer{padding:12px 20px;background:#fafafa;border-top:1px solid rgba(139,94,60,.08);display:flex;justify-content:space-between;align-items:center}
  .card-wc{font-size:11.5px;color:#a08060}
  .card-actions{display:flex;gap:8px}
  .btn-view{padding:5px 14px;background:rgba(200,146,42,.1);color:var(--gold);border:1px solid rgba(200,146,42,.3);border-radius:4px;font-size:12px;font-weight:700;cursor:pointer;transition:background .2s}
  .btn-view:hover{background:rgba(200,146,42,.2)}
  .btn-del{padding:5px 10px;background:rgba(220,50,50,.08);color:#c0392b;border:1px solid rgba(220,50,50,.2);border-radius:4px;font-size:12px;cursor:pointer;transition:background .2s;text-decoration:none}
  .btn-del:hover{background:rgba(220,50,50,.15)}
  .empty-state{text-align:center;padding:80px 20px;color:#a08060}
  .empty-state .emoji{font-size:60px;margin-bottom:16px}
  .empty-state h3{font-family:'Playfair Display',serif;font-size:22px;color:var(--ink);margin-bottom:8px}
  .empty-state p{font-size:14px;margin-bottom:20px}
  .empty-state a{display:inline-block;padding:11px 28px;background:linear-gradient(135deg,var(--gold),var(--gold2));color:var(--ink);border-radius:6px;text-decoration:none;font-weight:700}
  .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px}
  .modal-overlay.open{display:flex}
  .modal{background:var(--cream);border-radius:12px;max-width:680px;width:100%;max-height:80vh;overflow-y:auto;box-shadow:0 30px 80px rgba(0,0,0,.5);border-top:4px solid var(--gold)}
  .modal-head{padding:22px 28px;border-bottom:1px solid rgba(139,94,60,.15);display:flex;justify-content:space-between;align-items:flex-start}
  .modal-head h2{font-family:'Playfair Display',serif;font-size:22px;color:var(--ink)}
  .modal-meta{font-size:12.5px;color:#a08060;margin-top:3px}
  .modal-close{background:none;border:none;font-size:22px;cursor:pointer;color:#a08060;transition:color .2s;line-height:1}
  .modal-close:hover{color:var(--ink)}
  .modal-body{padding:24px 28px;font-size:15px;color:#3a2810;line-height:1.8}
</style>
</head>
<body>
<nav class="sidebar">
  <div class="sidebar-logo"><span class="icon">📖</span><h2>Digital Diary</h2></div>
  <div class="nav">
    <a href="start.php"><span class="icon-s">🏠</span> Dashboard</a>
    <a href="diary.php"><span class="icon-s">✍️</span> Write Entry</a>
    <a href="entries.php" class="active"><span class="icon-s">📋</span> Past Entries</a>
    <div class="nav-sep"></div>
    <a href="fonts.php"><span class="icon-s">🔤</span> Fonts &amp; Style</a>
    <a href="features.php"><span class="icon-s">⚙️</span> Features</a>
  </div>
  <div class="sidebar-bottom"><a href="logout.php">🚪 Sign Out</a></div>
</nav>
<div class="main">
  <div class="topbar">
    <div>
      <h3>Past Entries</h3>
      <span><?= count($entries) ?> <?= count($entries)===1?'entry':'entries' ?> found</span>
    </div>
    <a href="diary.php" class="topbar-btn">+ New Entry</a>
  </div>
  <div class="content">
    <form method="get" action="entries.php">
      <div class="search-row">
        <div class="search-wrap">
          <span class="search-icon">🔍</span>
          <input type="text" name="q" placeholder="Search entries…" value="<?= htmlspecialchars($search) ?>">
        </div>
        <input type="month" name="month" class="filter-select" value="<?= htmlspecialchars($filterMonth) ?>">
        <button type="submit" class="search-btn">Search</button>
        <?php if($search||$filterMonth): ?><a href="entries.php" style="font-size:13px;color:var(--gold);text-decoration:none;padding:10px">Clear ✕</a><?php endif; ?>
      </div>
    </form>
    <?php if(empty($entries)): ?>
    <div class="empty-state">
      <div class="emoji">📭</div>
      <h3><?= $search?"No entries match \"$search\"":"No entries yet" ?></h3>
      <p><?= $search?"Try a different search term.":"Start writing your first diary entry today!" ?></p>
      <?php if(!$search): ?><a href="diary.php">Write My First Entry →</a><?php endif; ?>
    </div>
    <?php else: ?>
    <div class="entries-grid">
      <?php foreach($entries as $i=>$e):
        $wc=str_word_count(strip_tags($e['entry']));
        $hasTitle=!empty(trim($e['title']??''));
        $mood=$e['mood']??'neutral';
        $moodE=$moodEmoji[$mood]??'😐';
      ?>
      <div class="entry-card" style="animation-delay:<?=$i*.05?>s">
        <div class="card-top"></div>
        <div class="card-body">
          <div class="card-meta">
            <span class="card-date">📅 <?= date('M j, Y',strtotime($e['entry_date'])) ?></span>
            <span class="card-mood" title="<?= htmlspecialchars($mood) ?>"><?= $moodE ?></span>
          </div>
          <div class="card-title"><?= $hasTitle?htmlspecialchars($e['title']):'<em>Untitled Entry</em>' ?></div>
          <div class="card-preview"><?= htmlspecialchars(strip_tags($e['entry'])) ?></div>
        </div>
        <div class="card-footer">
          <span class="card-wc">✍️ <?= $wc ?> words</span>
          <div class="card-actions">
            <button class="btn-view" onclick="openModal(<?= htmlspecialchars(json_encode($e)) ?>)">Read</button>
            <a class="btn-del" href="entries.php?delete=<?= (int)$e['id'] ?>" onclick="return confirm('Delete this entry?')">🗑</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
  <div class="modal">
    <div class="modal-head">
      <div>
        <h2 id="modalTitle"></h2>
        <div class="modal-meta" id="modalMeta"></div>
      </div>
      <button class="modal-close" onclick="closeModal(null)">✕</button>
    </div>
    <div class="modal-body"><div id="modalBody"></div></div>
  </div>
</div>

<script>
const moodEmoji={happy:'😊',excited:'🤩',calm:'😌',sad:'😢',anxious:'😰',angry:'😤',love:'🥰',neutral:'😐'};
function openModal(e){
  document.getElementById('modalTitle').textContent=e.title||'Untitled Entry';
  const mood=e.mood||'neutral';
  document.getElementById('modalMeta').textContent='📅 '+e.entry_date+'   '+(moodEmoji[mood]||'😐')+' '+mood.charAt(0).toUpperCase()+mood.slice(1);
  document.getElementById('modalBody').innerHTML=e.entry;
  document.getElementById('modalOverlay').classList.add('open');
  document.body.style.overflow='hidden';
}
function closeModal(ev){
  if(ev&&ev.target!==document.getElementById('modalOverlay'))return;
  document.getElementById('modalOverlay').classList.remove('open');
  document.body.style.overflow='';
}
document.addEventListener('keydown',e=>{ if(e.key==='Escape')closeModal(null); });
</script>
</body>
</html>