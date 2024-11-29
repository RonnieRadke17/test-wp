<?php
// Verificar si se recibió un ID para editar
if (isset($_GET['id'])) {
    $day = get_day_by_id($_GET['id']); // Obtener datos del día por ID
}

// Manejar la solicitud de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_day'])) {
    update_day($_POST['id'], $_POST['name']); // Actualizar el registro
    wp_redirect('?crud_action=list_days'); // Redirigir a la lista
    exit;
}
?>

<h2>Editar Día</h2>
<form method="POST">
    <input type="hidden" name="id" value="<?php echo $day['id']; ?>"> <!-- ID oculto -->
    <label for="name">Nombre del Día:</label><br>
    <input type="text" id="name" name="name" value="<?php echo esc_attr($day['name']); ?>" required><br><br>
    <button type="submit" name="update_day">Actualizar</button>
</form>
<a href="?crud_action=list_days">Volver a la Lista</a>
