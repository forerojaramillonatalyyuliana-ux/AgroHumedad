<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (
    !isset($_SESSION['usuario_id']) ||
    strcasecmp(trim((string)($_SESSION['usuario_rol'] ?? '')), 'Administrador') !== 0
) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/conexion.php';

if (!isset($conexion) || !($conexion instanceof mysqli)) {
    http_response_code(500);
    exit('Error: conexion.php debe crear una conexión MySQLi en la variable $conexion.');
}

$conexion->set_charset('utf8mb4');

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

$csrfToken = (string)$_SESSION['admin_csrf'];

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function adminRedirect(string $table, string $message = ''): void {
    $url = 'admin.php?tabla=' . rawurlencode($table);

    if ($message !== '') {
        $url .= '&mensaje=' . rawurlencode($message);
    }

    header('Location: ' . $url);
    exit;
}

function bindValues(mysqli_stmt $stmt, string $types, array &$values): void {
    if ($types === '' || !$values) {
        return;
    }

    $args = [$types];

    foreach ($values as $key => &$value) {
        $args[] = &$value;
    }

    call_user_func_array([$stmt, 'bind_param'], $args);
}

function isPasswordColumn(string $column): bool {
    return in_array(
        strtolower($column),
        ['password', 'contrasena', 'contrasena_hash'],
        true
    );
}

function prettyLabel(string $table, string $column): string {
    $labels = [
        'id_u' => 'ID de usuario',
        'id_sh' => 'ID de sensor',
        'id_lh' => 'ID de lectura',
        'id_c' => 'ID de contacto',
        'id_contacto' => 'ID de contacto',
        'id_pc' => 'ID de pronóstico',
        'id_rr' => 'ID de recomendación',
        'id_n' => 'ID de notificación',
        'id_nr' => 'ID de notificación',
        'id_rd' => 'ID de rol',
        'id_noticia' => 'ID de noticia',
        'nombre' => 'Nombre',
        'apellido' => 'Apellido',
        'tel' => 'Teléfono',
        'documento' => 'Documento',
        'rol' => 'Rol',
        'tipo_usuario' => 'Tipo de usuario',
        'correo' => 'Correo electrónico',
        'password' => 'Contraseña',
        'codigo' => 'Código',
        'marca' => 'Marca',
        'profundidad' => 'Profundidad (cm)',
        'fecha_hora' => 'Fecha y hora',
        'humedad' => 'Humedad del suelo (%)',
        'mensaje' => 'Mensaje',
        'fecha' => 'Fecha',
        'temp_max' => 'Temperatura máxima (°C)',
        'temp_min' => 'Temperatura mínima (°C)',
        'prob_lluvia' => 'Probabilidad de lluvia (%)',
        'descripcion' => 'Descripción',
        'nivel' => 'Nivel de riesgo',
        'leida' => '¿Leída?',
        'titulo' => 'Título',
        'categoria' => 'Categoría',
        'resumen' => 'Resumen',
        'contenido' => 'Contenido de la noticia',
        'imagen_url' => 'Imagen de la noticia',
        'fuente_url' => 'Enlace de la fuente',
        'estado' => 'Estado',
        'fecha_creacion' => 'Fecha de creación',
        'fecha_publicacion' => 'Fecha de publicación'
    ];

    if ($column === 'id_u' && $table !== 'usuario') {
        return 'Usuario relacionado';
    }

    if ($column === 'id_sh' && $table === 'lectora') {
        return 'Sensor relacionado';
    }

    return $labels[$column] ?? ucfirst(str_replace('_', ' ', $column));
}

function enumOptionsFromType(string $type): array {
    if (!preg_match('/^enum\((.*)\)$/i', $type, $matches)) {
        return [];
    }

    preg_match_all("/'((?:\\\\\\\\.|[^'])*)'/", $matches[1], $items);

    return $items[1] ?? [];
}

function columnInputType(string $column, array $meta): string {
    $type = strtolower((string)$meta['Type']);

    if (isPasswordColumn($column)) return 'password';
    if (preg_match('/^enum\(/', $type)) return 'select';
    if ($column === 'leida') return 'boolean';
    if ($column === 'correo' || str_contains($type, 'email')) return 'email';

    if (
        str_contains($type, 'datetime') ||
        str_contains($type, 'timestamp')
    ) {
        return 'datetime-local';
    }

    if (preg_match('/^date(?:\(|$)/', $type)) return 'date';
    if (preg_match('/^time(?:\(|$)/', $type)) return 'time';

    if (preg_match('/int|decimal|float|double|real/', $type)) {
        return 'number';
    }

    if (preg_match('/text|json/', $type)) return 'textarea';

    return 'text';
}

function mysqlMaxLength(string $type): ?int {
    if (preg_match('/^(?:var)?char\((\d+)\)/i', $type, $matches)) {
        return (int)$matches[1];
    }

    return null;
}

/* Tablas que se mostrarán en el administrador. */
$tableTitles = [
    'usuario' => 'Usuarios',
    'sensor' => 'Sensores',
    'lectora' => 'Lecturas de humedad',
    'contacto' => 'Mensajes de contacto',
    'pronostico_clima' => 'Pronósticos del clima',
    'recomendacion_riesgo' => 'Recomendaciones de riesgo',
    'notificaciones_riesgo' => 'Notificaciones de riesgo',
    'roles' => 'Roles',
    'noticias' => 'Noticias'
];

