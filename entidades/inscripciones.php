<?php
include '../db.php';
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}

// Insertar nueva inscripción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alumno_id = intval($_POST['alumno_id']);
    $division_id = intval($_POST['division_id']);
    $inscripto_en = $_POST['inscripto_en'];
    $abandono_en = $_POST['abandono_en'] ?: NULL;

    $stmt = $conn->prepare("INSERT INTO inscripciones (alumno_id, division_id, inscripto_en, abandono_en) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $alumno_id, $division_id, $inscripto_en, $abandono_en);
    if (!$stmt->execute()) {
        $error = "Error al insertar inscripción: " . $stmt->error;
    }
    $stmt->close();
}

// Listar inscripciones con datos relacionados
$sql = "SELECT i.id, u.nombre_completo AS alumno, d.nombre AS division, i.inscripto_en, i.abandono_en
        FROM inscripciones i
        JOIN alumnos a ON i.alumno_id = a.id
        JOIN usuarios u ON a.usuario_id = u.id
        JOIN divisiones d ON i.division_id = d.id
        ORDER BY i.id DESC";

$result = $conn->query($sql);
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Inscripciones</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<a href="../inicio.php" class="return-link">Volver</a>
<div class="container">
<h2>Inscripciones</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="inscripciones.php">
    <input type="number" name="alumno_id" placeholder="ID Alumno" required>
    <input type="number" name="division_id" placeholder="ID División" required>
    <label>Fecha Inscripción: <input type="date" name="inscripto_en" required></label>
    <label>Fecha Abandono: <input type="date" name="abandono_en"></label>
    <button type="submit">Agregar Inscripción</button>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Alumno</th>
            <th>División</th>
            <th>Incripción</th>
            <th>Abandono</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['alumno']) ?></td>
            <td><?= htmlspecialchars($row['division']) ?></td>
            <td><?= $row['inscripto_en'] ?></td>
            <td><?= $row['abandono_en'] ?: '-' ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>


</div>
</body>
</html>
