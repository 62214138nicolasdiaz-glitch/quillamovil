<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header('Location: login.php?redirect=conductor.php');
    exit;
}

require "functions.php";


//incluir el archivo de conexion a la base de datos
require "database.php";

$editMode = false;
$conductor = [
    'dni' => '',
    'nombre' => '',
    'apellido' => '',
    'telefono' => '',
    'vehiculo' => '',
    'tipo_lic' => '',
    'numero_censo' => '',
    'color' => '',
    'estado' => '',
    'foto' => ''
];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM conductor WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) === 1) {
        $conductor = mysqli_fetch_assoc($result);
        if (trim($conductor['estado']) === 'Libre') {
            $conductor['estado'] = 'Activo';
        }
        $editMode = true;
    } else {
        header("Location: listar_conductor.php");
        exit();
    }
}

//verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni = mysqli_real_escape_string($conn, clean($_POST["dni"]));
    $nombre = mysqli_real_escape_string($conn, clean($_POST["nombres"]));
    $apellido = mysqli_real_escape_string($conn, clean($_POST["apellidos"]));
    $telefono = mysqli_real_escape_string($conn, clean($_POST["telefono"]));
    $vehiculo = mysqli_real_escape_string($conn, clean($_POST["vehiculo"]));
    $tipo_lic = mysqli_real_escape_string($conn, clean($_POST["tipo_lic"]));
    $numero_censo = mysqli_real_escape_string($conn, clean($_POST["numero_censo"]));
    $color = mysqli_real_escape_string($conn, clean($_POST["color"]));
    $estado = mysqli_real_escape_string($conn, clean($_POST["estado"]));

    // Procesar subida de foto (si existe)
    $foto_path = isset($conductor['foto']) ? $conductor['foto'] : '';
    if (isset($_FILES['foto']) && isset($_FILES['foto']['error']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['foto']['tmp_name']);
        finfo_close($finfo);
        if (in_array($mime, $allowed, true)) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $uploadDir = __DIR__ . '/uploads/conductores/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = uniqid('c_', true) . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) {
                $foto_path = 'uploads/conductores/' . $filename;
            } else {
                $mensaje = "Error al mover el archivo subido.";
            }
        } else {
            $mensaje = "Tipo de archivo no permitido. Use JPG, PNG o GIF.";
        }
    }

    if (!empty($_POST['id'])) {
        $id = intval($_POST['id']);
        if (dniDuplicado($conn, $dni, $id)) {
            $mensaje = "El número de DNI ya existe.";
        } elseif (telefonoDuplicado($conn, $telefono, $id)) {
            $mensaje = "El número de teléfono ya existe en otro conductor.";
        } else {
            $set = "dni='$dni', nombre='$nombre', apellido='$apellido', telefono='$telefono', vehiculo='$vehiculo', tipo_lic='$tipo_lic', numero_censo='$numero_censo', color='$color', estado='$estado'";
            if (!empty($foto_path)) {
                $set .= ", foto='" . mysqli_real_escape_string($conn, $foto_path) . "'";
            }
            $sql = "UPDATE conductor SET $set WHERE id=$id";
            if (mysqli_query($conn, $sql)) {
                header("Location: ver-lista-conductor.php");
                exit();
            } else {
                $mensaje = "Error al actualizar el conductor: " . mysqli_error($conn);
            }
        }
    } else {
        if (dniDuplicado($conn, $dni)) {
            $mensaje = "El número de DNI ya existe.";
        } elseif (telefonoDuplicado($conn, $telefono)) {
            $mensaje = "El número de teléfono ya existe en otro conductor.";
        } else {
            if (crearConductor($conn, $dni, $nombre, $apellido, $telefono, $vehiculo, $tipo_lic, $numero_censo, $color, $estado, $foto_path)) {
                header("Location: listar_conductor.php");
                exit();
            } else {
                $mensaje = "Error al crear el conductor: " . mysqli_error($conn);
            }
        }
    }
}

?>

<?php

function crearConductor($conn, $dni, $nombre, $apellido, $telefono, $vehiculo, $tipo_lic, $numero_censo, $color, $estado, $foto = '')
{
    $fotoEsc = mysqli_real_escape_string($conn, $foto);
    $sql = "INSERT INTO conductor (dni, nombre, apellido, telefono, vehiculo, tipo_lic, numero_censo, color, estado, foto) 
    VALUES ('$dni', '$nombre', '$apellido', '$telefono', '$vehiculo', '$tipo_lic', '$numero_censo', '$color', '$estado', '$fotoEsc')";
    return mysqli_query($conn, $sql);
}

function normalizarTelefono($telefono)
{
    return preg_replace('/\D/', '', $telefono);
}

