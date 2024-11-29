<?php

class DayController{


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

}