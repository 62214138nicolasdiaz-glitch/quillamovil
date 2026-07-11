<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nuevoEstado = isset($_POST['estado']) ? trim($_POST['estado']) : '';

    if ($id <= 0 || empty($nuevoEstado)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }

    $nuevoEstado = mb_strtolower($nuevoEstado, 'UTF-8');
    $estadosPermitidos = ['activo', 'en camino', 'ocupado'];
    if (!in_array($nuevoEstado, $estadosPermitidos, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Estado no permitido']);
        exit;
    }

    $query = "UPDATE motocarga SET estado = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error de preparación de consulta']);
        exit;
    }

    mysqli_stmt_bind_param($stmt, 'si', $nuevoEstado, $id);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>