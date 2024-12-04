<?php
// Obtener todos los nombres sociales
$social_names = get_all_social_names(); // Esta función ya está definida en el functions.php


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_social_name'])) {

    $result = delete_social_name($_POST['delete_id']);

    if ($result !== false) {
        // Redirigir a la lista después de eliminar
        wp_safe_redirect('?crud_action=list_social_name');
        exit; // Terminar la ejecución después de redirigir
    } else {
        echo '<div style="color: red;">No se pudo eliminar el nombre social. Inténtalo nuevamente.</div>';
    }

    wp_redirect('?crud_action=list_days'); // Redirigir después de eliminar
    exit;
}

?>

<h2>Listado de Nombres Sociales</h2>
<a href="?crud_action=add_social_name">Agregar Nuevo Nombre Social</a>
<br><br>

<?php if (!empty($social_names)): ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($social_names as $social_name): ?>
                <tr>
                    <td><?php echo esc_html($social_name['id']); ?></td>
                    <td><?php echo esc_html($social_name['name']); ?></td>
                    <td>
                        <a href="?crud_action=edit_social_name&id=<?php echo esc_attr($social_name['id']); ?>">Editar</a> |
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $social_name['id']; ?>">
                            <button type="submit" name="delete_social_name" onclick="return confirm('¿Estás seguro de eliminar este día?');">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No hay nombres sociales registrados.</p>
<?php endif; ?>
