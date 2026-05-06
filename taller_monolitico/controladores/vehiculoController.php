<?php
include("../configuracion/conexion.php");

if ($_POST) {
    $marca = trim($_POST["marca"] ?? "");
    $modelo = trim($_POST["modelo"] ?? "");
    $anio = (int)($_POST["anio"] ?? 0);
    $categoria = trim($_POST["categoria"] ?? "");

    if ($marca !== "" && $modelo !== "" && $anio > 0 && $categoria !== "") {
        $estado = "disponible";
        $stmt = $conn->prepare("INSERT INTO vehiculos (marca, modelo, anio, categoria, estado) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $marca, $modelo, $anio, $categoria, $estado);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../view/vehiculos.php");
    exit;
}
?>