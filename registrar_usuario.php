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
        // Registrar en el historial
        $historial_sql = "INSERT INTO historial (usuario_id, accion) VALUES (?, ?)";
        if ($historial_stmt = $conexion->prepare($historial_sql)) {
            $accion = "REGISTRO_USUARIO: " . $usuario . " (" . $rol . ")";
            $historial_stmt->bind_param('is', $_SESSION['id_usuario'], $accion);
            $historial_stmt->execute();
            $historial_stmt->close();
        }
        
        $_SESSION['exito'] = "Usuario registrado correctamente";
        header("Location: registrar_usuario.php");
        exit();
    } else {
        $_SESSION['error'] = "Error al registrar usuario: " . $conexion->error;
    }
    
    $stmt->close();
    $conexion->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario | Sistema Penitenciario</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --danger: #e74c3c;
            --success: #27ae60;
            --warning: #f39c12;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background: var(--primary);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header h2 {
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logout-btn {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .logout-btn:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .menu {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 2rem;
        }
        
        .menu a {
            text-decoration: none;
            background-color: var(--secondary);
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .menu a:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .menu a i {
            font-size: 1.1em;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            padding: 1.5rem;
            transition: transform 0.3s, box-shadow 0.3s;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light);
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        input, select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        input:focus, select:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .mensaje {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .exito {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid var(--success);
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--danger);
        }
        
        .volver-link {
            color: var(--primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 20px;
            transition: all 0.3s;
        }
        
        .volver-link:hover {
            color: var(--secondary);
            text-decoration: underline;
        }
        
        .password-strength {
            margin-top: 5px;
            height: 5px;
            background: #eee;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s, background 0.3s;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .menu {
                flex-direction: column;
            }
            
            .card {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2><i class="fas fa-user-shield"></i> Bienvenido, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></h2>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </a>
    </div>

    <div class="container">
        <div class="menu">
            <a href="listadealojados.php"><i class="fas fa-list"></i> Lista de Alojados</a>
            <a href="actualizar_estado.php"><i class="fas fa-edit"></i> Actualizar Estados</a>
            <a href="cargar_nota.php"><i class="fas fa-plus-circle"></i> Registrar Novedad</a>
            <a href="ver_notas.php"><i class="fas fa-clipboard-list"></i> Ver Novedades</a>
            <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
                <a href="registrar_usuario.php"><i class="fas fa-user-plus"></i> Registrar Usuario</a>
                <a href="agregarpersona.php"><i class="fas fa-user-plus"></i> Agregar Interno</a>
            <?php endif; ?>
        </div>

        <div class="card">
            <h1><i class="fas fa-user-plus"></i> Registrar Nuevo Usuario</h1>
            
            <?php if (isset($_SESSION['exito'])): ?>
                <div class="mensaje exito"><?= $_SESSION['exito'] ?></div>
                <?php unset($_SESSION['exito']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mensaje error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form method="POST" id="form-usuario">
                <div class="form-group">
                    <label for="nombre"><i class="fas fa-id-card"></i> Nombre completo:</label>
                    <input type="text" name="nombre" id="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="usuario"><i class="fas fa-user"></i> Nombre de usuario:</label>
                    <input type="text" name="usuario" id="usuario" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Contraseña:</label>
                    <input type="password" name="password" id="password" required>
                    <div class="password-strength">
                        <div class="strength-bar" id="strength-bar"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="rol"><i class="fas fa-user-tag"></i> Rol:</label>
                    <select name="rol" id="rol" required>
                        <option value="celador">Celador</option>
                        <option value="direccion">Dirección</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Registrar Usuario
                </button>
                
                <a href="index.php" class="volver-link">
                    <i class="fas fa-arrow-left"></i> Volver al panel
                </a>
            </form>
        </div>
    </div>

    <script>
        // Validación de fortaleza de contraseña
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('strength-bar');
            let strength = 0;
            
            // Validar longitud
            if (password.length > 7) strength++;
            // Validar mayúsculas
            if (password.match(/[A-Z]/)) strength++;
            // Validar números
            if (password.match(/[0-9]/)) strength++;
            // Validar caracteres especiales
            if (password.match(/[^A-Za-z0-9]/)) strength++;
            
            // Actualizar barra de fortaleza
            switch(strength) {
                case 0:
                    strengthBar.style.width = '0%';
                    strengthBar.style.background = 'transparent';
                    break;
                case 1:
                    strengthBar.style.width = '25%';
                    strengthBar.style.background = 'var(--danger)';
                    break;
                case 2:
                    strengthBar.style.width = '50%';
                    strengthBar.style.background = 'var(--warning)';
                    break;
                case 3:
                    strengthBar.style.width = '75%';
                    strengthBar.style.background = '#4CAF50';
                    break;
                case 4:
                    strengthBar.style.width = '100%';
                    strengthBar.style.background = 'var(--success)';
                    break;
            }
        });
        
        // Validación del formulario
        document.getElementById('form-usuario').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres');
                document.getElementById('password').focus();
            }
        });
        
        // Efecto de carga suave
        document.addEventListener('DOMContentLoaded', () => {
            const card = document.querySelector('.card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>