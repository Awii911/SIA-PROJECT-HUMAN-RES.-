<?php
require_once __DIR__ . '/config.php';
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

// Grab messages from session
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);

$old_username = $_SESSION['old_username'] ?? '';
unset($_SESSION['old_username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<style>
* { box-sizing: border-box; margin:0; padding:0; font-family:'Inter',sans-serif; }
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
button:hover:enabled { background:#0056b3; transform: translateY(-1px); }
button:disabled { background:#999; cursor:not-allowed; }

#password-match, #password-strength { font-size:13px; margin-top:4px; margin-bottom:10px; min-height:16px; }

/* Notifications */
.notification { padding:10px; border-radius:6px; font-size:14px; margin-bottom:12px; text-align:center; }
.error { background:#ffe5e5; color:#d32f2f; }
.success { background:#e0f7e9; color:#2e7d32; transition:opacity 0.5s; }

p { text-align:center; margin-top:20px; font-size:14px; color:#555; }
p a { color:#007bff; text-decoration:none; font-weight:500; }
p a:hover { text-decoration: underline; }

@media(max-width:450px){.card{padding:30px 20px;}}
</style>
</head>
<body>
<div class="card">
    <h2>Create Account</h2>

    <!-- Notifications -->
    <?php if($error): ?>
        <div class="notification error"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="notification success" id="success-alert"><?=htmlspecialchars($success)?></div>
    <?php endif; ?>

    <form action="register_process.php" method="post" autocomplete="off">
        <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf)?>">

        <div class="field-wrapper">
            <label>Username</label>
            <input name="username" required value="<?=htmlspecialchars($old_username)?>">
        </div>

        <div class="field-wrapper">
            <label>Password</label>
            <div class="password-wrapper">
                <input id="regPassword" name="password" type="password" required oninput="checkPasswordStrength(); checkPasswordMatch();">
                <span class="toggle-eye" onclick="togglePassword('regPassword', this)"><i class="fa-solid fa-eye"></i></span>
            </div>
            <div id="password-strength"></div>
        </div>

        <div class="field-wrapper">
            <label>Confirm Password</label>
            <div class="password-wrapper">
                <input id="confirmPassword" name="confirm_password" type="password" required oninput="checkPasswordMatch()">
                <span class="toggle-eye" onclick="togglePassword('confirmPassword', this)"><i class="fa-solid fa-eye"></i></span>
            </div>
            <div id="password-match"></div>
        </div>

        <!-- Google reCAPTCHA -->
        <div class="field-wrapper">
            <div class="g-recaptcha" data-sitekey="6LeFeM4rAAAAAITVQgcPNjn5ZRnfCHxwV4DbRUZQ"></div>
        </div>

        <button type="submit" id="registerBtn" disabled>Register</button>
    </form>
    <p><a href="login.php">Back to login</a></p>
</div>

<script>
function togglePassword(id, el){
    const input = document.getElementById(id);
    const icon = el.querySelector('i');
    if(input.type==="password"){
        input.type="text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type="password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function checkPasswordMatch(){
    const password = document.getElementById('regPassword').value;
    const confirm = document.getElementById('confirmPassword').value;
    const msg = document.getElementById('password-match');
    const btn = document.getElementById('registerBtn');

    if(confirm===""){ msg.textContent=""; return; }

    if(password!==confirm){
        msg.textContent="Passwords do not match.";
        msg.style.color="red";
        btn.disabled = true;
    } else {
        msg.textContent="Passwords match!";
        msg.style.color="green";
        // Only enable if password is Good or Strong
        checkPasswordStrength();
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('regPassword').value;
    const msg = document.getElementById('password-strength');
    const btn = document.getElementById('registerBtn');

    let score = 0;

    if(password.length >= 6) score++;
    if(/[A-Z]/.test(password)) score++;
    if(/[0-9]/.test(password)) score++;
    if(/[\W_]/.test(password)) score++;

    let status = "";
    let color = "";

    if(score <= 2){
        status = "Weak";
        color = "red";
        btn.disabled = true;
    } else if(score === 3){
        status = "Good";
        color = "orange";
        // Enable only if confirm password matches
        const confirm = document.getElementById('confirmPassword').value;
        btn.disabled = (confirm !== password);
    } else if(score === 4){
        status = "Strong";
        color = "green";
        // Enable only if confirm password matches
        const confirm = document.getElementById('confirmPassword').value;
        btn.disabled = (confirm !== password);
    }

    msg.textContent = `Password strength: ${status}`;
    msg.style.color = color;
}

// Auto fade success message after 3 seconds
const alert = document.getElementById('success-alert');
if(alert){
    setTimeout(()=>{ alert.style.opacity='0'; }, 3000);
}
</script>
</body>
</html>
