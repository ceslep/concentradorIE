<?php
/**
 * Script de prueba para getInfoDocentes.php
 * Simula una petición POST con un payload JSON
 */

$url = "https://app.iedeoccidente.com/consex2/getInfoDocentes.php";
$data = array("x" => "x");
$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true
    )
);

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "Error al realizar la petición.\n";
} else {
    // Intentar formatear el JSON para mejor legibilidad
    $json = json_decode($result);
    if ($json !== null) {
        echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo "Respuesta no es JSON válido:\n";
        echo $result;
    }
}
?>
