<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>QuillaMovil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lobster&family=Poppins:wght@300;400;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: #000;
            color: #fff;
        }
        .card {
            background: #000;
            border: 2px solid #ff0000;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            border-radius: 16px;
            width: 100%;
            max-width: 760px;
            margin: 1.5rem auto;
        }
        .row.justify-content-center {
            justify-content: center !important;
        }
        @media (max-width: 768px) {
            .container {
                width: 100%;
                max-width: 100%;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            .card {
                max-width: 100%;
                margin: 0.9rem auto;
                border-radius: 12px;
            }
        }
        .card-header, .card-body h1 {
            color: #fff;
            font-family: 'Lobster', cursive;
        }
        .btn-danger {
            background-color: #d60f0f;
            border-color: #d60f0f;
            color: #fff;
        }
        .btn-danger:hover {
            background-color: #a30c0c;
            border-color: #a30c0c;
        }
        .btn-outline-danger:hover,
        .btn-outline-danger:focus {
            background-color: #d70000;
            border-color: #d70000;
            color: #ffffff;
        }
        .btn-login {
            border: 2px solid red;
            padding: 12px 30px;
            border-radius: 10px;
            color: white;
            text-decoration: none;
            font-size: 20px;
            background: transparent;
        }
        .form-control {
            background: #111;
            color: #fff;
            border: 1px solid #ff0000;
            width: 100%;
        }
        .form-label {
            color: #fff;
        }
        h1, h3, label {
            color: #fff !important;
        }
        .torito-rojo, .camion-rojo, .motocarga-rojo, .conductor-rojo {
            color: #ff0000 !important;
        }
        @media (max-width: 768px) {
            .container {
                width: 100%;
                max-width: 100%;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            .row.justify-content-center {
                margin-left: 0;
                margin-right: 0;
            }
            .col-md-8 {
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
            }
            .card {
                max-width: 100%;
                margin: 0.9rem auto;
                border-radius: 14px;
                padding: 20px;
            }
            .card h2 {
                font-size: 1.6rem;
            }
            .form-label {
                font-size: 0.98rem;
            }
            .btn {
                width: 100%;
            }
        }
        @media (max-width: 576px) {
            body {
                background-size: cover;
            }
            .card {
                border-width: 1px;
            }
            .form-control {
                font-size: 0.95rem;
            }
            .btn-login {
                width: 100%;
                text-align: center;
            }
        }
        /* Placeholder and date/time input color tweaks */
        ::placeholder { color: rgba(255,255,255,0.75); opacity: 1; }
        input::placeholder, textarea::placeholder { color: rgba(255,255,255,0.75); }

        /* Ensure date/time inputs show white text across browsers */
        input[type="date"], input[type="time"] {
            color: #fff;
            background: #222;
        }
        input[type="date"]::-webkit-datetime-edit, input[type="time"]::-webkit-datetime-edit {
            color: #fff;
        }
        input[type="date"]::-webkit-inner-spin-button, input[type="time"]::-webkit-inner-spin-button {
            color: #fff;
        }
        /* Firefox specific */
        input[type="date"]::-moz-focus-inner, input[type="time"]::-moz-focus-inner {
            color: #fff;
        }
    </style>
</head>
<body>
