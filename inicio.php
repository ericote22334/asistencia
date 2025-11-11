<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php"); exit;
}
?>
<html>
<head><link rel="stylesheet" href="styles.css"></head>
<body>
<h1>Panel principal</h1>
<nav>
<ul>
    <li><a href="entidades/usuarios.php">Usuarios</a></li>
    <li><a href="entidades/alumnos.php">Alumnos</a></li>
    <li><a href="entidades/docentes.php">Docentes</a></li>
    <li><a href="entidades/materias.php">Materias</a></li>
    <li><a href="entidades/divisiones.php">Divisiones</a></li>
    <li><a href="entidades/preceptores.php">Preceptores</a></li>
    <li><a href="entidades/inscripciones.php">Inscripciones</a></li>
    <li><a href="entidades/horarios.php">Horarios</a></li>
    <li><a href="entidades/asignaciones_docentes.php">Asignaciones Docentes</a></li>
    <li><a href="entidades/asignaciones_preceptores.php">Asignaciones Preceptores</a></li>
    <li><a href="entidades/asistencias.php">Asistencias</a></li>
    <li><a href="entidades/registrar_entrada.php">Registrar Asistencias</a></li>
</ul>
</nav>
<a href='logout.php'>Salir</a>
</body>
</html>
