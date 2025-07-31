<?php
$password = "admin123";
$hash = password_hash($admin123, PASSWORD_BCRYPT);
echo "Hash generado: " . $hash;
?>