<?php
session_start();
require "functions.php";
redirectIfNotLogged("listar_conductor.php");
require "database.php";

if (!isset($_GET['id'])) {
    header("Location: listar_conductor.php");
    exit();
}
$id = $_GET['id'];

$sql = "SELECT * FROM conductor WHERE id = $id";
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: listar_conductor.php");
    exit();
}
$conductor = mysqli_fetch_assoc($result);
if (trim($conductor['estado']) === 'Libre') {
    $conductor['estado'] = 'Activo';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensaje = '';
    $dni = mysqli_real_escape_string($conn, clean($_POST["dni"]));
    $nombre = mysqli_real_escape_string($conn, clean($_POST["nombre"]));
    $apellido = mysqli_real_escape_string($conn, clean($_POST["apellido"]));
    $telefono = mysqli_real_escape_string($conn, clean($_POST["telefono"]));
    $vehiculo = mysqli_real_escape_string($conn, clean($_POST["vehiculo"]));
    $tipo_lic = mysqli_real_escape_string($conn, clean($_POST["tipo_lic"]));
    $estado = mysqli_real_escape_string($conn, clean($_POST["estado"]));

    // PASO 1: Validar todos los campos ANTES de procesar la foto
    // Si el DNI fue modificado, comprobar duplicado
    if ($dni !== $conductor['dni']) {
        $dniEsc = mysqli_real_escape_string($conn, $dni);
        $sqlCheckDni = "SELECT id FROM conductor WHERE dni = '$dniEsc' AND id != $id";
        $resCheck = mysqli_query($conn, $sqlCheckDni);
        if ($resCheck && mysqli_num_rows($resCheck) > 0) {
            $mensaje = "El número de DNI ya existe.";
        }
    }

    // PASO 2: Si las validaciones pasaron, procesar la foto
    $foto_path = $conductor['foto'] ?? '';
    if (empty($mensaje)) {
        if (!empty($_FILES['foto']['name']) && isset($_FILES['foto']['error']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
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
                    $mensaje = "Error al subir la foto.";
                }
            } else {
                $mensaje = "Tipo de archivo no permitido.";
            }
        }
    }

    // PASO 3: Si no hay errores, actualizar la base de datos con todos los campos
    if (empty($mensaje)) {
        $fotoUpdate = !empty($foto_path) ? ", foto='" . mysqli_real_escape_string($conn, $foto_path) . "'" : '';
        $sql = "UPDATE conductor SET dni='$dni', nombre='$nombre', apellido='$apellido', telefono='$telefono', vehiculo='$vehiculo', tipo_lic='$tipo_lic', estado='$estado'" . $fotoUpdate . " WHERE id=$id";
        if (mysqli_query($conn, $sql)) {
            header("Location: dashboard-conductor.php");
            exit();
        } else {
            $mensaje = "Error al actualizar: " . mysqli_error($conn);
        }
    }
}

function normalizarTelefono($telefono)
{
    return preg_replace('/\D/', '', $telefono);
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
?>

<?php include "form_head.php"; ?>
<div class="torito-bg d-flex flex-column min-vh-100 justify-content-start align-items-center" style="background: url('fondo_todos.png') center/cover no-repeat;">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card p-4">
                    <h2 class="mb-4 text-center">Editar datos de Conductor</h2>
                    <?php if (isset($mensaje)): ?>
                        <div class="alert alert-danger"><?php echo $mensaje; ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data">
                        <style>
                            .profile-photo-wrapper{position:relative;width:140px;height:140px;margin:0 auto 10px;}
                            .profile-photo{width:140px;height:140px;border-radius:50%;object-fit:cover;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.15);display:block}
                            .edit-photo-btn{position:absolute;right:0;bottom:0;background:#0d6efd;color:#fff;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;border:none;box-shadow:0 2px 6px rgba(0,0,0,0.2);cursor:pointer}
                            .edit-photo-btn svg{width:16px;height:16px;fill:currentColor}
                            #foto{display:none}
                        </style>

                        <div class="text-center mb-3">
                            <div class="profile-photo-wrapper">
                                <img id="profilePhoto" class="profile-photo" src="<?php echo !empty($conductor['foto'])?htmlspecialchars($conductor['foto']):'https://via.placeholder.com/140?text=Foto'; ?>" alt="Foto conductor">
                                <button type="button" class="edit-photo-btn" id="editPhotoBtn" title="Cambiar foto">
                                    <!-- lápiz SVG -->
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.42l-2.34-2.34a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z"/></svg>
                                </button>
                            </div>
                            <input type="file" id="foto" name="foto" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" class="form-control" id="dni" name="dni" value="<?php echo htmlspecialchars($conductor['dni']); ?>" required maxlength="8" inputmode="numeric">
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($conductor['nombre']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($conductor['apellido']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($conductor['telefono']); ?>" required maxlength="9" inputmode="numeric">
                        </div>
                        <div class="mb-3">
                            <label for="vehiculo" class="form-label">Vehículo</label>
                            <select class="form-control" id="vehiculo" name="vehiculo" required>
                                <option value="Torito" <?php echo $conductor['vehiculo'] === 'Torito' ? 'selected' : ''; ?>>Torito</option>
                                <option value="Mototaxi" <?php echo $conductor['vehiculo'] === 'Mototaxi' ? 'selected' : ''; ?>>Mototaxi</option>
                                <option value="Auto" <?php echo $conductor['vehiculo'] === 'Auto' ? 'selected' : ''; ?>>Auto</option>
                                <option value="Camioneta" <?php echo $conductor['vehiculo'] === 'Camioneta' ? 'selected' : ''; ?>>Camioneta</option>
                                <option value="Motolineal" <?php echo $conductor['vehiculo'] === 'Motolineal' ? 'selected' : ''; ?>>Motolineal</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_lic" class="form-label">Tipo de Licencia</label>
                            <select class="form-control" id="tipo_lic" name="tipo_lic" required>
                                <option value="Licencia A-I" <?php echo $conductor['tipo_lic'] === 'Licencia A-I' ? 'selected' : ''; ?>>Licencia A-I</option>
                                <option value="Licencia A-II" <?php echo $conductor['tipo_lic'] === 'Licencia A-II' ? 'selected' : ''; ?>>Licencia A-II</option>
                                <option value="Licencia B" <?php echo $conductor['tipo_lic'] === 'Licencia B' ? 'selected' : ''; ?>>Licencia B</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-control" id="estado" name="estado" required>
                                <option value="Activo" <?php echo $conductor['estado'] === 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                <option value="Ocupado" <?php echo $conductor['estado'] === 'Ocupado' ? 'selected' : ''; ?>>Ocupado</option>
                            </select>
                        </div>
                        <!-- foto moved above DNI; input kept there -->
                        <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>

                        <script>
                            (function(){
                                var editBtn = document.getElementById('editPhotoBtn');
                                var fileInput = document.getElementById('foto');
                                var img = document.getElementById('profilePhoto');
                                if(editBtn && fileInput){
                                    editBtn.addEventListener('click', function(){ fileInput.click(); });
                                    fileInput.addEventListener('change', function(e){
                                        if(this.files && this.files[0]){
                                            var reader = new FileReader();
                                            reader.onload = function(ev){ img.src = ev.target.result; };
                                            reader.readAsDataURL(this.files[0]);
                                        }
                                    });
                                }
                            })();
                        </script>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
