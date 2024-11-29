<?php
// Manejar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['day_id'])) {
        // Actualizar
        update_day($_POST['day_id'], sanitize_text_field($_POST['day_name']));
    } else {
        // Insertar
        insert_day(sanitize_text_field($_POST['day_name']));
    }
}

// Manejar eliminación
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    delete_day(intval($_GET['id']));
}

// Obtener todos los días
$days = get_all_days();

// Obtener el día a editar, si aplica
$editing_day = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])) {
    $editing_day = get_day_by_id(intval($_GET['id']));
}
?>

<div>
    <h1><?php echo $editing_day ? 'Editar Día' : 'Agregar Nuevo Día'; ?></h1>

    <form method="post" action="">
        <?php if ($editing_day): ?>
            <input type="hidden" name="day_id" value="<?php echo esc_attr($editing_day->id); ?>">
        <?php endif; ?>

        <label for="day_name">Nombre:</label>
        <input type="text" id="day_name" name="day_name" 
               value="<?php echo $editing_day ? esc_attr($editing_day->name) : ''; ?>" required>
        <button type="submit"><?php echo $editing_day ? 'Actualizar Día' : 'Agregar Día'; ?></button>
    </form>

    <h2>Todos los Días</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($days as $day): ?>
                <tr>
                    <td><?php echo esc_html($day->id); ?></td>
                    <td><?php echo esc_html($day->name); ?></td>
                    <td><?php echo esc_html($day->created_at); ?></td>
                    <td>
                        <a href="?action=edit&id=<?php echo esc_attr($day->id); ?>">Editar</a>
                        <a href="?action=delete&id=<?php echo esc_attr($day->id); ?>" 
                           onclick="return confirm('¿Estás seguro de eliminar este día?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
