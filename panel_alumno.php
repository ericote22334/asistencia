<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}

if (($_SESSION['rol'] ?? '') !== 'alumno') {
    header('Location: index.php');
    exit;
}

$nombre = $_SESSION['nombre'] ?? 'Alumno';
?>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Panel de Alumno</title>
</head>
<body>
<div class="container">
    <h1>Hola, <?= htmlspecialchars($nombre) ?></h1>
    <p>Desde aqui puedes consultar tu informacion basica.</p>
    <ul>
        <li><a href="entidades/asistencias.php">Ver asistencias registradas</a></li>
        <li><a href="entidades/registrar_entrada.php">Registrar nueva asistencia (si corresponde)</a></li>
    </ul>
    <a href="logout.php">Cerrar sesion</a>
</div>
</body>
</html>
