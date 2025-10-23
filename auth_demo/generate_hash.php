<?php
// Simple helper: set the password you want here, save file, then open this page in browser
$password = 'Admin@123'; // <- change this to your desired admin password
$hash = password_hash($password, PASSWORD_DEFAULT);
?>
<!doctype html>
<html><body>
<h3>Password hash generator</h3>
<p>Password: <strong><?=htmlspecialchars($password)?></strong></p>
<p>Hash (copy the entire value and paste into init.sql in place of {HASH}):</p>
<pre><?=htmlspecialchars($hash)?></pre>
<p>After you copy the hash, delete this file or protect it (it prints the password on the page).</p>
</body></html>
