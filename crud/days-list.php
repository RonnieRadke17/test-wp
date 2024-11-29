<?php
// Manejar la solicitud de eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_day'])) {
    delete_day($_POST['id']); // Llama a la función para eliminar el registro
    wp_redirect('?crud_action=list_days'); // Redirigir después de eliminar
    exit;
}

// Obtener todos los días
$days_list = get_all_days();
?>

<h2>Lista de Días</h2>
<a href="?crud_action=add_day">Agregar Nuevo Día</a>
<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Acciones</th>
    </tr>
    <?php foreach ($days_list as $day): ?>
        <tr>
            <td><?php echo $day['id']; ?></td>
            <td><?php echo $day['name']; ?></td>
            <td>
                <a href="?crud_action=edit_day&id=<?php echo $day['id']; ?>">Editar</a>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $day['id']; ?>">
                    <button type="submit" name="delete_day" onclick="return confirm('¿Estás seguro de eliminar este día?');">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
