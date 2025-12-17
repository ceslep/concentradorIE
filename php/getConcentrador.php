<?php
// Orígenes permitidos

// Orígenes permitidos
require_once 'cors.php';
require_once 'Database.php';

// Conexión a BD
// (Handled by Database class)

$db = Database::getInstance();

// 📥 Datos recibidos
$datos = json_decode(file_get_contents("php://input"));
$nivel      = $datos->nivel ?? null;
$numero     = $datos->numero ?? null;
$periodo    = $datos->periodo ?? null;
$asignacion = $datos->Asignacion ?? null;
$YEAR       = $datos->year ?? null;

// ----------------------
// 1. Fetch todas las notas
// ----------------------
$all_grades = [];
$stmt_grades = $db->query("
    SELECT estudiante, asignatura, valoracion, periodo 
    FROM notas 
    WHERE year = ? 
      AND estudiante IN (
        SELECT estudiante 
        FROM estugrupos 
        WHERE nivel = ? AND numero = ? AND asignacion = ? AND year = ?
      )
", "siiss", [$YEAR, $nivel, $numero, $asignacion, $YEAR]);

$result_grades = $stmt_grades->get_result();

while ($row = $result_grades->fetch_assoc()) {
    $all_grades[$row['estudiante']][$row['asignatura']][$row['periodo']] = $row['valoracion'];
}
$stmt_grades->close();

// ----------------------
// 2. Promedios por asignatura
// ----------------------
$stmt_avg_grades = $db->query("
    SELECT estudiante, asignatura, AVG(valoracion) as valoracion 
    FROM notas 
    WHERE year = ? 
      AND estudiante IN (
        SELECT estudiante 
        FROM estugrupos 
        WHERE nivel = ? AND numero = ? AND asignacion = ? AND year = ?
      )
    GROUP BY estudiante, asignatura
", "siiss", [$YEAR, $nivel, $numero, $asignacion, $YEAR]);

$result_avg_grades = $stmt_avg_grades->get_result();

while ($row = $result_avg_grades->fetch_assoc()) {
    $all_grades[$row['estudiante']][$row['asignatura']]['DEF'] = $row['valoracion'];
}
$stmt_avg_grades->close();

// ----------------------
// 3. Consulta asignaturas
// ----------------------
$sql1 = "
    SELECT 
        asignacion_asignaturas.asignatura, 
        asignacion_asignaturas.abreviatura, 
        asignacion_asignaturas.docente, 
        asignacion_asignaturas.materia, 
        IF(parametros_informe.orden IS NULL, 200, parametros_informe.orden) AS ordenar 
    FROM asignacion_asignaturas
    LEFT JOIN parametros_informe 
        ON asignacion_asignaturas.asignatura = parametros_informe.codigo_materia
    INNER JOIN docentes 
        ON asignacion_asignaturas.docente = docentes.identificacion
    WHERE asignacion_asignaturas.nivel = ? 
      AND asignacion_asignaturas.numero = ? 
      AND asignacion = ? 
      AND asignacion_asignaturas.year = ? 
      AND parametros_informe.year = ?
";

$current_year = date('Y');
if ($datos->year === $current_year) {
    $sql1 .= " AND asignacion_asignaturas.visible = 'S'";
}
$sql1 .= " ORDER BY ordenar";

$stmt_asignaturas = $db->query($sql1, "ssiss", [$nivel, $numero, $asignacion, $datos->year, $datos->year]);
$rasignaturas = $stmt_asignaturas->get_result();

// ----------------------
// 4. Construcción HTML
// ----------------------
$html = '<input placeholder="Buscar Estudiante" type="search" id="searchConcentrador" class="form-control searchConcentrador" autofocus oninput=filterTable(this.value)>';
$html .= "<div class='table-responsive overflow-y-hidden'><table class='table table-bordered table-hover table-striped tableconcentrador$datos->tipo' border='1' id='tableconcentrador$datos->tipo'>";
$html .= "<thead>";
$html .= "<th style='text-align:center;min-width:200px;vertical-align:middle;'>Nombres Estudiantes</th>";

$array_asignaturas = [];
while ($asignaturas = $rasignaturas->fetch_assoc()) {
    $html .= "<th style='padding:0;min-width:55px;text-align:center;vertical-align:middle;'>";
    $html .= sprintf("<div><span id='conc_%s' data-docente='%s'>%s</span></div>", 
        $asignaturas['docente'], 
        $asignaturas['docente'], 
        $asignaturas['asignatura']
    );
    $html .= sprintf("<br/><div id='spor_%s'></div></th>", $asignaturas['materia']);
    $array_asignaturas[] = $asignaturas;
}
$html .= "</thead>";

// ----------------------
// 5. Estudiantes
// ----------------------
$sql2 = "
    SELECT estudiante, nombres, nombres AS nombres2 
    FROM estugrupos 
    WHERE nivel = ? AND numero = ? AND asignacion = ? AND year = ?
";

if (isset($datos->activos) && $datos->activos) {
    $sql2 .= " AND activo = 'S'";
}
if ($datos->year === $current_year) {
    $sql2 .= " AND activo = 'S'";
}
$sql2 .= " ORDER BY nombres2";

$stmt_estudiantes = $db->query($sql2, "ssis", [$nivel, $numero, $asignacion, $datos->year]);
$restudiantes = $stmt_estudiantes->get_result();

$haye = false;
while ($datos = $restudiantes->fetch_assoc()) {
    $haye = true;
    $nnombres   = $datos['nombres'];
    $datoos     = json_encode($datos);
    $Estudiante = $datos['estudiante'];

    $html .= "<tr><td><a href='#!' data-datos='$datoos' class='text-decoration-none'>$nnombres</a></td>";

    foreach ($array_asignaturas as $asignatura) {
        $Asignatura = $asignatura['asignatura'];

        $student_grades = [];
        if (isset($all_grades[$Estudiante][$Asignatura])) {
            foreach (['UNO','DOS','TRES','CUATRO','DEF'] as $p) {
                if (isset($all_grades[$Estudiante][$Asignatura][$p])) {
                    $student_grades[] = [
                        'valoracion' => $all_grades[$Estudiante][$Asignatura][$p], 
                        'periodo' => $p
                    ];
                }
            }
        }
        $periodos = json_encode($student_grades);

        $html .= "<td data-periodos='$periodos' data-asignatura='$Asignatura' data-nombres='$nnombres'></td>";
    }
    $html .= "</tr>";
}

$html .= "</table></div>";

// ----------------------
// 6. Respuesta
// ----------------------
echo json_encode([
    "html" => $haye ? $html : ""
]);

$stmt_asignaturas->close();
$stmt_estudiantes->close();
// $db->close() is not needed/exposed, connection persists or closes on script end

