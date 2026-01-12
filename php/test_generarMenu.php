<?php
/**
 * Script de prueba para generarMenu.php
 * Utiliza la lógica directa del archivo original sin cURL.
 */

require_once("Database.php");

// 1. Simulación de entrada de datos
$payload = json_encode(["docente" => 75081186, "year" => 2025]);
$datos = json_decode($payload);

// 2. Lógica idéntica a generarMenu.php
$startTime = microtime(true);
$error = null;

try {
    $db = Database::getInstance();
    $mysqli = $db->getConnection();

    if ($mysqli->connect_error) {
        throw new Exception('Error de conexión a la base de datos');
    }

    $docente = isset($datos->docente) ? $datos->docente : null;
    $year = isset($datos->year) ? $datos->year : date('Y');

    if (!$docente) {
        throw new Exception('Faltan parámetros: se requiere "docente"');
    }

    // 1. Obtener grados del docente
    $sqlGrados = "SELECT DISTINCT nivel, numero, grados FROM asignacion_asignaturas 
                  WHERE docente = ? AND year = ? 
                  ORDER BY nivel, numero";

    $stmtGrados = $mysqli->prepare($sqlGrados);
    if (!$stmtGrados) {
        throw new Exception('Error al preparar consulta de grados');
    }

    $stmtGrados->bind_param('ss', $docente, $year);
    $stmtGrados->execute();
    $resultGrados = $stmtGrados->get_result();

    $data = [];

    // 2. Preparar consulta de asignaturas
    $sqlAsignaturas = "SELECT a.asignatura 
                       FROM asignacion_asignaturas a
                       INNER JOIN orden_asignaturas o ON o.asignatura = a.asignatura
                       WHERE a.docente = ? AND a.nivel = ? AND a.numero = ? AND a.year = ?
                       ORDER BY o.orden";

    $stmtAsignaturas = $mysqli->prepare($sqlAsignaturas);
    if (!$stmtAsignaturas) {
        throw new Exception('Error al preparar consulta de asignaturas');
    }

    while ($grado = $resultGrados->fetch_assoc()) {
        $nivel = $grado['nivel'];
        $numero = $grado['numero'];
        $gradoA = $grado['grados'];
        
        $stmtAsignaturas->bind_param('ssss', $docente, $nivel, $numero, $year);
        $stmtAsignaturas->execute();
        $resAsig = $stmtAsignaturas->get_result();
        
        $asignaturas = [];
        while ($asignatura = $resAsig->fetch_assoc()) {
            $asignaturas[] = $asignatura;
        }
        
        $data[] = [
            "grado" => $nivel . "-" . $numero,
            "nivel" => $nivel,
            "numero" => $numero,
            "gradoA" => $gradoA,
            "asignaturas" => $asignaturas
        ];
    }

    $httpCode = 200;
    $response = json_encode($data);
    $stmtGrados->close();
    $stmtAsignaturas->close();

} catch (Exception $e) {
    $httpCode = 500;
    $error = $e->getMessage();
    $response = json_encode(['error' => $error]);
}

$endTime = microtime(true);
$executionTime = round(($endTime - $startTime) * 1000, 2);
$jsonFormatted = json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$url = "Lógica Interna (Mocked)";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test: generarMenu.php</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #a855f7;
            --bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text: #f1f5f9;
            --success: #22c55e;
            --error: #ef4444;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.15) 0px, transparent 50%);
            color: var(--text);
            margin: 0;
            padding: 2rem;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .header h1 {
            font-weight: 600;
            font-size: 2.5rem;
            margin: 0;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .grid { grid-template-columns: 1fr; }
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.5rem;
            padding: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .card-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            opacity: 0.8;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-success { background: rgba(34, 197, 94, 0.2); color: var(--success); border: 1px solid var(--success); }
        .status-error { background: rgba(239, 68, 68, 0.2); color: var(--error); border: 1px solid var(--error); }

        .info-item {
            margin-bottom: 1rem;
        }

        .info-label {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.5;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-family: 'Fira Code', monospace;
            font-size: 0.95rem;
        }

        pre {
            background: rgba(0, 0, 0, 0.3);
            padding: 1.5rem;
            border-radius: 1rem;
            overflow-x: auto;
            font-family: 'Fira Code', monospace;
            font-size: 0.9rem;
            margin: 0;
            border: 1px solid rgba(255, 255, 255, 0.05);
            max-height: 600px;
        }

        .json-key { color: #818cf8; }
        .json-string { color: #34d399; }
        .json-number { color: #fbbf24; }
        .json-boolean { color: #f87171; }

        .btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 1rem;
            font-family: inherit;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>API Test Debugger</h1>
            <p style="opacity: 0.6;">Verificando respuesta de <code style="color: var(--primary);">generarMenu.php</code></p>
        </header>

        <div class="grid">
            <div class="card">
                <div class="card-title">Configuración</div>
                
                <div class="info-item">
                    <span class="info-label">Estado HTTP</span>
                    <span class="status-badge <?php echo ($httpCode == 200) ? 'status-success' : 'status-error'; ?>">
                        <?php echo $httpCode; ?> <?php echo ($httpCode == 200) ? 'OK' : 'Error'; ?>
                    </span>
                </div>

                <div class="info-item">
                    <span class="info-label">Endpoint</span>
                    <div class="info-value" style="word-break: break-all;"><?php echo $url; ?></div>
                </div>

                <div class="info-item">
                    <span class="info-label">Payload Enviado</span>
                    <pre style="padding: 0.75rem; font-size: 0.8rem;"><?php echo htmlspecialchars($payload); ?></pre>
                </div>

                <div class="info-item">
                    <span class="info-label">Tiempo de Respuesta</span>
                    <div class="info-value"><?php echo $executionTime; ?> ms</div>
                </div>

                <?php if ($error): ?>
                <div class="info-item">
                    <span class="info-label">Error cURL</span>
                    <div class="info-value" style="color: var(--error);"><?php echo htmlspecialchars($error); ?></div>
                </div>
                <?php endif; ?>

                <a href="" class="btn">Re-ejecutar Test</a>
            </div>

            <div class="card">
                <div class="card-title">Respuesta JSON</div>
                <pre id="json-output"><?php 
                    if ($jsonFormatted) {
                        // Resaltado básico de sintaxis JSON
                        $highlighted = preg_replace(
                            ['/("[^"]*")(\s*:)/', '/: ("[^"]*")/', '/: (-?\d+\.?\d*)/', '/: (true|false|null)/'],
                            ['<span class="json-key">$1</span>$2', ': <span class="json-string">$1</span>', ': <span class="json-number">$1</span>', ': <span class="json-boolean">$1</span>'],
                            htmlspecialchars($jsonFormatted)
                        );
                        echo $highlighted;
                    } else {
                        echo htmlspecialchars($response);
                    }
                ?></pre>
            </div>
        </div>
    </div>
</body>
</html>
