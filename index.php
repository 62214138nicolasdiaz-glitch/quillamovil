<?php
session_start();
require "functions.php";
require "database.php";
?>

<?php include "header.php"; ?>

<!-- Sección Héroe -->
<section class="hero"
    style="background: url('fondo-torito.png') center/cover no-repeat, linear-gradient(135deg, #ff3535 0%, #007bff 100%); color: white; padding: 80px 20px; position: relative; min-height: 100vh;">
    <!-- Overlay negro semitransparente -->
    <div
        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.6); z-index: 1;">
    </div>



    <style>
        /* ============================================================
           RESET
        ============================================================ */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* ============================================================
           HERO SECTION
        ============================================================ */


        .hero-inner {
            position: relative;
            z-index: 2;
            padding: 70px 40px 60px;
        }

        /* ============================================================
           TÍTULOS
        ============================================================ */
        .muevete-title {
            font-family: 'Arial Black', Arial, sans-serif;
            font-weight: 900;
            font-size: clamp(1.8rem, 7.9vw, 7rem);
            line-height: 0.88;
            letter-spacing: -1px;
            color: #fff;
        }

        .sin-limites-title {
            font-family: 'Arial Black', Arial, sans-serif;
            font-weight: 900;
            font-size: clamp(1.8rem, 6.4vw, 6rem);
            line-height: 0.88;
            color: #f30000;
            margin-top: 6px;
        }

        .a-tu-ciudad-row {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 10px;
            margin-bottom: 6px;
        }

        .red-line {
            display: inline-block;
            height: 3px;
            width: 40px;
            background: #f30000;
            border-radius: 2px;
            flex-shrink: 0;
        }

        .a-tu-ciudad-title {
            font-family: 'Arial Black', Arial, sans-serif;
            font-weight: 900;
            font-size: clamp(1rem, 3.5vw, 3.5rem);
            line-height: 0.9;
            color: #fff;
            white-space: nowrap;
        }

        /* ============================================================
           DESCRIPCIÓN
        ============================================================ */
        .hero-description {
            font-size: clamp(1.30rem, 1.6vw, 1.1rem);
            line-height: 1.75;
            color: rgba(255, 255, 255, 0.88);
            max-width: 750px;
            margin-top: 16px;

        }



        /* ============================================================
           TARJETA DE SELECCIÓN
        ============================================================ */
        .selection-card {
            background: rgba(0, 0, 0, 0.58);
            border: 2px solid #2a2020;
            border-radius: 18px;
            padding: 20px 28px 28px;
            margin-top: 22px;
            max-width: 755px;
            width: 100%;
        }

        .selection-card .card-title {
            font-family: 'Arial Black', Arial, sans-serif;
            font-size: clamp(1.4rem, 3.5vw, 2rem);
            font-weight: 900;
            color: #fff;
            display: block;
            margin-bottom: 10px;
        }

        .selection-card label {
            font-weight: bold;
            color: #fff;
            font-size: clamp(0.9rem, 1.8vw, 1.1rem);
            display: block;
            margin-bottom: 10px;
        }

        .select-btn-row {
            display: flex;
            align-items: stretch;
            gap: 14px;
            flex-wrap: wrap;
        }

        /* ============================================================
           SELECT PERSONALIZADO
        ============================================================ */
        .custom-select-wrapper {
            position: relative;
            flex: 1 1 180px;
            min-width: 140px;
        }

        .custom-select-black {
            width: 100%;
            height: 46px;
            border-radius: 14px;
            font-size: clamp(0.9rem, 1.8vw, 1.1rem);
            background: #000;
            color: #fff;
            border: 1.5px solid #f30000;
            padding: 0 42px 0 14px;
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .custom-select-black:focus {
            outline: 2px solid #f30000;
            outline-offset: 1px;
        }

        .custom-select-black option {
            background: #1a1a1a;
            color: #fff;
        }

        .custom-select-arrow {
            pointer-events: none;
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
        }

        /* ============================================================
           BOTÓN SOLICITAR
        ============================================================ */
        .btn-solicitar {
            background-color: #f30000;
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 12px 30px;
            font-size: clamp(1rem, 2vw, 1.25rem);
            font-weight: bold;
            cursor: not-allowed;
            opacity: 0.55;
            box-shadow: 0 3px 12px rgba(243, 0, 0, 0.3);
            transition: background 0.2s, opacity 0.2s, transform 0.15s, box-shadow 0.2s;
            flex: 0 0 auto;
            white-space: nowrap;
        }

        .btn-solicitar:not(:disabled) {
            cursor: pointer;
            opacity: 1;
        }

        .btn-solicitar:not(:disabled):hover {
            background-color: #c80000;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(243, 0, 0, 0.45);
        }

        .btn-solicitar:not(:disabled):active {
            transform: translateY(0);
        }

        /* ============================================================
           BOTÓN LINK DISABLED (BOOTSTRAP STYLE)
        ============================================================ */
        #btnSolicitarServicio {
            background-color: #f30000 !important;
            color: #fff !important;
        }

        #btnSolicitarServicio:not(.btn-link-disabled) {
            cursor: pointer;
            opacity: 1;
        }

        #btnSolicitarServicio:not(.btn-link-disabled):hover {
            background-color: #c80000 !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(243, 0, 0, 0.45) !important;
            text-decoration: none;
            color: #fff !important;
        }

        #btnSolicitarServicio:not(.btn-link-disabled):active {
            transform: translateY(0);
        }

        .btn-link-disabled {
            opacity: 0.55;
            cursor: not-allowed;
            pointer-events: none;
        }

        .btn-link-disabled:hover,
        .btn-link-disabled:active {
            color: #fff;
            text-decoration: none;
            transform: none;
            box-shadow: 0 3px 12px rgba(243, 0, 0, 0.3);
        }

        .hero-title-row {
            display: flex;
            align-items: stretch;
            gap: 28px;
            flex-wrap: wrap;
        }

        .hero-title-group {
            display: flex;
            flex: 1 1 48%;
            flex-direction: column;
            align-items: flex-start;
            min-width: 320px;
        }

        .hero-cta-card {
            flex: 1 1 48%;
            min-width: 220px;
            max-width: 560px;
            margin-top: 0;
            padding: 22px 24px;
            border-radius: 20px;
            background: rgba(0, 0, 0, 0.68);
            border: 1.5px solid rgba(243, 0, 0, 0.55);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
        }

        .hero-cta-card p {
            color: rgba(255, 255, 255, 0.92);
            font-size: clamp(1rem, 1.5vw, 1.08rem);
            line-height: 1.7;
            margin-bottom: 18px;
            text-align: left;
        }

        .hero-cta-actions {
            display: flex;
            justify-content: center;
        }

        .btn-reserva {
            background-color: #f30000;
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 14px 34px;
            font-size: clamp(1.05rem, 1.9vw, 1.25rem);
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 3px 12px rgba(243, 0, 0, 0.32);
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        .btn-reserva:hover {
            background-color: #c80000;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(243, 0, 0, 0.42);
        }

        .btn-reserva:active {
            transform: translateY(0);
        }

        .btn-reserva i {
            font-size: 1.1rem;
        }

        /* ============================================================
           PANEL CARDS
        ============================================================ */
        .panel-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-top: 44px;
        }

        .panel-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(0, 0, 0, 0.70);
            border: 2px solid #f30000;
            border-radius: 16px;
            padding: 22px 12px;
            text-align: center;
            color: #fff;
            font-weight: bold;
            font-size: clamp(1rem, 1.8vw, 1.25rem);
            min-height: 120px;
            max-width: 280px;
            width: 100%;
            transition: transform 0.22s ease, box-shadow 0.22s ease, background 0.22s;
        }

        .panel-card i {
            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
            color: #f30000;
        }

        .como-funciona-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1.5px solid rgba(243, 0, 0, 0.35);
            border-radius: 18px;
            padding: 24px 20px;
            height: 100%;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
        }

        .como-funciona-card h4 {
            color: #fff;
            font-weight: bold;
        }

        .como-funciona-card p {
            color: #fff;
            line-height: 1.7;
            margin-bottom: 0;
        }

        /* ============================================================
           RESPONSIVE — TABLET (≤ 991px)
        ============================================================ */
        @media (max-width: 991.98px) {
            .hero-inner {
                padding: 54px 28px 48px;
                text-align: center;
            }

            .muevete-title,
            .sin-limites-title {
                text-align: center;
            }

            .a-tu-ciudad-row {
                justify-content: center;
            }

            .hero-description {
                text-align: center;
                margin-left: auto;
                margin-right: auto;
            }

            .hero-title-row {
                justify-content: center;
                text-align: center;
            }

            .hero-title-group {
                align-items: center;
                flex-basis: 100%;
            }

            .hero-cta-card {
                flex-basis: 100%;
                margin-left: auto;
                margin-right: auto;
            }

            .hero-cta-actions {
                justify-content: center;
            }

            .selection-card {
                margin-left: auto;
                margin-right: auto;
            }

            .select-btn-row {
                justify-content: center;
            }

            .panel-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
                margin-top: 32px;
            }
        }

        /* ============================================================
           RESPONSIVE — MÓVIL GRANDE (≤ 767px)
        ============================================================ */
        @media (max-width: 767.98px) {
            .hero-inner {
                padding: 44px 18px 36px;
            }

            .muevete-title {
                font-size: clamp(2.8rem, 14vw, 4.5rem);
            }

            .sin-limites-title {
                font-size: clamp(2.2rem, 11.5vw, 3.6rem);
            }

            .a-tu-ciudad-title {
                font-size: clamp(1rem, 5.5vw, 1.6rem);
            }

            .red-line {
                width: 26px;
            }

            .hero-description {
                font-size: clamp(0.95rem, 4vw, 1.1rem);
            }

            .select-btn-row {
                flex-direction: column;
                gap: 12px;
            }

            .custom-select-wrapper {
                width: 100%;
                flex: none;
            }

            .btn-solicitar {
                width: 100%;
                padding: 14px 16px;
            }

            .btn-reserva {
                width: 100%;
                justify-content: center;
            }

            .panel-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
                margin-top: 18px;
            }

            .panel-card {
                padding: 12px 4px;
                min-height: 70px;
                font-size: 0.85rem;
            }

            .panel-card i {
                font-size: 1.1rem;
            }
        }

        /* ============================================================
           RESPONSIVE — MÓVIL PEQUEÑO (≤ 480px)
        ============================================================ */
        @media (max-width: 480px) {
            .hero-inner {
                padding: 36px 14px 30px;
            }

            .muevete-title {
                font-size: clamp(2.4rem, 16vw, 3.6rem);
            }

            .sin-limites-title {
                font-size: clamp(1.9rem, 13vw, 3rem);
            }

            .a-tu-ciudad-title {
                font-size: clamp(0.85rem, 5vw, 1.2rem);
            }

            .red-line {
                width: 18px;
            }

            .selection-card {
                padding: 16px 16px 20px;
                border-radius: 14px;
            }

            .selection-card .card-title {
                font-size: 1.5rem;
            }

            .panel-grid {
                gap: 6px;
                margin-top: 12px;
            }

            .panel-card {
                padding: 8px 2px;
                min-height: 50px;
                font-size: 0.75rem;
            }

            .panel-card i {
                font-size: 0.95rem;
            }
        }

        /* ============================================================
           RESPONSIVE — MÓVIL MUY PEQUEÑO (≤ 360px)
        ============================================================ */
        @media (max-width: 360px) {
            .panel-card {
                padding: 14px 4px;
                min-height: 90px;
                font-size: 0.88rem;
            }

            .panel-card i {
                font-size: 1.3rem;
            }
        }
    </style>
    </head>

    <body>

        <section class="hero">

            <div class="hero-overlay"></div>

            <div class="hero-inner">

                <div class="hero-title-row">
                    <div class="hero-title-group">
                        <!-- TÍTULOS -->
                        <h1 class="muevete-title">MUEVETE</h1>
                        <h2 class="sin-limites-title">SIN LÍMITES</h2>

                        <div class="a-tu-ciudad-row">
                            <span class="red-line"></span>
                            <h3 class="a-tu-ciudad-title">A TU CIUDAD</h3>
                            <span class="red-line"></span>
                        </div>
                    </div>

                    <div class="hero-cta-card">
                        <p>
                            Reserva tu viaje con facilidad y ve a tu destino con seguridad, rapidez y atención
                            confiable.
                            Ideal para recorridos diarios, traslados y transportes de carga con una experiencia moderna.
                        </p>

                        <p>
                            ¡No pierdas tiempo buscando transporte! Haz clic en el botón de reserva y asegura tu viaje
                            en pocos segundos.
                        </p>
                        <div class="hero-cta-actions">
                            <a href="reserva.php" class="btn" style="display: inline-flex; align-items: center; gap: 10px; white-space: nowrap; text-decoration: none; padding: 14px 34px; font-size: clamp(1.05rem, 1.9vw, 1.25rem); font-weight: bold; border-radius: 14px; box-shadow: 0 3px 12px rgba(243, 0, 0, 0.32); background-color: #f30000; color: #fff; border: none; transition: background-color 0.2s, transform 0.15s, box-shadow 0.2s;" onmouseover="this.style.backgroundColor='#c80000'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 18px rgba(243, 0, 0, 0.42)';" onmouseout="this.style.backgroundColor='#f30000'; this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 12px rgba(243, 0, 0, 0.32)';">
                                <i class="bi bi-calendar-check"></i>
                                Reserva ya
                            </a>
                        </div>
                    </div>
                </div>

                <!-- DESCRIPCIÓN -->
                <p class="hero-description" style="text-align: justify;">
                    Muévete por Quillabamba de manera segura, rápida y confiable con QuillaMovil.
                    Disfruta de un servicio moderno pensado para tu comodidad, con conductores verificados,
                    atención inmediata y precios accesibles para cada viaje. Solicita tu movilidad en
                    segundos y llega a tu destino sin preocupaciones, con la tranquilidad de viajar
                    acompañado por un servicio eficiente y disponible las 24 horas del día.
                </p>

                <!-- TARJETA DE SELECCIÓN -->
                <div class="selection-card">
                    <span class="card-title">Conductor Urbano</span>
                      <p class="hero-description" style="text-align: justify;">
                   Un conductor como tú, que están siempre listos para llevar 
                   a un pasajero, sin importar la zona ni el horario. Regístrate y 
                   conviértete en uno de los conductores más solicitados de Quillabamba:
                </p>
                    <label for="servicioSelect">Selecciona tu vehículo:</label>
                    <div class="select-btn-row">
                        <div class="custom-select-wrapper">
                            <select id="servicioSelect" class="custom-select-black">
                                <option value="" selected disabled>Elige una opción</option>
                                <option value="torito">Torito</option>
                                <option value="motocarga">Motocarga</option>
                                <option value="camion">Camión</option>
                            </select>
                            <span class="custom-select-arrow">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 8L10 13L15 8" stroke="#fff" stroke-width="2.2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <a id="btnSolicitarServicio" href="#" class="btn btn-danger btn-link-disabled" style="padding: 12px 30px; font-size: clamp(1rem, 2vw, 1.25rem); font-weight: bold; box-shadow: 0 3px 12px rgba(243, 0, 0, 0.3); transition: background 0.2s, opacity 0.2s, transform 0.15s, box-shadow 0.2s; flex: 0 0 auto; white-space: nowrap; display: inline-block; border-radius: 14px;">
                            Registrar
                        </a>
                    </div>
                </div>

                <!-- PANEL CARDS -->
                <div class="panel-grid"
                    style="display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; align-items: center;">
                    <div class="panel-card">
                        <i class="bi bi-shield-lock"></i>
                        Seguro
                    </div>
                    <div class="panel-card">
                        <i class="bi bi-lightning-charge"></i>
                        Rápido
                    </div>
                    <div class="panel-card">
                        <i class="bi bi-patch-check"></i>
                        Confiable
                    </div>
                    <div class="panel-card">
                        <i class="bi bi-star-fill"></i>
                        Calidad
                    </div>
                </div>

            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var select = document.getElementById('servicioSelect');
                var btnSolicitar = document.getElementById('btnSolicitarServicio');

                var rutas = {
                    torito: 'torito.php',
                    motocarga: 'motocarga.php',
                    camion: 'camion.php'
                };

                select.addEventListener('change', function () {
                    var isValid = rutas.hasOwnProperty(select.value);
                    if (isValid) {
                        btnSolicitar.href = rutas[select.value];
                        btnSolicitar.classList.remove('btn-link-disabled');
                    } else {
                        btnSolicitar.href = '#';
                        btnSolicitar.classList.add('btn-link-disabled');
                    }
                });
            });
        </script>

    </body>

    </html>
