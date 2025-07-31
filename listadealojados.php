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
    <title>Lista de Alojados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f2f2f2;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #333;
            color: white;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .sancionado {
            background-color: #ffeeee;
        }

        .acciones a {
            margin-right: 8px;
            text-decoration: none;
            padding: 6px 10px;
            color: white;
            border-radius: 5px;
        }

        .ver-ficha {
            background-color: #2980b9;
        }

        .editar {
            background-color: #f39c12;
        }

        .busqueda {
            margin-bottom: 20px;
        }

        .busqueda input, .busqueda select, .busqueda button {
            padding: 8px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Lista de Alojados</h1>

        <div class="busqueda">
            <input type="text" id="buscar" placeholder="Buscar por nombre..." onkeyup="filtrarTabla()">
            <select id="filtro-riesgo" onchange="filtrarTabla()">
                <option value="">Todos los niveles</option>
                <option value="Bajo">Bajo</option>
                <option value="Medio">Medio</option>
                <option value="Alto">Alto</option>
                <option value="Máximo">Máximo</option>
            </select>
            <button onclick="filtrarTabla()">Buscar</button>
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
                        <?php
                            $riesgo = strtolower($interno['nivel_riesgo']);
                            $color = "";

                            switch ($riesgo) {
                                case 'bajo':
                                    $color = "background-color: green; color: white;";
                                    break;
                                case 'medio':
                                    $color = "background-color: gold; color: black;";
                                    break;
                                case 'alto':
                                    $color = "background-color: orange; color: white;";
                                    break;
                                case 'máximo':
                                    $color = "background-color: red; color: white; font-weight: bold;";
                                    break;
                            }
                        ?>
                        <span style="padding:6px 10px; border-radius: 8px; <?= $color ?>">
                            <?= htmlspecialchars($interno['nivel_riesgo']) ?>
                        </span>
                    </td>
                    <td><?= ucfirst(htmlspecialchars($interno['estado'])) ?></td>
                    <td><?= $interno['sancionado'] === 'sancionado' ? '✅ Sí' : '❌ No' ?></td>
                    
                    <td class="acciones">
                        <a href="ficha_interno.php?id=<?= $interno['id'] ?>" class="ver-ficha">Ficha</a>
                        <?php if(!$es_direccion): ?>
                        <a href="actualizar_estado.php?id=<?= $interno['id'] ?>" class="editar">Editar</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
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
    </script>
</body>
</html>
