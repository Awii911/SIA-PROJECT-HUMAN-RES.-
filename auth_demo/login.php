<?php
require_once __DIR__ . '/config.php';
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

$error = $_SESSION['error'] ?? null;
$error_type = $_SESSION['error_type'] ?? null;
unset($_SESSION['error'], $_SESSION['error_type']);

$old_username = $_SESSION['old_username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
* { box-sizing: border-box; margin:0; padding:0; font-family: 'Inter', sans-serif; }
body { background:#f0f4f8; display:flex; justify-content:center; align-items:center; min-height:100vh; }

.card {
    background:#fff; padding:40px 30px; border-radius:14px; width:100%; max-width:400px;
    box-shadow:0 12px 30px rgba(0,0,0,0.1); transition: transform 0.2s;
}
.card:hover { transform: translateY(-2px); }

h2 { text-align:center; margin-bottom:25px; color:#333; font-weight:600; }

.field-wrapper { margin-bottom:18px; position:relative; }
label { display:block; margin-bottom:6px; font-weight:500; color:#555; }
input { width:100%; padding:12px 14px; border-radius:8px; border:1px solid #ccc; font-size:15px; height:42px; transition: all 0.2s; }
input:focus { border-color:#007bff; outline:none; }

.password-wrapper { position:relative; }
.password-wrapper input { padding-right:42px; }

.toggle-eye { position:absolute; right:12px; top:50%; transform:translateY(-50%);
    font-size:16px; cursor:pointer; color:#007bff; transition: color 0.3s, transform 0.2s; }
.toggle-eye:hover { color:#0056b3; transform: scale(1.2); }

button { width:100%; padding:14px; background:#007bff; border:none; border-radius:10px;
    font-size:16px; color:#fff; cursor:pointer; transition: background 0.3s, transform 0.2s; }
button:hover { background:#0056b3; transform: translateY(-1px); }

.error { background:#ffe5e5; color:#d32f2f; padding:10px; border-radius:6px; font-size:14px; margin-bottom:12px; text-align:center; }
#captcha-error { font-size:13px; color:red; margin-top:4px; text-align:center; }

p { text-align:center; margin-top:20px; font-size:14px; color:#555; }
p a { color:#007bff; text-decoration:none; font-weight:500; }
p a:hover { text-decoration: underline; }

@media (max-width:450px) { .card { padding:30px 20px; } }
</style>
</head>
<body>
<div class="card">
    <h2>Login</h2>

    <?php if ($error && $error_type !== 'captcha'): ?>
        <div class="error"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <form action="login_process.php" method="post" autocomplete="off" onsubmit="return validateCaptcha()">
        <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf)?>">

        <div class="field-wrapper">
            <label>Username</label>
            <input name="username" required value="<?=htmlspecialchars($old_username)?>">
        </div>

        <div class="field-wrapper">
            <label>Password</label>
            <div class="password-wrapper">
                <input id="loginPassword" name="password" type="password" required>
                <span class="toggle-eye" onclick="togglePassword('loginPassword', this)">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>
        </div>

        <div class="g-recaptcha" data-sitekey="6LeFeM4rAAAAAITVQgcPNjn5ZRnfCHxwV4DbRUZQ"></div>
        <div id="captcha-error"><?=($error_type==='captcha') ? htmlspecialchars($error) : ''?></div>

        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Create one</a></p>
</div>

<script>
function togglePassword(id, el){
    const input = document.getElementById(id);
    const icon = el.querySelector('i');
    if(input.type === "password"){
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function validateCaptcha(){
    const response = grecaptcha.getResponse();
    if(response.length === 0){
        document.getElementById("captcha-error").innerText="Please verify the captcha.";
        return false;
    }
    document.getElementById("captcha-error").innerText="";
    return true;
}
</script>
</body>
</html>
