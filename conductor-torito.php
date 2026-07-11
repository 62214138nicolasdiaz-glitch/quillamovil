<?php
// conductor-torito.php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header('Location: login.php?redirect=conductor-torito.php');
    exit;
}

include 'database.php';

// Agregar CSS para estilos
echo '<head><link rel="stylesheet" href="estilos.css"></head>';

// Indicar fondo negro para listar
echo '<body class="listar-bg">';
include 'header.php';

// Obtener los conductores registrados con Torito
$query = "SELECT dni, nombre, apellido, vehiculo, tipo_lic, telefono, estado FROM conductor WHERE vehiculo = 'Torito'";
$result = mysqli_query($conn, $query);
?>

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h2 class="mb-0">Conductores Torito</h2>
    <div class="position-relative" style="width: 250px;">
      <i class="bi bi-search position-absolute" style="left:10px; top:50%; transform:translateY(-50%); color:#E24B4A;"></i>
      <input
        type="text"
        id="searchInput"
        class="form-control ps-4"
        placeholder="Buscar por nombre..."
        onkeyup="filterTable()"
        style="border: 2px solid #E24B4A !important; background: #fff !important; width: 100%;"
      >
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped" id="tableBody">
      <thead>
        <tr>
          <th>DNI</th>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Vehículo</th>
          <th>Tipo Licencia</th>
          <th>Teléfono</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>

      <tr>
        <td><?php echo htmlspecialchars($row['dni']); ?></td>
        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
        <td><?php echo htmlspecialchars($row['apellido']); ?></td>
        <td><?php echo htmlspecialchars($row['vehiculo']); ?></td>
        <td><?php echo htmlspecialchars($row['tipo_lic']); ?></td>
        <td>
          <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $row['telefono']); ?>?text=<?php echo urlencode('Te estoy contactando desde Quillamovil, necesito hacer una reserva de su vehiculo'); ?>" target="_blank" title="WhatsApp" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1" style="padding: 0.25rem 0.45rem;">
            <i class="bi bi-whatsapp" style="font-size: 18px;"></i>
          </a>
          <?php echo htmlspecialchars($row['telefono']); ?>
        </td>
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

<script>
function filterTable() {
  const query = document.getElementById('searchInput').value.toLowerCase();
  const rows = document.querySelectorAll('#tableBody tr');
  rows.forEach(row => {
    const dni = row.cells[0].textContent.toLowerCase();
    const nombre = row.cells[1].textContent.toLowerCase();
    const apellido = row.cells[2].textContent.toLowerCase();
    row.style.display = (dni.includes(query) || nombre.includes(query) || apellido.includes(query)) ? '' : 'none';
  });
}
</script>
