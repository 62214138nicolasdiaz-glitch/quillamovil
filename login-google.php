<?php
session_start();

// Configuración de Google OAuth
$GOOGLE_CLIENT_ID = getenv('GOOGLE_CLIENT_ID');
$GOOGLE_CLIENT_SECRET = getenv('GOOGLE_CLIENT_SECRET');
$GOOGLE_REDIRECT_URI = getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/semestre-6/quillamovil/login-google.php';

// Si no hay código de autenticación, redirigir a Google
if (!isset($_GET['code'])) {
    $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $GOOGLE_CLIENT_ID,
        'redirect_uri' => $GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'online',
        'prompt' => 'consent'
    ]);
    header('Location: ' . $auth_url);
    exit;
}

// Intercambiar código por token
$code = $_GET['code'];
$token_response = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query([
            'code' => $code,
            'client_id' => $GOOGLE_CLIENT_ID,
            'client_secret' => $GOOGLE_CLIENT_SECRET,
            'redirect_uri' => $GOOGLE_REDIRECT_URI,
            'grant_type' => 'authorization_code'
        ])
    ]
]));

$token_data = json_decode($token_response, true);

if (isset($token_data['error'])) {
    die('Error al obtener el token: ' . $token_data['error_description']);
}

// Obtener información del usuario
$access_token = $token_data['access_token'];
$user_response = file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $access_token);
$user_info = json_decode($user_response, true);

if (isset($user_info['error'])) {
    die('Error al obtener información del usuario');
}

// Guardar datos en sesión
$_SESSION['user'] = [
    'id' => $user_info['id'],
    'username' => $user_info['email'],
    'email' => $user_info['email'],
    'name' => $user_info['name'],
    'picture' => $user_info['picture'],
    'oauth_provider' => 'google'
];

// Redirigir al dashboard
header('Location: index.php');
exit;