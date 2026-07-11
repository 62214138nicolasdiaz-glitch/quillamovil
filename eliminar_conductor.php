<?php
session_start();
require "functions.php";
redirectIfNotLogged("ver-lista-conductor.php");
require "database.php";

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: ver-lista-conductor.php');
    exit();
}

$id = (int)$_GET['id'];

$sql = "DELETE FROM conductor WHERE id = $id";
if (mysqli_query($conn, $sql)) {
    header('Location: ver-lista-conductor.php');
    exit();
}

header('Location: ver-lista-conductor.php');
exit();