function dniDuplicado($conn, $dni, $excludeId = null)
{
    $dniEsc = mysqli_real_escape_string($conn, $dni);
    $sql = "SELECT id FROM conductor WHERE dni = '$dniEsc'";
    if ($excludeId !== null) {
        $excludeId = intval($excludeId);
        $sql .= " AND id != $excludeId";
    }

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return false;
    }

    return mysqli_num_rows($result) > 0;
}

function telefonoDuplicado($conn, $telefono, $excludeId = null)
{
    $telefonoNormalizado = normalizarTelefono($telefono);
    if ($telefonoNormalizado === '') {
        return false;
    }

    $sql = "SELECT id, telefono FROM conductor";
    if ($excludeId !== null) {
        $excludeId = intval($excludeId);
        $sql .= " WHERE id != $excludeId";
    }

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return false;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        if (normalizarTelefono($row['telefono']) === $telefonoNormalizado) {
            return true;
        }
    }

    return false;
}

function obtenerConductores($conn)
{
    $sql = "SELECT * FROM conductor";
    return mysqli_query($conn, $sql);
}
?>


<!-- ICONOS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<?php include "form_head.php"; ?>

<style>
    body {
        background: url('fondo_todos.png') center/cover no-repeat;
        min-height: 100vh;
        color: #fff;
        font-family: 'Poppins', Arial, sans-serif;
    }

    .page-shell {
        background: rgba(0, 0, 0, 0.65);
        min-height: 100vh;
        padding: 3rem 0;
    }

    .page-card {
        background: rgba(0, 0, 0, 0.72);
        border: 1px solid rgba(253, 7, 32, 0.8);
        border-radius: 1.25rem;
    }

    .section-title {
        font-size: clamp(2rem, 3.5vw, 3rem);
        font-weight: 700;
    }

    .form-label,
    .service-item p,
    .service-item i,
    .nav-brand {
        color: #fff;
    }

    .alert-danger {
        color: #fff;
        background-color: #fa051d;
        border-color: #dc3545;
    }

    .form-control,
    .form-control:focus {
        color: #fff;
        background-color: rgba(255, 255, 255, 0.08);
        border-color: #fae7e9;
        box-shadow: none;
    }

    .form-select {
        color: #fff;
        background-color: #2f2f2f;
        border-color: #555;
    }

    .form-select:focus {
        color: #fff;
        background-color: #2f2f2f;
        border-color: #888;
        box-shadow: none;
    }

    .form-select option {
        color: #fff;
        background-color: #2f2f2f;
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.7);
        opacity: 1;
    }

    .service-item {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        min-height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.75rem;
    }

    .service-item i {
        font-size: 2rem;
        color: #dc3545;
    }

    .service-item p {
        font-size: 1rem;
        font-weight: 500;
        margin: 0;
    }

    .divider-line {
        height: 1px;
        background: rgba(255, 255, 255, 0.3);
        flex: 1;
    }

    .nav-brand {
        font-size: 1.3rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    @media (max-width: 768px) {
        .page-shell {
            padding: 2rem 0;
        }
    }
</style>




<div class="page-shell">
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-lg-12">
                <div class="card page-card shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title text-white mb-4 text-center"><?php echo $editMode ? 'Editar Conductor' : 'Registrar Conductor'; ?></h2>

                        <?php if (isset($mensaje)): ?>
                            <div class="alert alert-danger"><?php echo $mensaje; ?></div>
                        <?php endif; ?>

                        <form action="conductor.php" method="POST" enctype="multipart/form-data">
                            <?php if ($editMode): ?>
                                <input type="hidden" name="id" value="<?php echo intval($id); ?>">
                            <?php endif; ?>
                            <div class="row g-3">
                                 <div class="col-12">
                                    <label for="foto" class="form-label text-white">Foto:</label>
                                    <?php if (!empty($conductor['foto'])): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo htmlspecialchars($conductor['foto']); ?>" alt="Foto conductor" style="max-height:120px; border-radius:8px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                                </div>
                                <div class="col-12">
                                    <label for="dni" class="form-label text-white">DNI:</label>
                                    <input type="text" class="form-control" id="dni" placeholder="Ingrese el DNI" name="dni" value="<?php echo htmlspecialchars($conductor['dni']); ?>" required maxlength="8" pattern="\d{8}" inputmode="numeric" autocomplete="off">
                                </div>
                                <div class="col-md-6">
                                    <label for="nombres" class="form-label text-white">Nombre:</label>
                                    <input type="text" class="form-control" id="nombres" placeholder="Nombres" name="nombres" value="<?php echo htmlspecialchars($conductor['nombre']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="apellidos" class="form-label text-white">Apellidos:</label>
                                    <input type="text" class="form-control" id="apellidos" placeholder="Apellidos" name="apellidos" value="<?php echo htmlspecialchars($conductor['apellido']); ?>" required>
                                </div>
                                <div class="col-12">
                                    <label for="telefono" class="form-label text-white">Teléfono:</label>
                                    <input type="text" class="form-control" id="telefono" placeholder="Teléfono" name="telefono" value="<?php echo htmlspecialchars($conductor['telefono']); ?>" required maxlength="9" pattern="\d{9}" inputmode="numeric" autocomplete="off">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="vehiculo" class="form-label text-white">Vehículo:</label>
                                    <select class="form-select" id="vehiculo" name="vehiculo" required>
                                        <option disabled <?php echo $conductor['vehiculo'] === '' ? 'selected' : ''; ?>>Seleccionar Vehículo</option>
                                        <option value="Torito" <?php echo $conductor['vehiculo'] === 'Torito' ? 'selected' : ''; ?>>Torito</option>
                                        <option value="Motocarga" <?php echo $conductor['vehiculo'] === 'Motocarga' ? 'selected' : ''; ?>>Motocarga</option>
                                        <option value="Camion" <?php echo $conductor['vehiculo'] === 'Camion' ? 'selected' : ''; ?>>Camion</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="tipo_lic" class="form-label text-white">Placa:</label>
                                    <input type="text" class="form-control" id="tipo_lic" placeholder="Tipo de licencia" name="tipo_lic" value="<?php echo htmlspecialchars($conductor['tipo_lic']); ?>" required>
                                </div>

                                 <div class="col-md-6">
                                    <label for="numero_censo" class="form-label text-white">Numero de serie:</label>
                                    <input type="text" class="form-control" id="numero_censo" placeholder="Numero de serie" name="numero_censo" value="<?php echo htmlspecialchars($conductor['numero_censo']); ?>" required maxlength="4" pattern="\d{4}" inputmode="numeric" autocomplete="off">
                                </div>
                                <div class="col-md-6">
                                    <label for="color" class="form-label text-white">Color del vehículo:</label>
                                    <input type="text" class="form-control" id="color" placeholder="Color del vehículo" name="color" value="<?php echo htmlspecialchars($conductor['color']); ?>" required>
                                </div>
                                <div class="col-12">
                                    <label for="estado" class="form-label text-white">Estado:</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option disabled <?php echo $conductor['estado'] === '' ? 'selected' : ''; ?>>Seleccionar Estado</option>
                                        <option value="Activo" <?php echo $conductor['estado'] === 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="Ocupado" <?php echo $conductor['estado'] === 'Ocupado' ? 'selected' : ''; ?>>Ocupado</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-danger btn-lg"><?php echo $editMode ? 'Guardar Cambios' : 'Enviar'; ?></button>
                            </div>

                            <div class="d-flex align-items-center justify-content-center text-center text-white my-4">
                                <div class="divider-line me-3"></div>
                                <span class="fw-semibold">Ya eres conductor</span>
                                <div class="divider-line ms-3"></div>
                            </div>
                            <div class="text-center">
                                <a href="ver-lista-conductor.php" class="btn btn-outline-light btn-lg">
                                    Ver lista de conductores <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<script>
    const dniInput = document.getElementById('dni');
    let timer;

    dniInput.addEventListener('input', function () {
        clearTimeout(timer);
        let dni = this.value.replace(/\D/g, '');
        dni = dni.slice(0, 8);
        this.value = dni;

        if (dni.length < 8) {
            document.getElementById('nombres').value = '';
            document.getElementById('apellidos').value = '';
            return;
        }

        timer = setTimeout(() => buscarDni(dni), 500);
    });

    const telefonoInput = document.getElementById('telefono');
    const numeroCensoInput = document.getElementById('numero_censo');

    if (telefonoInput) {
        telefonoInput.addEventListener('input', function () {
            let telefono = this.value.replace(/\D/g, '');
            telefono = telefono.slice(0, 9);
            this.value = telefono;
        });
    }

    if (numeroCensoInput) {
        numeroCensoInput.addEventListener('input', function () {
            let numeroCenso = this.value.replace(/\D/g, '');
            numeroCenso = numeroCenso.slice(0, 4);
            this.value = numeroCenso;
        });
    }

    // También al presionar Enter en el campo DNI
    dniInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(timer);
            buscarDni(this.value.trim());
        }
    });

    async function buscarDni(dni) {
        try {
            // Mostrar que está cargando
            document.getElementById('nombres').placeholder = 'Buscando...';
            document.getElementById('apellidos').placeholder = 'Buscando...';

            const res = await fetch(`buscar_dni.php?dni=${dni}`);
            const data = await res.json();

            if (data.nombre) {
                document.getElementById('nombres').value = data.nombre;
                document.getElementById('apellidos').value = data.apellido;
                document.getElementById('nombres').placeholder = 'Nombres';
                document.getElementById('apellidos').placeholder = 'Apellidos';
            } else {
                document.getElementById('nombres').placeholder = 'No encontrado';
                document.getElementById('apellidos').placeholder = 'No encontrado';
            }
        } catch (err) {
            console.error('Error:', err);
        }
    }
</script>
</body>

</html>