<?php
include("../configuracion/conexion.php");

if ($_POST) {
    $nombre = trim($_POST["nombre"] ?? "");
    $contacto = trim($_POST["contacto"] ?? "");
    $licencia = trim($_POST["licencia"] ?? "");

    if ($nombre !== "" && $contacto !== "" && $licencia !== "") {
        $stmt = $conn->prepare("INSERT INTO clientes (nombre, contacto, licencia) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $contacto, $licencia);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../view/clientes.php");
    exit;
}
?>