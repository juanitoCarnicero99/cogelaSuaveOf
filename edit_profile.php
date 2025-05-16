<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];
$message = '';

// Obtener datos actuales del usuario
$result = $mysqli->query("SELECT * FROM usuarios WHERE id = $user_id");
$user = $result->fetch_assoc();

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $carrera = $_POST['carrera'];
    $descripcion = $_POST['descripcion_personal'];
    $email = $_POST['email'];

    // Validación backend: solo letras y espacios
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$/u', $nombre) || !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$/u', $apellido)) {
        $message = "<span style='color:#c0392b;'>El nombre y el apellido solo pueden contener letras y espacios.</span>";
    } else {
        $query = "UPDATE usuarios SET 
                  nombre = ?, 
                  apellido = ?, 
                  fecha_nacimiento = ?, 
                  carrera = ?, 
                  descripcion_personal = ?, 
                  email = ? 
                  WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssssssi", $nombre, $apellido, $fecha_nacimiento, $carrera, $descripcion, $email, $user_id);
        if ($stmt->execute()) {
            $message = "Perfil actualizado exitosamente";
            // Actualizar los datos del usuario
            $result = $mysqli->query("SELECT * FROM usuarios WHERE id = $user_id");
            $user = $result->fetch_assoc();
        } else {
            $message = "Error al actualizar el perfil";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Cogela Suave</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container profile-container">
        <h2>Editar Perfil</h2>
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" class="profile-form" onsubmit="return validarNombreApellido();">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user['nombre'] ?? ''); ?>" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$" title="Solo letras y espacios">
            </div>

            <div class="form-group">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($user['apellido'] ?? ''); ?>" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$" title="Solo letras y espacios">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo $user['fecha_nacimiento'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="carrera">Carrera</label>
                <input type="text" id="carrera" name="carrera" value="<?php echo htmlspecialchars($user['carrera'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="descripcion_personal">Descripción Personal</label>
                <textarea id="descripcion_personal" name="descripcion_personal" rows="4"><?php echo htmlspecialchars($user['descripcion_personal'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="save-button">Guardar Cambios</button>
        </form>
    </div>
    <script>
    function validarNombreApellido() {
        const nombre = document.getElementById('nombre').value.trim();
        const apellido = document.getElementById('apellido').value.trim();
        const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$/;
        if (!regex.test(nombre) || !regex.test(apellido)) {
            alert('El nombre y el apellido solo pueden contener letras y espacios.');
            return false;
        }
        return true;
    }
    </script>
</body>
</html> 