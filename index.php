<!DOCTYPE html>
<html><head><title>MyPulse Docker</title></head>
<body>
<h1>✅ MyPulse migré ! PHP v<?php echo phpversion(); ?></h1>
<p>Structure OK: <a href="/index/">index/</a> | <a href="/login/">login/</a> | <a href="/vote/">vote/</a></p>
<?php
// Test DB
/*$conn = @new mysqli('db', 'mypulse_user', 'mypulse_pass', 'mypulse', 3306);
echo $conn ? '<p style="color:green">✅ DB connectée</p>' : print_r(mysqli_connect_error());
$conn?->close();*/
echo '<p>Test DB plus tard après mysqli</p>';
?>
<hr>
<p>Extensions: mysqli=<?php echo extension_loaded('mysqli') ? '✅' : '❌'; ?>, gd=<?php echo extension_loaded('gd') ? '✅' : '❌'; ?></p>

Fichiers: <?php echo implode(', ', array_diff(scandir('.'), ['.', '..'])); ?>
</body></html>
