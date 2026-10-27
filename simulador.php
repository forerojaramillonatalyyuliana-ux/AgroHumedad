<?php

session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$nombre = (string)($_SESSION['usuario_nombre'] ?? 'Usuario');
$usuarioId = (int)$_SESSION['usuario_id'];
$mensaje = '';
$tipoMensaje = '';
$errorBD = '';
$historial = [];
$humedadActual = 45.0;
$notificacionCreada = false;

function e($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function interpretarHumedad(float $humedad): array
{
    if ($humedad < 30) {
        return [
            'estado' => 'Suelo seco',
            'recomendacion' => 'La humedad es baja. Revisa las condiciones del cultivo y evalúa si necesita riego.',
            'clase' => 'seco',
            'icono' => 'fa-sun',
            'nivel' => 'Alto',
            'riego' => 'Revisar la necesidad de riego'
        ];
    }

    if ($humedad <= 70) {
        return [
            'estado' => 'Humedad intermedia',
            'recomendacion' => 'El valor se encuentra en el rango intermedio de esta simulación. Continúa observando las condiciones del suelo y las necesidades del cultivo.',
            'clase' => 'adecuado',
            'icono' => 'fa-seedling',
            'nivel' => 'Bajo',
            'riego' => 'Continuar observando las condiciones'
        ];
    }

    return [
        'estado' => 'Suelo muy húmedo',
        'recomendacion' => 'La humedad simulada es alta. Revisa el drenaje y evita agregar agua hasta verificar las necesidades del cultivo.',
        'clase' => 'humedo',
        'icono' => 'fa-cloud-rain',
        'nivel' => 'Medio',
        'riego' => 'Revisar drenaje y evitar exceso de agua'
    ];
}

/** Devuelve las columnas existentes de una tabla; si no existe, retorna un arreglo vacío. */
function columnasTabla(mysqli $conexion, string $tabla): array
{
    $permitidas = ['lectora', 'pronostico_clima', 'recomendacion_riesgo', 'notificaciones_riesgo'];
    if (!in_array($tabla, $permitidas, true)) {
        return [];
    }

    try {
        $resultado = $conexion->query("SHOW COLUMNS FROM `{$tabla}`");
        $columnas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $columnas[$fila['Field']] = $fila;
        }
        $resultado->free();
        return $columnas;
    } catch (Throwable $ex) {
        return [];
    }
}

/** Vincula dinámicamente los parámetros de una consulta preparada. */
function vincularParametros(mysqli_stmt $stmt, string $tipos, array &$valores): void
{
    $referencias = [$tipos];
    foreach ($valores as &$valor) {
        $referencias[] = &$valor;
    }
    unset($valor);
    call_user_func_array([$stmt, 'bind_param'], $referencias);
}