</section>





<!-- Sección Cómo Funciona -->
<section id="como-funciona" class="como-funciona-section" style="background: #000000; padding: 60px 0;">
    <div class="container">
        <div class="d-flex align-items-center justify-content-center mb-5" style="gap: 18px;">
            <span
                style="display: inline-block; height: 3px; width: 40px; background: #f30000; border-radius: 2px;"></span>
            <h2 class="text-center m-0" style="color: #f30000; font-weight: bold;">COMO FUNCIONA QUILLAMOVIL</h2>
            <span
                style="display: inline-block; height: 3px; width: 40px; background: #f30000; border-radius: 2px;"></span>
        </div>

        <div class="row justify-content-center align-items-stretch g-4"> <!-- ← align-items-stretch y g-4 -->
            <div class="col-12 col-sm-6 col-md-4"> <!-- ← col-sm-6 para tablets -->
                <div class="como-funciona-card h-100">
                    <i class="bi bi-phone" style="font-size: 2.2rem; color: #f30000;"></i>
                    <h4 class="mt-3 mb-2" style="font-weight: bold; color: #fff;">Registrate</h4>
                    <p style="color: #fff;">Crea tu cuenta en pocos minutos con tus datos personales.
                        </p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="como-funciona-card">
                    <i class="bi bi-person-check" style="font-size: 2.2rem; color: #f30000;"></i>
                    <h4 class="mt-3 mb-2" style="font-weight: bold; color: #fff;">Solicita</h4>
                    <p style="color: #fff;"> Elige el tipo de vehículo
                         Torito, Motocarga o Camión según lo que necesites.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="como-funciona-card">
                    <i class="bi bi-geo-alt" style="font-size: 2.2rem; color: #f30000;"></i>
                    <h4 class="mt-3 mb-2" style="font-weight: bold; color: #fff;">Llega seguro</h4>
                    <p style="color: #fff;"> Viaja con confianza, rastreando tu vehículo en tiempo real.</p>
                </div>
            </div>
             <div class="col-12 col-sm-6 col-md-4">
                <div class="como-funciona-card h-100">
                    <i class="bi bi-person-plus-fill" style="font-size: 2.2rem; color: #f30000;"></i>
                    <h4 class="mt-3 mb-2" style="font-weight: bold; color: #fff;">Registrar como Conductor</h4>
                    <p style="color: #fff;"> Sube tu DNI, datos del vehículo y documentación.
                        Marca tu estado como "Activo" para empezar a recibir solicitudes.
                    </p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="como-funciona-card">
                    <i class="bi bi-box-arrow-in-right" style="font-size: 2.2rem; color: #f30000;"></i>
                    <h4 class="mt-3 mb-2" style="font-weight: bold; color: #fff;">Ingresa como Conductor</h4>
                    <p style="color: #fff;">Ingresa como conductor con tu número de DNI para acceder a tu panel.
                         Sin necesidad de contraseñas complicadas: 
                        tu DNI te identifica y te da acceso directo a tus solicitudes.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="como-funciona-card">
                    <i class="bi bi-clipboard-check" style="font-size: 2.2rem; color: #f30000;"></i>
                    <h4 class="mt-3 mb-2" style="font-weight: bold; color: #fff;">Administra tus solicitudes</h4>
                    <p style="color: #fff;">Acepta o rechaza solicitudes, revisa el origen/destino de cada viaje, 
                        y lleva el control de tus pasajeros activos y completados.</p>
                </div>
            </div>
             <div class="col-12 col-sm-6 col-md-4">
                <div class="como-funciona-card">
                    <i class="bi bi-calendar2-week" style="font-size: 2.2rem; color: #f30000;"></i>
                    <h4 class="mt-3 mb-2" style="font-weight: bold; color: #fff;">Conductor Reserva </h4>
                    <p style="color: #fff;">Para viajes programados con anticipación traslados a otras ciudades, eventos, horarios fijos.

