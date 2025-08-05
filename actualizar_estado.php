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
    <title>Actualizar Estados | Sistema Penitenciario</title>
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: var(--primary);
            color: white;
            position: sticky;
            top: 0;
            font-weight: 500;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .sancionado-row {
            background-color: #ffebee;
        }
        
        select, input[type="date"] {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-family: 'Roboto', sans-serif;
        }
        
        .btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 10px 20px;
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
        
        .checkbox-style {
            width: 18px;
            height: 18px;
            accent-color: var(--danger);
            cursor: pointer;
        }
        
        .form-actions {
            margin-top: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .volver-link {
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
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
        
        .badge-condenado {
            background-color: var(--danger);
            color: white;
        }
        
        .badge-procesado {
            background-color: var(--success);
            color: white;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .form-actions {
                flex-direction: column;
                align-items: flex-start;
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
            <h3><i class="fas fa-edit"></i> Actualizar Estados y Sanciones</h3>
            
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
                                           class="checkbox-style"
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
                
                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    
                     <a href="index.php" class="btn">
                <i class="fas fa-arrow-left"></i> Volver al panel
            </a>
                </div>
            </form>
        </div>
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
        
        // Efecto de carga suave
        document.addEventListener('DOMContentLoaded', () => {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(10px)';
                row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                
                setTimeout(() => {
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, 50 * index);
            });
        });
    </script>
</body>

</html>

<?php $conexion->close(); ?>