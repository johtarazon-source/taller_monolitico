<?php include("../configuracion/conexion.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehiculos</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="container">
        <div class="top-nav"><a href="../index.html">Volver al inicio</a></div>

        <div class="panel">
            <h1>Registro de Vehiculos</h1>
            <form method="POST" action="../controladores/vehiculoController.php">
                <input type="text" name="marca" placeholder="Marca" required>
                <input type="text" name="modelo" placeholder="Modelo" required>
                <input type="number" name="anio" min="1900" max="2100" placeholder="Anio" required>
                <select name="categoria" required>
                    <option value="">Selecciona una categoria</option>
                    <option value="economico">Economico</option>
                    <option value="sedan">Sedan</option>
                    <option value="suv">SUV</option>
                    <option value="pickup">Pickup</option>
                    <option value="lujo">Lujo</option>
                </select>
                <button type="submit">Guardar vehiculo</button>
            </form>
        </div>

        <div class="panel">
            <h2>Flota registrada</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Anio</th>
                    <th>Categoria</th>
                    <th>Estado</th>
                </tr>
                <?php
                $result = $conn->query("SELECT * FROM vehiculos ORDER BY id DESC");
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['marca']}</td>
                            <td>{$row['modelo']}</td>
                            <td>{$row['anio']}</td>
                            <td>{$row['categoria']}</td>
                            <td>{$row['estado']}</td>
                        </tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>