<?php

function get_all_days() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';

    return $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
}

// Función para agregar un nuevo día
function add_day($name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';

    // Sanitización del campo
    $name = sanitize_text_field($name);

    // Validación de longitud
    if (strlen($name) < 5 || strlen($name) > 45) {
        return new WP_Error('invalid_name_length', 'El nombre debe tener entre 5 y 45 caracteres.');
    }

    // Intentar insertar el nuevo día
    $result = $wpdb->insert($table_name, ['name' => $name]);

    // Manejar errores de la base de datos
    if ($result === false) {
        $last_error = $wpdb->last_error;
        if (strpos($last_error, 'Duplicate entry') !== false) {
            return new WP_Error('duplicate_entry', 'El nombre ingresado ya existe. Por favor, elige otro.');
        }
        return new WP_Error('db_insert_error', 'No se pudo agregar el día. Inténtalo nuevamente.');
    }

    return $wpdb->insert_id; // Devuelve el ID del registro insertado
}


// Función para actualizar un día existente
// Función para actualizar un día existente con validaciones
function update_day($id, $name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';

    // Validar y sanitizar los datos
    $name = sanitize_text_field($name);
    $id = intval($id);

    // Validación de longitud
    if (strlen($name) < 5 || strlen($name) > 45) {
        return new WP_Error('invalid_name_length', 'El nombre debe tener entre 5 y 45 caracteres.');
    }

    // Comprobar si el nombre ya existe en otro registro
    $existing_day = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE name = %s AND id != %d",
        $name, $id
    ));

    if ($existing_day) {
        return new WP_Error('duplicate_entry', 'El nombre ingresado ya existe. Por favor, elige otro.');
    }

    // Intentar la actualización
    $result = $wpdb->update(
        $table_name,
        ['name' => $name], // Nuevos valores
        ['id' => $id] // Condición
    );

    if ($result === false) {
        return new WP_Error('db_update_error', 'No se pudo actualizar el día. Inténtalo nuevamente.');
    }

    return $result;
}

// Función para obtener un día por ID
function get_day_by_id($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';

    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
}

// Función para eliminar un día por ID
function delete_day($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';

    return $wpdb->delete($table_name, ['id' => intval($id)]);
}

function handle_days_crud_routes($template) {
    if (isset($_GET['crud_action'])) {
        switch ($_GET['crud_action']) {
            case 'add_day':
                return get_template_directory() . '/crud/days-add.php';
            case 'edit_day': // Ruta para editar
                return get_template_directory() . '/crud/days-edit.php';
        }
    }
    return $template;
}

function handle_days_view($template) {//index
    if (isset($_GET['crud_action']) && $_GET['crud_action'] === 'list_days') {
        return get_template_directory() . '/crud/days-list.php';
    }
    return $template;
}











//crud de schendule
// Función para agregar un nuevo horario
// Función para agregar un nuevo horario con validaciones
function add_schedule($day_id, $start_time, $end_time) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'schedules';

    // Validar y sanitizar los datos
    $day_id = intval($day_id);
    $start_time = sanitize_text_field($start_time);
    $end_time = sanitize_text_field($end_time);

    // Verificar que el día existe
    $day_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}days WHERE id = %d",
        $day_id
    ));
    if (!$day_exists) {
        return new WP_Error('invalid_day', 'El día seleccionado no existe.');
    }

    // Validar que la hora de inicio sea menor que la hora de fin
    if (strtotime($start_time) >= strtotime($end_time)) {
        return new WP_Error('invalid_time_range', 'La hora de inicio debe ser menor que la hora de fin.');
    }

    // Comprobar duplicados
    $schedule_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE day_id = %d AND start_time = %s AND end_time = %s",
        $day_id, $start_time, $end_time
    ));
    if ($schedule_exists) {
        return new WP_Error('duplicate_schedule', 'Ya existe un horario con estos valores.');
    }

    // Insertar el nuevo horario
    $result = $wpdb->insert($table_name, [
        'day_id' => $day_id,
        'start_time' => $start_time,
        'end_time' => $end_time,
    ]);

    if ($result === false) {
        return new WP_Error('db_insert_error', 'No se pudo agregar el horario. Inténtalo nuevamente.');
    }

    return $wpdb->insert_id; // Devuelve el ID del registro insertado
}


