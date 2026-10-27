<?php

session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mensaje = '';
$tipo_mensaje = '';
$nombre = '';
$apellido = '';
$tel = '';
$correo = '';

function e($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

try {
    require_once __DIR__ . '/conexion.php';

    if (!isset($conexion) || !($conexion instanceof mysqli) || $conexion->connect_errno) {
        throw new RuntimeException('No se encontró una conexión MySQLi válida en conexion.php.');
    }

    $conexion->set_charset('utf8mb4');

    // Asegurar que el registro use la base de datos indicada en el volcado SQL.
    if (!$conexion->select_db('sistema_de_riesgo')) {
        throw new RuntimeException('No fue posible seleccionar la base de datos sistema_de_riesgo.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        $apellido = trim((string)($_POST['apellido'] ?? ''));
        $tel = trim((string)($_POST['tel'] ?? ''));
        $correo = strtolower(trim((string)($_POST['correo'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        $confirmarPassword = (string)($_POST['confirmar_password'] ?? '');

        if ($nombre === '' || $apellido === '' || $correo === '' || $password === '' || $confirmarPassword === '') {
            $mensaje = 'Completa todos los campos obligatorios.';
            $tipo_mensaje = 'error';
        } elseif (mb_strlen($nombre) > 20 || mb_strlen($apellido) > 80) {
            $mensaje = 'El nombre debe tener máximo 20 caracteres y el apellido máximo 80.';
            $tipo_mensaje = 'error';
        } elseif ($tel !== '' && mb_strlen($tel) > 15) {
            $mensaje = 'El teléfono debe tener máximo 15 caracteres.';
            $tipo_mensaje = 'error';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 100) {
            $mensaje = 'Ingresa un correo válido de máximo 100 caracteres.';
            $tipo_mensaje = 'error';
        } elseif (strlen($password) < 6) {
            $mensaje = 'La contraseña debe tener al menos 6 caracteres.';
            $tipo_mensaje = 'error';
        } elseif ($password !== $confirmarPassword) {
            $mensaje = 'Las contraseñas no coinciden.';
            $tipo_mensaje = 'error';
        } else {
            // Comprobar si el correo ya está registrado. store_result() evita depender de get_result().
            $consulta = $conexion->prepare('SELECT `id_u` FROM `usuario` WHERE `correo` = ? LIMIT 1');
            $consulta->bind_param('s', $correo);
            $consulta->execute();
            $consulta->store_result();
            $correoExiste = $consulta->num_rows > 0;
            $consulta->close();

            if ($correoExiste) {
                $mensaje = 'Este correo ya está registrado. Intenta iniciar sesión.';
                $tipo_mensaje = 'error';
            } else {
                $passwordSegura = password_hash($password, PASSWORD_DEFAULT);
                $rol = 'Usuario'; // El rol lo asigna el servidor; nunca se recibe desde el formulario.

                $sql = 'INSERT INTO `usuario` (`nombre`, `apellido`, `tel`, `rol`, `correo`, `password`) VALUES (?, ?, ?, ?, ?, ?)';
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param('ssssss', $nombre, $apellido, $tel, $rol, $correo, $passwordSegura);
                $stmt->execute();
                $stmt->close();

                $mensaje = 'Registro exitoso. Ahora puedes iniciar sesión.';
                $tipo_mensaje = 'exito';
                $nombre = $apellido = $tel = $correo = '';
            }
        }
    }
} catch (mysqli_sql_exception $ex) {
    error_log('Error en registro.php (' . $ex->getCode() . '): ' . $ex->getMessage());

    if ((int)$ex->getCode() === 1062) {
        $mensaje = 'Este correo ya está registrado. Intenta iniciar sesión.';
    } elseif ((int)$ex->getCode() === 1054 || (int)$ex->getCode() === 1146) {
        $mensaje = 'No se encontró la tabla o alguna columna de usuario en la base sistema_de_riesgo. Según tu SQL, la tabla debe llamarse usuario (en minúscula) y contener id_u, nombre, apellido, tel, rol, correo y password.';
    } elseif ((int)$ex->getCode() === 1049) {
        $mensaje = 'No existe la base de datos sistema_de_riesgo. Revisa el nombre de la base en phpMyAdmin y en conexion.php.';
    } else {
        $mensaje = 'No fue posible completar el registro. Verifica que MySQL esté activo y que conexion.php conecte con la base de datos correcta. Código de error: ' . (int)$ex->getCode() . '.';
    }
    $tipo_mensaje = 'error';
} catch (Throwable $ex) {
    error_log('Error en registro.php: ' . $ex->getMessage());
    $mensaje = 'No se pudo conectar con la base de datos. Verifica XAMPP y el archivo conexion.php.';
    $tipo_mensaje = 'error';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | AgroHumedad</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="pagina-registro">
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
            <a href="contacto.php">CONTACTO</a>
            <a href="login.php">INICIAR SESIÓN</a>
            <a href="registro.php" class="activo">REGISTRO</a>
        </nav>
    </div>
</header>

<main class="registro-contenedor">
    <section class="registro-card">
        <div class="registro-encabezado">
            <div class="registro-icono"><i class="fa-solid fa-user-plus" aria-hidden="true"></i></div>
            <span class="subtitulo">AGROHUMEDAD</span>
            <h1>Crear una cuenta</h1>
            <p>Regístrate para formar parte de AgroHumedad.</p>
        </div>

        <?php if ($mensaje !== ''): ?>
            <div class="mensaje-registro <?= e($tipo_mensaje) ?>" role="status" aria-live="polite">
                <?= e($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="registro.php" autocomplete="on">
            <div class="campo-registro">
                <label for="nombreRegistro">Nombre</label>
                <div class="input-icono">
                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                    <input type="text" id="nombreRegistro" name="nombre" placeholder="Ingresa tu nombre" maxlength="20" value="<?= e($nombre) ?>" autocomplete="given-name" required>
                </div>
            </div>

            <div class="campo-registro">
                <label for="apellidoRegistro">Apellido</label>
                <div class="input-icono">
                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                    <input type="text" id="apellidoRegistro" name="apellido" placeholder="Ingresa tu apellido" maxlength="80" value="<?= e($apellido) ?>" autocomplete="family-name" required>
                </div>
            </div>

            <div class="campo-registro">
                <label for="telRegistro">Teléfono</label>
                <div class="input-icono">
                    <i class="fa-solid fa-phone" aria-hidden="true"></i>
                    <input type="tel" id="telRegistro" name="tel" placeholder="Ingresa tu teléfono" maxlength="15" value="<?= e($tel) ?>" autocomplete="tel">
                </div>
            </div>

            <div class="campo-registro">
                <label for="correoRegistro">Correo electrónico</label>
                <div class="input-icono">
                    <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                    <input type="email" id="correoRegistro" name="correo" placeholder="Ingresa tu correo" maxlength="100" value="<?= e($correo) ?>" autocomplete="email" required>
                </div>
            </div>

            <div class="campo-registro">
                <label for="passwordRegistro">Contraseña</label>
                <div class="input-icono">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                    <input type="password" id="passwordRegistro" name="password" placeholder="Crea una contraseña (mínimo 6 caracteres)" minlength="6" autocomplete="new-password" required>
                </div>
            </div>

            <div class="campo-registro">
                <label for="confirmarPassword">Confirmar contraseña</label>
                <div class="input-icono">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                    <input type="password" id="confirmarPassword" name="confirmar_password" placeholder="Repite tu contraseña" minlength="6" autocomplete="new-password" required>
                </div>
            </div>

            <button type="submit" class="boton-registro">Registrarse</button>
        </form>

        <div class="registro-login">
            <p>¿Ya tienes una cuenta?</p>
            <a href="login.php">Inicia sesión aquí</a>
        </div>
        <div class="registro-volver">
            <a href="INDEX.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver a inicio</a>
        </div>
    </section>
</main>
</body>
</html>
