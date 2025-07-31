<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'conexion.php';
    $conexion = conectarBaseDeDatos();

    // Depuración: Verificar datos recibidos
    error_log("Intento de login: Usuario: ".$_POST['usuario']);

    $usuario = $conexion->real_escape_string($_POST['usuario']);
    $password = $_POST['password'];

    $stmt = $conexion->prepare("SELECT id, nombre, usuario, password, rol FROM usuarios WHERE usuario = ?");
    
    if (!$stmt) {
        error_log("Error en preparación: ".$conexion->error);
        die("Error en el sistema. Intente más tarde.");
    }

    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $user = $resultado->fetch_assoc();
        
        // Depuración: Verificar contraseñas
        error_log("Contraseña almacenada: ".$user['password']);
        error_log("Contraseña ingresada: ".$password);
        
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION = [
                'id_usuario' => $user['id'],
                'nombre' => $user['nombre'],
                'usuario' => $user['usuario'],
                'rol' => $user['rol']
            ];
            
            // Registrar login
            $conexion->query("INSERT INTO historial_login (usuario_id, accion) VALUES ({$user['id']}, 'LOGIN')");
            
            header("Location: index.php");
            exit();
        } else {
            error_log("Contraseña no coincide");
        }
    } else {
        error_log("Usuario no encontrado: ".$usuario);
    }
    
    $_SESSION['error'] = "Credenciales inválidas";
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; text-align: center; }
        .login-box h2 { margin-top: 0; color: #333; }
        .form-group { margin-bottom: 1rem; text-align: left; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; }
        .btn-login { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: #dc3545; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Sistema Penitenciario</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Ingresar</button>
        </form>
        
        <div style="margin-top: 1rem; font-size: 0.9em;">
        </div>
    </div>
</body>
</html>