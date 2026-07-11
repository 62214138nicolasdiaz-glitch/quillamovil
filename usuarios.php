<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php?redirect=usuarios.php');
    exit;
}

include 'database.php';

$query = "SELECT id, username, email, oauth_provider FROM users ORDER BY id ASC";
$result = mysqli_query($conn, $query);

$totalUsers = $result ? mysqli_num_rows($result) : 0;
$localCount = 0;
$googleCount = 0;
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['oauth_provider'])) {
            $googleCount++;
        } else {
            $localCount++;
        }
    }
    mysqli_data_seek($result, 0);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios | QuillaMovil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="estilos.css">
    <style>
        body {
            background: #000;
            color: #ffffff;
        }
        h1, h2, h3, h4, h5, h6 {
            color: #ffffff !important;
        }
        p {
            color: #ffffff !important;
        }
        .usuarios-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .usuarios-header h1 {
            margin: 0;
            color: #ffffff;
        }
        .usuarios-header p {
            color: #ffffff;
        }
        .usuarios-card {
            border-radius: 16px;
            padding: 1.5rem 1.3rem;
            min-height: 130px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            border: 2px solid;
        }
        .usuarios-card small {
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 600;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .usuarios-card small i {
            font-size: 1.1rem;
        }
        .usuarios-card strong {
            display: block;
            font-size: 2.4rem;
            margin-top: 0.6rem;
            color: #ffffff;
            font-weight: 700;
        }
        /* Tarjeta 1: Total (Verde) */
        .usuarios-card-total {
            background: linear-gradient(135deg, #1b4d3e 0%, #2d7a5f 100%);
            border-color: #4db899;
        }
        /* Tarjeta 2: Locales (Naranja) */
        .usuarios-card-local {
            background: linear-gradient(135deg, #5c3517 0%, #9e5d1a 100%);
            border-color: #ff9800;
        }
        /* Tarjeta 3: Google (Azul) */
        .usuarios-card-google {
            background: linear-gradient(135deg, #1a3a52 0%, #2d5a7f 100%);
            border-color: #4285f4;
        }
        .table-custom {
            background: #111 !important;
            border: 2px solid #c62828 !important;
        }
        .table-custom th,
        .table-custom td {
            border: 1px solid #c62828 !important;
            color: #ffffff !important;
            vertical-align: middle !important;
        }
        .table-custom thead th {
            background: #1f1f1f !important;
            color: #ffffff !important;
            font-weight: 700 !important;
        }
        .table-custom tbody tr {
            background: #000 !important;
        }
        .table-custom tbody tr:hover {
            background: rgba(200, 40, 40, 0.1) !important;
        }
        .table-custom tbody td {
            color: #ffffff !important;
        }
        #tablaUsuarios {
            background: #111 !important;
        }
        #tablaUsuarios th,
        #tablaUsuarios td {
            color: #ffffff !important;
            border-color: #c62828 !important;
            background: transparent !important;
        }
        #tablaUsuarios thead th {
            background: #1f1f1f !important;
            color: #ffffff !important;
        }
        #tablaUsuarios tbody tr {
            background: #000 !important;
        }
        #tablaUsuarios tbody tr:hover {
            background: rgba(200, 40, 40, 0.1) !important;
        }
        .badge-provider {
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-size: 0.7rem;
            font-weight: 600;
            color: #ffffff !important;
        }
        .badge-local {
            background: #d60000;
            color: #ffffff;
        }
        .badge-google {
            background: #4285f4;
            color: #ffffff;
        }
        .table-search {
            max-width: 420px;
        }
        .table-search .form-control {
            min-height: 46px;
            color: #000 !important;
        }
        .text-muted-light {
            color: #ffffff;
            opacity: 0.85;
        }
        .btn-outline-light {
            color: #ffffff;
            border-color: #ffffff;
        }
        .btn-outline-light:hover {
            background-color: #ffffff;
            color: #000;
        }
    </style>
</head>
<body class="listar-bg">
    <div class="container py-5">
        <div class="usuarios-header">
            <div>
                <h1>Usuarios de QuillaMovil</h1>
                <p class="text-muted-light mb-0">Ventana de administración para revisar cuentas de acceso y proveedores OAuth.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-light">Volver</a>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
            <div class="col">
                <div class="usuarios-card usuarios-card-total">
                    <small><i class="bi bi-people-fill"></i> Total de cuentas</small>
                    <strong><?php echo $totalUsers; ?></strong>
                </div>
            </div>
            <div class="col">
                <div class="usuarios-card usuarios-card-local">
                    <small><i class="bi bi-shield-lock-fill"></i> Cuentas locales</small>
                    <strong><?php echo $localCount; ?></strong>
                </div>
            </div>
            <div class="col">
                <div class="usuarios-card usuarios-card-google">
                    <small><i class="bi bi-google"></i> Acceso Google</small>
                    <strong><?php echo $googleCount; ?></strong>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
            <div>
                <h2 class="h4 mb-1" style="color: #ffffff;">Lista de usuarios</h2>
                <p class="text-muted-light mb-0" style="color: #ffffff;">Busca por nombre de usuario, correo o proveedor de inicio de sesión.</p>
            </div>
            <div class="table-search w-100 w-md-auto">
                <div class="input-group">
                    <span class="input-group-text bg-white text-dark border-danger"><i class="bi bi-search"></i></span>
                    <input type="search" id="searchUsuarios" class="form-control bg-white text-dark border-danger" placeholder="Buscar usuarios...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-custom" id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Proveedor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email'] ?: '-'); ?></td>
                                <td>
                                    <?php if (!empty($row['oauth_provider'])): ?>
                                        <span class="badge badge-provider badge-google"><?php echo htmlspecialchars($row['oauth_provider']); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-provider badge-local">Local</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted-light py-4">No se encontraron usuarios registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('searchUsuarios').addEventListener('input', function() {
            const filter = this.value.trim().toLowerCase();
            document.querySelectorAll('#tablaUsuarios tbody tr').forEach(function(row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
            });
        });
    </script>
</body>
</html>
