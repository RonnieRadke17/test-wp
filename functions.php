<?php

//Archivos de gestión de funcionalidades del código

require_once get_template_directory() . '/includes/company.php';

require_once get_template_directory() . '/includes/day.php';

require_once get_template_directory() . '/includes/schedule.php';

require_once get_template_directory() . '/includes/keyword.php';//no sirve

require_once get_template_directory() . '/includes/social_name.php';

require_once get_template_directory() . '/includes/category.php';

function obtener_horarios_con_dias() {
    global $wpdb;

    // Tablas
    $tabla_horarios = $wpdb->prefix . 'schedules';
    $tabla_dias = $wpdb->prefix . 'days';

    // Consulta para obtener horarios con sus días asociados
    $resultados = $wpdb->get_results("
        SELECT 
            $tabla_horarios.id AS horario_id,
            $tabla_horarios.start_time,
            $tabla_horarios.end_time,
            $tabla_dias.name AS dia
        FROM $tabla_horarios
        JOIN $tabla_dias ON $tabla_horarios.day_id = $tabla_dias.id
        ORDER BY $tabla_dias.id, $tabla_horarios.start_time
    ", ARRAY_A);

    return $resultados;
}
// Llama a la función y almacena los horarios con días
$horarios = obtener_horarios_con_dias();