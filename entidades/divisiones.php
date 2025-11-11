<?php
include '../db.php';
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}

// Eliminar división si llega parámetro borrar
if (isset($_GET['borrar'])) {
    $id = intval($_GET['borrar']);
    $conn->query("DELETE FROM divisiones WHERE id = $id");
    header("Location: divisiones.php");
    exit;
}

// Agregar nueva división si llega POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre']) && isset($_POST['ano_id'])) {
    $nombre = trim($_POST['nombre']);
    $ano_id = intval($_POST['ano_id']);
    if ($nombre !== '' && $ano_id > 0) {
        $stmt = $conn->prepare("INSERT INTO divisiones (nombre, ano_id) VALUES (?, ?)");
        if (!$stmt) {
            die("Error en prepare: " . $conn->error);
        }
        $stmt->bind_param('si', $nombre, $ano_id);
        if (!$stmt->execute()) {
            $error = "Error al agregar la división: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Obtener lista de años para el dropdown - PRIMERO obtenemos los años
$anos_query = $conn->query("SELECT * FROM anos ORDER BY id ASC");
if (!$anos_query) {
    die("Error en consulta de años: " . $conn->error);
}
$anos = $anos_query->fetch_all(MYSQLI_ASSOC);

// Obtener lista de divisiones con información del año
$result = $conn->query("
    SELECT d.*, a.nombre as ano_nombre 
    FROM divisiones d 
    LEFT JOIN anos a ON d.ano_id = a.id 
    ORDER BY d.id ASC
");
if (!$result) {
    die("Error en consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Divisiones</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<a href="../inicio.php">Volver</a>
<h2>Divisiones</h2>

<?php if (isset($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="divisiones.php">
    <input type="text" name="nombre" placeholder="Nombre de la división" required>
    <select name="ano_id" required>
        <option value="">Seleccionar año</option>
        <?php foreach ($anos as $ano): ?>
            <option value="<?= $ano['id'] ?>">
                <?= htmlspecialchars($ano['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Agregar División</button>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Año</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
<?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['ano_nombre']) ?></td>
            <td>
                <a href="?borrar=<?= $row['id'] ?>" onclick="return confirm('¿Eliminar esta división?')">Eliminar</a>
            </td>
        </tr>
<?php endwhile; ?>
    </tbody>
</table>

</body>
</html>