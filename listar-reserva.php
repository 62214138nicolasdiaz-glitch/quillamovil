<?php
session_start();
require 'functions.php';
require 'database.php';

redirectIfNotLogged('listar-reserva.php');

$query = "SELECT * FROM reservas ORDER BY fecha_reserva DESC, hora_reserva DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listar Reservas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .table-custom {
      background: #000;
      border: 2px solid #c6302f;
      color: #fff;
    }
    .table-custom th,
    .table-custom td {
      border: 1px solid #c6302f;
      color: #fff;
      background: #000;
    }
    .table-custom thead th {
      background: #1b1b1b;
      border-color: #c6302f;
    }
    .table-custom tbody tr {
      background: #000;
    }
    .table-custom tbody tr:hover {
      background: rgba(198, 48, 47, 0.1);
    }
    .btn-cancelar {
      color: #fff;
      border: 1px solid #c6302f;
      background: transparent;
    }
    .btn-cancelar:hover {
      background: #c6302f;
      color: #000;
    }
    body {
      background: #000;
      color: #fff;
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <h1 class="text-center mb-4">Listar Reservas</h1>
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-custom">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Origen</th>
            <th>Destino</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Conductor</th>
            <th>Teléfono</th>
            
          </tr>
        </thead>
        <tbody>
          <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['nombre_completo']); ?></td>
                <td><?php echo htmlspecialchars($row['origen']); ?></td>
                <td><?php echo htmlspecialchars($row['destino']); ?></td>
                <td><?php echo htmlspecialchars($row['fecha_reserva']); ?></td>
                <td><?php echo str_replace(['am', 'pm'], ['a.m.', 'p.m.'], strtolower(date('g:i a', strtotime($row['hora_reserva'])))); ?></td>
                <td><?php echo htmlspecialchars($row['conductor_nombre'] . ' ' . $row['conductor_apellido']); ?></td>
                <td><?php echo htmlspecialchars($row['conductor_telefono']); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="10" class="text-center">No hay reservas registradas.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
