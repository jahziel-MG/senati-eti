<?php

require_once "conexion.php";

header("Content-Type: application/json; charset=UTF-8");


/* =========================================
   VERIFICAR MÉTODO
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "exito" => false,
        "mensaje" => "Método no permitido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* =========================================
   RECIBIR DATOS
========================================= */

$id_visita = isset($_POST["id_visita"])
    ? (int) $_POST["id_visita"]
    : 0;

$respuesta = trim($_POST["respuesta"] ?? "");


if ($id_visita <= 0) {

    echo json_encode([
        "exito" => false,
        "mensaje" => "ID de visita no válido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if ($respuesta === "") {

    echo json_encode([
        "exito" => false,
        "mensaje" => "Debe ingresar una respuesta."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* =========================================
   VERIFICAR VISITA
========================================= */

$sql = "
    SELECT id_visita, estado
    FROM visitas
    WHERE id_visita = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param("i", $id_visita);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows !== 1) {

    echo json_encode([
        "exito" => false,
        "mensaje" => "La visita no existe."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$visita = $resultado->fetch_assoc();


/* =========================================
   VERIFICAR ESTADO
========================================= */

if ($visita["estado"] !== "EN_ATENCION") {

    echo json_encode([
        "exito" => false,
        "mensaje" => "La visita no está actualmente en atención."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* =========================================
   FINALIZAR VISITA
========================================= */

$sqlActualizar = "
    UPDATE visitas

    SET
        respuesta = ?,
        estado = 'RESUELTA',
        resuelto_at = CURRENT_TIMESTAMP,
        finalizado_at = CURRENT_TIMESTAMP

    WHERE id_visita = ?
";


$stmtActualizar = $conexion->prepare($sqlActualizar);

$stmtActualizar->bind_param(
    "si",
    $respuesta,
    $id_visita
);


if (!$stmtActualizar->execute()) {

    echo json_encode([
        "exito" => false,
        "mensaje" => "No se pudo finalizar la visita."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* =========================================
   RESPUESTA
========================================= */

echo json_encode([

    "exito" => true,

    "mensaje" => "La visita fue finalizada correctamente.",

    "id_visita" => $id_visita

], JSON_UNESCAPED_UNICODE);

?>