<?php
// Manejar la solicitud de eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_adress'])) {
    delete_adress($_POST['id']); // Llama a la función para eliminar el registro
    wp_redirect('?crud_action=list_adress'); // Redirigir después de eliminar
    exit;
}

?>

<h2>Lista de Días</h2>
<a href="?crud_action=add_adress">Agregar Nuevo Día</a>
<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Acciones</th>
    </tr>
    <?php foreach ($adress_list as $adress): ?>
        <tr>
            <td><?php echo $adress['id']; ?></td>
            <td><?php echo $adress['name']; ?></td>
            <td>
                <a href="?crud_action=edit_adress&id=<?php echo $adress['id']; ?>">Editar</a>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $adress['id']; ?>">
                    <button type="submit" name="delete_adress" onclick="return confirm('¿Estás seguro de eliminar este día?');">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
