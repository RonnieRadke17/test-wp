<?php
// Verificar si se recibió un ID para editar
if (isset($_GET['id'])) {
    $keyword = get_keywords_by_id($_GET['id']); // Obtener datos del día por ID
}

// Manejar la solicitud de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_keywords'])) {
    update_keywords($_POST['id'], $_POST['name']); // Actualizar el registro
    wp_redirect('?crud_action=list_keywords'); // Redirigir a la lista
    exit;
}
?>

<h2>Editar Día</h2>
<form method="POST">
    <input type="hidden" name="id" value="<?php echo $keyword['id']; ?>"> <!-- ID oculto -->
    <label for="name">Nombre del Día:</label><br>
    <input type="text" id="name" name="name" value="<?php echo esc_attr($keyword['name']); ?>" required><br><br>
    <button type="submit" name="update_keywords">Actualizar</button>
</form>
<a href="?crud_action=list_keywords">Volver a la Lista</a>
