<?php
include '../db.php';
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['borrar'])) {
    $id = intval($_GET['borrar']);
    $stmtDelete = $conn->prepare("DELETE FROM asistencias WHERE id = ?");
    if ($stmtDelete) {
        $stmtDelete->bind_param("i", $id);
        $stmtDelete->execute();
        $stmtDelete->close();
    }
    header("Location: asistencias.php");
    exit;
}

$rfidColumnExists = false;
$columnCheck = $conn->query("SHOW COLUMNS FROM asistencias LIKE 'rfid'");
if ($columnCheck) {
    $rfidColumnExists = $columnCheck->num_rows > 0;
    $columnCheck->free();
}

$nombreExpr = $rfidColumnExists
    ? "COALESCE(u_alumno.nombre_completo, u_rfid.nombre_completo, 'Sin nombre asociado')"
    : "COALESCE(u_alumno.nombre_completo, 'Sin nombre asociado')";
$rfidJoin = $rfidColumnExists ? "LEFT JOIN usuarios u_rfid ON a.rfid = u_rfid.rfid" : "";

$sql = "
SELECT 
    a.id,
    {$nombreExpr} AS nombre_completo,
    h.hora_inicio,
    h.hora_fin,
    d.nombre AS division,
    a.fecha,
    a.estado,
    a.notas
FROM asistencias a
LEFT JOIN alumnos al ON a.alumno_id = al.id
LEFT JOIN usuarios u_alumno ON al.usuario_id = u_alumno.id
{$rfidJoin}
LEFT JOIN horarios h ON a.horario_id = h.id
LEFT JOIN divisiones d ON a.division_id = d.id
ORDER BY a.fecha DESC, a.id DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Error en la consulta SQL: " . $conn->error);
}

$asistencias = [];
while ($row = $result->fetch_assoc()) {
    $asistencias[] = $row;
}
?>

<html>
<head>
    <link rel="stylesheet" href="../styles.css" />
    <title>Listado de Asistencias</title>
</head>
<body>
<div class="container">
<a href="../inicio.php" class="return-link">Volver</a>
<h2>Asistencias</h2>
<?php if (empty($asistencias)) : ?>
    <p>No se encontraron registros de asistencia.</p>
<?php else : ?>
<table>
<thead>
<tr>
    <th>ID</th>
    <th>Alumno</th>
    <th>Hora Inicio</th>
    <th>Hora Fin</th>
    <th>Division</th>
    <th>Fecha</th>
    <th>Estado</th>
    <th>Notas</th>
    <th>Accion</th>
</tr>
</thead>
<tbody>
<?php foreach ($asistencias as $row) : ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
    <td><?= $row['hora_inicio'] !== null ? $row['hora_inicio'] : '-' ?></td>
    <td><?= $row['hora_fin'] !== null ? $row['hora_fin'] : '-' ?></td>
    <td><?= $row['division'] !== null ? htmlspecialchars($row['division']) : '-' ?></td>
    <td><?= $row['fecha'] ?></td>
    <td><?= htmlspecialchars($row['estado']) ?></td>
    <td><?= $row['notas'] !== null ? htmlspecialchars($row['notas']) : '-' ?></td>
    <td><a href="?borrar=<?= $row['id'] ?>" onclick="return confirm('Eliminar esta asistencia?')">Eliminar</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
</body>
</html>