/* Detectar tablas y columnas existentes. */
$schema = [];

foreach ($tableTitles as $tableName => $title) {
    try {
        $result = $conexion->query(
            'SHOW COLUMNS FROM `' . $tableName . '`'
        );

        $columns = [];

        while ($column = $result->fetch_assoc()) {
            $columns[$column['Field']] = $column;
        }

        if ($columns) {
            $schema[$tableName] = [
                'title' => $title,
                'columns' => $columns
            ];
        }
    } catch (Throwable $ex) {
        error_log(
            'AgroHumedad schema ' .
            $tableName . ': ' . $ex->getMessage()
        );
    }
}

if (!$schema) {
    http_response_code(500);
    exit(
        'No se encontraron las tablas compatibles. ' .
        'Revisa conexion.php y la base de datos seleccionada.'
    );
}

/* Consultar las relaciones entre tablas. */
$foreignKeys = [];

try {
    $result = $conexion->query(
        "SELECT TABLE_NAME, COLUMN_NAME,
                REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
         FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_SCHEMA = DATABASE()
           AND REFERENCED_TABLE_NAME IS NOT NULL"
    );

    while ($fk = $result->fetch_assoc()) {
        $foreignKeys[$fk['TABLE_NAME']][$fk['COLUMN_NAME']] = [
            'table' => $fk['REFERENCED_TABLE_NAME'],
            'column' => $fk['REFERENCED_COLUMN_NAME']
        ];
    }
} catch (Throwable $ex) {
    error_log('AgroHumedad foreign keys: ' . $ex->getMessage());
}

$table = (string)($_GET['tabla'] ?? 'usuario');

if (!isset($schema[$table])) {
    $table = (string)array_key_first($schema);
}

$config = $schema[$table];
$message = (string)($_GET['mensaje'] ?? '');
$currentUserId = (int)$_SESSION['usuario_id'];

/* Cerrar sesión. */
if (isset($_GET['salir'])) {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    header('Location: login.php');
    exit;
}

