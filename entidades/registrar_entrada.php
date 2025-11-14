<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}

include "../db.php";

if (!$conn) {
    die("<h2 style='color: red;'>Error de conexion a la base de datos</h2>");
}

function descripcionColumna(mysqli $conn, string $tabla, string $columna): ?array
{
    $tablaSeguro = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
    $columnaSeguro = preg_replace('/[^a-zA-Z0-9_]/', '', $columna);
    $result = $conn->query("SHOW COLUMNS FROM {$tablaSeguro} LIKE '{$columnaSeguro}'");
    if ($result && $result->num_rows > 0) {
        $info = $result->fetch_assoc();
        $result->free();
        return $info;
    }
    return null;
}

function primerValorEnum(?string $type): ?string
{
    if ($type && strpos(strtolower($type), "enum(") === 0) {
        if (preg_match_all("/'([^']+)'/", $type, $matches) && !empty($matches[1])) {
            return $matches[1][0];
        }
    }
    return null;
}

function obtenerAlumnoId(mysqli $conn, int $usuarioId): ?int
{
    $stmt = $conn->prepare("SELECT id FROM alumnos WHERE usuario_id = ?");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    $alumno = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $alumno ? (int) $alumno['id'] : null;
}

function crearAlumnoAutomatico(mysqli $conn, int $usuarioId): ?int
{
    $fechaInfo = descripcionColumna($conn, 'alumnos', 'fecha_nacimiento');
    $fechaDefault = null;
    if ($fechaInfo && strtoupper($fechaInfo['Null']) !== 'YES') {
        $fechaDefault = '2000-01-01';
    }

    $generoInfo = descripcionColumna($conn, 'alumnos', 'genero');
    $generoDefault = null;
    if ($generoInfo && strtoupper($generoInfo['Null']) !== 'YES') {
        $generoDefault = primerValorEnum($generoInfo['Type']) ?? 'Sin dato';
    }

    $stmt = $conn->prepare("INSERT INTO alumnos (usuario_id, fecha_nacimiento, genero) VALUES (?, ?, ?)");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("iss", $usuarioId, $fechaDefault, $generoDefault);
    $exito = $stmt->execute();
    $nuevoId = $exito ? ($stmt->insert_id ?: $conn->insert_id) : null;
    $stmt->close();
    return $nuevoId ?: obtenerAlumnoId($conn, $usuarioId);
}

$rfidColumnExists = false;
$columnCheck = $conn->query("SHOW COLUMNS FROM asistencias LIKE 'rfid'");
$mensaje = "Esperando RFID...";
$mensaje_color = "#333";
if ($columnCheck) {
    $rfidColumnExists = $columnCheck->num_rows > 0;
    $columnCheck->free();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = trim($_POST['rfid'] ?? '');

    if ($uid === '') {
        $mensaje = "Debes ingresar un RFID.";
        $mensaje_color = "red";
    } else {
        $stmtUsuario = $conn->prepare("SELECT id, nombre_completo, rol FROM usuarios WHERE rfid = ?");
        if (!$stmtUsuario) {
            $mensaje = "Error en consulta SELECT: " . $conn->error;
            $mensaje_color = "red";
        } else {
            $stmtUsuario->bind_param("s", $uid);
            $stmtUsuario->execute();
            $resultadoUsuario = $stmtUsuario->get_result();

            if ($usuario = $resultadoUsuario->fetch_assoc()) {
                if (strtolower($usuario['rol']) !== 'alumno') {
                    $mensaje = "El RFID pertenece a un usuario con rol " . $usuario['rol'] . ".";
                    $mensaje_color = "red";
                } else {
                    $alumnoId = obtenerAlumnoId($conn, (int) $usuario['id']);
                    if ($alumnoId === null) {
                        $alumnoId = crearAlumnoAutomatico($conn, (int) $usuario['id']);
                    }

                    if ($alumnoId === null) {
                        $mensaje = "No se pudo vincular automaticamente al usuario como alumno.";
                        $mensaje_color = "red";
                    } else {
                        $divisionId = null;
                        $stmtDivision = $conn->prepare("SELECT division_id FROM inscripciones WHERE alumno_id = ? AND (abandono_en IS NULL OR abandono_en > CURDATE()) ORDER BY inscripto_en DESC LIMIT 1");
                        if ($stmtDivision) {
                        $stmtDivision->bind_param("i", $alumnoId);
                        $stmtDivision->execute();
                        $resultadoDivision = $stmtDivision->get_result();
                        if ($rowDivision = $resultadoDivision->fetch_assoc()) {
                            $divisionId = (int) $rowDivision['division_id'];
                        }
                        $stmtDivision->close();
                    }

                    $horarioId = null;
                    if ($divisionId !== null) {
                        $horaActual = date('H:i:s');
                        $stmtHorario = $conn->prepare("SELECT id FROM horarios WHERE division_id = ? AND ? BETWEEN hora_inicio AND hora_fin ORDER BY id DESC LIMIT 1");
                        if ($stmtHorario) {
                            $stmtHorario->bind_param("is", $divisionId, $horaActual);
                            $stmtHorario->execute();
                            $resultadoHorario = $stmtHorario->get_result();
                            if ($rowHorario = $resultadoHorario->fetch_assoc()) {
                                $horarioId = (int) $rowHorario['id'];
                            }
                            $stmtHorario->close();
                        }
                    }

                        $registradoPor = $_SESSION['id_usuario'] ?? null;

                        if ($rfidColumnExists) {
                            $insert = $conn->prepare("INSERT INTO asistencias (rfid, alumno_id, division_id, horario_id, estado, fecha, registrado_en, registrado_por) VALUES (?, ?, ?, ?, 'presente', CURDATE(), NOW(), ?)");
                        } else {
                            $insert = $conn->prepare("INSERT INTO asistencias (alumno_id, division_id, horario_id, estado, fecha, registrado_en, registrado_por) VALUES (?, ?, ?, 'presente', CURDATE(), NOW(), ?)");
                        }

                        if ($insert) {
                            if ($rfidColumnExists) {
                                $insert->bind_param("siiii", $uid, $alumnoId, $divisionId, $horarioId, $registradoPor);
                            } else {
                                $insert->bind_param("iiii", $alumnoId, $divisionId, $horarioId, $registradoPor);
                            }
                            if ($insert->execute()) {
                                $mensaje = "Asistencia registrada para " . $usuario['nombre_completo'];
                                $mensaje_color = "green";
                            } else {
                                $mensaje = "Error al guardar la asistencia: " . $insert->error;
                                $mensaje_color = "red";
                            }
                            $insert->close();
                        } else {
                            $mensaje = "Error en consulta INSERT: " . $conn->error;
                            $mensaje_color = "red";
                        }
                    }
                }
            } else {
                $mensaje = "RFID no encontrado.";
                $mensaje_color = "red";
            }
            $stmtUsuario->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registro de Asistencia</title>
    <link rel="stylesheet" href="../styles.css" />
</head>
<body>
    <div class="container">
        <a href="../inicio.php" class="return-link">Volver</a>
        <h2>Registro de Asistencia por RFID</h2>
        <p style="color: <?= htmlspecialchars($mensaje_color) ?>;">
            <?= htmlspecialchars($mensaje) ?>
        </p>
        <form method="POST">
            <input type="text" name="rfid" placeholder="Introduce RFID" required autofocus />
            <button type="submit">Registrar</button>
        </form>
    </div>
</body>
</html>
