<?php
// Manejar la solicitud de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_day'])) {
    add_day($_POST['name']);
    wp_redirect('?crud_action=list_days'); // Redirige después de agregar
    exit;
}
?>

<h2>Agregar Nuevo Día</h2>
<form method="POST">
    <label for="name">Nombre del Día:</label><br>
    <input type="text" id="name" name="name" required><br><br>
    <button type="submit" name="add_day">Guardar</button>
</form>
<a href="?crud_action=list_days">Volver a la Lista</a>
