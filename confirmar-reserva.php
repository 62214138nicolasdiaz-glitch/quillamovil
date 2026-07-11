<?php
session_start();
require "functions.php";
redirectIfNotLogged("reserva.php");
require "database.php";

$mensajeError = '';
$mostrarExito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar']) && $_POST['confirmar'] == '1') {
    // Obtener datos del POST o de la sesión
    $nombre_completo = $_POST['nombre'] && $_POST['apellidos']
        ? trim($_POST['nombre'] . ' ' . $_POST['apellidos'])
        : ($_SESSION['reserva']['nombre_apellido'] ?? '');

    $origen = $_POST['origen'] ?? ($_SESSION['reserva']['origen'] ?? '');
    $destino = $_POST['destino'] ?? ($_SESSION['reserva']['destino'] ?? '');
    $fecha_reserva = $_POST['fecha'] ?? ($_SESSION['reserva']['fecha'] ?? '');
    $hora_reserva = $_POST['hora'] ?? ($_SESSION['reserva']['hora'] ?? '');
    $conductor_raw = $_POST['conductor'] ?? ($_SESSION['reserva']['conductor'] ?? '');
    $conductor_id = $_POST['conductor_id'] ?? ($_SESSION['reserva']['conductor_id'] ?? null);

    $datosConductor = array_pad(explode('|', $conductor_raw), 3, '');
    $conductor_nombre = trim($datosConductor[0]);
    $conductor_apellido = trim($datosConductor[1]);
    $conductor_telefono = trim($datosConductor[2]);

    if (!$nombre_completo || !$origen || !$destino || !$fecha_reserva || !$hora_reserva) {
        $mensajeError = 'Faltan datos obligatorios para enviar la solicitud.';
    } else {
        $whatsappNumber = preg_replace('/[^0-9]/', '', $conductor_telefono);
        if ($whatsappNumber === '') {
            $mensajeError = 'El número de WhatsApp del conductor no es válido.';
        } else {
            $sqlInsert = "INSERT INTO reservas (nombre_completo, origen, destino, fecha_reserva, hora_reserva, conductor_nombre, conductor_apellido, conductor_telefono, status)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')";
            $stmtInsert = mysqli_prepare($conn, $sqlInsert);
            if ($stmtInsert) {
                mysqli_stmt_bind_param($stmtInsert, 'ssssssss', $nombre_completo, $origen, $destino, $fecha_reserva, $hora_reserva, $conductor_nombre, $conductor_apellido, $conductor_telefono);
                if (mysqli_stmt_execute($stmtInsert)) {
                    $insertId = mysqli_insert_id($conn);
                    $mensajeWhatsApp = "Nueva solicitud de reserva de QuillaMovil:\n" .
                        "Cliente: $nombre_completo\n" .
                        "Origen: $origen\n" .
                        "Destino: $destino\n" .
                        "Fecha: $fecha_reserva\n" .
                        "Hora: $hora_reserva\n\n" .
                        "Revisa la solicitud pendiente en tu panel de conductor para aceptar o cancelar.";
                    $whatsappLink = 'https://wa.me/' . $whatsappNumber . '?text=' . urlencode($mensajeWhatsApp);
                    // Guardar datos en sesión para que reserva.php muestre el estado "Pendiente" solo para el conductor seleccionado
                    if ($conductor_id) {
                        $_SESSION['pending_conductor_id'] = intval($conductor_id);
                    }
                    $_SESSION['pending_reserva_id'] = $insertId;
                    $_SESSION['open_whatsapp'] = $whatsappLink;
                    // Redirigir directamente al listado de conductores
                    header('Location: reserva.php?from=confirm');
                    exit();
                } else {
                    $mensajeError = 'No se pudo guardar la solicitud en la base de datos.';
                }
                mysqli_stmt_close($stmtInsert);
            } else {
                $mensajeError = 'Error interno al preparar la solicitud.';
            }
        }
    }
} else {
    header('Location: reserva.php');
    exit();
}
?>
<?php include "form_head.php"; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 overflow-hidden">
                <div class="card-header text-center <?php echo $mostrarExito ? 'bg-success' : 'bg-danger'; ?> text-white py-4">
                    <i class="bi <?php echo $mostrarExito ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?>" style="font-size: 2.4rem;"></i>
                    <h2 class="mt-3 mb-0" style="font-family: Arial, sans-serif;">
                        <?php echo $mostrarExito ? '¡Reserva Exitosa!' : 'Error al Guardar Reserva'; ?>
                    </h2>
                </div>
                <div class="card-body bg-dark text-white p-4">
                    <?php if ($mostrarExito): ?>
                        <p class="lead">La solicitud se creó correctamente y quedó registrada como pendiente en el panel del conductor.</p>
                        <p>El conductor podrá aceptar o cancelar la reserva desde su dashboard.</p>
                        <div class="bg-white bg-opacity-10 rounded-4 p-3 my-3">
                            <p class="mb-1"><strong>Mensaje para WhatsApp:</strong></p>
                            <p class="mb-0">Se abrirá una ventana de WhatsApp con la solicitud como respaldo.</p>
                        </div>
                        <div class="d-flex gap-2 flex-column flex-sm-row mt-3">
                            <a href="<?php echo htmlspecialchars($whatsappLink); ?>" target="_blank" class="btn btn-success flex-fill">Abrir WhatsApp</a>
                            <a href="reserva.php" class="btn btn-outline-light flex-fill">Volver a conductores</a>
                        </div>
                        <div class="animacion animacion-exito mt-4">
                            <div class="rocket">
                                <div class="rocket-body"></div>
                                <div class="rocket-fire"></div>
                            </div>
                            <div class="rocket-smoke">
                                <span></span><span></span><span></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="lead">No se pudo crear la solicitud.</p>
                        <p><?php echo htmlspecialchars($mensajeError); ?></p>
                        <div class="animacion animacion-error mt-4">
                            <div class="sad-face">
                                <div class="eyes"><span></span><span></span></div>
                                <div class="mouth"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="index.php" class="btn btn-success flex-fill">Inicio</a>
                        <a href="reserva.php" class="btn btn-outline-light flex-fill">Ver Conductores</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
