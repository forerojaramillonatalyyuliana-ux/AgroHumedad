
<?php
session_start();

// Eliminar todas las variables de sesión.
$_SESSION = [];

// Eliminar la cookie de sesión del navegador.
if (ini_get("session.use_cookies")) {
    $parametros = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $parametros["path"],
        $parametros["domain"],
        $parametros["secure"],
        $parametros["httponly"]
    );
}

// Destruir la sesión.
session_destroy();

// Evitar que se muestre una página privada desde la caché.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Volver al inicio de sesión.
header("Location: login.php");
exit;
