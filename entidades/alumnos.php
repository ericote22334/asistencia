<?php
include '../db.php';
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}

// Eliminar alumno si se recibe el parámetro 'borrar'
if (isset($_GET['borrar'])) {
    $id = intval($_GET['borrar']);
    $conn->query("DELETE FROM alumnos WHERE id=$id");
    header("Location: alumnos.php");
    exit;
}

$error = '';
// Insertar nuevo alumno
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = intval($_POST['usuario_id']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];

    if (!$usuario_id || !$fecha_nacimiento || !$genero) {
        $error = "Completa todos los campos.";
    } else {
        // Validar que no exista alumno con ese usuario_id
        $check = $conn->prepare("SELECT id FROM alumnos WHERE usuario_id = ?");
        $check->bind_param("i", $usuario_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error = "El alumno ya está registrado.";
        } else {
            $stmt = $conn->prepare("INSERT INTO alumnos (usuario_id, fecha_nacimiento, genero) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $usuario_id, $fecha_nacimiento, $genero);
            if (!$stmt->execute()) {
                $error = "Error al insertar alumno: " . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}

// Consulta para obtener alumnos con nombre desde tabla usuarios
$sql = "SELECT alumnos.id, usuarios.nombre_completo AS nombre_alumno, alumnos.fecha_nacimiento, alumnos.genero
        FROM alumnos
        JOIN usuarios ON alumnos.usuario_id = usuarios.id";

$alumnos = $conn->query($sql);

// Obtener usuarios que pueden ser alumnos (filtrar así si quieres)
$usuarios_result = $conn->query("SELECT id, nombre_completo FROM usuarios WHERE rol='alumno' ORDER BY nombre_completo");
?>

<html>
<head><link rel="stylesheet" href="../styles.css"></head>
<body>
<div class="container">
    <a href="../inicio.php">Volver</a>
<h2>Alumnos</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- Formulario para agregar alumno -->
<form method="POST" action="alumnos.php">
    <label>Usuario:
        <select name="usuario_id" required>
            <option value="" disabled selected>Seleccione un usuario</option>
            <?php while ($row = $usuarios_result->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nombre_completo']) ?></option>
            <?php endwhile; ?>
        </select>
    </label>
    <label>Fecha de Nacimiento:
        <input type="date" name="fecha_nacimiento" required>
    </label>
    <label>Género:
        <select name="genero" required>
            <option value="" disabled selected>Seleccione género</option>
            <option value="Masculino">m</option>
            <option value="Femenino">f</option>
    
        </select>
    </label>
    <button type="submit">Agregar Alumno</button>
</form>

<table>
<tr><th>ID</th><th>Nombre</th><th>Fecha Nacimiento</th><th>Género</th><th>Acción</th></tr>
<?php while ($row = $alumnos->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['nombre_alumno']) ?></td>
        <td><?= $row['fecha_nacimiento'] ?></td>
        <td><?= htmlspecialchars($row['genero']) ?></td>
        <td>
            <a href="?borrar=<?= $row['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este alumno?')">Eliminar</a>
        </td>
    </tr>
<?php } ?>
</table>


</div>
</body>
</html>
