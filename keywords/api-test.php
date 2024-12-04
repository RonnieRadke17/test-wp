<?php
// Manejar la solicitud de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ia_Test'])) {
    ia_Test();
    $responseData = ia_Test();

    if ($responseData) {
            echo '<p style="color: red;">' . esc_html($responseData) . '</p>';
    
    }else {
        echo '<p style="color: green;">Empresa agregada exitosamente.</p>';
    }

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <form method="POST">
        <label for="name">Nombre de la etiqueta:</label><br>
        <button type="submit" name="ia_Test">Guardar</button>
    </form>

</body>
</html>