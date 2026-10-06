<?php

$servidor = "sql304.infinityfree.com";
$usuario = "if0_42983342";
$password = "TU_CONTRASEÑA_DE_MYSQL";
$base_datos = "if0_42983342_senati_eti";
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