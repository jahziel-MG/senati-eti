<?php

require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../registrar-visita.html");
    exit;
}


/* =========================================
   RECIBIR DATOS
========================================= */

$documento = trim($_POST["documento"] ?? "");
$nombres = trim($_POST["nombres"] ?? "");
$apellidos = trim($_POST["apellidos"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$correo = trim($_POST["correo"] ?? "");
$id_asunto = (int) ($_POST["id_asunto"] ?? 0);
$prioridad = strtoupper(trim($_POST["prioridad"] ?? "MEDIA"));
$consulta = trim($_POST["consulta"] ?? "");


/* =========================================
   VALIDAR CAMPOS
========================================= */

if (
    $documento === "" ||
    $nombres === "" ||
    $apellidos === "" ||
    $id_asunto <= 0 ||
    $consulta === ""
) {
    die("Todos los campos obligatorios deben completarse.");
}


/* =========================================
   VALIDAR PRIORIDAD
========================================= */

$prioridades_validas = [
    "BAJA",
    "MEDIA",
    "ALTA",
    "URGENTE"
];

if (!in_array($prioridad, $prioridades_validas, true)) {
    die("La prioridad seleccionada no es válida.");
}


/* =========================================
   VERIFICAR ASUNTO
========================================= */

$sql = "
    SELECT id_asunto
    FROM asuntos
    WHERE id_asunto = ?
    AND estado = 1
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error al verificar el asunto.");
}

$stmt->bind_param("i", $id_asunto);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    die("El asunto seleccionado no existe.");
}


/* =========================================
   BUSCAR VISITANTE
========================================= */

$sql = "
    SELECT id_visitante
    FROM visitantes
    WHERE documento = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error al buscar el visitante.");
}

$stmt->bind_param("s", $documento);
$stmt->execute();

$resultado = $stmt->get_result();


/* =========================================
   VISITANTE EXISTENTE
========================================= */

if ($resultado->num_rows > 0) {

    $visitante = $resultado->fetch_assoc();

    $id_visitante = (int) $visitante["id_visitante"];


    /* Actualizar datos del visitante */

    $sql = "
        UPDATE visitantes
        SET
            nombres = ?,
            apellidos = ?,
            telefono = ?,
            correo = ?
        WHERE id_visitante = ?
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error al actualizar los datos del visitante.");
    }

    $stmt->bind_param(
        "ssssi",
        $nombres,
        $apellidos,
        $telefono,
        $correo,
        $id_visitante
    );

    if (!$stmt->execute()) {
        die("Error al actualizar el visitante.");
    }

}


/* =========================================
   NUEVO VISITANTE
========================================= */

else {

    $sql = "
        INSERT INTO visitantes
        (
            documento,
            nombres,
            apellidos,
            telefono,
            correo
        )
        VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error al preparar el registro del visitante.");
    }

    $stmt->bind_param(
        "sssss",
        $documento,
        $nombres,
        $apellidos,
        $telefono,
        $correo
    );

    if (!$stmt->execute()) {
        die("Error al registrar el visitante: " . $stmt->error);
    }

    $id_visitante = $conexion->insert_id;
}


/* =========================================
   REGISTRAR VISITA
========================================= */

$sql = "
    INSERT INTO visitas
    (
        id_visitante,
        id_asunto,
        prioridad,
        estado,
        consulta,
        registrado_at
    )
    VALUES
    (
        ?,
        ?,
        ?,
        'REGISTRADA',
        ?,
        CURRENT_TIMESTAMP
    )
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error al preparar el registro de la visita.");
}

$stmt->bind_param(
    "iiss",
    $id_visitante,
    $id_asunto,
    $prioridad,
    $consulta
);

if (!$stmt->execute()) {
    die("Error al registrar la visita: " . $stmt->error);
}

$id_visita = $conexion->insert_id;


/* =========================================
   REGISTRO EXITOSO
========================================= */

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Senati ETI | Visita registrada</title>

    <link
        rel="stylesheet"
        href="../Css/estilo.css"
    >

</head>

<body>

    <header class="encabezado">

        <div class="logo">

            <div class="logo-icono">
                +
            </div>

            <div>

                <h1>
                    Senati ETI
                </h1>

                <span>
                    Sistema de Gestión de Visitas
                </span>

            </div>

        </div>

    </header>


    <main class="contenido">

        <section class="tarjeta">

            <div class="icono-principal">
                ✅
            </div>

            <h2>
                ¡Visita registrada!
            </h2>

            <p class="descripcion">

                Tu visita ha sido registrada correctamente.

            </p>

            <p style="margin-bottom: 10px;">

                <strong>
                    Número de atención:
                </strong>

                #<?php echo $id_visita; ?>

            </p>

            <p style="margin-bottom: 20px;">

                Por favor, espera a ser atendido.

            </p>


            <a
                href="../index.html"
                class="btn-acceso"
            >

                🏠

                <span>
                    Volver al inicio
                </span>

            </a>

        </section>

    </main>


    <footer class="pie">

        <p>
            Senati ETI © 2026
        </p>

        <span>
            Sistema de Gestión de Visitas
        </span>

    </footer>

</body>

</html>