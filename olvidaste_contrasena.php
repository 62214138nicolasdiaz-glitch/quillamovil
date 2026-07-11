<?php
session_start();
require "database.php";
require "functions.php";

$mensaje = '';
$tipoMensaje = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');

    if ($username === '') {
        $mensaje = 'Por favor ingresa tu usuario.';
    } else {
        $usernameEscaped = mysqli_real_escape_string($conn, $username);
        $sql = "SELECT id FROM users WHERE username = '$usernameEscaped' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $mensaje = 'Si existe el usuario, se enviaron instrucciones de recuperación al correo asociado.';
            $tipoMensaje = 'success';
        } else {
            $mensaje = 'Si existe el usuario, se enviaron instrucciones de recuperación al correo asociado.';
            $tipoMensaje = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - Quillamovil</title>
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
            max-width: 720px;
            margin: 60px auto 24px;
            padding: 12px;
        }

        .login-card {
            width: 100%;
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

        .card-header img.login-logo {
            display: block;
            width: 300px;
            max-width: 100%;
            margin: 0 auto 16px auto;
        }

        .card-header h3 {
            margin: 0;
            font-weight: 700;
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
            color: #111;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-label i {
            font-size: 1rem;
            color: #ff6500;
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
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            font-size: 14px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .link-actions a,
        .remember-forgot a {
            color: #ffffff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .link-actions a:hover,
        .remember-forgot a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }

        .alert-danger {
            background-color: #ffe8e8;
            color: #b30000;
        }

        .alert-success {
            background-color: #eff7e8;
            color: #1f6f00;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="card-header">
                <h3>RECUPERAR CONTRASEÑA</h3>
                <p>Ingresa tu usuario para recibir instrucciones</p>
            </div>
            <div class="card-body">
                <?php if ($mensaje !== ''): ?>
                    <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="olvidaste_contrasena.php" method="POST">
                    <div class="form-group">
                        <label for="username" class="form-label">
                             Usuario
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input
                                type="text"
                                class="form-control"
                                id="username"
                                name="username"
                                placeholder="Ingresa tu usuario"
                                autocomplete="username"
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login text-white">
                        <i class="fas fa-envelope"></i> Enviar instrucciones
                    </button>
                </form>

                <div class="link-actions mt-4">
                    <a href="login.php">Volver a iniciar sesión</a>
                    <a href="crear_cuenta.php">Crear cuenta</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
