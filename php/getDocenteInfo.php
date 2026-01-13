<?php
require_once("cors.php");
require_once("Database.php");

/**
 * API: getDocenteInfo.php
 * Propósito: Retornar la información detallada de un docente específico para el módulo de registro.
 * Payload: { "docente": "identificacion" }
 */

// Establecer conexión
$db = Database::getInstance();
$mysqli = $db->getConnection();

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Leer y decodificar entrada JSON
$input = file_get_contents("php://input");
$datos = json_decode($input);

// Validar entrada JSON
if (!$datos || !isset($datos->docente)) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido. Se requiere "docente"']);
    exit;
}

$identificacion = $datos->docente;

// Consulta preparada para obtener todos los campos requeridos según excons.json
$sql = "SELECT 
            identificacion, 
            nombres, 
            correo, 
            asignacion, 
            activo, 
            pass, 
            codigoTemporal, 
            solocitaCodigo, 
            verEstudiantes, 
            maestra, 
            banda, 
            acceso_total, 
            idn, 
            soloexcusas, 
            fechaactualizacion, 
            clave_acceso
        FROM docentes 
        WHERE identificacion = ? 
        LIMIT 1";

$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar la consulta: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param("s", $identificacion);
$stmt->execute();
$resultado = $stmt->get_result();

if ($row = $resultado->fetch_assoc()) {
    // Retornar el objeto del docente
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Docente no encontrado']);
}

$stmt->close();
?>
