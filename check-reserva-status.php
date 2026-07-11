<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once 'database.php';
require_once 'functions.php';

header('Content-Type: application/json');

if (!isLogged()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if (!isset($_SESSION['pending_reserva_id']) || !intval($_SESSION['pending_reserva_id'])) {
    echo json_encode(['success' => true, 'no_pending' => true]);
    exit;
}

$pendingReservaId = intval($_SESSION['pending_reserva_id']);

$stmt = mysqli_prepare($conn, "SELECT status FROM reservas WHERE id = ? LIMIT 1");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error interno']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $pendingReservaId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $status);
$found = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$found) {
    echo json_encode(['success' => true, 'no_pending' => true]);
    exit;
}

$response = ['success' => true, 'status' => $status];

if ($status === 'Confirmada') {
    unset($_SESSION['pending_reserva_id']);
}

echo json_encode($response);
