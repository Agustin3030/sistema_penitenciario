<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = conectarBaseDeDatos();
    
    // Validación de campos
    $required = ['nombre', 'edad', 'causa', 'estado', 'ubicacion', 'tiempo_condena', 'nivel_riesgo'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "El campo $field es obligatorio";
            header("Location: agregarpersona.php");
            exit();
        }
    }

    // Procesar ranchograma (relaciones)
    $ranchograma = [];
    if (!empty($_POST['relacionados'])) {
        foreach ($_POST['relacionados'] as $relacion) {
            $ranchograma[] = [
                'interno_id' => (int)$relacion,
                'tipo_relacion' => $_POST['tipo_relacion'][$relacion] ?? 'Desconocido'
            ];
        }
    }

    $stmt = $conexion->prepare("INSERT INTO personas (
        nombre, edad, causa, estado, ubicacion, tiempo_condena, 
        nivel_riesgo, sancionado, ranchograma
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $sancionado = isset($_POST['sancionado']) ? 'sancionado' : 'no_sancionado';
    
    $stmt->bind_param(
        'sisssssss',
        $_POST['nombre'],
        $_POST['edad'],
        $_POST['causa'],
        $_POST['estado'],
        $_POST['ubicacion'],
        $_POST['tiempo_condena'],
        $_POST['nivel_riesgo'],
        $sancionado,
        json_encode($ranchograma)
    );

    if ($stmt->execute()) {
        // Registrar en el historial
        $historial_sql = "INSERT INTO historial (usuario_id, accion) VALUES (?, ?)";
        if ($historial_stmt = $conexion->prepare($historial_sql)) {
            $accion = "REGISTRO_INTERNO: " . $_POST['nombre'];
            $historial_stmt->bind_param('is', $_SESSION['id_usuario'], $accion);
            $historial_stmt->execute();
            $historial_stmt->close();
        }
        
        $_SESSION['exito'] = "Interno agregado correctamente";
        header("Location: agregarpersona.php");
        exit();
    } else {
        $_SESSION['error'] = "Error al agregar: " . $conexion->error;
    }
    
    $stmt->close();
    $conexion->close();
}

// Obtener lista de internos para ranchograma
$conexion = conectarBaseDeDatos();
$internos = $conexion->query("SELECT id, nombre FROM personas ORDER BY nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Interno | Sistema Penitenciario</title>
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
        
        h1 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 1.5rem;
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
        
        input, select, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.5;
        }
        
        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1rem;
        }
        
        .checkbox-container input {
            width: auto;
        }
        
        .relacionados-container {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1rem;
            background-color: #f9f9f9;
        }
        
        .relacionados-title {
            font-weight: 500;
            margin-bottom: 1rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .relacion-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .relacion-item:last-child {
            border-bottom: none;
        }
        
        .relacion-select {
            flex: 1;
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
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--secondary);
            color: var(--secondary);
        }
        
        .btn-outline:hover {
            background: var(--secondary);
            color: white;
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
        
        .no-internos {
            color: #7f8c8d;
            font-style: italic;
            padding: 15px 0;
            text-align: center;
        }
        
        .form-actions {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .menu {
                flex-direction: column;
            }
            
            .relacion-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .relacion-select {
                width: 100%;
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
            <h1><i class="fas fa-user-plus"></i> Agregar Nuevo Interno</h1>
            
            <?php if (isset($_SESSION['exito'])): ?>
                <div class="mensaje exito"><?= $_SESSION['exito'] ?></div>
                <?php unset($_SESSION['exito']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mensaje error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre"><i class="fas fa-id-card"></i> Nombre Completo:</label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan Pérez">
                    </div>
                    
                    <div class="form-group">
                        <label for="edad"><i class="fas fa-calendar-alt"></i> Edad:</label>
                        <input type="number" id="edad" name="edad" min="12" max="100" required placeholder="Ej: 35">
                    </div>
                    
                    <div class="form-group">
                        <label for="estado"><i class="fas fa-gavel"></i> Estado Legal:</label>
                        <select id="estado" name="estado" required>
                            <option value="" disabled selected>Seleccione un estado</option>
                            <option value="condenado">Condenado</option>
                            <option value="procesado">Procesado</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="ubicacion"><i class="fas fa-map-marker-alt"></i> Ubicación:</label>
                        <input type="text" id="ubicacion" name="ubicacion" required placeholder="Ej: Pabellón 3 - Celda 12">
                    </div>
                    
                    <div class="form-group">
                        <label for="tiempo_condena"><i class="fas fa-clock"></i> Tiempo de Condena:</label>
                        <input type="text" id="tiempo_condena" name="tiempo_condena" required placeholder="Ej: 5 años">
                    </div>
                    
                    <div class="form-group">
                        <label for="nivel_riesgo"><i class="fas fa-exclamation-triangle"></i> Nivel de Riesgo:</label>
                        <select id="nivel_riesgo" name="nivel_riesgo" required>
                            <option value="" disabled selected>Seleccione nivel</option>
                            <option value="Bajo">Bajo</option>
                            <option value="Medio">Medio</option>
                            <option value="Alto">Alto</option>
                            <option value="Máximo">Máximo</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="causa"><i class="fas fa-file-alt"></i> Causa de Ingreso:</label>
                    <textarea id="causa" name="causa" required placeholder="Describa los detalles de la causa de ingreso..."></textarea>
                </div>
                
                <div class="checkbox-container">
                    <input type="checkbox" id="sancionado" name="sancionado">
                    <label for="sancionado">¿Actualmente sancionado?</label>
                </div>
                
                <div class="form-group">
                    <div class="relacionados-container">
                        <div class="relacionados-title"><i class="fas fa-users"></i> Relaciones con otros internos</div>
                        
                        <?php if ($internos && $internos->num_rows > 0): ?>
                            <?php while($interno = $internos->fetch_assoc()): ?>
                                <div class="relacion-item">
                                    <div>
                                        <input type="checkbox" id="relacion-<?= $interno['id'] ?>" name="relacionados[]" value="<?= $interno['id'] ?>">
                                        <label for="relacion-<?= $interno['id'] ?>"><?= htmlspecialchars($interno['nombre']) ?></label>
                                    </div>
                                    <select class="relacion-select" name="tipo_relacion[<?= $interno['id'] ?>]">
                                        <option value="" disabled selected>Tipo de relación</option>
                                        <option value="Familia">Familia</option>
                                        <option value="Amistad">Amistad</option>
                                        <option value="Conflictiva">Conflictiva</option>
                                        <option value="Laboral">Laboral</option>
                                    </select>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-internos">No hay otros internos registrados</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-block">
                        <i class="fas fa-save"></i> Guardar Interno
                    </button>
                    <a href="listadealojados.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Volver a la lista
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
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

<?php 
// Liberar recursos
if (isset($internos)) {
    $internos->free();
}
if (isset($conexion)) {
    $conexion->close();
}
?>