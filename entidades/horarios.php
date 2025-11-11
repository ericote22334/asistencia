<?php
include '../db.php';
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: ../index.php"); exit; }

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['accion'] == 'crear') {
    $division_id = $_POST['division_id'];
    $asignacion_docente_id = $_POST['asignacion_docente_id'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $ubicacion = $_POST['ubicacion'];
    $conn->query("INSERT INTO horarios (division_id, asignacion_docente_id, hora_inicio, hora_fin, ubicacion) VALUES ('$division_id', '$asignacion_docente_id', '$hora_inicio', '$hora_fin', '$ubicacion')");
}
if (isset($_GET['borrar'])) {
    $id = $_GET['borrar'];
    $conn->query("DELETE FROM horarios WHERE id=$id");
}
$horarios = $conn->query(
    "SELECT horarios.id, divisiones.nombre AS division, horarios.hora_inicio, horarios.hora_fin, horarios.ubicacion 
    FROM horarios 
    JOIN divisiones ON horarios.division_id=divisiones.id"
);
?>
<html>
<head><link rel="stylesheet" href="../styles.css"></head>
<body>
<a href="../inicio.php">Volver</a>
<h2>Horarios</h2>
<form method="POST">
    <input name="division_id" required placeholder="ID División">
    <input name="asignacion_docente_id" required placeholder="ID Asignación docente">
    <input name="hora_inicio" required placeholder="Hora Inicio (HH:MM)">
    <input name="hora_fin" required placeholder="Hora Fin (HH:MM)">
    <input name="ubicacion" required placeholder="Ubicación">
    <input type="hidden" name="accion" value="crear">
    <button type="submit">Agregar</button>
</form>
<table>
<tr><th>ID</th><th>División</th><th>Hora Inicio</th><th>Hora Fin</th><th>Ubicación</th><th>Acción</th></tr>
<?php while($row = $horarios->fetch_assoc()){ ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['division'] ?></td>
    <td><?= $row['hora_inicio'] ?></td>
    <td><?= $row['hora_fin'] ?></td>
    <td><?= $row['ubicacion'] ?></td>
    <td><a href="?borrar=<?= $row['id']?>">Eliminar</a></td>
</tr>
<?php } ?>
</table>

</body>
</html>
