<?php
// Verificar si se recibió un ID válido para editar
if (isset($_GET['id'])) {
    $social_name = get_social_name_by_id($_GET['id']); // Obtener los datos del nombre social
    if (!$social_name) {
        wp_die('El nombre social especificado no existe.'); // Mostrar error si no se encuentra
    }
}

// Manejar la solicitud de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_social_name'])) {
    // Validar el nonce para prevenir CSRF
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update_social_name_nonce')) {
        wp_die('Error de validación. Inténtalo nuevamente.');
    }

    // Intentar actualizar el nombre social
    $result = update_social_name($_POST['id'], $_POST['name']);

    // Comprobar el resultado
    if (is_wp_error($result)) {
        echo '<div style="color: red;">' . esc_html($result->get_error_message()) . '</div>';
    } else {
        // Redirigir a la lista después de actualizar
        wp_safe_redirect('?crud_action=list_social_name');
        exit;
    }
}
?>

<h2>Editar Nombre Social</h2>
<form method="POST">
    <?php wp_nonce_field('update_social_name_nonce'); // Generar nonce para validación ?>
    <input type="hidden" name="id" value="<?php echo esc_attr($social_name['id']); ?>"> <!-- ID oculto -->
    <label for="name">Nombre:</label><br>
    <input type="text" id="name" name="name" value="<?php echo esc_attr($social_name['name']); ?>" required minlength="5" maxlength="45"><br><br>
    <button type="submit" name="update_social_name">Actualizar</button>
</form>
<a href="?crud_action=list_social_name">Volver a la Lista</a>
