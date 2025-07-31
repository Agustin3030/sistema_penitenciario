<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = conectarBaseDeDatos();
    
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $usuario = $conexion->real_escape_string($_POST['usuario']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $conexion->real_escape_string($_POST['rol']);
    
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $nombre, $usuario, $password, $rol);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Usuario registrado correctamente";
        header("Location: registrar_usuario.php");
        exit();
    } else {
        $_SESSION['error'] = "Error al registrar usuario: " . $conexion->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registrar Nuevo Usuario</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 1rem; }
        .form-container { max-width: 500px; margin: 0 auto; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; }
        .form-group input, .form-group select { width: 100%; padding: 0.5rem; }
        .btn { background: #007bff; color: white; border: none; padding: 0.7rem 1rem; cursor: pointer; }
        .mensaje { padding: 0.5rem; margin: 1rem 0; border-radius: 4px; }
        .exito { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Registrar Nuevo Usuario</h2>
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje exito"><?= $_SESSION['mensaje'] ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mensaje error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Nombre completo:</label>
                <input type="text" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label>Nombre de usuario:</label>
                <input type="text" name="usuario" required>
            </div>
            
            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Rol:</label>
                <select name="rol" required>
                    <option value="celador">Celador</option>
                   <!-- <option value="jefe">Jefe de Pabellón</option>-->
                    <option value="direccion">Dirección</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Registrar Usuario</button>
        </form>
        
        <p><a href="index.php">Volver al panel</a></p>
    </div>
</body>
</html>