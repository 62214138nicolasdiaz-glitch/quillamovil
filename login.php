<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$redirect = $_GET['redirect'] ?? '';
$error = $_GET['error'] ?? '';
$errorMessage = '';
if ($error === 'empty') {
    $errorMessage = 'Por favor completa todos los campos.';
} elseif ($error === 'wrong_password') {
    $errorMessage = 'Contraseña incorrecta.';
} elseif ($error === 'no_account') {
    $errorMessage = 'El usuario no existe. Crea una cuenta primero.';
} elseif ($error === 'credentials') {
    $errorMessage = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Quillamovil</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
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

        .input-group .form-control:focus + .input-group-text {
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

        .remember-forgot {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
            color: #111;
            gap: 16px;
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
            color: #ebebeb;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .link-actions a:hover,
        .remember-forgot a:hover {
            color: #f9f9ff;
            text-decoration: underline;
        }

        .form-check-input {
            border-color: #111;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #ff3b3b;
            border-color: #ff3b3b;
        }

        .form-check-input:focus {
            border-color: #ff6500;
            box-shadow: 0 0 0 0.25rem rgba(255, 101, 0, 0.18);
        }

        .form-check-label {
            cursor: pointer;
            margin: 0;
            color: #111;
            font-size: 14px;
        }

        .divider {
            text-align: center;
            margin: 28px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #ddd;
        }

        .divider span {
            background-color: #fff;
            padding: 0 12px;
            color: #111;
            font-size: 13px;
            position: relative;
        }

        .social-login {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn-social {
            border: 2px solid #111;
            border-radius: 10px;
            padding: 12px 35px;
            font-size: 18px;
            font-weight: 700;
            transition: all 0.3s ease;
            background-color: #fff;
            color: #111;
        }

        .btn-social:hover {
            background-color: #383636;
            color: #fff;
            border-color: #000;
        }

        

        .card-footer {
            background-color: #111111;
            border-top: 1px solid #222222;
            padding: 20px;
            text-align: center;
        }

        .card-footer p {
            margin: 0;
            color: #fff;
            font-size: 14px;
        }

        .card-footer a {
            color: #ad0303;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .card-footer a:hover {
            color: #ff3b3b;
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
            <!-- Header -->
            <div class="card-header">
                <img src="logo.png" alt="Logo Quillamovil" class="login-logo">
                <h3> BIENVENIDO</h3>
                <p>Inicia sesión para continuar</p>
            </div>

            <!-- Body -->
            <div class="card-body">
                <?php if ($errorMessage !== ''): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" action="validate.php">
                    <!-- Usuario -->
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
                                placeholder="Ingresa tu nombre de usuario"
                                autocomplete="username"
                                autofocus
                                required
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                           Contraseña
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-key"></i>
                            </span>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="Tu contraseña"
                                required
                            >
                            <button 
                                class="btn btn-outline-secondary" 
                                type="button" 
                                id="togglePassword"
                                style="border: 2px solid #e0e0e0; border-left: none;"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="link-actions">
                        <a href="crear_cuenta.php">Crear cuenta</a>
                        <a href="olvidaste_contrasena.php">¿Olvidaste tu contraseña?</a>
                    </div>

                  

                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

                    <!-- Login Button -->
                    <button type="submit" class="btn btn-login text-white">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                </form>

                <!-- Divider -->
                <div class="divider">
                    <span>continúa con</span>
                </div>

                <!-- Social Login -->
                <div class="social-login">
                    <a href="login-google.php" class="btn btn-social">
                        <i class="fab fa-google"></i> Google
                    </a>
                  
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script para mostrar/ocultar contraseña -->
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
    </script>
</body>
</html>
