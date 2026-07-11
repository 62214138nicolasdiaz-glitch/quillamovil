<?php
// listar_conductor.php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header('Location: login.php?redirect=listar_conductor.php');
    exit;
}

include 'database.php';

// Agregar CSS para estilos
echo '<head><link rel="stylesheet" href="estilos.css"></head>';

// Indicar fondo negro para listar
echo '<body class="listar-bg">';
include 'header.php';

// Obtener los conductores de la base de datos
$query = "SELECT * FROM conductor";
$result = mysqli_query($conn, $query);
?>

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Conductores de QuillaMovil </h2>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered tabla-listar" id="tablaConductor">
      <thead>
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
        <td class="<?php echo ($row['estado'] === 'Activo' || $row['estado'] === 'Libre') ? 'estado-activo' : ($row['estado'] === 'Ocupado' ? 'estado-ocupado' : ''); ?>">
          <span class="estado-punto"></span>
          <span class="estado-texto"><?php echo htmlspecialchars($row['estado']); ?></span>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  </div>
</div>

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