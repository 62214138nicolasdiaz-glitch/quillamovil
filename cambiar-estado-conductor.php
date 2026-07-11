<?php
session_start();
require 'database.php';

// Verificar que el conductor esté autenticado
if (!isset($_SESSION['conductor'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

// Solo aceptar POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Obtener el JSON del request
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['estado'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Estado no proporcionado']);
    exit;
}

$nuevoEstado = trim($input['estado']);
$conductor = $_SESSION['conductor'];
$conductorId = intval($conductor['id']);

// Validar estado
$estadosValidos = ['Activo', 'Ocupado'];
if (!in_array($nuevoEstado, $estadosValidos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Estado inválido']);
    exit;
}

// Actualizar estado en la base de datos
$updateSql = "UPDATE conductor SET estado = ? WHERE id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $updateSql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error en la preparación de la consulta']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'si', $nuevoEstado, $conductorId);
$ejecutado = mysqli_stmt_execute($stmt);
$afectadas = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if ($ejecutado && $afectadas > 0) {
    // Actualizar la sesión
    $_SESSION['conductor']['estado'] = $nuevoEstado;
    
    // Log de cambio de estado (opcional)
    $logMensaje = date('Y-m-d H:i:s') . " - Conductor ID={$conductorId} Nombre={$conductor['nombre']} {$conductor['apellido']} cambió estado a: {$nuevoEstado}\n";
    @file_put_contents(__DIR__ . '/conductor_estado.log', $logMensaje, FILE_APPEND);
    
    echo json_encode(['success' => true, 'estado' => $nuevoEstado]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'No se pudo actualizar el estado']);
}
?>
