<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
session_start();

$error = '';
$exito = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $pass = $_POST['password'];
    $correo = trim($_POST['correo']);
    $nombre_completo = trim($_POST['nombre_completo']);
    $rol = trim($_POST['rol']);

    if (empty($usuario) || empty($pass) || empty($correo) || empty($nombre_completo) || empty($rol)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Verificar si usuario existe
        $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
        if (!$stmt_check) {
            die("Error en prepare (check): " . $conn->error);
        }
        $stmt_check->bind_param("s", $usuario);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $error = "El usuario ya existe.";
        } else {
            // Insertar nuevo usuario
            $stmt_insert = $conn->prepare("INSERT INTO usuarios (nombre_usuario, hash_contrasena, correo, nombre_completo, rol, creado_en) VALUES (?, ?, ?, ?, ?, NOW())");
            if (!$stmt_insert) {
                die("Error en prepare (insert): " . $conn->error);
            }
            $hash_contrasena = password_hash($pass, PASSWORD_DEFAULT);
            $stmt_insert->bind_param("sssss", $usuario, $hash_contrasena, $correo, $nombre_completo, $rol);

            if ($stmt_insert->execute()) {
                $exito = "Usuario registrado correctamente. <a href='index.php'>Ir a login</a>";
            } else {
                $error = "Error al registrar usuario.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Usuario Nuevo</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Registrar Usuario Nuevo</h2>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($exito): ?>
        <p style="color:green;"><?= $exito ?></p>
    <?php endif; ?>
    <form action="registrar.php" method="POST">
        <input type="text" name="usuario" placeholder="Nombre de usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <input type="email" name="correo" placeholder="Correo electrónico" required>
        <input type="text" name="nombre_completo" placeholder="Nombre Completo" required>
       <select name="rol" required>
    <option value="" disabled selected>Selecciona un rol</option>
    <option value="administrador">administrador</option>
    <option value="alumno">alumno</option>
    <option value="docente">docente</option>
    <option value="preceptor">preceptor</option>
</select>

        <button type="submit">Registrar</button>
    </form>
    <p><a href="index.php">Volver a login</a></p>
</body>
</html>