// Función para obtener todos los horarios con el nombre del día
function get_all_schedules() {
    global $wpdb;
    $schedules_table = $wpdb->prefix . 'schedules';
    $days_table = $wpdb->prefix . 'days';

    // Consulta con JOIN para obtener el nombre del día
    return $wpdb->get_results("
        SELECT 
            $schedules_table.id, 
            $days_table.name AS day_name, 
            $schedules_table.start_time, 
            $schedules_table.end_time
        FROM $schedules_table
        JOIN $days_table ON $schedules_table.day_id = $days_table.id
        ORDER BY $days_table.id, $schedules_table.start_time
    ", ARRAY_A);
}

// Función para actualizar un horario con validaciones
function update_schedule($id, $day_id, $start_time, $end_time) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'schedules';

    // Validar y sanitizar los datos
    $id = intval($id);
    $day_id = intval($day_id);
    $start_time = sanitize_text_field($start_time);
    $end_time = sanitize_text_field($end_time);

    // Validar que el día existe
    $day_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}days WHERE id = %d",
        $day_id
    ));
    if (!$day_exists) {
        return new WP_Error('invalid_day', 'El día seleccionado no existe.');
    }

    // Validar que la hora de inicio sea menor que la hora de fin
    if (strtotime($start_time) >= strtotime($end_time)) {
        return new WP_Error('invalid_time_range', 'La hora de inicio debe ser menor que la hora de fin.');
    }

    // Comprobar duplicados
    $schedule_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE day_id = %d AND start_time = %s AND end_time = %s AND id != %d",
        $day_id, $start_time, $end_time, $id
    ));
    if ($schedule_exists) {
        return new WP_Error('duplicate_schedule', 'Ya existe un horario con estos valores.');
    }

    // Intentar la actualización
    $result = $wpdb->update(
        $table_name,
        [
            'day_id' => $day_id,
            'start_time' => $start_time,
            'end_time' => $end_time,
        ],
        ['id' => $id] // Condición
    );

    if ($result === false) {
        return new WP_Error('db_update_error', 'No se pudo actualizar el horario. Inténtalo nuevamente.');
    }

    return $result;
}

// Función para obtener un horario por ID
function get_schedule_by_id($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'schedules';

    return $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id),
        ARRAY_A
    );
}

// Función para eliminar un horario de la base de datos
function delete_schedule($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'schedules';  // Nombre de la tabla 'schedule'

    // Usamos el método delete() de $wpdb para eliminar el registro con el ID proporcionado
    return $wpdb->delete($table_name, ['id' => intval($id)]);  // Retorna true si la eliminación fue exitosa
}

function handle_schedules_crud_routes($template) {
    if (isset($_GET['crud_action'])) {
        switch ($_GET['crud_action']) {
            case 'list_schedules':
                return get_template_directory() . '/schedule/schedules-list.php'; // Lista de horarios
            case 'add_schedule':
                return get_template_directory() . '/schedule/schedules-add.php'; // Agregar un horario
            case 'edit_schedule': // Ruta para editar
                return get_template_directory() . '/schedule/schedules-edit.php';
        }
    }
    return $template;
}
add_filter('template_include', 'handle_schedules_crud_routes');
















//keywords

function get_all_keywords() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'keywords';

    return $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
}

// Función para agregar un nuevo día
function add_keywords($name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'keywords';

    $wpdb->insert($table_name, [
        'name' => sanitize_text_field($name),
    ]);

    return $wpdb->insert_id; // Devuelve el ID del registro insertado
}


function get_keywords_by_id($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'keywords';

    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
}


// Función para actualizar un día existente
function update_keywords($id, $name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'keywords';

    return $wpdb->update(
        $table_name,
        ['name' => sanitize_text_field($name)], // Nuevos valores
        ['id' => intval($id)] // Condición
    );
}



function delete_keywords($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'keywords';

    return $wpdb->delete($table_name, ['id' => intval($id)]);
}


function handle_keywords_crud_routes($template) {
    if (isset($_GET['crud_action'])) {
        switch ($_GET['crud_action']) {
            case 'list_keywords':
                return get_template_directory() . '/keywords/keywords-list.php'; // Lista de horarios
            case 'add_keywords':
                return get_template_directory() . '/keywords/keywords-add.php'; // Agregar un horario
            case 'edit_keywords': // Ruta para editar
                return get_template_directory() . '/keywords/keywords-edit.php';
        }
    }
    return $template;
}



// social names

// Función para obtener todos los nombres sociales
function get_all_social_names() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'social_names';

    return $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
}

