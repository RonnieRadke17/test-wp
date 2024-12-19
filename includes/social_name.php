<?php

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
                return get_template_directory() . '/resources/pages/social-names/social-names-add.php';
            case 'edit_social_name':
                return get_template_directory() . '/resources/pages/social-names/social-names-edit.php';
            case 'list_social_name':
                return get_template_directory() . '/resources/pages/social-names/social-names-list.php';
        }
    }
    return $template;
}

add_filter('template_include', 'handle_social_names_crud_routes');
