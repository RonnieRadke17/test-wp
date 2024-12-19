<?php

$categorias = obtener_categorias_principales();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/states.css">
    <title>Document</title>
</head>
<body>

<h2>Lista de negocios</h2>

    <?php if (!empty($categorias)) : ?>
    <ul id="categoria_estado">
        <?php foreach ($categorias as $categoria) : ?>
            <li>
                <!-- Enlace que lleva a companies-list.php con el ID de la categoría -->
                <a href="<?php echo esc_url(home_url('/')); ?>?crud_action=list_companies&category_id=<?php echo esc_attr($categoria->term_id); ?>">
                    <?php echo esc_html($categoria->name); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else : ?>
    <p>No hay categorías disponibles.</p>
<?php endif; ?>


    <?php include get_template_directory() . '/parts/footer.html'; ?>


</body>
</html>