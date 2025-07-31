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
            die("El campo $field es obligatorio");
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
        nivel_riesgo, sanciones, ranchograma
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        'sisssssss',
        $_POST['nombre'],
        $_POST['edad'],
        $_POST['causa'],
        $_POST['estado'],
        $_POST['ubicacion'],
        $_POST['tiempo_condena'],
        $_POST['nivel_riesgo'],
        $_POST['sanciones'],
        json_encode($ranchograma)
    );

    if ($stmt->execute()) {
        $_SESSION['exito'] = "Interno agregado correctamente";
        header("Location: agregarpersona.php");
        exit();
    } else {
        $_SESSION['error'] = "Error al agregar: " . $conexion->error;
    }
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
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --danger: #e74c3c;
            --success: #2ecc71;
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
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        h2 {
            color: var(--primary);
            margin-bottom: 25px;
            font-weight: 700;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--secondary);
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .alert-danger {
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .relacionados-container {
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 20px;
            margin-top: 10px;
            background-color: #f9f9f9;
        }
        
        .relacionados-title {
            font-weight: 500;
            margin-bottom: 15px;
            color: var(--primary);
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
            display: inline-block;
            background: var(--secondary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
        }
        
        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
        
        .text-center {
            text-align: center;
        }
        
        .mt-4 {
            margin-top: 40px;
        }
        
        .no-internos {
            color: #7f8c8d;
            font-style: italic;
            padding: 15px 0;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .relacion-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .relacion-select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Agregar Nuevo Interno</h2>
            
            <?php if (isset($_SESSION['exito'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['exito']) ?>
                </div>
                <?php unset($_SESSION['exito']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan Pérez">
                    </div>
                    
                    <div class="form-group">
                        <label for="edad">Edad</label>
                        <input type="number" id="edad" name="edad" min="12" max="100" required placeholder="Ej: 35">
                    </div>
                    
                    <div class="form-group">
                        <label for="estado">Estado Legal</label>
                        <select id="estado" name="estado" required>
                            <option value="" disabled selected>Seleccione un estado</option>
                            <option value="condenado">Condenado</option>
                            <option value="procesado">Procesado</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="ubicacion">Ubicación</label>
                        <input type="text" id="ubicacion" name="ubicacion" required placeholder="Ej: Pabellón 3 - Celda 12">
                    </div>
                    
                    <div class="form-group">
                        <label for="tiempo_condena">Tiempo de Condena</label>
                        <input type="text" id="tiempo_condena" name="tiempo_condena" required placeholder="Ej: 5 años">
                    </div>
                    
                    <div class="form-group">
                        <label for="nivel_riesgo">Nivel de Riesgo</label>
                        <select id="nivel_riesgo" name="nivel_riesgo" required>
                            <option value="" disabled selected>Seleccione nivel</option>
                            <option value="Bajo">Bajo</option>
                            <option value="Medio" selected>Medio</option>
                            <option value="Alto">Alto</option>
                            <option value="Máximo">Máximo</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="causa">Causa de Ingreso</label>
                    <textarea id="causa" name="causa" required placeholder="Describa los detalles de la causa de ingreso..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="sanciones">Sanciones</label>
                    <textarea id="sanciones" name="sanciones" placeholder="Describa las sanciones aplicadas..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Relaciones con otros internos</label>
                    <div class="relacionados-container">
                        <div class="relacionados-title">Seleccione los internos relacionados</div>
                        
                        <?php if ($internos->num_rows > 0): ?>
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
                
                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-block">Guardar Interno</button>
                    <a href="index.php" class="btn btn-outline mt-3">← Volver al inicio</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>