<?php
session_start();
header('Content-Type: application/json');

// Verificar si es conductor logueado
if (isset($_SESSION['conductor'])) {
    include 'database.php';
    $conductorId = $_SESSION['conductor']['id'] ?? null;
    
    if ($conductorId) {
        $result = mysqli_query($conn, "SELECT estado FROM conductor WHERE id = '" . (int)$conductorId . "' LIMIT 1");
        if ($result && $row = mysqli_fetch_assoc($result)) {
            echo json_encode(['estado' => $row['estado'] ?? 'activo', 'tipo' => 'conductor']);
            exit;
        }
    }
    echo json_encode(['estado' => 'activo', 'tipo' => 'conductor']);
    exit;
}

// Si no, buscar en tabla toro (si el ID coincide)
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

include 'database.php';
$userId = $_SESSION['user']['id'] ?? null;

// Buscar si el admin está asociado a algún torito
if ($userId) {
    $result = mysqli_query($conn, "SELECT estado FROM toro WHERE id = '" . (int)$userId . "' LIMIT 1");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        echo json_encode(['estado' => $row['estado'] ?? 'activo', 'tipo' => 'toro']);
        exit;
    }
}

// Si es admin sin estado específico, usar azul (observador)
echo json_encode(['estado' => 'observador', 'tipo' => 'admin']);
