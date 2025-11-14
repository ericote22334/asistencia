<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}

if (($_SESSION['rol'] ?? '') !== 'preceptor') {
    header('Location: index.php');
    exit;
}

$nombre = $_SESSION['nombre'] ?? 'Preceptor';
?>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Panel de Preceptor</title>
</head>
<body>
<div class="container">
    <h1>Hola <?= htmlspecialchars($nombre) ?></h1>
    <p>Selecciona la accion que necesitas.</p>
    <ul>
        <li><a href="entidades/asistencias.php">Listar asistencias</a></li>
        <li><a href="entidades/registrar_entrada.php">Registrar asistencia por RFID</a></li>
        <li><a href="entidades/divisiones.php">Gestionar divisiones</a></li>
    </ul>
    <a href="logout.php">Cerrar sesion</a>
</div>
</body>
</html>
