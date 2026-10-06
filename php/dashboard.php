<?php

require_once "conexion.php";

header("Content-Type: application/json; charset=UTF-8");

$respuesta = [

    "visitantes_activos" => 0,
    "visitas_registradas" => 0,
    "tiempo_promedio_espera" => 0,
    "tasa_efectividad" => 0,

    "visitas_hora" => [],
    "rendimiento_empleados" => [],
    "distribucion_asuntos" => []

];


/* =====================================================
   1. VISITANTES ACTIVOS
===================================================== */

$sql = "
    SELECT COUNT(*) AS total
    FROM visitas
    WHERE estado IN (
        'REGISTRADA',
        'ESPERANDO',
        'EN_ATENCION'
    )
";

$resultado = $conexion->query($sql);

if ($resultado) {

    $fila = $resultado->fetch_assoc();

    $respuesta["visitantes_activos"] =
        (int) $fila["total"];
}


/* =====================================================
   2. VISITAS REGISTRADAS
===================================================== */

$sql = "
    SELECT COUNT(*) AS total
    FROM visitas
";

$resultado = $conexion->query($sql);

if ($resultado) {

    $fila = $resultado->fetch_assoc();

    $respuesta["visitas_registradas"] =
        (int) $fila["total"];
}


/* =====================================================
   3. TIEMPO PROMEDIO DE ESPERA
===================================================== */

$sql = "
    SELECT AVG(
        TIMESTAMPDIFF(
            MINUTE,
            registrado_at,
            tomado_at
        )
    ) AS promedio

    FROM visitas

    WHERE registrado_at IS NOT NULL
    AND tomado_at IS NOT NULL
";

$resultado = $conexion->query($sql);

if ($resultado) {

    $fila = $resultado->fetch_assoc();

    if ($fila["promedio"] !== null) {

        $respuesta["tiempo_promedio_espera"] =
            round((float) $fila["promedio"]);
    }
}


/* =====================================================
   4. TASA DE EFECTIVIDAD
===================================================== */

$sql = "
    SELECT
        COUNT(*) AS total,

        SUM(
            CASE
                WHEN estado = 'RESUELTA'
                THEN 1
                ELSE 0
            END
        ) AS resueltas

    FROM visitas
";

$resultado = $conexion->query($sql);

if ($resultado) {

    $fila = $resultado->fetch_assoc();

    $total =
        (int) $fila["total"];

    $resueltas =
        (int) $fila["resueltas"];

    if ($total > 0) {

        $respuesta["tasa_efectividad"] =
            round(($resueltas / $total) * 100);
    }
}


/* =====================================================
   5. VISITAS POR HORA
===================================================== */

$sql = "
    SELECT
        HOUR(registrado_at) AS hora,
        COUNT(*) AS total

    FROM visitas

    GROUP BY HOUR(registrado_at)

    ORDER BY hora
";

$resultado = $conexion->query($sql);

if ($resultado) {

    while ($fila = $resultado->fetch_assoc()) {

        $respuesta["visitas_hora"][] = [

            "hora" => (int) $fila["hora"],

            "total" => (int) $fila["total"]

        ];
    }
}


/* =====================================================
   6. RENDIMIENTO DE EMPLEADOS
===================================================== */

$sql = "
    SELECT

        CONCAT(
            e.nombres,
            ' ',
            e.apellidos
        ) AS empleado,

        COUNT(v.id_visita) AS total

    FROM empleados e

    LEFT JOIN visitas v
        ON e.id_empleado = v.id_empleado
        AND v.estado = 'RESUELTA'

    GROUP BY
        e.id_empleado,
        e.nombres,
        e.apellidos

    ORDER BY total DESC
";

$resultado = $conexion->query($sql);

if ($resultado) {

    while ($fila = $resultado->fetch_assoc()) {

        $respuesta["rendimiento_empleados"][] = [

            "empleado" =>
                $fila["empleado"],

            "total" =>
                (int) $fila["total"]

        ];
    }
}


/* =====================================================
   7. DISTRIBUCIÓN POR ASUNTO
===================================================== */

$sql = "
    SELECT

        a.nombre AS asunto,

        COUNT(v.id_visita) AS total

    FROM asuntos a

    LEFT JOIN visitas v
        ON a.id_asunto = v.id_asunto

    GROUP BY
        a.id_asunto,
        a.nombre

    HAVING total > 0

    ORDER BY total DESC
";

$resultado = $conexion->query($sql);

if ($resultado) {

    while ($fila = $resultado->fetch_assoc()) {

        $respuesta["distribucion_asuntos"][] = [

            "asunto" =>
                $fila["asunto"],

            "total" =>
                (int) $fila["total"]

        ];
    }
}


/* =====================================================
   RESPUESTA FINAL
===================================================== */

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

?>