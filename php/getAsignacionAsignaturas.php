<?php
include 'cors.php';
include 'Database.php';

try {
    $db = Database::getInstance();
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

    // Fetch asignacion_asignaturas with teacher names
    $sql = "SELECT aa.*, d.nombres as docente_nombre 
            FROM asignacion_asignaturas aa 
            LEFT JOIN docentes d ON aa.docente = d.identificacion 
            WHERE aa.year = ? 
            ORDER BY CAST(aa.grados AS UNSIGNED), aa.orden";
    $result = $db->fetchAll($sql, 'i', [$year]);

    echo json_encode(['success' => true, 'data' => $result]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