/** Registra recomendación/notificación si las tablas tienen los campos necesarios. */
function crearNotificacionRiesgo(mysqli $conexion, int $usuarioId, float $humedad, array $resultado): bool
{
    $columnasRecomendacion = columnasTabla($conexion, 'recomendacion_riesgo');
    $columnasNotificacion = columnasTabla($conexion, 'notificaciones_riesgo');

    if (
        !$columnasRecomendacion ||
        !$columnasNotificacion ||
        !isset($columnasRecomendacion['descripcion'], $columnasRecomendacion['nivel']) ||
        !isset($columnasNotificacion['id_u'], $columnasNotificacion['id_rr'])
    ) {
        return false;
    }

    // Obtener el pronóstico más reciente, si existe. id_pc puede ser NULL en el esquema original.
    $idPronostico = null;
    $columnasPronostico = columnasTabla($conexion, 'pronostico_clima');
    if (isset($columnasPronostico['id_pc'])) {
        try {
            $orden = isset($columnasPronostico['fecha'])
                ? '`fecha` DESC, `id_pc` DESC'
                : '`id_pc` DESC';
            $resPronostico = $conexion->query("SELECT `id_pc` FROM `pronostico_clima` ORDER BY {$orden} LIMIT 1");
            if ($resPronostico && ($filaPronostico = $resPronostico->fetch_assoc())) {
                $idPronostico = (int)$filaPronostico['id_pc'];
            }
            if ($resPronostico) {
                $resPronostico->free();
            }
        } catch (Throwable $ex) {
            $idPronostico = null;
        }
    }

    // Insertar recomendación asociada con el resultado de la simulación.
    $descripcion = 'Humedad simulada: ' . number_format($humedad, 2, '.', '') . ' %. ' . $resultado['recomendacion'];
    $nivel = $resultado['nivel'];
    $stmtRecomendacion = $conexion->prepare(
        'INSERT INTO `recomendacion_riesgo` (`descripcion`, `nivel`) VALUES (?, ?)'
    );
    $stmtRecomendacion->bind_param('ss', $descripcion, $nivel);
    $stmtRecomendacion->execute();
    $idRecomendacion = (int)$conexion->insert_id;
    $stmtRecomendacion->close();

    if ($idRecomendacion <= 0) {
        return false;
    }

    // Algunas versiones de la tabla tienen id_n y otras id_nr; al ser autoincremental no se inserta manualmente.
    $campos = [];
    $marcadores = [];
    $tipos = '';
    $valores = [];

    $campos[] = '`id_u`';
    $marcadores[] = '?';
    $tipos .= 'i';
    $valores[] = $usuarioId;

    if (isset($columnasNotificacion['id_pc'])) {
        $permiteNulo = strtoupper((string)($columnasNotificacion['id_pc']['Null'] ?? 'NO')) === 'YES';
        if ($idPronostico !== null || $permiteNulo) {
            $campos[] = '`id_pc`';
            $marcadores[] = '?';
            $tipos .= 'i';
            $valores[] = $idPronostico;
        } else {
            return false;
        }
    }

    $campos[] = '`id_rr`';
    $marcadores[] = '?';
    $tipos .= 'i';
    $valores[] = $idRecomendacion;

    if (isset($columnasNotificacion['fecha'])) {
        $campos[] = '`fecha`';
        $marcadores[] = 'NOW()';
    }
    if (isset($columnasNotificacion['leida'])) {
        $campos[] = '`leida`';
        $marcadores[] = '0';
    }

    $sqlNotificacion = 'INSERT INTO `notificaciones_riesgo` (' . implode(', ', $campos) . ') VALUES (' . implode(', ', $marcadores) . ')';
    $stmtNotificacion = $conexion->prepare($sqlNotificacion);
    vincularParametros($stmtNotificacion, $tipos, $valores);
    $stmtNotificacion->execute();
    $stmtNotificacion->close();

    return true;
}

$conexion = null;
$columnasLectora = [];
$columnaIdLectura = '';

