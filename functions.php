<?php

/**
 * Función para limpiar y sanitizar datos de entrada
 * Previene ataques XSS eliminando caracteres especiales
 * @param string $str - Cadena a limpiar
 * @return string - Cadena sanitizada
 */
function clean($str) {
    // Convierte caracteres especiales en entidades HTML para evitar inyección XSS
    // trim() elimina espacios en blanco al inicio y final
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

/**
 * Función para autenticar un usuario
 * Verifica credenciales contra la base de datos y valida la contraseña
 * @param mysqli $conn - Conexión a la base de datos
 * @param string $username - Nombre de usuario ingresado
 * @param string $password - Contraseña ingresada
 * @return array|false - Retorna datos del usuario si login es exitoso, false si falla
 */
function login($conn, $username, $password) {
    // Sanitiza el nombre de usuario para evitar inyección SQL
    $username = clean($username);

    // Consulta la base de datos para encontrar el usuario
    $sql = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    // Verifica si la consulta fue exitosa y si se encontró exactamente un usuario
    if ($result && mysqli_num_rows($result) === 1) {
        // Obtiene los datos del usuario como un arreglo asociativo
        $user = mysqli_fetch_assoc($result);

        // Verifica la contraseña usando hash bcrypt
        if (!empty($user['password']) && password_verify($password, $user['password'])) {
            return $user;
        }
    }

    // Retorna false si las credenciales no son válidas
    return false;
}

/**
 * Devuelve true si la columna existe en la tabla especificada
 */
function columnExists($conn, $table, $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM $table LIKE '" . mysqli_real_escape_string($conn, $column) . "'");
    return $result && mysqli_num_rows($result) > 0;
}

/**
 * Asegura que la tabla usuarios tenga columnas de OAuth y un id válido
 */
function ensureOAuthUserSchema($conn) {
    if (!columnExists($conn, 'users', 'email')) {
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN email VARCHAR(150) DEFAULT NULL");
    }
    if (!columnExists($conn, 'users', 'oauth_provider')) {
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN oauth_provider VARCHAR(50) DEFAULT NULL");
    }
    if (!columnExists($conn, 'users', 'oauth_id')) {
        mysqli_query($conn, "ALTER TABLE users ADD COLUMN oauth_id VARCHAR(255) DEFAULT NULL");
    }

    $idColumn = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'id'");
    if ($idColumn && mysqli_num_rows($idColumn) > 0) {
        $idInfo = mysqli_fetch_assoc($idColumn);
        if (stripos($idInfo['Extra'], 'auto_increment') === false) {
            mysqli_query($conn, "ALTER TABLE users MODIFY COLUMN id INT(11) NOT NULL AUTO_INCREMENT");
        }
        $primaryKey = mysqli_query($conn, "SHOW INDEX FROM users WHERE Key_name = 'PRIMARY'");
        if (!$primaryKey || mysqli_num_rows($primaryKey) === 0) {
            mysqli_query($conn, "ALTER TABLE users ADD PRIMARY KEY (id)");
        }
    }
}

/**
 * Retorna el siguiente id disponible para la tabla users cuando no hay AUTO_INCREMENT
 */
function getNextUserId($conn) {
    $result = mysqli_query($conn, "SELECT MAX(id) AS max_id FROM users");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    return ($row && $row['max_id'] !== null) ? intval($row['max_id']) + 1 : 1;
}

/**
 * Verifica si existe un usuario con el nombre dado
 */
function userExists($conn, $username) {
    $usernameEscaped = mysqli_real_escape_string($conn, $username);
    $sql = "SELECT id FROM users WHERE username = '$usernameEscaped' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    return $result && mysqli_num_rows($result) > 0;
}

/**
 * Crea un nuevo usuario en la base de datos
 */
function createUser($conn, $username, $passwordHash, $email = null, $oauthProvider = null, $oauthId = null) {
    ensureOAuthUserSchema($conn);

    $usernameEscaped = mysqli_real_escape_string($conn, $username);
    $passwordEscaped = mysqli_real_escape_string($conn, $passwordHash);
    $emailEscaped = $email !== null ? "'" . mysqli_real_escape_string($conn, $email) . "'" : 'NULL';
    $providerEscaped = $oauthProvider !== null ? "'" . mysqli_real_escape_string($conn, $oauthProvider) . "'" : 'NULL';
    $oauthIdEscaped = $oauthId !== null ? "'" . mysqli_real_escape_string($conn, $oauthId) . "'" : 'NULL';

    $idColumn = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'id'");
    $idInfo = $idColumn ? mysqli_fetch_assoc($idColumn) : null;
    $useId = $idInfo && stripos($idInfo['Extra'], 'auto_increment') === false;
    $idValue = $useId ? getNextUserId($conn) : null;

    $columns = 'username, password, email, oauth_provider, oauth_id';
    $values = "'$usernameEscaped', '$passwordEscaped', $emailEscaped, $providerEscaped, $oauthIdEscaped";

    if ($useId) {
        $columns = 'id, ' . $columns;
        $values = "$idValue, $values";
    }

    $sql = "INSERT INTO users ($columns) VALUES ($values)";
    if (mysqli_query($conn, $sql)) {
        if ($useId) {
            return [
                'id' => $idValue,
                'username' => $username,
                'email' => $email,
                'oauth_provider' => $oauthProvider,
                'oauth_id' => $oauthId,
            ];
        }

        $insertId = mysqli_insert_id($conn);
        return [
            'id' => $insertId,
            'username' => $username,
            'email' => $email,
            'oauth_provider' => $oauthProvider,
            'oauth_id' => $oauthId,
        ];
    }
    return false;
}

/**
 * Busca un usuario por proveedor OAuth + oauth_id.
 */
function findUserByOAuth($conn, $provider, $oauthId) {
    ensureOAuthUserSchema($conn);
    $providerEscaped = mysqli_real_escape_string($conn, $provider);
    $oauthIdEscaped = mysqli_real_escape_string($conn, $oauthId);
    $sql = "SELECT * FROM users WHERE oauth_provider = '$providerEscaped' AND oauth_id = '$oauthIdEscaped' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    return $result && mysqli_num_rows($result) === 1 ? mysqli_fetch_assoc($result) : false;
}

/**
 * Busca un usuario por dirección de correo.
 */
function findUserByEmail($conn, $email) {
    ensureOAuthUserSchema($conn);
    $emailEscaped = mysqli_real_escape_string($conn, $email);
    $sql = "SELECT * FROM users WHERE email = '$emailEscaped' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    return $result && mysqli_num_rows($result) === 1 ? mysqli_fetch_assoc($result) : false;
}

/**
 * Enlaza un usuario existente con las credenciales OAuth.
 */
function bindOAuthToUser($conn, $userId, $provider, $oauthId, $email = null) {
    ensureOAuthUserSchema($conn);
    $providerEscaped = mysqli_real_escape_string($conn, $provider);
    $oauthIdEscaped = mysqli_real_escape_string($conn, $oauthId);
    $userIdEscaped = intval($userId);

    $sql = "UPDATE users SET oauth_provider = '$providerEscaped', oauth_id = '$oauthIdEscaped'";
    if ($email !== null) {
        $emailEscaped = mysqli_real_escape_string($conn, $email);
        $sql .= ", email = '$emailEscaped'";
    }
    $sql .= " WHERE id = $userIdEscaped";
    mysqli_query($conn, $sql);
}

/**
 * Genera un nombre de usuario único basado en correo o proveedor.
 */
function generateOAuthUsername($conn, $name, $email, $provider) {
    $base = '';
    if (!empty($email)) {
        $parts = explode('@', $email);
        $base = preg_replace('/[^a-z0-9_]/', '', strtolower($parts[0]));
    }
    if ($base === '') {
        $base = preg_replace('/[^a-z0-9_]/', '', strtolower($provider . '_' . preg_replace('/[^a-z0-9]/', '', $name)));
    }
    if ($base === '') {
        $base = $provider . '_' . random_int(1000, 9999);
    }

    $username = $base;
    $counter = 1;
    while (userExists($conn, $username)) {
        $username = $base . $counter;
        $counter++;
    }
    return $username;
}

/**
 * Función para verificar si un usuario está autenticado
 * Comprueba si existe la variable de sesión 'user'
 * @return bool - Retorna true si el usuario está logueado, false en caso contrario
 */
function isLogged() {
    // Retorna true si la sesión 'user' está establecida
    return isset($_SESSION['user']);
}

/**
 * Sincroniza el estado de los conductores con las reservas activas
 *
 * Un conductor se marca como "Ocupado" si tiene al menos una reserva con fecha
 * posterior a la fecha actual, o en la misma fecha con hora mayor o igual a la actual.
 * Si no hay reservas activas, el conductor se marca como "Activo".
 *
 * @param mysqli $conn Conexión a la base de datos
 * @return bool Retorna true si la sincronización se realizó correctamente
 */
function syncConductorEstado($conn) {
    $fechaActual = date('Y-m-d');
    $horaActual = date('H:i:s');
    $timestampActual = strtotime($fechaActual . ' ' . $horaActual);

    // No reiniciamos todos los estados a 'Activo' aquí para evitar sobreescribir
    // cambios manuales hechos desde la interfaz de conductores (ver-lista-conductor.php).

    // Obtener todas las reservas activas (futuras o en proceso)
    $sqlReservas = "SELECT conductor_nombre, conductor_apellido, conductor_telefono, fecha_reserva, hora_reserva, duracion_minutos
                    FROM reservas
                    WHERE fecha_reserva >= ?
                    GROUP BY conductor_nombre, conductor_apellido, conductor_telefono";
    
    $stmtReservas = mysqli_prepare($conn, $sqlReservas);
    if (!$stmtReservas) {
        return false;
    }

    mysqli_stmt_bind_param($stmtReservas, 's', $fechaActual);
    mysqli_stmt_execute($stmtReservas);
    $resultadoReservas = mysqli_stmt_get_result($stmtReservas);
    mysqli_stmt_close($stmtReservas);

    // Procesar cada reserva para verificar si está activa
    $conductoresOcupados = array();
    while ($reserva = mysqli_fetch_assoc($resultadoReservas)) {
        $fechaReserva = $reserva['fecha_reserva'];
        $horaReserva = $reserva['hora_reserva'];
        $duracionMinutos = (int)$reserva['duracion_minutos'];
        
        // Calcular timestamp de inicio y fin de la reserva
        $timestampInicio = strtotime($fechaReserva . ' ' . $horaReserva);
        $timestampFin = $timestampInicio + ($duracionMinutos * 60);
        
        // La reserva es activa si el timestamp actual está entre inicio y fin
        if ($timestampActual >= $timestampInicio && $timestampActual < $timestampFin) {
            $conductoresOcupados[] = array(
                'nombre' => $reserva['conductor_nombre'],
                'apellido' => $reserva['conductor_apellido'],
                'telefono' => $reserva['conductor_telefono']
            );
        }
        // O si la reserva es futura
        else if ($timestampActual < $timestampInicio) {
            $conductoresOcupados[] = array(
                'nombre' => $reserva['conductor_nombre'],
                'apellido' => $reserva['conductor_apellido'],
                'telefono' => $reserva['conductor_telefono']
            );
        }
    }

    // Actualizar estado de conductores ocupados
    foreach ($conductoresOcupados as $conductor) {
        $updateSql = "UPDATE conductor SET estado = 'Ocupado' 
                      WHERE nombre = ? AND apellido = ? AND telefono = ? LIMIT 1";
        $updateStmt = mysqli_prepare($conn, $updateSql);
        if ($updateStmt) {
            mysqli_stmt_bind_param($updateStmt, 'sss', $conductor['nombre'], $conductor['apellido'], $conductor['telefono']);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
        }
    }

    return true;
}

/**
 * Función para redirigir a usuarios no autenticados
 * Si el usuario no está logueado, lo envía automáticamente a la página de login
 * Se utiliza para proteger páginas que requieren autenticación
 * @param string $redirect Página a la que redirigir después del login (opcional)
 * @return void
 */
function redirectIfNotLogged($redirect = '') {
    // Verifica si el usuario no está logueado
    if (!isLogged()) {
        // Construir la URL de redirección
        $loginUrl = "login.php";
        if ($redirect) {
            $loginUrl .= "?redirect=" . urlencode($redirect);
        } else {
            // Si no se especifica, usar la página actual
            $currentPage = basename($_SERVER['PHP_SELF']);
            $loginUrl .= "?redirect=" . urlencode($currentPage);
        }
        // Redirige al usuario a la página de login
        header("Location: " . $loginUrl);
        // Detiene la ejecución del resto del script
        exit;
    }
}
?>
