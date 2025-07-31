<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Estadísticas para el dashboard
$conexion = conectarBaseDeDatos();
$total_internos = $conexion->query("SELECT COUNT(*) as total FROM personas")->fetch_assoc()['total'];
$total_sancionados = $conexion->query("SELECT COUNT(*) as total FROM personas WHERE sancionado = 'sancionado'")->fetch_assoc()['total'];

// Consulta modificada para obtener las últimas notas
$sql_notas = "SELECT n.nota, p.nombre, n.fecha_registro as fecha 
              FROM notas n 
              LEFT JOIN personas p ON n.usuario_id = p.id 
              ORDER BY n.fecha_registro DESC 
              LIMIT 5";
$ultimas_notas = $conexion->query($sql_notas);

// Verificar si la consulta tuvo éxito
if ($ultimas_notas === false) {
    die("Error en la consulta: " . $conexion->error);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Panel de Control</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
        .header { background: #333; color: white; padding: 1rem; display: flex; justify-content: space-between; }
        .container { padding: 1rem; }
        .card { background: white; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); padding: 1rem; margin-bottom: 1rem; }
        .card h3 { margin-top: 0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; }
        .menu { display: flex; gap: 1rem; margin: 1rem 0; }
        .menu a { text-decoration: none; background: #007bff; color: white; padding: 0.5rem 1rem; border-radius: 4px; }
        .nota-item { border-bottom: 1px solid #eee; padding: 0.5rem 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></h2>
        <a href="logout.php" style="color: white;">Cerrar sesión</a>
    </div>

    <div class="container">
        <div class="menu">
            <a href="listadealojados.php">Lista de Alojados</a>
            <a href="actualizar_estado.php">Actualizar Estados</a>
            <a href="cargar_nota.php">Registrar Novedad</a>
            <a href="ver_notas.php">Ver Novedades</a>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <a href="registrar_usuario.php">Registrar Usuario</a>
                <a href="agregarpersona.php">Agregar Interno</a>

            <?php endif; ?>
        </div>

        <div class="grid">
            <div class="card">
                <h3>Resumen</h3>
                <p>Internos totales: <?= $total_internos ?></p>
                <p>Sancionados: <?= $total_sancionados ?></p>
            </div>

            <div class="card">
                <h3>Últimas Novedades</h3>
                <?php while($nota = $ultimas_notas->fetch_assoc()): ?>
                    <div class="nota-item">
                        <strong><?= htmlspecialchars($nota['nombre'] ?: 'General') ?></strong>
                        <p><?= htmlspecialchars(substr($nota['nota'], 0, 50)) ?>...</p>
                        <small><?= htmlspecialchars($nota['fecha']) ?></small>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>