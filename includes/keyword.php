<?php

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

add_filter('template_include', 'handle_keywords_crud_routes');