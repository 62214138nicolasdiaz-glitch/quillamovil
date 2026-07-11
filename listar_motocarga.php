<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php?redirect=listar.php');
    exit;
}

include 'database.php';

$query  = "SELECT * FROM motocarga";
$result = mysqli_query($conn, $query);

$motocargas = [];
while ($row = mysqli_fetch_assoc($result)) {
    $motocargas[] = $row;
}

$activeCount = 0;
$pathCount   = 0;
$busyCount   = 0;
foreach ($motocargas as $row) {
    $estado = mb_strtolower(trim($row['estado']), 'UTF-8');
    if (strpos($estado, 'activo') !== false)       $activeCount++;
    elseif (strpos($estado, 'camino') !== false)   $pathCount++;
    else                                            $busyCount++;
}

// Pre-calculamos los datos derivados UNA sola vez para que tabla y tarjetas
// usen exactamente la misma fuente (evita que se desincronicen).
$rowsPrepared = [];
foreach ($motocargas as $row) {
    $estadoLower = mb_strtolower(trim($row['estado']), 'UTF-8');
    $badgeClass = 'bg-danger';
    $cardClass  = 'border-danger';
    $estadoTexto = 'Fuera de servicio';
    if ($estadoLower === 'activo') {
        $badgeClass = 'bg-success';
        $cardClass  = 'border-success';
        $estadoTexto = 'Activo';
    } elseif ($estadoLower === 'en camino' || $estadoLower === 'camino') {
        $badgeClass = 'bg-warning text-dark';
        $cardClass  = 'border-warning';
        $estadoTexto = 'En camino';
    }
    $telefonoDigits = preg_replace('/\D+/', '', $row['telefono']);

    $row['estado_lower'] = $estadoLower;
    $row['badge_class']  = $badgeClass;
    $row['card_class']   = $cardClass;
    $row['estado_texto'] = $estadoTexto;
    $row['telefono_href']   = $telefonoDigits ? 'https://wa.me/' . $telefonoDigits : '#';
    $row['tiene_ubicacion'] = !empty($row['latitud']) && !empty($row['longitud']);
    $rowsPrepared[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listar Motocarga</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
  <style>
    body { background: #000; color: #fff; }
    .initial-icon {
      width: 44px;
      height: 44px;
      display: grid;
      place-items: center;
      border-radius: 50%;
      background: #c6302f;
      color: #fff;
      font-weight: 800;
      font-size: 1rem;
    }
    .card-activa {
      box-shadow: 0 0 0 0.45rem rgba(198,48,47,0.18);
    }
    .detalle-grid {
      display: grid;
      gap: 0.95rem;
    }
    .detalle-row {
      display: flex;
      justify-content: space-between;
      gap: 0.75rem;
      font-size: 0.95rem;
      color: #ddd;
    }
    .detalle-row strong {
      color: #fff;
      min-width: 120px;
      font-weight: 700;
    }
    .mapa-wrapper {
      position: relative;
      border-radius: 1rem;
      background: #0d0d0d;
      border: 1px solid rgba(198,48,47,0.35);
      box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    }
    #mapa {
      height: 520px;
      width: 100%;
    }
    .mapa-leyenda {
      position: absolute;
      z-index: 500;
      top: 1rem;
      left: 1rem;
      min-width: 180px;
      background: rgba(255,255,255,0.95);
      color: #212529;
      border-radius: 0.75rem;
      border: 1px solid rgba(0,0,0,0.1);
      padding: 0.85rem;
      box-shadow: 0 1rem 2rem rgba(0,0,0,0.18);
    }
    .mapa-leyenda-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.82rem;
      font-weight: 600;
      color: #212529;
      text-transform: uppercase;
      letter-spacing: 0.02em;
    }
    .mapa-leyenda-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      box-shadow: 0 0 6px rgba(0,0,0,0.15);
      flex: none;
    }
    .mapa-btn-centrar {
      position: absolute;
      z-index: 500;
      bottom: 1rem;
      right: 1rem;
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: #212529;
      border: 1px solid #c6302f;
      color: #fff;
      display: grid;
      place-items: center;
      cursor: pointer;
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.28);
      transition: background 0.15s ease, transform 0.15s ease;
    }
    .mapa-btn-centrar:hover { background: #c6302f; color: #fff; transform: scale(1.04); }
    .mapa-btn-centrar svg { width: 18px; height: 18px; fill: currentColor; }
    .leaflet-control-zoom a {
      background: #1b1b1b !important;
      color: #fff !important;
      border: 1px solid #c6302f !important;
    }
    .leaflet-control-zoom a:hover { background: #c6302f !important; color: #fff !important; }
    .leaflet-bar { border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.4); }
    .leaflet-control-attribution {
      background: rgba(0,0,0,0.6) !important;
      color: #888 !important;
    }
    .leaflet-control-attribution a { color: #c6302f !important; }
    .leaflet-control-scale-line {
      background: rgba(10,10,10,0.7) !important;
      color: #ddd !important;
      border-color: #c6302f !important;
    }
    @keyframes pulse-glow {
      0% { opacity: 1; transform: scale(1); box-shadow: 0 0 6px currentColor, 0 0 12px currentColor; }
      50% { opacity: 0.6; transform: scale(1.3); box-shadow: 0 0 10px currentColor, 0 0 20px currentColor; }
      100% { opacity: 1; transform: scale(1); box-shadow: 0 0 6px currentColor, 0 0 12px currentColor; }
    }
    .marcador-parpadeante { animation: pulse-glow 1.5s ease-in-out infinite; }
    .estado-badge {
      font-size: 0.78rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      margin-top: 0.2rem;
      padding: 0;
      background: transparent;
      color: inherit;
    }
    .estado-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      display: inline-block;
      flex: none;
      box-shadow: 0 0 0 2px rgba(255,255,255,0.16);
    }
    .estado-dot-success { background: #198754; }
    .estado-dot-warning { background: #ffc107; }
    .estado-dot-danger { background: #dc3545; }
    .estado-text-success { color: #198754; }
    .estado-text-warning { color: #ffc107; }
    .estado-text-danger { color: #dc3545; }
  </style>
</head>
<body>
  <div class="container py-4">
    <h1 class="text-center mb-3">Listar Motocargas</h1>

    <!-- Tarjetas de motocargas -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3" id="card-list">
      <?php foreach ($rowsPrepared as $row):
        $initial = mb_strtoupper(mb_substr(trim($row['nombre']), 0, 1, 'UTF-8'), 'UTF-8');
      ?>
      <div class="col">
        <div class="card bg-warning text-dark <?php echo $row['card_class']; ?> h-100<?php echo !$row['tiene_ubicacion'] ? ' opacity-75' : ''; ?><?php echo $row['tiene_ubicacion'] ? '' : ' disabled'; ?>" id="card-<?php echo $row['id']; ?>">
          <div class="card-body d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex align-items-center gap-3 mb-2">
                <div class="initial-icon"><?php echo htmlspecialchars($initial ?: 'A'); ?></div>
                <div>
                  <h5 class="card-title mb-1"><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></h5>
                </div>
              </div>
            </div>
            <div class="d-flex gap-2 flex-wrap mt-3">
              <button type="button" class="btn btn-dark btn-sm btn-detalle flex-fill"
                      data-id="<?php echo $row['id']; ?>"
                      data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>"
                      data-apellido="<?php echo htmlspecialchars($row['apellido']); ?>"
                      data-dni="<?php echo htmlspecialchars($row['dni']); ?>"
                      data-direccion="<?php echo htmlspecialchars($row['direccion']); ?>"
                      data-telefono="<?php echo htmlspecialchars($row['telefono']); ?>"
                      data-estado="<?php echo htmlspecialchars($row['estado_texto']); ?>"
                      data-lat="<?php echo htmlspecialchars($row['latitud']); ?>"
                      data-lng="<?php echo htmlspecialchars($row['longitud']); ?>">
                Ver
              </button>
              <button type="button" class="btn btn-danger btn-sm btn-mapa flex-fill" 
                      data-id="<?php echo $row['id']; ?>"
                      data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>"
                      data-lat="<?php echo (float)$row['latitud']; ?>"
                      data-lng="<?php echo (float)$row['longitud']; ?>"
                      <?php if (!$row['tiene_ubicacion']) echo 'disabled'; ?>>
                Mapa
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Modal de Detalles -->
    <div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white border border-danger">
          <div class="modal-header border-bottom border-secondary">
            <h5 class="modal-title" id="detalleModalLabel">Detalle de la motocarga</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="detalle-grid">
              <div class="detalle-row"><strong>ID</strong><span id="detalle-id"></span></div>
              <div class="detalle-row"><strong>DNI</strong><span id="detalle-dni"></span></div>
              <div class="detalle-row"><strong>Nombre</strong><span id="detalle-nombre"></span></div>
              <div class="detalle-row"><strong>Apellidos</strong><span id="detalle-apellido"></span></div>
              <div class="detalle-row"><strong>Dirección</strong><span id="detalle-direccion"></span></div>
              <div class="detalle-row"><strong>Teléfono</strong><span id="detalle-telefono"></span></div>
              <div class="detalle-row"><strong>Lat / Lng</strong><span id="detalle-ubicacion"></span></div>
            </div>
          </div>
          <div class="modal-footer border-top border-secondary">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Mapa -->
    <div class="modal fade" id="mapaModal" tabindex="-1" role="dialog" aria-labelledby="mapaModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-fullscreen-sm-down" role="document">
        <div class="modal-content bg-dark border border-danger rounded-4">
          <div class="modal-header border-bottom border-secondary">
            <h5 class="modal-title text-white" id="mapaModalLabel">Ubicación en Vivo</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body p-0">
            <div class="mapa-wrapper rounded-4 overflow-hidden">
              <div id="mapa"></div>
              <div class="mapa-leyenda">
                <div class="mapa-leyenda-item"><span class="mapa-leyenda-dot" style="background:#00e910"></span><span>Activo</span></div>
                <div class="mapa-leyenda-item"><span class="mapa-leyenda-dot" style="background:#ffbf00"></span><span>En camino</span></div>
                <div class="mapa-leyenda-item"><span class="mapa-leyenda-dot" style="background:#ff4444"></span><span>Fuera de servicio</span></div>
              </div>
              <button id="btn-centrar" class="mapa-btn-centrar shadow" title="Centrar mapa" type="button">
                <svg viewBox="0 0 24 24"><path d="M12 8a4 4 0 100 8 4 4 0 000-8zm9 3h-2.06A7.002 7.002 0 0013 4.06V2h-2v2.06A7.002 7.002 0 004.06 11H2v2h2.06A7.002 7.002 0 0011 19.94V22h2v-2.06A7.002 7.002 0 0019.94 13H22v-2zm-9 7a5 5 0 110-10 5 5 0 010 10z"/></svg>
              </button>
            </div>
          </div>
          <div class="modal-footer border-top border-secondary d-flex align-items-center justify-content-between">
            <span id="ultimo-update" class="text-muted small mb-0">Actualizando...</span>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
    // Ids de sesión disponibles en JS para detectar cambios del usuario actual
    var sessionUserId = <?php echo json_encode($_SESSION['user']['id'] ?? null); ?>;
    var sessionConductorId = <?php echo json_encode($_SESSION['conductor']['id'] ?? null); ?>;
    // ── Mapa ──
    var mapa = L.map('mapa', { zoomControl: true }).setView([-13.5319, -71.9675], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors',
      maxZoom: 19
    }).addTo(mapa);
    L.control.scale({ imperial: false, position: 'bottomleft' }).addTo(mapa);

    var layerGroup = L.layerGroup().addTo(mapa);

    // Guardamos referencias a los marcadores por id para moverlos en vez de recrearlos
    var marcadoresMap = {};

    // Marcador de ubicación actual del usuario
    var marcadorUsuario = null;
    var ultimaPosicionUsuario = null;
    var inicioMovimientoUsuario = null;
    var watchIdUsuario = null;
    var MOVIMIENTO_USUARIO_THRESHOLD_METERS = 15;
    var MOVIMIENTO_USUARIO_SUSTAINED_MS = 5000;

    function colorEstado(estado) {
      var e = (estado || '').toLowerCase();
      if (e === 'activo')    return '#00e910';
      if (e === 'en camino') return '#ffbf00';
      return '#ff4444';
    }

    function crearIcono(color) {
      return L.divIcon({
        className: 'marcador-parpadeante',
        html: '<div style="width:16px;height:16px;background:' + color + ';border:2px solid #fff;border-radius:50%;color:' + color + ';"></div>',
        iconSize: [16,16], iconAnchor: [8,8], popupAnchor: [0,-10]
      });
    }

    function estadoTexto(estado) {
      var e = (estado || '').toLowerCase();
      if (e === 'activo') return 'Activo';
      if (e === 'en camino') return 'En camino';
      if (e === 'ocupado') return 'Fuera de servicio';
      return estado;
    }

    function popupHtml(t, estado) {
      var color = colorEstado(estado);
      return '<div style="font-family:system-ui, sans-serif; color:#111; line-height:1.35;">'
        + '<strong style="display:block; margin-bottom:.35rem;">' + t.nombre + '</strong>'
        + '<div style="margin-bottom:.35rem;">Estado: <span style="color:' + color + '; font-weight:700;">' + estadoTexto(estado) + '</span></div>'
        + '<div>Tel: <a href="https://wa.me/' + t.telefono + '" target="_blank" style="color:#0d6efd; text-decoration:none;">' + t.telefono + '</a></div>'
        + '</div>';
    }

    // ── Actualiza la etiqueta de estado en TODOS los lugares donde aparece
    // (fila de tabla + tarjeta móvil) usando data-estado-id, así no se desincronizan ──
    function actualizarEtiquetaEstado(id, estado) {
      var texto = estado === 'activo' ? 'Activo' : (estado === 'en camino' ? 'En camino' : 'Fuera de servicio');
      document.querySelectorAll('[data-estado-id="' + id + '"]').forEach(function(label) {
        label.textContent = texto;
        label.classList.remove('bg-success', 'bg-warning', 'text-dark', 'bg-danger');
        if (estado === 'activo') label.classList.add('bg-success');
        else if (estado === 'en camino') label.classList.add('bg-warning', 'text-dark');
        else label.classList.add('bg-danger');
      });
    }

    var previousLocations = {};
    var lastMovementTime = {};
    var movementStartTime = {};
    var MOVEMENT_THRESHOLD_METERS = 15;
    var MOVEMENT_SUSTAINED_MS = 15000;
    var OCCUPIED_TIMEOUT_MS = 20000;

    function distanciaMetros(lat1, lon1, lat2, lon2) {
      var R = 6371000;
      var dLat = (lat2 - lat1) * Math.PI / 180;
      var dLon = (lon2 - lon1) * Math.PI / 180;
      var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
      var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      return R * c;
    }

    function cambiarEstadoAutomatico(id, nuevoEstado) {
      var formData = new FormData();
      formData.append('id', id);
      formData.append('estado', nuevoEstado);

      fetch('actualizar-estado-toro.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
          if (data.success) {
            actualizarEtiquetaEstado(id, nuevoEstado);
            actualizarContadores();
          }
        })
        .catch(function(err) {
          console.error('Error al cambiar estado automáticamente:', err);
        });
    }

    // ── Actualizar mapa en tiempo real ──
    function actualizarMapa() {
      fetch('torito-ubicacion.php')
        .then(r => r.json())
        .then(function(lista) {
          var idsActuales = lista.map(t => t.id);

          // Eliminar marcadores que ya no están
          Object.keys(marcadoresMap).forEach(function(id) {
            if (!idsActuales.includes(parseInt(id))) {
              layerGroup.removeLayer(marcadoresMap[id]);
              delete marcadoresMap[id];
            }
          });

          lista.forEach(function(t) {
            var previous = previousLocations[t.id];
            var ahora = Date.now();
            var estadoMovimiento = 'ocupado';

            if (previous) {
              var distancia = distanciaMetros(previous.lat, previous.lng, t.lat, t.lng);
              if (distancia >= MOVEMENT_THRESHOLD_METERS) {
                if (!movementStartTime[t.id]) {
                  movementStartTime[t.id] = ahora;
                }
                var tiempoMovimiento = ahora - movementStartTime[t.id];
                estadoMovimiento = tiempoMovimiento >= MOVEMENT_SUSTAINED_MS ? 'activo' : 'en camino';
                lastMovementTime[t.id] = ahora;
              } else {
                movementStartTime[t.id] = null;
                var ultimoMovimiento = lastMovementTime[t.id] || 0;
                if (ahora - ultimoMovimiento >= OCCUPIED_TIMEOUT_MS) {
                  estadoMovimiento = 'ocupado';
                } else {
                  estadoMovimiento = 'en camino';
                }
              }
            } else {
              lastMovementTime[t.id] = ahora;
              movementStartTime[t.id] = null;
              estadoMovimiento = 'ocupado';
            }

            previousLocations[t.id] = { lat: t.lat, lng: t.lng, time: ahora };
            var color = colorEstado(estadoMovimiento);

            if (marcadoresMap[t.id]) {
              marcadoresMap[t.id].setLatLng([t.lat, t.lng]);
              marcadoresMap[t.id].setIcon(crearIcono(color));
              marcadoresMap[t.id].setPopupContent(popupHtml(t, estadoMovimiento));
            } else {
              var m = L.marker([t.lat, t.lng], { icon: crearIcono(color) });
              m.bindPopup(popupHtml(t, estadoMovimiento));
              m.addTo(layerGroup);
              marcadoresMap[t.id] = m;
            }

            actualizarEtiquetaEstado(t.id, estadoMovimiento);

            // Solo guardamos en BD si el estado calculado difiere del que ya
            // tiene el torito según el último fetch (t.estado).
            var estadoParaGuardar = estadoMovimiento;
            var estadoBD = (t.estado || '').toLowerCase();
            if (estadoBD === 'camino') estadoBD = 'en camino';

            if (estadoBD !== estadoParaGuardar) {
              cambiarEstadoAutomatico(t.id, estadoParaGuardar);
            }
          });

          actualizarContadores();

          // Timestamp
          var ahoraTs = new Date();
          document.getElementById('ultimo-update').textContent =
            'Última actualización: ' + ahoraTs.toLocaleTimeString('es-PE');
        })
        .catch(err => console.error('Error al actualizar mapa:', err));
    }

    // ── Actualizar contadores desde todos los toritos ──
    function actualizarContadores() {
      fetch('torito-estado.php')
        .then(r => r.json())
        .then(function(data) {
          document.getElementById('count-activo').textContent  = data.activo  || 0;
          document.getElementById('count-camino').textContent  = data.camino  || 0;
          document.getElementById('count-ocupado').textContent = data.ocupado || 0;
        })
        .catch(() => {});
    }

    // ── Carga inicial y polling ──
    obtenerEstadoUsuario();
    actualizarMapa();
    setInterval(function() {
      obtenerEstadoUsuario();
      actualizarMapa();
    }, 10000); // cada 10 segundos

    // ── Botón centrar: ajusta la vista a todos los toritos visibles ──
    document.getElementById('btn-centrar').addEventListener('click', function() {
      var ids = Object.keys(marcadoresMap);
      if (ids.length === 0) {
        mapa.setView([-13.5319, -71.9675], 13);
        return;
      }
      var grupo = L.featureGroup(ids.map(id => marcadoresMap[id]));
      mapa.fitBounds(grupo.getBounds().pad(0.2));
    });

    // ── Obtener estado actual del usuario logueado ──
    var estadoUsuarioActual = 'activo';
    var estadoMarcadorUsuario = 'activo';
    var estadoMarcadorSeleccionado = null;

    function crearIconoUsuario(color) {
      var azul = '#007bff';
      color = color || azul;
      return L.divIcon({
        className: 'marcador-usuario',
        html: '<div style="width:20px;height:20px;background:' + color + ';border:3px solid #fff;border-radius:50%;box-shadow:0 0 10px ' + color + ';"></div>',
        iconSize: [20, 20],
        iconAnchor: [10, 10],
        popupAnchor: [0, -15]
      });
    }

    function colorEstadoGeolocalizacion(estado) {
      if (estado === 'movimiento sostenido') return '#00e910';
      if (estado === 'moviendo') return '#ffbf00';
      return '#ff4444';
    }

    function determinarEstadoMovimientoUsuario(lat, lng) {
      if (typeof lat !== 'number' || typeof lng !== 'number') {
        return 'quieto';
      }

      if (!ultimaPosicionUsuario) {
        ultimaPosicionUsuario = { lat: lat, lng: lng, time: Date.now() };
        inicioMovimientoUsuario = null;
        return 'quieto';
      }

      var distancia = distanciaMetros(ultimaPosicionUsuario.lat, ultimaPosicionUsuario.lng, lat, lng);
      var ahora = Date.now();

      if (distancia < MOVIMIENTO_USUARIO_THRESHOLD_METERS) {
        inicioMovimientoUsuario = null;
        ultimaPosicionUsuario = { lat: lat, lng: lng, time: ahora };
        return 'quieto';
      }

      if (!inicioMovimientoUsuario) {
        inicioMovimientoUsuario = ahora;
      }

      var tiempoMovimiento = ahora - inicioMovimientoUsuario;
      ultimaPosicionUsuario = { lat: lat, lng: lng, time: ahora };

      if (tiempoMovimiento >= MOVIMIENTO_USUARIO_SUSTAINED_MS) {
        return 'movimiento sostenido';
      }
      return 'moviendo';
    }

    function actualizarMarcadorUsuario(lat, lng) {
      var color = '#ff4444';
      var estadoTextoMostrar = 'Quieto';

      if (estadoMarcadorSeleccionado) {
        var iconColorStatus = estadoMarcadorSeleccionado || estadoMarcadorUsuario || estadoUsuarioActual;
        color = colorEstado(iconColorStatus) || '#007bff';
        estadoTextoMostrar = estadoUsuarioActual || iconColorStatus;
      } else {
        var estadoMovimiento = determinarEstadoMovimientoUsuario(lat, lng);
        color = colorEstadoGeolocalizacion(estadoMovimiento);
        estadoTextoMostrar = estadoMovimiento === 'movimiento sostenido' ? 'Movimiento sostenido' : (estadoMovimiento === 'moviendo' ? 'Moviéndose' : 'Quieto');
      }

      var iconoUsuario = crearIconoUsuario(color);

      if (!marcadorUsuario) return;

      if (typeof lat === 'number' && typeof lng === 'number') {
        marcadorUsuario.setLatLng([lat, lng]);
      }

      marcadorUsuario.setIcon(iconoUsuario);

      var posicion = marcadorUsuario.getLatLng();
      var popupContenido = '<strong>Tu ubicación</strong><br>' +
                          'Estado: <span style="color:' + color + ';font-weight:bold;">' + estadoTextoMostrar + '</span>';
      if (posicion) {
        popupContenido += '<br>Lat: ' + posicion.lat.toFixed(5) + '<br>Lng: ' + posicion.lng.toFixed(5);
      }
      marcadorUsuario.setPopupContent(popupContenido);
    }

    function obtenerEstadoUsuario() {
      fetch('get-estado-usuario.php')
        .then(r => r.json())
        .then(data => {
          if (data.estado) {
            estadoUsuarioActual = (data.estado || '').toLowerCase();
            if (!estadoMarcadorSeleccionado) {
              estadoMarcadorUsuario = estadoUsuarioActual;
            }
            if (marcadorUsuario) {
              actualizarMarcadorUsuario();
            }
          }
        })
        .catch(err => console.error('Error al obtener estado del usuario:', err));
    }

    // ── Función para crear/actualizar marcador de usuario ──
    function mostrarUbicacionActual() {
      if (!navigator.geolocation) {
        console.warn('Geolocalización no disponible');
        return;
      }

      if (watchIdUsuario !== null) {
        navigator.geolocation.clearWatch(watchIdUsuario);
      }

      ultimaPosicionUsuario = null;
      inicioMovimientoUsuario = null;

      watchIdUsuario = navigator.geolocation.watchPosition(function(position) {
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;

        if (!marcadorUsuario) {
          var colorInicial = '#ff4444';
          var iconoUsuarioInicial = crearIconoUsuario(colorInicial);

          marcadorUsuario = L.marker([lat, lng], { icon: iconoUsuarioInicial });
          marcadorUsuario.addTo(mapa);
        }

        actualizarMarcadorUsuario(lat, lng);
      }, function(error) {
        console.error('Error al obtener ubicación:', error.message);
      }, {
        enableHighAccuracy: true,
        maximumAge: 1000,
        timeout: 10000
      });
    }

    function abrirModalDetalles(btn) {
      document.getElementById('detalle-id').textContent = btn.dataset.id || '-';
      document.getElementById('detalle-dni').textContent = btn.dataset.dni || '-';
      document.getElementById('detalle-nombre').textContent = btn.dataset.nombre || '-';
      document.getElementById('detalle-apellido').textContent = btn.dataset.apellido || '-';
      document.getElementById('detalle-direccion').textContent = btn.dataset.direccion || '-';
      document.getElementById('detalle-telefono').textContent = btn.dataset.telefono || '-';
      var lat = btn.dataset.lat || '';
      var lng = btn.dataset.lng || '';
      document.getElementById('detalle-ubicacion').textContent = lat && lng ? lat + ' / ' + lng : 'Sin GPS';

      var modal = new bootstrap.Modal(document.getElementById('detalleModal'));
      modal.show();
    }

    function abrirModalMapa(btn) {
      var lat    = parseFloat(btn.dataset.lat);
      var lng    = parseFloat(btn.dataset.lng);
      var nombre = btn.dataset.nombre || 'Conductor';
      var id     = btn.dataset.id;

      document.getElementById('mapaModalLabel').textContent = '📍 Ubicación - ' + nombre;
      var modal = new bootstrap.Modal(document.getElementById('mapaModal'));
      modal.show();

      setTimeout(function() {
        mapa.invalidateSize();
        if (!isNaN(lat) && !isNaN(lng)) {
          mapa.setView([lat, lng], 17);
        }

        estadoMarcadorSeleccionado = btn.dataset.estado || estadoUsuarioActual;
        mostrarUbicacionActual();

        if (marcadoresMap[id]) {
          marcadoresMap[id].openPopup();
        }

        document.querySelectorAll('.card-activa').forEach(function(el) {
          el.classList.remove('card-activa');
        });
        var card = document.getElementById('card-' + id);
        if (card) card.classList.add('card-activa');
      }, 300);
    }

    document.getElementById('card-list').addEventListener('click', function(e) {
      var btnDetalle = e.target.closest('.btn-detalle');
      var btnMapa = e.target.closest('.btn-mapa');
      if (btnDetalle) {
        abrirModalDetalles(btnDetalle);
        return;
      }
      if (btnMapa) {
        abrirModalMapa(btnMapa);
        return;
      }
    });
  </script>
</body>
</html>