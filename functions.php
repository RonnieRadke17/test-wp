<?php
// Add this to functions.php

// Create table on theme activation

function get_all_days() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';

    return $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
}

// Función para agregar un nuevo día
function add_day($name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';

    $wpdb->insert($table_name, [
        'name' => sanitize_text_field($name),
    ]);

    return $wpdb->insert_id; // Devuelve el ID del registro insertado
}


// Función para actualizar un día existente
function update_day($id, $name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';

    return $wpdb->update(
        $table_name,
        ['name' => sanitize_text_field($name)], // Nuevos valores
        ['id' => intval($id)] // Condición
    );
}

// Función para obtener un día por ID
function get_day_by_id($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';

    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
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

// Función para eliminar un día por ID
function delete_day($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'days';

    return $wpdb->delete($table_name, ['id' => intval($id)]);
}


//
//
//
//
//
//
//crud de schendule
// Función para agregar un nuevo horario
function add_schedule($day_id, $start_time, $end_time) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'schedules'; // Nombre de la tabla

    $wpdb->insert($table_name, [
        'day_id' => intval($day_id), // ID del día
        'start_time' => sanitize_text_field($start_time), // Hora de inicio
        'end_time' => sanitize_text_field($end_time), // Hora de fin
    ]);

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



// Función para actualizar un horario
function update_schedule($id, $day_id, $start_time, $end_time) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'schedules';

    return $wpdb->update(
        $table_name,
        [
            'day_id' => intval($day_id),
            'start_time' => sanitize_text_field($start_time),
            'end_time' => sanitize_text_field($end_time)
        ],
        ['id' => intval($id)] // Condición: Actualizar por ID
    );
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


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////Categorias
/* function obtener_categorias_estados_datos() {
    // Obtener todas las categorías principales
    $args = array(
        'taxonomy'   => 'category',
        'parent'     => 0, // Solo categorías principales
        'hide_empty' => false, // Mostrar categorías vacías
    );

    $categorias = get_terms($args);

    if (!empty($categorias) && !is_wp_error($categorias)) {
        return $categorias; // Devuelve las categorías como datos
    }

    return []; // Devuelve un array vacío si no hay categorías
} */


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
// Función para agregar una compañía y su dirección
function add_company_and_address($company_data, $address_data, $phones, $social_media) {
    global $wpdb;

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

        $address_id = $wpdb->insert_id; // Obtiene el ID de la dirección insertada

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

        $company_id = $wpdb->insert_id; // Obtiene el ID de la compañía insertada

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
                    'name' => $name,
                    'url'        => $social,
                ),
                array('%d', '%s')
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
        wp_die('Error: ' . $e->getMessage()); // Muestra el mensaje de error
    }
}


















add_filter('template_include', 'handle_companies_crud_routes');

add_filter('template_include', 'handle_schedules_crud_routes');

add_filter('template_include', 'handle_keywords_crud_routes');


add_filter('template_include', 'handle_days_crud_routes');

//add_filter('template_include', 'handle_days_crud_routes');

add_filter('template_include', 'handle_days_view');

