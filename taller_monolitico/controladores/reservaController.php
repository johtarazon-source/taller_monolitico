<?php
include("../configuracion/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "reservar";

    if ($accion === "reservar") {
        $cliente = (int)($_POST["cliente"] ?? 0);
        $vehiculo = (int)($_POST["vehiculo"] ?? 0);
        $inicio = $_POST["inicio"] ?? "";
        $fin = $_POST["fin"] ?? "";

        if ($cliente > 0 && $vehiculo > 0 && $inicio !== "" && $fin !== "" && $inicio <= $fin) {
            $check = $conn->prepare("SELECT estado FROM vehiculos WHERE id = ?");
            $check->bind_param("i", $vehiculo);
            $check->execute();
            $estadoResult = $check->get_result();
            $vehiculoData = $estadoResult->fetch_assoc();
            $check->close();

            if ($vehiculoData && $vehiculoData["estado"] === "disponible") {
                $estadoReserva = "activa";
                $ins = $conn->prepare("INSERT INTO reservas (id_cliente, id_vehiculo, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, ?)");
                $ins->bind_param("iisss", $cliente, $vehiculo, $inicio, $fin, $estadoReserva);
                $ins->execute();
                $ins->close();

                $estadoVehiculo = "alquilado";
                $updVehiculo = $conn->prepare("UPDATE vehiculos SET estado = ? WHERE id = ?");
                $updVehiculo->bind_param("si", $estadoVehiculo, $vehiculo);
                $updVehiculo->execute();
                $updVehiculo->close();
            }
        }
    }

    if ($accion === "devolver") {
        $reserva = (int)($_POST["reserva"] ?? 0);
        $vehiculo = (int)($_POST["vehiculo"] ?? 0);
        $fechaDevolucion = date("Y-m-d");

        if ($reserva > 0 && $vehiculo > 0) {
            $estadoReserva = "finalizada";
            $updReserva = $conn->prepare("UPDATE reservas SET estado = ?, fecha_devolucion = ? WHERE id = ?");
            $updReserva->bind_param("ssi", $estadoReserva, $fechaDevolucion, $reserva);
            $updReserva->execute();
            $updReserva->close();

            $estadoVehiculo = "disponible";
            $updVehiculo = $conn->prepare("UPDATE vehiculos SET estado = ? WHERE id = ?");
            $updVehiculo->bind_param("si", $estadoVehiculo, $vehiculo);
            $updVehiculo->execute();
            $updVehiculo->close();
        }
    }

    header("Location: ../view/reserva.php");
    exit;
}
?>