<?php
session_start();
require "functions.php";
redirectIfNotLogged("listar_motocarga.php");
require "database.php";

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: listar_motocarga.php');
    exit();
}

$id = (int)$_GET['id'];
$sql = "DELETE FROM motocarga WHERE id = $id";
mysqli_query($conn, $sql);
header('Location: listar_motocarga.php');
exit();