/* Procesar formularios. */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals(
        $csrfToken,
        (string)($_POST['csrf_token'] ?? '')
    )) {
        adminRedirect(
            $table,
            'El formulario venció. Recarga la página e inténtalo nuevamente.'
        );
    }

    $action = (string)($_POST['accion'] ?? '');
    $postTable = (string)($_POST['tabla'] ?? $table);

    if (!isset($schema[$postTable])) {
        adminRedirect($table, 'La tabla seleccionada no existe.');
    }

    $columnsMeta = $schema[$postTable]['columns'];
    $primaryKey = null;

    foreach ($columnsMeta as $columnName => $meta) {
        if (($meta['Key'] ?? '') === 'PRI') {
            $primaryKey = $columnName;
            break;
        }
    }

    if ($primaryKey === null) {
        adminRedirect(
            $postTable,
            'La tabla no tiene una clave primaria.'
        );
    }

    $id = (int)($_POST['id'] ?? 0);

    /* Eliminar registros. */
    if ($action === 'eliminar') {
        if ($postTable === 'usuario' && $id === $currentUserId) {
            adminRedirect(
                $postTable,
                'No puedes eliminar tu propia cuenta mientras usas el panel.'
            );
        }

        if ($id < 1) {
            adminRedirect(
                $postTable,
                'El registro seleccionado no es válido.'
            );
        }

        try {
            $stmt = $conexion->prepare(
                'DELETE FROM `' . $postTable .
                '` WHERE `' . $primaryKey . '` = ?'
            );

            $stmt->bind_param('i', $id);
            $stmt->execute();

            adminRedirect(
                $postTable,
                'Registro eliminado correctamente.'
            );
        } catch (Throwable $ex) {
            error_log(
                'AgroHumedad delete: ' . $ex->getMessage()
            );

            adminRedirect(
                $postTable,
                'No se pudo eliminar. Comprueba si el registro tiene relaciones con otros datos.'
            );
        }
    }

    if ($action !== 'guardar') {
        adminRedirect($postTable, 'La acción solicitada no es válida.');
    }

    $data = [];
    $imageUpload = null;
    $newImageAbsolutePath = null;

    try {
        foreach ($columnsMeta as $columnName => $meta) {

            if (
                $columnName === $primaryKey &&
                stripos((string)$meta['Extra'], 'auto_increment') !== false
            ) {
                continue;
            }

            if (
                stripos((string)($meta['Extra'] ?? ''), 'generated') !== false
            ) {
                continue;
            }

            $columnType = strtolower((string)$meta['Type']);

            /*
             * NOTICIAS:
             * La imagen se recibe como archivo y no como texto.
             */
            if (
                $postTable === 'noticias' &&
                $columnName === 'imagen_url'
            ) {
                $upload = $_FILES['imagen_url'] ?? null;

                $uploadError = is_array($upload)
                    ? (int)($upload['error'] ?? UPLOAD_ERR_NO_FILE)
                    : UPLOAD_ERR_NO_FILE;

                if ($uploadError !== UPLOAD_ERR_NO_FILE) {

                    if ($uploadError !== UPLOAD_ERR_OK) {
                        adminRedirect(
                            $postTable,
                            'No se pudo recibir la imagen. Comprueba el tamaño del archivo y vuelve a intentarlo.'
                        );
                    }

                    if ((int)$upload['size'] > 5 * 1024 * 1024) {
                        adminRedirect(
                            $postTable,
                            'La imagen no puede superar los 5 MB.'
                        );
                    }

                    $temporaryFile = (string)($upload['tmp_name'] ?? '');

                    if (
                        $temporaryFile === '' ||
                        !is_uploaded_file($temporaryFile)
                    ) {
                        adminRedirect(
                            $postTable,
                            'El archivo seleccionado no es una carga válida.'
                        );
                    }

                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($temporaryFile);

                    $allowedImageTypes = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        'image/gif' => 'gif'
                    ];

                    if (
                        !isset($allowedImageTypes[$mimeType]) ||
                        @getimagesize($temporaryFile) === false
                    ) {
                        adminRedirect(
                            $postTable,
                            'Formato no permitido. Usa JPG, PNG, WEBP o GIF.'
                        );
                    }

                    $imageUpload = [
                        'tmp_name' => $temporaryFile,
                        'extension' => $allowedImageTypes[$mimeType]
                    ];

                    $data[$columnName] = '__PENDING_IMAGE_UPLOAD__';

                } elseif (
                    $id === 0 &&
                    (string)$meta['Null'] === 'NO' &&
                    $meta['Default'] === null
                ) {
                    adminRedirect(
                        $postTable,
                        'Selecciona una imagen para la noticia.'
                    );
                }

                /*
                 * En edición, si no adjunta otra imagen,
                 * se conserva la imagen que ya tiene la noticia.
                 */
                continue;
            }

            /* Procesamiento especial para contraseñas. */
            if (isPasswordColumn($columnName)) {
                $plain = (string)($_POST[$columnName] ?? '');

                if ($id === 0 && $plain === '') {
                    adminRedirect(
                        $postTable,
                        'Debes ingresar una contraseña para crear el usuario.'
                    );
                }

                if ($plain !== '') {
                    if (strlen($plain) < 6) {
                        adminRedirect(
                            $postTable,
                            'La contraseña debe tener al menos 6 caracteres.'
                        );
                    }

                    $data[$columnName] = password_hash(
                        $plain,
                        PASSWORD_DEFAULT
                    );
                }

                continue;
            }

            /* Revisar campos que no llegaron desde el formulario. */
            if (!array_key_exists($columnName, $_POST)) {
                if ($id > 0 || $meta['Default'] !== null) {
                    continue;
                }

                if ((string)$meta['Null'] === 'YES') {
                    $data[$columnName] = null;
                    continue;
                }

                adminRedirect(
                    $postTable,
                    'Completa el campo obligatorio: ' .
                    prettyLabel($postTable, $columnName) . '.'
                );
            }

            $raw = trim((string)($_POST[$columnName] ?? ''));

            if ($raw === '') {
                if ((string)$meta['Null'] === 'YES') {
                    $data[$columnName] = null;
                } elseif ($meta['Default'] !== null) {
                    continue;
                } elseif ($id > 0) {
                    adminRedirect(
                        $postTable,
                        'El campo "' .
                        prettyLabel($postTable, $columnName) .
                        '" no puede quedar vacío.'
                    );
                } else {
                    adminRedirect(
                        $postTable,
                        'Completa el campo obligatorio: ' .
                        prettyLabel($postTable, $columnName) . '.'
                    );
                }

                continue;
            }

            if (
                $columnName === 'correo' &&
                !filter_var($raw, FILTER_VALIDATE_EMAIL)
            ) {
                adminRedirect(
                    $postTable,
                    'Ingresa un correo electrónico válido.'
                );
            }

            if (in_array($columnName, ['humedad', 'prob_lluvia'], true)) {
                if (
                    !is_numeric($raw) ||
                    (float)$raw < 0 ||
                    (float)$raw > 100
                ) {
                    adminRedirect(
                        $postTable,
                        prettyLabel($postTable, $columnName) .
                        ' debe estar entre 0 y 100.'
                    );
                }
            }

            if (
                $columnName === 'leida' &&
                !in_array($raw, ['0', '1'], true)
            ) {
                adminRedirect(
                    $postTable,
                    'El campo Leída debe ser Sí o No.'
                );
            }

            /* Validar los registros relacionados cuando hay llaves foráneas. */
            if (isset($foreignKeys[$postTable][$columnName])) {
                $fk = $foreignKeys[$postTable][$columnName];

                $check = $conexion->prepare(
                    'SELECT 1 FROM `' . $fk['table'] .
                    '` WHERE `' . $fk['column'] . '` = ? LIMIT 1'
                );

                $checkValue = $raw;
                $check->bind_param('s', $checkValue);
                $check->execute();

                if (!$check->get_result()->fetch_row()) {
                    adminRedirect(
                        $postTable,
                        'El registro relacionado para "' .
                        prettyLabel($postTable, $columnName) .
                        '" no existe.'
                    );
                }
            }

            /* Evitar quitar el rol del administrador conectado. */
            if (
                $postTable === 'usuario' &&
                in_array($columnName, ['rol', 'tipo_usuario'], true) &&
                $id === $currentUserId &&
                !in_array(
                    mb_strtolower($raw, 'UTF-8'),
                    ['administrador', 'admin'],
                    true
                )
            ) {
                adminRedirect(
                    $postTable,
                    'No puedes quitarte el rol de administrador mientras usas el panel.'
                );
            }

            if (preg_match('/^enum\(/', $columnType)) {
                $allowed = enumOptionsFromType($columnType);

                if ($allowed && !in_array($raw, $allowed, true)) {
                    adminRedirect(
                        $postTable,
                        'El valor seleccionado para "' .
                        prettyLabel($postTable, $columnName) .
                        '" no es válido.'
                    );
                }
            }

            /* Convertir el valor según el tipo de columna MySQL. */
            if (str_contains($columnType, 'int')) {
                if (filter_var($raw, FILTER_VALIDATE_INT) === false) {
                    adminRedirect(
                        $postTable,
                        prettyLabel($postTable, $columnName) .
                        ' debe ser un número entero.'
                    );
                }

                $value = (int)$raw;

            } elseif (
                preg_match('/decimal|float|double|real/', $columnType)
            ) {
                if (!is_numeric($raw)) {
                    adminRedirect(
                        $postTable,
                        prettyLabel($postTable, $columnName) .
                        ' debe ser numérico.'
                    );
                }

                $value = (float)$raw;

            } elseif (
                str_contains($columnType, 'datetime') ||
                str_contains($columnType, 'timestamp')
            ) {
                $value = str_replace('T', ' ', $raw);

                if (strlen($value) === 16) {
                    $value .= ':00';
                }

            } else {
                $value = $raw;
            }

            $data[$columnName] = $value;
        }

        if (!$data) {
            adminRedirect(
                $postTable,
                'No hay campos para guardar. Revisa el formulario.'
            );
        }

        /*
         * GUARDAR IMAGEN:
         * Crea uploads/noticias si todavía no existe.
         */
        if ($postTable === 'noticias' && $imageUpload !== null) {

            $uploadDirectory =
                __DIR__ . DIRECTORY_SEPARATOR .
                'uploads' . DIRECTORY_SEPARATOR . 'noticias';

            if (
                !is_dir($uploadDirectory) &&
                !mkdir($uploadDirectory, 0755, true) &&
                !is_dir($uploadDirectory)
            ) {
                adminRedirect(
                    $postTable,
                    'No se pudo crear uploads/noticias. Verifica los permisos de la carpeta del proyecto.'
                );
            }

            $fileName =
                'noticia_' .
                bin2hex(random_bytes(12)) .
                '.' .
                $imageUpload['extension'];

            $newImageAbsolutePath =
                $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;

            if (
                !move_uploaded_file(
                    $imageUpload['tmp_name'],
                    $newImageAbsolutePath
                )
            ) {
                $newImageAbsolutePath = null;

                adminRedirect(
                    $postTable,
                    'No se pudo guardar la imagen. Verifica los permisos de uploads/noticias.'
                );
            }

            $data['imagen_url'] = 'uploads/noticias/' . $fileName;
        }

        if ($postTable === 'usuario' && $id === $currentUserId) {
            $role = (string)(
                $data['rol'] ??
                $data['tipo_usuario'] ??
                'Administrador'
            );

            if (!in_array(
                mb_strtolower($role, 'UTF-8'),
                ['administrador', 'admin'],
                true
            )) {
                adminRedirect(
                    $postTable,
                    'No puedes quitarte el rol de administrador mientras usas el panel.'
                );
            }
        }

        $values = [];
        $types = '';

        /* Actualizar registro existente. */
        if ($id > 0) {
            $sets = [];

            foreach ($data as $columnName => $value) {
                $sets[] = '`' . $columnName . '` = ?';
                $values[] = $value;

                $types .= is_int($value)
                    ? 'i'
                    : (is_float($value) ? 'd' : 's');
            }

            $values[] = $id;
            $types .= 'i';

            $sql =
                'UPDATE `' . $postTable .
                '` SET ' . implode(', ', $sets) .
                ' WHERE `' . $primaryKey . '` = ?';

        } else {
            /* Crear registro nuevo. */
            $columnNames = array_keys($data);

            foreach ($data as $value) {
                $values[] = $value;

                $types .= is_int($value)
                    ? 'i'
                    : (is_float($value) ? 'd' : 's');
            }

            $marks = array_fill(0, count($columnNames), '?');

            $sql =
                'INSERT INTO `' . $postTable . '` (`' .
                implode('`,`', $columnNames) .
                '`) VALUES (' . implode(',', $marks) . ')';
        }

        $stmt = $conexion->prepare($sql);
        bindValues($stmt, $types, $values);
        $stmt->execute();

        adminRedirect(
            $postTable,
            $id > 0
                ? 'Registro actualizado correctamente.'
                : 'Registro creado correctamente.'
        );

    } catch (Throwable $ex) {

        if (
            $newImageAbsolutePath !== null &&
            is_file($newImageAbsolutePath)
        ) {
            @unlink($newImageAbsolutePath);
        }

        error_log(
            'AgroHumedad admin save (' .
            $postTable . '): ' .
            $ex->getMessage()
        );

        adminRedirect(
            $postTable,
            'No se pudo guardar. Revisa los campos obligatorios, las relaciones y los valores únicos.'
        );
    }
}

