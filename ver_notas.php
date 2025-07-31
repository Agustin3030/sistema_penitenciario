<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$filtro = "";
$params = [];

if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
    $filtro .= " AND n.tipo = ?";
    $params[] = $_GET['tipo'];
}

if (isset($_GET['usuario_id']) && !empty($_GET['usuario_id'])) {
    $filtro .= " AND n.usuario_id = ?";
    $params[] = (int)$_GET['usuario_id'];
}

$conexion = conectarBaseDeDatos();
$personas = $conexion->query("SELECT id, nombre FROM personas ORDER BY nombre");

// Consulta modificada con los nombres correctos de las columnas
$sql = "SELECT n.*, p.nombre as persona_nombre, u.nombre as creador 
        FROM notas n 
        LEFT JOIN personas p ON n.usuario_id = p.id 
        JOIN usuarios u ON n.creado_por = u.id
        WHERE 1=1 $filtro
        ORDER BY n.fecha_registro DESC";  // Usando fecha_registro que es el campo real

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die("Error al preparar la consulta: " . $conexion->error);
}

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$notas = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registro de Novedades</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 1rem; }
        .filtros { background: #f8f9fa; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; }
        .filtros label { display: block; margin-bottom: 0.5rem; }
        .nota-item { background: white; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        .nota-header { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; margin-bottom: 0.5rem; }
        .nota-tipo { display: inline-block; padding: 0.2rem 0.5rem; background: #007bff; color: white; border-radius: 4px; font-size: 0.8rem; }
        .nota-fecha { color: #666; font-size: 0.9rem; }
        .btn { padding: 0.5rem 1rem; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
        .severidad { margin-left: 0.5rem; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Registro de Novedades</h2>
    
    <div class="filtros">
        <form method="GET">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label>Filtrar por tipo:</label>
                    <select name="tipo" style="width: 100%;">
                        <option value="">Todos los tipos</option>
                        <option value="conducta" <?= isset($_GET['tipo']) && $_GET['tipo'] === 'conducta' ? 'selected' : '' ?>>Conducta</option>
                        <option value="sancion" <?= isset($_GET['tipo']) && $_GET['tipo'] === 'sancion' ? 'selected' : '' ?>>Sanción</option>
                        <option value="visita" <?= isset($_GET['tipo']) && $_GET['tipo'] === 'visita' ? 'selected' : '' ?>>Visita</option>
                        <option value="incidente" <?= isset($_GET['tipo']) && $_GET['tipo'] === 'incidente' ? 'selected' : '' ?>>Incidente</option>
                        <option value="medica" <?= isset($_GET['tipo']) && $_GET['tipo'] === 'medica' ? 'selected' : '' ?>>Atención Médica</option>
                    </select>
                </div>
                
                <div>
                    <label>Filtrar por interno:</label>
                    <select name="usuario_id" style="width: 100%;">
                        <option value="">Todos los internos</option>
                        <?php while($persona = $personas->fetch_assoc()): ?>
                            <option value="<?= $persona['id'] ?>" <?= isset($_GET['usuario_id']) && $_GET['usuario_id'] == $persona['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($persona['nombre']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <button type="submit" style="margin-top: 1rem;" class="btn">Aplicar Filtros</button>
            <a href="ver_notas.php" class="btn" style="background: #6c757d; margin-left: 0.5rem;">Limpiar</a>
        </form>
    </div>
    
    <?php foreach($notas as $nota): ?>
        <div class="nota-item">
            <div class="nota-header">
                <div>
                    <strong><?= htmlspecialchars($nota['persona_nombre'] ?: 'Novedad General') ?></strong>
                    <span class="nota-tipo"><?= ucfirst(htmlspecialchars($nota['tipo'])) ?></span>
                    <?php if(isset($nota['severidad'])): ?>
                        <span class="severidad" style="color: <?= $nota['severidad'] > 2 ? 'red' : 'orange' ?>;">
                            Severidad: <?= $nota['severidad'] ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="nota-fecha">
                    <?= htmlspecialchars($nota['fecha_registro']) ?> - Registrado por: <?= htmlspecialchars($nota['creador']) ?>
                    <?php if(isset($nota['fecha_incidente']) && $nota['fecha_incidente'] != $nota['fecha_registro']): ?>
                        <br>Incidente ocurrió: <?= htmlspecialchars($nota['fecha_incidente']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="nota-contenido">
                <?= nl2br(htmlspecialchars($nota['nota'])) ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <p><a href="index.php" class="btn">Volver al panel</a></p>
</body>
</html>