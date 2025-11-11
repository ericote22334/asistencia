<?php
include '../db.php';
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}

// Eliminar registro si llega parámetro borrar
if (isset($_GET['borrar'])) {
    $id = intval($_GET['borrar']);
    $conn->query("DELETE FROM asignaciones_preceptores WHERE id=$id");
    header("Location: asignaciones_preceptores.php");
    exit;
}

$error = '';
// Insertar nueva asignación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $preceptor_id = intval($_POST['preceptor_id']);
    $division_id = intval($_POST['division_id']);
    $periodo = trim($_POST['periodo']);

    if (!$preceptor_id || !$division_id || $periodo === '') {
        $error = "Completa todos los campos.";
    } else {
        $stmt = $conn->prepare("INSERT INTO asignaciones_preceptores (preceptor_id, division_id, periodo) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $preceptor_id, $division_id, $periodo);
        if (!$stmt->execute()) {
            $error = "Error al insertar asignación: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Obtener datos para selects
$preceptores = $conn->query("SELECT p.id, u.nombre_completo FROM preceptores p JOIN usuarios u ON p.usuario_id = u.id ORDER BY u.nombre_completo");
$divisiones = $conn->query("SELECT id, nombre FROM divisiones ORDER BY nombre");

// Consulta para mostrar asignaciones existentes
$sql = "SELECT ap.id, u.nombre_completo as preceptor, d.nombre as division, ap.periodo
        FROM asignaciones_preceptores ap
        JOIN preceptores p ON ap.preceptor_id = p.id
        JOIN usuarios u ON p.usuario_id = u.id
        JOIN divisiones d ON ap.division_id = d.id
        ORDER BY ap.id DESC";

$result = $conn->query($sql);
if (!$result) {
    die("Error en la consulta SQL: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Asignaciones Preceptores</title>
    <link rel="stylesheet" href="../styles.css" />
</head>
<body>
<div class="container">
    <a href="../inicio.php" class="return-link">Volver</a>
    <h2>Asignaciones Preceptores</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="asignaciones_preceptores.php">
        <label>Preceptor:
            <select name="preceptor_id" required>
                <option value="" disabled selected>Seleccione un preceptor</option>
                <?php while ($row = $preceptores->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nombre_completo']) ?></option>
                <?php endwhile; ?>
            </select>
        </label>

        <label>División:
            <select name="division_id" required>
                <option value="" disabled selected>Seleccione una división</option>
                <?php while ($row = $divisiones->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </label>

        <label>Periodo:
            <input type="text" name="periodo" placeholder="Ej: 2025" required />
        </label>

        <button type="submit">Agregar Asignación</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Preceptor</th>
            <th>División</th>
            <th>Periodo</th>
            <th>Acción</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['preceptor']) ?></td>
                <td><?= htmlspecialchars($row['division']) ?></td>
                <td><?= htmlspecialchars($row['periodo']) ?></td>
                <td>
                    <a href="?borrar=<?= $row['id'] ?>" onclick="return confirm('¿Eliminar esta asignación?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

  
</div>
</body>
</html>
