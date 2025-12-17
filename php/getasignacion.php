<?php


require_once 'cors.php';

// Conexi贸n a BD
// Conexión a BD
require_once "Database.php";
$db = Database::getInstance();
$mysqli = $db->getConnection();



$sql = "Select * from  sedes";
$result = $mysqli->query($sql);
$datos = [];
while ($dato = $result->fetch_assoc()) {
    $ind = $dato['ind'];
    $sql = "
        select distinct nivel,numero from estugrupos
        where asignacion='$ind' and year=year(curdate())
        order by nivel,numero;
        ";
    $grados = ($mysqli->query($sql))->fetch_all(MYSQLI_ASSOC);
    $dato['grados'] = $grados;
    $datos[] = $dato;
}
echo json_encode($datos);
$result->free();
// $mysqli->close();