/* Identificar clave primaria. */
$primaryKey = null;

foreach ($config['columns'] as $columnName => $meta) {
    if (($meta['Key'] ?? '') === 'PRI') {
        $primaryKey = $columnName;
        break;
    }
}

if ($primaryKey === null) {
    http_response_code(500);
    exit('La tabla seleccionada no tiene clave primaria.');
}

/* Obtener registro para edición. */
$editing = null;

if (isset($_GET['editar']) && (int)$_GET['editar'] > 0) {
    $editId = (int)$_GET['editar'];

    $stmt = $conexion->prepare(
        'SELECT * FROM `' . $table .
        '` WHERE `' . $primaryKey . '` = ? LIMIT 1'
    );

    $stmt->bind_param('i', $editId);
    $stmt->execute();

    $editing = $stmt->get_result()->fetch_assoc() ?: null;

    if (!$editing) {
        $message = 'No se encontró el registro seleccionado.';
    }
}

/* Consultar los registros sin mostrar hashes de contraseñas. */
$records = [];

try {
    $selected = [];

    foreach ($config['columns'] as $columnName => $meta) {
        if (!isPasswordColumn($columnName)) {
            $selected[] = '`' . $columnName . '`';
        }
    }

    if (!$selected) {
        $selected[] = '`' . $primaryKey . '`';
    }

    $sql =
        'SELECT ' . implode(',', $selected) .
        ' FROM `' . $table .
        '` ORDER BY `' . $primaryKey . '` DESC';

    $records = $conexion->query($sql)->fetch_all(MYSQLI_ASSOC);

} catch (Throwable $ex) {
    error_log('AgroHumedad admin list: ' . $ex->getMessage());
    $message = 'No se pudieron consultar los registros de esta tabla.';
}

