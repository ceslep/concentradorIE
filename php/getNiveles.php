<?php
require_once "cors.php";

// Conexi贸n a BD
require_once "../datos_conexion.php";
    $mysqli = new mysqli($host,$user,$pass,$database);
    $datos=(Object)json_decode(file_get_contents("php://input"));
    $sql="select distinct nivel from estugrupos where asignacion='$datos->asignacion'"; 
    if ($datos->year==="")
    $sql.=" and year=year(curdate())";
    else
    $sql.=" and year='$datos->year'";
    $sql.=" order by nivel";
   // echo json_encode($sql);exit(0);
    $result=$mysqli->query($sql);
    $resultados=$result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($resultados);
   
    $mysqli->close();