El pasajero agenda el viaje con fecha y hora específica.
El conductor confirma disponibilidad antes del día.
Ideal para: viajes largos, traslados a Cusco/Machu Picchu, grupos, equipaje grande.</p>
                </div>
            </div>
             <div class="col-12 col-sm-6 col-md-4">
                <div class="como-funciona-card">
                    <i class="bi bi-signpost-split" style="font-size: 2.2rem; color: #f30000;"></i>
                    <h4 class="mt-3 mb-2" style="font-weight: bold; color: #fff;">Conductor Urbano</h4>
                    <p style="color: #fff;">Para viajes inmediatos dentro de Quillabamba, tipo "pide y llega".

El pasajero solicita el vehículo y lo recibe en minutos.
El conductor debe estar "Activo" y cercano a la zona.
Ideal para: mandados rápidos, movilidad diaria, Torito/Motocarga.</p>
                </div>
            </div>
        </div>
    </div>

   


    <!-- Tarjetas de Nuestros Servicios -->
 

    <section class="nuestros-servicios-section" style="background: #000000; padding: 0 0 60px 0;">
<div class="d-flex align-items-center justify-content-center mb-4" style="gap: 18px; margin-top: 18px; margin-bottom: 40px;">
        <span style="display: inline-block; height: 3px; width: 40px; background: #f30000; border-radius: 2px;"></span>
        <h2 class="text-center m-0" style="color: #f30000; font-weight: bold;">NUESTROS SERVICIOS</h2>
        <span style="display: inline-block; height: 3px; width: 40px; background: #f30000; border-radius: 2px;"></span>
    </div>

        <div class="container">
            <div class="row justify-content-center">

                <div class="col-12 col-md-3 mb-4">
                    <div
                        style="background: rgba(0,0,0,0.65); border-radius: 22px; box-shadow: 0 4px 18px rgba(184, 37, 37, 0.13); padding: 0 0 28px 0; text-align: center; border: 2.5px solid #333131; min-height: 260px; max-width: 260px; margin: 0 auto; overflow: hidden;">
                        <div style="width: 100%; height: 140px; overflow: hidden;">
                            <img src="torito-azul.png" alt="Torito"
                                style="width: 100%; height: 100%; object-fit: cover; display: block; margin: 0; border-radius: 0;">
                        </div>
                        <h5 style="color: #e70303; font-weight: bold; margin-top: 18px; font-size: 1.35rem;">Motocarga
                        </h5>
                        <p style="color: #fff;">Ideal para viajes cortos y rápidos</p>
                    </div>
                </div>

                <div class="col-12 col-md-3 mb-4">
                    <div
                        style="background: rgba(0,0,0,0.65); border-radius: 22px; box-shadow: 0 4px 18px rgba(184, 37, 37, 0.13); padding: 0 0 28px 0; text-align: center; border: 2.5px solid #333131; min-height: 260px; max-width: 260px; margin: 0 auto; overflow: hidden;">
                        <div style="width: 100%; height: 140px; overflow: hidden;">
                            <img src="motocarga-verde.png" alt="Motocarga"
                                style="width: 100%; height: 100%; object-fit: cover; display: block; margin: 0; border-radius: 0;">
                        </div>
                        <h5 style="color: #e70303; font-weight: bold; margin-top: 18px; font-size: 1.35rem;">Motocarga
                        </h5>
                        <p style="color: #fff;">Transporte de carga mediana</p>
                    </div>
                </div>

                <div class="col-12 col-md-3 mb-4">
                    <div
                        style="background: rgba(0,0,0,0.65); border-radius: 22px; box-shadow: 0 4px 18px rgba(228, 11, 11, 0.13); padding: 0 0 28px 0; text-align: center; border: 2.5px solid #333131; min-height: 260px; max-width: 260px; margin: 0 auto; overflow: hidden;">
                        <div style="width: 100%; height: 140px; overflow: hidden;">
                            <img src="camion-rojo.png" alt="Camión"
                                style="width: 100%; height: 100%; object-fit: cover; display: block; margin: 0; border-radius: 0;">
                        </div>
                        <h5 style="color: #e70303; font-weight: bold; margin-top: 18px; font-size: 1.35rem;">Camión</h5>
                        <p style="color: #fff;">Transporte de carga más grande</p>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-4">
                    <div
                        style="background: rgba(0,0,0,0.65); border-radius: 22px; box-shadow: 0 4px 18px rgba(179, 19, 19, 0.13); padding: 0 0 28px 0; text-align: center; border: 2.5px solid #333131; min-height: 260px; max-width: 260px; margin: 0 auto; overflow: hidden;">
                        <div style="width: 100%; height: 140px; overflow: hidden;">
                            <img src="camioneta-dorado.png" alt="camioneta"
                                style="width: 100%; height: 100%; object-fit: cover; display: block; margin: 0; border-radius: 0;">
                        </div>
                        <h5 style="color: #e70303; font-weight: bold; margin-top: 18px; font-size: 1.35rem;">Carro</h5>
                        <p style="color: #fff;">Viajes cómodos para tu familia</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Tarjeta de iconos de ancho completo -->
        <div class="container mb-5">
            <div class="card" style="background: rgba(0,0,0,0.85); border-radius: 22px; border: 2.5px solid #f30000;">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="padding: 28px 0;">
                    <div class="flex-fill d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-geo-alt" style="font-size: 2.5rem; color: #f30000;"></i>
                        <span class="text-white fw-bold" style="font-size: 1.2rem;">Llega a tu destino</span>
                    </div>
                    <div style="width:2px; background:#f30000; height:48px;"></div>
                    <div class="flex-fill d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-person-check" style="font-size: 2.5rem; color: #f30000;"></i>
                        <span class="text-white fw-bold" style="font-size: 1.2rem;">Conductores verificados</span>
                    </div>
                    <div style="width:2px; background:#f30000; height:48px;"></div>
                    <div class="flex-fill d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-shield-lock" style="font-size: 2.5rem; color: #f30000;"></i>
                        <span class="text-white fw-bold" style="font-size: 1.2rem;">Viaja seguro</span>
                    </div>
                    <div style="width:2px; background:#f30000; height:48px;"></div>
                    <div class="flex-fill d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-star-fill" style="font-size: 2.5rem; color: #f30000;"></i>
                        <span class="text-white fw-bold" style="font-size: 1.2rem;">Calidad garantizada</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="container mb-5">
            <div class="card"
                style="background: rgba(0,0,0,0.85); border-radius: 22px; border: 2.5px solid #6b5f5f; padding: 32px 24px;">
                <h3 class=" fw-bold mb-3 text-center" style="color: #f30000;">COBERTURA EN QUILLABAMBA</h3>
                <p class="text-white" style="font-size: 1.1rem;">Llegamos a todos los rincones de la ciudad de
                    quillabamba.</p>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <ul class="text-white" style="font-size: 1.05rem; margin-bottom: 1.5rem; text-align: left;">
                            <li>Plaza de Armas de Quillabamba</li>
                            <li>Plaza Grau</li>
                            <li>Plaza Bolivar</li>
                            <li>Plaza del Campesino</li>
                            <li>Plazotea Santa Ana</li>
                            <li>Parque la Convecion</li>
                            <li>Piscina de Torrechayoc</li>
                            <li>Balneario de Sambaray</li>
                            <li>Macamango</li>

                        </ul>
                    </div>
                    <div class="col-12 col-md-6">
                        <ul class="text-white"
                            style="font-size: 1.05rem; margin-bottom: 1.5rem; list-style-position: inside;">
                            <li>Santo Domingo Park</li>
                            <li>Reserva Natural de Tunkimayo</li>
                            <li>Catarata de Yapay</li>
                            <li>Pavayoc Park</li>
                            <li>Mercado Mayorista</li>
                            <li>Hospital de Quillabamba</li>
                            <li>Terminal Terrestre</li>
                            <li>Entre otros</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-4"
                    style="width: 100%; min-height: 320px; border-radius: 16px; overflow: hidden; border: 2px solid #645454;">
                    <iframe src="https://www.google.com/maps?q=Quillabamba,+Peru&output=embed" width="100%" height="320"
                        style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>

               

            </div>
 <!-- Tarjeta Eres Conductor de Reserva -->
                <div class="mt-5 card" style="background: rgba(0,0,0,0.85); border-radius: 22px; border: 2.5px solid #f30000; padding: 36px 28px;">
                    <h3 class="fw-bold mb-4 text-center" style="color: #f30000; font-size: 1.8rem;">Eres Conductor de Reserva</h3>
                    
                    <p class="text-white mb-3" style="font-size: 1.05rem; line-height: 1.8; text-align: justify;">
                        Si te registrasre como conductor de reserva, podrás recibir solicitudes de servicio de manera rápida y eficiente.
                         Asegúrate de tener tu vehículo listo y estar disponible para brindar un servicio confiable a nuestros usuarios.
                         
                    </p>

                    <p class="text-white mb-3 text-center" style="font-size: 1.05rem; line-height: 1.8; text-align: justify;">
                        Haz clic aqui.
                    </p>
                    
                   
                    
                    <div class="d-flex justify-content-center">
                        <a href="login-conductor.php" class="btn" style="display: inline-flex; align-items: center; gap: 10px; white-space: nowrap; text-decoration: none; padding: 14px 40px; font-size: 1.15rem; font-weight: bold; border-radius: 14px; box-shadow: 0 3px 12px rgba(243, 0, 0, 0.32); background-color: #f30000; color: #fff; border: none; transition: background-color 0.2s, transform 0.15s, box-shadow 0.2s;" onmouseover="this.style.backgroundColor='#c80000'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 18px rgba(243, 0, 0, 0.42)';" onmouseout="this.style.backgroundColor='#f30000'; this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 12px rgba(243, 0, 0, 0.32)';">
                            <i class="bi bi-briefcase"></i>
                            Solicitud de Servicio
                        </a>
                    </div>
                </div>

        </div>
    </section>
    <!-- Tarjeta de cobertura de Quillabamba -->

</section>

<!-- Botón Flotante de WhatsApp -->
<style>
    .whatsapp-float {
        position: fixed;
        width: 60px;
        height: 60px;
        bottom: 30px;
        right: 30px;
        background-color: #25d366;
        color: white;
        border-radius: 50%;
        text-align: center;
        font-size: 30px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .whatsapp-float:hover {
        background-color: #20ba5e;
        transform: scale(1.1);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .whatsapp-float i {
        font-size: 32px;
    }
</style>

<a href="https://wa.me/51988966199?text=Hola,%20te%20contacto%20desde%20QuillaMovil" target="_blank"
    class="whatsapp-float" title="Contactar por WhatsApp">
    <i class="bi bi-whatsapp"></i>
</a>

<?php include "footer.php"; ?>