/* Crear opciones para las llaves foráneas. */
$foreignOptions = [];

foreach ($config['columns'] as $columnName => $meta) {
    if (!isset($foreignKeys[$table][$columnName])) {
        continue;
    }

    $fk = $foreignKeys[$table][$columnName];

    if (!isset($schema[$fk['table']])) {
        continue;
    }

    try {
        $refColumns = $schema[$fk['table']]['columns'];
        $displayColumn = null;

        foreach (
            [
                'nombre',
                'apellido',
                'titulo',
                'fecha',
                'codigo',
                'descripcion',
                'nivel',
                'correo',
                'categoria'
            ] as $candidate
        ) {
            if (
                isset($refColumns[$candidate]) &&
                $candidate !== $fk['column']
            ) {
                $displayColumn = $candidate;
                break;
            }
        }

        $cols = '`' . $fk['column'] . '`';

        if ($displayColumn !== null) {
            $cols .= ', `' . $displayColumn . '`';
        }

        $rows = $conexion->query(
            'SELECT ' . $cols .
            ' FROM `' . $fk['table'] .
            '` ORDER BY `' . $fk['column'] . '` DESC'
        )->fetch_all(MYSQLI_ASSOC);

        foreach ($rows as $row) {
            $key = (string)$row[$fk['column']];

            $foreignOptions[$columnName][$key] =
                $key .
                (
                    $displayColumn &&
                    (string)$row[$displayColumn] !== ''
                        ? ' — ' . $row[$displayColumn]
                        : ''
                );
        }

    } catch (Throwable $ex) {
        error_log('AgroHumedad admin FK options: ' . $ex->getMessage());
        $foreignOptions[$columnName] = [];
    }
}

