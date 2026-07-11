<?php
session_start();
// Solo destruir la sesión del conductor
if (isset($_SESSION['conductor'])) {
    unset($_SESSION['conductor']);
}
session_write_close();
header('Location: login-conductor.php');
exit;
