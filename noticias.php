<?php
session_start();
mysqli_report(MYSQLI_REPORT_OFF);

$noticias = [];
$errorNoticias = '';

$usuarioEsAdmin = isset($_SESSION['usuario_id'])
    && strcasecmp(
        trim((string)($_SESSION['usuario_rol'] ?? '')),
        'Administrador'
    ) === 0;

/* Escapar texto para mostrarlo de forma segura en HTML. */
function eNoticia($valor): string {
    return htmlspecialchars(
        (string)($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

/*
 * Validar la ruta de la imagen.
 * Admite URL HTTP/HTTPS e imágenes guardadas localmente.
 */
function rutaImagenNoticia($imagen): ?string {
    $ruta = trim((string)($imagen ?? ''));

    if ($ruta === '') {
        return null;
    }

    // Admitir URL externas HTTP o HTTPS.
    if (filter_var($ruta, FILTER_VALIDATE_URL)) {
        $esquema = strtolower(
            (string)parse_url($ruta, PHP_URL_SCHEME)
        );

        if (in_array($esquema, ['http', 'https'], true)) {
            return $ruta;
        }

        return null;
    }

    // No admitir esquemas, rutas de red ni recorridos de directorios.
    if (
        preg_match('#^[a-z][a-z0-9+.-]*:#i', $ruta) ||
        str_starts_with($ruta, '//') ||
        str_contains($ruta, '..') ||
        str_contains($ruta, '\\') ||
        str_contains($ruta, "\0")
    ) {
        return null;
    }

    // Solo aceptar extensiones de imagen conocidas.
    $extension = strtolower(
        pathinfo($ruta, PATHINFO_EXTENSION)
    );

    $extensionesPermitidas = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'gif'
    ];

    if (!in_array($extension, $extensionesPermitidas, true)) {
        return null;
    }

    return $ruta;
}

/* Consultar noticias publicadas. */
try {
    require_once __DIR__ . '/conexion.php';

    if (
        !isset($conexion) ||
        !($conexion instanceof mysqli) ||
        $conexion->connect_errno
    ) {
        throw new RuntimeException(
            'No se encontró una conexión válida en conexion.php.'
        );
    }

    $conexion->set_charset('utf8mb4');
    $conexion->select_db('sistema_de_riesgo');

    $consulta = $conexion->query(
        "SELECT
            id_noticia,
            titulo,
            categoria,
            resumen,
            contenido,
            imagen_url,
            fuente_url,
            fecha_publicacion,
            fecha_creacion
         FROM noticias
         WHERE estado = 'Publicado'
         ORDER BY
            COALESCE(fecha_publicacion, fecha_creacion) DESC,
            id_noticia DESC"
    );

    if (!$consulta) {
        throw new RuntimeException($conexion->error);
    }

    while ($fila = $consulta->fetch_assoc()) {
        $fila['ruta_imagen_mostrar'] = rutaImagenNoticia(
            $fila['imagen_url'] ?? ''
        );

        $noticias[] = $fila;
    }

    $consulta->free();

} catch (Throwable $ex) {
    error_log(
        'Error cargando noticias.php: ' . $ex->getMessage()
    );

    $errorNoticias =
        'No se pudieron cargar las noticias. Verifica que exista ' .
        'la tabla noticias y revisa la conexión con la base de datos.';
}

/* Formatear la fecha de publicación. */
function fechaNoticia($fecha): string {
    if (!$fecha) {
        return 'Fecha no disponible';
    }

    $marca = strtotime((string)$fecha);

    return $marca
        ? date('d/m/Y', $marca)
        : 'Fecha no disponible';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Noticias y novedades de AgroHumedad sobre tecnología, agricultura y sostenibilidad."
    >

    <title>Noticias | AgroHumedad</title>

    <link rel="stylesheet" href="style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>
        .pagina-noticias {
            font-family: 'Poppins', sans-serif;
            color: #26352a;
        }

        .pagina-noticias .container {
            width: min(1160px, 92%);
            margin: 0 auto;
        }

        .noticias-hero {
            margin: 30px 0;
            padding: clamp(26px, 5vw, 54px);
            border-radius: 22px;
            background: #f7f5eb;
            display: grid;
            grid-template-columns: 1.4fr .6fr;
            gap: 25px;
            align-items: center;
        }

        .noticias-hero h1 {
            margin: 5px 0 14px;
            color: #244d35;
            font-size: clamp(30px, 4vw, 46px);
            line-height: 1.15;
        }

        .noticias-hero p {
            color: #647067;
            line-height: 1.8;
            max-width: 720px;
        }

        .hero-sello {
            justify-self: center;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #e4efdf;
            color: #39734b;
            font-size: 64px;
            box-shadow: inset 0 0 0 12px rgba(57, 115, 75, .08);
        }

        .noticias-cabecera {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 20px;
            margin: 42px 0 22px;
        }

        .noticias-cabecera h2 {
            margin: 0;
            color: #244d35;
            font-size: clamp(25px, 3vw, 34px);
        }

        .noticias-cabecera p {
            margin: 8px 0 0;
            color: #647067;
            line-height: 1.7;
        }

        .admin-noticias-link {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            background: #39734b;
            color: white !important;
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .admin-noticias-link:hover {
            background: #244d35;
        }

        .noticias-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
            margin-bottom: 55px;
        }

        .noticia-card {
            min-width: 0;
            overflow: hidden;
            border: 1px solid #dfe7dc;
            border-radius: 17px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(35, 77, 53, .06);
            display: flex;
            flex-direction: column;
        }

        /* Área donde se muestra la imagen de cada noticia. */
        .noticia-imagen {
            height: 205px;
            background: linear-gradient(135deg, #edf5eb, #d8e8d5);
            display: grid;
            place-items: center;
            overflow: hidden;
            color: #39734b;
            font-size: 48px;
            position: relative;
        }

        .noticia-imagen img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        .noticia-contenido {
            padding: 21px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .noticia-categoria {
            color: #39734b;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 10px;
            font-weight: 700;
        }

        .noticia-contenido h3 {
            font-size: 19px;
            line-height: 1.4;
            color: #244d35;
            margin: 10px 0;
        }

        .noticia-fecha {
            font-size: 12px;
            color: #647067;
            margin: 0 0 12px;
        }

        .noticia-resumen {
            font-size: 13px;
            color: #59665b;
            line-height: 1.8;
            margin: 0 0 20px;
            flex: 1;
            white-space: pre-line;
        }

        .noticia-leer {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            align-self: flex-start;
            text-decoration: none;
            color: #39734b;
            font-weight: 700;
            font-size: 13px;
        }

        .noticia-leer:hover {
            color: #244d35;
        }

        .aviso-noticias {
            padding: 16px 18px;
            border-radius: 12px;
            background: #fff4d9;
            color: #80540b;
            margin: 20px 0;
            line-height: 1.7;
            font-size: 14px;
        }

        .sin-noticias {
            padding: 36px 24px;
            border: 1px dashed #b9cbb5;
            background: #fbfcfa;
            border-radius: 16px;
            text-align: center;
            color: #647067;
            margin-bottom: 55px;
        }

        .sin-noticias i {
            font-size: 35px;
            color: #39734b;
            margin-bottom: 12px;
        }

        .temas-destacados {
            margin: 45px 0;
            padding: 30px;
            background: #edf5eb;
            border-radius: 18px;
        }

        .temas-destacados h2 {
            color: #244d35;
            margin-top: 0;
        }

        .temas-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .tema-item {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
        }

        .tema-item i {
            font-size: 22px;
            color: #39734b;
        }

        .tema-item h3 {
            color: #244d35;
            margin: 12px 0 7px;
            font-size: 16px;
        }

        .tema-item p {
            margin: 0;
            color: #647067;
            font-size: 13px;
            line-height: 1.7;
        }

        .cierre-noticias {
            position: relative;
            overflow: hidden;
            margin: 45px 0 55px;
            padding: clamp(28px, 5vw, 50px);
            border-radius: 22px;
            background: #244d35;
            color: #fff;
        }

        .cierre-contenido {
            max-width: 760px;
            position: relative;
            z-index: 1;
        }

        .cierre-etiqueta {
            font-size: 11px;
            letter-spacing: 1.5px;
            color: #c8e5c6;
            font-weight: 700;
        }

        .cierre-noticias h2 {
            font-size: clamp(25px, 3.3vw, 38px);
            line-height: 1.25;
            margin: 16px 0;
        }

        .cierre-noticias h2 span {
            color: #b9d99c;
        }

        .cierre-noticias p {
            color: #e1e9e1;
            line-height: 1.8;
            max-width: 700px;
        }

        .cierre-boton {
            display: inline-flex;
            gap: 10px;
            align-items: center;
            margin-top: 12px;
            padding: 12px 17px;
            border-radius: 10px;
            background: #fff;
            color: #244d35;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
        }

        .cierre-decoracion {
            position: absolute;
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 50%;
            width: 250px;
            height: 250px;
            right: -70px;
            top: -50px;
        }

        .cierre-decoracion-2 {
            width: 360px;
            height: 360px;
            right: -110px;
            top: auto;
            bottom: -240px;
        }

        /* Pie de página institucional. */
        .footer {
            background: #203e2c;
            color: #fff;
            padding: 44px 22px 0;
            margin-top: 55px;
        }

        .footer-contenido {
            width: min(1120px, 100%);
            margin: 0 auto;
        }

        .footer-columnas {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: clamp(26px, 4vw, 55px);
            padding-bottom: 32px;
        }

        .footer-columna {
            min-width: 0;
        }

        .footer-logo {
            margin: 0 0 13px;
            color: #fff;
            font-size: 26px;
            font-weight: 700;
        }

        .footer-logo .agro {
            color: #b9d99c;
        }

        .footer-descripcion {
            max-width: 340px;
            margin: 0 0 16px;
            color: #d6e3d8;
            font-size: 12px;
            line-height: 1.8;
        }

        .footer-etiqueta {
            display: inline-block;
            padding: 7px 12px;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 30px;
            color: #e5eee5;
            font-size: 11px;
        }

        .footer-titulo {
            margin: 3px 0 17px;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
        }

        .footer-contacto-lista {
            display: grid;
            gap: 15px;
        }

        .footer-contacto {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: 11px;
            align-items: start;
        }

        .footer-contacto-icono {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: rgba(255, 255, 255, .1);
            display: grid;
            place-items: center;
            color: #c8e5c6;
        }

        .footer-contacto-texto {
            min-width: 0;
        }

        .footer-contacto-texto strong {
            display: block;
            margin-bottom: 3px;
            color: #fff;
            font-size: 12px;
        }

        .footer-contacto-texto p,
        .footer-contacto-texto a {
            margin: 0;
            color: #d6e3d8;
            font-size: 11px;
            line-height: 1.7;
            overflow-wrap: anywhere;
            text-decoration: none;
        }

        .footer-contacto-texto a:hover {
            color: #fff;
            text-decoration: underline;
        }

        .footer-instituciones {
            display: grid;
            gap: 12px;
        }

        .footer-institucion {
            height: 76px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 12px;
            background: rgba(255, 255, 255, .06);
        }

        .footer-institucion img {
            max-width: 100%;
            max-height: 50px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .footer-linea {
            height: 1px;
            background: rgba(255, 255, 255, .18);
        }

        .footer-inferior {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 17px 0 20px;
        }

        .footer-inferior p {
            margin: 0;
            color: #c9d9cc;
            font-size: 11px;
            line-height: 1.7;
        }

        @media(max-width:850px) {
            .noticias-hero {
                grid-template-columns: 1fr;
            }

            .hero-sello {
                display: none;
            }

            .noticias-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .footer-columnas {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .footer-marca {
                grid-column: 1 / -1;
            }

            .footer-descripcion {
                max-width: 600px;
            }
        }

        @media(max-width:560px) {
            .noticias-cabecera {
                align-items: flex-start;
                flex-direction: column;
            }

            .noticias-grid,
            .temas-grid,
            .footer-columnas {
                grid-template-columns: 1fr;
            }

            .footer-marca {
                grid-column: auto;
            }

            .noticia-imagen {
                height: 190px;
            }

            .footer-instituciones {
                grid-template-columns: 1fr 1fr;
            }

            .footer-institucion {
                height: 72px;
            }

            .footer-inferior {
                flex-direction: column;
                gap: 5px;
            }

            .cierre-noticias {
                margin-top: 30px;
            }
        }
    </style>
</head>

<body class="pagina-noticias">

<header class="header">
    <div class="header-contenido">

        <a href="INDEX.php" class="logo">
            <div class="logo-icono">
                <img
                    src="logo.png"
                    alt="Logo AgroHumedad"
                    style="width:100%;height:100%;object-fit:cover;object-position:center top;border-radius:13px;"
                >
            </div>

            <div class="logo-texto">
                <span class="agro">Agro</span><span>Humedad</span>
            </div>
        </a>

        <nav class="menu" aria-label="Navegación principal">
            <a href="INDEX.php">INICIO</a>
            <a href="quienes_somos.html">¿QUIÉNES SOMOS?</a>
            <a href="antecedentes.html">ANTECEDENTES</a>
            <a href="fundamentos.html">FUNDAMENTOS</a>
            <a href="servicio1.php">SERVICIOS</a>
            <a href="noticias.php" class="activo">NOTICIAS</a>
            <a href="politicas.html">POLÍTICAS</a>
            <a href="contacto.php">CONTACTO</a>
        </nav>

    </div>
</header>

<main class="container">

    <section class="noticias-hero">
        <div>
            <span class="subtitulo">ACTUALIDAD</span>

            <h1>Últimas novedades</h1>

            <p>
                Noticias, avances tecnológicos y novedades relacionadas
                con la agricultura, el monitoreo del suelo y la sostenibilidad.
                Las publicaciones que aparecen aquí son administradas desde
                el panel de AgroHumedad.
            </p>
        </div>

        <div class="hero-sello" aria-hidden="true">
            <i class="fa-solid fa-newspaper"></i>
        </div>
    </section>

    <?php if ($usuarioEsAdmin): ?>
        <p>
            <a class="admin-noticias-link" href="admin.php?tabla=noticias">
                <i class="fa-solid fa-pen-to-square"></i>
                Administrar noticias
            </a>
        </p>
    <?php endif; ?>

    <?php if ($errorNoticias !== ''): ?>
        <div class="aviso-noticias" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?=eNoticia($errorNoticias)?>
            Revisa la conexión a la base de datos y la tabla noticias.
        </div>
    <?php endif; ?>

    <section>

        <div class="noticias-cabecera">
            <div>
                <span class="subtitulo">ACTUALIZACIONES</span>

                <h2>Noticias publicadas</h2>

                <p>
                    Consulta las publicaciones disponibles en AgroHumedad.
                </p>
            </div>
        </div>

        <?php if (!$noticias && $errorNoticias === ''): ?>

            <div class="sin-noticias">
                <i class="fa-regular fa-newspaper"></i>

                <h3>Aún no hay noticias publicadas</h3>

                <p>
                    Cuando el administrador publique una noticia,
                    aparecerá en esta sección.
                </p>

                <?php if ($usuarioEsAdmin): ?>
                    <a
                        class="admin-noticias-link"
                        href="admin.php?tabla=noticias"
                    >
                        Crear primera noticia
                    </a>
                <?php endif; ?>
            </div>

        <?php elseif ($noticias): ?>

            <div class="noticias-grid">

                <?php foreach ($noticias as $noticia): ?>

                    <article class="noticia-card">

                        <div class="noticia-imagen">

                            <?php if (!empty($noticia['ruta_imagen_mostrar'])): ?>

                                <img
                                    src="<?=eNoticia($noticia['ruta_imagen_mostrar'])?>"
                                    alt="Imagen de <?=eNoticia($noticia['titulo'])?>"
                                    loading="lazy"
                                    onerror="this.style.display='none'; this.nextElementSibling.hidden=false;"
                                >

                                <i
                                    class="fa-solid fa-seedling"
                                    aria-hidden="true"
                                    hidden
                                ></i>

                            <?php else: ?>

                                <i
                                    class="fa-solid fa-seedling"
                                    aria-hidden="true"
                                ></i>

                            <?php endif; ?>

                        </div>

                        <div class="noticia-contenido">

                            <span class="noticia-categoria">
                                <?=eNoticia($noticia['categoria'])?>
                            </span>

                            <h3>
                                <?=eNoticia($noticia['titulo'])?>
                            </h3>

                            <p class="noticia-fecha">
                                <i class="fa-regular fa-calendar"></i>
                                <?=eNoticia(
                                    fechaNoticia(
                                        $noticia['fecha_publicacion']
                                        ?? $noticia['fecha_creacion']
                                        ?? null
                                    )
                                )?>
                            </p>

                            <p class="noticia-resumen">
                                <?=eNoticia($noticia['resumen'])?>
                            </p>

                            <a
                                class="noticia-leer"
                                href="noticia.php?id=<?=(int)$noticia['id_noticia']?>"
                            >
                                Leer noticia
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

    <section class="temas-destacados">

        <span class="subtitulo">TEMAS DE INTERÉS</span>

        <h2>Información para una agricultura más consciente</h2>

        <div class="temas-grid">

            <article class="tema-item">
                <i class="fa-solid fa-droplet"></i>

                <h3>Uso eficiente del agua</h3>

                <p>
                    Ideas y herramientas que ayudan a reflexionar sobre
                    el consumo responsable del agua en los cultivos.
                </p>
            </article>

            <article class="tema-item">
                <i class="fa-solid fa-microchip"></i>

                <h3>Tecnología agrícola</h3>

                <p>
                    Conoce aplicaciones, sensores y recursos digitales
                    relacionados con el seguimiento de las condiciones del suelo.
                </p>
            </article>

            <article class="tema-item">
                <i class="fa-solid fa-leaf"></i>

                <h3>Sostenibilidad</h3>

                <p>
                    Prácticas orientadas al cuidado de los recursos naturales
                    y al desarrollo responsable del sector agrícola.
                </p>
            </article>

        </div>
    </section>

    <section class="cierre-noticias">

        <div class="cierre-decoracion"></div>
        <div class="cierre-decoracion cierre-decoracion-2"></div>

        <div class="cierre-contenido">

            <span class="cierre-etiqueta">
                AGROHUMEDAD · INNOVACIÓN Y SOSTENIBILIDAD
            </span>

            <h2>
                El futuro del campo comienza con
                <span>mejores decisiones</span>
            </h2>

            <p>
                Mantenernos informados sobre los avances tecnológicos
                nos ayuda a encontrar nuevas formas de cuidar el agua,
                mejorar los cultivos y construir una agricultura más sostenible.
            </p>

            <a href="INDEX.php" class="cierre-boton">
                Explorar AgroHumedad
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </div>

    </section>

    <div class="boton">
        <a href="INDEX.php" class="button">
            <i class="fa-solid fa-house"></i>
            Volver al inicio
        </a>
    </div>

</main>

<footer class="footer">

    <div class="footer-contenido">

        <div class="footer-columnas">

            <section
                class="footer-columna footer-marca"
                aria-label="Información de AgroHumedad"
            >
                <h2 class="footer-logo">
                    <span class="agro">Agro</span>Humedad
                </h2>

                <p class="footer-descripcion">
                    Tecnología para monitorear la humedad del suelo
                    y promover una agricultura más eficiente, sostenible
                    y responsable con el agua.
                </p>

                <span class="footer-etiqueta">
                    Proyecto académico
                </span>
            </section>

            <section
                class="footer-columna"
                aria-labelledby="footer-contacto-titulo"
            >
                <h3 class="footer-titulo" id="footer-contacto-titulo">
                    Información de contacto
                </h3>

                <div class="footer-contacto-lista">

                    <div class="footer-contacto">
                        <span class="footer-contacto-icono" aria-hidden="true">
                            <i class="fa-solid fa-envelope"></i>
                        </span>

                        <div class="footer-contacto-texto">
                            <strong>Correo electrónico</strong>

                            <a href="mailto:tecnologiaagricola@gmail.com">
                                tecnologiaagricola@gmail.com
                            </a>
                        </div>
                    </div>

                    <div class="footer-contacto">
                        <span class="footer-contacto-icono" aria-hidden="true">
                            <i class="fa-solid fa-phone"></i>
                        </span>

                        <div class="footer-contacto-texto">
                            <strong>Teléfono</strong>

                            <p>Contacto del proyecto académico.</p>
                        </div>
                    </div>

                    <div class="footer-contacto">
                        <span class="footer-contacto-icono" aria-hidden="true">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>

                        <div class="footer-contacto-texto">
                            <strong>Ubicación</strong>

                            <p>Medellín, Antioquia, Colombia</p>
                        </div>
                    </div>

                </div>
            </section>

            <section
                class="footer-columna"
                aria-labelledby="footer-instituciones-titulo"
            >
                <h3 class="footer-titulo" id="footer-instituciones-titulo">
                    Instituciones vinculadas
                </h3>

                <div class="footer-instituciones">

                    <div class="footer-institucion">
                        <img
                            src="img/pascual_bravo.png"
                            alt="Institución Universitaria Pascual Bravo"
                            loading="lazy"
                        >
                    </div>

                    <div class="footer-institucion">
                        <img
                            src="img/IE-Republica-de-Uruguay.png"
                            alt="Institución Educativa República de Uruguay"
                            loading="lazy"
                        >
                    </div>

                </div>
            </section>

        </div>

        <div class="footer-linea"></div>

        <div class="footer-inferior">
            <p>
                &copy; <?=date('Y')?> AgroHumedad. Todos los derechos reservados.
            </p>

            <p>
                Desarrollado como proyecto académico.
            </p>
        </div>

    </div>

</footer>

</body>
</html>