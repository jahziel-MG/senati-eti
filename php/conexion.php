<?php

$servidor = "sql205.infinityfree.com";
$usuario = "if0_43104635";
$password = "";
$base_datos = "if0_43104635_sistema_visitas";
$puerto = 3306;

$conexion = new mysqli(
    $servidor,
    $usuario,
    $password,
    $base_datos,
    $puerto
);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");

?>