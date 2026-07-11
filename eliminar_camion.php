<?php
session_start();
require "functions.php";
redirectIfNotLogged();
require "database.php";

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: listar_camion.php');
    exit();
}

$id = (int)$_GET['id'];
$sql = "DELETE FROM camion WHERE id = $id";
mysqli_query($conn, $sql);
header('Location: listar_camion.php');
exit();
