<?php
session_start();
require "database.php";
require "functions.php";

$mensaje = '';
$tipoMensaje = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($username === '' || $password === '' || $password_confirm === '') {
        $mensaje = 'Por favor completa todos los campos.';
    } elseif ($password !== $password_confirm) {
        $mensaje = 'Las contraseñas no coinciden.';
    } else {
        $usernameEscaped = mysqli_real_escape_string($conn, $username);
        $checkSql = "SELECT id FROM users WHERE username = '$usernameEscaped' LIMIT 1";
        $checkResult = mysqli_query($conn, $checkSql);

        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $mensaje = 'El nombre de usuario ya existe, elige otro.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $newUser = createUser($conn, $username, $passwordHash);

            if ($newUser !== false) {
                $mensaje = 'Cuenta creada correctamente. Ya puedes iniciar sesión.';
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'No se pudo crear la cuenta. Intenta de nuevo.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Quillamovil</title>
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
            margin: 60px auto 24px;
            padding: 12px;
        }

        .login-card {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            border: 2px solid transparent;
            border-radius: 18px;
            background:
                linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.75)) padding-box,
                linear-gradient(90deg, #999999 0%, #999999 35%, #ff0000 65%, #ff0000 100%) border-box;
            background-clip: padding-box, border-box;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.35);
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
            background: rgba(0, 0, 0, 0.75);
            color: #fff;
            padding: 10px 12px;
            text-align: center;
            border: none;
            border-bottom: 1px solid #ff0000;
        }

        .card-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 30px;
            letter-spacing: 0.02em;
        }

        .card-header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
            color: #fff;
        }

        .card-body {
            padding: 18px;
            background-color: rgba(0, 0, 0, 0.55);
            color: #fff;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            border: 2px solid #ff0000;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        .form-control:focus {
            border-color: #ff0000;
            box-shadow: 0 0 0 0.2rem rgba(255, 0, 0, 0.18);
            background-color: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .form-control::placeholder {
            color: #888;
        }

        .input-group-text {
            background-color: rgba(255, 255, 255, 0.08);
            border: 2px solid #ff0000;
            color: #fff;
            border-right: none;
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .input-group .form-control {
            border-left: none;
        }

        .input-group .form-control:focus {
            border-color: #ff0000;
        }

        .btn-toggle-password {
            background-color: rgba(255, 255, 255, 0.08);
            border: 2px solid #ff0000;
            color: #fff;
            border-left: none;
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
            cursor: pointer;
            padding: 10px 12px;
        }

        .btn-toggle-password:hover {
            background-color: rgba(255, 255, 255, 0.12);
        }

        .btn-login {
            background: #ff1f1f;
            border: none;
            border-radius: 10px;
            padding: 12px 18px;
            font-weight: 700;
            font-size: 16px;
            width: 100%;
            transition: all 0.25s ease;
            color: #ffffff;
        }

        .btn-login:hover {
            background: #ff0000;
            transform: translateY(-2px);
            color: #ffffff;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .link-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 10px;
            gap: 8px;
            font-size: 14px;
            flex-wrap: wrap;
        }

        .link-actions a {
            color: #ff1f1f;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .link-actions a:hover {
            color: #ff0000;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <div class="card-header">
            <h3>Crear Cuenta</h3>
            <p>Regístrate para acceder a Quillamovil.</p>
        </div>
        <div class="card-body">
            <?php if ($mensaje !== ''): ?>
                <div class="alert alert-<?php echo $tipoMensaje; ?>">
                    <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <form action="crear_cuenta.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="username">Usuario:</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Ingresa tu usuario" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Contraseña:</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                        <button class="btn-toggle-password" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirm">Confirmar contraseña:</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Repite tu contraseña" required>
                        <button class="btn-toggle-password" type="button" id="togglePasswordConfirm">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn-login">Crear cuenta</button>
                </div>
            </form>
            <div class="link-actions">
                <a href="login.php">Volver a iniciar sesión</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
        const passwordInput = document.getElementById('password_confirm');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
</script>
</body>
</html>
