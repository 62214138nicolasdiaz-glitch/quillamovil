<?php
session_start();
require 'functions.php';
require 'database.php';
redirectIfNotLogged('dashboard.php');

// Totales generales
$totals = [
    'toritos' => 0,
    'conductores' => 0,
    'camiones' => 0,
    'motocargas' => 0,
    'reservas' => 0,
    'usuarios' => 0,
];

$queries = [
    'toritos' => "SELECT COUNT(*) AS total FROM toro",
    'conductores' => "SELECT COUNT(*) AS total FROM conductor",
    'camiones' => "SELECT COUNT(*) AS total FROM camion",
    'motocargas' => "SELECT COUNT(*) AS total FROM motocarga",
    'reservas' => "SELECT COUNT(*) AS total FROM reservas",
    'usuarios' => "SELECT COUNT(*) AS total FROM users",
];

foreach ($queries as $key => $sql) {
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $totals[$key] = intval($row['total']);
    }
}

$estadoCounts = [
    'activo' => 0,
    'en camino' => 0,
    'ocupado' => 0,
];
$result = mysqli_query($conn, "SELECT LOWER(TRIM(estado)) AS estado, COUNT(*) AS total FROM toro GROUP BY LOWER(TRIM(estado))");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $estado = $row['estado'];
        if (isset($estadoCounts[$estado])) {
            $estadoCounts[$estado] = intval($row['total']);
        }
    }
}

$reservasRecientes = [];
$result = mysqli_query($conn, "SELECT id, nombre_completo, origen, destino, fecha_reserva, hora_reserva, status FROM reservas ORDER BY fecha_reserva DESC, hora_reserva DESC LIMIT 6");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $reservasRecientes[] = $row;
    }
}

$toritosRecientes = [];
$result = mysqli_query($conn, "SELECT id, dni, nombre, apellido, estado FROM toro ORDER BY id DESC LIMIT 5");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $toritosRecientes[] = $row;
    }
}

