<?php
const DEBUG_MODE = false;

require_once "cors.php";

require_once("../datos_conexion.php");

// Función para traducir meses
function translateMonthNames($month) {
    $monthNames = [
        'January'   => 'Enero',
        'February'  => 'Febrero',
        'March'     => 'Marzo',
        'April'     => 'Abril',
        'May'       => 'Mayo',
        'June'      => 'Junio',
        'July'      => 'Julio',
        'August'    => 'Agosto',
        'September' => 'Septiembre',
        'October'   => 'Octubre',
        'November'  => 'Noviembre',
        'December'  => 'Diciembre'
    ];
    return $monthNames[$month] ?? $month;
}

$mysqli = new mysqli($host, $user, $pass, $database);
if ($mysqli->connect_error) {
    echo json_encode(["error" => "Conexión fallida: " . $mysqli->connect_error]);
    exit();
}
$mysqli->set_charset("utf8");

// Parámetros por defecto en modo DEBUG
if (DEBUG_MODE) {
    $input = [
        "estudiante" => 1059703541,
        "periodo"    => "UNO",
        "asignatura"    => "QUIMICA"
    ];
} else {
    $input = json_decode(file_get_contents("php://input"), true);
}

// Validar campos obligatorios
$required = ['estudiante', 'asignatura', 'periodo'];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        echo json_encode(["error" => "Falta el campo: $field"]);
        exit();
    }
}

$estudiante = (int)$input['estudiante'];
$materia    = $input['asignatura'];
$periodo    = $input['periodo'];
$anio_actual = date("Y");

// Construir consulta
    $sql = "
    SELECT 
        i.ind,
        i.fecha,
        DATE_FORMAT(i.fecha, '%M') AS mes,
        DATE_FORMAT(i.fecha, '%d') AS dia,
        i.horas,
        i.hora_clase,
        i.detalle,
        e.motivo AS excusa_motivo
    FROM inasistencia i
    LEFT JOIN excusas e ON i.estudiante = e.estudiante AND i.fecha = e.fecha
    WHERE i.estudiante = ?
      AND i.materia = ?
      AND i.year = ?
";
$params = [$estudiante, $materia, $anio_actual];
$types = "iss";

if ($periodo !== "TODOS") {
    $sql .= " AND i.periodo = ?";
    $params[] = $periodo;
    $types .= "s";
}

$sql .= " ORDER BY fecha DESC";

// Depuración
if (DEBUG_MODE) {
    error_log("Consulta SQL: " . $sql);
    error_log("Parámetros: " . json_encode($params));
}

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Error en la consulta: " . $mysqli->error]);
    exit();
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$datos = [];
while ($row = $result->fetch_assoc()) {
    $row["fechac"] = $row["fecha"]; // fecha original en formato YYYY-MM-DD
    $row["fecha"] = translateMonthNames($row["mes"]) . ' ' . ltrim($row["dia"], '0'); // Ej: "Marzo 5"
    $datos[] = $row;
}

echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

$stmt->close();
$mysqli->close();
?>