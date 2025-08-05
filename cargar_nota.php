<?php
session_start();
require 'conexion.php';

// Verificar autenticación y permisos
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$conexion = conectarBaseDeDatos();

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y limpiar datos
    $usuario_id = !empty($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : null;
    $nota = trim($_POST['nota']);
    $tipo = $_POST['tipo'];
    $severidad = isset($_POST['severidad']) ? (int)$_POST['severidad'] : 1;
    $fecha_incidente = !empty($_POST['fecha_incidente']) ? $_POST['fecha_incidente'] : date('Y-m-d H:i:s');

    // Validación básica
    if (empty($nota)) {
        $_SESSION['error'] = "La descripción de la nota es obligatoria";
        header("Location: cargar_nota.php");
        exit();
    }

    // Preparar consulta con manejo de errores
    $sql = "INSERT INTO notas (usuario_id, nota, tipo, severidad, creado_por, fecha_incidente) VALUES (?, ?, ?, ?, ?, ?)";
    
    if (!$stmt = $conexion->prepare($sql)) {
        error_log("Error preparando consulta: " . $conexion->error);
        $_SESSION['error'] = "Error en el sistema. Por favor contacte al administrador.";
        header("Location: cargar_nota.php");
        exit();
    }

    // Vincular parámetros
    if (!$stmt->bind_param('issiis', $usuario_id, $nota, $tipo, $severidad, $_SESSION['id_usuario'], $fecha_incidente)) {
        error_log("Error vinculando parámetros: " . $stmt->error);
        $_SESSION['error'] = "Error en el sistema. Por favor contacte al administrador.";
        $stmt->close();
        header("Location: cargar_nota.php");
        exit();
    }

    // Ejecutar consulta
    if ($stmt->execute()) {
        // Registrar en el historial
        $historial_sql = "INSERT INTO historial (usuario_id, accion) VALUES (?, ?)";
        if ($historial_stmt = $conexion->prepare($historial_sql)) {
            $accion = "REGISTRO_NOTA: " . $tipo;
            $historial_stmt->bind_param('is', $_SESSION['id_usuario'], $accion);
            $historial_stmt->execute();
            $historial_stmt->close();
        }
        
        $_SESSION['exito'] = "Nota registrada correctamente";
    } else {
        error_log("Error ejecutando consulta: " . $stmt->error);
        $_SESSION['error'] = "Error al guardar la nota. Por favor intente nuevamente.";
    }

    $stmt->close();
    header("Location: cargar_nota.php");
    exit();
}