$adminName = $_SESSION['usuario_nombre'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel de administración | AgroHumedad</title>

<style>
:root{
    --green:#39734b;
    --dark:#244d35;
    --light:#edf5eb;
    --bg:#f4f7f1;
    --text:#34443a;
    --border:#dce6dc;
    --red:#a83d3d;
}

*{box-sizing:border-box}

body{
    margin:0;
    font-family:Arial,Helvetica,sans-serif;
    background:var(--bg);
    color:var(--text);
    line-height:1.5;
}

a{color:inherit;text-decoration:none}
button,input,select,textarea{font:inherit}

.bar{
    background:#fff;
    border-bottom:1px solid var(--border);
    padding:16px 4%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    flex-wrap:wrap;
}

.brand{
    font-size:22px;
    font-weight:800;
    color:var(--dark);
}

.brand span{color:var(--green)}

.bar-right{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.btn{
    display:inline-flex;
    justify-content:center;
    align-items:center;
    border:0;
    border-radius:9px;
    background:var(--green);
    color:#fff;
    padding:10px 14px;
    cursor:pointer;
    font-weight:700;
    font-size:13px;
    text-align:center;
}

.btn:hover{background:var(--dark)}
.btn-secondary{background:#e8eee7;color:var(--dark)}
.btn-delete{background:var(--red)}

.container{
    width:min(1500px,94%);
    margin:28px auto;
}

.welcome{
    background:linear-gradient(120deg,var(--dark),var(--green));
    color:#fff;
    padding:clamp(20px,4vw,32px);
    border-radius:16px;
    margin-bottom:22px;
}

.welcome h1{
    margin:0 0 6px;
    font-size:clamp(24px,4vw,34px);
}

.welcome p{margin:0}

.message{
    background:#e2f2e4;
    border:1px solid #b7d9bc;
    color:var(--dark);
    padding:12px 15px;
    border-radius:9px;
    margin-bottom:18px;
    overflow-wrap:anywhere;
}

.tabs{
    display:flex;
    gap:9px;
    flex-wrap:wrap;
    margin-bottom:20px;
}

.tabs a{
    padding:10px 14px;
    border:1px solid var(--border);
    border-radius:9px;
    background:#fff;
    font-weight:700;
    font-size:13px;
}

.tabs a.active{
    background:var(--green);
    color:#fff;
    border-color:var(--green);
}

.layout{
    display:grid;
    grid-template-columns:minmax(260px,.75fr) minmax(0,1.8fr);
    gap:20px;
    align-items:start;
}

.panel{
    background:#fff;
    padding:22px;
    border:1px solid var(--border);
    border-radius:14px;
    box-shadow:0 7px 24px #244d350c;
    min-width:0;
}

.panel h2{
    margin:0 0 16px;
    color:var(--dark);
    font-size:20px;
}

label{
    display:block;
    font-size:13px;
    font-weight:700;
    margin:13px 0 6px;
}

input,select,textarea{
    width:100%;
    min-width:0;
    padding:11px 12px;
    border:1px solid #cbd8cc;
    border-radius:8px;
    background:#fff;
    color:var(--text);
}

textarea{
    min-height:95px;
    resize:vertical;
}

.actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin-top:17px;
}

.table-wrap{
    width:100%;
    overflow-x:auto;
    border-radius:8px;
}

table{
    border-collapse:collapse;
    width:100%;
    min-width:650px;
    font-size:13px;
}

th,td{
    text-align:left;
    padding:11px 9px;
    border-bottom:1px solid #e7eee7;
    vertical-align:top;
    overflow-wrap:anywhere;
}

th{
    background:var(--light);
    color:var(--dark);
    white-space:nowrap;
}

td.actions-cell{white-space:nowrap}
td.actions-cell .btn{margin:2px}

.empty{
    padding:24px 12px;
    text-align:center;
    color:#718074;
    background:#fafcf9;
    border-radius:9px;
}

.note,.field-help{
    font-size:12px;
    color:#738075;
    margin-top:10px;
    line-height:1.6;
    font-weight:400;
}

/* ESTILOS DEL BOTÓN PARA ADJUNTAR IMÁGENES */
.image-upload-box{
    padding:12px;
    border:1px dashed #b9cbbc;
    border-radius:10px;
    background:#fafcf9;
}

.image-file-input{
    display:block;
    padding:9px;
    background:#fff;
    cursor:pointer;
}

.image-file-input::file-selector-button{
    border:0;
    border-radius:7px;
    background:var(--green);
    color:#fff;
    font-weight:700;
    padding:9px 12px;
    margin-right:10px;
    cursor:pointer;
}

.image-preview{
    display:block;
    max-width:100%;
    max-height:220px;
    object-fit:contain;
    margin:8px 0 12px;
    border-radius:8px;
    border:1px solid var(--border);
    background:#fff;
}

.table-image{
    display:block;
    width:80px;
    height:58px;
    object-fit:cover;
    border-radius:6px;
    border:1px solid var(--border);
    margin-bottom:5px;
}

.footer{
    margin-top:35px;
    padding:22px 5%;
    text-align:center;
    background:#fff;
    border-top:1px solid var(--border);
    color:#657568;
    font-size:13px;
}

@media(max-width:1050px){
    .layout{grid-template-columns:1fr}
    .container{margin:20px auto}
}

@media(max-width:600px){
    .bar{align-items:flex-start}
    .bar-right{width:100%}
    .bar-right .btn{flex:1}
    .panel{padding:16px}
    .tabs{display:grid;grid-template-columns:1fr}
    .tabs a{text-align:center}
    .actions{flex-direction:column}
    .actions .btn{width:100%}
}
</style>
</head>

<body>

<header class="bar">
    <div class="brand">
        Agro<span>Humedad</span> | Administrador
    </div>

    <div class="bar-right">
        <strong>Hola, <?=e($adminName)?></strong>

        <a class="btn btn-secondary" href="INDEX.php">
            Ver sitio
        </a>

        <a
            class="btn btn-delete"
            href="admin.php?salir=1"
            onclick="return confirm('¿Deseas cerrar la sesión?')"
        >
            Cerrar sesión
        </a>
    </div>
</header>

<main class="container">

    <section class="welcome">
        <h1>Panel de administración</h1>
        <p>
            Gestiona los datos de AgroHumedad desde un solo lugar.
            Las tablas y campos se muestran según la estructura de la base de datos.
        </p>
    </section>

    <?php if ($message !== ''): ?>
        <div class="message" role="status">
            <?=e($message)?>
        </div>
    <?php endif; ?>

    <nav class="tabs" aria-label="Tablas de administración">
        <?php foreach ($schema as $key => $item): ?>
            <a
                class="<?=$table === $key ? 'active' : ''?>"
                href="admin.php?tabla=<?=e($key)?>"
            >
                <?=e($item['title'])?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="layout">

        <section class="panel">

            <h2>
                <?=$editing ? 'Editar registro' : 'Crear registro'?>
                · <?=e($config['title'])?>
            </h2>

            <!-- IMPORTANTE: multipart/form-data permite enviar archivos -->
            <form
                method="post"
                enctype="multipart/form-data"
                action="admin.php?tabla=<?=e($table)?>"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?=e($csrfToken)?>"
                >

                <input type="hidden" name="accion" value="guardar">

                <input
                    type="hidden"
                    name="tabla"
                    value="<?=e($table)?>"
                >

                <input
                    type="hidden"
                    name="id"
                    value="<?=e($editing[$primaryKey] ?? 0)?>"
                >

                <?php
                foreach ($config['columns'] as $columnName => $meta):

                    if (
                        $columnName === $primaryKey &&
                        stripos((string)$meta['Extra'], 'auto_increment') !== false
                    ) {
                        continue;
                    }

                    if (
                        stripos((string)($meta['Extra'] ?? ''), 'generated') !== false
                    ) {
                        continue;
                    }

                    $value = $editing[$columnName] ?? '';
                    $type = strtolower((string)$meta['Type']);
                    $isPassword = isPasswordColumn($columnName);
                    $inputType = columnInputType($columnName, $meta);

                    $required =
                        (string)$meta['Null'] === 'NO' &&
                        $meta['Default'] === null &&
                        stripos((string)$meta['Extra'], 'auto_increment') === false;

                    $isForeign =
                        isset($foreignKeys[$table][$columnName]) &&
                        isset($foreignOptions[$columnName]);

                    if ($inputType === 'datetime-local' && $value !== '') {
                        $value = date('Y-m-d\TH:i', strtotime((string)$value));
                    }

                    if ($inputType === 'date' && $value !== '') {
                        $value = date('Y-m-d', strtotime((string)$value));
                    }

                    if ($inputType === 'time' && $value !== '') {
                        $value = date('H:i', strtotime((string)$value));
                    }

                    $enumOptions = enumOptionsFromType($type);
                    $maxLength = mysqlMaxLength($type);
                ?>

                    <label for="f_<?=e($columnName)?>">
                        <?=e(prettyLabel($table, $columnName))?>

                        <?=(
                            ($required && !$isPassword) ||
                            ($required && $isPassword && !$editing)
                        ) ? ' *' : ''?>

                        <?php if ($isPassword && $editing): ?>
                            <span class="field-help">
                                (deja vacío para conservar la contraseña actual)
                            </span>
                        <?php endif; ?>
                    </label>

                    <?php if (
                        $table === 'noticias' &&
                        $columnName === 'imagen_url'
                    ): ?>

                        <!-- BOTÓN PARA SELECCIONAR LA IMAGEN -->
                        <div class="image-upload-box">

                            <?php if (
                                is_string($value) &&
                                trim($value) !== ''
                            ): ?>

                                <p class="field-help">Imagen actual:</p>

                                <img
                                    id="imagen-preview"
                                    class="image-preview"
                                    src="<?=e($value)?>"
                                    alt="Imagen actual de la noticia"
                                    onerror="this.style.display='none'"
                                >

                            <?php else: ?>

                                <img
                                    id="imagen-preview"
                                    class="image-preview"
                                    src=""
                                    alt="Vista previa de la imagen seleccionada"
                                    hidden
                                >

                            <?php endif; ?>

                            <input
                                id="f_imagen_url"
                                class="image-file-input"
                                type="file"
                                name="imagen_url"
                                accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
                                aria-describedby="imagen-ayuda"
                                <?=(!$editing && $required) ? 'required' : ''?>
                            >

                            <p id="imagen-ayuda" class="field-help">
                                Pulsa “Seleccionar archivo” para adjuntar una imagen
                                JPG, PNG, WEBP o GIF de máximo 5 MB.

                                <?php if ($editing): ?>
                                    Si no seleccionas otra imagen, se conserva la actual.
                                <?php endif; ?>
                            </p>

                        </div>

                    <?php elseif ($isForeign): ?>

                        <select
                            id="f_<?=e($columnName)?>"
                            name="<?=e($columnName)?>"
                            <?=$required ? 'required' : ''?>
                        >
                            <option value="">
                                <?=$required ? 'Selecciona una opción' : 'Sin relación / no aplica'?>
                            </option>

                            <?php foreach ($foreignOptions[$columnName] as $optionValue => $optionLabel): ?>
                                <option
                                    value="<?=e($optionValue)?>"
                                    <?=((string)$value === (string)$optionValue) ? 'selected' : ''?>
                                >
                                    <?=e($optionLabel)?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                    <?php elseif ($enumOptions): ?>

                        <select
                            id="f_<?=e($columnName)?>"
                            name="<?=e($columnName)?>"
                            <?=$required ? 'required' : ''?>
                        >
                            <?php if (!$required): ?>
                                <option value="">Selecciona una opción</option>
                            <?php endif; ?>

                            <?php foreach ($enumOptions as $option): ?>
                                <option
                                    value="<?=e($option)?>"
                                    <?=((
                                        (string)$value === (string)$option ||
                                        (
                                            $value === '' &&
                                            $meta['Default'] !== null &&
                                            (string)$meta['Default'] === (string)$option
                                        )
                                    ) ? 'selected' : '' )?>
                                >
                                    <?=e($option)?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                    <?php elseif ($inputType === 'boolean'): ?>

                        <select
                            id="f_<?=e($columnName)?>"
                            name="<?=e($columnName)?>"
                        >
                            <option
                                value="0"
                                <?=((string)$value === '0' || $value === '') ? 'selected' : ''?>
                            >
                                No
                            </option>

                            <option
                                value="1"
                                <?=((string)$value === '1') ? 'selected' : ''?>
                            >
                                Sí
                            </option>
                        </select>

                    <?php elseif ($inputType === 'textarea'): ?>

                        <textarea
                            id="f_<?=e($columnName)?>"
                            name="<?=e($columnName)?>"
                            <?=$required ? 'required' : ''?>
                        ><?=e($value)?></textarea>

                    <?php else: ?>

                        <input
                            id="f_<?=e($columnName)?>"
                            type="<?=e($inputType)?>"
                            name="<?=e($columnName)?>"
                            value="<?=$isPassword ? '' : e($value)?>"
                            <?=$required && (!$isPassword || !$editing) ? 'required' : ''?>
                            <?=(
                                $maxLength !== null && $inputType !== 'number'
                            ) ? 'maxlength="' . (int)$maxLength . '"' : ''?>
                            <?=(
                                $inputType === 'number' &&
                                preg_match('/int/', $type)
                            ) ? 'step="1"' : (
                                ($inputType === 'number') ? 'step="any"' : ''
                            )?>
                            <?=(
                                $columnName === 'humedad' ||
                                $columnName === 'prob_lluvia'
                            ) ? 'min="0" max="100"' : ''?>
                            <?=$isPassword
                                ? 'minlength="6" autocomplete="new-password"'
                                : 'autocomplete="off"'?>
                        >

                    <?php endif; ?>

                <?php endforeach; ?>

                <div class="actions">

                    <button class="btn" type="submit">
                        <?=$editing ? 'Guardar cambios' : 'Crear registro'?>
                    </button>

                    <?php if ($editing): ?>
                        <a
                            class="btn btn-secondary"
                            href="admin.php?tabla=<?=e($table)?>"
                        >
                            Cancelar edición
                        </a>
                    <?php endif; ?>

                </div>
            </form>

            <p class="note">
                Las contraseñas se guardan con hash y no se muestran en el listado.
                Las imágenes de Noticias se almacenan en la carpeta uploads/noticias.
            </p>

        </section>

        <section class="panel">

            <h2><?=e($config['title'])?> registrados</h2>

            <?php if (!$records): ?>

                <div class="empty">
                    No hay registros para mostrar en esta tabla.
                </div>

            <?php else: ?>

                <div class="table-wrap">

                    <table>
                        <thead>
                            <tr>
                                <?php foreach ($config['columns'] as $columnName => $meta):
                                    if (isPasswordColumn($columnName)) continue;
                                ?>
                                    <th>
                                        <?=e(prettyLabel($table, $columnName))?>
                                    </th>
                                <?php endforeach; ?>

                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($records as $record): ?>

                                <tr>
                                    <?php foreach ($config['columns'] as $columnName => $meta):

                                        if (isPasswordColumn($columnName)) {
                                            continue;
                                        }

                                        $cell = $record[$columnName] ?? '';

                                        if ($columnName === 'leida') {
                                            $cell = ((int)$cell === 1) ? 'Sí' : 'No';
                                        }

                                        if (
                                            in_array(
                                                $columnName,
                                                ['mensaje', 'descripcion', 'contenido', 'resumen'],
                                                true
                                            ) &&
                                            mb_strlen((string)$cell, 'UTF-8') > 140
                                        ) {
                                            $cell = mb_substr(
                                                (string)$cell,
                                                0,
                                                140,
                                                'UTF-8'
                                            ) . '…';
                                        }
                                    ?>

                                        <td>
                                            <?php if (
                                                $table === 'noticias' &&
                                                $columnName === 'imagen_url' &&
                                                trim((string)$cell) !== ''
                                            ): ?>

                                                <img
                                                    class="table-image"
                                                    src="<?=e($cell)?>"
                                                    alt="Imagen de la noticia"
                                                    loading="lazy"
                                                    onerror="this.style.display='none'"
                                                >

                                                <span><?=e($cell)?></span>

                                            <?php else: ?>

                                                <?=e($cell)?>

                                            <?php endif; ?>
                                        </td>

                                    <?php endforeach; ?>

                                    <td class="actions-cell">

                                        <a
                                            class="btn btn-secondary"
                                            href="admin.php?tabla=<?=e($table)?>&editar=<?=e($record[$primaryKey] ?? '')?>"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            method="post"
                                            action="admin.php?tabla=<?=e($table)?>"
                                            style="display:inline"
                                            onsubmit="return confirm('¿Seguro que deseas eliminar este registro?')"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?=e($csrfToken)?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="accion"
                                                value="eliminar"
                                            >

                                            <input
                                                type="hidden"
                                                name="tabla"
                                                value="<?=e($table)?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?=e($record[$primaryKey] ?? '')?>"
                                            >

                                            <button
                                                class="btn btn-delete"
                                                type="submit"
                                            >
                                                Eliminar
                                            </button>
                                        </form>

                                    </td>
                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>

            <?php endif; ?>

        </section>

    </div>
</main>

<footer class="footer">
    AgroHumedad &copy; <?=date('Y')?> · Panel de administración
</footer>

<script>
(function () {
    const input = document.getElementById('f_imagen_url');
    const preview = document.getElementById('imagen-preview');

    if (!input || !preview) {
        return;
    }

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];

        if (!file) {
            return;
        }

        const allowed = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif'
        ];

        if (!allowed.includes(file.type)) {
            input.value = '';
            alert('Selecciona una imagen JPG, PNG, WEBP o GIF.');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            input.value = '';
            alert('La imagen no puede superar los 5 MB.');
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
        preview.style.display = 'block';
    });
})();
</script>

</body>
</html>