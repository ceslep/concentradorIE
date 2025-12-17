<?php

require_once "cors.php";
require_once("../datos_conexion.php");
require_once "Database.php";
$db = Database::getInstance();
$mysqli = $db->getConnection();

$datos = json_decode(file_get_contents("php://input"));


$sql = "select distinct year from estugrupos where year<>0 order by year desc";

$result = $mysqli->query($sql);

$datos=$result->fetch_all(MYSQLI_ASSOC);




echo json_encode($datos);
