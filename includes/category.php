<?php

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