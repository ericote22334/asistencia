<?php
include '../db.php';
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}

// Consulta correcta con JOINs para traer datos de asistencias completos
$sql = "
SELECT 
    a.id,
    u.nombre_completo,
    h.hora_inicio,
    h.hora_fin,
    d.nombre AS division,
    a.fecha,
    a.estado,
    a.notas
FROM asistencias a
JOIN alumnos al ON a.alumno_id = al.id
JOIN usuarios u ON al.usuario_id = u.id
JOIN horarios h ON a.horario_id = h.id
JOIN divisiones d ON a.division_id = d.id
ORDER BY a.fecha DESC, a.id DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Error en la consulta SQL: " . $conn->error);
}

if ($result->num_rows == 0) {
    echo "<p>No se encontraron registros de asistencia.</p>";
    exit;
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
<table>
<thead>
<tr>
    <th>ID</th>
    <th>Alumno</th>
    <th>Hora Inicio</th>
    <th>Hora Fin</th>
    <th>División</th>
    <th>Fecha</th>
    <th>Estado</th>
    <th>Notas</th>
    <th>Acción</th>
</tr>
</thead>
<tbody>
<?php while ($row = $result->fetch_assoc()) : ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
    <td><?= $row['hora_inicio'] ?></td>
    <td><?= $row['hora_fin'] ?></td>
    <td><?= htmlspecialchars($row['division']) ?></td>
    <td><?= $row['fecha'] ?></td>
    <td><?= htmlspecialchars($row['estado']) ?></td>
    <td><?= htmlspecialchars($row['notas']) ?></td>
    <td><a href="?borrar=<?= $row['id'] ?>" onclick="return confirm('¿Eliminar esta asistencia?')">Eliminar</a></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>
</body>
</html>
