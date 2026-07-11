<?php
session_start();
require 'database.php';
require 'functions.php';
redirectIfNotLogged("reserva.php");

// Recibir datos del conductor desde reserva.php
$conductor_nombre = isset($_GET['conductor_nombre']) ? htmlspecialchars($_GET['conductor_nombre']) : '';
$conductor_apellido = isset($_GET['conductor_apellido']) ? htmlspecialchars($_GET['conductor_apellido']) : '';
$conductor_telefono = isset($_GET['conductor_telefono']) ? htmlspecialchars($_GET['conductor_telefono']) : '';
$nombre_conductor_completo = trim($conductor_nombre . ' ' . $conductor_apellido);
$conductor_id = isset($_GET['conductor_id']) ? intval($_GET['conductor_id']) : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasos Reserva - QuillaMovil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>

<style>
    body {
        background: #000;
        color: #fff;
    }

    /* Estilos para el sistema de pasos */
    .pasos-container {
        max-width: 600px;
        margin: 40px auto;
        padding: 30px;
        background: #000;
        border: 1px solid;
        border-image: linear-gradient(to right, #999999 0%, #999999 50%, #ff0000 50%, #ff0000 100%) 1;
        border-radius: 20px;
       
    }

    /* Indicador de pasos */
    .pasos-indicador {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        gap: 10px;
    }

    .paso-numero {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #111;
        border: 2px solid #ff0000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #fff;
        transition: all 0.3s ease;
    }

    .paso-numero.activo {
        background: #ff0000;
        border-color: #ff0000;
        
    }

    .paso-numero.completado {
        background: #ff0000;
        border-color: #ff0000;
        color: #fff;
    }

    .paso-linea {
        flex: 1;
        height: 2px;
        background: #333;
        margin: 0 5px;
    }

    .paso-linea.activo {
        background: #ff0000;
    }

    /* Contenido de pasos */
    .paso-contenido {
        display: none;
    }

    .paso-contenido.activo {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .paso-titulo {
        font-size: 24px;
        font-weight: bold;
        color: #fff;
        margin-bottom: 20px;
        text-align: center;
    }

    /* Estilos para formularios */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #fff;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        background: #222;
        border: 2px solid #ff0000;
        border-radius: 5px;
        color: #fff;
        font-size: 14px;
        transition: border-color 0.3s ease, background-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #ff0000;
        
        background: #2a2a2a;
    }

    /* Estilos para datos de confirmación */
    .dato-confirmacion {
        background: #1a1a1a;
        padding: 15px;
        margin-bottom: 12px;
        border-left: 4px solid #ff0000;
        border-radius: 3px;
    }

    .dato-confirmacion label {
        font-weight: 600;
        color: #fff;
        margin-bottom: 5px;
    }

    .dato-confirmacion .valor {
        color: #fff;
        font-size: 16px;
    }

    .checkbox-confirmacion {
        margin-top: 20px;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #fff;
        font-size: 14px;
    }

    .checkbox-label input {
        width: 18px;
        height: 18px;
        accent-color: #ff0000;
    }

    .error-mensaje {
        display: none;
        color: #ff5252;
        font-size: 13px;
        margin-top: 8px;
    }

    .error-mensaje.activo {
        display: block;
    }

    /* Conductor asignado */
    .conductor-box {
        background: #0b2f13;
        padding: 20px;
        border-radius: 15px;
        border: 2px solid #28a745;
        border-image: linear-gradient(to right, #28a745 0%, #28a745 50%, #1f7b34 50%, #1f7b34 100%) 1;
        margin-top: 20px;
        text-align: center;
    }

    .conductor-titulo {
        color: #7ed47f;
        font-weight: bold;
        margin-bottom: 10px;
        font-size: 14px;
        text-transform: uppercase;
    }

    .conductor-nombre {
        font-size: 22px;
        font-weight: bold;
        color: #e7f7e7;
        margin-bottom: 10px;
    }

    .conductor-info {
        color: #bbb;
        font-size: 12px;
    }

    /* Botones */
    .botones-grupo {
        display: flex;
        gap: 12px;
        margin-top: 30px;
        justify-content: space-between;
    }

    .boton {
        flex: 1;
        padding: 12px 20px;
        border: 1px solid transparent;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
    }

    .boton-primario {
        background: #ff0000;
        color: #fff;
        border: 2px solid #ff0000;
    }

    .boton-primario:hover {
        background: #ff1f1f;
        border-color: #ff1f1f;
        box-shadow: 0 4px 12px rgba(255, 0, 0, 0.35);
    }

    .boton-secundario {
        background: #1b1b1b;
        color: #fff;
        flex: 0.5;
        border: 1px solid #ff0000;
    }

    .boton-secundario:hover {
        background: #2a2a2a;
        border-color: #ff0000;
    }

    .boton:disabled {
        background: #333;
        color: #666;
        cursor: not-allowed;
        opacity: 0.5;
    }

    /* Validaciones */
    .error-mensaje {
        color: #ff5252;
        font-size: 12px;
        margin-top: 5px;
        display: none;
    }

    .error-mensaje.activo {
        display: block;
    }

    .form-group input.error,
    .form-group select.error {
        border-color: #ff5252;
    }

    @media (max-width: 600px) {
        .pasos-container {
            margin: 20px 10px;
            padding: 20px;
        }

        .paso-numero {
            width: 35px;
            height: 35px;
            font-size: 12px;
        }

        .paso-titulo {
            font-size: 18px;
        }

        .botones-grupo {
            flex-direction: column;
        }

        .boton-secundario {
            flex: 1;
        }
    }
</style>

<div class="pasos-container">
    <!-- Indicador de pasos -->
    <div class="pasos-indicador">
        <div class="paso-numero activo" id="num-paso-1">1</div>
        <div class="paso-linea" id="linea-1"></div>
        <div class="paso-numero" id="num-paso-2">2</div>
    </div>

    <!-- PASO 1: Formulario de datos -->
    <div class="paso-contenido activo" id="paso-1">
        <h3 class="text-center">COMPLETA TUS DATOS</h3>

        <form id="formulario-paso1" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre <span style="color: #ff5252;">*</span></label>
                <input type="text" id="nombre" name="nombre" placeholder="Ingresa tu nombre" required>
                <div class="error-mensaje" id="error-nombre">El nombre es requerido</div>
            </div>

            <div class="form-group">
                <label for="apellidos">Apellidos <span style="color: #ff5252;">*</span></label>
                <input type="text" id="apellidos" name="apellidos" placeholder="Ingresa tus apellidos" required>
                <div class="error-mensaje" id="error-apellidos">Los apellidos son requeridos</div>
            </div>

            <div class="form-group">
                <label for="origen">Origen <span style="color: #ff5252;">*</span></label>
                <input type="text" id="origen" name="origen" placeholder="Ingresa el punto de origen" required>
                <div class="error-mensaje" id="error-origen">El origen es requerido</div>
            </div>

            <div class="form-group">
                <label for="destino">Destino <span style="color: #ff5252;">*</span></label>
                <input type="text" id="destino" name="destino" placeholder="Ingresa el punto de destino" required>
                <div class="error-mensaje" id="error-destino">El destino es requerido</div>
            </div>

            <div class="form-group">
                <label for="fecha">Fecha <span style="color: #ff5252;">*</span></label>
                <input type="date" id="fecha" name="fecha" required>
                <div class="error-mensaje" id="error-fecha">La fecha es requerida</div>
            </div>

            <div class="form-group">
                <label for="hora">Hora <span style="color: #ff5252;">*</span></label>
                <input type="time" id="hora" name="hora" required>
                <div class="error-mensaje" id="error-hora">La hora es requerida</div>
            </div>

            <div class="botones-grupo">
                <button type="button" class="boton boton-secundario" onclick="irAtras()">
                    <i class="bi bi-arrow-left"></i> Atrás
                </button>
                <button type="button" class="boton boton-primario" onclick="irAlPaso2()">
                    Siguiente <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- PASO 2: Confirmación de datos -->
    <div class="paso-contenido" id="paso-2">
        <h3 class="text-center">CONFIRMAR RESERVA</h3>
        <p class="text-center text-success">Asegúrate de que todos los datos sean correctos.</p>

        <div class="dato-confirmacion">
            <label>Nombre Completo</label>
            <div class="valor" id="conf-nombre"></div>
        </div>

        <div class="dato-confirmacion">
            <label>Origen</label>
            <div class="valor" id="conf-origen"></div>
        </div>

        <div class="dato-confirmacion">
            <label>Destino</label>
            <div class="valor" id="conf-destino"></div>
        </div>

        <div class="dato-confirmacion">
            <label>Fecha</label>
            <div class="valor" id="conf-fecha"></div>
        </div>

        <div class="dato-confirmacion">
            <label>Hora</label>
            <div class="valor" id="conf-hora"></div>
        </div>

        <!-- Conductor asignado -->
        <div class="conductor-box">
            <div class="conductor-titulo">Conductor Asignado</div>
            <div class="conductor-nombre" id="conf-conductor">
                <?php echo !empty($nombre_conductor_completo) ? $nombre_conductor_completo : 'No asignado'; ?>
            </div>
            <div class="conductor-info">
                <?php if (!empty($conductor_telefono)): ?>
                    <i class="bi bi-telephone"></i> <?php echo $conductor_telefono; ?> <br>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group checkbox-confirmacion">
            <label class="checkbox-label" for="confirm-checkbox">
                <input type="checkbox" id="confirm-checkbox" name="confirm-checkbox">
                He revisado y confirmo que todos los datos son correctos.
            </label>
            <div class="error-mensaje" id="error-confirm-checkbox">Debes marcar esta casilla antes de confirmar.</div>
        </div>

        <div class="botones-grupo">
            <button type="button" class="boton boton-secundario" onclick="irAlPaso1()">
                <i class="bi bi-arrow-left"></i> Atrás
            </button>
            <button type="button" class="boton boton-primario" onclick="confirmarReserva()">
                Enviar Solicitud <i class="bi bi-check2"></i>
            </button>
        </div>
    </div>
</div>

<script>
    // Funciones para navegación entre pasos

    function irAlPaso2() {
        // Validar formulario
        if (validarPaso1()) {
            // Obtener datos del formulario
            const nombre = document.getElementById('nombre').value;
            const apellidos = document.getElementById('apellidos').value;
            const origen = document.getElementById('origen').value;
            const destino = document.getElementById('destino').value;
            const fecha = document.getElementById('fecha').value;
            const hora = document.getElementById('hora').value;

            // Mostrar datos en paso 2
            document.getElementById('conf-nombre').textContent = nombre + ' ' + apellidos;
            document.getElementById('conf-origen').textContent = origen;
            document.getElementById('conf-destino').textContent = destino;

            // Formatear fecha
            const fechaObj = new Date(fecha);
            const fechaFormato = fechaObj.toLocaleDateString('es-ES', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById('conf-fecha').textContent = fechaFormato;
            document.getElementById('conf-hora').textContent = hora;

            // Cambiar paso activo
            cambiarPaso(1, 2);
        }
    }

    function irAlPaso1() {
        // Volver al paso 1
        cambiarPaso(2, 1);
        // Limpiar validaciones
        limpiarValidaciones();
    }

    function irAtras() {
        // Volver a la página anterior
        window.history.back();
    }

    function cambiarPaso(pasAnterior, pasActual) {
        // Ocultar paso anterior
        document.getElementById(`paso-${pasAnterior}`).classList.remove('activo');
        document.getElementById(`num-paso-${pasAnterior}`).classList.remove('activo');

        if (pasAnterior === 1) {
            document.getElementById('linea-1').classList.remove('activo');
        }

        // Mostrar paso actual
        document.getElementById(`paso-${pasActual}`).classList.add('activo');
        document.getElementById(`num-paso-${pasActual}`).classList.add('activo');

        // Actualizar indicadores completados
        if (pasAnterior < pasActual) {
            document.getElementById(`num-paso-${pasAnterior}`).classList.add('completado');
            document.getElementById('linea-1').classList.add('activo');
        } else {
            document.getElementById(`num-paso-${pasAnterior}`).classList.remove('completado');
            document.getElementById('linea-1').classList.remove('activo');
        }

        // Scroll al contenedor
        document.querySelector('.pasos-container').scrollIntoView({ behavior: 'smooth' });
    }

    function validarPaso1() {
        const campos = ['nombre', 'apellidos', 'origen', 'destino', 'fecha', 'hora'];
        let esValido = true;

        campos.forEach(campo => {
            const elemento = document.getElementById(campo);
            const valor = elemento.value.trim();

            if (valor === '') {
                elemento.classList.add('error');
                document.getElementById(`error-${campo}`).classList.add('activo');
                esValido = false;
            } else {
                elemento.classList.remove('error');
                document.getElementById(`error-${campo}`).classList.remove('activo');
            }
        });

        return esValido;
    }

    function limpiarValidaciones() {
        const campos = ['nombre', 'apellidos', 'origen', 'destino', 'fecha', 'hora'];
        campos.forEach(campo => {
            document.getElementById(campo).classList.remove('error');
            document.getElementById(`error-${campo}`).classList.remove('activo');
        });
    }

    function confirmarReserva() {
        const nombre = document.getElementById('nombre').value;
        const apellidos = document.getElementById('apellidos').value;
        const origen = document.getElementById('origen').value;
        const destino = document.getElementById('destino').value;
        const fecha = document.getElementById('fecha').value;
        const hora = document.getElementById('hora').value;

        // Crear formulario oculto para enviar a confirmar-reserva.php
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'confirmar-reserva.php';
        form.style.display = 'none';

        const confirmCheckbox = document.getElementById('confirm-checkbox');
        if (!confirmCheckbox.checked) {
            document.getElementById('error-confirm-checkbox').classList.add('activo');
            confirmCheckbox.focus();
            return;
        }

        // Agregar todos los campos necesarios
        const campos = {
            'confirmar': '1',
            'nombre': nombre,
            'apellidos': apellidos,
            'origen': origen,
            'destino': destino,
            'fecha': fecha,
            'hora': hora,
            'conductor': '<?php echo htmlspecialchars($conductor_nombre . '|' . $conductor_apellido . '|' . $conductor_telefono); ?>',
            'conductor_id': '<?php echo $conductor_id; ?>'
        };

        // Crear inputs para cada campo
        for (const [key, value] of Object.entries(campos)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        // Agregar formulario al documento y enviarlo
        document.body.appendChild(form);
        form.submit();
    }

    // Validación en tiempo real
    document.getElementById('nombre').addEventListener('blur', function() {
        if (this.value.trim() === '') {
            this.classList.add('error');
            document.getElementById('error-nombre').classList.add('activo');
        } else {
            this.classList.remove('error');
            document.getElementById('error-nombre').classList.remove('activo');
        }
    });

    document.getElementById('apellidos').addEventListener('blur', function() {
        if (this.value.trim() === '') {
            this.classList.add('error');
            document.getElementById('error-apellidos').classList.add('activo');
        } else {
            this.classList.remove('error');
            document.getElementById('error-apellidos').classList.remove('activo');
        }
    });

    document.getElementById('origen').addEventListener('blur', function() {
        if (this.value.trim() === '') {
            this.classList.add('error');
            document.getElementById('error-origen').classList.add('activo');
        } else {
            this.classList.remove('error');
            document.getElementById('error-origen').classList.remove('activo');
        }
    });

    document.getElementById('destino').addEventListener('blur', function() {
        if (this.value.trim() === '') {
            this.classList.add('error');
            document.getElementById('error-destino').classList.add('activo');
        } else {
            this.classList.remove('error');
            document.getElementById('error-destino').classList.remove('activo');
        }
    });
</script>
</body>
</html>
