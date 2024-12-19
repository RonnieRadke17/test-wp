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
                return get_template_directory() . '/resources/pages/days/days-add.php';
            case 'edit_day': // Ruta para editar
                return get_template_directory() . '/resources/pages/days/days-edit.php';
        }
    }
    return $template;
}

function handle_days_view($template) {//index
    if (isset($_GET['crud_action']) && $_GET['crud_action'] === 'list_days') {
        return get_template_directory() . '/resources/pages/days/days-list.php';
    }
    return $template;
}


add_filter('template_include', 'handle_days_crud_routes');
add_filter('template_include', 'handle_days_view');