<?php

function add_company($company_data, $address_data, $phones, $social_media,$schedules,$images) {
    global $wpdb;

    // Array para almacenar errores
    $errors = [];

    // Validaciones para $company_data
    if (empty($company_data['name']) || !preg_match('/^[a-zA-Z0-9\s]+$/', $company_data['name'])) {
        $errors[] = 'El nombre de la empresa es inválido o está vacío.';
    }
    if (empty($company_data['description']) || strlen($company_data['description']) > 255) {// expresion regular falta
        $errors[] = 'La descripción de la empresa debe tener menos de 255 caracteres.';
    }
    if (empty($company_data['category_id']) || empty($company_data['subcategory_id'])) {
        $errors[] = 'La categoría y subcategoría de la empresa son obligatorias.';
    }
    
    // Validaciones para $address_data
    if (empty($address_data['name']) || empty($address_data['latitude']) || empty($address_data['longitude'])) {
        $errors[] = 'Selecciona una dirección';
    }
    
    // Validaciones para $phones
    foreach ($phones as $phone) {
        if (!preg_match('/^\+?[0-9\s\-]+$/', $phone)) {
            $errors[] = "El número de teléfono '{$phone}' es inválido.";
        }
    }

        global $wpdb;
    $social_table = $wpdb->prefix . 'social_names'; // Tabla de redes sociales

    // Validaciones para $social_media
    foreach ($social_media as $social) {
        // Validar que el ID de la red social sea un número entero válido
        $social_id = intval($social['name']);
        if ($social_id <= 0) {
            $errors[] = "El ID de la red social '{$social['name']}' es inválido.";
        } else {
            // Verificar que el ID exista en la base de datos
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $social_table WHERE id = %d",
                $social_id
            ));

            if (!$exists) {
                $errors[] = "El ID de la red social '{$social_id}' no existe en el sistema.";
            }
        }

        // Validar que la URL sea válida
        if (!filter_var($social['url'], FILTER_VALIDATE_URL)) {
            $errors[] = "El URL de la red social '{$social['url']}' es inválido.";
        }
    }
    //validacion de schedules
    
     // Validar datos de horarios
    if (empty($schedules)) {
        $errors[] = "Debes seleccionar al menos un horario.";
    }

    // Validar duplicados
    if (count($schedules) !== count(array_unique($schedules))) {
        $errors[] = "No puedes enviar el mismo horario más de una vez.";
    }

    // Validar existencia de los horarios en la base de datos
    $placeholders = implode(',', array_fill(0, count($schedules), '%d'));
    $horarios_validos = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}schedules WHERE id IN ($placeholders)",
        $schedules
    ));

    if (count($horarios_validos) !== count($schedules)) {
        $errors[] = "Algunos de los horarios seleccionados no existen.";
    }


    // **Validaciones de imágenes**
    if (!empty($images)) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif']; // Tipos permitidos
        $max_size = 2 * 1024 * 1024; // Tamaño máximo (2MB)

        foreach ($images as $image) {
            // Validar error al subir
            if (!isset($image['tmp_name']) || $image['tmp_name'] === '') {
                $errors[] = "Una de las imágenes no se subió correctamente.";
                continue;
            }

            // Validar tamaño del archivo
            if ($image['size'] > $max_size) {
                $errors[] = "La imagen '{$image['name']}' excede el tamaño máximo permitido de 2MB.";
                continue;
            }

            // Validar tipo de archivo
            if (!in_array($image['type'], $allowed_types)) {
                $errors[] = "La imagen '{$image['name']}' tiene un formato no permitido.";
                continue;
            }

            // Validar si el archivo es realmente una imagen (verificar MIME)
            if (!@getimagesize($image['tmp_name'])) {
                $errors[] = "El archivo '{$image['name']}' no es una imagen válida.";
                continue;
            }
        }
    } else {
        $errors[] = "Debes subir al menos una imagen.";
    }


    // Si hay errores, devolverlos
    if (!empty($errors)) {
        return $errors;
    }

    // Inicia una transacción
    $wpdb->query('START TRANSACTION');
    try {
        // Inserción en wp_addresses
        $address_inserted = $wpdb->insert(
            $wpdb->prefix . 'addresses',
            array(
                'name'        => $address_data['name'],
                /* 'description' => $address_data['description'], */
                'latitude'    => $address_data['latitude'],
                'longitude'   => $address_data['longitude'],
            ),
            array('%s', '%s', '%f', '%f')
        );

        if (!$address_inserted) {
            throw new Exception('Error al insertar en la tabla wp_addresses');
        }

        $address_id = $wpdb->insert_id;

        // Inserción en wp_companies
        $company_inserted = $wpdb->insert(
            $wpdb->prefix . 'companies',
            array(
                'name'             => $company_data['name'],
                'description'      => $company_data['description'],
                'address_id'       => $address_id,
                'category_id'      => $company_data['category_id'],
                'subcategory_id'   => $company_data['subcategory_id'],
            ),
            array('%s', '%s', '%d', '%d', '%d')
        );

        if (!$company_inserted) {
            throw new Exception('Error al insertar en la tabla wp_companies');
        }

        $company_id = $wpdb->insert_id;

        // Inserción de teléfonos
        foreach ($phones as $phone) {
            $phone_inserted = $wpdb->insert(
                $wpdb->prefix . 'phones',
                array(
                    'company_id' => $company_id,
                    'phone'      => $phone,
                ),
                array('%d', '%s')
            );

            if (!$phone_inserted) {
                throw new Exception('Error al insertar en la tabla wp_phones');
            }
        }

        //redes sociales
        foreach ($social_media as $social) {
            // Validar URL y existencia de social_name_id
            if (!filter_var($social['url'], FILTER_VALIDATE_URL)) {
                throw new Exception("URL inválida: {$social['url']}");
            }
        
            $social_id = intval($social['name']);
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}social_names WHERE id = %d",
                $social_id
            ));
            if (!$exists) {
                throw new Exception("El ID de la red social '{$social_id}' no existe.");
            }
        
            // Intentar la inserción
            $social_inserted = $wpdb->insert(
                $wpdb->prefix . 'social_media',
                [
                    'url' => $social['url'],
                    'company_id' => $company_id,
                    'social_name_id' => $social_id,
                ],
                ['%s', '%d', '%d']
            );
        
            if (!$social_inserted) {
                error_log("Error al insertar en wp_social_media: " . $wpdb->last_error);
                throw new Exception('Error al insertar en la tabla wp_social_media');
            }
        }
        

        //horarios
         // Insertar relación de horarios
        foreach ($schedules as $schedule_id) {
            $schedule_inserted = $wpdb->insert(
                $wpdb->prefix . 'companies_schedules',
                array(
                    'company_id' => $company_id,
                    'schedule_id' => $schedule_id,
                ),
                array('%d', '%d')
            );
            if (!$schedule_inserted) {
                throw new Exception('Error al insertar los horarios.');
            }
        }

        //multimedia
        $carpeta_subida = get_template_directory() . '/storage/uploads/';
        $imagenes_guardadas = [];

        foreach ($images as $image) {
            $nombre_unico = uniqid('img_', true) . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
            $ruta_completa = $carpeta_subida . $nombre_unico;

            // Crear la carpeta si no existe
            if (!file_exists($carpeta_subida)) {
                mkdir($carpeta_subida, 0755, true);
            }

            // Mover el archivo a la carpeta de destino
            if (move_uploaded_file($image['tmp_name'], $ruta_completa)) {
                // Guardar la referencia en la base de datos
                $wpdb->insert(
                    "{$wpdb->prefix}images", // Tabla de imágenes
                    [
                        'company_id' => $company_id,
                        'image_url' => 'storage/uploads/' . $nombre_unico, // Ruta relativa
                        'created_at' => current_time('mysql')
                    ]
                );

                if ($wpdb->insert_id) {
                    $imagenes_guardadas[] = $nombre_unico;
                } else {
                    $errors[] = "Error al guardar la referencia de la imagen '{$image['name']}' en la base de datos.";
                }
            } else {
                $errors[] = "Error al mover la imagen '{$image['name']}' al directorio de destino.";
            }
        }


        // Confirmar la transacción
        $wpdb->query('COMMIT');
    } catch (Exception $e) {
        // Si ocurre un error, revertir la transacción
        $wpdb->query('ROLLBACK');
        $errors[] = $e->getMessage();
        return $errors; // Retornar errores
    }

    // Si todo fue exitoso, retornar a index
    /* return null; */
    wp_redirect('?crud_action=list_companies');
        exit;
}


