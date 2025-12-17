<?php
require_once 'cors.php';
// Conexión a BD
require_once 'Database.php';

$db = Database::getInstance();

if ($db->getConnection()->connect_error) {
    echo json_encode(["error" => "Error de conexión: " . $db->getConnection()->connect_error]);
    exit();
}


$input = json_decode(file_get_contents("php://input"), true);
$estudianteId = $input['estudianteId'] ?? null;
$year = $input['year'] ?? null; // Get year from input

if (!$estudianteId || !$year) { // Validate both parameters
    echo json_encode(["error" => "ID de estudiante o año no proporcionado."]);
    exit();
}

$sql = "
    SELECT
        ind,
        codigo,
        estudiante,
        nombres,
        asignacion,
        institucion_externa,
        genero,
        fecnac,
        edad,
        nivel,
        grado,
        numero,
        anio,
        pass,
        activo,
        banda,
        HED,
        idacudiente,
        acudiente,
        telefono1,
        telefono2,
        direccion,
        email_estudiante,
        email_acudiente,
        desertor,
        otraInformacion,
        estado,
        year,
        lugar,
        sisben,
        estrato,
        lugarNacimiento,
        lugarExpedicion,
        fechaExpedicion,
        tdei,
        victimaConflicto,
        lugarDesplazamiento,
        fechaDesplazamiento,
        etnia,
        tipoSangre,
        eps,
        padre,
        padreid,
        telefonopadre,
        ocupacionpadre,
        madre,
        madreid,
        telefonomadre,
        ocupacionmadre,
        parentesco,
        discapacidad,
        telefono_acudiente,
        eanterior,
        sede
    FROM estugrupos
    WHERE estudiante = ? AND year = ?
    LIMIT 1;
";

$stmt = $db->getConnection()->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Error al preparar la consulta: " . $db->getConnection()->error]);
    exit();
}

$stmt->bind_param("ss", $estudianteId, $year); // Bind both parameters
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $studentDetails = $result->fetch_assoc();
    echo json_encode($studentDetails, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["error" => "Estudiante no encontrado."]);
}

$stmt->close();
// $db->getConnection()->close();
?>