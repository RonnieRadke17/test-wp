<?php
// Manejar la solicitud de eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_keywords'])) {
    delete_keywords($_POST['id']); // Llama a la función para eliminar el registro
    wp_redirect('?crud_action=list_keywords'); // Redirigir después de eliminar
    exit;
}

// Obtener todos los días
$keywords_list = get_all_keywords();
?>

<h2>Lista de keywords</h2>
<a href="?crud_action=add_keywords">Agregar Nuevo</a>
<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Acciones</th>
    </tr>
    <?php foreach ($keywords_list as $keyword): ?>
        <tr>
            <td><?php echo $keyword['id']; ?></td>
            <td><?php echo $keyword['name']; ?></td>
            <td>
                <a href="?crud_action=edit_keywords&id=<?php echo $keyword['id']; ?>">Editar</a>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $keyword['id']; ?>">
                    <button type="submit" name="delete_keywords" onclick="return confirm('¿Estás seguro de eliminar este día?');">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
