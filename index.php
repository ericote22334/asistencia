<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $pass = $_POST['contrasena'];
    $query = "SELECT * FROM usuarios WHERE nombre_usuario=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        if (password_verify($pass, $user['hash_contrasena'])) {
            $_SESSION['id_usuario'] = $user['id'];
            $_SESSION['rol'] = $user['rol'];
            header("Location: inicio.php");
            exit;
        }
    }
    $error = "Usuario o contraseña inválidos";
}
?>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h2>Login</h2>
<form method="POST">
    <input name="usuario" placeholder="Usuario">
    <input name="contrasena" type="password" placeholder="Contraseña">
    <button type="submit">Acceder</button>
</form>
<?php if (isset($error)) echo "<p>$error</p>"; ?>
<p>¿No tienes cuenta? <a href="registrar.php">Regístrate aquí</a></p>
</body>
</html>