try {
    require_once __DIR__ . '/conexion.php';

    if (!isset($conexion) || !($conexion instanceof mysqli) || $conexion->connect_errno) {
        throw new RuntimeException('No se encontró una conexión MySQLi válida. Revisa conexion.php.');
    }

    $conexion->set_charset('utf8mb4');
    if (!$conexion->select_db('sistema_de_riesgo')) {
        throw new RuntimeException('No fue posible seleccionar la base de datos sistema_de_riesgo.');
    }

    $columnasLectora = columnasTabla($conexion, 'lectora');
    if (!$columnasLectora || !isset($columnasLectora['fecha_hora'], $columnasLectora['humedad'])) {
        throw new RuntimeException('La tabla lectora debe contener las columnas fecha_hora y humedad.');
    }

    if (isset($columnasLectora['id_lh'])) {
        $columnaIdLectura = 'id_lh';
    } elseif (isset($columnasLectora['id_lectora'])) {
        $columnaIdLectura = 'id_lectora';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_simulacion'])) {
        $valorRecibido = trim((string)($_POST['humedad'] ?? ''));

        if ($valorRecibido === '' || !is_numeric($valorRecibido)) {
            $mensaje = 'Selecciona un porcentaje de humedad válido.';
            $tipoMensaje = 'error';
        } else {
            $humedadActual = (float)$valorRecibido;
            if ($humedadActual < 0 || $humedadActual > 100) {
                $mensaje = 'La humedad debe estar entre 0 % y 100 %.';
                $tipoMensaje = 'error';
                $humedadActual = max(0, min(100, $humedadActual));
            } else {
                $stmt = $conexion->prepare('INSERT INTO `lectora` (`fecha_hora`, `humedad`) VALUES (NOW(), ?)');
                $stmt->bind_param('d', $humedadActual);
                $stmt->execute();
                $stmt->close();

                $resultadoHumedad = interpretarHumedad($humedadActual);
                $mensaje = 'La lectura se guardó correctamente en el historial.';
                $tipoMensaje = 'exito';

                // La recomendación/notificación es complementaria: si la estructura cambia,
                // el registro principal de humedad se conserva y sigue funcionando.
                try {
                    $notificacionCreada = crearNotificacionRiesgo($conexion, $usuarioId, $humedadActual, $resultadoHumedad);
                } catch (Throwable $exNotificacion) {
                    error_log('AgroHumedad: no se pudo crear la notificación: ' . $exNotificacion->getMessage());
                    $notificacionCreada = false;
                }

                if ($notificacionCreada) {
                    $mensaje .= ' También se generó una recomendación y una notificación para tu cuenta.';
                } else {
                    $mensaje .= ' La notificación no se creó porque las tablas complementarias no tienen todos los campos requeridos o deben revisarse.';
                }
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['humedad']) && is_numeric($_POST['humedad'])) {
        $humedadActual = max(0, min(100, (float)$_POST['humedad']));
    }

    $ordenHistorial = $columnaIdLectura !== ''
        ? "`fecha_hora` DESC, `{$columnaIdLectura}` DESC"
        : '`fecha_hora` DESC';
    $campoIdSeleccion = $columnaIdLectura !== '' ? "`{$columnaIdLectura}` AS `id_registro`," : 'NULL AS `id_registro`,';
    $consultaHistorial = $conexion->query(
        "SELECT {$campoIdSeleccion} `fecha_hora`, `humedad` FROM `lectora` ORDER BY {$ordenHistorial} LIMIT 10"
    );
    while ($fila = $consultaHistorial->fetch_assoc()) {
        $historial[] = $fila;
    }
    $consultaHistorial->free();
} catch (Throwable $ex) {
    error_log('Error en simulador.php: ' . $ex->getMessage());
    $errorBD = $ex->getMessage();
}

$resultadoInicial = interpretarHumedad($humedadActual);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Simulador educativo de humedad del suelo AgroHumedad con registro e historial de lecturas.">
    <title>Simulador de humedad | AgroHumedad</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="simulador.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .simulador-mensaje{padding:14px 17px;margin:18px 0;border-radius:10px;line-height:1.7;font-size:14px}
        .simulador-mensaje.exito{color:#205a30;background:#eaf6eb;border:1px solid #c8e6cb}
        .simulador-mensaje.error{color:#922b2b;background:#fff0ef;border:1px solid #f0c8c5}
        .simulador-error-bd{padding:14px 17px;margin:18px 0;border:1px solid #efd69b;border-radius:10px;background:#fff6e5;color:#6e4a0b;line-height:1.7;font-size:14px}
        .simulador-guardar .btn-guardar{display:inline-flex;align-items:center;justify-content:center;gap:9px;border:0;border-radius:10px;padding:12px 18px;background:#39734b;color:#fff;font:600 14px 'Poppins',sans-serif;cursor:pointer;transition:background .2s,transform .2s}
        .simulador-guardar .btn-guardar:hover{background:#244d35;transform:translateY(-1px)}
        .simulador-guardar .btn-guardar:disabled{opacity:.55;cursor:not-allowed;transform:none}
        .simulador-mensaje,.simulador-error-bd{width:min(1120px,92%);margin-left:auto;margin-right:auto}
        .simulador-historial{width:min(1120px,92%);margin:34px auto 55px;padding:clamp(20px,3vw,32px);border:1px solid #dfe7dc;border-radius:18px;background:#fff;box-shadow:0 10px 28px rgba(35,77,53,.06)}
        .simulador-historial h2{margin:0 0 8px;color:#244d35}.simulador-historial>p{margin:0 0 18px;color:#647067;font-size:14px;line-height:1.7}
        .simulador-tabla-wrap{width:100%;overflow-x:auto;border:1px solid #dfe7dc;border-radius:10px}
        .simulador-tabla{width:100%;min-width:480px;border-collapse:collapse}.simulador-tabla th,.simulador-tabla td{padding:12px 15px;border-bottom:1px solid #edf0eb;text-align:left;font-size:13px}.simulador-tabla th{background:#f2f6ef;color:#244d35}.simulador-tabla tr:last-child td{border-bottom:0}
        .simulador-estado{display:inline-block;padding:5px 9px;border-radius:20px;font-size:12px;font-weight:600}.simulador-estado.seco{background:#fff0ef;color:#a52e2e}.simulador-estado.adecuado{background:#fff4d9;color:#80540b}.simulador-estado.humedo{background:#eaf6eb;color:#245a30}
        .simulador-footer{margin-top:55px!important;padding:44px 22px 0!important;background:#203e2c!important;color:#fff!important;text-align:left!important}
        .simulador-footer *{box-sizing:border-box}
        .simulador-footer .footer-contenido{width:min(1120px,100%);margin:0 auto}
        .simulador-footer .footer-columnas{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(0,1fr) minmax(0,.9fr);gap:clamp(26px,4vw,58px);padding-bottom:32px}
        .simulador-footer .footer-columna{min-width:0}
        .simulador-footer .footer-logo{margin:0 0 14px;color:#fff;font-size:27px;font-weight:700;line-height:1.25}
        .simulador-footer .footer-logo .agro{color:#b9d99c}
        .simulador-footer .footer-descripcion{max-width:360px;margin:0 0 17px;color:#e1e9e1;font-size:13px;line-height:1.8}
        .simulador-footer .footer-etiqueta{display:inline-block;padding:7px 12px;border:1px solid rgba(255,255,255,.25);border-radius:25px;color:#f0f5ed;font-size:11px}
        .simulador-footer .footer-titulo{margin:4px 0 18px;color:#fff;font-size:15px;font-weight:600}
        .simulador-footer .footer-contacto{display:grid;grid-template-columns:36px minmax(0,1fr);align-items:start;gap:11px;margin-bottom:15px}
        .simulador-footer .footer-icono{width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:9px;background:rgba(255,255,255,.12);color:#c8e1b6;font-size:15px}
        .simulador-footer .footer-contacto strong{display:block;margin:1px 0 3px;color:#fff;font-size:12px}
        .simulador-footer .footer-contacto p,.simulador-footer .footer-contacto a{display:block;margin:0;color:#e1e9e1;font-size:12px;line-height:1.75;text-decoration:none;overflow-wrap:anywhere}
        .simulador-footer .footer-contacto a:hover{color:#c8e1b6;text-decoration:underline}
        .simulador-footer .footer-instituciones{display:grid;gap:12px}
        .simulador-footer .footer-institucion{height:76px;display:flex;align-items:center;justify-content:center;padding:10px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);border-radius:12px}
        .simulador-footer .footer-institucion img{display:block;width:auto;height:auto;max-width:100%;max-height:52px;object-fit:contain}
        .simulador-footer .footer-linea{height:1px;background:rgba(255,255,255,.2)}
        .simulador-footer .footer-inferior{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:16px 0 20px}
        .simulador-footer .footer-inferior p{margin:0;color:#d8e4d9;font-size:11px;line-height:1.7}
        @media(max-width:780px){.simulador-footer .footer-columnas{grid-template-columns:repeat(2,minmax(0,1fr));gap:28px}.simulador-footer .footer-marca{grid-column:1/-1}.simulador-footer .footer-descripcion{max-width:600px}}
        @media(max-width:520px){.simulador-footer{padding:34px 20px 0!important}.simulador-footer .footer-columnas{grid-template-columns:minmax(0,1fr);gap:26px}.simulador-footer .footer-marca{grid-column:auto}.simulador-footer .footer-instituciones{grid-template-columns:1fr 1fr}.simulador-footer .footer-institucion{height:70px;padding:7px}.simulador-footer .footer-institucion img{max-height:45px}.simulador-footer .footer-inferior{align-items:flex-start;flex-direction:column;gap:5px}}
    </style>
</head>
<body class="pagina-simulador">
<header class="simulador-header">
    <a href="INDEX.php" class="simulador-marca">
        <img src="logo.png" alt="Logo de AgroHumedad">
        <span>AgroHumedad</span>
    </a>
    <nav class="simulador-nav" aria-label="Navegación del simulador">
        <a href="INDEX.php"><i class="fa-solid fa-house" aria-hidden="true"></i> Inicio</a>
        <a href="simulador.php" class="activo"><i class="fa-solid fa-droplet" aria-hidden="true"></i> Simulador</a>
        <a href="cerrar_sesion.php"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Cerrar sesión</a>
    </nav>
</header>

<main class="simulador-principal">
    <section class="simulador-presentacion">
        <span class="simulador-etiqueta"><i class="fa-solid fa-seedling" aria-hidden="true"></i> Agricultura inteligente</span>
        <h1>Simulador de humedad del suelo</h1>
        <p>Hola, <?= e($nombre) ?>. Ajusta el porcentaje de humedad y observa la recomendación. Cuando guardes una lectura, quedará almacenada en la base de datos para consultar el historial.</p>
    </section>

    <?php if ($mensaje !== ''): ?>
        <div class="simulador-mensaje <?= e($tipoMensaje) ?>" role="status"><?= e($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($errorBD !== ''): ?>
        <div class="simulador-error-bd" role="alert"><strong>No se pudo conectar con el almacenamiento:</strong> <?= e($errorBD) ?>. Verifica que MySQL esté activo y que conexion.php use la base sistema_de_riesgo.</div>
    <?php endif; ?>

    <section class="simulador-panel">
        <div class="simulador-controles">
            <h2>Control del suelo</h2>
            <p class="simulador-descripcion">Ajusta el porcentaje para simular el estado del suelo. Pulsa «Guardar lectura» para registrar el valor en MySQL.</p>

            <form method="post" action="simulador.php#simuladorHistorial" id="formSimulador">
                <label for="humedad">Nivel de humedad</label>
                <div class="simulador-valor"><span id="valorHumedad"><?= e(number_format($humedadActual, 0)) ?></span><span>%</span></div>
                <input type="range" id="humedad" name="humedad" min="0" max="100" value="<?= e($humedadActual) ?>" step="1" aria-label="Porcentaje de humedad del suelo">
                <div class="simulador-escala"><span>0 % · Seco</span><span>100 % · Muy húmedo</span></div>

                <div class="simulador-botones">
                    <button type="button" id="btnSeco">Simular suelo seco</button>
                    <button type="button" id="btnAdecuado">Humedad adecuada</button>
                    <button type="button" id="btnHumedo">Simular suelo húmedo</button>
                </div>

                <div class="simulador-guardar" style="margin-top:18px">
                    <button type="submit" name="guardar_simulacion" value="1" class="btn-guardar" <?= $errorBD !== '' ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Guardar lectura
                    </button>
                </div>
            </form>

            <p class="simulador-nota"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Simulación educativa: no utiliza datos de un sensor real.</p>
        </div>

        <div class="simulador-resultado" aria-live="polite">
            <div class="simulador-icono <?= e($resultadoInicial['clase']) ?>" id="iconoEstado"><i class="fa-solid <?= e($resultadoInicial['icono']) ?>" aria-hidden="true"></i></div>
            <p class="resultado-subtitulo">Estado del suelo</p>
            <h2 id="estadoSuelo"><?= e($resultadoInicial['estado']) ?></h2>
            <div class="barra-fondo" role="progressbar" aria-label="Nivel de humedad" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= e($humedadActual) ?>" id="barraProgreso">
                <div id="barraHumedad" class="barra-humedad" style="width:<?= e($humedadActual) ?>%"></div>
            </div>
            <div class="resultado-porcentaje"><span>Humedad simulada</span><strong id="porcentajeResultado"><?= e(number_format($humedadActual, 0)) ?>%</strong></div>
            <div class="resultado-recomendacion">
                <h3><i class="fa-solid fa-lightbulb" aria-hidden="true"></i> Recomendación</h3>
                <p id="recomendacion"><?= e($resultadoInicial['recomendacion']) ?></p>
            </div>
            <div class="simulador-riego" id="estadoRiego"><i class="fa-solid fa-faucet-drip" aria-hidden="true"></i><span>Riego: <span id="textoRiego"><?= e($resultadoInicial['riego']) ?></span></span></div>
        </div>
    </section>

    <section class="simulador-informacion">
        <h2>¿Cómo interpretar los resultados?</h2>
        <div class="simulador-tarjetas">
            <article class="estado-tarjeta seco"><i class="fa-solid fa-sun" aria-hidden="true"></i><h3>0 % a 29 %</h3><p>Suelo seco. Revisa si el cultivo necesita agua.</p></article>
            <article class="estado-tarjeta adecuado"><i class="fa-solid fa-seedling" aria-hidden="true"></i><h3>30 % a 70 %</h3><p>Humedad intermedia para esta simulación. Verifica las necesidades del cultivo.</p></article>
            <article class="estado-tarjeta humedo"><i class="fa-solid fa-cloud-rain" aria-hidden="true"></i><h3>71 % a 100 %</h3><p>Suelo muy húmedo. Revisa el drenaje y evita el exceso de riego.</p></article>
        </div>
        <p class="simulador-aclaracion">Los rangos son de ejemplo para el simulador. La humedad ideal cambia según el cultivo, el tipo de suelo y las condiciones ambientales.</p>
    </section>
</main>

<section class="simulador-historial" id="simuladorHistorial">
    <h2>Historial de lecturas guardadas</h2>
    <p>Últimas diez lecturas de la tabla <strong>lectora</strong>, de la más reciente a la más antigua. Los valores se comparten en la tabla porque su estructura actual no relaciona cada lectura con un usuario específico.</p>
    <?php if ($errorBD !== ''): ?>
        <p>No se puede mostrar el historial hasta corregir la conexión o la estructura de la tabla.</p>
    <?php elseif (empty($historial)): ?>
        <p>Aún no hay lecturas guardadas. Ajusta el porcentaje y pulsa «Guardar lectura».</p>
    <?php else: ?>
        <div class="simulador-tabla-wrap">
            <table class="simulador-tabla">
                <thead><tr><th>Registro</th><th>Fecha y hora</th><th>Humedad</th><th>Estado</th><th>Nivel de riesgo</th></tr></thead>
                <tbody>
                <?php foreach ($historial as $lectura): ?>
                    <?php $estadoHistorial = interpretarHumedad((float)$lectura['humedad']); ?>
                    <tr>
                        <td><?= $lectura['id_registro'] !== null ? '#' . e($lectura['id_registro']) : '—' ?></td>
                        <td><?= e(date('d/m/Y H:i', strtotime((string)$lectura['fecha_hora']))) ?></td>
                        <td><?= e(number_format((float)$lectura['humedad'], 2, ',', '.')) ?> %</td>
                        <td><span class="simulador-estado <?= e($estadoHistorial['clase']) ?>"><?= e($estadoHistorial['estado']) ?></span></td>
                        <td><?= e($estadoHistorial['nivel']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<footer class="simulador-footer footer">
    <div class="footer-contenido">
        <div class="footer-columnas">
            <section class="footer-columna footer-marca" aria-label="Información de AgroHumedad">
                <h2 class="footer-logo"><span class="agro">Agro</span>Humedad</h2>
                <p class="footer-descripcion">AgroHumedad acerca herramientas tecnológicas e información agrícola para promover el aprendizaje y el uso responsable de los recursos del campo.</p>
                <span class="footer-etiqueta">Proyecto académico</span>
            </section>
            <section class="footer-columna" aria-labelledby="footerContacto">
                <h3 class="footer-titulo" id="footerContacto">Información de contacto</h3>
                <div class="footer-contacto"><span class="footer-icono" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span><div><strong>Correo electrónico</strong><a href="mailto:tecnologiaagricola@gmail.com">tecnologiaagricola@gmail.com</a></div></div>
                <div class="footer-contacto"><span class="footer-icono" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span><div><strong>Ubicación</strong><p>Medellín, Antioquia, Colombia</p></div></div>
                <div class="footer-contacto"><span class="footer-icono" aria-hidden="true"><i class="fa-solid fa-message"></i></span><div><strong>Formulario de contacto</strong><a href="contacto.php">Escríbenos desde la página de contacto</a></div></div>
            </section>
            <section class="footer-columna" aria-labelledby="footerInstituciones">
                <h3 class="footer-titulo" id="footerInstituciones">Instituciones vinculadas</h3>
                <div class="footer-instituciones">
                    <div class="footer-institucion"><img src="img/pascual_bravo.png" alt="Institución Universitaria Pascual Bravo" loading="lazy"></div>
                    <div class="footer-institucion"><img src="img/IE-Republica-de-Uruguay.png" alt="Institución Educativa República de Uruguay" loading="lazy"></div>
                </div>
            </section>
        </div>
        <div class="footer-linea"></div>
        <div class="footer-inferior"><p>&copy; <?= date('Y') ?> AgroHumedad. Todos los derechos reservados.</p><p>Desarrollado como proyecto académico.</p></div>
    </div>
</footer>

<script>
(function () {
    'use strict';

    const control = document.getElementById('humedad');
    const valorHumedad = document.getElementById('valorHumedad');
    const estadoSuelo = document.getElementById('estadoSuelo');
    const recomendacion = document.getElementById('recomendacion');
    const iconoEstado = document.getElementById('iconoEstado');
    const barraHumedad = document.getElementById('barraHumedad');
    const barraProgreso = document.getElementById('barraProgreso');
    const porcentajeResultado = document.getElementById('porcentajeResultado');
    const textoRiego = document.getElementById('textoRiego');
    const btnSeco = document.getElementById('btnSeco');
    const btnAdecuado = document.getElementById('btnAdecuado');
    const btnHumedo = document.getElementById('btnHumedo');

    function obtenerInterpretacion(valor) {
        if (valor < 30) {
            return {
                estado: 'Suelo seco',
                recomendacion: 'La humedad es baja. Revisa las condiciones del cultivo y evalúa si necesita riego.',
                clase: 'seco', icono: 'fa-sun', color: '#b83b3b',
                riego: 'Revisar la necesidad de riego'
            };
        }
        if (valor <= 70) {
            return {
                estado: 'Humedad intermedia',
                recomendacion: 'El valor se encuentra en el rango intermedio de esta simulación. Continúa observando las condiciones del suelo y las necesidades del cultivo.',
                clase: 'adecuado', icono: 'fa-seedling', color: '#39734b',
                riego: 'Continuar observando las condiciones'
            };
        }
        return {
            estado: 'Suelo muy húmedo',
            recomendacion: 'La humedad simulada es alta. Revisa el drenaje y evita agregar agua hasta verificar las necesidades del cultivo.',
            clase: 'humedo', icono: 'fa-cloud-rain', color: '#2876a8',
            riego: 'Revisar drenaje y evitar exceso de agua'
        };
    }

    function actualizar() {
        const valor = Number(control.value);
        const resultado = obtenerInterpretacion(valor);
        valorHumedad.textContent = String(valor);
        estadoSuelo.textContent = resultado.estado;
        recomendacion.textContent = resultado.recomendacion;
        porcentajeResultado.textContent = valor + '%';
        textoRiego.textContent = resultado.riego;
        barraHumedad.style.width = valor + '%';
        barraHumedad.style.backgroundColor = resultado.color;
        barraProgreso.setAttribute('aria-valuenow', String(valor));
        iconoEstado.classList.remove('seco', 'adecuado', 'humedo');
        iconoEstado.classList.add(resultado.clase);
        iconoEstado.innerHTML = '<i class="fa-solid ' + resultado.icono + '" aria-hidden="true"></i>';
    }

    control.addEventListener('input', actualizar);
    btnSeco.addEventListener('click', function () { control.value = '15'; actualizar(); });
    btnAdecuado.addEventListener('click', function () { control.value = '50'; actualizar(); });
    btnHumedo.addEventListener('click', function () { control.value = '85'; actualizar(); });
    actualizar();
})();
</script>
</body>
</html>
