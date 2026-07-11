<?php
session_start();
require "functions.php";
redirectIfNotLogged("listar.php");
require "database.php";

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = (int)$_GET['id'];

$sql = "DELETE FROM toro WHERE id = $id";
if (mysqli_query($conn, $sql)) {
    header('Location: listar.php');
    exit();
}

// En caso de error, redirige con mensaje opcional (puede ajustarse si se desea mostrar errores)
header('Location: listar.php');
exit();
