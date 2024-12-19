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
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/companies-list.css">
</head>
<body>
    <h1>Empresas en la Categoría <?php echo esc_html($category_id); ?></h1>

    <div class="company-list">
    <?php if (!empty($companies)) : ?>
        <?php foreach ($companies as $company) : ?>
            
            <a href="<?php echo esc_url(add_query_arg(['crud_action' => 'show_companies', 'id' => $company->company_id], home_url())); ?>" class="company-card">

                <?php
                $template_url = get_template_directory_uri();
                $image_url = $template_url . '/' . $company->first_image;
                $file_path = get_template_directory() . '/' . $company->first_image;

                if (file_exists($file_path)) : ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="Imagen de <?php echo esc_html($company->company_name); ?>">
                <?php else : ?>
                    <img src="https://via.placeholder.com/300x200?text=No+Image" alt="No hay imagen disponible">
                <?php endif; ?>

                <h2><?php echo esc_html($company->company_name); ?></h2>
                <p><?php echo esc_html($company->subcategory_name ?? 'Sin subcategoría'); ?></p>
            </a>
        <?php endforeach; ?>
    <?php else : ?>
        <p style="text-align: center;">No hay empresas en esta categoría.</p>
    <?php endif; ?>
</div>

</body>
</html>
