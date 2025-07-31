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
    <title>Registrar Novedad</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        select, textarea, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 16px;
        }
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        .severidad-options {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .severidad-option {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }
        .severidad-option input {
            display: none;
        }
        .severidad-option label {
            cursor: pointer;
            margin-bottom: 0;
        }
        .severidad-option:hover {
            transform: scale(1.02);
        }
        .severidad-option input:checked + label {
            font-weight: bold;
        }
        .severidad-1 { background-color: #d4edda; }
        .severidad-2 { background-color: #fff3cd; }
        .severidad-3 { background-color: #f8d7da; }
        .severidad-option input:checked + label + .severidad-indicator {
            display: block;
        }
        .btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #218838;
        }
        .mensaje {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .severidad-selected {
            border: 2px solid #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Registrar Novedad</h1>
        
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
                <label for="usuario_id">Residente (opcional):</label>
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
                <label for="tipo">Tipo de Novedad:</label>
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
                <label for="fecha_incidente">Fecha del Incidente:</label>
                <input type="datetime-local" name="fecha_incidente" id="fecha_incidente" value="<?= date('Y-m-d\TH:i') ?>">
            </div>
            
            <div class="form-group">
    <label>Severidad:</label>
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
                <label for="nota">Descripción Detallada:</label>
                <textarea name="nota" id="nota" required placeholder="Describa la novedad con el mayor detalle posible..."></textarea>
            </div>
            
            <button type="submit" class="btn">📤 Registrar Novedad</button>
            <a href="index.php" style="margin-left: 10px;">← Volver al inicio</a>
        </form>
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