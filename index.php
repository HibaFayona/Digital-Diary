<?php
session_start();
require_once __DIR__ . '/connect.php';
$error = '';

if(isset($_POST['login'])){
    $user = trim($_POST['username']);
    $pass = $_POST['password'];
    $stmt = $db->prepare('SELECT * FROM users WHERE username = :u');
    $stmt->bindValue(':u', $user, SQLITE3_TEXT);
    $res = $stmt->execute();
    $row = $res->fetchArray(SQLITE3_ASSOC);
    if($row && password_verify($pass, $row['password'])){
        $_SESSION['user_id']  = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['name']     = $row['name'];
        header('Location: start.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Digital Diary — Sign In</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --ink: #1c1410; --gold: #c8922a; --gold2: #e8b84b;
    --cream: #fdf6e3; --warm: #8b5e3c;
  }
  body { min-height: 100vh; font-family: 'Lato', sans-serif; background: var(--cream); }
  .bg-wrap {
    position: fixed; inset: 0; z-index: 0;
    background: linear-gradient(135deg, #2b1d0e 0%, #4a3015 40%, #1a1208 100%);
    overflow: hidden;
  }
  .bg-wrap::before {
    content: ''; position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 60% 50% at 20% 30%, rgba(200,146,42,.18) 0%, transparent 70%),
      radial-gradient(ellipse 40% 60% at 80% 70%, rgba(139,94,60,.2)  0%, transparent 70%);
    animation: bgPulse 8s ease-in-out infinite alternate;
  }
  @keyframes bgPulse { from { opacity:.7; } to { opacity:1; } }
  .lines {
    position: absolute; inset: 0;
    background-image: repeating-linear-gradient(
      0deg, transparent, transparent 34px,
      rgba(200,146,42,.06) 34px, rgba(200,146,42,.06) 35px
    );
  }
  .page-wrap {
    position: relative; z-index: 1; min-height: 100vh;
    display: flex; align-items: center; justify-content: center; padding: 20px;
  }
  .login-card {
    width: 440px; background: var(--cream); border-radius: 4px;
    box-shadow: 0 30px 80px rgba(0,0,0,.55), 0 0 0 1px rgba(200,146,42,.3);
    overflow: hidden;
    animation: cardIn .7s cubic-bezier(.22,.68,0,1.2) both;
  }
  @keyframes cardIn {
    from { opacity:0; transform:translateY(30px) scale(.96); }
    to   { opacity:1; transform:none; }
  }
  .card-header {
    background: linear-gradient(135deg, #1c1410 0%, #3b240e 100%);
    padding: 36px 40px 28px; text-align: center;
    border-bottom: 3px solid var(--gold); position: relative;
  }
  .card-header::after {
    content: ''; position: absolute; bottom:-1px; left:50%; transform:translateX(-50%);
    width:60px; height:3px; background:var(--gold2);
  }
  .diary-icon { font-size:42px; margin-bottom:10px; display:block; }
  .card-header h1 {
    font-family: 'Playfair Display', serif; font-size:28px;
    color:var(--cream); margin-bottom:4px;
  }
  .card-header p { color:rgba(253,246,227,.55); font-size:13px; letter-spacing:1.5px; text-transform:uppercase; font-weight:300; }
  .card-body { padding:36px 40px 32px; background:var(--cream); }
  .err-msg {
    background:#fff0f0; border-left:4px solid #d94f4f; color:#7a1515;
    padding:10px 14px; font-size:13.5px; border-radius:0 3px 3px 0;
    margin-bottom:20px; animation:shake .4s ease;
  }
  @keyframes shake {
    0%,100% { transform:translateX(0); }
    20%,60%  { transform:translateX(-6px); }
    40%,80%  { transform:translateX(6px); }
  }
  .field { margin-bottom:18px; }
  .field label {
    display:block; font-size:11px; font-weight:700; letter-spacing:1.8px;
    text-transform:uppercase; color:var(--warm); margin-bottom:7px;
  }
  .field input {
    width:100%; padding:12px 16px; border:1.5px solid rgba(139,94,60,.25);
    border-radius:3px; font-family:'Lato',sans-serif; font-size:15px;
    color:var(--ink); background:#fff; outline:none;
    transition:border-color .2s, box-shadow .2s;
  }
  .field input:focus { border-color:var(--gold); box-shadow:0 0 0 3px rgba(200,146,42,.15); }
  .btn-login {
    width:100%; padding:13px;
    background:linear-gradient(135deg,var(--gold) 0%,var(--gold2) 100%);
    border:none; border-radius:3px; font-family:'Lato',sans-serif;
    font-size:14px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase;
    color:var(--ink); cursor:pointer; margin-top:6px;
    box-shadow:0 4px 16px rgba(200,146,42,.35);
    transition:filter .2s, transform .15s;
  }
  .btn-login:hover  { filter:brightness(1.08); transform:translateY(-1px); }
  .btn-login:active { transform:translateY(0); }
  .card-footer {
    padding:16px 40px 24px; text-align:center;
    border-top:1px solid rgba(139,94,60,.15);
    background:rgba(245,234,208,.5);
  }
  .card-footer p { font-size:13.5px; color:#6b4e2a; }
  .card-footer a { color:var(--gold); font-weight:700; text-decoration:none; }
  .card-footer a:hover { text-decoration:underline; }
</style>
</head>
<body>
<div class="bg-wrap"><div class="lines"></div></div>
<div class="page-wrap">
  <div class="login-card">
    <div class="card-header">
      <span class="diary-icon">📖</span>
      <h1>Digital Diary</h1>
      <p>Your personal sanctuary</p>
    </div>
    <div class="card-body">
      <?php if($error): ?>
        <div class="err-msg">⚠ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="post" autocomplete="off">
        <div class="field">
          <label>Username</label>
          <input type="text" name="username" placeholder="Enter your username" required>
        </div>
        <div class="field">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter your password" required>
        </div>
        <button type="submit" name="login" class="btn-login">Sign In to Your Diary</button>
      </form>
    </div>
    <div class="card-footer">
      <p>New here? <a href="register.php">Create an account →</a></p>
    </div>
  </div>
</div>
</body>
</html>