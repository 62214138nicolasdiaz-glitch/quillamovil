<?php
header('Content-Type: application/json');

$dni = isset($_GET['dni']) ? trim($_GET['dni']) : '';

if (!preg_match('/^\d{8}$/', $dni)) {
    echo json_encode(['error' => 'DNI inválido']);
    exit;
}

$token = '63ab88b50257743deeb2e31c4acb39b344bb101ace450f8cb2c71dd119ad365e'; // 👈 Tu token de apiperu.dev

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => 'https://apiperu.dev/api/dni',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => 'POST',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_POSTFIELDS     => json_encode(['dni' => $dni]),
    CURLOPT_HTTPHEADER     => [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ],
]);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);

if (isset($data['data'])) {
    echo json_encode([
        'nombre'   => $data['data']['nombres'],
        'apellido' => $data['data']['apellido_paterno'] . ' ' . $data['data']['apellido_materno'],
    ]);
} else {
    echo json_encode(['error' => 'No encontrado']);
}