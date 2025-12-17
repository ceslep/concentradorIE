<?php
require_once "cors.php";

// Conexión a BD
// Conexión a BD
require_once "Database.php";
    $db = Database::getInstance();
    $mysqli = $db->getConnection();
    $datos=(Object)json_decode(file_get_contents("php://input"));
    $sql='Select ind,nombre,if(curdate()>=fechainicial and curdate()<=fechafinal,"selected","") as selected,nombre from periodos';
   
    $result=$mysqli->query($sql);
    $resultados=[];
    while($resultado=$result->fetch_assoc()) $resultados[]=$resultado;
    echo json_encode($resultados);
    $result->free();
    // $mysqli->close();