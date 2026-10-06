<?php

require_once "conexion.php";

header("Content-Type: application/json; charset=UTF-8");


/* =========================================
   CONSULTAR VISITAS
========================================= */

$sql = "
    SELECT
        v.id_visita,
        v.id_visitante,
        CONCAT(vi.nombres, ' ', vi.apellidos) AS visitante,
        a.nombre AS asunto,
        v.prioridad,
        v.estado,
        v.consulta,
        v.registrado_at,
        v.tomado_at,
        v.inicio_at,
        v.resuelto_at,
        CONCAT(e.nombres, ' ', e.apellidos) AS empleado

    FROM visitas v

    INNER JOIN visitantes vi
        ON v.id_visitante = vi.id_visitante

    INNER JOIN asuntos a
        ON v.id_asunto = a.id_asunto

    LEFT JOIN empleados e
        ON v.id_empleado = e.id_empleado

    ORDER BY v.registrado_at DESC
";


$resultado = $conexion->query($sql);


if (!$resultado) {

    http_response_code(500);

    echo json_encode([
        "error" => "Error al consultar las visitas.",
        "detalle" => $conexion->error
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* =========================================
   PREPARAR RESPUESTA
========================================= */

$visitas = [];


while ($fila = $resultado->fetch_assoc()) {

    $visitas[] = [
        "id_visita" => (int) $fila["id_visita"],
        "id_visitante" => (int) $fila["id_visitante"],

        "visitante" => $fila["visitante"],

        "asunto" => $fila["asunto"],

        "prioridad" => $fila["prioridad"],

        "estado" => $fila["estado"],

        "consulta" => $fila["consulta"],

        "registrado_at" => $fila["registrado_at"],

        "tomado_at" => $fila["tomado_at"],

        "inicio_at" => $fila["inicio_at"],

        "resuelto_at" => $fila["resuelto_at"],

        "empleado" => $fila["empleado"]

    ];
}


/* =========================================
   ENVIAR DATOS
========================================= */

echo json_encode(
    $visitas,
    JSON_UNESCAPED_UNICODE
);

?>