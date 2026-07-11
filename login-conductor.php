<?php
session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['conductor'])) {
    header('Location: dashboard-conductor.php');
    exit;
}

require "database.php";
require "functions.php";

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni = clean($_POST['dni'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($dni) || empty($password)) {
        $error = 'Por favor completa todos los campos.';
    } else {
        // Buscar conductor por DNI
        $sql = "SELECT id, dni, nombre, apellido, telefono, estado FROM conductor WHERE dni = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $dni);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($result && mysqli_num_rows($result) === 1) {
                $conductor = mysqli_fetch_assoc($result);
                
                // Verificar contraseña (debe ser igual al DNI)
                if ($password === $conductor['dni']) {
                    // Login exitoso
                    $_SESSION['conductor'] = [
                        'id' => $conductor['id'],
                        'dni' => $conductor['dni'],
                        'nombre' => $conductor['nombre'],
                        'apellido' => $conductor['apellido'],
                        'telefono' => $conductor['telefono'],
                        'estado' => $conductor['estado']
                    ];
                    header('Location: dashboard-conductor.php');
                    exit;
                } else {
                    $error = 'Contraseña incorrecta.';
                }
            } else {
                $error = 'DNI no encontrado. Por favor verifica tu número de documento.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Error en la consulta de base de datos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Conductor - QuillaMovil</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #000000;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #ffffff;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            border: 2px solid transparent;
            border-radius: 18px;
            background:
                linear-gradient(rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.85)) padding-box,
                linear-gradient(90deg, #666666 0%, #666666 35%, #ff0000 65%, #ff0000 100%) border-box;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            background: rgba(0, 0, 0, 0.9);
            color: #fff;
            padding: 20px;
            text-align: center;
            border: none;
            border-bottom: 2px solid rgb(104, 100, 100);
        }

        .card-header img {
            display: block;
            width: 120px;
            max-width: 100%;
            margin: 0 auto 15px;
        }

        .card-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #fdfdfd;
        }

        .card-header p {
            margin: 8px 0 0;
            font-size: 13px;
            opacity: 0.85;
            color: #bbb;
        }

        .card-body {
            padding: 30px 25px;
            background: rgba(5, 5, 5, 0.9);
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(131, 131, 131, 0.25);
            color: #fff;
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            padding-left: 11px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: #ffffff;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(192, 1, 20, 0.15);
            outline: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .btn-login {
            background: #ff0000;
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #c70000;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(192, 1, 20, 0.35);
            color: white;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .alert-danger {
            background: rgba(255, 77, 79, 0.15);
            color: #ff9999;
            padding: 12px 16px;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.15);
            color: #90EE90;
            padding: 12px 16px;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #888;
        }

        .footer-text a {
            color: #c00114;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .footer-text a:hover {
            color: #c82333;
            text-decoration: underline;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #c00114;
            font-size: 14px;
            cursor: pointer;
            transition: color 0.2s ease;
            z-index: 10;
            pointer-events: auto;
        }

        .toggle-password:hover {
            color: #ff0000;
        }

        .password-container .form-control {
            padding-right: 38px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="card-header">
                <img src="logo.png" alt="QuillaMovil Logo">
                <h2>Conductor</h2>
                <p>Sistema de Reservas</p>
            </div>

            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="dni">DNI:</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="dni" 
                            name="dni" 
                            required
                            maxlength="8"
                            pattern="[0-9]{8}"
                            inputmode="numeric"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Contraseña:</label>
                        <div class="password-container">
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                required
                            >
                            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Ingresar
                    </button>
                </form>

                <div class="footer-text">
                    ¿Sin contraseña? Contacta al administrador
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
