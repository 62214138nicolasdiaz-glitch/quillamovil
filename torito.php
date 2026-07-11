<?php
session_start();
require "functions.php";
redirectIfNotLogged("torito.php");

// Incluir el archivo de conexión a la base de datos
require "database.php";

$editing = false;
$editingId = null;
$nombre = '';
$apellido = '';
$dni = '';
$direccion = '';
$telefono = '';
$estado = 'Activo';
$latitud = '';
$longitud = '';
$direccion_actual = '';
$consentimiento_ubicacion = 0;
$fecha_consentimiento = null;

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $editingId = (int)$_GET['id'];
    $sql = "SELECT * FROM toro WHERE id = $editingId";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $torito = mysqli_fetch_assoc($result);
        $editing = true;
        $nombre = $torito['nombre'];
        $apellido = $torito['apellido'];
        $dni = $torito['dni'];
        $direccion = $torito['direccion'];
        $telefono = $torito['telefono'];
        $estado = $torito['estado'];
        $latitud = $torito['latitud'];
        $longitud = $torito['longitud'];
        $direccion_actual = $torito['direccion_actual'];
        $consentimiento_ubicacion = $torito['consentimiento_ubicacion'];
        $fecha_consentimiento = $torito['fecha_consentimiento'];
    }
}

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y limpiar los datos del formulario
    $nombre = trim($_POST["nombre"] ?? '');
    $apellido = trim($_POST["apellido"] ?? '');
    $dni = trim($_POST["dni"] ?? '');
    $direccion = trim($_POST["direccion"] ?? '');
    $telefono = trim($_POST["telefono"] ?? '');
    $estado = trim($_POST["estado"] ?? '');
    $latitud = trim($_POST["latitud"] ?? '');
    $longitud = trim($_POST["longitud"] ?? '');
    $direccion_actual = trim($_POST["direccion_actual"] ?? '');
    $consentimiento_ubicacion = isset($_POST["consentimiento_ubicacion"]) ? 1 : 0;
    $fecha_consentimiento = trim($_POST["fecha_consentimiento"] ?? '');
    $editingId = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int)$_POST['id'] : null;
    $editing = $editingId !== null;

    // Validar que el DNI sea obligatorio
    if (empty($dni)) {
        $mensaje = "El DNI es obligatorio.";
    } else {

        // Escapar para usar en consultas
        $nombre = mysqli_real_escape_string($conn, $nombre);
        $apellido = mysqli_real_escape_string($conn, $apellido);
        $dni = mysqli_real_escape_string($conn, $dni);
        $direccion = mysqli_real_escape_string($conn, $direccion);
        $telefono = mysqli_real_escape_string($conn, $telefono);
        $estado = mysqli_real_escape_string($conn, $estado);
        $latitud = mysqli_real_escape_string($conn, $latitud);
        $longitud = mysqli_real_escape_string($conn, $longitud);
        $direccion_actual = mysqli_real_escape_string($conn, $direccion_actual);
        $fecha_consentimiento = mysqli_real_escape_string($conn, $fecha_consentimiento);

        if ($editing) {
            // Verificar si el DNI ya existe en otro registro
            $checkSql = "SELECT id FROM toro WHERE dni = '$dni' AND id <> $editingId LIMIT 1";
            $checkRes = mysqli_query($conn, $checkSql);

            if ($checkRes && mysqli_num_rows($checkRes) > 0) {
                $mensaje = "El DNI ya existe en la base de datos.";
            } else {
                $latitudValue = $latitud === '' ? 'NULL' : "'$latitud'";
                $longitudValue = $longitud === '' ? 'NULL' : "'$longitud'";
                $direccion_actualValue = $direccion_actual === '' ? 'NULL' : "'$direccion_actual'";
                $fecha_consentimientoValue = $fecha_consentimiento === null ? 'NULL' : "'$fecha_consentimiento'";
                $sql = "UPDATE toro SET nombre='$nombre', apellido='$apellido', dni='$dni', direccion='$direccion', telefono='$telefono', estado='$estado', latitud=$latitudValue, longitud=$longitudValue, direccion_actual=$direccion_actualValue, consentimiento_ubicacion=$consentimiento_ubicacion, fecha_consentimiento=$fecha_consentimientoValue WHERE id=$editingId";
                if (mysqli_query($conn, $sql)) {
                    header("Location: listar.php");
                    exit();
                } else {
                    $mensaje = "Error al actualizar el torito: " . mysqli_error($conn);
                }
            }
        } else {
            // Verificar si el DNI ya existe en la tabla 'toro'
            $checkSql = "SELECT id FROM toro WHERE dni = '$dni' LIMIT 1";
            $checkRes = mysqli_query($conn, $checkSql);

            if ($checkRes && mysqli_num_rows($checkRes) > 0) {
                $mensaje = "El DNI ya existe en la base de datos.";
            } else {
                // Ejecutar la función para crear el torito
                if (crearTorito($conn, $nombre, $apellido, $dni, $direccion, $telefono, $estado, $latitud, $longitud, $direccion_actual, $consentimiento_ubicacion, $fecha_consentimiento)) {
                    // Redirigir a listar.php después de crear exitosamente
                    header("Location: listar.php");
                    exit();
                } else {
                    $mensaje = "Error al crear el torito: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>

<?php

function crearAlumno($conn, $nombre, $apellidos, $dni)
{
    $sql = "INSERT INTO alumnos (nombres, apellidos, dni) 
            VALUES ('$nombre', '$apellidos', '$dni')";
    return mysqli_query($conn, $sql);
}

function obtenerAlumnos($conn)
{
    $sql = "SELECT * FROM alumnos";
    return mysqli_query($conn, $sql);
}
?>



<?php

/**
 * Crea un nuevo registro de torito en la base de datos.
 *
 * @param mysqli $conn Conexión a la base de datos.
 * @param string $nombre Nombre del conductor.
 * @param string $apellido Apellido del conductor.
 * @param string $dni DNI del conductor.
 * @param string $direccion Dirección del conductor.
 * @param string $telefono Teléfono del conductor.
 * @param string $latitud Latitud de la ubicación.
 * @param string $longitud Longitud de la ubicación.
 * @param string $direccion_actual Dirección actual del conductor.
 * @param int $consentimiento_ubicacion Indicador de consentimiento para el seguimiento de ubicación.
 * @param string $fecha_consentimiento Fecha de consentimiento para el seguimiento de ubicación.
 * @return bool True si la inserción fue exitosa, false en caso contrario.
 */
function crearTorito($conn, $nombre, $apellido, $dni, $direccion, $telefono, $estado, $latitud = '', $longitud = '', $direccion_actual = '', $consentimiento_ubicacion = 0, $fecha_consentimiento = null)
{
    $latitudValue = $latitud === '' ? 'NULL' : "'$latitud'";
    $longitudValue = $longitud === '' ? 'NULL' : "'$longitud'";
    $direccion_actualValue = $direccion_actual === '' ? 'NULL' : "'$direccion_actual'";
    $fecha_consentimientoValue = $fecha_consentimiento === null ? 'NULL' : "'$fecha_consentimiento'";
    $sql = "INSERT INTO toro (nombre, apellido, dni, direccion, telefono, estado, latitud, longitud, direccion_actual, consentimiento_ubicacion, fecha_consentimiento)
    VALUES ('$nombre', '$apellido', '$dni', '$direccion', '$telefono', '$estado', $latitudValue, $longitudValue, $direccion_actualValue, $consentimiento_ubicacion, $fecha_consentimientoValue)";
    return mysqli_query($conn, $sql);
}
?>



<!-- ICONOS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-sA+e2Yd3rY6k2bYb1gGk4vQb2q5gP3p0h2v+6b6YQmM=" crossorigin=""/>

<?php include "form_head.php"; ?>


<style>
    body {
        background: url('fondo_todos.png') center/cover no-repeat;
        color: white;
        font-family: 'Poppins', Arial, sans-serif;
    }

    .torito-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 3rem;
    }

    .torito-menu a {
        color: white;
        text-decoration: none;
        font-size: 1.2rem;
        font-weight: 600;
        transition: 0.3s;
        margin: 0 1.5rem;
    }

    .torito-menu a:hover {
        color: #f7e0e0;
    }

    .torito-title h2 {
        font-size: 4rem;
        line-height: 1.1;
        font-weight: 700;
        color: white;
    }

    .torito-title h2 span {
        color: #f30000;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        margin: 3rem 0;
    }

    .service-item {
        text-align: center;
        padding: 0 1.5rem;
        transform: translateX(-0.35rem);
    }

    .service-item + .service-item {
        border-left: 1px solid rgba(255, 255, 255, 0.65);
    }

    .service-item i {
        color: #f30000;
        font-size: 2.5rem;
        margin-bottom: 1rem;
        display: inline-block;
    }

    .service-item p {
        font-size: 1.1rem;
        font-weight: 500;
    }

    .form-section {
        background: rgba(0, 0, 0, 0.65);
        border: 2px solid #ebebeb;
        border-radius: 14px;
        padding: 2rem;
        
    }

    .form-section h1 {
        color: #ffffff !important;
        font-size: 1.75rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .form-section .form-label {
        color: #ffffff !important;
    }

    .form-section .form-control,
    .form-section .form-control:focus {
        color: #ffffff !important;
        background-color: rgba(0, 0, 0, 0.45) !important;
        border-color: #fdfdfd!important;
    }

    .form-section .form-control::placeholder {
        color: rgba(255, 255, 255, 0.85) !important;
        opacity: 1;
    }

    .form-section .form-control option {
        background-color: #000000;
        color: #ffffff;
    }

    .form-section select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23ffffff' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        padding-right: 2.5rem;
    }

    @media (max-width: 768px) {
        .services-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .service-item + .service-item {
            border-left: none;
            padding-left: 0;
        }

        .torito-title h2 {
            font-size: 2.5rem;
        }

        .torito-menu a {
            font-size: 1rem;
            margin: 0 0.5rem;
        }
    }

    @media (max-width: 576px) {
        .services-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="min-vh-100 d-flex flex-column justify-content-start align-items-center py-4" 
    style="background: rgba(0, 0, 0, 0.6);">
    <div class="container-fluid px-4 px-md-5">
        <!-- HEADER -->
        <header class="torito-header mb-5">
            <div style="width: 100%;">
                <nav class="torito-menu d-flex justify-content-end align-items-center gap-3">
                    <a href="motocarga.php" class="nav-link-custom">Motocarga</a>
                    <a href="camion.php" class="nav-link-custom">Camión</a>
                    <a href="index.php" class="btn btn-outline-danger ms-3">
                        <i class="fa-regular fa-user"></i> Volver
                    </a>
                </nav>
            </div>
        </header>

        <div class="row g-5 align-items-center">
            <!-- CONTENIDO IZQUIERDO -->
            <div class="col-lg-6 col-12 mb-4 mb-lg-0">
                <div class="torito-title">
                    <h2 class="mb-4">
                        ESCOGISTE <br>
                        <span>EL TORITO</span>
                    </h2>
                </div>

                <div class="services-grid">
                    <div class="service-item">
                        <i class="fa-solid fa-car"></i>
                        <p>Transporte urbano</p>
                    </div>
                    <div class="service-item">
                        <i class="fa-solid fa-camera"></i>
                        <p>Servicio turístico</p>
                    </div>
                    <div class="service-item">
                        <i class="fa-solid fa-motorcycle"></i>
                        <p>Servicio delivery</p>
                    </div>
                    <div class="service-item">
                        <i class="fa-solid fa-box"></i>
                        <p>Carga ligera</p>
                    </div>
                </div>
            </div>


            <!-- FORMULARIO DERECHO -->
            <div class="col-lg-6 col-12">
                <div class="form-section">
                    <h1>
                        <?php echo $editing ? 'EDITAR TORITO' : 'INGRESE SUS DATOS'; ?>
                    </h1>

                    <?php if (isset($mensaje)): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($mensaje); ?>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="torito.php" method="POST" novalidate>
                        <?php if ($editing): ?>
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($editingId); ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI:</label>
                            <input type="text" class="form-control bg-dark text-white border-danger" 
                                id="dni" placeholder="Ingrese DNI" name="dni" required maxlength="8" 
                                autocomplete="off" value="<?php echo htmlspecialchars($dni); ?>" inputmode="numeric">
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre:</label>
                            <input type="text" class="form-control bg-dark text-white border-danger" 
                                id="nombre" placeholder="Ingrese nombre" name="nombre" 
                                value="<?php echo htmlspecialchars($nombre); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="apellido" class="form-label">Apellidos:</label>
                            <input type="text" class="form-control bg-dark text-white border-danger" 
                                id="apellido" placeholder="Ingrese apellidos" name="apellido" 
                                value="<?php echo htmlspecialchars($apellido); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección:</label>
                            <input type="text" class="form-control bg-dark text-white border-danger" 
                                id="direccion" placeholder="Ingrese dirección" name="direccion" 
                                value="<?php echo htmlspecialchars($direccion); ?>">
                        </div>

                        <div class="mb-4">
                            <label for="telefono" class="form-label">Teléfono:</label>
                            <input type="text" class="form-control bg-dark text-white border-danger" 
                                id="telefono" placeholder="Ingrese teléfono" name="telefono" 
                                value="<?php echo htmlspecialchars($telefono); ?>" maxlength="9" inputmode="numeric">
                        </div>
                        <div class="mb-4">
                            <label for="estado" class="form-label">Estado:</label>
                            <select class="form-control bg-dark text-white border-danger" 
                                id="estado" name="estado">
                                <option value="" <?php echo $estado === '' ? 'selected' : ''; ?>>Seleccione estado</option>
                                <option value="Activo" <?php echo $estado === 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                <option value="Ocupado" <?php echo $estado === 'Ocupado' ? 'selected' : ''; ?>>Ocupado</option>
                            </select>
                        </div>

                         <div class="mb-4">
                            <button type="button" id="btnObtenerUbicacion" class="btn btn-outline-light w-100">
                                Obtener ubicación actual
                            </button>
                        </div>

                        <div class="mb-4">
                                <label for="direccion_actual" class="form-label">Dirección actual:</label>
                                <input type="text" class="form-control bg-dark text-white border-danger"
                                    id="direccion_actual" name="direccion_actual" value="<?php echo htmlspecialchars($direccion_actual); ?>">
                            </div>

                        <div class="mb-4">
                            <label for="latitud" class="form-label">Latitud:</label>
                            <input type="text" class="form-control bg-dark text-white border-danger"
                                id="latitud" name="latitud" value="<?php echo htmlspecialchars($latitud); ?>">
                        </div>

                        <div class="mb-4">
                            <label for="longitud" class="form-label">Longitud:</label>
                            <input type="text" class="form-control bg-dark text-white border-danger"
                                id="longitud" name="longitud" value="<?php echo htmlspecialchars($longitud); ?>">
                        </div>
                            
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="consentimiento_ubicacion" name="consentimiento_ubicacion" <?php echo $consentimiento_ubicacion ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="consentimiento_ubicacion">
                                        Consentimiento para seguimiento de ubicación
                                    </label>
                                </div>
                            </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-danger btn-lg fw-bold">
                                
                                <?php echo $editing ? 'Guardar cambios' : 'Enviar'; ?>
                            </button>
                        </div>
                    </form>

                    <!-- Divisor -->
                    <div class="d-flex align-items-center my-4">
                        <hr class="flex-grow-1 border-light" style="opacity: 0.3;">
                        <span class="px-3 fw-bold text-light" style="white-space: nowrap;">Ya eres usuario</span>
                        <hr class="flex-grow-1 border-light" style="opacity: 0.3;">
                    </div>

                    <!-- Botón secundario -->
                    <div class="text-center">
                        <a href="listar.php" class="btn btn-warning btn-sm fw-bold px-4">
                            Ver lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Esperar a que el DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', function () {
        // ===== DNI AUTO-COMPLETE =====
        const dniInput = document.getElementById('dni');
        let timer;

        if (dniInput) {
            // Validar DNI: solo números, máximo 8
            dniInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 8) {
                    this.value = this.value.slice(0, 8);
                }
                clearTimeout(timer);
                const dni = this.value.trim();

                if (dni.length < 8) {
                    document.getElementById('nombre').value = '';
                    document.getElementById('apellido').value = '';
                    return;
                }

                timer = setTimeout(() => buscarDni(dni), 500);
            });

            // También al presionar Enter en el campo DNI
            dniInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(timer);
                    buscarDni(this.value.trim());
                }
            });
        }

        async function buscarDni(dni) {
            try {
                // Mostrar que está cargando
                document.getElementById('nombre').placeholder = 'Buscando...';
                document.getElementById('apellido').placeholder = 'Buscando...';

                const res = await fetch(`buscar_dni.php?dni=${dni}`);
                const data = await res.json();

                if (data.nombre) {
                    document.getElementById('nombre').value = data.nombre;
                    document.getElementById('apellido').value = data.apellido;
                    document.getElementById('nombre').placeholder = 'Nombre';
                    document.getElementById('apellido').placeholder = 'Apellido';
                } else {
                    document.getElementById('nombre').placeholder = 'No encontrado';
                    document.getElementById('apellido').placeholder = 'No encontrado';
                }
            } catch (err) {
                console.error('Error:', err);
            }
        }

        // ===== VALIDACIÓN DE TELÉFONO =====
        const telefonoInput = document.getElementById('telefono');
        if (telefonoInput) {
            telefonoInput.addEventListener('input', function () {
                // Remover caracteres no numéricos
                this.value = this.value.replace(/[^0-9]/g, '');
                // Limitar a 9 dígitos
                if (this.value.length > 9) {
                    this.value = this.value.slice(0, 9);
                }
            });
        }

        // ===== GEOLOCALIZACIÓN =====
        const geoButton = document.getElementById('btnObtenerUbicacion');
        const latInput = document.getElementById('latitud');
        const lonInput = document.getElementById('longitud');
        const direccionActualInput = document.getElementById('direccion_actual');
        const seguimientoCheckbox = document.getElementById('consentimiento_ubicacion');
        let watchId = null;
        let lastReverseAt = 0;

        async function reverseGeocode(lat, lon) {
            try {
                const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}&accept-language=es`;
                const res = await fetch(url);
                const data = await res.json();

                if (data && data.display_name) {
                    return data.display_name;
                }
            } catch (err) {
                console.error('Error al obtener la dirección:', err);
            }
            return '';
        }

        function actualizarUbicacion(position, mostrarLoadingButton = true) {
            const lat = position.coords.latitude.toFixed(7);
            const lon = position.coords.longitude.toFixed(7);
            const now = Date.now();

            if (latInput) latInput.value = lat;
            if (lonInput) lonInput.value = lon;
            if (direccionActualInput) {
                direccionActualInput.value = 'Obteniendo dirección...';
            }

            if (direccionActualInput && now - lastReverseAt > 5000) {
                lastReverseAt = now;
                reverseGeocode(lat, lon).then((direccion) => {
                    if (direccionActualInput) {
                        direccionActualInput.value = direccion || 'Dirección no disponible';
                    }
                });
            }

            if (geoButton && mostrarLoadingButton) {
                geoButton.disabled = false;
                if (seguimientoCheckbox && seguimientoCheckbox.checked) {
                    geoButton.textContent = 'Detener seguimiento';
                } else {
                    geoButton.textContent = 'Obtener ubicación actual';
                }
            }
        }

        function mostrarErrorUbicacion(error, mostrarLoadingButton = true) {
            console.error('Error de geolocalización:', error);
            if (geoButton && mostrarLoadingButton) {
                geoButton.disabled = false;
                geoButton.textContent = 'Obtener ubicación actual';
            }
            if (error.code !== error.PERMISSION_DENIED && mostrarLoadingButton) {
                alert('No se pudo obtener la ubicación. Asegúrate de permitir el acceso al GPS.');
            }
        }

        function obtenerUbicacionUnaVez() {
            if (!navigator.geolocation) {
                alert('Geolocation no es compatible con este navegador.');
                return;
            }

            if (geoButton) {
                geoButton.disabled = true;
                geoButton.textContent = 'Obteniendo ubicación...';
            }
            if (direccionActualInput) {
                direccionActualInput.value = 'Buscando ubicación actual...';
            }

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    actualizarUbicacion(position, true);
                },
                function (error) {
                    mostrarErrorUbicacion(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        function iniciarSeguimientoUbicacion() {
            if (!navigator.geolocation) {
                alert('Geolocation no es compatible con este navegador.');
                return;
            }

            if (geoButton) {
                geoButton.disabled = true;
                geoButton.textContent = 'Obteniendo ubicación...';
            }
            if (direccionActualInput) {
                direccionActualInput.value = 'Buscando ubicación actual...';
            }

            watchId = navigator.geolocation.watchPosition(
                function (position) {
                    actualizarUbicacion(position);
                },
                function (error) {
                    mostrarErrorUbicacion(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        function detenerSeguimientoUbicacion() {
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            if (geoButton) {
                geoButton.textContent = 'Obtener ubicación actual';
                geoButton.disabled = false;
            }
        }

        // Botón click listener
        if (geoButton) {
            geoButton.addEventListener('click', function () {
                if (seguimientoCheckbox && seguimientoCheckbox.checked) {
                    if (watchId === null) {
                        iniciarSeguimientoUbicacion();
                    } else {
                        detenerSeguimientoUbicacion();
                    }
                } else {
                    obtenerUbicacionUnaVez();
                }
            });
        }

        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (seguimientoCheckbox && !seguimientoCheckbox.checked) {
                    e.preventDefault();
                    alert('Debe marcar el consentimiento para seguimiento de ubicación antes de enviar.');
                    seguimientoCheckbox.focus();
                }
            });
        }
    });
</script>

</body>

</html>