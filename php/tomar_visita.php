<?php

session_start();

require_once "conexion.php";

header("Content-Type: application/json; charset=UTF-8");


/* =========================================
   VERIFICAR MÉTODO
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Método no permitido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* =========================================
   RECIBIR ID DE VISITA
========================================= */

$id_visita = isset($_POST["id_visita"])
    ? (int) $_POST["id_visita"]
    : 0;


if ($id_visita <= 0) {

    echo json_encode([
        "exito" => false,
        "mensaje" => "ID de visita no válido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* =========================================
   EMPLEADO ACTUAL
========================================= */

/*
 * Para esta primera prueba utilizamos
 * el empleado ID 1, que corresponde a
 * Carlos Rodriguez Lopez.
 */

$id_empleado = 1;


/* =========================================
   VERIFICAR QUE LA VISITA EXISTA
========================================= */

$sql = "
    SELECT id_visita, estado
    FROM visitas
    WHERE id_visita = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $id_visita
);

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

if (
    $visita["estado"] !== "REGISTRADA" &&
    $visita["estado"] !== "ESPERANDO"
) {

    echo json_encode([
        "exito" => false,
        "mensaje" => "Esta visita ya fue tomada o no está disponible."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* =========================================
   TOMAR VISITA
========================================= */

$sqlActualizar = "
    UPDATE visitas

    SET
        id_empleado = ?,
        estado = 'EN_ATENCION',
        tomado_at = CURRENT_TIMESTAMP,
        inicio_at = CURRENT_TIMESTAMP

    WHERE id_visita = ?
";


$stmtActualizar = $conexion->prepare(
    $sqlActualizar
);


$stmtActualizar->bind_param(
    "ii",
    $id_empleado,
    $id_visita
);


if (!$stmtActualizar->execute()) {

    echo json_encode([
        "exito" => false,
        "mensaje" => "No se pudo actualizar la visita.",
        "detalle" => $conexion->error
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* =========================================
   RESPUESTA
========================================= */

echo json_encode([

    "exito" => true,

    "mensaje" => "La visita fue tomada correctamente.",

    "id_visita" => $id_visita,

    "id_empleado" => $id_empleado

], JSON_UNESCAPED_UNICODE);

?>