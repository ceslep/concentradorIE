<?php
require_once "cors.php";
// Conexión a BD
require_once 'Database.php';

$db = Database::getInstance();
$mysqli = $db->getConnection();

if ($mysqli->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $mysqli->connect_error]));
}


$input = file_get_contents("php://input");
$datos = json_decode($input);

if (!$datos) {
    echo json_encode([]);
    exit;
}

// --- Helper Functions for Dynamic Columns ---
function buildDynamicColumns($prefix, $aliasPrefix, $count = 12) {
    $columns = [];
    for ($i = 1; $i <= $count; $i++) {
        if ($prefix === 'nota') {
             // Special case for grades: handle nulls as empty strings or keep value
             $columns[] = "IF(n.nota{$i} IS NULL, ' ', n.nota{$i}) as N{$i}";
        } elseif ($prefix === 'fecha') {
             // Special case for dates if needed, or just standard mapping
             $columns[] = "n.fecha{$i} as fecha{$i}";
        } elseif ($prefix === 'fechaa') {
             $columns[] = "n.fechaa{$i} as fechaa{$i}";
        } else {
             // Generic case (aspecto, porcentaje)
             $columns[] = "n.{$prefix}{$i} as {$prefix}{$i}";
        }
    }
    return implode(", ", $columns);
}

// --- 1. Insert History Log (Prepared Statement) ---
date_default_timezone_set('America/Bogota');
$fecha_actual = date('Y-m-d');
$hora_actual = date('H');
$minuto_actual = date('i');
$year_actual = isset($datos->year) ? $datos->year : date('Y');
$grado_str = $datos->nivel . "-" . $datos->numero;

$sqlLog = "INSERT INTO notas_historico (fecha, docente, asignatura, grado, year, tiempo, hora, minuto) VALUES (?, ?, ?, ?, ?, 'Inicia', ?, ?)";

if ($stmtLog = $mysqli->prepare($sqlLog)) {
    $stmtLog->bind_param("sssssss", $fecha_actual, $datos->docente, $datos->asignatura, $grado_str, $year_actual, $hora_actual, $minuto_actual);
    $stmtLog->execute();
    $stmtLog->close();
}

// --- 2. Main Query (Optimized & Prepared) ---

// Build dynamic parts of the SELECT
$colsNotas = buildDynamicColumns('nota', 'N');
$colsAspectos = buildDynamicColumns('aspecto', 'aspecto');
$colsPorcentajes = buildDynamicColumns('porcentaje', 'porcentaje');
$colsFechaas = buildDynamicColumns('fechaa', 'fechaa');
$colsFechas = buildDynamicColumns('fecha', 'fecha');

$sql = "SELECT 
            e.estudiante, 
            e.nombres as Nombres, 
            n.valoracion as Val,
            $colsNotas,
            $colsAspectos,
            $colsPorcentajes,
            $colsFechaas,
            $colsFechas
        FROM estugrupos e
        LEFT JOIN notas n ON e.estudiante = n.estudiante 
            AND e.year = n.year
            AND n.asignatura = ?
            AND n.periodo = ?
            AND (n.docente = ? OR n.docente IS NULL)
            AND n.grado LIKE ?
        WHERE 
            e.nivel = ? 
            AND e.numero = ? 
            AND e.asignacion = ? 
            AND e.year = ? 
            AND e.activo = 'S'
        ORDER BY e.nombres";

if ($stmt = $mysqli->prepare($sql)) {
    // Prepare params for binding
    // n.grado LIKE ... needs the wildcards added to the param, not the query if possible, or constructed before.
    // Original query used: and notas.grado like '%$datos->nivel-$datos->numero%'
    $gradoLike = "%" . $datos->nivel . "-" . $datos->numero . "%";
    
    // Types: s (asignatura), s (periodo), s (docente), s (gradoLike), s (nivel), s (numero), s (asignacion), s (year)
    // Total 8 strings
    $stmt->bind_param("ssssssss", 
        $datos->asignatura, 
        $datos->periodo, 
        $datos->docente, 
        $gradoLike, 
        $datos->nivel, 
        $datos->numero, 
        $datos->asignacion, 
        $year_actual
    );
    
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($data);
    
    $stmt->close();
} else {
    // Fallback or error handling
    echo json_encode(["error" => $mysqli->error]);
}

// $mysqli->close();
?>