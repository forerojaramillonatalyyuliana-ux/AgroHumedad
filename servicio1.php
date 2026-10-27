<?php
$anioActual = date('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Conoce el servicio de monitoreo de humedad del suelo de AgroHumedad y sus beneficios para el uso responsable del agua.">
    <title>Servicio 1 | Monitoreo de Humedad - AgroHumedad</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Ajustes específicos del pie de página para evitar conflictos con style.css */
        .footer-ah, .footer-ah * { box-sizing: border-box; }
        .footer-ah {
            width: 100%; margin: 0; padding: 44px 24px 0;
            background: #203e2c; color: #fff; text-align: left;
            font-family: 'Poppins', sans-serif;
        }
        .footer-ah .footer-ah-contenido { width: min(1120px, 100%); margin: 0 auto; }
        .footer-ah .footer-ah-columnas {
            display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr) minmax(220px, .9fr);
            gap: clamp(24px, 4vw, 54px); align-items: start; padding-bottom: 32px;
        }
        .footer-ah .footer-ah-columna { min-width: 0; margin: 0; padding: 0; }
        .footer-ah .footer-ah-marca { display: flex; flex-direction: column; align-items: flex-start; }
        .footer-ah .footer-ah-logo { margin: 0 0 14px; padding: 0; color: #fff; font-size: 27px; font-weight: 700; line-height: 1.25; }
        .footer-ah .footer-ah-logo span { color: #b9d99c; }
        .footer-ah .footer-ah-descripcion { max-width: 340px; margin: 0 0 18px; color: #e0e9e1; font-size: 13px; line-height: 1.85; }
        .footer-ah .footer-ah-etiqueta { display: inline-block; padding: 7px 12px; border: 1px solid rgba(255,255,255,.23); border-radius: 30px; color: #edf4ed; font-size: 11px; }
        .footer-ah .footer-ah-titulo { margin: 3px 0 18px; padding: 0; color: #fff; font-size: 15px; font-weight: 600; line-height: 1.4; }
        .footer-ah .footer-ah-contacto-lista { display: grid; gap: 16px; }
        .footer-ah .footer-ah-contacto-item { display: grid; grid-template-columns: 36px minmax(0,1fr); gap: 11px; align-items: start; margin: 0; padding: 0; }
        .footer-ah .footer-ah-icono { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: rgba(255,255,255,.11); color: #c9e3bd; font-size: 14px; }
        .footer-ah .footer-ah-contacto-item strong { display: block; margin: 0 0 4px; color: #fff; font-size: 12px; font-weight: 600; }
        .footer-ah .footer-ah-contacto-item p, .footer-ah .footer-ah-contacto-item a { display: block; margin: 0; padding: 0; color: #dce7dd; font-size: 12px; line-height: 1.75; text-decoration: none; overflow-wrap: anywhere; }
        .footer-ah .footer-ah-contacto-item a:hover { color: #fff; text-decoration: underline; }
        .footer-ah .footer-ah-instituciones { display: grid; gap: 12px; }
        .footer-ah .footer-ah-institucion { min-height: 76px; display: flex; align-items: center; justify-content: center; padding: 10px 14px; border: 1px solid rgba(255,255,255,.14); border-radius: 12px; background: rgba(255,255,255,.07); }
        .footer-ah .footer-ah-institucion img { display: block; width: auto; height: auto; max-width: 155px; max-height: 52px; object-fit: contain; }
        .footer-ah .footer-ah-linea { height: 1px; width: 100%; margin: 0; background: rgba(255,255,255,.2); }
        .footer-ah .footer-ah-inferior { display: flex; align-items: center; justify-content: space-between; gap: 12px 24px; flex-wrap: wrap; padding: 17px 0 20px; }
        .footer-ah .footer-ah-inferior p { margin: 0; padding: 0; color: #cbd9cd; font-size: 11px; line-height: 1.7; }
        @media (max-width: 780px) {
            .footer-ah .footer-ah-columnas { grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap: 28px; }
            .footer-ah .footer-ah-marca { grid-column: 1 / -1; }
            .footer-ah .footer-ah-descripcion { max-width: 620px; }
            .footer-ah .footer-ah-instituciones { grid-template-columns: repeat(2, minmax(0,1fr)); }
            .footer-ah .footer-ah-institucion { min-width: 0; }
        }
        @media (max-width: 520px) {
            .footer-ah { padding: 34px 20px 0; }
            .footer-ah .footer-ah-columnas { grid-template-columns: minmax(0,1fr); gap: 26px; padding-bottom: 25px; }
            .footer-ah .footer-ah-marca { grid-column: auto; }
            .footer-ah .footer-ah-titulo { margin-bottom: 13px; }
            .footer-ah .footer-ah-instituciones { grid-template-columns: repeat(2, minmax(0,1fr)); gap: 9px; }
            .footer-ah .footer-ah-institucion { min-height: 68px; padding: 8px; }
            .footer-ah .footer-ah-institucion img { max-width: 100%; max-height: 44px; }
            .footer-ah .footer-ah-inferior { align-items: flex-start; flex-direction: column; gap: 5px; padding: 15px 0 18px; }
        }
    </style>
</head>
<body>
<header class="header">
    <div class="header-contenido">
        <a href="INDEX.php" class="logo" aria-label="AgroHumedad, inicio">
            <span class="logo-icono"><img src="logo.png" alt="Logo AgroHumedad"></span>
            <span class="logo-texto"><span class="agro">Agro</span>Humedad</span>
        </a>
        <nav class="menu" aria-label="Menú principal">
            <a href="INDEX.php">INICIO</a>
            <a href="quienes_somos.html">¿QUIÉNES SOMOS?</a>
            <a href="antecedentes.html">ANTECEDENTES</a>
            <a href="fundamentos.html">FUNDAMENTOS</a>
            <a href="servicio1.php" class="activo" aria-current="page">SERVICIOS</a>
            <a href="noticias.php">NOTICIAS</a>
            <a href="politicas.html">POLÍTICAS</a>
            <a href="contacto.php">CONTACTO</a>
        </nav>
    </div>
</header>

<main class="container">
    <section class="servicio-presentacion" style="background-color:#f7f5eb;border-radius:20px;padding:clamp(24px,4vw,40px);margin-top:20px;">
        <div class="servicio-texto">
            <span class="subtitulo">MONITOREO INTELIGENTE</span>
            <h1>Monitoreo inteligente de la humedad del suelo</h1>
            <p>Este servicio presenta cómo el monitoreo de la humedad del suelo puede ayudar a conocer las condiciones de un cultivo. Cuando se dispone de sensores y de un sistema de registro conectado, la información puede apoyar la planificación del riego, el uso responsable del agua y el seguimiento de las condiciones del suelo.</p>
            <div class="servicio-datos">
                <div class="dato-servicio"><div><strong>Humedad</strong><span>Información sobre las condiciones del suelo.</span></div></div>
                <div class="dato-servicio"><div><strong>Monitoreo</strong><span>Seguimiento de los valores registrados.</span></div></div>
            </div>
            <p class="nota-servicio">La página es informativa. No muestra mediciones en tiempo real ni se conecta a sensores desde esta sección.</p>
        </div>
        <div class="servicio-imagen">
            <img src="https://images.pexels.com/photos/2255935/pexels-photo-2255935.jpeg?auto=compress&cs=tinysrgb&w=1200" alt="Cultivo agrícola y suelo">
            <div class="imagen-etiqueta"><span>Tecnología aplicada al campo</span></div>
        </div>
    </section>

    <section class="proceso-servicio">
        <div class="titulo-seccion">
            <span class="subtitulo">PASO A PASO</span>
            <h2>¿Cómo funciona?</h2>
            <p>Un sistema de monitoreo puede organizar la recolección y la interpretación de los datos para apoyar las decisiones relacionadas con el riego.</p>
        </div>
        <div class="proceso-grid">
            <article class="paso"><span class="paso-numero">01</span><h3>Instalación</h3><p>Los sensores se ubican en puntos adecuados del cultivo, según sus características y necesidades.</p></article>
            <article class="paso"><span class="paso-numero">02</span><h3>Recolección</h3><p>Los dispositivos registran valores de humedad de acuerdo con su configuración.</p></article>
            <article class="paso"><span class="paso-numero">03</span><h3>Análisis</h3><p>Los datos se revisan para comprender las condiciones del suelo y reconocer cambios.</p></article>
            <article class="paso"><span class="paso-numero">04</span><h3>Toma de decisiones</h3><p>La información puede orientar la planificación del riego y ayudar a evitar desperdicios.</p></article>
        </div>
    </section>

    <section class="beneficios-servicio">
        <div class="beneficios-encabezado">
            <div><span class="subtitulo">VENTAJAS</span><h2>Beneficios del servicio</h2></div>
            <p>Conocer las condiciones del suelo ayuda a planificar mejor las labores agrícolas y a utilizar los recursos de manera responsable.</p>
        </div>
        <div class="beneficios-grid">
            <article class="beneficio"><div><span class="beneficio-numero">01</span><h3>Ahorro de agua</h3><p>Contribuye a planificar el riego según la información disponible.</p></div></article>
            <article class="beneficio"><div><span class="beneficio-numero">02</span><h3>Productividad</h3><p>Apoya el seguimiento de las condiciones que influyen en el desarrollo del cultivo.</p></div></article>
            <article class="beneficio"><div><span class="beneficio-numero">03</span><h3>Uso de recursos</h3><p>Facilita la identificación de oportunidades para aprovechar mejor los recursos.</p></div></article>
            <article class="beneficio"><div><span class="beneficio-numero">04</span><h3>Seguimiento</h3><p>Permite revisar los cambios en los valores cuando existen registros disponibles.</p></div></article>
        </div>
    </section>

    <section class="compromiso-servicio">
        <div class="compromiso-contenido">
            <div class="texto-compromiso">
                <span class="subtitulo">NUESTRO PROPÓSITO</span>
                <h2>Tecnología para apoyar al agricultor</h2>
                <p>AgroHumedad busca acercar herramientas tecnológicas al contexto agrícola para facilitar la comprensión de los datos relacionados con la humedad del suelo y promover decisiones responsables sobre el uso del agua.</p>
                <p>La utilidad del monitoreo depende de la calidad de los datos, de las características del cultivo y de una interpretación adecuada de las condiciones del terreno.</p>
            </div>
            <div class="estadisticas-servicio">
                <article class="estadistica"><h3>Uso eficiente</h3><p>Promoción de prácticas responsables con el recurso hídrico.</p></article>
                <article class="estadistica"><h3>Mejores cultivos</h3><p>Información para comprender las condiciones del suelo.</p></article>
                <article class="estadistica"><h3>Tecnología</h3><p>Herramientas digitales para organizar y consultar datos agrícolas.</p></article>
            </div>
        </div>
    </section>

    <section class="frase-final">
        <div>
            <span class="subtitulo">AGROHUMEDAD</span>
            <h2>Tecnología para una agricultura más informada</h2>
            <p>El conocimiento de las condiciones del suelo puede contribuir a un manejo más consciente de los recursos y al cuidado de los cultivos.</p>
        </div>
    </section>

    <div class="botones-finales">
        <a href="INDEX.php" class="button">Volver al inicio</a>
        <a href="servicio2.php" class="button button-secundario">Servicio 2</a>
    </div>
</main>

<footer class="footer-ah">
    <div class="footer-ah-contenido">
        <div class="footer-ah-columnas">
            <section class="footer-ah-columna footer-ah-marca" aria-label="Información de AgroHumedad">
                <h2 class="footer-ah-logo"><span>Agro</span>Humedad</h2>
                <p class="footer-ah-descripcion">AgroHumedad acerca herramientas tecnológicas e información agrícola para promover el aprendizaje y el uso responsable de los recursos del campo.</p>
                <span class="footer-ah-etiqueta">Proyecto académico</span>
            </section>
            <section class="footer-ah-columna" aria-labelledby="footer-ah-contacto">
                <h3 class="footer-ah-titulo" id="footer-ah-contacto">Información de contacto</h3>
                <div class="footer-ah-contacto-lista">
                    <div class="footer-ah-contacto-item"><span class="footer-ah-icono" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span><div><strong>Correo electrónico</strong><a href="mailto:tecnologiaagricola@gmail.com">tecnologiaagricola@gmail.com</a></div></div>
                    <div class="footer-ah-contacto-item"><span class="footer-ah-icono" aria-hidden="true"><i class="fa-solid fa-comments"></i></span><div><strong>Formulario de contacto</strong><a href="contacto.php">Escríbenos desde la página de contacto</a></div></div>
                    <div class="footer-ah-contacto-item"><span class="footer-ah-icono" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span><div><strong>Ubicación</strong><p>Medellín, Antioquia, Colombia</p></div></div>
                </div>
            </section>
            <section class="footer-ah-columna" aria-labelledby="footer-ah-instituciones">
                <h3 class="footer-ah-titulo" id="footer-ah-instituciones">Instituciones vinculadas</h3>
                <div class="footer-ah-instituciones">
                    <div class="footer-ah-institucion"><img src="img/pascual_bravo.png" alt="Institución Universitaria Pascual Bravo" loading="lazy"></div>
                    <div class="footer-ah-institucion"><img src="img/IE-Republica-de-Uruguay.png" alt="Institución Educativa República de Uruguay" loading="lazy"></div>
                </div>
            </section>
        </div>
        <div class="footer-ah-linea"></div>
        <div class="footer-ah-inferior">
            <p>&copy; <?= $anioActual ?> AgroHumedad. Todos los derechos reservados.</p>
            <p>Desarrollado como proyecto académico.</p>
        </div>
    </div>
</footer>
</body>
</html>