$userName = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'Administrador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard QuillaMovil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: dark;
            color: #edf1f7;
            background-color: #020305;
            font-family: 'Poppins', sans-serif;
            --surface: rgba(8, 10, 15, 0.96);
            --surface-soft: rgba(24, 26, 34, 0.96);
            --border: rgba(255, 255, 255, 0.08);
            --border-strong: rgba(255, 255, 255, 0.14);
            --accent-red: #e11d23;
            --accent-orange: #c00114;
            --accent-yellow: #ffd500;
            --accent-green: #36d07b;
            --accent-grey: #8f98a6;
            --text-muted: #a6b0c3;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top left, rgba(192, 1, 20, 0.12), transparent 18%),
                        radial-gradient(circle at bottom right, rgba(225, 29, 35, 0.12), transparent 20%),
                        linear-gradient(180deg, #020305 0%, #05070d 45%, #0f131d 100%);
            color: #f5f7fb;
        
        .dashboard-shell {
            width: min(1240px, calc(100% - 2rem));
            margin: 0 auto;
            padding: 2rem 0 4rem;
        }
        .dashboard-top {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1.5rem;
            align-items: start;
            margin-bottom: 2rem;
        }
        .dashboard-top h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(2.25rem, 3vw, 3.5rem);
            margin: 0 0 0.5rem;
            letter-spacing: -0.04em;
            color: #fff;
        }
        .dashboard-top p {
            margin: 0;
            color: var(--text-muted);
            max-width: 730px;
            line-height: 1.75;
        }
        .user-card {
            min-width: 240px;
            border: 1px solid var(--border);
            background: rgba(16, 18, 24, 0.94);
            border-radius: 26px;
            padding: 1.35rem 1.4rem;
            box-shadow: 0 24px 80px rgba(0, 0, 0, 0.24);
            backdrop-filter: blur(16px);
        }
        .user-card h2 { margin: 0 0 0.35rem; font-size: 1.18rem; font-weight: 700; color: #fff; }
        .user-card p { margin: 0; color: var(--text-muted); }
        .label-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #f8f9fd;
            font-size: 0.92rem;
            margin-top: 1rem;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }
        .metric-card {
            background: linear-gradient(135deg, rgba(16, 18, 24, 0.9), rgba(32, 34, 42, 0.88));
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 28px;
            padding: 1.5rem 1.6rem;
            min-height: 170px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            position: relative;
        }
        .metric-card::before {
            content: '';
            position: absolute;
            top: -16px;
            right: -16px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            filter: blur(18px);
        }
        .metric-card strong {
            display: block;
            font-size: 2.5rem;
            line-height: 1;
            margin-bottom: 0.35rem;
        }
        .metric-card small {
            text-transform: uppercase;
            letter-spacing: 0.16em;
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        .metric-card .metric-icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,0.09);
            color: #fff;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.08);
        }
        .metric-card .metric-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .metric-bubble {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.75rem;
            border-radius: 999px;
            font-size: 0.85rem;
            color: #f5f7fb;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .metric-card[data-theme='red'] { background: linear-gradient(135deg, rgba(225, 29, 35, 0.44), rgba(18, 10, 12, 0.94)); }
        .metric-card[data-theme='red'] .metric-icon { background: rgba(225, 29, 35, 0.24); }
        .metric-card[data-theme='orange'] { background: linear-gradient(135deg, rgba(192, 1, 20, 0.38), rgba(34, 22, 8, 0.92)); }
        .metric-card[data-theme='orange'] .metric-icon { background: rgba(192, 1, 20, 0.24); }
        .metric-card[data-theme='yellow'] { background: linear-gradient(135deg, rgba(255, 213, 0, 0.34), rgba(36, 34, 12, 0.92)); }
        .metric-card[data-theme='yellow'] .metric-icon { background: rgba(255, 213, 0, 0.24); }
        .metric-card[data-theme='green'] { background: linear-gradient(135deg, rgba(54, 208, 123, 0.34), rgba(14, 32, 22, 0.92)); }
        .metric-card[data-theme='green'] .metric-icon { background: rgba(54, 208, 123, 0.20); }
        .metric-card[data-theme='grey'] { background: linear-gradient(135deg, rgba(143, 152, 166, 0.32), rgba(22, 24, 32, 0.92)); }
        .metric-card[data-theme='grey'] .metric-icon { background: rgba(143, 152, 166, 0.20); }
        .details-grid {
            display: grid;
            grid-template-columns: 2fr 1.15fr;
            gap: 1.5rem;
            margin-bottom: 1.75rem;
        }
        .panel {
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 28px;
            background: var(--surface);
            padding: 1.6rem;
            box-shadow: 0 24px 52px rgba(0,0,0,0.2);
        }
        .panel h2 { margin-top: 0; margin-bottom: 1.2rem; font-size: 1.15rem; letter-spacing: -0.02em; }
        .progress-segment { display: grid; gap: 1rem; }
        .progress-row { display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 1rem; }
        .progress-bar-wrap { height: 14px; border-radius: 999px; background: rgba(255,255,255,0.06); overflow: hidden; }
        .progress-bar-fill { height: 100%; border-radius: 999px; }
        .progress-label { display: inline-flex; gap: 0.4rem; align-items: center; font-size: 0.95rem; font-weight: 600; color: #eef2ff; }
        .progress-pill { display: inline-block; min-width: 56px; text-align: center; border-radius: 999px; font-size: 0.82rem; padding: 0.25rem 0.55rem; background: rgba(255,255,255,0.08); color: var(--text-muted); }
        .record-table { width: 100%; border-collapse: collapse; color: #e9eff5; }
        .record-table th, .record-table td { padding: 0.95rem 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.08); text-align: left; }
        .record-table thead th { color: #bec8da; font-weight: 600; border-bottom: 1px solid rgba(255,255,255,0.12); }
        .record-table tbody tr:hover { background: rgba(255,255,255,0.04); }
        .record-table td.status { font-weight: 700; text-transform: capitalize; }
        .status-chip { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.78rem; font-weight: 700; padding: 0.45rem 0.65rem; border-radius: 999px; color: #fff; background: rgba(255,255,255,0.07); }
        .status-chip.activo { background: rgba(57, 229, 141, 0.18); color: #c5ffde; }
        .status-chip.en-camino { background: rgba(255, 199, 79, 0.18); color: #fff2b8; }
        .status-chip.ocupado { background: rgba(255, 106, 106, 0.18); color: #ffd6d6; }
        .quick-actions { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
        .action-card { display: flex; flex-direction: column; justify-content: space-between; min-height: 160px; border-radius: 24px; padding: 1.4rem; background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.01)); border: 1px solid rgba(255,255,255,0.06); transition: transform 0.2s ease, border-color 0.2s ease; }
        .action-card:hover { transform: translateY(-3px); border-color: rgba(255,255,255,0.14); }
        .action-card a { color: #fff; text-decoration: none; }
        .action-card h3 { margin: 0 0 0.75rem; font-size: 1.05rem; line-height: 1.3; }
        .action-card p { margin: 0; color: var(--text-muted); line-height: 1.7; }
        .action-card .action-link { display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1rem; color: #f6f9ff; font-weight: 600; }
        @media (max-width: 1024px) {
            .details-grid { grid-template-columns: 1fr; }
            .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .quick-actions { grid-template-columns: 1fr; }
        }
        @media (max-width: 700px) {
            .dashboard-top { grid-template-columns: 1fr; }
            .metric-card { min-height: unset; }
            .stat-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="dashboard-shell">
        <div class="dashboard-top">
            <div>
                <p class="label-badge">Dashboard operativo</p>
                <h1>Control total de QuillaMovil</h1>
                <p>Visualiza el estado actual de conductores, vehículos y reservas con un estilo dinámico y de fácil lectura. Esta vista te ayuda a tomar decisiones rápidas y precisas.</p>
            </div>
            <div class="user-card">
                <h2>Bienvenido, <?php echo htmlspecialchars($userName); ?></h2>
                <p>Panel administrativo con métricas en tiempo real, accesos directos y seguimiento de reservaciones.</p>
                <div class="label-badge" style="margin-top: 1.25rem;">
                    <i class="bi bi-speedometer2"></i> Enfocado en rendimiento
                </div>
            </div>
        </div>

        <section class="stat-grid">
            <div class="metric-card" data-theme="red">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small>Conductores registrados</small>
                        <strong><?php echo number_format($totals['conductores']); ?></strong>
                    </div>
                    <span class="metric-icon"><i class="bi bi-people-fill"></i></span>
                </div>
                <div class="metric-footer">
                    <span class="metric-bubble">+ confiable</span>
                    <small>Actualizado siempre</small>
                </div>
            </div>
            <div class="metric-card" data-theme="blue">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small>Reservas activas</small>
                        <strong><?php echo number_format($totals['reservas']); ?></strong>
                    </div>
                    <span class="metric-icon"><i class="bi bi-calendar-check-fill"></i></span>
                </div>
                <div class="metric-footer">
                    <span class="metric-bubble">Prioridad alta</span>
                    <small>Seguimiento instantáneo</small>
                </div>
            </div>
            <div class="metric-card" data-theme="green">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small>Flota total</small>
                        <strong><?php echo number_format($totals['toritos'] + $totals['camiones'] + $totals['motocargas']); ?></strong>
                    </div>
                    <span class="metric-icon"><i class="bi bi-truck-flatbed"></i></span>
                </div>
                <div class="metric-footer">
                    <span class="metric-bubble">Cobertura amplia</span>
                    <small>Toritos, camiones y motos</small>
                </div>
            </div>
        </section>

        <section class="details-grid">
            <div class="panel">
                <h2>Estado de disponibilidad de toritos</h2>
                <div class="progress-segment">
                    <?php foreach (['activo' => '#3de28c', 'en camino' => '#ffd55a', 'ocupado' => '#ff7070'] as $estado => $color): ?>
                        <?php $count = $estadoCounts[$estado] ?? 0; ?>
                        <?php $percentage = $totals['toritos'] > 0 ? round($count / $totals['toritos'] * 100) : 0; ?>
                        <div class="progress-row">
                            <div>
                                <div class="progress-label"><span class="badge rounded-pill" style="background: <?php echo $color; ?>22; color: <?php echo $color; ?>;">&nbsp;</span> <?php echo ucfirst($estado); ?></div>
                                <div class="progress-bar-wrap" aria-hidden="true">
                                    <div class="progress-bar-fill" style="width: <?php echo $percentage; ?>%; background: <?php echo $color; ?>;"></div>
                                </div>
                            </div>
                            <div class="progress-pill"><?php echo $count; ?> / <?php echo $percentage; ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="panel">
                <h2>Reservas recientes</h2>
                <?php if (count($reservasRecientes) === 0): ?>
                    <p style="color: var(--text-muted);">No hay reservas recientes en este momento.</p>
                <?php else: ?>
                    <div class="table-responsive" style="overflow-x:auto;">
                        <table class="record-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservasRecientes as $reserva): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($reserva['id']); ?></td>
                                        <td><?php echo htmlspecialchars($reserva['nombre_completo']); ?></td>
                                        <td><?php echo htmlspecialchars($reserva['origen']); ?></td>
                                        <td><?php echo htmlspecialchars($reserva['destino']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($reserva['fecha_reserva'] . ' ' . $reserva['hora_reserva'])); ?></td>
                                        <td class="status"><span class="status-chip <?php echo str_replace(' ', '-', strtolower($reserva['status'])); ?>"><?php echo htmlspecialchars($reserva['status']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="details-grid">
            <div class="panel">
                <h2>Resumen de la flota</h2>
                <div class="row gy-3">
                    <div class="col-12 col-md-6">
                        <div class="metric-card" style="padding: 1.25rem; border-radius: 22px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.06);">
                            <small>Toritos</small>
                            <strong><?php echo number_format($totals['toritos']); ?></strong>
                            <p style="color: var(--text-muted); margin: .75rem 0 0;">Vehículos compactos listos para desplazarse por la ciudad.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="metric-card" style="padding: 1.25rem; border-radius: 22px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.06);">
                            <small>Camiones</small>
                            <strong><?php echo number_format($totals['camiones']); ?></strong>
                            <p style="color: var(--text-muted); margin: .75rem 0 0;">Unidades de mayor capacidad para servicios especiales.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="metric-card" style="padding: 1.25rem; border-radius: 22px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.06);">
                            <small>Motocargas</small>
                            <strong><?php echo number_format($totals['motocargas']); ?></strong>
                            <p style="color: var(--text-muted); margin: .75rem 0 0;">Movilidad ágil para entregas y traslados ligeros.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="metric-card" style="padding: 1.25rem; border-radius: 22px; background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.06);">
                            <small>Usuarios</small>
                            <strong><?php echo number_format($totals['usuarios']); ?></strong>
                            <p style="color: var(--text-muted); margin: .75rem 0 0;">Cuentas registradas con acceso al sistema.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel">
                <h2>Últimos toritos añadidos</h2>
                <?php if (count($toritosRecientes) === 0): ?>
                    <p style="color: var(--text-muted);">Aún no se han registrado toritos nuevos.</p>
                <?php else: ?>
                    <div class="table-responsive" style="overflow-x:auto;">
                        <table class="record-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>DNI</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($toritosRecientes as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                                        <td><?php echo htmlspecialchars($item['nombre'] . ' ' . $item['apellido']); ?></td>
                                        <td><?php echo htmlspecialchars($item['dni']); ?></td>
                                        <td class="status"><span class="status-chip <?php echo str_replace(' ', '-', strtolower($item['estado'])); ?>"><?php echo htmlspecialchars($item['estado']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="quick-actions">
            <div class="action-card">
                <div>
                    <h3>Ver lista de toritos</h3>
                    <p>Accede rápidamente a la gestión y actualización de estados de los toritos disponibles.</p>
                </div>
                <a class="action-link" href="listar.php">Abrir lista <i class="bi bi-arrow-right-short"></i></a>
            </div>
            <div class="action-card">
                <div>
                    <h3>Reservas completas</h3>
                    <p>Revisa las reservas recientes y mantén el control de las solicitudes más recientes.</p>
                </div>
                <a class="action-link" href="listar-reserva.php">Ver reservas <i class="bi bi-arrow-right-short"></i></a>
            </div>
            <div class="action-card">
                <div>
                    <h3>Configuración rápida</h3>
                    <p>Administra usuarios y ajustes de acceso desde una sola ubicación.</p>
                </div>
                <a class="action-link" href="usuarios.php">Gestionar usuarios <i class="bi bi-arrow-right-short"></i></a>
            </div>
        </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
