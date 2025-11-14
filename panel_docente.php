<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}

if (($_SESSION['rol'] ?? '') !== 'docente') {
    header('Location: index.php');
    exit;
}

$nombre = $_SESSION['nombre'] ?? 'Docente';
?>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Panel de Docente</title>
</head>
<body>
<div class="container">
    <h1>Bienvenido <?= htmlspecialchars($nombre) ?></h1>
    <p>Utiliza estos accesos rapidos.</p>
    <ul>
        <li><a href="entidades/horarios.php">Consultar horarios</a></li>
        <li><a href="entidades/asistencias.php">Revisar asistencias de alumnos</a></li>
    </ul>
    <a href="logout.php">Cerrar sesion</a>
</div>
</body>
</html>
