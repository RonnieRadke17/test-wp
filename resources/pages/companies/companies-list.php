<?php
    // Verificar que se haya recibido un ID de categoría
    if (isset($_GET['category_id'])) {
        $category_id = intval($_GET['category_id']); // Convertir el ID a entero
    } else {
        echo "No se especificó ninguna categoría.";
        exit;
    }

    global $wpdb;

    // Consulta para obtener empresas con sus subcategorías y la primera imagen
    $companies = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT 
                c.id AS company_id, 
                c.name AS company_name, 
                c.description AS company_description, 
                t.name AS subcategory_name, 
                MIN(img.image_url) AS first_image
            FROM wp_companies c
            LEFT JOIN wp_terms t ON c.subcategory_id = t.term_id
            LEFT JOIN wp_images img ON c.id = img.company_id
            WHERE c.category_id = %d
            GROUP BY c.id",
            $category_id
        )
    );
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas por Categoría</title>
</head>
<body>
    <h1>Empresas en la Categoría <?php echo esc_html($category_id); ?></h1>

    <?php if (!empty($companies)) : ?>
        <ul>
            <?php foreach ($companies as $company) : ?>
                <li>
                    <strong>Nombre:</strong> <?php echo esc_html($company->company_name); ?><br>
                    <strong>Descripción:</strong> <?php echo esc_html($company->company_description); ?><br>
                    <strong>Subcategoría:</strong> <?php echo esc_html($company->subcategory_name ?? 'No disponible'); ?><br>
                    
                    <!-- Mostrar la imagen -->
                    <?php
                    $template_url = get_template_directory_uri();
                    $image_url = $template_url . '/' . $company->first_image;
                    $file_path = get_template_directory() . '/' . $company->first_image;

                    if (file_exists($file_path)) : ?>
                        <img src="<?php echo esc_url($image_url); ?>" 
                             alt="Imagen de <?php echo esc_html($company->company_name); ?>" 
                             style="width: 150px; height: auto;">
                    <?php else : ?>
                        <p>No hay imagen disponible.</p>
                        <p>Ruta física: <?php echo $file_path; ?></p>
                        <p>URL generada: <?php echo $image_url; ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p>No hay empresas en esta categoría.</p>
    <?php endif; ?>
</body>
</html>
