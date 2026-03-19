<?php
session_start();
require_once "connect.php";
if(!isset($_SESSION['user_id'])){ header("Location: index.php"); exit; }

$uid       = $_SESSION['user_id'];
$fontCss   = $_SESSION['font']  ?? 'Lato, sans-serif';
$themeName = $_SESSION['theme'] ?? 'parchment';
$msg = '';

if(isset($_POST['save'])){
    $entry = trim($_POST['entry']);
    $mood  = trim($_POST['mood'] ?? 'neutral');
    if($entry != ""){
        $date  = date("Y-m-d");
        $title = trim($_POST['title'] ?? '');
        $ins = $db->prepare("INSERT INTO diary_entries(user_id,entry,entry_date,mood,title) VALUES(:uid,:entry,:d,:mood,:title)");
        $ins->bindValue(':uid',   $uid,   SQLITE3_INTEGER);
        $ins->bindValue(':entry', $entry, SQLITE3_TEXT);
        $ins->bindValue(':d',     $date,  SQLITE3_TEXT);
        $ins->bindValue(':mood',  $mood,  SQLITE3_TEXT);
        $ins->bindValue(':title', $title, SQLITE3_TEXT);
        $ins->execute();
        $msg = 'saved';
    }
}

$day        = date("D");
$datePretty = date("F j, Y");

