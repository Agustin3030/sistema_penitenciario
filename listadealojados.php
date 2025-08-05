<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
// Verificar si el usuario tiene rol de dirección
$es_direccion = ($_SESSION['rol'] === 'direccion');

$conexion = conectarBaseDeDatos();

$query = "SELECT 
            id, nombre, edad, causa, estado, ubicacion, 
            tiempo_condena, nivel_riesgo, sancionado 
          FROM personas 
          ORDER BY nombre";
$resultado = $conexion->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Alojados | Sistema Penitenciario</title>
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
        
        h1 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .busqueda {
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .busqueda input, 
        .busqueda select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            flex: 1;
            min-width: 200px;
        }
        
        .busqueda button {
            padding: 10px 20px;
            background-color: var(--secondary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .busqueda button:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
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
        
        .sancionado {
            background-color: #ffebee;
        }
        
        .acciones {
            white-space: nowrap;
        }
        
        .acciones a {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 13px;
            margin-right: 5px;
            transition: all 0.3s;
            gap: 5px;
        }
        
        .ver-ficha {
            background-color: var(--secondary);
        }
        
        .ver-ficha:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .editar {
            background-color: var(--warning);
        }
        
        .editar:hover {
            background-color: #e67e22;
            transform: translateY(-2px);
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            text-align: center;
            min-width: 70px;
        }
        
        .riesgo-bajo {
            background-color: var(--success);
            color: white;
        }
        
        .riesgo-medio {
            background-color: var(--warning);
            color: #333;
        }
        
        .riesgo-alto {
            background-color: #e67e22;
            color: white;
        }
        
       .riesgo-maximo {
    background-color: #ff0000;  /* Rojo puro */
    color: white;
    font-weight: bold;
    animation: pulse 1.5s infinite;
    text-shadow: 0 0 2px rgba(0,0,0,0.3); /* Mejor contraste */
}
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .busqueda {
                flex-direction: column;
            }
            
            table {
                display: block;
                overflow-x: auto;
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

        <h1><i class="fas fa-list"></i> Lista de Alojados</h1>

        <div class="busqueda">
            <input type="text" id="buscar" placeholder="Buscar por nombre..." onkeyup="filtrarTabla()">
            <select id="filtro-riesgo" onchange="filtrarTabla()">
                <option value="">Todos los niveles</option>
                <option value="Bajo">Bajo</option>
                <option value="Medio">Medio</option>
                <option value="Alto">Alto</option>
                <option value="Máximo">Máximo</option>
            </select>
            <button onclick="filtrarTabla()">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
        <table id="tabla-internos">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Edad</th>
                    <th>Ubicación</th>
                    <th>Tiempo Condena</th>
                    <th>Nivel Riesgo</th>
                    <th>Estado</th>
                    <th>Sancionado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($interno = $resultado->fetch_assoc()): ?>
                <tr class="<?= $interno['sancionado'] === 'sancionado' ? 'sancionado' : '' ?>">
                    <td><?= htmlspecialchars($interno['nombre']) ?></td>
                    <td><?= htmlspecialchars($interno['edad']) ?></td>
                    <td><?= htmlspecialchars($interno['ubicacion']) ?></td>
                    <td><?= htmlspecialchars($interno['tiempo_condena']) ?></td>
                    <td>
                        <span class="badge riesgo-<?= strtolower($interno['nivel_riesgo']) ?>">
                            <?= htmlspecialchars($interno['nivel_riesgo']) ?>
                        </span>
                    </td>
                    <td><?= ucfirst(htmlspecialchars($interno['estado'])) ?></td>
                    <td><?= $interno['sancionado'] === 'sancionado' ? '✅ Sí' : '❌ No' ?></td>
                    
                    <td class="acciones">
                        <a href="ficha_interno.php?id=<?= $interno['id'] ?>" class="ver-ficha">
                            <i class="fas fa-file-alt"></i> Ficha
                        </a>
                        <?php if(!$es_direccion): ?>
                        <a href="actualizar_estado.php?id=<?= $interno['id'] ?>" class="editar">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
         <div style="margin-top: 2rem;">
            <a href="index.php" class="btn">
                <i class="fas fa-arrow-left"></i> Volver al panel
            </a>
        </div>
    </div>

    <script>
        function filtrarTabla() {
            const input = document.getElementById('buscar');
            const filtroRiesgo = document.getElementById('filtro-riesgo').value.toLowerCase();
            const filter = input.value.toLowerCase();
            const table = document.getElementById('tabla-internos');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const tdNombre = tr[i].getElementsByTagName('td')[0];
                const tdRiesgo = tr[i].getElementsByTagName('td')[4];

                if (tdNombre && tdRiesgo) {
                    const txtValue = tdNombre.textContent || tdNombre.innerText;
                    const riesgoValue = tdRiesgo.textContent || tdRiesgo.innerText;

                    const nombreMatch = txtValue.toLowerCase().indexOf(filter) > -1;
                    const riesgoMatch = filtroRiesgo === '' || riesgoValue.toLowerCase().indexOf(filtroRiesgo) > -1;

                    tr[i].style.display = (nombreMatch && riesgoMatch) ? '' : 'none';
                }
            }
        }
        
        // Efecto de carga suave
        document.addEventListener('DOMContentLoaded', () => {
            const rows = document.querySelectorAll('#tabla-internos tbody tr');
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