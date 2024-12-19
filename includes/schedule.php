<?php

//crud de schendule
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
                return get_template_directory() . '/resources/pages/schedule/schedules-list.php'; // Lista de horarios
            case 'add_schedule':
                return get_template_directory() . '/resources/pages/schedule/schedules-add.php'; // Agregar un horario
            case 'edit_schedule': // Ruta para editar
                return get_template_directory() . '/resources/pages/schedule/schedules-edit.php';
        }
    }
    return $template;
}


add_filter('template_include', 'handle_schedules_crud_routes');
