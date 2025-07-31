<?php
session_start();
require 'conexion.php';

// Verificar permisos
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['rol'], ['admin', 'celador', 'direccion'])) {
    header("Location: login.php");
    exit();
}

$conexion = conectarBaseDeDatos();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Iniciar transacción para asegurar la integridad de los datos
    $conexion->begin_transaction();
    
    try {
        foreach ($_POST['estado'] as $id => $estado) {
            $id = (int)$id;
            $estado = $conexion->real_escape_string($estado);
            $sancionado = isset($_POST['sancionado'][$id]) ? 'sancionado' : 'no_sancionado';
            
            // Procesar fecha de sanción si está marcado como sancionado
            $fecha_sancion = null;
            if ($sancionado === 'sancionado' && !empty($_POST['fecha_sancion'][$id])) {
                $fecha_sancion = $conexion->real_escape_string($_POST['fecha_sancion'][$id]);
            }
            
            // Actualizar los datos del interno
            $stmt = $conexion->prepare("UPDATE personas SET 
                                      estado = ?, 
                                      sancionado = ?, 
                                      fecha_sancion = ?
                                      WHERE id = ?");
            $stmt->bind_param('sssi', $estado, $sancionado, $fecha_sancion, $id);
            $stmt->execute();
        }
        
        // Si todo va bien, confirmar la transacción
        $conexion->commit();
        $_SESSION['exito'] = "Estados actualizados correctamente";
    } catch (Exception $e) {
        // Si hay error, revertir los cambios
        $conexion->rollback();
        $_SESSION['error'] = "Error al actualizar: " . $e->getMessage();
    }
    
    header("Location: actualizar_estado.php");
    exit();
}

// Obtener la lista de residentes
$residentes = $conexion->query("
    SELECT id, nombre, estado, sancionado, fecha_sancion 
    FROM personas 
    ORDER BY nombre
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Estados y Sanciones</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #343a40;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .sancionado-row {
            background-color: #ffe6e6;
        }
        .form-group {
            margin-bottom: 15px;
        }
        select, input[type="date"] {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #218838;
        }
        .mensaje {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .exito {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .acciones {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚖️ Actualizar Estados y Sanciones</h1>
        
        <?php if (isset($_SESSION['exito'])): ?>
            <div class="mensaje exito"><?= $_SESSION['exito'] ?></div>
            <?php unset($_SESSION['exito']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mensaje error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Residente</th>
                        <th>Estado Actual</th>
                        <th>Sancionado</th>
                        <th>Fecha Fin Sanción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($residente = $residentes->fetch_assoc()): ?>
                        <tr class="<?= $residente['sancionado'] === 'sancionado' ? 'sancionado-row' : '' ?>">
                            <td><?= htmlspecialchars($residente['nombre']) ?></td>
                            <td>
                                <select name="estado[<?= $residente['id'] ?>]">
                                    <option value="condenado" <?= $residente['estado'] === 'condenado' ? 'selected' : '' ?>>Condenado</option>
                                    <option value="procesado" <?= $residente['estado'] === 'procesado' ? 'selected' : '' ?>>Procesado</option>
                                </select>
                            </td>
                            <td>
                                <input type="checkbox" 
                                       name="sancionado[<?= $residente['id'] ?>]" 
                                       value="1" 
                                       <?= $residente['sancionado'] === 'sancionado' ? 'checked' : '' ?>
                                       onchange="toggleFechaSancion(this, <?= $residente['id'] ?>)">
                            </td>
                            <td>
                                <input type="date" 
                                       name="fecha_sancion[<?= $residente['id'] ?>]" 
                                       id="fecha_sancion_<?= $residente['id'] ?>" 
                                       value="<?= $residente['fecha_sancion'] ?>" 
                                       <?= $residente['sancionado'] !== 'sancionado' ? 'disabled' : '' ?>>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="form-group" style="margin-top: 20px;">
                <button type="submit" class="btn">💾 Guardar Cambios</button>
                <a href="index.php" style="margin-left: 10px;">← Volver al inicio</a>
            </div>
        </form>
    </div>

    <script>
        // Habilitar/deshabilitar campo de fecha según el checkbox
        function toggleFechaSancion(checkbox, id) {
            const fechaInput = document.getElementById(`fecha_sancion_${id}`);
            fechaInput.disabled = !checkbox.checked;
            
            // Si se activa la sanción y no hay fecha, establecer fecha por defecto (7 días después)
            if (checkbox.checked && !fechaInput.value) {
                const hoy = new Date();
                hoy.setDate(hoy.getDate() + 7);
                const fechaFormateada = hoy.toISOString().split('T')[0];
                fechaInput.value = fechaFormateada;
            }
        }
    </script>
</body>
</html>

<?php $conexion->close(); ?>