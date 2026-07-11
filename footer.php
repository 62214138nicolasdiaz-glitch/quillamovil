<!-- FOOTER PROFESIONAL QUILLAMOVIL -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
        font-family: Arial, sans-serif;
    }

    .footer{
        background:#0d0d0d;
        color:#fff;
        padding:60px 8% 20px;
        
    }

    .footer-container{
        display:grid;
        grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
        gap:40px;
    }

    .footer-logo h2{
        font-size:38px;
        color:#fff;
        margin-bottom:10px;
    }

    .footer-logo span{
        color:#d60000;
    }

    .footer-logo p{
        color:#cfcfcf;
        line-height:1.7;
        margin-top:15px;
    }

    .footer-title{
        font-size:22px;
        margin-bottom:20px;
        position:relative;
    }

    .footer-title::after{
        content:'';
        position:absolute;
        left:0;
        bottom:-8px;
        width:50px;
        height:3px;
        background:#d60000;
    }

    .footer-links{
        list-style:none;
    }

    .footer-links li{
        margin:14px 0;
    }

    .footer-links a{
        text-decoration:none;
        color:#ddd;
        transition:0.3s;
    }

    .footer-links a:hover{
        color:#d60000;
        padding-left:5px;
    }

    .contact-info p{
        margin:15px 0;
        color:#ddd;
    }

    .contact-info i{
        color:#d60000;
        margin-right:10px;
    }

    .social-icons{
        margin-top:20px;
    }

    .social-icons a{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:45px;
        height:45px;
        border:2px solid #d60000;
        border-radius:50%;
        color:#fff;
        margin-right:10px;
        text-decoration:none;
        transition:0.3s;
    }

    .social-icons a:hover{
        background:#d60000;
        transform:translateY(-5px);
    }

    .footer-bottom{
        border-top:1px solid #333;
        margin-top:40px;
        padding-top:20px;
        text-align:center;
        color:#aaa;
        font-size:14px;
    }

    @media(max-width:768px){
        .footer{
            text-align:center;
        }

        .footer-title::after{
            left:50%;
            transform:translateX(-50%);
        }
    }
</style>

<footer class="footer">

    <div class="footer-container">

        <!-- LOGO -->
        <div class="footer-logo">
            <h2>Quilla<span>Movil</span></h2>
            <p>
                Servicio y Gestión de Transporte Urbano.
                Soluciones modernas, seguras y eficientes
                para empresas y ciudadanos.
            </p>
        </div>

        <!-- ENLACES -->
        <div>
            <h3 class="footer-title">Servicios</h3>
            <ul class="footer-links">
                <li><a href="#">Viajes Seguros en Quillabamba</a></li>
                <li><a href="#">Atención Disponible 24/7</a></li>
                <li><a href="#">Traslados y Recojos</a></li>
                <li><a href="#">Traslados Turísticos</a></li>
            </ul>
        </div>

        <!-- CONTACTO -->
        <div>
            <h3 class="footer-title">Contacto</h3>

            <div class="contact-info">
                <p><i class="fa-solid fa-envelope"></i> info@quillamovil.com</p>
                <p><i class="fa-solid fa-phone"></i> +51 999 999 999</p>
                <p><i class="fa-solid fa-location-dot"></i> Quillabamba-Cusco-Peru</p>
            </div>
        </div>

        <!-- REDES -->
        <div>
            <h3 class="footer-title">Síguenos</h3>

            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>

    </div>


</footer>