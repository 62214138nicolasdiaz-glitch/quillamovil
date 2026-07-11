<?php
// listar_conductor.php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header('Location: login.php?redirect=ver-lista-conductor.php');
    exit;
}

include 'database.php';
include 'functions.php';

// Sincronizar estados de conductores con las reservas confirmadas
if (function_exists('syncConductorEstado')) {
    syncConductorEstado($conn);
}

// Obtener los conductores de la base de datos
$query = "SELECT * FROM conductor";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Conductores - QuillaMovil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lobster&family=Poppins:wght@300;400;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body.listar-bg {
      background-color: #000 !important;
      color: #fff !important;
      font-family: 'Poppins', Arial, sans-serif;
    }
    body.listar-bg .table {
      background-color: #181818 !important;
      color: #fff !important;
    }
    body.listar-bg .table thead th {
      background-color: #222 !important;
      color: #ffeb3b !important;
    }
    body.listar-bg .table-bordered td,
    body.listar-bg .table-bordered th {
      border-color: #444 !important;
    }
    body.listar-bg .table-striped tbody tr:nth-of-type(odd) {
      background-color: #232323 !important;
    }
    .table-responsive {
      width: 100%;
    }
    body.listar-bg .btn {
      color: #fff;
    }
    body.listar-bg .btn-warning {
      background-color: #ff2600;
      border-color: #ff0000;
      color: #eeeeee;
    }
    body.listar-bg .btn-danger {
      background-color: #ee3e12;
      border-color: #ee4b0b;
      color: #fff;
    }
  </style>
</head>
<body class="listar-bg">

<main class="container-fluid py-5">
  <div class="card bg-dark border-secondary shadow-sm">
    <div class="card-body p-4 p-lg-5">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
          <h1 class="h1 text-white mb-1">Registro de Conductores</h1>
          
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2 align-items-center ms-auto">
          <a href="conductor.php" class="btn btn-warning btn-sm text-dark px-3 py-2">
             Crear nuevo conductor
          </a>
          <div class="input-group" style="min-width: 200px; max-width: 220px;">
            <span class="input-group-text bg-white text-red border-white py-2">
              <i class="bi bi-search"></i>
            </span>
            <input
              type="text"
              id="searchInput"
              class="form-control"
              placeholder="Buscar por nombre..."
              oninput="filtrarTabla()"
            />
          </div>
        </div>
      </div>

      <div class="table-responsive shadow-sm rounded-4 overflow-hidden">
        <table class="table table-dark table-striped table-hover table-bordered table-sm align-middle mb-0" id="tablaConductor">
          <thead class="table-secondary text-dark">
            <tr>
          <th>ID</th>
          <th>Foto</th>
          <th>DNI</th>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Teléfono</th>
          <th>Vehículo</th>
          <th>Placa</th>
          <th>Número de Serie</th>
          <th>Color</th>
          <th>Estado</th>
           
        </tr>
      </thead>
      <tbody id="tablaCuerpo">
        <?php while($row = mysqli_fetch_assoc($result)): ?>

      <tr>
        <td><?php echo $row['id']; ?></td>
        <td>
          <?php if (!empty($row['foto'])): ?>
            <img src="<?php echo htmlspecialchars($row['foto']); ?>" alt="foto" style="height:48px; border-radius:6px;"> 
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
        <td><?php echo $row['dni']; ?></td>
        <td class="col-nombre"><?php echo $row['nombre']; ?></td>
        <td><?php echo $row['apellido']; ?></td>
        <td>
          <?php echo $row['telefono']; ?>
          <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $row['telefono']); ?>?text=<?php echo urlencode('Te estoy contactando desde Quillamovil, necesito hacer una reserva de su vehiculo'); ?>" 
             target="_blank" 
             class="btn btn-sm btn-success ms-2"
             title="Contactar por WhatsApp"
             style="padding: 4px 8px;">
            <i class="bi bi-whatsapp"></i>
          </a>
        </td>
        <td><?php echo $row['vehiculo']; ?></td>
        <td><?php echo $row['tipo_lic']; ?></td>
        <td><?php echo $row['numero_censo']; ?></td>
        <td><?php echo $row['color']; ?></td>
        <td>
          <?php
            $estado = trim($row['estado']);
            $estadoClass = strtolower($estado) === 'ocupado' ? 'estado-ocupado' : 'estado-activo';
          ?>
          <span class="badge <?php echo $estadoClass; ?>">
            <?php echo htmlspecialchars($estado); ?>
          </span>
        </td>
    
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
      </div>
      <p id="sinResultados" class="text-center text-muted mt-4" style="display:none;">No se encontraron resultados.</p>
    </div>
  </div>
</main>

<script>
function filtrarTabla() {
  const q = document.getElementById('searchInput').value.trim().toLowerCase();
  const filas = document.querySelectorAll('#tablaCuerpo tr');
  let visibles = 0;

  filas.forEach(fila => {
    const nombre = fila.querySelector('.col-nombre').textContent.toLowerCase();
    if (!q || nombre.includes(q)) {
      fila.style.display = '';
      visibles++;
    } else {
      fila.style.display = 'none';
    }
  });

  document.getElementById('sinResultados').style.display = visibles === 0 ? '' : 'none';
}
</script>

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

  .status-badge {
    display: inline-block;
    padding: 0.35em 0.65em;
    border-radius: 0.35rem;
    font-size: 0.85rem;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.03em;
  }
  .status-libre {
    background-color: #00cc00;
  }
  .status-ocupado {
    background-color: #ff0000;
  }
  .estado-activo {
    background-color: #28a745 !important;
    color: #fff !important;
  }
  .estado-ocupado {
    background-color: #dc3545 !important;
    color: #fff !important;
  }

  .whatsapp-float i {
    font-size: 32px;
  }
</style>

<a href="https://wa.me/51988966199?text=Hola,%20te%20contacto%20desde%20QuillaMovil" 
   target="_blank" 
   class="whatsapp-float" 
   title="Contactar por WhatsApp">
  <i class="bi bi-whatsapp"></i>
</a>
</body>
</html>