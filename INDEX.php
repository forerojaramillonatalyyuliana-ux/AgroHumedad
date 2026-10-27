<?php
/* AgroHumedad - Página principal */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$sesionIniciada = !empty($_SESSION['usuario_id']);
$nombreUsuario = trim((string)($_SESSION['usuario_nombre'] ?? ''));
$apellidoUsuario = trim((string)($_SESSION['usuario_apellido'] ?? ''));
$rolUsuario = trim((string)($_SESSION['usuario_rol'] ?? 'Usuario'));

// Si el login solo guardó el ID en la sesión, consultar el nombre en la base de datos.
if ($sesionIniciada && $nombreUsuario === '') {
    try {
        require_once __DIR__ . '/conexion.php';
        if (isset($conexion) && $conexion instanceof mysqli && !$conexion->connect_errno) {
            $conexion->set_charset('utf8mb4');
            $idUsuario = (int)$_SESSION['usuario_id'];
            $consultaUsuario = $conexion->prepare(
                'SELECT nombre, apellido, rol FROM usuario WHERE id_u = ? LIMIT 1'
            );
            if ($consultaUsuario) {
                $consultaUsuario->bind_param('i', $idUsuario);
                $consultaUsuario->execute();
                $consultaUsuario->bind_result($nombreBD, $apellidoBD, $rolBD);
                if ($consultaUsuario->fetch()) {
                    $nombreUsuario = trim((string)$nombreBD);
                    $apellidoUsuario = trim((string)$apellidoBD);
                    $rolUsuario = trim((string)$rolBD);
                    $_SESSION['usuario_nombre'] = $nombreUsuario;
                    $_SESSION['usuario_apellido'] = $apellidoUsuario;
                    $_SESSION['usuario_rol'] = $rolUsuario;
                }
                $consultaUsuario->close();
            }
        }
    } catch (Throwable $e) {
        error_log('AgroHumedad INDEX: no fue posible consultar el nombre del usuario: ' . $e->getMessage());
    }
}

if ($nombreUsuario === '') {
    $nombreUsuario = trim((string)($_SESSION['nombre'] ?? 'Usuario'));
}
$nombreCompleto = trim($nombreUsuario . ' ' . $apellidoUsuario);
if ($nombreCompleto === '') {
    $nombreCompleto = 'Usuario';
}

