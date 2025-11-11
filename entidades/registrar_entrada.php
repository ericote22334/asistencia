<?php
include "../db.php";

// Verificar conexión
if (!$conn) {
    die("<h2 style='color: red;'>Error de conexión a la base de datos</h2>");
}

// Solo procesar si se envió RFID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['rfid'])) {
    $uid = trim($_POST['rfid']);
    $nombre = "";
    
    // Buscar usuario
    $stmt = $conn->prepare("SELECT nombre_completo FROM usuarios WHERE rfid = ?");
    if ($stmt) {
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $nombre = $row['nombre_completo'];
            
            // Insertar asistencia - CORREGIDO: solo un parámetro
            $insert = $conn->prepare("INSERT INTO asistencias (rfid, estado, registrado_en,alumno_id,horario_id,division_id,fecha,registrado_por) VALUES (?, 'presente', NOW())");
            if ($insert) {
                $insert->bind_param("s", $uid); // Solo el UID
                if ($insert->execute()) {
                    echo "<h2 style='color: green;'>✅ $nombre registrado con éxito</h2>";
                } else {
                    echo "<h2 style='color: red;'>❌ Error al guardar: " . $insert->error . "</h2>";
                }
                $insert->close();
            } else {
                echo "<h2 style='color: red;'>❌ Error en consulta INSERT: " . $conn->error . "</h2>";
            }
        } else {
            echo "<h2 style='color: red;'>❌ RFID no encontrado</h2>";
        }
        $stmt->close();
    } else {
        echo "<h2 style='color: red;'>❌ Error en consulta SELECT: " . $conn->error . "</h2>";
    }
} else {
    echo "<h2>Esperando RFID...</h2>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registro de Asistencia</title>
    <link rel="stylesheet" href="../styles.css" />
</head>
<body>
    <div class="container">
        <h2>Registro de Asistencia por RFID</h2>
        <form method="POST">
            <input type="text" name="rfid" placeholder="Introduce RFID" required autofocus />
            <button type="submit">Registrar</button>
        </form>
        <a href="../inicio.php" class="return-link">Volver</a>
    </div>
</body>
</html>