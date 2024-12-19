<?php
// Verificar que se haya recibido un ID de empresa
if (isset($_GET['id'])) {
    $company_id = intval($_GET['id']); // Convertir a entero para mayor seguridad
} else {
    echo "No se especificó ninguna empresa.";
    exit;
}

global $wpdb;

// Consultar información básica de la empresa
$company = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT 
            c.id AS company_id, 
            c.name AS company_name, 
            c.description AS company_description, 
            t.name AS subcategory_name
        FROM wp_companies c
        LEFT JOIN wp_terms t ON c.subcategory_id = t.term_id
        WHERE c.id = %d",
        $company_id
    )
);

// Consultar imágenes relacionadas
$images = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT image_url 
         FROM wp_images 
         WHERE company_id = %d",
        $company_id
    )
);

// Consultar teléfonos relacionados
$phones = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT phone 
         FROM wp_phones 
         WHERE company_id = %d",
        $company_id
    )
);

// Consultar redes sociales relacionadas
$social_media = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT sm.url, sn.name AS social_name 
         FROM wp_social_media sm
         LEFT JOIN wp_social_names sn ON sm.social_name_id = sn.id
         WHERE sm.company_id = %d",
        $company_id
    )
);

// Consultar dirección relacionada
$address = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT name, description, latitude, longitude 
         FROM wp_addresses 
         WHERE id = (SELECT address_id FROM wp_companies WHERE id = %d)",
        $company_id
    )
);

// Consultar horarios relacionados
$schedules = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT d.name AS day_name, s.start_time, s.end_time 
         FROM wp_companies_schedules cs
         LEFT JOIN wp_schedules s ON cs.schedule_id = s.id
         LEFT JOIN wp_days d ON s.day_id = d.id
         WHERE cs.company_id = %d",
        $company_id
    )
);

if (!$company) {
    echo "No se encontró información para esta empresa.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($company->company_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .left-column {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }

        .right-column {
            padding: 20px;
            text-align: left;
        }

        .right-column h1 {
            font-size: 28px;
            color: #000;
        }

        .info-section h2 {
            margin-top: 20px;
            color: #000;
        }

        ul.info-list {
            list-style: none;
            padding: 0;
        }

        ul.info-list li {
            margin-bottom: 10px;
        }

        ul.info-list a {
            color: #0073aa;
            text-decoration: none;
        }

        ul.info-list a:hover {
            text-decoration: underline;
        }

        .carousel-inner img {
            max-height: 350px;
            object-fit: cover;
        }

        .info-pair {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .info-pair > div {
            flex: 1;
        }
    </style>
</head>
<body>

<?php include get_template_directory() . '/parts/header.html'; ?>


    <div class="container">
        <div class="row">
            <!-- Columna Izquierda -->
            <div class="col-md-7 left-column">
                <!-- Carrusel de Imágenes -->
                <div id="companyCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php if (!empty($images)) : ?>
                            <?php foreach ($images as $index => $image) : ?>
                                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/' . $image->image_url); ?>" class="d-block w-100" alt="Imagen">
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="carousel-item active">
                                <img src="https://via.placeholder.com/800x400?text=No+Image" class="d-block w-100" alt="No hay imagen disponible">
                            </div>
                        <?php endif; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#companyCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#companyCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>

                <!-- Teléfonos y Redes Sociales en la misma altura -->
                <div class="info-pair">
                    <div class="info-section">
                        <h2>Teléfonos</h2>
                        <ul class="info-list">
                            <?php if (!empty($phones)) : ?>
                                <?php foreach ($phones as $phone) : ?>
                                    <li><?php echo esc_html($phone->phone); ?></li>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <li>No hay teléfonos disponibles.</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="info-section">
                        <h2>Redes Sociales</h2>
                        <ul class="info-list">
                            <?php if (!empty($social_media)) : ?>
                                <?php foreach ($social_media as $social) : ?>
                                    <li>
                                        <strong><?php echo esc_html($social->social_name); ?>:</strong>
                                        <a href="<?php echo esc_url($social->url); ?>" target="_blank"><?php echo esc_html($social->url); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <li>No hay redes sociales disponibles.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Dirección -->
                <div class="info-section">
                    <h2>Dirección</h2>
                    <?php if (!empty($address)) : ?>
                        <p><?php echo esc_html($address->name); ?></p>
                        <p><?php echo esc_html($address->description); ?></p>
                    <?php else : ?>
                        <p>No hay dirección disponible.</p>
                    <?php endif; ?>
                </div>

                <!-- Horarios -->
                <div class="info-section">
                    <h2>Horarios</h2>
                    <ul class="info-list">
                        <?php if (!empty($schedules)) : ?>
                            <?php foreach ($schedules as $schedule) : ?>
                                <li>
                                    <strong><?php echo esc_html($schedule->day_name); ?>:</strong> <?php echo esc_html($schedule->start_time); ?> - <?php echo esc_html($schedule->end_time); ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <li>No hay horarios disponibles.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="col-md-5 right-column">
                <h1><?php echo esc_html($company->company_name); ?></h1>
                <p><strong>Descripción:</strong> <?php echo esc_html($company->company_description); ?></p>
                <p><strong>Subcategoría:</strong> <?php echo esc_html($company->subcategory_name ?? 'Sin subcategoría'); ?></p>
            </div>
        </div>
    </div>
</body>
</html>
