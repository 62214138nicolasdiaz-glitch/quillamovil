<?php
session_start();
require "database.php";
require "functions.php";

// Obtener credenciales del formulario
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Obtener la página de redirección (si existe)
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? 'index.php';
if ($redirect === '') {
    $redirect = 'index.php';
}

$redirectParam = $redirect !== '' ? '&redirect=' . urlencode($redirect) : '';
if ($username === '' || $password === '') {
    header("Location: login.php?error=empty$redirectParam");
    exit;
}

$user = login($conn, $username, $password);
if ($user !== false) {
    $_SESSION['user'] = [
        'username' => $user['username'],
        'id' => $user['id'],
    ];
    
    // Redirigir según el parámetro
    if ($redirect === 'index.php') {
        header("Location: index.php");
    } else {
        header("Location: " . htmlspecialchars($redirect));
    }
    exit;
}

$usernameEscaped = mysqli_real_escape_string($conn, $username);
$checkSql = "SELECT id FROM users WHERE username = '$usernameEscaped' LIMIT 1";
$checkResult = mysqli_query($conn, $checkSql);
$errorType = 'credentials';
if ($checkResult && mysqli_num_rows($checkResult) === 1) {
    $errorType = 'wrong_password';
} else {
    $errorType = 'no_account';
}

$redirectParam = $redirect !== '' ? '&redirect=' . urlencode($redirect) : '';
header("Location: login.php?error=$errorType$redirectParam");
exit;
?>
