<?php
$host = "localhost";
$dbname = "php_mysql_biblioteca";
$usuario = "root";
$contraseña = "";

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname", $usuario, $contraseña); // Conexion a la db
} catch (Exception $error) {
    echo $error->getMessage();
}
