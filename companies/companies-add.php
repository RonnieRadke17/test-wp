<?php
    // Obtiene las categorías principales (estados)
    $categorias = obtener_categorias_principales();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_company'])) {
        // Datos de la compañía
        $company_data = array(
            'name'           => sanitize_text_field($_POST['company_name']),
            'description'    => sanitize_text_field($_POST['company_description']),
            'category_id'    => intval($_POST['categoria_estado']),
            'subcategory_id' => intval($_POST['subcategoria']),
        );
    
        // Datos de la dirección
        $address_data = array(
            'name'        => sanitize_text_field($_POST['placeName']),
            'latitude'    => floatval($_POST['latitude']),
            'longitude'   => floatval($_POST['longitude']),
        );
    
        // Teléfonos
        $phones = isset($_POST['phones']) ? array_map('sanitize_text_field', $_POST['phones']) : [];
    
        // Redes sociales
        $social_media = [];
        if (isset($_POST['social_media_names']) && isset($_POST['social_media_urls'])) {
            foreach ($_POST['social_media_names'] as $index => $name) {
                $url = $_POST['social_media_urls'][$index] ?? '';
                if (!empty($name) && !empty($url)) {
                    $social_media[] = [
                        'name' => sanitize_text_field($name),
                        'url'  => esc_url($url),
                    ];
                }
            }
        }
    
        // Horarios
        $schedules = isset($_POST['schedules']) ? array_map('sanitize_text_field', $_POST['schedules']) : [];
    
        // Imágenes
        $images = [];
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            // Iterar sobre las imágenes subidas
            foreach ($_FILES['images']['name'] as $index => $name) {
                // Validar archivo subido
                $tmp_name = $_FILES['images']['tmp_name'][$index];
                $size = $_FILES['images']['size'][$index];
                $error = $_FILES['images']['error'][$index];
                $type = $_FILES['images']['type'][$index];
    
                // Procesar archivo solo si no hay errores
                if ($error === UPLOAD_ERR_OK) {
                    $images[] = [
                        'name' => sanitize_file_name($name),
                        'tmp_name' => $tmp_name,
                        'size' => $size,
                        'type' => $type,
                    ];
                }
            }
        }
    
        // Llamada a la función add_company
        $errors = add_company($company_data, $address_data, $phones, $social_media, $schedules, $images);
    
        if ($errors) {
            foreach ($errors as $error) {
                echo '<p style="color: red;">' . esc_html($error) . '</p>';
            }
        } else {
            echo '<p style="color: green;">Empresa agregada exitosamente.</p>';
        }
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

