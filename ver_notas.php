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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Novedades | Sistema Penitenciario</title>
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
        
        .filtros {
            background: white;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .filtros-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        select, input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
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
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .nota-item {
            background: white;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .nota-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .nota-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .nota-title {
            font-weight: 500;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white;
        }
        
        .badge-conducta { background-color: #9b59b6; }
        .badge-sancion { background-color: var(--danger); }
        .badge-visita { background-color: var(--secondary); }
        .badge-incidente { background-color: var(--warning); color: #333; }
        .badge-medica { background-color: var(--success); }
        .badge-general { background-color: #7f8c8d; }
        
        .nota-fecha {
            color: #666;
            font-size: 0.9rem;
            text-align: right;
        }
        
        .nota-contenido {
            line-height: 1.6;
            white-space: pre-line;
        }
        
        .severidad {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
            margin-left: 10px;
        }
        
        .severidad-1 { color: var(--success); }
        .severidad-2 { color: var(--warning); }
        .severidad-3 { color: var(--danger); }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .filtros-grid {
                grid-template-columns: 1fr;
            }
            
            .menu {
                flex-direction: column;
            }
            
            .nota-header {
                flex-direction: column;
            }
            
            .nota-fecha {
                text-align: left;
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

        <h1><i class="fas fa-clipboard-list"></i> Registro de Novedades</h1>
        
        <div class="filtros card">
            <form method="GET">
                <div class="filtros-grid">
                    <div class="form-group">
                        <label><i class="fas fa-filter"></i> Filtrar por tipo:</label>
                        <select name="tipo">
                            <option value="">Todos los tipos</option>
                            <option value="conducta" <?= isset($_GET['tipo']) && $_GET['tipo'] === 'conducta' ? 'selected' : '' ?>>Conducta</option>
                            <option value="sancion" <?= isset($_GET['tipo']) && $_GET['tipo'] === 'sancion' ? 'selected' : '' ?>>Sanción</option>
                            <option value="visita" <?= isset($_GET['tipo']) && $_GET['tipo'] === 'visita' ? 'selected' : '' ?>>Visita</option>
                            <option value="incidente" <?= isset($_GET['tipo']) && $_GET['tipo'] === 'incidente' ? 'selected' : '' ?>>Incidente</option>
                            <option value="medica" <?= isset($_GET['tipo']) && $_GET['tipo'] === 'medica' ? 'selected' : '' ?>>Atención Médica</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Filtrar por interno:</label>
                        <select name="usuario_id">
                            <option value="">Todos los internos</option>
                            <?php while($persona = $personas->fetch_assoc()): ?>
                                <option value="<?= $persona['id'] ?>" <?= isset($_GET['usuario_id']) && $_GET['usuario_id'] == $persona['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($persona['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn">
                        <i class="fas fa-filter"></i> Aplicar Filtros
                    </button>
                    <a href="ver_notas.php" class="btn btn-secondary">
                        <i class="fas fa-broom"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
        
        <?php if (count($notas) > 0): ?>
            <?php foreach($notas as $nota): ?>
                <div class="nota-item">
                    <div class="nota-header">
                        <div class="nota-title">
                            <strong><?= htmlspecialchars($nota['persona_nombre'] ?: 'Novedad General') ?></strong>
                            <span class="badge badge-<?= htmlspecialchars($nota['tipo']) ?>">
                                <?= ucfirst(htmlspecialchars($nota['tipo'])) ?>
                            </span>
                            <?php if(isset($nota['severidad'])): ?>
                                <span class="severidad severidad-<?= $nota['severidad'] ?>">
                                    <i class="fas fa-exclamation-circle"></i> Severidad: <?= $nota['severidad'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="nota-fecha">
                            <div><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($nota['fecha_registro']) ?></div>
                            <div><i class="fas fa-user-edit"></i> Registrado por: <?= htmlspecialchars($nota['creador']) ?></div>
                            <?php if(isset($nota['fecha_incidente']) && $nota['fecha_incidente'] != $nota['fecha_registro']): ?>
                                <div><i class="fas fa-clock"></i> Incidente ocurrió: <?= htmlspecialchars($nota['fecha_incidente']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="nota-contenido">
                        <?= nl2br(htmlspecialchars($nota['nota'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 2rem;">
                <h3><i class="far fa-folder-open"></i> No se encontraron notas</h3>
                <p>No hay notas registradas con los filtros seleccionados</p>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 2rem;">
            <a href="index.php" class="btn">
                <i class="fas fa-arrow-left"></i> Volver al panel
            </a>
        </div>
    </div>

    <script>
        // Efecto de carga suave para las notas
        document.addEventListener('DOMContentLoaded', () => {
            const notas = document.querySelectorAll('.nota-item');
            notas.forEach((nota, index) => {
                nota.style.opacity = '0';
                nota.style.transform = 'translateY(20px)';
                nota.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                
                setTimeout(() => {
                    nota.style.opacity = '1';
                    nota.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        });
    </script>
</body>
</html>

<?php 
// Liberar recursos
if (isset($stmt)) {
    $stmt->close();
}
if (isset($personas)) {
    $personas->free();
}
$conexion->close(); 
?>