<?php
require_once __DIR__ . '/connect.php';
$msg = '';

if (isset($_POST['register'])) {
    $name     = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $db->prepare('SELECT id FROM users WHERE username = :u');
    $check->bindValue(':u', $username, SQLITE3_TEXT);
    $r = $check->execute()->fetchArray(SQLITE3_ASSOC);
    if ($r) {
        $msg = 'Username already exists. Please choose another.';
    } else {
        $ins = $db->prepare('INSERT INTO users (name,username,email,password) VALUES (:n,:u,:e,:p)');
        $ins->bindValue(':n', $name,     SQLITE3_TEXT);
        $ins->bindValue(':u', $username, SQLITE3_TEXT);
        $ins->bindValue(':e', $email,    SQLITE3_TEXT);
        $ins->bindValue(':p', $password, SQLITE3_TEXT);
        $ins->execute();
        header('Location: index.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Digital Diary — Register</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  :root{--ink:#1c1410;--gold:#c8922a;--gold2:#e8b84b;--cream:#fdf6e3;--warm:#8b5e3c}
  body{min-height:100vh;font-family:'Lato',sans-serif;background:var(--cream)}
  .bg-wrap{position:fixed;inset:0;z-index:0;background:linear-gradient(160deg,#3b1f08 0%,#2b1505 50%,#1a1005 100%);overflow:hidden}
  .bg-wrap::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 70% 40% at 80% 20%,rgba(200,146,42,.15) 0%,transparent 70%),radial-gradient(ellipse 50% 60% at 10% 80%,rgba(139,94,60,.18) 0%,transparent 70%)}
  .lines{position:absolute;inset:0;background-image:repeating-linear-gradient(0deg,transparent,transparent 34px,rgba(200,146,42,.05) 34px,rgba(200,146,42,.05) 35px)}
  .page-wrap{position:relative;z-index:1;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:30px 20px}
  .reg-card{width:480px;background:var(--cream);border-radius:4px;box-shadow:0 30px 80px rgba(0,0,0,.55),0 0 0 1px rgba(200,146,42,.25);overflow:hidden;animation:cardIn .7s cubic-bezier(.22,.68,0,1.2) both}
  @keyframes cardIn{from{opacity:0;transform:translateY(30px) scale(.96)}to{opacity:1;transform:none}}
  .card-header{background:linear-gradient(135deg,#1c1410 0%,#3b240e 100%);padding:30px 40px 24px;text-align:center;border-bottom:3px solid var(--gold)}
  .diary-icon{font-size:38px;margin-bottom:8px;display:block}
  .card-header h1{font-family:'Playfair Display',serif;font-size:26px;color:var(--cream);margin-bottom:4px}
  .card-header p{color:rgba(253,246,227,.5);font-size:12px;letter-spacing:1.5px;text-transform:uppercase;font-weight:300}
  .card-body{padding:32px 40px 28px}
  .err-msg{background:#fff0f0;border-left:4px solid #d94f4f;color:#7a1515;padding:10px 14px;font-size:13.5px;border-radius:0 3px 3px 0;margin-bottom:20px}
  .field{margin-bottom:16px}
  .field label{display:block;font-size:11px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase;color:var(--warm);margin-bottom:6px}
  .field input{width:100%;padding:11px 16px;border:1.5px solid rgba(139,94,60,.25);border-radius:3px;font-family:'Lato',sans-serif;font-size:14.5px;color:var(--ink);background:#fff;outline:none;transition:border-color .2s,box-shadow .2s}
  .field input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(200,146,42,.15)}
  .btn-reg{width:100%;padding:13px;background:linear-gradient(135deg,var(--gold) 0%,var(--gold2) 100%);border:none;border-radius:3px;font-family:'Lato',sans-serif;font-size:14px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--ink);cursor:pointer;margin-top:6px;box-shadow:0 4px 16px rgba(200,146,42,.35);transition:filter .2s,transform .15s}
  .btn-reg:hover{filter:brightness(1.08);transform:translateY(-1px)}
  .card-footer{padding:14px 40px 22px;text-align:center;border-top:1px solid rgba(139,94,60,.15);background:rgba(245,234,208,.5)}
  .card-footer p{font-size:13.5px;color:#6b4e2a}
  .card-footer a{color:var(--gold);font-weight:700;text-decoration:none}
  .card-footer a:hover{text-decoration:underline}
  .row2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
</style>
</head>
<body>
<div class="bg-wrap"><div class="lines"></div></div>
<div class="page-wrap">
  <div class="reg-card">
    <div class="card-header">
      <span class="diary-icon">✍️</span>
      <h1>Create Your Diary</h1>
      <p>Begin your journaling journey</p>
    </div>
    <div class="card-body">
      <?php if($msg): ?>
        <div class="err-msg">⚠ <?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>
      <form method="post" autocomplete="off">
        <div class="row2">
          <div class="field">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="Your name" required>
          </div>
          <div class="field">
            <label>Username</label>
            <input type="text" name="username" placeholder="Choose username" required>
          </div>
        </div>
        <div class="field">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="your@email.com" required>
        </div>
        <div class="field">
          <label>Password</label>
          <input type="password" name="password" placeholder="Create a strong password" required>
        </div>
        <button name="register" type="submit" class="btn-reg">Create My Diary →</button>
      </form>
    </div>
    <div class="card-footer">
      <p>Already have an account? <a href="index.php">Sign in →</a></p>
    </div>
  </div>
</div>
</body>
</html>