function edit_company(){}

/* 
function handle_companies_crud_routes($template) {
    if (isset($_GET['crud_action'])) {
        switch ($_GET['crud_action']) {
            case 'state':
                return get_template_directory() . '/resources/pages/companies/state.php';
            case 'list_companies':
                return get_template_directory() . '/resources/pages/companies/companies-list.php';
            case 'add_companies':
                return get_template_directory() . '/resources/pages/companies/companies-add.php';
            case 'edit_companies': // Ruta para editar
                return get_template_directory() . '/resources/pages/companies/companies-edit.php';
        }
    }
    return $template;
}


add_filter('template_include', 'handle_companies_crud_routes'); */

function handle_companies_crud_routes($template) {
    // Definir las rutas personalizadas
    $routes = [
        'state' => '/resources/pages/companies/state.php',
        'list_companies' => '/resources/pages/companies/companies-list.php',
        'add_companies' => '/resources/pages/companies/companies-add.php',
        'edit_companies' => '/resources/pages/companies/companies-edit.php',
        'show_companies' => '/resources/pages/companies/companies-show.php',
    ];

    // Validar si el parámetro `crud_action` está presente
    if (isset($_GET['crud_action'])) {
        $crud_action = sanitize_text_field($_GET['crud_action']);

        // Validar si la acción existe en las rutas
        if (array_key_exists($crud_action, $routes)) {
            return get_template_directory() . $routes[$crud_action];
        } else {
            // Si la acción no es válida, redirigir a la página 404
            wp_redirect(home_url('/404'));
            exit;
        }
    }

    // Retornar el template original si no hay `crud_action`
    return $template;
}
add_filter('template_include', 'handle_companies_crud_routes');