// Función para agregar un nuevo nombre social
function add_social_name($name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'social_names';

    // Sanitización del campo
    $name = sanitize_text_field($name);

    // Validación de longitud
    if (strlen($name) < 5 || strlen($name) > 45) {
        return new WP_Error('invalid_name_length', 'El nombre debe tener entre 5 y 45 caracteres.');
    }

    // Intentar insertar el nuevo nombre
    $result = $wpdb->insert($table_name, ['name' => $name]);

    // Manejar errores de la base de datos
    if ($result === false) {
        $last_error = $wpdb->last_error;
        if (strpos($last_error, 'Duplicate entry') !== false) {
            return new WP_Error('duplicate_entry', 'El nombre ingresado ya existe. Por favor, elige otro.');
        }
        return new WP_Error('db_insert_error', 'No se pudo agregar el nombre social. Inténtalo nuevamente.');
    }

    return $wpdb->insert_id; // Devuelve el ID del registro insertado
}

// Función para actualizar un nombre social existente con validaciones
function update_social_name($id, $name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'social_names';

    // Validar y sanitizar los datos
    $name = sanitize_text_field($name);
    $id = intval($id);

    // Validación de longitud
    if (strlen($name) < 5 || strlen($name) > 45) {
        return new WP_Error('invalid_name_length', 'El nombre debe tener entre 5 y 45 caracteres.');
    }

    // Comprobar si el nombre ya existe en otro registro
    $existing_name = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE name = %s AND id != %d",
        $name, $id
    ));

    if ($existing_name) {
        return new WP_Error('duplicate_entry', 'El nombre ingresado ya existe. Por favor, elige otro.');
    }

    // Intentar la actualización
    $result = $wpdb->update(
        $table_name,
        ['name' => $name], // Nuevos valores
        ['id' => $id] // Condición
    );

    if ($result === false) {
        return new WP_Error('db_update_error', 'No se pudo actualizar el nombre social. Inténtalo nuevamente.');
    }

    return $result;
}

// Función para obtener un nombre social por ID
function get_social_name_by_id($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'social_names';

    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
}

// Función para eliminar un nombre social por ID
function delete_social_name($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'social_names';

    // Eliminar el registro
    return $wpdb->delete($table_name, ['id' => intval($id)]);
}

// Función para manejar rutas específicas del CRUD de nombres sociales
function handle_social_names_crud_routes($template) {
    if (isset($_GET['crud_action'])) {
        switch ($_GET['crud_action']) {
            case 'add_social_name':
                return get_template_directory() . '/social-names/social-names-add.php';
            case 'edit_social_name':
                return get_template_directory() . '/social-names/social-names-edit.php';
            case 'list_social_name':
                return get_template_directory() . '/social-names/social-names-list.php';
        }
    }
    return $template;
}

add_filter('template_include', 'handle_social_names_crud_routes');






























//companies
// Función para agregar un nuevo día
function add_companies($name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'companies';

    $wpdb->insert($table_name, [
        'name' => sanitize_text_field($name),
        'description' => sanitize_text_field($description),
        //metatitle y metadescription se generan desde aqui
    ]);

    return $wpdb->insert_id; // Devuelve el ID del registro insertado
}

function handle_companies_crud_routes($template) {
    if (isset($_GET['crud_action'])) {
        switch ($_GET['crud_action']) {
            case 'list_companies':
                return get_template_directory() . '/companies/companies-list.php'; // Lista de horarios
            case 'add_companies':
                return get_template_directory() . '/companies/companies-add.php'; // Agregar un horario
            case 'edit_companies': // Ruta para editar
                return get_template_directory() . '/companies/companies-edit.php';
        }
    }
    return $template;
}











// Función para obtener categorías principales (estados)
function obtener_categorias_principales() {
    $args = array(
        'taxonomy'   => 'category',
        'parent'     => 0, // Solo categorías principales (sin padres)
        'hide_empty' => false, // Mostrar categorías incluso si no tienen publicaciones
    );

    $categorias = get_terms($args);

    return !empty($categorias) && !is_wp_error($categorias) ? $categorias : [];
}

// Función para obtener subcategorías (municipios) dinámicamente según el padre
function obtener_subcategorias_ajax() {
    // Verifica que se haya pasado un ID válido
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;

    $args = array(
        'taxonomy'   => 'category',
        'parent'     => $parent_id,
        'hide_empty' => false,
    );

    $subcategorias = get_terms($args);

    // Prepara el resultado para enviarlo como JSON
    $resultado = [];
    if (!empty($subcategorias) && !is_wp_error($subcategorias)) {
        foreach ($subcategorias as $subcategoria) {
            $resultado[] = [
                'id'   => $subcategoria->term_id,
                'name' => $subcategoria->name,
            ];
        }
    }

    // Devuelve las subcategorías en formato JSON
    wp_send_json($resultado);
}
add_action('wp_ajax_obtener_subcategorias', 'obtener_subcategorias_ajax');
add_action('wp_ajax_nopriv_obtener_subcategorias', 'obtener_subcategorias_ajax');





























