<?php

session_start();

require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.html");
    exit;
}

$usuario = trim($_POST["usuario"] ?? "");
$password = $_POST["password"] ?? "";
$rol = strtoupper(trim($_POST["rol"] ?? ""));

if ($usuario === "" || $password === "") {
    die("Debe ingresar usuario y contraseña.");
}

if ($rol !== "ADMIN" && $rol !== "EMPLEADO") {
    die("Rol no válido.");
}

$sql = "SELECT id_usuario, nombre_usuario, correo, password, rol, estado
        FROM usuarios
        WHERE nombre_usuario = ?
        AND rol = ?
        AND estado = 1
        LIMIT 1";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . $conexion->error);
}

$stmt->bind_param("ss", $usuario, $rol);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    die("Usuario, contraseña o rol incorrectos.");
}

$usuarioBD = $resultado->fetch_assoc();

if ($password !== $usuarioBD["password"]) {
    die("Usuario, contraseña o rol incorrectos.");
}

$_SESSION["id_usuario"] = $usuarioBD["id_usuario"];
$_SESSION["nombre_usuario"] = $usuarioBD["nombre_usuario"];
$_SESSION["correo"] = $usuarioBD["correo"];
$_SESSION["rol"] = $usuarioBD["rol"];

if ($usuarioBD["rol"] === "ADMIN") {
    header("Location: ../admin.html");
    exit;
}

if ($usuarioBD["rol"] === "EMPLEADO") {
    header("Location: ../empleado.html");
    exit;
}

die("No se pudo determinar el acceso.");

?>