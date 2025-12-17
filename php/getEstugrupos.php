<?php
require_once("cors.php");
require_once("Database.php");

// Establecer conexión con la base de datos
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

// Validar que los datos JSON sean válidos
if ($datos === null && $input !== '') {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    // $mysqli->close();
    exit;
}

// Actualizar edades (mejor usar una constante en lugar de expresión mágica)
$sqlEdad = "UPDATE estugrupos SET edad = FLOOR(DATEDIFF(CURDATE(), fecnac) / 365.242199)";
if (!$mysqli->query($sqlEdad)) {
    error_log("Error al actualizar edades: " . $mysqli->error);
}

// Preparar y ejecutar la consulta principal
if (isset($datos->estudiante)) {
    $sql = "SELECT *, sedes.sede, estugrupos.ind AS ind
            FROM estugrupos
            INNER JOIN sedes ON estugrupos.asignacion = sedes.ind 
            WHERE estudiante = ?
            ORDER BY year DESC, asignacion, nivel, numero, nombres
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al preparar la consulta']);
        // $mysqli->close();
        exit;
    }
    $stmt->bind_param('s', $datos->estudiante);
} elseif (isset($datos->year)) {
    $sql = "SELECT *, sedes.sede, estugrupos.ind AS ind
            FROM estugrupos
            INNER JOIN sedes ON estugrupos.asignacion = sedes.ind 
            WHERE year = ?
            ORDER BY asignacion, nivel, numero, nombres";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al preparar la consulta']);
        // $mysqli->close();
        exit;
    }
    $stmt->bind_param('i', $datos->year);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros: se requiere "estudiante" o "year"']);
    // $mysqli->close();
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
echo json_encode($result ? $result->fetch_all(MYSQLI_ASSOC) : []);
$stmt->close();
// $mysqli->close();