// Obtener lista de residentes para el select con manejo de errores
$residentes_result = $conexion->query("SELECT id, nombre FROM personas ORDER BY nombre");
if ($residentes_result === false) {
    error_log("Error en consulta SQL: " . $conexion->error);
    die("Error al cargar la lista de residentes. Por favor intente más tarde.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Novedad | Sistema Penitenciario</title>
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
            margin-bottom: 2rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card h3 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        select, textarea, input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        select:focus, textarea:focus, input:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
            line-height: 1.5;
        }
        
        .severidad-options {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }
        
        .severidad-option {
            flex: 1;
            text-align: center;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
            background-color: var(--light);
        }
        
        .severidad-option input {
            display: none;
        }
        
        .severidad-option label {
            cursor: pointer;
            margin-bottom: 0;
            display: block;
        }
        
        .severidad-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .severidad-1 { background-color: #d4edda; }
        .severidad-2 { background-color: #fff3cd; }
        .severidad-3 { background-color: #f8d7da; }
        
        .severidad-selected {
            border: 2px solid var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            transform: translateY(-2px);
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
        
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid var(--secondary);
        }
        
        .volver-link {
            color: var(--primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-left: 15px;
            transition: all 0.3s;
        }
        
        .volver-link:hover {
            color: var(--secondary);
            text-decoration: underline;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .severidad-options {
                flex-direction: column;
            }
            
            .menu {
                flex-direction: column;
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
            <h3><i class="fas fa-plus-circle"></i> Registrar Novedad</h3>
            
            <?php if (isset($_SESSION['exito'])): ?>
                <div class="mensaje exito"><?= $_SESSION['exito'] ?></div>
                <?php unset($_SESSION['exito']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mensaje error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <div class="mensaje info">
                <strong>Instrucciones:</strong> Complete todos los campos para registrar una nueva novedad relacionada con un residente o general del establecimiento.
            </div>
            
            <form method="POST" id="form-novedad">
                <div class="form-group">
                    <label for="usuario_id"><i class="fas fa-user"></i> Residente (opcional):</label>
                    <select name="usuario_id" id="usuario_id">
                        <option value="">-- Novedad General --</option>
                        <?php 
                        if ($residentes_result && $residentes_result->num_rows > 0) {
                            while($residente = $residentes_result->fetch_assoc()): 
                        ?>
                            <option value="<?= htmlspecialchars($residente['id']) ?>">
                                <?= htmlspecialchars($residente['nombre']) ?>
                            </option>
                        <?php 
                            endwhile;
                        } else {
                            echo '<option value="">No hay residentes registrados</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tipo"><i class="fas fa-tag"></i> Tipo de Novedad:</label>
                    <select name="tipo" id="tipo" required>
                        <option value="conducta">Conducta</option>
                        <option value="sancion">Sanción</option>
                        <option value="visita">Visita</option>
                        <option value="incidente">Incidente</option>
                        <option value="medica">Atención Médica</option>
                        <option value="traslado">Traslado</option>
                        <option value="observacion">Observación General</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fecha_incidente"><i class="fas fa-calendar-alt"></i> Fecha del Incidente:</label>
                    <input type="datetime-local" name="fecha_incidente" id="fecha_incidente" value="<?= date('Y-m-d\TH:i') ?>">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-exclamation-triangle"></i> Severidad:</label>
                    <div class="severidad-options">
                        <div class="severidad-option severidad-1" onclick="selectSeveridad(1)">
                            <input type="radio" name="severidad" id="severidad-1" value="1" <?= (!isset($_POST['severidad']) || (isset($_POST['severidad']) && $_POST['severidad'] == 1)) ? 'checked' : '' ?>>
                            <label for="severidad-1">Leve</label>
                        </div>
                        <div class="severidad-option severidad-2" onclick="selectSeveridad(2)">
                            <input type="radio" name="severidad" id="severidad-2" value="2" <?= (isset($_POST['severidad']) && $_POST['severidad'] == 2) ? 'checked' : '' ?>>
                            <label for="severidad-2">Moderada</label>
                        </div>
                        <div class="severidad-option severidad-3" onclick="selectSeveridad(3)">
                            <input type="radio" name="severidad" id="severidad-3" value="3" <?= (isset($_POST['severidad']) && $_POST['severidad'] == 3) ? 'checked' : '' ?>>
                            <label for="severidad-3">Grave</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="nota"><i class="fas fa-align-left"></i> Descripción Detallada:</label>
                    <textarea name="nota" id="nota" required placeholder="Describa la novedad con el mayor detalle posible..."></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Registrar Novedad
                    </button>
                    
            <a href="index.php" class="btn">
                <i class="fas fa-arrow-left"></i> Volver al panel
            </a>
       
            </form>
        </div>
    </div>

    <script>
        // Establecer la fecha/hora actual si no se ha seleccionado otra
        document.addEventListener('DOMContentLoaded', function() {
            const fechaInput = document.getElementById('fecha_incidente');
            if (!fechaInput.value) {
                const now = new Date();
                const timezoneOffset = now.getTimezoneOffset() * 60000;
                const localISOTime = (new Date(now - timezoneOffset)).toISOString().slice(0, 16);
                fechaInput.value = localISOTime;
            }
            
            // Resaltar la severidad seleccionada al cargar
            const selectedSeveridad = document.querySelector('input[name="severidad"]:checked');
            if (selectedSeveridad) {
                selectedSeveridad.closest('.severidad-option').classList.add('severidad-selected');
            }
        });

        // Función para seleccionar severidad
        function selectSeveridad(level) {
            // Remover selección previa
            document.querySelectorAll('.severidad-option').forEach(opt => {
                opt.classList.remove('severidad-selected');
            });
            
            // Marcar como seleccionado
            const selectedOption = document.getElementById('severidad-' + level).closest('.severidad-option');
            selectedOption.classList.add('severidad-selected');
            
            // Actualizar el radio button
            document.getElementById('severidad-' + level).checked = true;
        }

        // Validación del formulario
        document.getElementById('form-novedad').addEventListener('submit', function(e) {
            const nota = document.getElementById('nota').value.trim();
            if (!nota) {
                e.preventDefault();
                alert('La descripción de la nota es obligatoria');
                document.getElementById('nota').focus();
            }
        });
    </script>
</body>
</html>

<?php 
// Liberar recursos
if (isset($residentes_result)) {
    $residentes_result->free();
}
$conexion->close(); 
?>