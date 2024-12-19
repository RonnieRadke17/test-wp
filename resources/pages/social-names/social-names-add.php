<?php
// Manejar la solicitud de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_social_name'])) {
    // Validar el nonce para prevenir CSRF
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'add_social_name_nonce')) {
        wp_die('Error de validación. Inténtalo nuevamente.');
    }

    // Intentar agregar el nuevo nombre social
    $result = add_social_name($_POST['name']);

    // Comprobar el resultado
    if (is_wp_error($result)) {
        echo '<div style="color: red;">' . esc_html($result->get_error_message()) . '</div>';
    } else {
        // Redirigir después de guardar
        wp_safe_redirect('?crud_action=list_social_name');
        exit;
    }
}
?>

<h2>Agregar Nuevo Nombre Social</h2>
<form method="POST">
    <?php wp_nonce_field('add_social_name_nonce'); // Generar nonce para validación ?>
    <label for="name">Nombre:</label><br>
    <input type="text" id="name" name="name" required minlength="5" maxlength="45"><br><br>
    <button type="submit" name="add_social_name">Guardar</button>
</form>
<a href="?crud_action=list_social_name">Volver a la Lista</a>
