<?php
session_start();
mysqli_report(MYSQLI_REPORT_OFF);


$mensaje_estado = '';
$tipo_estado = '';
$nombre = '';
$correo = '';
$mensaje = '';
$perfilUsuario = null;
$conexion = null;
$errorConexion = '';
$sesionIniciada = !empty($_SESSION['usuario_id']);
$usuarioId = $sesionIniciada ? (int)$_SESSION['usuario_id'] : 0;

function e($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function longitudTexto(string $texto): int
{
    return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
}

try {
    require_once __DIR__ . '/conexion.php';

    if (!isset($conexion) || !($conexion instanceof mysqli) || $conexion->connect_errno) {
        $errorConexion = 'No se pudo establecer la conexión con la base de datos. Verifica conexion.php y que MySQL esté iniciado en XAMPP.';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mensaje_estado = $errorConexion;
            $tipo_estado = 'error';
            $nombre = trim((string)($_POST['nombre'] ?? ''));
            $correo = strtolower(trim((string)($_POST['correo'] ?? '')));
            $mensaje = trim((string)($_POST['mensaje'] ?? ''));
        }
    } else {
        $conexion->set_charset('utf8mb4');

        // Si la persona inició sesión, traer su nombre y correo desde la tabla usuario.
        if ($sesionIniciada) {
            $sqlPerfil = 'SELECT nombre, apellido, correo FROM `usuario` WHERE id_u = ? LIMIT 1';
            $stmtPerfil = $conexion->prepare($sqlPerfil);
            if ($stmtPerfil) {
                $stmtPerfil->bind_param('i', $usuarioId);
                if ($stmtPerfil->execute()) {
                    $resultadoPerfil = $stmtPerfil->get_result();
                    if ($resultadoPerfil) {
                        $perfilUsuario = $resultadoPerfil->fetch_assoc() ?: null;
                    }
                }
                $stmtPerfil->close();
            }

            if ($perfilUsuario) {
                $nombre = trim((string)$perfilUsuario['nombre'] . ' ' . (string)$perfilUsuario['apellido']);
                $correo = (string)$perfilUsuario['correo'];
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mensaje = trim((string)($_POST['mensaje'] ?? ''));

            // Para una persona autenticada, la identidad se toma de la base de datos,
            // no de los valores que pudieran enviarse manualmente desde el formulario.
            if ($sesionIniciada) {
                if (!$perfilUsuario) {
                    $mensaje_estado = 'No fue posible obtener los datos de tu cuenta. Cierra sesión, vuelve a ingresar e inténtalo de nuevo.';
                    $tipo_estado = 'error';
                }
            } else {
                $nombre = trim((string)($_POST['nombre'] ?? ''));
                $correo = strtolower(trim((string)($_POST['correo'] ?? '')));
            }

            if ($mensaje_estado === '') {
                if ($nombre === '' || $correo === '' || $mensaje === '') {
                    $mensaje_estado = 'Completa los campos obligatorios antes de enviar el mensaje.';
                    $tipo_estado = 'error';
                } elseif (longitudTexto($nombre) > 80) {
                    $mensaje_estado = 'El nombre completo debe tener máximo 80 caracteres.';
                    $tipo_estado = 'error';
                } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL) || longitudTexto($correo) > 100) {
                    $mensaje_estado = 'Ingresa un correo electrónico válido de máximo 100 caracteres.';
                    $tipo_estado = 'error';
                } elseif (longitudTexto($mensaje) > 500) {
                    $mensaje_estado = 'El mensaje debe tener máximo 500 caracteres.';
                    $tipo_estado = 'error';
                } elseif (!$conexion instanceof mysqli || $conexion->connect_errno) {
                    $mensaje_estado = $errorConexion !== '' ? $errorConexion : 'No hay conexión disponible para enviar el mensaje.';
                    $tipo_estado = 'error';
                } else {
                    // La tabla contacto tiene fecha con DEFAULT CURRENT_TIMESTAMP.
                    $sql = 'INSERT INTO `contacto` (`nombre`, `correo`, `mensaje`) VALUES (?, ?, ?)';
                    $stmt = $conexion->prepare($sql);

                    if (!$stmt) {
                        error_log('Error preparando contacto: ' . $conexion->error);
                        $mensaje_estado = 'No se pudo preparar el envío. Verifica que la tabla `contacto` tenga las columnas nombre, correo y mensaje.';
                        $tipo_estado = 'error';
                    } else {
                        $stmt->bind_param('sss', $nombre, $correo, $mensaje);

                        if ($stmt->execute()) {
                            $mensaje_estado = 'Mensaje enviado correctamente. Gracias por comunicarte con AgroHumedad.';
                            $tipo_estado = 'exito';
                            $mensaje = '';
                            if (!$sesionIniciada) {
                                $nombre = '';
                                $correo = '';
                            }
                        } else {
                            error_log('Error insertando contacto (' . $stmt->errno . '): ' . $stmt->error);
                            $mensaje_estado = 'No se pudo enviar el mensaje. Verifica la tabla `contacto` en phpMyAdmin e inténtalo nuevamente.';
                            $tipo_estado = 'error';
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }
} catch (Throwable $ex) {
    error_log('Error en contacto.php: ' . $ex->getMessage());
    $errorConexion = 'No se pudo conectar con la base de datos. Verifica conexion.php y que MySQL esté activo.';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $mensaje_estado = $errorConexion;
        $tipo_estado = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contacta con AgroHumedad para compartir preguntas, sugerencias e inquietudes sobre el proyecto.">
    <title>Contacto | AgroHumedad</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .pagina-contacto { font-family: 'Poppins', sans-serif; }
        .pagina-contacto .contacto-contenedor { display:flex; justify-content:center; align-items:center; width:100%; margin:40px auto 55px; }
        .pagina-contacto .formulario-contacto { width:100%; max-width:850px; margin:0 auto; }
        .pagina-contacto .formulario-encabezado { text-align:center; }
        .pagina-contacto .contacto-contenido { display:flex; justify-content:center; width:100%; }
        .pagina-contacto .contacto-contenido form { width:100%; max-width:650px; margin:0 auto; }
        .pagina-contacto .campo { width:100%; margin-bottom:20px; }
        .pagina-contacto .campo input, .pagina-contacto .campo textarea { width:100%; box-sizing:border-box; font-family:inherit; }
        .pagina-contacto .campo textarea { min-height:150px; resize:vertical; }
        .pagina-contacto .campo input[readonly] { background:#f0f4ef; color:#405347; cursor:default; }
        .ayuda-campo { display:block; margin-top:6px; color:#647067; font-size:12px; }
        .pagina-contacto .boton-contacto { display:block; margin:25px auto 0; cursor:pointer; }
        .mensaje-contacto { text-align:center; padding:13px 16px; margin:20px auto; max-width:650px; border-radius:9px; line-height:1.6; font-size:14px; }
        .mensaje-contacto.exito { background:#e4f5e2; color:#2f6b35; }
        .mensaje-contacto.error { background:#f8e2e2; color:#a33a3a; }
        .contacto-sesion { max-width:650px; margin:0 auto 18px; padding:12px 15px; background:#edf5eb; color:#244d35; border-radius:10px; font-size:13px; }
        .footer { margin-top:0; padding:44px 22px 0; background:#203e2c; color:#fff; text-align:left; }
        .footer .footer-contenido { width:min(1120px,100%); margin:0 auto; }
        .footer .footer-columnas { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(0,1fr) minmax(0,1fr); gap:clamp(24px,4vw,56px); padding-bottom:32px; }
        .footer .footer-columna { min-width:0; }
        .footer .footer-logo { margin:0 0 14px; color:#fff; font-size:25px; font-weight:700; }
        .footer .footer-logo .agro { color:#b9d99c; }
        .footer .footer-descripcion { max-width:350px; margin:0 0 18px; color:#dce7dd; font-size:13px; line-height:1.8; }
        .footer .footer-etiqueta { display:inline-block; padding:7px 12px; border:1px solid rgba(255,255,255,.25); border-radius:30px; color:#edf4eb; font-size:11px; }
        .footer .footer-titulo { margin:3px 0 18px; color:#fff; font-size:15px; font-weight:600; }
        .footer .footer-contacto-lista { display:grid; gap:17px; }
        .footer .footer-contacto { display:grid; grid-template-columns:36px minmax(0,1fr); gap:11px; align-items:start; margin:0; }
        .footer .footer-contacto-icono { width:36px; height:36px; display:flex; justify-content:center; align-items:center; border-radius:10px; background:rgba(255,255,255,.11); color:#c8e5c6; }
        .footer .footer-contacto-texto { min-width:0; }
        .footer .footer-contacto-texto strong { display:block; margin:1px 0 4px; color:#fff; font-size:12px; }
        .footer .footer-contacto-texto p, .footer .footer-contacto-texto a { display:block; margin:0; color:#dce7dd; font-size:12px; line-height:1.7; text-decoration:none; overflow-wrap:anywhere; }
        .footer .footer-contacto-texto a:hover { color:#fff; text-decoration:underline; }
        .footer .footer-instituciones { display:grid; gap:12px; }
        .footer .footer-institucion { min-height:78px; display:flex; align-items:center; justify-content:center; padding:10px 14px; border:1px solid rgba(255,255,255,.15); border-radius:12px; background:rgba(255,255,255,.06); }
        .footer .footer-institucion img { display:block; width:auto; height:auto; max-width:155px; max-height:52px; object-fit:contain; }
        .footer .footer-linea { height:1px; background:rgba(255,255,255,.2); }
        .footer .footer-inferior { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; padding:17px 0 20px; }
        .footer .footer-inferior p { margin:0; color:#cbd9cd; font-size:11px; line-height:1.7; }
        @media(max-width:800px) { .footer .footer-columnas { grid-template-columns:repeat(2,minmax(0,1fr)); gap:28px; } .footer .footer-marca { grid-column:1/-1; } .footer .footer-descripcion { max-width:600px; } }
        @media(max-width:540px) { .footer { padding:34px 20px 0; } .footer .footer-columnas { grid-template-columns:minmax(0,1fr); gap:27px; } .footer .footer-marca { grid-column:auto; } .footer .footer-instituciones { grid-template-columns:1fr 1fr; } .footer .footer-institucion { min-height:70px; padding:8px; } .footer .footer-institucion img { max-width:100%; max-height:44px; } .footer .footer-inferior { align-items:flex-start; flex-direction:column; gap:5px; } }
    </style>
</head>
<body class="pagina-contacto">
<header class="header">
    <div class="header-contenido">
        <a href="INDEX.php" class="logo">
            <div class="logo-icono"><img src="logo.png" alt="Logo AgroHumedad" style="width:100%;height:100%;object-fit:contain;border-radius:13px;"></div>
            <div class="logo-texto"><span class="agro">Agro</span><span>Humedad</span></div>
        </a>
        <nav class="menu" aria-label="Navegación principal">
            <a href="INDEX.php">INICIO</a>
            <a href="quienes_somos.html">¿QUIÉNES SOMOS?</a>
            <a href="antecedentes.html">ANTECEDENTES</a>
            <a href="fundamentos.html">FUNDAMENTOS</a>
            <a href="servicio1.php">SERVICIOS</a>
            <a href="noticias.php">NOTICIAS</a>
            <a href="politicas.html">POLÍTICAS</a>
            <a href="contacto.php" class="activo">CONTACTO</a>
            
        </nav>
    </div>
</header>

<main class="container">
    <section class="contacto-intro">
        <div class="contacto-intro-texto">
            <span class="subtitulo">HABLEMOS</span>
            <h1>Ponte en contacto con nosotros</h1>
            <p>Si tienes preguntas, sugerencias o quieres conocer más sobre nuestro proyecto, puedes escribirnos mediante el formulario de contacto.</p>
        </div>
        <div class="contacto-intro-dato">
            <span>RESPONDEMOS TUS INQUIETUDES</span>
            <strong>AgroHumedad</strong>
        </div>
    </section>

    <section class="contacto-contenedor">
        <div class="formulario-contacto">
            <div class="formulario-encabezado">
                <span class="subtitulo">ESCRÍBENOS</span>
                <h2>Envíanos un mensaje</h2>
                <p>Completa el formulario para compartir tus preguntas, ideas o sugerencias.</p>
            </div>

            <?php if ($mensaje_estado !== ''): ?>
                <div class="mensaje-contacto <?= e($tipo_estado) ?>" role="status" aria-live="polite"><?= e($mensaje_estado) ?></div>
            <?php endif; ?>

            <?php if ($sesionIniciada && $perfilUsuario): ?>
                <div class="contacto-sesion"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Iniciaste sesión. Usaremos los datos asociados a tu cuenta.</div>
            <?php endif; ?>

            <div class="contacto-contenido">
                <form action="contacto.php" method="POST">
                    <div class="campo">
                        <label for="nombre">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre" maxlength="80" placeholder="Tu nombre" value="<?= e($nombre) ?>" <?= ($sesionIniciada && $perfilUsuario) ? 'readonly' : '' ?> required>
                        <?php if ($sesionIniciada && $perfilUsuario): ?><small class="ayuda-campo">Este dato proviene de tu cuenta.</small><?php endif; ?>
                    </div>

                    <div class="campo">
                        <label for="correo">Correo electrónico</label>
                        <input type="email" id="correo" name="correo" maxlength="100" placeholder="Tu correo electrónico" value="<?= e($correo) ?>" <?= ($sesionIniciada && $perfilUsuario) ? 'readonly' : '' ?> required>
                        <?php if ($sesionIniciada && $perfilUsuario): ?><small class="ayuda-campo">La respuesta podrá enviarse a este correo.</small><?php endif; ?>
                    </div>

                    <div class="campo">
                        <label for="mensaje">Mensaje</label>
                        <textarea id="mensaje" name="mensaje" maxlength="500" placeholder="Escribe tu mensaje (máximo 500 caracteres)" required><?= e($mensaje) ?></textarea>
                    </div>

                    <button type="submit" class="boton-contacto"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Enviar mensaje</button>
                </form>
            </div>
        </div>
    </section>

    <section class="preguntas">
        <div class="titulo-seccion">
            <span class="subtitulo">AGROHUMEDAD</span>
            <h2>Conoce más sobre nuestro proyecto</h2>
            <p>Estas son algunas preguntas frecuentes sobre AgroHumedad.</p>
        </div>
        <div class="preguntas-grid">
            <article class="pregunta"><span class="pregunta-numero">01</span><h3>¿Qué es AgroHumedad?</h3><p>Es un proyecto enfocado en el monitoreo de la humedad del suelo para apoyar una agricultura más eficiente.</p></article>
            <article class="pregunta"><span class="pregunta-numero">02</span><h3>¿Para qué sirve monitorear la humedad?</h3><p>Permite conocer las condiciones del suelo y tomar mejores decisiones relacionadas con el riego.</p></article>
            <article class="pregunta"><span class="pregunta-numero">03</span><h3>¿Qué tecnología utilizamos?</h3><p>El proyecto contempla sensores y herramientas digitales para trabajar con información sobre el suelo.</p></article>
            <article class="pregunta"><span class="pregunta-numero">04</span><h3>¿Cuál es nuestro objetivo?</h3><p>Promover el uso responsable del agua y apoyar una agricultura más sostenible.</p></article>
            <article class="pregunta"><span class="pregunta-numero">05</span><h3>¿Cómo puedo comunicarme?</h3><p>Puedes utilizar el formulario de contacto disponible en esta página.</p></article>
            <article class="pregunta"><span class="pregunta-numero">06</span><h3>¿Puedo conocer más sobre AgroHumedad?</h3><p>Sí. Puedes recorrer las diferentes secciones de la página para conocer el proyecto.</p></article>
        </div>
    </section>

    <section class="frase-final">
        <div>
            <span class="subtitulo">NUESTRO PROPÓSITO</span>
            <h2>Cultivamos ideas para un futuro sostenible</h2>
            <p>En AgroHumedad creemos que la tecnología puede convertirse en una herramienta importante para cuidar el campo, el agua y nuestros recursos naturales.</p>
        </div>
    </section>

    <div class="boton" style="text-align:center;margin:30px 0 50px;">
        <a href="INDEX.php" class="button"><i class="fa-solid fa-house" aria-hidden="true"></i> Volver al inicio</a>
    </div>
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
        <div class="footer-inferior">
            <p>&copy; <?= date('Y') ?> AgroHumedad. Todos los derechos reservados.</p>
            <p class="footer-creditos">Desarrollado como proyecto académico.</p>
        </div>
    </div>
</footer>
</body>
</html>
