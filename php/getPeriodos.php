<?php

require_once "cors.php";

require("../datos_conexion.php");


$mysqli = new mysqli($host, $user, $pass, $database);
$mysqli->query("SET NAMES utf8");
$mysqli->set_charset('utf8');

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Conexión fallida: " . $mysqli->connect_error]);
    exit;
}

$sql = "SELECT ind, nombre, 
               IF(CURDATE() >= fechainicial AND CURDATE() <= fechafinal, 'selected', '') AS selected,
               nombre AS periodo 
        FROM periodos";

$result = $mysqli->query($sql);
$resultados = [];

if ($result) {
    while ($fila = $result->fetch_assoc()) {
        $resultados[] = $fila;
    }
    $result->free();
}

$mysqli->close();

echo json_encode($resultados);