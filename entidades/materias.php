<?php
include '../db.php';
session_start();
if (!isset($_SESSION['id_usuario'])) {header("Location: ../index.php"); exit;}
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['accion'] == 'crear') {
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $conn->query("INSERT INTO materias (codigo, nombre) VALUES ('$codigo', '$nombre')");
}
if (isset($_GET['borrar'])) {
    $id = $_GET['borrar'];
    $conn->query("DELETE FROM materias WHERE id=$id");
}
$materias = $conn->query("SELECT * FROM materias");
?>
<html>
<head><link rel="stylesheet" href="../styles.css"></head>
<body>
<a href="../inicio.php">Volver</a>
<h2>Materias</h2>
<form method="POST">
    <input name="codigo" required placeholder="Código">
    <input name="nombre" required placeholder="Nombre">
    <input type="hidden" name="accion" value="crear">
    <button type="submit">Agregar</button>
</form>
<table>
<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Acción</th></tr>
<?php while($row = $materias->fetch_assoc()){ ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['codigo'] ?></td>
    <td><?= $row['nombre'] ?></td>
    <td><a href="?borrar=<?= $row['id']?>">Eliminar</a></td>
</tr>
<?php } ?>
</table>

</body>
</html>
