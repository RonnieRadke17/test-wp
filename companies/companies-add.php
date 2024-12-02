<?php

// Obtiene las categorías principales (estados)
$categorias = obtener_categorias_principales();

// Manejar la solicitud de inserción para la compañía, dirección, teléfonos y redes sociales
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_company_and_address'])) {
    $company_data = array(
        'name'             => sanitize_text_field($_POST['company_name']),
        'description'      => sanitize_text_field($_POST['company_description']),
        'category_id'      => intval($_POST['categoria_estado']),
        'subcategory_id'   => intval($_POST['subcategoria']),
    );

    $address_data = array(
        'name'        => sanitize_text_field($_POST['placeName']),
        'description' => sanitize_text_field($_POST['placeDescription']),
        'latitude'    => floatval($_POST['latitude']),
        'longitude'   => floatval($_POST['longitude']),
    );

    $phones = isset($_POST['phones']) ? array_map('sanitize_text_field', $_POST['phones']) : [];
    $social_media = isset($_POST['social_media']) ? array_map('sanitize_text_field', $_POST['social_media']) : [];

    add_company_and_address($company_data, $address_data, $phones, $social_media);

    wp_redirect('?crud_action=list_companies');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map {
            height: 500px;
            margin-bottom: 20px;
        }
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
        }
    </style>
    <title>Registrar Compañía</title>
</head>
<body>

<form id="company-form" method="POST">
    <!-- Paso 1: Información de la Compañía -->
    <div id="step-1" class="form-step">
        <h3>Información de la Compañía</h3>
        <label for="company_name">Nombre de la Compañía:</label><br>
        <input type="text" id="company_name" name="company_name" required><br><br>

        <label for="company_description">Descripción de la Compañía:</label><br>
        <input type="text" id="company_description" name="company_description" required><br><br>

        <!-- Selector de categorías -->
        <label for="categoria_estado">Estado:</label><br>
        <select name="categoria_estado" id="categoria_estado" required>
            <option value="">-- Selecciona un estado --</option>
            <?php if (!empty($categorias)) : ?>
                <?php foreach ($categorias as $categoria) : ?>
                    <option value="<?php echo esc_attr($categoria->term_id); ?>">
                        <?php echo esc_html($categoria->name); ?>
                    </option>
                <?php endforeach; ?>
            <?php else : ?>
                <option value="">No hay estados disponibles</option>
            <?php endif; ?>
        </select><br><br>

        <!-- Selector de subcategorías -->
        <label for="subcategoria">Municipio:</label><br>
        <select name="subcategoria" id="subcategoria" required>
            <option value="">-- Selecciona un municipio --</option>
        </select><br><br>

        <button type="button" class="next-step">Siguiente</button>
    </div>

    <!-- Paso 2: Dirección y Contacto -->
    <div id="step-2" class="form-step" style="display:none;">
        <h3>Dirección</h3>
        <div>
            <input type="text" id="searchBox" placeholder="Buscar un lugar..." style="width: 300px;" />
            <button type="button" onclick="searchPlace()">Buscar</button>
        </div>
        <div id="map"></div>

        <div class="form-container">
            <input type="hidden" id="placeName" name="placeName" />
            <input type="hidden" id="placeDescription" name="placeDescription" value="Descripción del lugar seleccionado" />
            <input type="hidden" id="latitude" name="latitude" />
            <input type="hidden" id="longitude" name="longitude" />

            <p><strong>Nombre:</strong> <span id="displayName"></span></p>
            <p><strong>Latitud:</strong> <span id="displayLat"></span></p>
            <p><strong>Longitud:</strong> <span id="displayLng"></span></p>
        </div>

        <h3>Teléfonos</h3>
        <div id="phone-container">
            <input type="text" name="phones[]" placeholder="Número de teléfono" />
        </div>
        <button type="button" id="add-phone">Agregar Teléfono</button>

        <h3>Redes Sociales</h3>
        <div id="social-container">
            <input type="text" name="social_media[]" placeholder="Red social (URL)" />
        </div>
        <button type="button" id="add-social">Agregar Red Social</button>

        <button type="button" class="prev-step">Anterior</button>
        <button type="submit" name="add_company_and_address">Enviar</button>
    </div>
</form>

<script>
    // Manejo de pasos dinámico
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".next-step").forEach(function (button) {
            button.addEventListener("click", function () {
                const currentStep = this.closest(".form-step");
                const nextStep = currentStep.nextElementSibling;

                if (nextStep) {
                    currentStep.style.display = "none";
                    nextStep.style.display = "block";
                }
            });
        });

        document.querySelectorAll(".prev-step").forEach(function (button) {
            button.addEventListener("click", function () {
                const currentStep = this.closest(".form-step");
                const prevStep = currentStep.previousElementSibling;

                if (prevStep) {
                    currentStep.style.display = "none";
                    prevStep.style.display = "block";
                }
            });
        });

        // Agregar más números de teléfono
        document.getElementById('add-phone').addEventListener('click', function () {
            const container = document.getElementById('phone-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'phones[]';
            input.placeholder = 'Número de teléfono';
            container.appendChild(input);
        });

        // Agregar más redes sociales
        document.getElementById('add-social').addEventListener('click', function () {
            const container = document.getElementById('social-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'social_media[]';
            input.placeholder = 'Red social (URL)';
            container.appendChild(input);
        });
    });

    // Cargar subcategorías dinámicamente
    document.getElementById('categoria_estado').addEventListener('change', function () {
        const estadoId = this.value;
        const subcategoriaSelect = document.getElementById('subcategoria');

        subcategoriaSelect.innerHTML = '<option value="">-- Selecciona un municipio --</option>';

        if (!estadoId) return;

        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'obtener_subcategorias',
                parent_id: estadoId,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.length > 0) {
                    data.forEach((subcategoria) => {
                        const option = document.createElement('option');
                        option.value = subcategoria.id;
                        option.textContent = subcategoria.name;
                        subcategoriaSelect.appendChild(option);
                    });
                } else {
                    subcategoriaSelect.innerHTML =
                        '<option value="">No hay municipios disponibles</option>';
                }
            })
            .catch((error) => {
                console.error('Error al cargar los municipios:', error);
            });
    });

    // Inicializamos el mapa
    var map = L.map('map').setView([19.432608, -99.133209], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var marker;

    function searchPlace() {
        const searchBox = document.getElementById('searchBox').value;
        if (!searchBox) {
            alert("Por favor, ingresa un lugar para buscar.");
            return;
        }

        fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(searchBox)}&format=json&limit=1`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    alert("No se encontró el lugar. Intenta con otro término.");
                    return;
                }

                const place = data[0];
                const name = place.display_name;
                const lat = place.lat;
                const lon = place.lon;

                if (marker) map.removeLayer(marker);
                marker = L.marker([lat, lon]).addTo(map).bindPopup(name).openPopup();
                map.setView([lat, lon], 14);

                document.getElementById('placeName').value = name;
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lon;

                document.getElementById('displayName').innerText = name;
                document.getElementById('displayLat').innerText = lat;
                document.getElementById('displayLng').innerText = lon;
            })
            .catch(error => {
                console.error("Error al buscar el lugar:", error);
                alert("Ocurrió un error al buscar el lugar. Intenta nuevamente.");
            });
    }
</script>

</body>
</html>
