<?php include("../configuracion/conexion.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="container">
        <div class="top-nav"><a href="../index.html">Volver al inicio</a></div>

        <div class="panel">
            <h1>Registro de Clientes</h1>
            <form method="POST" action="../controladores/clienteController.php">
                <input type="text" name="nombre" placeholder="Nombre completo" required>
                <input type="text" name="contacto" placeholder="Telefono o correo" required>
                <input type="text" name="licencia" placeholder="Numero de licencia" required>
                <button type="submit">Guardar cliente</button>
            </form>
        </div>

        <div class="panel">
            <h2>Listado de clientes</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Contacto</th>
                    <th>Licencia</th>
                </tr>
                <?php
                $result = $conn->query("SELECT * FROM clientes ORDER BY id DESC");
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['nombre']}</td>
                            <td>{$row['contacto']}</td>
                            <td>{$row['licencia']}</td>
                        </tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>