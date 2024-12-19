<?php

$categorias = obtener_categorias_principales();
// Manejar la solicitud de eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_adress'])) {
    delete_adress($_POST['id']); // Llama a la función para eliminar el registro
    wp_redirect('?crud_action=list_adress'); // Redirigir después de eliminar
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<h2>Lista de Días</h2>
<?php if (!empty($categorias)) : ?>
    <ul id="categoria_estado">
        <?php foreach ($categorias as $categoria) : ?>
            <li data-value="<?php echo esc_attr($categoria->term_id); ?>">
                <?php echo esc_html($categoria->name); ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else : ?>
    <p>No hay estados disponibles</p>
<?php endif; ?>


<?php include get_template_directory() . '/parts/footer.html'; ?>

</body>
</html>