//operaciones en conjunto de incersion de una empresa

function add_company($company_data, $address_data, $phones, $social_media) {
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
    if (!empty($company_data['category_id']) || !is_numeric($company_data['category_id']) || !is_numeric($company_data['subcategory_id']) || empty($company_data['subcategory_id'])) {
        $errors[] = 'La categoría o subcategoría de la empresa no son válidas.';
    }

    // Validaciones para $address_data
    if (empty($address_data['name']) || !preg_match('/^[a-zA-Z0-9\s]+$/', $address_data['name']) || empty($address_data['latitude']) || !is_numeric($address_data['latitude']) || !is_numeric($address_data['longitude']) || empty($address_data['longitude'])) {
        $errors[] = 'Selecciona una dirección';
    }
    
    // Validaciones para $phones
    foreach ($phones as $phone) {
        if (!preg_match('/^\+?[0-9\s\-]+$/', $phone)) {
            $errors[] = "El número de teléfono '{$phone}' es inválido.";
        }
    }

    // Validaciones para $social_media
    foreach ($social_media as $social) {
        if (empty($social['name']) || !preg_match('/^[a-zA-Z\s]+$/', $social['name'])) {
            $errors[] = "El nombre de la red social '{$social['name']}' es inválido.";
        }
        if (!filter_var($social['url'], FILTER_VALIDATE_URL)) {
            $errors[] = "El URL de la red social '{$social['url']}' es inválido.";
        }
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
                'description' => $address_data['description'],
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

        // Inserción de redes sociales
        foreach ($social_media as $social) {
            $social_inserted = $wpdb->insert(
                $wpdb->prefix . 'social_media',
                array(
                    'company_id' => $company_id,
                    'name'       => $social['name'],
                    'url'        => $social['url'],
                ),
                array('%d', '%s', '%s')
            );

            if (!$social_inserted) {
                throw new Exception('Error al insertar en la tabla wp_social_media');
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

function ia_Test(){
        

    // Define tu clave de API de OpenAI
    $apiKey = "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"; // Reemplaza con tu clave de API

    // Define el mensaje que deseas enviar a ChatGPT
    $message = "Hola, ¿cómo estás?";

    // Configuración de la solicitud
    $url = "https://api.openai.com/v1/chat/completions";
    $data = [
        "model" => "gpt-3.5-turbo", // Cambia a "gpt-3.5-turbo" si usas ese modelo
        "messages" => [
            ["role" => "system", "content" => "Eres un asistente útil."],
            ["role" => "user", "content" => $message]
        ],
        "max_tokens" => 200,
        "temperature" => 0.7
    ];

    $headers = [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ];

    // Llamada a la API usando cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    // Verifica si ocurrió un error
    if (curl_errno($ch)) {
        echo "Error: " . curl_error($ch);
    } else {
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpStatus == 200) {
            $responseData = json_decode($response, true);
            // Muestra la respuesta de ChatGPT
            if (isset($responseData['choices'][0]['message']['content'])) {
                

                return $responseData['choices'][0]['message']['content'];
                
                echo "ChatGPT dice: " . $responseData['choices'][0]['message']['content'];
            } else {
                echo "No se pudo obtener una respuesta válida.";
            }
        } else {
            echo "Error: Código de estado HTTP " . $httpStatus . "\n";
            echo "Detalles: " . $response;
        }
    }

    // Cierra cURL
    curl_close($ch);


}

function handle_ia_routes($template) {
    if (isset($_GET['crud_action'])) {
        switch ($_GET['crud_action']) {
            case 'ia-test':
                return get_template_directory() . '/keywords/api-test.php'; // Lista de horarios
            
        }
    }
    return $template;
}
add_filter('template_include', 'handle_ia_routes');






add_filter('template_include', 'handle_companies_crud_routes');

add_filter('template_include', 'handle_schedules_crud_routes');

add_filter('template_include', 'handle_keywords_crud_routes');

add_filter('template_include', 'handle_days_crud_routes');

add_filter('template_include', 'handle_days_view');