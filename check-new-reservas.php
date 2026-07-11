<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once 'database.php';

// Responder solo si el conductor está logueado
if (!isset($_SESSION['conductor'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');

$conductor = $_SESSION['conductor'];
$nombre = mysqli_real_escape_string($conn, $conductor['nombre']);
$apellido = mysqli_real_escape_string($conn, $conductor['apellido']);

// Contar reservas pendientes
$sql = "SELECT COUNT(*) as total FROM reservas WHERE conductor_nombre = '$nombre' AND conductor_apellido = '$apellido' AND status = 'Pendiente'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

echo json_encode([
    'success' => true,
    'pendientes' => (int)$row['total']
]);
?>
