<?php
include 'db.php';
session_start();

function obtenerRutaPorRol(?string $rol): string
{
    switch ($rol) {
        case 'alumno':
            return 'panel_alumno.php';
        case 'docente':
            return 'panel_docente.php';
        case 'preceptor':
            return 'panel_preceptor.php';
        case 'administrador':
        default:
            return 'inicio.php';
    }
}

if (isset($_SESSION['id_usuario'], $_SESSION['rol'])) {
    header('Location: ' . obtenerRutaPorRol($_SESSION['rol']));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $pass = $_POST['contrasena'] ?? '';

    if ($usuario === '' || $pass === '') {
        $error = 'Ingresa usuario y contrasena.';
    } else {
        $query = 'SELECT * FROM usuarios WHERE nombre_usuario = ?';
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($user = $result->fetch_assoc()) {
                if (password_verify($pass, $user['hash_contrasena'])) {
                    $_SESSION['id_usuario'] = $user['id'];
                    $_SESSION['rol'] = $user['rol'];
                    $_SESSION['nombre'] = $user['nombre_completo'];
                    header('Location: ' . obtenerRutaPorRol($user['rol']));
                    exit;
                }
            }
            $stmt->close();
        }
        $error = 'Usuario o contrasena invalidos.';
    }
}
?>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Iniciar sesion</title>
</head>
<body>
<h2>Iniciar sesion</h2>
<form method="POST">
    <input name="usuario" placeholder="Usuario" required>
    <input name="contrasena" type="password" placeholder="Contrasena" required>
    <button type="submit">Acceder</button>
</form>
<?php if ($error !== '') { echo '<p>' . htmlspecialchars($error) . '</p>'; } ?>
<p>No tienes cuenta? <a href="registrar.php">Registrate aqui</a></p>
</body>
</html>
