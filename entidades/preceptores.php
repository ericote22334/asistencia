<?php
include '../db.php';
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}

// Eliminar preceptor si se recibe el parámetro 'borrar'
if (isset($_GET['borrar'])) {
    $id = intval($_GET['borrar']);
    $conn->query("DELETE FROM preceptores WHERE id=$id");
    header("Location: preceptores.php");
    exit;
}

// Insertar nuevo preceptor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = intval($_POST['usuario_id']);
    $stmt = $conn->prepare("INSERT INTO preceptores (usuario_id) VALUES (?)");
    $stmt->bind_param("i", $usuario_id);
    if (!$stmt->execute()) {
        $error = "Error al insertar preceptor: " . $stmt->error;
    }
    $stmt->close();
}

// Obtener lista de preceptores con nombre desde usuarios
$sql = "SELECT p.id, u.nombre_completo 
        FROM preceptores p 
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.id ASC";

$result = $conn->query($sql);
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Preceptores</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="container">
<a href="../inicio.php" class="return-link">Volver</a>
<h2>Preceptores</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="preceptores.php">
    <input type="number" name="usuario_id" placeholder="ID Usuario" required>
    <button type="submit">Agregar Preceptor</button>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre Completo</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
            <td><a href="?borrar=<?= $row['id'] ?>" onclick="return confirm('¿Eliminar este preceptor?')">Eliminar</a></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>


</div>
</body>
</html>
