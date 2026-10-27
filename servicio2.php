<?php

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicio 2 | Asesoría Agrícola - AgroHumedad</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --verde: #39734b;
            --verde-oscuro: #244d35;
            --verde-claro: #edf5eb;
            --crema: #f7f5eb;
            --texto: #26352a;
            --gris: #647067;
            --borde: #dfe7dc;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body.pagina-servicio2 { margin: 0; font-family: 'Poppins', sans-serif; color: var(--texto); background: #fff; }
        .pagina-servicio2 .container { width: min(1160px, 92%); margin: 0 auto; }

        /* ENCABEZADO */
        .pagina-servicio2 .header { background: #fff; box-shadow: 0 3px 16px rgba(24,55,33,.08); position: relative; z-index: 2; }
        .pagina-servicio2 .header-contenido { width: min(1200px, 94%); min-height: 82px; margin: auto; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
        .pagina-servicio2 .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: var(--verde-oscuro); font-weight: 700; white-space: nowrap; }
        .pagina-servicio2 .logo-icono { width: 42px; height: 42px; display: grid; place-items: center; overflow: hidden; border-radius: 12px; background: var(--verde-claro); }
        .pagina-servicio2 .logo-icono img { width: 100%; height: 100%; object-fit: contain; }
        .pagina-servicio2 .logo-texto { font-size: 22px; }
        .pagina-servicio2 .logo-texto .agro { color: var(--verde); }
        .pagina-servicio2 .menu { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 7px; }
        .pagina-servicio2 .menu a { padding: 9px 11px; border-radius: 8px; color: #344338; text-decoration: none; font-size: 12px; font-weight: 600; }
        .pagina-servicio2 .menu a:hover, .pagina-servicio2 .menu a.activo { background: var(--verde-claro); color: var(--verde-oscuro); }

        /* PRESENTACIÓN */
        .pagina-servicio2 .servicio-presentacion { display: grid; grid-template-columns: 1.05fr .95fr; gap: 38px; align-items: center; margin-top: 28px; padding: clamp(24px, 4vw, 42px); border-radius: 20px; background: var(--crema); }
        .pagina-servicio2 .subtitulo { display: inline-block; margin-bottom: 10px; color: var(--verde); font-size: 11px; font-weight: 700; letter-spacing: 1.4px; }
        .pagina-servicio2 h1, .pagina-servicio2 h2, .pagina-servicio2 h3 { line-height: 1.3; }
        .pagina-servicio2 .servicio-texto h1 { margin: 0 0 16px; color: var(--verde-oscuro); font-size: clamp(29px, 4vw, 42px); }
        .pagina-servicio2 .servicio-texto > p { color: #59665b; line-height: 1.8; }
        .pagina-servicio2 .servicio-imagen { position: relative; min-width: 0; }
        .pagina-servicio2 .servicio-imagen img { display: block; width: 100%; height: 350px; object-fit: cover; border-radius: 17px; }
        .pagina-servicio2 .imagen-etiqueta { position: absolute; bottom: 15px; left: 15px; right: 15px; padding: 12px 16px; border-radius: 10px; background: rgba(255,255,255,.94); color: var(--verde-oscuro); font-size: 13px; font-weight: 600; }
        .pagina-servicio2 .servicio-datos { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; margin-top: 23px; }
        .pagina-servicio2 .dato-servicio { min-width: 0; padding: 16px; border: 1px solid var(--borde); border-radius: 12px; background: #fff; }
        .pagina-servicio2 .dato-servicio strong, .pagina-servicio2 .dato-servicio span { display: block; }
        .pagina-servicio2 .dato-servicio strong { color: var(--verde-oscuro); font-size: 14px; }
        .pagina-servicio2 .dato-servicio span { margin-top: 5px; color: var(--gris); font-size: 12px; line-height: 1.7; }

        /* PROCESO Y BENEFICIOS */
        .pagina-servicio2 .proceso-servicio, .pagina-servicio2 .beneficios-servicio { margin: 60px 0; }
        .pagina-servicio2 .titulo-seccion { max-width: 720px; margin: 0 auto 28px; text-align: center; }
        .pagina-servicio2 .titulo-seccion h2, .pagina-servicio2 .beneficios-encabezado h2, .pagina-servicio2 .texto-compromiso h2, .pagina-servicio2 .frase-final h2 { margin: 0 0 15px; color: var(--verde-oscuro); font-size: clamp(25px, 3vw, 34px); }
        .pagina-servicio2 .titulo-seccion p, .pagina-servicio2 .beneficios-encabezado > p, .pagina-servicio2 .texto-compromiso p, .pagina-servicio2 .frase-final p { color: var(--gris); line-height: 1.8; }
        .pagina-servicio2 .proceso-grid, .pagina-servicio2 .beneficios-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 16px; }
        .pagina-servicio2 .paso, .pagina-servicio2 .beneficio { min-width: 0; padding: 23px; border: 1px solid var(--borde); border-radius: 15px; background: #fff; }
        .pagina-servicio2 .paso-numero, .pagina-servicio2 .beneficio-numero { color: var(--verde); font-size: 12px; font-weight: 700; }
        .pagina-servicio2 .paso h3, .pagina-servicio2 .beneficio h3 { margin: 13px 0 8px; color: var(--verde-oscuro); font-size: 17px; }
        .pagina-servicio2 .paso p, .pagina-servicio2 .beneficio p { margin-bottom: 0; color: var(--gris); font-size: 13px; line-height: 1.8; }
        .pagina-servicio2 .beneficios-encabezado { display: flex; justify-content: space-between; align-items: end; gap: 28px; margin-bottom: 24px; }
        .pagina-servicio2 .beneficios-encabezado > p { max-width: 430px; margin: 0; }

        /* COMPROMISO */
        .pagina-servicio2 .info-final { margin: 60px 0; padding: clamp(24px,4vw,42px); border-radius: 20px; background: var(--verde-claro); }
        .pagina-servicio2 .compromiso-contenido { display: grid; grid-template-columns: 1fr 1fr; align-items: center; gap: 34px; }
        .pagina-servicio2 .texto-compromiso p { margin: 0 0 13px; }
        .pagina-servicio2 .estadisticas-servicio { display: grid; gap: 12px; }
        .pagina-servicio2 .estadistica { display: grid; grid-template-columns: 38px minmax(0,1fr); column-gap: 12px; align-items: start; padding: 17px; border-radius: 12px; background: #fff; }
        .pagina-servicio2 .estadistica-icono { width: 36px; height: 36px; display: grid; place-items: center; grid-row: span 2; border-radius: 10px; background: var(--verde-claro); color: var(--verde); font-size: 17px; }
        .pagina-servicio2 .estadistica h3 { margin: 0 0 4px; color: var(--verde-oscuro); font-size: 15px; }
        .pagina-servicio2 .estadistica p { margin: 0; color: var(--gris); font-size: 12px; line-height: 1.7; }
        .pagina-servicio2 .frase-final { margin: 60px 0 0; padding: 42px 24px; border-radius: 20px; background: var(--crema); text-align: center; }
        .pagina-servicio2 .frase-final > div { max-width: 760px; margin: auto; }
        .pagina-servicio2 .frase-icono { width: 48px; height: 48px; display: grid; place-items: center; margin: 0 auto 14px; border-radius: 50%; background: #e1eddd; color: var(--verde-oscuro); font-size: 23px; }
        .pagina-servicio2 .botones-finales { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px; margin: 30px 0 55px; }
        .pagina-servicio2 .button { display: inline-flex; justify-content: center; align-items: center; min-height: 44px; padding: 12px 19px; border: 1px solid var(--verde); border-radius: 10px; background: var(--verde); color: #fff; text-decoration: none; font-size: 13px; font-weight: 600; transition: background .2s, transform .2s; }
        .pagina-servicio2 .button:hover { transform: translateY(-1px); background: var(--verde-oscuro); }
        .pagina-servicio2 .button-secundario { border-color: var(--borde); background: #e8efe6; color: var(--verde-oscuro); }
        .pagina-servicio2 .button-secundario:hover { background: #dce8d9; }

        /* FOOTER COMPARTIDO DE AGROHUMEDAD */
        .pagina-servicio2 footer.footer { display: block; width: 100%; margin: 0; padding: 48px 24px 0; background: #203e2c; color: #fff; text-align: left; font-family: 'Poppins',sans-serif; }
        .pagina-servicio2 .footer-contenido { display: block; width: min(1120px,100%); max-width: 1120px; margin: 0 auto; padding: 0; }
        .pagina-servicio2 .footer-columnas { display: grid; grid-template-columns: minmax(0,1.15fr) minmax(0,1fr) minmax(190px,.85fr); gap: clamp(28px,4vw,58px); align-items: start; padding: 0 0 34px; }
        .pagina-servicio2 .footer-columna { display: block; min-width: 0; margin: 0; padding: 0; }
        .pagina-servicio2 .footer-marca { display: flex; flex-direction: column; align-items: flex-start; }
        .pagina-servicio2 .footer-logo { display: block; margin: 0 0 14px; padding: 0; color: #fff; font-size: 29px; line-height: 1.2; font-weight: 700; letter-spacing: -.5px; }
        .pagina-servicio2 .footer-logo .agro { display: inline; margin: 0; color: #b9d99c; font-size: inherit; letter-spacing: inherit; }
        .pagina-servicio2 .footer-descripcion { max-width: 350px; margin: 0 0 18px; padding: 0; color: #e1e9e1; font-size: 13px; line-height: 1.85; }
        .pagina-servicio2 .footer-etiqueta { display: inline-block; width: auto; margin: 0; padding: 8px 13px; border: 1px solid rgba(255,255,255,.25); border-radius: 30px; color: #f0f5ed; font-size: 11px; line-height: 1.5; }
        .pagina-servicio2 .footer-titulo { margin: 2px 0 19px; padding: 0; color: #fff; font-size: 15px; line-height: 1.5; font-weight: 700; }
        .pagina-servicio2 .footer-contacto-lista { display: grid; gap: 17px; }
        .pagina-servicio2 .footer-contacto { display: grid; grid-template-columns: 38px minmax(0,1fr); gap: 11px; align-items: start; min-width: 0; margin: 0; padding: 0; }
        .pagina-servicio2 .footer-contacto-icono { width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; margin: 0; padding: 0; border-radius: 10px; background: rgba(255,255,255,.12); color: #c8e1b6; font-size: 16px; }
        .pagina-servicio2 .footer-contacto-texto { display: block; min-width: 0; margin: 0; padding: 0; }
        .pagina-servicio2 .footer-contacto-texto strong { display: block; margin: 1px 0 4px; color: #fff; font-size: 12px; line-height: 1.5; font-weight: 600; }
        .pagina-servicio2 .footer-contacto-texto p, .pagina-servicio2 .footer-contacto-texto a { display: block; margin: 0; padding: 0; color: #e1e9e1; font-size: 12px; line-height: 1.75; text-decoration: none; overflow-wrap: anywhere; }
        .pagina-servicio2 .footer-contacto-texto a:hover { color: #c8e1b6; text-decoration: underline; }
        .pagina-servicio2 .footer-instituciones { display: grid; gap: 12px; margin: 0; padding: 0; }
        .pagina-servicio2 .footer-institucion { display: flex; align-items: center; justify-content: center; width: 100%; min-width: 0; height: 82px; margin: 0; padding: 12px; border: 1px solid rgba(255,255,255,.15); border-radius: 13px; background: rgba(255,255,255,.07); }
        .pagina-servicio2 .footer-institucion img { display: block; width: auto; height: auto; max-width: 100%; max-height: 52px; margin: 0; padding: 0; object-fit: contain; }
        .pagina-servicio2 .footer-linea { display: block; width: 100%; height: 1px; margin: 0; padding: 0; background: rgba(255,255,255,.2); }
        .pagina-servicio2 .footer-inferior { display: flex; align-items: center; justify-content: space-between; gap: 16px; min-width: 0; margin: 0; padding: 18px 0 20px; }
        .pagina-servicio2 .footer-inferior p { margin: 0; padding: 0; color: #d4e0d5; font-size: 11px; line-height: 1.7; font-weight: 400; }
        .pagina-servicio2 .footer-creditos { text-align: right; }

        /* RESPONSIVE */
        @media (max-width: 950px) {
            .pagina-servicio2 .header-contenido { align-items: flex-start; flex-direction: column; justify-content: center; padding: 15px 0; }
            .pagina-servicio2 .menu { justify-content: flex-start; }
            .pagina-servicio2 .servicio-presentacion { grid-template-columns: 1fr; }
            .pagina-servicio2 .servicio-imagen img { height: 310px; }
            .pagina-servicio2 .proceso-grid, .pagina-servicio2 .beneficios-grid { grid-template-columns: repeat(2,minmax(0,1fr)); }
            .pagina-servicio2 .footer-columnas { grid-template-columns: minmax(0,1.1fr) minmax(0,1fr); gap: 30px; }
            .pagina-servicio2 .footer-marca { grid-column: 1 / -1; }
            .pagina-servicio2 .footer-descripcion { max-width: 600px; }
            .pagina-servicio2 .footer-instituciones { grid-template-columns: repeat(2,minmax(0,1fr)); }
        }
        @media (max-width: 600px) {
            .pagina-servicio2 .menu { gap: 3px; }
            .pagina-servicio2 .menu a { padding: 8px; font-size: 11px; }
            .pagina-servicio2 .servicio-datos, .pagina-servicio2 .compromiso-contenido { grid-template-columns: 1fr; }
            .pagina-servicio2 .proceso-grid, .pagina-servicio2 .beneficios-grid { grid-template-columns: 1fr; }
            .pagina-servicio2 .servicio-imagen img { height: 240px; }
            .pagina-servicio2 .beneficios-encabezado { align-items: flex-start; flex-direction: column; gap: 0; }
            .pagina-servicio2 .footer { padding: 36px 20px 0; }
            .pagina-servicio2 .footer-columnas { grid-template-columns: minmax(0,1fr); gap: 28px; }
            .pagina-servicio2 .footer-marca { grid-column: auto; }
            .pagina-servicio2 .footer-descripcion { max-width: 100%; }
            .pagina-servicio2 .footer-instituciones { grid-template-columns: repeat(2,minmax(0,1fr)); }
            .pagina-servicio2 .footer-institucion { height: 74px; padding: 9px; }
            .pagina-servicio2 .footer-institucion img { max-height: 45px; }
            .pagina-servicio2 .footer-inferior { align-items: flex-start; flex-direction: column; gap: 4px; }
            .pagina-servicio2 .footer-creditos { text-align: left; }
            .pagina-servicio2 .botones-finales .button { width: 100%; }
        }
    </style>
</head>
<body class="pagina-servicio2">
<header class="header">
    <div class="header-contenido">
        <a href="INDEX.php" class="logo" aria-label="AgroHumedad, inicio">
            <span class="logo-icono"><img src="logo.png" alt="Logo AgroHumedad"></span>
            <span class="logo-texto"><span class="agro">Agro</span>Humedad</span>
        </a>
        <nav class="menu" aria-label="Menú principal">
            <a href="INDEX.php">Inicio</a>
            <a href="quienes_somos.html">¿Quiénes somos?</a>
            <a href="antecedentes.html">Antecedentes</a>
            <a href="fundamentos.html">Fundamentos</a>
            <a href="servicio1.php">Servicios</a>
            <a href="servicio2.php" class="activo" aria-current="page">Servicios 2</a>
            <a href="noticias.php">Noticias</a>
            <a href="politicas.html">Políticas</a>
            <a href="contacto.php">Contacto</a>
        </nav>
    </div>
</header>

<main class="container">
    <section class="servicio-presentacion">
        <div class="servicio-texto">
            <span class="subtitulo">ASESORÍA ESPECIALIZADA</span>
            <h1>Asesoría Agrícola Tecnológica</h1>
            <p>En AgroHumedad acompañamos a los agricultores en la comprensión y el uso de herramientas tecnológicas que pueden contribuir al manejo de los cultivos, al uso responsable del agua y a una producción más sostenible.</p>
            <p>La orientación propuesta busca acercar la tecnología al campo mediante información y recomendaciones generales. Las decisiones sobre un cultivo deben considerar sus características y, cuando sea necesario, la valoración de personal especializado.</p>
            <div class="servicio-datos">
                <div class="dato-servicio">
                    <strong>Mejor manejo del cultivo</strong>
                    <span>Orientación para identificar necesidades y oportunidades de mejora.</span>
                </div>
                <div class="dato-servicio">
                    <strong>Uso responsable del agua</strong>
                    <span>Buenas prácticas para reflexionar sobre el recurso hídrico.</span>
                </div>
            </div>
        </div>
        <div class="servicio-imagen">
            <img src="https://images.pexels.com/photos/2933243/pexels-photo-2933243.jpeg?auto=compress&cs=tinysrgb&w=1200" alt="Cultivo agrícola y trabajo en el campo" loading="lazy">
            <div class="imagen-etiqueta">Tecnología aplicada al campo</div>
        </div>
    </section>

    <section class="proceso-servicio">
        <div class="titulo-seccion">
            <span class="subtitulo">NUESTRO PROCESO</span>
            <h2>Así se estructura la asesoría</h2>
            <p>Estas etapas sirven como guía general para analizar necesidades y explorar cómo la tecnología puede apoyar el manejo de los cultivos.</p>
        </div>
        <div class="proceso-grid">
            <article class="paso"><span class="paso-numero">01</span><h3>Diagnóstico</h3><p>Se identifican las condiciones y necesidades del cultivo, teniendo en cuenta el suelo, el agua y el contexto de producción.</p></article>
            <article class="paso"><span class="paso-numero">02</span><h3>Selección de herramientas</h3><p>Se consideran herramientas tecnológicas apropiadas para el seguimiento de variables agrícolas cuando se dispone de los recursos necesarios.</p></article>
            <article class="paso"><span class="paso-numero">03</span><h3>Interpretación</h3><p>Se revisa la información disponible para comprender las condiciones observadas y apoyar la toma de decisiones.</p></article>
            <article class="paso"><span class="paso-numero">04</span><h3>Acompañamiento</h3><p>Se promueve el uso adecuado de las herramientas y la revisión de las prácticas de manejo implementadas.</p></article>
        </div>
    </section>

    <section class="beneficios-servicio">
        <div class="beneficios-encabezado">
            <div><span class="subtitulo">VENTAJAS</span><h2>Beneficios de la orientación agrícola</h2></div>
            <p>El aprovechamiento de la tecnología puede ayudar a organizar información y revisar prácticas de manejo de los recursos agrícolas.</p>
        </div>
        <div class="beneficios-grid">
            <article class="beneficio"><span class="beneficio-numero">01</span><h3>Cuidado del agua</h3><p>Promueve la reflexión sobre el uso responsable del recurso hídrico.</p></article>
            <article class="beneficio"><span class="beneficio-numero">02</span><h3>Manejo del cultivo</h3><p>Ayuda a reconocer variables que pueden influir en el desarrollo de las plantas.</p></article>
            <article class="beneficio"><span class="beneficio-numero">03</span><h3>Organización de datos</h3><p>Facilita revisar la información disponible antes de tomar decisiones.</p></article>
            <article class="beneficio"><span class="beneficio-numero">04</span><h3>Decisiones informadas</h3><p>Invita a considerar datos, contexto y orientación especializada cuando se requiera.</p></article>
        </div>
    </section>

    <section class="info-final">
        <div class="compromiso-contenido">
            <div class="texto-compromiso">
                <span class="subtitulo">¿POR QUÉ AGROHUMEDAD?</span>
                <h2>Tecnología y conocimiento al servicio del campo</h2>
                <p>AgroHumedad busca acercar conceptos tecnológicos al contexto agrícola mediante recursos claros y fáciles de consultar. La propuesta promueve la comprensión de las variables relacionadas con el suelo y el uso del agua, así como la importancia de interpretar la información de manera responsable.</p>
                <p>La página tiene fines informativos y académicos; no reemplaza una asesoría técnica profesional ni garantiza resultados específicos para los cultivos.</p>
            </div>
            <div class="estadisticas-servicio">
                <article class="estadistica"><span class="estadistica-icono" aria-hidden="true">⌁</span><h3>Cultivos</h3><p>Información para comprender mejor las necesidades del cultivo.</p></article>
                <article class="estadistica"><span class="estadistica-icono" aria-hidden="true">≈</span><h3>Agua</h3><p>Promoción de prácticas responsables con el recurso hídrico.</p></article>
                <article class="estadistica"><span class="estadistica-icono" aria-hidden="true">✓</span><h3>Acompañamiento</h3><p>Orientación general para conocer y aprovechar herramientas tecnológicas.</p></article>
            </div>
        </div>
    </section>

    <section class="frase-final">
        <div class="frase-icono" aria-hidden="true">✦</div>
        <div>
            <span class="subtitulo">AGROHUMEDAD</span>
            <h2>Tecnología y conocimiento para el campo</h2>
            <p>Una propuesta para acercar herramientas tecnológicas y promover el uso responsable de la información agrícola.</p>
        </div>
    </section>

    <div class="botones-finales">
        <a href="servicio1.php" class="button button-secundario">Servicio 1: Humedad del suelo</a>
        <a href="INDEX.php" class="button">Volver al inicio</a>
    </div>
</main>

<footer class="footer">
    <div class="footer-contenido">
        <div class="footer-columnas">
            <section class="footer-columna footer-marca" aria-label="Información de AgroHumedad">
                <h2 class="footer-logo"><span class="agro">Agro</span>Humedad</h2>
                <p class="footer-descripcion">AgroHumedad acerca herramientas tecnológicas e información agrícola para promover el aprendizaje y el uso responsable de los recursos del campo.</p>
                <span class="footer-etiqueta">Proyecto académico</span>
            </section>

            <section class="footer-columna" aria-labelledby="footer-contacto-titulo">
                <h3 class="footer-titulo" id="footer-contacto-titulo">Información de contacto</h3>
                <div class="footer-contacto-lista">
                    <div class="footer-contacto">
                        <span class="footer-contacto-icono" aria-hidden="true">@</span>
                        <div class="footer-contacto-texto"><strong>Correo electrónico</strong><p>Agregar el correo oficial del proyecto.</p></div>
                    </div>
                    <div class="footer-contacto">
                        <span class="footer-contacto-icono" aria-hidden="true">☎</span>
                        <div class="footer-contacto-texto"><strong>Teléfono</strong><p>Agregar el teléfono de contacto.</p></div>
                    </div>
                    <div class="footer-contacto">
                        <span class="footer-contacto-icono" aria-hidden="true">⌖</span>
                        <div class="footer-contacto-texto"><strong>Ubicación</strong><p>Medellín, Antioquia, Colombia.</p></div>
                    </div>
                </div>
            </section>

            <section class="footer-columna" aria-labelledby="footer-instituciones-titulo">
                <h3 class="footer-titulo" id="footer-instituciones-titulo">Instituciones vinculadas</h3>
                <div class="footer-instituciones">
                    <div class="footer-institucion"><img src="img/pascual_bravo.png" alt="Institución Universitaria Pascual Bravo" loading="lazy"></div>
                    <div class="footer-institucion"><img src="img/IE-Republica-de-Uruguay.png" alt="Institución Educativa República de Uruguay" loading="lazy"></div>
                </div>
            </section>
        </div>
        <div class="footer-linea"></div>
        <div class="footer-inferior">
            <p>&copy; <?= date('Y') ?> AgroHumedad. Todos los derechos reservados.</p>
            <p class="footer-creditos">Desarrollado como proyecto académico.</p>
        </div>
    </div>
</footer>
</body>
</html>
