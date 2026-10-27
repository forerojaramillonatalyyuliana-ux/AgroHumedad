<?php

$servidor = "localhost";
$usuario = "root";
$password = "";
$base_datos = "sistema_de_riesgo";

// Crear conexión
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar caracteres
$conexion->set_charset("utf8");

?>