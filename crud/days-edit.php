<?php
// Verificar si se recibió un ID para editar
if (isset($_GET['id'])) {
    $day = get_day_by_id($_GET['id']); // Obtener datos del día por ID

    // Si no se encuentra el día, mostrar un mensaje de error
    if (!$day) {
        wp_die('El día solicitado no existe.');
    }
}

// Manejar la solicitud de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_day'])) {
    // Validar el nonce para prevenir CSRF
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update_day_nonce')) {
        wp_die('Error de validación. Inténtalo nuevamente.');
    }

    // Intentar actualizar el día
    $result = update_day($_POST['id'], $_POST['name']);

    // Comprobar el resultado
    if (is_wp_error($result)) {
        echo '<div style="color: red;">' . esc_html($result->get_error_message()) . '</div>';
    } else {
        // Redirigir a la lista después de actualizar
        wp_safe_redirect('?crud_action=list_days');
        exit;
    }
}
?>

<h2>Editar Día</h2>
<form method="POST">
    <?php wp_nonce_field('update_day_nonce'); // Generar nonce para validación ?>
    <input type="hidden" name="id" value="<?php echo esc_attr($day['id']); ?>"> <!-- ID oculto -->
    <label for="name">Nombre del Día:</label><br>
    <input type="text" id="name" name="name" value="<?php echo esc_attr($day['name']); ?>" required minlength="5" maxlength="45"><br><br>
    <button type="submit" name="update_day">Actualizar</button>
</form>
<a href="?crud_action=list_days">Volver a la Lista</a>
