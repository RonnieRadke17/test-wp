<?php
// Verificar si se ha enviado una solicitud POST para eliminar un horario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_schedule'])) {
    // Verificar que el ID esté presente y sea un valor numérico
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $id = $_POST['id'];  // Obtener el ID del horario a eliminar

        // Llamar a la función para eliminar el horario
        $deleted = delete_schedule($id);

        if ($deleted) {
            // Redirigir a la lista de horarios después de la eliminación
            wp_redirect('?crud_action=list_schedules');  // Ajusta la URL de redirección según sea necesario
            exit;  // Asegura que el script se detenga después de la redirección
        } else {
            // Mostrar un mensaje si la eliminación falló
            echo 'Hubo un problema al eliminar el horario.';
        }
    } else {
        echo 'ID inválido para eliminar el horario.';
    }
}

// Obtener todos los horarios con el nombre del día (esto depende de tu implementación)
$schedules = get_all_schedules();
?>

<h2>Lista de Horarios</h2>
<a href="?crud_action=add_schedule">Agregar Nuevo Horario</a>
<table border="1" cellpadding="10">
    <thead>
        <tr>
            <th>ID</th>
            <th>Día</th>
            <th>Hora de Inicio</th>
            <th>Hora de Fin</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($schedules)): ?>
            <?php foreach ($schedules as $schedule): ?>
                <tr>
                    <td><?php echo $schedule['id']; ?></td>
                    <td><?php echo $schedule['day_name']; ?></td>
                    <td><?php echo $schedule['start_time']; ?></td>
                    <td><?php echo $schedule['end_time']; ?></td>
                    <td>
                        <a href="?crud_action=edit_schedule&id=<?php echo $schedule['id']; ?>">Editar</a>

                        <!-- Formulario para eliminar el horario -->
                        <form method="POST" style="display:inline;">
                            <!-- Campo oculto con el ID del horario -->
                            <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">
                            <!-- Botón para eliminar el horario -->
                            <button type="submit" name="delete_schedule" onclick="return confirm('¿Estás seguro de eliminar este horario?');">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No hay horarios registrados.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
