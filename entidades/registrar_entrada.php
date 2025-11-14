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

$rfidColumnExists = false;
$columnCheck = $conn->query("SHOW COLUMNS FROM asistencias LIKE 'rfid'");
if ($columnCheck) {
    $rfidColumnExists = $columnCheck->num_rows > 0;
    $columnCheck->free();
}

$mensaje = "Esperando RFID...";
$mensaje_color = "#333";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = trim($_POST['rfid'] ?? '');

    if ($uid === '') {
        $mensaje = "Debes ingresar un RFID.";
        $mensaje_color = "red";
    } else {
        $stmtUsuario = $conn->prepare("SELECT id, nombre_completo FROM usuarios WHERE rfid = ?");
        if (!$stmtUsuario) {
            $mensaje = "Error en consulta SELECT: " . $conn->error;
            $mensaje_color = "red";
        } else {
            $stmtUsuario->bind_param("s", $uid);
            $stmtUsuario->execute();
            $resultadoUsuario = $stmtUsuario->get_result();

            if ($usuario = $resultadoUsuario->fetch_assoc()) {
                $alumnoId = null;
                $stmtAlumno = $conn->prepare("SELECT id FROM alumnos WHERE usuario_id = ?");
                if ($stmtAlumno) {
                    $stmtAlumno->bind_param("i", $usuario['id']);
                    $stmtAlumno->execute();
                    $resultadoAlumno = $stmtAlumno->get_result();
                    if ($rowAlumno = $resultadoAlumno->fetch_assoc()) {
                        $alumnoId = (int) $rowAlumno['id'];
                    }
                    $stmtAlumno->close();
                }

                if ($alumnoId === null) {
                    $mensaje = "El usuario encontrado no esta vinculado como alumno.";
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
