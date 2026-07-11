<?php
session_start();
require "functions.php";
redirectIfNotLogged();
require "database.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar-reserva.php');
    exit();
}

$id = (int)$_GET['id'];

// Obtener datos de la reserva
$sql = "SELECT conductor_nombre, conductor_apellido, conductor_telefono FROM reservas WHERE id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    header('Location: listar-reserva.php');
    exit();
}
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $conductor_nombre, $conductor_apellido, $conductor_telefono);
$reservaExiste = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ($reservaExiste) {
    // Eliminar reserva
    $deleteSql = "DELETE FROM reservas WHERE id = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteSql);
    if ($deleteStmt) {
        mysqli_stmt_bind_param($deleteStmt, 'i', $id);
        mysqli_stmt_execute($deleteStmt);
        mysqli_stmt_close($deleteStmt);

        // Recalculamos el estado de los conductores después de cancelar la reserva
        syncConductorEstado($conn);
    }
}

header('Location: listar-reserva.php');
exit();
