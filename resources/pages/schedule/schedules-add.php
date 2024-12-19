<?php
// Manejar la solicitud de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    // Validar el nonce para prevenir CSRF
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'add_schedule_nonce')) {
        wp_die('Error de validación. Inténtalo nuevamente.');
    }

    // Intentar agregar el nuevo horario
    $result = add_schedule($_POST['day_id'], $_POST['start_time'], $_POST['end_time']);

    // Comprobar el resultado
    if (is_wp_error($result)) {
        echo '<div style="color: red;">' . esc_html($result->get_error_message()) . '</div>';
    } else {
        // Redirigir después de guardar
        wp_safe_redirect('?crud_action=list_schedules');
        exit;
    }
}
?>

<h2>Agregar Nuevo Horario</h2>
<form method="POST">
    <?php wp_nonce_field('add_schedule_nonce'); // Generar nonce para validación ?>
    
    <label for="day_id">Día:</label><br>
    <select id="day_id" name="day_id" required>
        <?php foreach (get_all_days() as $day): ?>
            <option value="<?php echo esc_attr($day['id']); ?>">
                <?php echo esc_html($day['name']); ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="start_time">Hora de Inicio:</label><br>
    <input type="time" id="start_time" name="start_time" required><br><br>

    <label for="end_time">Hora de Fin:</label><br>
    <input type="time" id="end_time" name="end_time" required><br><br>

    <button type="submit" name="add_schedule">Guardar</button>
</form>
<a href="?crud_action=list_schedules">Volver a la Lista</a>