<form id="company-form" method="POST" enctype="multipart/form-data">
    <!-- Paso 1: Información de la Compañía -->
    <div id="step-1" class="form-step">
        <h3>Información de la Compañía</h3>
        <label for="company_name">Nombre de la Compañía:</label><br>
        <input type="text" id="company_name" name="company_name" ><br><br>

        <label for="company_description">Descripción de la Compañía:</label><br>
        <input type="text" id="company_description" name="company_description" ><br><br>

        <label for="categoria_estado">Estado:</label><br>
        <select name="categoria_estado" id="categoria_estado" >
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

        <label for="subcategoria">Municipio:</label><br>
        <select name="subcategoria" id="subcategoria" >
            <option value="">-- Selecciona un municipio --</option>
        </select><br><br>

        <button type="button" class="next-step">Siguiente</button>
    </div>

    <!-- Paso 2: Dirección -->
    <div id="step-2" class="form-step" style="display:none;">
        <h3>Dirección</h3>
        <div>
            <input type="text" id="searchBox" placeholder="Buscar un lugar..." style="width: 300px;" />
            <button type="button" onclick="searchPlace()">Buscar</button>
        </div>
        <div id="map"></div>

        <div class="form-container">
            <input type="hidden" id="placeName" name="placeName" />
            <input type="hidden" id="latitude" name="latitude" />
            <input type="hidden" id="longitude" name="longitude" />

            <p><strong>Nombre:</strong> <span id="displayName"></span></p>
            <p><strong>Latitud:</strong> <span id="displayLat"></span></p>
            <p><strong>Longitud:</strong> <span id="displayLng"></span></p>
        </div>

        <button type="button" class="prev-step">Anterior</button>
        <button type="button" class="next-step">Siguiente</button>
    </div>

    <!-- Paso 3: Teléfonos -->
    <div id="step-3" class="form-step" style="display:none;">
        <h3>Teléfonos</h3>
        <div id="phone-container">
            <input type="text" name="phones[]" placeholder="Número de teléfono" />
        </div>
        <button type="button" id="add-phone">Agregar Teléfono</button><br><br>

        <button type="button" class="prev-step">Anterior</button>
        <button type="button" class="next-step">Siguiente</button>
    </div>

    <!-- Paso 4: Redes Sociales -->
    <div id="step-4" class="form-step" style="display:none;">
        <h3>Redes Sociales</h3>
        <div id="social-container">
            <div class="social-group">
                <!-- Selector dinámico para los nombres de redes sociales -->
                <select name="social_media_names[]">
                    <option value="">Selecciona una red social</option>
                    <?php
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'social_names';
                    $social_names = $wpdb->get_results("SELECT id, name FROM $table_name", ARRAY_A);

                    foreach ($social_names as $social_name) {
                        echo '<option value="' . esc_attr($social_name['id']) . '">' . esc_html($social_name['name']) . '</option>';
                    }
                    ?>
                </select>
                <input type="text" name="social_media_urls[]" placeholder="URL de la red social" />
            </div>
        </div>
        <button type="button" id="add-social">Agregar Red Social</button><br><br>

        <button type="button" class="prev-step">Anterior</button>
        <button type="button" class="next-step">Siguiente</button>
    </div>

    <!-- Paso 5: Seleccionar Horario -->
    <div id="step-5" class="form-step" style="display:none;">
        <h3>Seleccionar Horarios</h3>
        <div id="horarios-container">
            <div class="horario-group">
                <label for="horario">Horario disponible:</label><br>
                <select name="schedules[]" class="horario-select" required>
                    <option value="">-- Selecciona un horario --</option>
                    <?php if (!empty($horarios)) : ?>
                        <?php foreach ($horarios as $horario) : ?>
                            <option value="<?php echo esc_attr($horario['horario_id']); ?>">
                                <?php echo esc_html($horario['dia'] . ': ' . $horario['start_time'] . ' - ' . $horario['end_time']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <option value="">No hay horarios disponibles</option>
                    <?php endif; ?>
                </select>
                <button type="button" class="remove-horario">Eliminar</button>
            </div>
        </div>
        <button type="button" id="add-horario">Agregar Horario</button><br><br>
        <button type="button" class="prev-step">Anterior</button>
        <button type="button" class="next-step">Siguiente</button>
    </div>

    <!-- Paso 6: multimedia -->
    <div id="step-6" class="form-step" style="display:none;">
        <h3>Imágenes</h3>
        <div id="images-container">
            <label for="imagen">Seleccionar imágenes:</label>
            <!-- Atributo "multiple" permite seleccionar varias imágenes -->
            <input type="file" id="imagen" name="images[]" accept="image/jpeg, image/png, image/gif" multiple required>
            <small>Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB. Dimensiones máximas: 1920x1080.</small>
        </div>
        <button type="button" class="prev-step">Anterior</button>
        <button type="submit" name="add_company">Enviar</button>

    </div>


</form>

<!-- horarios -->
 <script>
    document.addEventListener("DOMContentLoaded", function () {
    // Botón para agregar horarios
    const addHorarioButton = document.getElementById('add-horario');
    const horariosContainer = document.getElementById('horarios-container');

    addHorarioButton.addEventListener('click', function () {
        // Crear un nuevo contenedor de horario
        const horarioGroup = document.createElement('div');
        horarioGroup.classList.add('horario-group');

        // Contenido del nuevo horario
        horarioGroup.innerHTML = `
            
            <select name="schedules[]" class="horario-select" required>
                <option value="">-- Selecciona un horario --</option>
                <?php if (!empty($horarios)) : ?>
                    <?php foreach ($horarios as $horario) : ?>
                        <option value="<?php echo esc_attr($horario['horario_id']); ?>">
                            <?php echo esc_html($horario['dia'] . ': ' . $horario['start_time'] . ' - ' . $horario['end_time']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else : ?>
                    <option value="">No hay horarios disponibles</option>
                <?php endif; ?>
            </select>
            <button type="button" class="remove-horario">Eliminar</button>
        `;

        // Agregar el nuevo grupo al contenedor
        horariosContainer.appendChild(horarioGroup);

        // Manejar la eliminación de este horario
        const removeButton = horarioGroup.querySelector('.remove-horario');
        removeButton.addEventListener('click', function () {
            horarioGroup.remove();
        });
    });

    // Manejar la eliminación de los horarios iniciales
    horariosContainer.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-horario')) {
            e.target.parentElement.remove();
        }
    });
});

 </script>


<!-- script de pasos dinamicos  -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
    // Manejo de pasos dinámico
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
    });

</script>

<!-- elementos dinamicos del dom(numeros, redes sociales) -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
    // Agregar más números de teléfono
        document.getElementById('add-phone').addEventListener('click', function () {
            const container = document.getElementById('phone-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'phones[]';
            input.placeholder = 'Número de teléfono';
            container.appendChild(input);
        });

        
        document.getElementById('add-social').addEventListener('click', function() {
            const container = document.getElementById('social-container');
            const newGroup = document.createElement('div');
            newGroup.classList.add('social-group');

            newGroup.innerHTML = `
                <select name="social_media_names[]">
                    <option value="">Selecciona una red social</option>
                    <?php foreach ($social_names as $social_name): ?>
                        <option value="<?php echo esc_attr($social_name['id']); ?>"><?php echo esc_html($social_name['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="social_media_urls[]" placeholder="URL de la red social" />
            `;

            container.appendChild(newGroup);
        });

    });

</script>

<!-- carga dinamica de categorias y subcategorias -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
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
    });

</script>

<!-- mapa -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
    // Inicializamos el mapa
    var map = L.map('map').setView([19.432608, -99.133209], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var marker;

    // Función para buscar un lugar
    window.searchPlace = function () {
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
        };
    });

</script>



<!-- <script>
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
</script> -->

</body>
</html>
