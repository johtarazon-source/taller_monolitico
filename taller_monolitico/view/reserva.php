<?php include("../configuracion/conexion.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas y Devoluciones</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="container">
        <div class="top-nav"><a href="../index.html">Volver al inicio</a></div>

        <div class="panel">
            <h1>Nueva Reserva</h1>
            <form method="POST" action="../controladores/reservaController.php">
                <input type="hidden" name="accion" value="reservar">

                <select name="cliente" required>
                    <option value="">Selecciona cliente</option>
                    <?php
                    $clientes = $conn->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC");
                    while ($row = $clientes->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
                    }
                    ?>
                </select>

                <select name="vehiculo" required>
                    <option value="">Selecciona vehiculo disponible</option>
                    <?php
                    $vehiculos = $conn->query("SELECT id, marca, modelo FROM vehiculos WHERE estado='disponible' ORDER BY marca, modelo");
                    while ($row = $vehiculos->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['marca']} {$row['modelo']}</option>";
                    }
                    ?>
                </select>

                <input type="date" name="inicio" required>
                <input type="date" name="fin" required>
                <button type="submit">Registrar reserva</button>
            </form>
        </div>

        <div class="panel">
            <h2>Reservas activas (devolucion)</h2>
            <table>
                <tr>
                    <th>Cliente</th>
                    <th>Vehiculo</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Accion</th>
                </tr>
                <?php
                $activas = $conn->query("
                    SELECT r.id, r.id_vehiculo, r.fecha_inicio, r.fecha_fin,
                           c.nombre AS cliente, v.marca, v.modelo
                    FROM reservas r
                    INNER JOIN clientes c ON r.id_cliente = c.id
                    INNER JOIN vehiculos v ON r.id_vehiculo = v.id
                    WHERE r.estado = 'activa'
                    ORDER BY r.id DESC
                ");

                while ($row = $activas->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['cliente']}</td>
                            <td>{$row['marca']} {$row['modelo']}</td>
                            <td>{$row['fecha_inicio']}</td>
                            <td>{$row['fecha_fin']}</td>
                            <td>
                                <form method='POST' action='../controladores/reservaController.php' class='row-actions'>
                                    <input type='hidden' name='accion' value='devolver'>
                                    <input type='hidden' name='reserva' value='{$row['id']}'>
                                    <input type='hidden' name='vehiculo' value='{$row['id_vehiculo']}'>
                                    <button type='submit' class='btn-secondary'>Marcar devolucion</button>
                                </form>
                            </td>
                        </tr>";
                }
                ?>
            </table>
        </div>

        <div class="panel">
            <h2>Historial de alquileres</h2>
            <table>
                <tr>
                    <th>Cliente</th>
                    <th>Vehiculo</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Devolucion</th>
                    <th>Estado</th>
                </tr>
                <?php
                $historial = $conn->query("
                    SELECT r.fecha_inicio, r.fecha_fin, r.fecha_devolucion, r.estado,
                           c.nombre AS cliente, v.marca, v.modelo
                    FROM reservas r
                    INNER JOIN clientes c ON r.id_cliente = c.id
                    INNER JOIN vehiculos v ON r.id_vehiculo = v.id
                    ORDER BY r.id DESC
                ");

                while ($row = $historial->fetch_assoc()) {
                    $devolucion = $row["fecha_devolucion"] ?: "Pendiente";
                    echo "<tr>
                            <td>{$row['cliente']}</td>
                            <td>{$row['marca']} {$row['modelo']}</td>
                            <td>{$row['fecha_inicio']}</td>
                            <td>{$row['fecha_fin']}</td>
                            <td>{$devolucion}</td>
                            <td>{$row['estado']}</td>
                        </tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>