<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

include 'database.php';
header('Content-Type: application/json');

$result = mysqli_query($conn, "SELECT estado FROM toro");

$activo = 0; $camino = 0; $ocupado = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $e = mb_strtolower(trim($row['estado']), 'UTF-8');
    if ($e === 'activo')              $activo++;
    elseif (strpos($e, 'camino') !== false) $camino++;
    else                              $ocupado++;
}

echo json_encode([
    'activo'  => $activo,
    'camino'  => $camino,
    'ocupado' => $ocupado,
]);