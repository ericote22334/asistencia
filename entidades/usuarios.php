<?php
include '../db.php';
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}

// Eliminar usuario si llega parámetro borrar
if (isset($_GET['borrar'])) {
    $id_borrar = intval($_GET['borrar']);
    $conn->query("DELETE FROM usuarios WHERE id = $id_borrar");
    header("Location: usuarios.php"); // asegúrate que este archivo se llame usuarios.php
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $correo = trim($_POST['correo']);
    $nombre = trim($_POST['nombre']);
    $rol = trim($_POST['rol']);
    $rfid = trim($_POST['rfid']);

    if ($usuario == '' || $correo == '' || $nombre == '' || $rol == '' || $rfid == '') {
        echo "<p style='color:red'>Completa todos los campos.</p>";
    } else {
        $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? OR rfid = ?");
        $stmt_check->bind_param("ss", $usuario, $rfid);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            echo "<p style='color:red'>El usuario o RFID ya existe.</p>";
        } else {
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, hash_contrasena, correo, nombre_completo, rol, creado_en, rfid) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
            $stmt->bind_param("ssssss", $usuario, $contrasena, $correo, $nombre, $rol, $rfid);
            if ($stmt->execute()) {
                echo "<p style='color:green'>Usuario registrado con éxito.</p>";
            } else {
                echo "<p style='color:red'>Error al registrar usuario: " . $stmt->error . "</p>";
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}

$result = $conn->query("SELECT * FROM usuarios");
?>

<html>
<head><link rel="stylesheet" href="../styles.css"></head>
<body>
<a href="../inicio.php">Volver</a>
<h2>Usuarios</h2>
<form method="POST">
    <input name="usuario" placeholder="Usuario" required>
    <input name="contrasena" type="password" placeholder="Contraseña" required>
    <input name="correo" placeholder="Correo" required>
    <input name="nombre" placeholder="Nombre completo" required>
    <select name="rol" required>
        <option value="" disabled selected>Selecciona un rol</option>
        <option value="alumno">alumno</option>
        <option value="preceptor">preceptor</option>
        <option value="administrador">administrador</option>
        <option value="docente">docente</option>
    </select>
    <input name="rfid" placeholder="rfid (SOLO ALUMNOS)" required>
    <button type="submit">Agregar</button>
</form>

<table>
<tr><th>ID</th><th>Usuario</th><th>Correo</th><th>Nombre</th><th>Rol</th><th>RFID</th><th>Acción</th></tr>
<?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['nombre_usuario']) ?></td>
    <td><?= htmlspecialchars($row['correo']) ?></td>
    <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
    <td><?= htmlspecialchars($row['rol']) ?></td>
    <td><?= htmlspecialchars($row['rfid']) ?></td>
    <td><a href="?borrar=<?= $row['id'] ?>" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a></td>
    </tr>
<?php } ?>
</table>

</body>
</html>
