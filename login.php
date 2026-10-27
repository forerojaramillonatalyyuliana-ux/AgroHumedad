
<?php
session_start();
require_once("conexion.php");

$mensaje = "";
$correo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST["correo"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($correo === "" || $password === "") {
        $mensaje = "Por favor, completa todos los campos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Ingresa un correo electrónico válido.";
    } else {
        /*
         * La consulta utiliza los nombres de columnas de la tabla
         * usuario de la base de datos sistema_de_riesgo.
         */
        $sql = "SELECT id_u, nombre, apellido, tel, rol, correo, password
                FROM usuario
                WHERE correo = ?
                LIMIT 1";

        $stmt = $conexion->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $correo);

            if ($stmt->execute()) {
                $resultado = $stmt->get_result();
                $usuario = $resultado->fetch_assoc();

                if ($usuario && password_verify($password, $usuario["password"])) {

                    // Regenerar el identificador para proteger la sesión.
                    session_regenerate_id(true);

                    // Guardar la información del usuario en la sesión.
                    $_SESSION["usuario_id"] = $usuario["id_u"];
                    $_SESSION["usuario_nombre"] = $usuario["nombre"];
                    $_SESSION["usuario_apellido"] = $usuario["apellido"];
                    $_SESSION["usuario_correo"] = $usuario["correo"];

                    // El rol de la base de datos se utiliza para los permisos.
                    $rol = trim($usuario["rol"]);
                    $_SESSION["usuario_rol"] = $rol;

                    $stmt->close();

                    // Redirigir según el rol registrado.
                    if (strcasecmp($rol, "Administrador") === 0) {
                        header("Location: admin.php");
                        exit();
                    }

                    // Los demás usuarios ingresan a la página principal.
                    header("Location: INDEX.php");
                    exit();

                } else {
                    $mensaje = "El correo o la contraseña son incorrectos.";
                }

                $stmt->close();

            } else {
                $mensaje = "No se pudo procesar el inicio de sesión. Inténtalo nuevamente.";
                $stmt->close();
            }
        } else {
            $mensaje = "No se pudo procesar el inicio de sesión. Verifica la estructura de la base de datos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión | AgroHumedad</title>

    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">
</head>

<body class="pagina-login">

<header class="header">
    <div class="header-contenido">

        <a href="INDEX.php" class="logo">
            <div class="logo-icono">
                <img src="logo.png" alt="Logo AgroHumedad">
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
            <a href="servicio1.html">SERVICIOS</a>
            <a href="noticias.php">NOTICIAS</a>
            <a href="politicas.html">POLÍTICAS</a>
            <a href="contacto.php">CONTACTO</a>
            <a href="login.php" class="activo" aria-current="page">
                INICIAR SESIÓN
            </a>
            <a href="registro.php">REGISTRO</a>
        </nav>

    </div>
</header>

<main class="login-contenedor">
    <section class="login-card">

        <div class="login-encabezado">
            <div class="login-icono">
                <i class="fa-solid fa-user" aria-hidden="true"></i>
            </div>

            <span class="subtitulo">AGROHUMEDAD</span>

            <h1>Iniciar sesión</h1>

            <p>Ingresa a tu cuenta para continuar.</p>
        </div>

        <?php if ($mensaje !== ""): ?>
            <p class="mensaje-login" role="alert">
                <?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <div class="campo-login">
                <label for="loginCorreo">Correo electrónico</label>

                <div class="input-icono">
                    <i class="fa-solid fa-envelope" aria-hidden="true"></i>

                    <input
                        type="email"
                        id="loginCorreo"
                        name="correo"
                        placeholder="Ingresa tu correo"
                        autocomplete="username"
                        value="<?= htmlspecialchars($correo, ENT_QUOTES, "UTF-8") ?>"
                        required
                    >
                </div>
            </div>

            <div class="campo-login">
                <label for="loginPassword">Contraseña</label>

                <div class="input-icono">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i>

                    <input
                        type="password"
                        id="loginPassword"
                        name="password"
                        placeholder="Ingresa tu contraseña"
                        autocomplete="current-password"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="boton-login">
                Iniciar sesión
            </button>

        </form>

        <div class="login-registro">
            <p>¿No tienes una cuenta?</p>
            <a href="registro.php">Regístrate aquí</a>
        </div>

        <div class="login-volver">
            <a href="INDEX.php">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                Volver a inicio
            </a>
        </div>

    </section>
</main>

<style>
.header .logo-icono img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    border-radius: 10px;
}

.mensaje-login {
    margin: 0 0 18px;
    padding: 11px 14px;
    border-radius: 10px;
    background: #fff0ef;
    color: #b42318;
    text-align: center;
    font-size: 14px;
}

.campo-login input {
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

@media (max-width: 760px) {
    .login-contenedor {
        width: 100%;
        box-sizing: border-box;
        padding: 28px 14px;
    }

    .login-card {
        width: 100%;
        max-width: 460px;
        box-sizing: border-box;
        margin-inline: auto;
    }
}
</style>

</body>
</html>
