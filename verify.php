<?php
$hash = '$2y$10$IwM9eRSzKcsqCOrBbdKp1e2GZfVgGRZ6cVDWxslxL6GLrmfyKChS6';
$password = 'admin123';

echo "Verificación: ";
var_dump(password_verify($password, $hash));

echo "<br>Hash generado ahora: ";
echo password_hash($password, PASSWORD_BCRYPT);
?>