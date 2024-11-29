<?php
// Manejar la solicitud de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    add_schedule($_POST['day_id'], $_POST['start_time'], $_POST['end_time']); // Llama a la función para insertar
    wp_redirect('?crud_action=list_schedules'); // Redirige después de guardar
    exit;
}
?>

<h2>Agregar Nuevo Horario</h2>
<form method="POST">

    <label for="day_id">Día:</label><br>
    <select id="day_id" name="day_id" required>
        <?php foreach (get_all_days() as $day): ?>
            <option value="<?php echo $day['id']; ?>"><?php echo $day['name']; ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="start_time">Hora de Inicio:</label><br>
    <input type="time" id="start_time" name="start_time" required><br><br>

    <label for="end_time">Hora de Fin:</label><br>
    <input type="time" id="end_time" name="end_time" required><br><br>

    <button type="submit" name="add_schedule">Guardar</button>
</form>
<a href="?crud_action=list_schedules">Volver a la Lista</a>


