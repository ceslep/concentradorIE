<?php
require_once 'cors.php';
require_once 'Database.php';

/**
 * GOOGLE_LOGIN.PHP
 * Procesa el inicio de sesión mediante Google (ID Token).
 */

$input = json_decode(file_get_contents("php://input"), true);
$credential = $input['credential'] ?? '';

if (empty($credential)) {
    echo json_encode(['success' => false, 'message' => 'Token de Google no proporcionado']);
    exit;
}

try {
    // Verificar el token con Google API
    // En un entorno de producción ideal usaríamos la librería de Google para PHP.
    // Como alternativa ligera, verificaremos el token contra el endpoint de Google.
    $verify_url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $credential;
    $response = file_get_contents($verify_url);
    
    if ($response === false) {
        echo json_encode(['success' => false, 'message' => 'Error al validar token con Google']);
        exit;
    }

    $payload = json_decode($response, true);

    if (isset($payload['error'])) {
        echo json_encode(['success' => false, 'message' => 'Token de Google inválido: ' . $payload['error_description']]);
        exit;
    }

    // El correo electrónico del usuario está en $payload['email']
    $email = $payload['email'];

    $db = Database::getInstance();
    // Buscamos al docente por su correo electrónico
    $row = $db->fetchOne("SELECT identificacion, nombres, correo FROM docentes WHERE correo = ? AND activo = 1", "s", [$email]);

    if ($row) {
        // Usuario encontrado y activo
        echo json_encode([
            'success' => true,
            'user' => [
                'identificacion' => $row['identificacion'],
                'nombres' => $row['nombres'],
                'correo' => $row['correo']
            ]
        ]);
    } else {
        // El correo no está registrado o el docente no está activo
        echo json_encode([
            'success' => false, 
            'message' => 'El correo ' . $email . ' no está registrado como docente autorizado o la cuenta está inactiva.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>