$themes = [
  'parchment' => ['bg'=>'#f5ead0','paper'=>'#fdf6e3','border'=>'#8b5e3c','text'=>'#2a1a0a','sidebar'=>'#d6c4a0','accent'=>'#c8922a'],
  'midnight'  => ['bg'=>'#0d1117','paper'=>'#161b22','border'=>'#30363d','text'=>'#e6edf3','sidebar'=>'#1c2128','accent'=>'#388bfd'],
  'forest'    => ['bg'=>'#1a2e1a','paper'=>'#243324','border'=>'#3d6b3d','text'=>'#d4edda','sidebar'=>'#1e2e1e','accent'=>'#56ab6a'],
  'rose'      => ['bg'=>'#2d1520','paper'=>'#3d2030','border'=>'#7a3055','text'=>'#fce4ec','sidebar'=>'#2a1228','accent'=>'#e91e8c'],
  'ocean'     => ['bg'=>'#0a1929','paper'=>'#102a43','border'=>'#1565c0','text'=>'#e3f2fd','sidebar'=>'#0d2137','accent'=>'#29b6f6'],
];
$t = $themes[$themeName] ?? $themes['parchment'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Write — Digital Diary</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&family=Merriweather:ital,wght@0,400;1,400&family=Source+Code+Pro:wght@400&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  :root{
    --bg:    <?= $t['bg'] ?>;
    --paper: <?= $t['paper'] ?>;
    --border:<?= $t['border'] ?>;
    --text:  <?= $t['text'] ?>;
    --sidebar:<?= $t['sidebar'] ?>;
    --accent:<?= $t['accent'] ?>;
    --font:  <?= $fontCss ?>;
  }
  body{font-family:var(--font);background:var(--bg);min-height:100vh;color:var(--text);transition:background .4s,color .4s}
  /* TOPBAR */
  .topbar{height:52px;background:rgba(0,0,0,.35);backdrop-filter:blur(12px);display:flex;align-items:center;padding:0 24px;gap:16px;border-bottom:1px solid rgba(255,255,255,.08);position:sticky;top:0;z-index:200}
  .topbar-brand{font-family:'Playfair Display',serif;font-size:17px;color:var(--accent);font-style:italic;flex:1}
  .topbar-nav a{color:rgba(255,255,255,.6);text-decoration:none;font-size:13px;padding:5px 12px;border-radius:4px;margin-left:6px;transition:background .2s,color .2s}
  .topbar-nav a:hover{background:rgba(255,255,255,.1);color:#fff}
  /* LAYOUT */
  .layout{display:flex;min-height:calc(100vh - 52px)}
  /* LEFT PANEL */
  .left-panel{width:260px;flex-shrink:0;background:var(--sidebar);border-right:1px solid rgba(255,255,255,.07);padding:20px 16px;overflow-y:auto}
  .panel-section{margin-bottom:22px}
  .panel-title{font-size:9.5px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--accent);opacity:.8;margin-bottom:10px}
  /* THEME SWATCHES */
  .theme-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:6px}
  .theme-swatch{width:100%;aspect-ratio:1;border-radius:6px;cursor:pointer;border:2px solid transparent;transition:transform .2s,border-color .2s}
  .theme-swatch:hover{transform:scale(1.12)}
  .theme-swatch.active{border-color:#fff}
  .theme-swatch[data-theme="parchment"]{background:linear-gradient(135deg,#f5ead0,#8b5e3c)}
  .theme-swatch[data-theme="midnight"]{background:linear-gradient(135deg,#0d1117,#388bfd)}
  .theme-swatch[data-theme="forest"]{background:linear-gradient(135deg,#1a2e1a,#56ab6a)}
  .theme-swatch[data-theme="rose"]{background:linear-gradient(135deg,#2d1520,#e91e8c)}
  .theme-swatch[data-theme="ocean"]{background:linear-gradient(135deg,#0a1929,#29b6f6)}
  /* FONT SELECT */
  .font-select{width:100%;padding:8px 10px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:6px;color:var(--text);font-size:13px;outline:none;cursor:pointer}
  .font-select option{background:#1a1a1a}
  /* SIZE SLIDER */
  .size-row{display:flex;align-items:center;gap:10px}
  .size-row input[type=range]{flex:1;accent-color:var(--accent)}
  .size-val{font-size:12px;color:var(--accent);font-weight:700;min-width:28px;text-align:right}
  /* COLOR DOTS */
  .color-row{display:flex;flex-wrap:wrap;gap:6px}
  .color-dot{width:24px;height:24px;border-radius:50%;cursor:pointer;border:2px solid transparent;transition:transform .15s,border-color .15s}
  .color-dot:hover{transform:scale(1.2);border-color:#fff}
  .color-dot.active{border-color:#fff;transform:scale(1.15)}
  /* HIGHLIGHT */
  .hl-row{display:flex;flex-wrap:wrap;gap:6px}
  .hl-dot{width:24px;height:24px;border-radius:4px;cursor:pointer;border:2px solid transparent;transition:transform .15s}
  .hl-dot:hover{transform:scale(1.2);border-color:rgba(255,255,255,.6)}
  /* MOOD */
  .mood-row{display:flex;flex-wrap:wrap;gap:6px}
  .mood-btn{padding:5px 9px;border-radius:20px;font-size:17px;background:rgba(255,255,255,.07);border:1.5px solid rgba(255,255,255,.1);cursor:pointer;transition:all .2s}
  .mood-btn:hover{background:rgba(255,255,255,.15)}
  .mood-btn.active{border-color:var(--accent);background:rgba(255,255,255,.15)}
  /* BG BUTTONS */
  .bg-btn{width:100%;padding:8px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:6px;color:var(--text);font-size:12.5px;cursor:pointer;text-align:center;transition:background .2s;margin-bottom:6px}
  .bg-btn:hover{background:rgba(255,255,255,.15)}
  /* DIARY CENTER */
  .diary-center{flex:1;display:flex;flex-direction:column;align-items:center;padding:32px 24px;overflow-y:auto}
  /* META */
  .diary-meta{width:100%;max-width:720px;margin-bottom:14px}
  .diary-meta-row{display:flex;gap:12px;align-items:center;margin-bottom:10px;flex-wrap:wrap}
  .entry-date-badge{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:20px;padding:4px 14px;font-size:12.5px;color:var(--accent);font-weight:700}
  .day-badge{background:var(--accent);color:var(--bg);border-radius:4px;padding:3px 10px;font-size:12px;font-weight:700}
  .word-count-badge{margin-left:auto;font-size:12px;color:rgba(255,255,255,.4)}
  /* TITLE */
  .title-input{width:100%;max-width:720px;background:transparent;border:none;border-bottom:2px solid rgba(255,255,255,.12);outline:none;font-family:'Playfair Display',serif;font-size:28px;color:var(--text);padding:6px 0;margin-bottom:16px;transition:border-color .2s}
  .title-input::placeholder{color:rgba(255,255,255,.2)}
  .title-input:focus{border-bottom-color:var(--accent)}
  /* PAPER */
  .diary-paper{width:100%;max-width:720px;background:var(--paper);border-radius:8px;box-shadow:0 12px 48px rgba(0,0,0,.35),0 0 0 1px rgba(255,255,255,.06);border-top:4px solid var(--accent);overflow:hidden;position:relative}
  .diary-paper::before{content:'';position:absolute;left:56px;top:0;bottom:0;width:1px;background:rgba(255,0,0,.12);pointer-events:none}
  /* TOOLBAR */
  .toolbar{display:flex;align-items:center;flex-wrap:wrap;gap:4px;padding:10px 16px 8px;border-bottom:1px solid rgba(255,255,255,.07);background:rgba(0,0,0,.1)}
  .tool{width:30px;height:28px;border-radius:4px;border:none;background:transparent;color:var(--text);cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center;transition:background .15s}
  .tool:hover{background:rgba(255,255,255,.12)}
  .tool-sep{width:1px;height:20px;background:rgba(255,255,255,.12);margin:0 4px}
  /* ENTRY AREA */
  #entryArea{width:100%;min-height:380px;padding:22px 24px 22px 68px;background:transparent;border:none;outline:none;font-family:var(--font);font-size:15.5px;line-height:1.85;color:var(--text);resize:none;transition:font-family .3s,font-size .2s,color .3s;background-image:repeating-linear-gradient(transparent,transparent 32px,rgba(255,255,255,.04) 32px,rgba(255,255,255,.04) 33px);background-attachment:local}
  #entryArea:empty::before{content:attr(data-placeholder);color:rgba(255,255,255,.2);pointer-events:none}
  /* ACTIONS */
  .diary-actions{width:100%;max-width:720px;margin-top:16px;display:flex;gap:12px;align-items:center}
  .btn-save{padding:11px 28px;background:linear-gradient(135deg,var(--accent),#fff);border:none;border-radius:6px;font-size:14px;font-weight:700;color:#111;cursor:pointer;transition:filter .2s,transform .15s;box-shadow:0 4px 16px rgba(0,0,0,.3);letter-spacing:.5px}
  .btn-save:hover{filter:brightness(1.1);transform:translateY(-1px)}
  .btn-clear{padding:10px 20px;border:1.5px solid rgba(255,255,255,.2);border-radius:6px;background:transparent;color:var(--text);font-size:14px;cursor:pointer;transition:background .2s}
  .btn-clear:hover{background:rgba(255,255,255,.08)}
  /* TOAST */
  .toast{position:fixed;bottom:28px;right:28px;z-index:999;background:#22c55e;color:#fff;padding:12px 22px;border-radius:8px;font-size:14px;font-weight:700;box-shadow:0 8px 24px rgba(0,0,0,.3);animation:toastIn .4s ease both}
  @keyframes toastIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
  #imgFileInput{display:none}
</style>
</head>
<body>
<div class="topbar">
  <div class="topbar-brand">📖 Digital Diary</div>
  <nav class="topbar-nav">
    <a href="start.php">🏠 Home</a>
    <a href="entries.php">📋 Entries</a>
    <a href="logout.php">🚪 Logout</a>
  </nav>
</div>

<form method="post" id="diaryForm">
<input type="hidden" name="mood"  id="moodInput"  value="neutral">
<input type="hidden" name="entry" id="entryHidden" value="">
<input type="hidden" name="theme" id="themeInput"  value="<?= htmlspecialchars($themeName) ?>">

<div class="layout">
  <!-- LEFT PANEL -->
  <aside class="left-panel">
    <div class="panel-section">
      <div class="panel-title">🎨 Theme</div>
      <div class="theme-grid">
        <?php foreach(array_keys($themes) as $tk): ?>
        <div class="theme-swatch <?= $tk===$themeName?'active':'' ?>" data-theme="<?= $tk ?>" title="<?= ucfirst($tk) ?>"></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="panel-section">
      <div class="panel-title">🔤 Font Family</div>
      <select class="font-select" id="fontSelect">
        <option value="'Lato', sans-serif"           <?= strpos($fontCss,'Lato')!==false?'selected':'' ?>>Lato (Modern)</option>
        <option value="'Playfair Display', serif"    <?= strpos($fontCss,'Playfair')!==false?'selected':'' ?>>Playfair (Elegant)</option>
        <option value="'Merriweather', serif"        <?= strpos($fontCss,'Merriweather')!==false?'selected':'' ?>>Merriweather (Classic)</option>
        <option value="'Dancing Script', cursive"    <?= strpos($fontCss,'Dancing')!==false?'selected':'' ?>>Dancing (Handwritten)</option>
        <option value="'Source Code Pro', monospace" <?= strpos($fontCss,'Source Code')!==false?'selected':'' ?>>Source Code (Mono)</option>
        <option value="Georgia, serif"               <?= strpos($fontCss,'Georgia')!==false?'selected':'' ?>>Georgia (Newspaper)</option>
        <option value="Impact, sans-serif"           <?= strpos($fontCss,'Impact')!==false?'selected':'' ?>>Impact (Bold)</option>
      </select>
    </div>
    <div class="panel-section">
      <div class="panel-title">📏 Font Size</div>
      <div class="size-row">
        <input type="range" id="fontSize" min="12" max="26" value="15" step="1">
        <span class="size-val" id="sizeLabel">15</span>
      </div>
    </div>
    <div class="panel-section">
      <div class="panel-title">🖊 Text Color</div>
      <div class="color-row">
        <?php foreach(['#ffffff','#f5ead0','#ffd700','#ff8c69','#90ee90','#87ceeb','#dda0dd','#ff69b4','#ff4444','#4444ff','#1c1410','#2a1a0a'] as $c): ?>
        <div class="color-dot" style="background:<?=$c?>" data-color="<?=$c?>"></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="panel-section">
      <div class="panel-title">🌟 Highlight Color</div>
      <div class="hl-row">
        <?php foreach(['#ffff00','#90ee90','#87ceeb','#ffb6c1','#e6e6fa','#ffa500','transparent'] as $h): ?>
        <div class="hl-dot" style="background:<?=$h==='transparent'?'repeating-linear-gradient(45deg,#aaa 0,#aaa 4px,transparent 4px,transparent 8px)':$h?>" data-hl="<?=$h?>"></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="panel-section">
      <div class="panel-title">😊 Today's Mood</div>
      <div class="mood-row">
        <button type="button" class="mood-btn active" data-mood="neutral">😐</button>
        <button type="button" class="mood-btn" data-mood="happy">😊</button>
        <button type="button" class="mood-btn" data-mood="excited">🤩</button>
        <button type="button" class="mood-btn" data-mood="calm">😌</button>
        <button type="button" class="mood-btn" data-mood="sad">😢</button>
        <button type="button" class="mood-btn" data-mood="anxious">😰</button>
        <button type="button" class="mood-btn" data-mood="angry">😤</button>
        <button type="button" class="mood-btn" data-mood="love">🥰</button>
      </div>
    </div>
    <div class="panel-section">
      <div class="panel-title">🖼 Custom Background</div>
      <button type="button" class="bg-btn" id="bgUploadBtn">📁 Upload Image</button>
      <button type="button" class="bg-btn" id="bgClearBtn">✕ Clear Image</button>
      <input type="file" id="imgFileInput" accept="image/*">
    </div>
  </aside>

  <!-- DIARY AREA -->
  <div class="diary-center" id="diaryCenterEl">
    <div class="diary-meta">
      <div class="diary-meta-row">
        <span class="day-badge"><?= $day ?></span>
        <span class="entry-date-badge">📅 <?= $datePretty ?></span>
        <span class="word-count-badge" id="wordCount">0 words</span>
      </div>
    </div>
    <input type="text" name="title" class="title-input" placeholder="Entry title (optional)…">
    <div class="diary-paper" id="diaryPaper">
      <div class="toolbar">
        <button type="button" class="tool" onclick="fmt('bold')" title="Bold"><b>B</b></button>
        <button type="button" class="tool" onclick="fmt('italic')" title="Italic"><i>I</i></button>
        <button type="button" class="tool" onclick="fmt('underline')" title="Underline"><u>U</u></button>
        <button type="button" class="tool" onclick="fmt('strikeThrough')" title="Strike"><s>S</s></button>
        <div class="tool-sep"></div>
        <button type="button" class="tool" onclick="fmt('insertUnorderedList')" title="Bullets">≡</button>
        <button type="button" class="tool" onclick="fmt('insertOrderedList')" title="Numbers">1.</button>
        <div class="tool-sep"></div>
        <button type="button" class="tool" onclick="fmt('justifyLeft')"   title="Left">⫷</button>
        <button type="button" class="tool" onclick="fmt('justifyCenter')" title="Center">☰</button>
        <button type="button" class="tool" onclick="fmt('justifyRight')"  title="Right">⫸</button>
        <div class="tool-sep"></div>
        <button type="button" class="tool" onclick="insertDate()" title="Insert date">📅</button>
        <button type="button" class="tool" onclick="document.execCommand('removeFormat')" title="Clear format">Tx</button>
      </div>
      <div id="entryArea" contenteditable="true" spellcheck="true" data-placeholder="Dear Diary,&#10;&#10;Today was…"></div>
    </div>
    <div class="diary-actions">
      <button type="submit" name="save" class="btn-save" onclick="prepSave()">💾 Save Entry</button>
      <button type="button" class="btn-clear" onclick="clearEntry()">🗑 Clear</button>
      <a href="entries.php" style="margin-left:auto;color:var(--accent);text-decoration:none;font-size:13px">View all entries →</a>
    </div>
  </div>
</div>
</form>

<?php if($msg==='saved'): ?>
<div class="toast" id="savedToast">✅ Entry saved successfully!</div>
<script>
  setTimeout(()=>{
    const t=document.getElementById('savedToast');
    if(t){t.style.opacity='0';t.style.transform='translateY(20px)';t.style.transition='.4s';setTimeout(()=>t.remove(),400);}
  },2500);
</script>
<?php endif; ?>

<script>
function fmt(cmd){ document.execCommand(cmd,false,null); }
function insertDate(){ document.execCommand('insertText',false,new Date().toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'})); }
function prepSave(){ document.getElementById('entryHidden').value=document.getElementById('entryArea').innerHTML; }
function clearEntry(){ if(confirm('Clear all content?')){ document.getElementById('entryArea').innerHTML=''; document.querySelector('.title-input').value=''; updateWordCount(); } }

function updateWordCount(){
  const txt=document.getElementById('entryArea').innerText||'';
  const w=txt.trim().split(/\s+/).filter(x=>x.length>0).length;
  document.getElementById('wordCount').textContent=w+(w===1?' word':' words');
}
document.getElementById('entryArea').addEventListener('input',updateWordCount);

document.getElementById('fontSelect').addEventListener('change',function(){
  document.getElementById('entryArea').style.fontFamily=this.value;
  fetch('set_pref.php?font='+encodeURIComponent(this.value));
});

const sizeSlider=document.getElementById('fontSize');
sizeSlider.addEventListener('input',function(){
  document.getElementById('sizeLabel').textContent=this.value;
  document.getElementById('entryArea').style.fontSize=this.value+'px';
});

document.querySelectorAll('.color-dot').forEach(dot=>{
  dot.addEventListener('click',function(){
    document.querySelectorAll('.color-dot').forEach(d=>d.classList.remove('active'));
    this.classList.add('active');
    document.execCommand('foreColor',false,this.dataset.color);
  });
});

document.querySelectorAll('.hl-dot').forEach(dot=>{
  dot.addEventListener('click',function(){
    const hl=this.dataset.hl;
    document.execCommand('hiliteColor',false,hl==='transparent'?'transparent':hl);
  });
});

document.querySelectorAll('.mood-btn').forEach(btn=>{
  btn.addEventListener('click',function(){
    document.querySelectorAll('.mood-btn').forEach(b=>b.classList.remove('active'));
    this.classList.add('active');
    document.getElementById('moodInput').value=this.dataset.mood;
  });
});

document.querySelectorAll('.theme-swatch').forEach(sw=>{
  sw.addEventListener('click',function(){
    document.getElementById('themeInput').value=this.dataset.theme;
    fetch('set_pref.php?theme='+encodeURIComponent(this.dataset.theme)).then(()=>location.reload());
  });
});

document.getElementById('bgUploadBtn').addEventListener('click',()=>document.getElementById('imgFileInput').click());
document.getElementById('imgFileInput').addEventListener('change',function(){
  const file=this.files[0]; if(!file) return;
  const r=new FileReader();
  r.onload=e=>{ const c=document.getElementById('diaryCenterEl'); c.style.backgroundImage=`url(${e.target.result})`; c.style.backgroundSize='cover'; c.style.backgroundPosition='center'; };
  r.readAsDataURL(file);
});
document.getElementById('bgClearBtn').addEventListener('click',()=>{ const c=document.getElementById('diaryCenterEl'); c.style.backgroundImage=''; });
</script>
</body>
</html>