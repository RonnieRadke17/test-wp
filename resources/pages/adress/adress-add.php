<?php
// Manejar la solicitud de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_adress'])) {
    add_adress($_POST['name']);
    wp_redirect('?crud_action=list_adresss'); // Redirige después de agregar
    exit;
}
?>

<h2>Agregar Nueva direccion</h2>
<form method="POST">
    <label for="name">Nombre del Día:</label><br>
    <input type="text" id="name" name="name" required><br><br>
    <button type="submit" name="add_adress">Guardar</button>
</form>
<a href="?crud_action=list_adresss">Volver a la Lista</a>