function eIndex($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AgroHumedad: tecnología para monitorear la humedad del suelo y promover una agricultura eficiente y sostenible.">
    <title>AgroHumedad | Inicio</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
</head>
<body>

<header class="header">
    <div class="header-contenido">
        <a href="INDEX.php" class="logo">
            <div class="logo-icono">
                <img src="logo.png" alt="Logo de AgroHumedad">
            </div>
            <div class="logo-texto"><span class="agro">Agro</span><span>Humedad</span></div>
        </a>

        <nav class="menu" aria-label="Navegación principal">
            <a href="INDEX.php" class="activo">INICIO</a>
            <a href="quienes_somos.html">¿QUIÉNES SOMOS?</a>
            <a href="antecedentes.html">ANTECEDENTES</a>
            <a href="fundamentos.html">FUNDAMENTOS</a>
            <a href="servicio1.php">SERVICIOS</a>
            <a href="noticias.php">NOTICIAS</a>
            <a href="politicas.html">POLÍTICAS</a>
            <a href="contacto.php">CONTACTO</a>

            <?php if ($sesionIniciada): ?>
                <span class="usuario-sesion" title="Sesión iniciada">
                    <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
                    Hola, <?= eIndex($nombreCompleto) ?>
                </span>
                <a href="simulador.php" class="boton-simulador">
                    <i class="fa-solid fa-droplet" aria-hidden="true"></i>
                    <span>Control de humedad</span>
                </a>
                <?php if (strcasecmp($rolUsuario, 'Administrador') === 0): ?>
                    <a href="admin.php"><i class="fa-solid fa-gear" aria-hidden="true"></i> Administración</a>
                <?php endif; ?>
                <a href="cerrar_sesion.php" class="cerrar-sesion" onclick="return confirm('¿Deseas cerrar sesión?');">
                    <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                    Cerrar sesión
                </a>
            <?php else: ?>
                <a href="login.php"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> INICIAR SESIÓN</a>
                <a href="registro.php"><i class="fa-solid fa-user-plus" aria-hidden="true"></i> REGISTRO</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>
    
<!-- HERO -->
<section class="hero">
    <div class="hero-contenido">
        <div class="hero-texto">
            <span class="etiqueta">
                MONITOREO INTELIGENTE DEL SUELO
            </span>

            <h1>Agro<span>Humedad</span></h1>

            <p>
                Monitoreo inteligente de la humedad del suelo
                para una agricultura más eficiente,
                sostenible y responsable.
            </p>
        </div>
    </div>
</section>


    
<section class="bienvenida">
    <div class="bienvenida-contenido">

        <div class="bienvenida-texto tarjeta-bienvenida">
            <span class="tarjeta-etiqueta">AGROHUMEDAD</span>

            <h2>Tecnología al servicio de la agricultura</h2>

            <div class="numero-seccion">01</div>

            <h3>Una forma más inteligente de cuidar nuestros cultivos</h3>

            <p>
                AgroHumedad es un proyecto creado para mejorar el
                monitoreo de la humedad del suelo mediante herramientas
                tecnológicas.
            </p>

            <p>
                Nuestro propósito es ayudar a optimizar el uso del agua,
                facilitar la toma de decisiones y apoyar a los agricultores
                en el cuidado de sus cultivos.
            </p>

            <div class="lista-beneficios">
                <div class="beneficio">
                    <i class="fa-solid fa-droplet"></i>
                    <span>Monitoreo de la humedad del suelo</span>
                </div>

                <div class="beneficio">
                    <i class="fa-solid fa-leaf"></i>
                    <span>Uso responsable del agua</span>
                </div>

                <div class="beneficio">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Información para mejores decisiones</span>
                </div>
            </div>
        </div>

        <div class="bienvenida-imagen">
            <img
                src="https://images.unsplash.com/photo-1492496913980-501348b61469?auto=format&fit=crop&w=900&q=85"
                alt="Agricultura y cultivos"
            >
            <div class="imagen-etiqueta">
                <strong>AGRICULTURA</strong>
                <span>Inteligente y sostenible</span>
            </div>
        </div>

    </div>
</section>


    <section class="propuesta">
        <div class="seccion-titulo">
            <span>NUESTRA PROPUESTA</span>
            <h2>¿Qué ofrecemos?</h2>
            <div class="titulo-linea"></div>
            <p>Soluciones pensadas para facilitar el cuidado de los cultivos y aprovechar mejor los recursos naturales.</p>
        </div>
        <div class="propuesta-grid">
            <article class="info-card card-tecnologia">
                <div class="card-numero">01</div><h3>Tecnología inteligente</h3>
                <p>Utilizamos sensores para conocer el estado del suelo y obtener información útil para la toma de decisiones.</p>
                <a href="fundamentos.html">Conocer más <i class="fa-solid fa-arrow-right"></i></a>
            </article>
            <article class="info-card card-sostenibilidad">
                <div class="card-numero">02</div><h3>Agricultura sostenible</h3>
                <p>Promovemos un uso responsable del agua y de los recursos naturales para apoyar una agricultura más sostenible.</p>
                <a href="antecedentes.html">Conocer más <i class="fa-solid fa-arrow-right"></i></a>
            </article>
            <article class="info-card card-monitoreo">
                <div class="card-numero">03</div><h3>Monitoreo constante</h3>
                <p>La información sobre la humedad permite conocer mejor las condiciones del suelo de forma rápida y sencilla.</p>
                <a href="servicio1.php">Conocer más <i class="fa-solid fa-arrow-right"></i></a>
            </article>
        </div>
    </section>

    <section class="franja-informativa">
        <div class="franja-texto">
            <span class="franja-etiqueta">UNA DECISIÓN PUEDE HACER LA DIFERENCIA</span>
            <h2>Conocer la humedad del suelo permite cuidar mejor el agua.</h2>
            <p>Con información adecuada podemos evitar el desperdicio, cuidar mejor los cultivos y aprovechar responsablemente cada gota de agua.</p>
        </div>
        <a href="servicio1.php" class="franja-flecha" aria-label="Conocer nuestros servicios"><i class="fa-solid fa-arrow-right"></i></a>
    </section>

    <section class="proposito">
        <div class="proposito-contenido">
            <div class="proposito-visual">
                <div class="proposito-panel">
                    <img src="logo.png" alt="Logo AgroHumedad">
                    <h3>AgroHumedad</h3>
                    <p>Tecnología para cuidar mejor el agua y los cultivos.</p>
                    <div class="proposito-estadisticas">
                        <div class="proposito-estadistica"><strong>01</strong><span>Monitoreo</span></div>
                        <div class="proposito-estadistica"><strong>02</strong><span>Información</span></div>
                        <div class="proposito-estadistica"><strong>03</strong><span>Sostenibilidad</span></div>
                    </div>
                </div>
            </div>
            <div class="proposito-texto">
                <span>NUESTRO PROPÓSITO</span>
                <h2>Una agricultura más eficiente comienza con información.</h2>
                <p>Brindamos información sobre las condiciones del suelo para facilitar la toma de decisiones en los cultivos y fomentar una agricultura moderna, eficiente y sostenible.</p>
                <p>AgroHumedad busca ayudar a los agricultores a conocer mejor las necesidades de sus cultivos y utilizar el agua de una manera más responsable.</p>
                <a href="quienes_somos.html" class="btn-verde">Conoce AgroHumedad <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <section class="datos">
        <div class="dato"><i class="fa-solid fa-droplet"></i><div><strong>Uso eficiente</strong><span>del agua</span></div></div>
        <div class="dato"><i class="fa-solid fa-chart-line"></i><div><strong>Mejores</strong><span>decisiones agrícolas</span></div></div>
        <div class="dato"><i class="fa-solid fa-seedling"></i><div><strong>Producción</strong><span>más sostenible</span></div></div>
        <div class="dato"><i class="fa-solid fa-shield-halved"></i><div><strong>Cuidado</strong><span>de los cultivos</span></div></div>
    </section>

    <section class="porque">
        <div class="seccion-titulo">
            <span>¿POR QUÉ ELEGIRNOS?</span><h2>Una solución pensada para el campo</h2><div class="titulo-linea"></div>
        </div>
        <div class="porque-grid">
            <article class="porque-item"><div class="porque-icono"><i class="fa-solid fa-leaf"></i></div><div><span class="porque-numero">01</span><h3>Cuidado ambiental</h3><p>Buscamos reducir el desperdicio de agua mediante un monitoreo más eficiente.</p></div></article>
            <article class="porque-item"><div class="porque-icono"><i class="fa-solid fa-chart-line"></i></div><div><span class="porque-numero">02</span><h3>Mayor productividad</h3><p>La información del suelo permite tomar decisiones más adecuadas para los cultivos.</p></div></article>
            <article class="porque-item"><div class="porque-icono"><i class="fa-solid fa-mobile-screen"></i></div><div><span class="porque-numero">03</span><h3>Fácil acceso</h3><p>Presentamos la información de manera clara y sencilla para facilitar su consulta.</p></div></article>
            <article class="porque-item"><div class="porque-icono"><i class="fa-solid fa-shield-halved"></i></div><div><span class="porque-numero">04</span><h3>Información confiable</h3><p>El monitoreo permite contar con datos útiles sobre las condiciones del suelo.</p></div></article>
        </div>
    </section>

    <section class="llamado">
        <div class="llamado-contenido">
            <div><span>AGROHUMEDAD</span><h2>Conoce una nueva forma de cuidar tus cultivos</h2><p>Tecnología, información y sostenibilidad trabajando juntos.</p></div>
            <a href="servicio1.php" class="btn-blanco">Ver nuestros servicios <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="footer-contenido">
        <div class="footer-columnas">
            <section class="footer-columna footer-marca" aria-label="Información de AgroHumedad">
                <h2 class="footer-logo"><span class="agro">Agro</span>Humedad</h2>
                <p class="footer-descripcion">Tecnología para monitorear la humedad del suelo y promover una agricultura más eficiente, sostenible y responsable con el agua.</p>
                <span class="footer-etiqueta">Proyecto académico</span>
            </section>
            <section class="footer-columna" aria-labelledby="footer-contacto-titulo">
                <h3 class="footer-titulo" id="footer-contacto-titulo">Información de contacto</h3>
                <div class="footer-contacto-lista">
                    <div class="footer-contacto"><span class="footer-contacto-icono" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span><div class="footer-contacto-texto"><strong>Correo electrónico</strong><a href="mailto:tecnologiaagricola@gmail.com">tecnologiaagricola@gmail.com</a></div></div>
                    <div class="footer-contacto"><span class="footer-contacto-icono" aria-hidden="true"><i class="fa-solid fa-phone"></i></span><div class="footer-contacto-texto"><strong>Teléfono</strong><p>Agrega aquí el teléfono oficial del proyecto.</p></div></div>
                    <div class="footer-contacto"><span class="footer-contacto-icono" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span><div class="footer-contacto-texto"><strong>Ubicación</strong><p>Medellín, Antioquia, Colombia</p></div></div>
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
        <div class="footer-inferior"><p>&copy; <?= date('Y') ?> AgroHumedad. Todos los derechos reservados.</p><p class="footer-creditos">Desarrollado como proyecto académico.</p></div>
    </div>
</footer>
</body>
</html>
