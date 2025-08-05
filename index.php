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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control | Sistema Penitenciario</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
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
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            padding: 1.5rem;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card h3 {
            color: var(--primary);
            margin-bottom: 1rem;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stats-container {
            display: flex;
            gap: 15px;
        }
        
        .stat-box {
            flex: 1;
            padding: 15px;
            border-radius: 8px;
            background: var(--light);
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin: 5px 0;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }
        
        .nota-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }
        
        .nota-item:hover {
            background: #f9f9f9;
        }
        
        .nota-item:last-child {
            border-bottom: none;
        }
        
        .nota-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .nota-title {
            font-weight: 500;
            color: var(--dark);
        }
        
        .nota-date {
            font-size: 0.8rem;
            color: #777;
        }
        
        .nota-preview {
            color: #555;
            margin: 8px 0;
            font-size: 0.95rem;
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
        
        .badge-conducta { background: #9b59b6; color: white; }
        .badge-sancion { background: #e74c3c; color: white; }
        .badge-visita { background: #3498db; color: white; }
        .badge-incidente { background: #f39c12; color: white; }
        .badge-medica { background: #2ecc71; color: white; }
        .badge-general { background: #7f8c8d; color: white; }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .stats-container {
                flex-direction: column;
            }
            
            .menu {
                flex-direction: column;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        <div class="grid">
            <div class="card">
                <h3><i class="fas fa-chart-pie"></i> Resumen General</h3>
                <div class="stats-container">
                    <div class="stat-box">
                        <div class="stat-value"><?= $total_internos ?? 0 ?></div>
                        <div class="stat-label">Internos totales</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?= $total_sancionados ?? 0 ?></div>
                        <div class="stat-label">Sancionados</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3><i class="fas fa-bell"></i> Últimas Novedades</h3>
                <?php if (isset($ultimas_notas) && $ultimas_notas->num_rows > 0): ?>
                    <?php while($nota = $ultimas_notas->fetch_assoc()): ?>
                        <div class="nota-item">
                            <div class="nota-header">
                                <span class="nota-title"><?= htmlspecialchars($nota['nombre'] ?? 'General') ?></span>
                                <span class="nota-date"><?= htmlspecialchars($nota['fecha'] ?? '') ?></span>
                            </div>
                            <div class="nota-preview"><?= htmlspecialchars(substr($nota['nota'] ?? '', 0, 80)) ?>...</div>
                            <span class="badge badge-<?= isset($nota['tipo']) ? strtolower($nota['tipo']) : 'general' ?>">
                                <?= isset($nota['tipo']) ? ucfirst(htmlspecialchars($nota['tipo'])) : 'General' ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="nota-item">
                        <div class="nota-preview">No hay novedades recientes</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Efecto de carga suave
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 150 * index);
            });
        });
    </script>
</body>
</html>