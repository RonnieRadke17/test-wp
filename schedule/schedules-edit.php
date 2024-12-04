<?php
// Verificar si se recibió un ID válido para editar
if (isset($_GET['id'])) {
    $schedule = get_schedule_by_id($_GET['id']); // Obtener los datos del horario
    if (!$schedule) {
        wp_die('El horario especificado no existe.'); // Mostrar error si no se encuentra el horario
    }
}

// Obtener todos los días para el selector
$days = get_all_days(); // Asegúrate de que esta función ya esté definida en functions.php

// Manejar la solicitud de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_schedule'])) {
    // Validar el nonce para prevenir CSRF
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update_schedule_nonce')) {
        wp_die('Error de validación. Inténtalo nuevamente.');
    }

    // Intentar actualizar el horario
    $result = update_schedule($_POST['id'], $_POST['day_id'], $_POST['start_time'], $_POST['end_time']);

    // Comprobar el resultado
    if (is_wp_error($result)) {
        echo '<div style="color: red;">' . esc_html($result->get_error_message()) . '</div>';
    } else {
        // Redirigir a la lista después de actualizar
        wp_safe_redirect('?crud_action=list_schedules');
        exit;
    }
}
?>

<h2>Editar Horario</h2>
<form method="POST">
    <?php wp_nonce_field('update_schedule_nonce'); // Generar nonce para validación ?>
    
    <input type="hidden" name="id" value="<?php echo esc_attr($schedule['id']); ?>"> <!-- ID oculto -->

    <!-- Selector de días -->
    <label for="day_id">Día:</label><br>
    <select id="day_id" name="day_id" required>
        <?php foreach ($days as $day): ?>
            <option value="<?php echo esc_attr($day['id']); ?>" <?php selected($day['id'], $schedule['day_id']); ?>>
                <?php echo esc_html($day['name']); ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="start_time">Hora de Inicio:</label><br>
    <input type="time" id="start_time" name="start_time" value="<?php echo esc_attr($schedule['start_time']); ?>" required><br><br>

    <label for="end_time">Hora de Fin:</label><br>
    <input type="time" id="end_time" name="end_time" value="<?php echo esc_attr($schedule['end_time']); ?>" required><br><br>

    <button type="submit" name="update_schedule">Actualizar</button>
</form>
<a href="?crud_action=list_schedules">Volver a la Lista</a>
