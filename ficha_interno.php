<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("ID de interno no especificado");
}

$id = (int)$_GET['id'];
$conexion = conectarBaseDeDatos();
$interno = $conexion->query("SELECT * FROM personas WHERE id = $id")->fetch_assoc();

if (!$interno) {
    die("Interno no encontrado");
}

// Decodificar ranchograma
$relaciones = json_decode($interno['ranchograma'] ?? '[]', true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ficha de Interno</title>
    <style>
        .ficha { max-width: 800px; margin: 0 auto; }
        .seccion { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .etiqueta { font-weight: bold; color: #555; }
        .riesgo-bajo { color: green; }
        .riesgo-medio { color: orange; }
        .riesgo-alto { color: red; }
        .riesgo-maximo { color: darkred; font-weight: bold; }
        .relaciones { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
        .relacion { border: 1px solid #ddd; padding: 10px; }
    </style>
</head>
<body>
    <div class="ficha">
        <h1>Ficha de Interno: <?= htmlspecialchars($interno['nombre']) ?></h1>
        
        <div class="seccion">
            <h2>Datos Básicos</h2>
            <p><span class="etiqueta">Edad:</span> <?= $interno['edad'] ?></p>
            <p><span class="etiqueta">Ubicación:</span> <?= htmlspecialchars($interno['ubicacion']) ?></p>
            <p><span class="etiqueta">Estado:</span> <?= ucfirst($interno['estado']) ?></p>
            <p><span class="etiqueta">Tiempo de condena:</span> <?= htmlspecialchars($interno['tiempo_condena']) ?></p>
            <p><span class="etiqueta">Nivel de riesgo:</span> 
                <span class="riesgo-<?= strtolower($interno['nivel_riesgo']) ?>">
                    <?= $interno['nivel_riesgo'] ?>
                </span>
            </p>
        </div>
        
        <div class="seccion">
            <h2>Causa de Ingreso</h2>
            <p><?= nl2br(htmlspecialchars($interno['causa'])) ?></p>
        </div>
        
        <div class="seccion">
            <h2>Sanciones</h2>
            <p><?= $interno['sanciones'] ? nl2br(htmlspecialchars($interno['sanciones'])) : 'Ninguna registrada' ?></p>
        </div>
        
        <div class="seccion">
            <h2>Ranchograma (Relaciones)</h2>
            <?php if (!empty($relaciones)): ?>
                <div class="relaciones">
                    <?php foreach ($relaciones as $relacion): 
                        $relacionado = $conexion->query("SELECT nombre FROM personas WHERE id = {$relacion['interno_id']}")->fetch_assoc();
                    ?>
                        <div class="relacion">
                            <strong><?= htmlspecialchars($relacionado['nombre'] ?? 'Desconocido') ?></strong><br>
                            Tipo: <?= htmlspecialchars($relacion['tipo_relacion'] ?? 'Desconocido') ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No se han registrado relaciones</p>
            <?php endif; ?>
        </div>
    </div>
     <p><a href="index.php">← Volver al inicio</a></p>
</body>
</html>