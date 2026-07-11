<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

include 'database.php';
header('Content-Type: application/json');

$result = mysqli_query($conn,
    "SELECT id, nombre, apellido, estado, latitud, longitud, telefono
     FROM toro
     WHERE latitud IS NOT NULL AND longitud IS NOT NULL
       AND latitud != '' AND longitud != ''"
);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        'id'       => (int)$row['id'],
        'nombre'   => $row['nombre'] . ' ' . $row['apellido'],
        'estado'   => $row['estado'],
        'lat'      => (float)$row['latitud'],
        'lng'      => (float)$row['longitud'],
        'telefono' => preg_replace('/\D+/', '', $row['telefono']),
    ];
}

echo json_encode($data);