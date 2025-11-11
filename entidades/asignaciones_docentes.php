<?php
include '../db.php';
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}

// Eliminar asignación si llega parámetro borrar
if (isset($_GET['borrar'])) {
    $id = intval($_GET['borrar']);
    $conn->query("DELETE FROM asignaciones_docentes WHERE id=$id");
    header("Location: asignaciones_docentes.php");
    exit;
}

// Insertar nueva asignación desde formulario
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $docente_id = intval($_POST['docente_id']);
    $materia_id = intval($_POST['materia_id']);
    $division_id = intval($_POST['division_id']);
    $periodo = trim($_POST['periodo']);

    if (!$docente_id || !$materia_id || !$division_id || $periodo == '') {
        $error = "Por favor completa todos los campos.";
    } else {
        $stmt = $conn->prepare("INSERT INTO asignaciones_docentes (docente_id, materia_id, division_id, periodo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $docente_id, $materia_id, $division_id, $periodo);
        if (!$stmt->execute()) {
            $error = "Error al insertar asignación: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Consulta con JOIN para mostrar datos
$sql = "SELECT ad.id, u.nombre_completo as docente, m.nombre as materia, d.nombre as division, ad.periodo
        FROM asignaciones_docentes ad
        JOIN docentes doc ON ad.docente_id = doc.id
        JOIN usuarios u ON doc.usuario_id = u.id
        JOIN materias m ON ad.materia_id = m.id
        JOIN divisiones d ON ad.division_id = d.id
        ORDER BY ad.id DESC";

$result = $conn->query($sql);
if (!$result) {
    die("Error en la consulta SQL: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignaciones Docentes</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="container">
<a href="../inicio.php" class="return-link">Volver</a>
<h2>Asignaciones Docentes</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="asignaciones_docentes.php">
    <input type="number" name="docente_id" placeholder="ID Docente" required>
    <input type="number" name="materia_id" placeholder="ID Materia" required>
    <input type="number" name="division_id" placeholder="ID División" required>
    <input type="text" name="periodo" placeholder="Periodo" required>
    <button type="submit">Agregar Asignación</button>
</form>

<table>
<tr><th>ID</th><th>Docente</th><th>Materia</th><th>División</th><th>Periodo</th><th>Acción</th></tr>
<?php while($row = $result->fetch_assoc()){ ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['docente']) ?></td>
    <td><?= htmlspecialchars($row['materia']) ?></td>
    <td><?= htmlspecialchars($row['division']) ?></td>
    <td><?= htmlspecialchars($row['periodo']) ?></td>
    <td><a href="?borrar=<?= $row['id']?>" onclick="return confirm('¿Eliminar esta asignación?')">Eliminar</a></td>
</tr>
<?php } ?>
</table>


</div>
</body>
</html>
