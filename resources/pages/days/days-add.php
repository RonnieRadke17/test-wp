<?php
// Manejar la solicitud de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_day'])) {
    
    // Intentar agregar el nuevo día
    $result = add_day($_POST['name']);

    // Comprobar el resultado de la inserción
    if (is_wp_error($result)) {
        echo '<div style="color: red;">' . esc_html($result->get_error_message()) . '</div>';
    } else {
        // Redirigir después de agregar
        wp_safe_redirect('?crud_action=list_days');
        exit;
    }
}
?>


<h2>Agregar Nuevo Día</h2>
<form method="POST">
    <label for="name">Nombre del Día:</label><br>
    <input type="text" id="name" name="name" required><br><br>
    <button type="submit" name="add_day">Guardar</button>
</form>
<a href="?crud_action=list_days">Volver a la Lista</a>
