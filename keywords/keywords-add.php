<?php
// Manejar la solicitud de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_keywords'])) {
    add_keywords($_POST['name']);
    wp_redirect('?crud_action=list_keywords'); // Redirige después de agregar
    exit;
}
?>

<h2>Agregar Nuevo Día</h2>
<form method="POST">
    <label for="name">Nombre de la etiqueta:</label><br>
    <input type="text" id="name" name="name" required><br><br>
    <button type="submit" name="add_keywords">Guardar</button>
</form>
<a href="?crud_action=list_keywords">Volver a la Lista</a>
