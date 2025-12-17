<?php

require_once 'cors.php';
require_once 'Database.php';

$input = json_decode(file_get_contents("php://input"), true);

$identificacion = $input['identificacion'] ?? '';
$clave_acceso = $input['clave_acceso'] ?? '';

if (empty($identificacion) || empty($clave_acceso)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

try {
    $db = Database::getInstance();
    $row = $db->fetchOne("SELECT identificacion, nombres, clave_acceso FROM docentes WHERE identificacion = ?", "s", [$identificacion]);

    if ($row) {
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
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    // error_log($e->getMessage()); // Optional: log error
}

?>
