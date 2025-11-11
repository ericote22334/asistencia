<?php
include '../db.php';
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}

// Eliminar docente si se recibe el parámetro 'borrar'
if (isset($_GET['borrar'])) {
    $id = intval($_GET['borrar']);
    $conn->query("DELETE FROM docentes WHERE id = $id");
    header("Location: docentes.php");
    exit;
}

$error = '';
// Insertar nuevo docente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = intval($_POST['usuario_id']);

    if ($usuario_id <= 0) {
        $error = "Por favor selecciona un usuario válido.";
    } else {
        // Verificar que el usuario no sea ya un docente
        $check = $conn->prepare("SELECT id FROM docentes WHERE usuario_id = ?");
        $check->bind_param("i", $usuario_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error = "Este usuario ya está asignado como docente.";
        } else {
            $stmt = $conn->prepare("INSERT INTO docentes (usuario_id) VALUES (?)");
            $stmt->bind_param("i", $usuario_id);
            if (!$stmt->execute()) {
                $error = "Error al insertar docente: " . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}

// Obtener usuarios para selección
$usuarios = $conn->query("SELECT id, nombre_completo FROM usuarios ORDER BY nombre_completo");

// Consulta para obtener docentes con nombre desde tabla usuarios
$sql = "SELECT docentes.id, usuarios.nombre_completo AS nombre_docente, docentes.usuario_id
        FROM docentes
        JOIN usuarios ON docentes.usuario_id = usuarios.id";

$docentes = $conn->query($sql);
?>

<html>
<head>
    <link rel="stylesheet" href="../styles.css">
    <title>Docentes</title>
</head>
<body>
<div class="container">
    <a href="../inicio.php">Volver</a>
    <h2>Docentes</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="docentes.php">
        <label>Selecciona Usuario para Docente:
            <select name="usuario_id" required>
                <option value="" disabled selected>Selecciona un usuario</option>
                <?php while ($row = $usuarios->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nombre_completo']) ?></option>
                <?php endwhile; ?>
            </select>
        </label>
        <button type="submit">Agregar Docente</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Usuario ID</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $docentes->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nombre_docente']) ?></td>
                <td><?= $row['usuario_id'] ?></td>
                <td>
                    <a href="?borrar=<?= $row['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este docente?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    
</div>
</body>
</html>
