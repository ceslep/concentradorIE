<?php
require_once 'cors.php';
require_once "../datos_conexion.php";

$mysqli = new mysqli($host, $user, $pass, $database);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->query("SET NAMES utf8");
$mysqli->set_charset('utf8');

$input = json_decode(file_get_contents("php://input"), true);

$identificacion = $input['identificacion'] ?? '';
$clave_acceso = $input['clave_acceso'] ?? '';

if (empty($identificacion) || empty($clave_acceso)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

// Prepare statement to prevent SQL injection
$stmt = $mysqli->prepare("SELECT identificacion, nombres, clave_acceso FROM docentes WHERE identificacion = ?");
$stmt->bind_param("s", $identificacion);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Verify password using SHA-256
    if (hash('sha256', $clave_acceso) === $row['clave_acceso']) {
        echo json_encode([
            'success' => true,
            'user' => [
                'identificacion' => $row['identificacion'],
                'nombres' => $row['nombres']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
}

$stmt->close();
$mysqli->close();
?>
