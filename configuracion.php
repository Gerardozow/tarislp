<?php
$title_page = 'Configuracion';
//Menus Sidebar
$page = 'configuracion';
require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}

/** Cargar todos los datos de las bases de datos. */
$user = find_by_id('users', $_SESSION['user_id']);
$biografy = find_by_sql("SELECT * FROM biografy WHERE id_user = '{$user['id']}' LIMIT 1");
$address = find_by_sql("SELECT * FROM user_address WHERE id_user = '{$user['id']}' LIMIT 1");

/** Ejecutar los metodos POST */
/** Validar si existe un metodo post */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    /** Validar si existe un metodo post con el nombre info_public */
    if (isset($_POST['info_public'])) {
        // Verifica si el campo 'file' está presente en la solicitud
        if (isset($_FILES["photo_file"]) && $_FILES["photo_file"]["error"] == 0) {

            $targetDir = "uploads/users/";
            $imageName = basename($_FILES["photo_file"]["name"]);
            $targetFile = $targetDir . $imageName;
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            // Verificar si es una imagen real
            $check = getimagesize($_FILES["photo_file"]["tmp_name"]);
            if ($check === false) {
                $uploadOk = 0;
            }

            // Verificar tamaño del archivo
            if ($_FILES["photo_file"]["size"] > 5000000) {
                $uploadOk = 0;
                echo "imagen pesada";
            }

            // Permitir solo ciertos formatos de imagen
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                $uploadOk = 0;
                echo "No es formato valido";
            }

            // Si el archivo ya existe, agregar un sufijo único al nombre del archivo
            $counter = 1;
            while (file_exists($targetFile)) {
                $imageName = pathinfo($_FILES["photo_file"]["name"], PATHINFO_FILENAME) . '_' . $counter . '.' . $imageFileType;
                $targetFile = $targetDir . $imageName;
                $counter++;
            }

            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["photo_file"]["tmp_name"], $targetFile)) {
                    // Actualizar la base de datos con el nombre del archivo
                    $userId = $_SESSION["user_id"];
                    $consulta = "UPDATE users SET image = '$imageName' WHERE id = '$userId'";
                    replace_image_profile($consulta);
                    $imageName;
                }
            }
        }


        $req_fields = array('inputUsername');
        validate_fields($req_fields);
        if (empty($errors)) {
            $username = remove_junk($db->escape($_POST['inputUsername']));
            /** Actualizar la biografia si es diferente */
            if ($biografy[0]['biografy'] != $_POST['inputBio']) {
                /** crear sentecia si no existe en la tabla */
                if ($biografy[0]['biografy'] == '') {
                    $sql = "INSERT INTO biografy (id_user, biografy) VALUES ('{$user['id']}', '{$_POST['inputBio']}')";
                } else {
                    $sql = "UPDATE biografy SET biografy='{$_POST['inputBio']}' WHERE id_user='{$user['id']}'";
                }
                $result = $db->query($sql);
                if ($result && $db->affected_rows() === 1) {
                    $session->msg('s', "Biografia actualizada exitosamente! ");
                } else {
                    $session->msg('d', ' Lo siento, actualización falló.');
                }
            }
            /** Validar que la informacion sea diferente*/
            if ($user['username'] != $username) {
                /** Validar que el Username no exista en la base de datos */
                $exist = find_by_sql("SELECT * FROM users WHERE username = '{$username}' AND id != '{$user['id']}'");
                if ($exist) {
                    $session->msg('d', ' El nombre de usuario ya existe en la base de datos, favor escoger otro.');
                    redirect('configuracion.php', false);
                }
                $sql = "UPDATE users SET username='{$username}' WHERE id='{$user['id']}'";
                $result = $db->query($sql);
                if ($result && $db->affected_rows() === 1) {
                    $session->msg('s', "Usuario actualizada exitosamente! ");
                    redirect('configuracion.php', false);
                } else {
                    $session->msg('d', ' Lo siento, actualización falló.');
                    redirect('configuracion.php', false);
                }
            } else {
                redirect('configuracion.php', false);
            }
        } else {
            $session->msg("d", $errors);
            redirect('configuracion.php', false);
        }
    }
    /** Validar si existe un metodo post con el nombre info_private */
    if (isset($_POST['info_private'])) {
        $req_fields = array('inputFirstName', 'inputLastName', 'inputEmail4');
        validate_fields($req_fields);
        if (empty($errors)) {
            $name = remove_junk($db->escape($_POST['inputFirstName']));
            $last_name = remove_junk($db->escape($_POST['inputLastName']));
            $email = remove_junk($db->escape($_POST['inputEmail4']));
            /** Validar que la informacion sea diferente*/
            if ($user['name'] != $name || $user['last_name'] != $last_name || $user['email'] != $email) {
                /** Validar que el Email no exista en la base de datos */
                $exist = find_by_sql("SELECT * FROM users WHERE email = '{$email}' AND id != '{$user['id']}'");
                if ($exist) {
                    $sql = "UPDATE users SET name='{$name}', last_name='{$last_name}' WHERE id='{$user['id']}'";
                    $session->msg('d', ' El email ya existe en la base de datos, favor escoger otro.');
                } else {
                    $sql = "UPDATE users SET name='{$name}', last_name='{$last_name}', email='{$email}' WHERE id='{$user['id']}'";
                }
                $result = $db->query($sql);
                if ($result && $db->affected_rows() === 1) {
                    $session->msg('s', "Información actualizada exitosamente! ");
                } else {
                    $session->msg('d', ' Lo siento, actualización falló.');
                }
            }
            /** Actualizar la direccion si es diferente */
            /** Validar Inputs */
            $calle = remove_junk($db->escape($_POST['inputAddress']));
            $colonia = remove_junk($db->escape($_POST['inputAddress2']));
            $ciudad = remove_junk($db->escape($_POST['inputCity']));
            $cp = remove_junk($db->escape($_POST['inputZip']));
            $estado = remove_junk($db->escape($_POST['inputState']));
            /** si $calle, $colonia, $ciudad, $cp estan vacionos hacer un if */
            if (!empty($calle) && !empty($colonia) && !empty($ciudad) && !empty($estado) && !empty($cp)) {
                //validar si no es la infomracion actual
                if ($address[0]['calle'] != $calle || $address[0]['colonia'] != $colonia || $address[0]['ciudad'] != $ciudad || $address[0]['id_estado'] != $ciudad || $address[0]['cp'] != $cp) {
                    /** crear sentecia si no existe en la tabla */
                    if ($address[0]['calle'] == '') {
                        $sql = "INSERT INTO user_address (id_user, calle, colonia, ciudad, id_estado, cp) VALUES ('{$user['id']}', '{$calle}', '{$colonia}', '{$ciudad}', '{$estado}', '{$cp}')";
                    } else {
                        $sql = "UPDATE user_address SET calle='{$calle}', colonia='{$colonia}', ciudad='{$ciudad}', id_estado='{$estado}', cp='{$cp}' WHERE id_user='{$user['id']}'";
                    }
                    $result = $db->query($sql);
                    if ($result && $db->affected_rows() === 1) {
                        $session->msg('s', "Dirección actualizada exitosamente! ");
                    } else {
                        $session->msg('d', ' Lo siento, actualización falló.');
                    }
                }
            }

            redirect('configuracion.php', false);
        } else {
            $session->msg("d", $errors);
            redirect('configuracion.php', false);
        }
    }
    /** Validar si existe un metodo post con el nombre change_password */
    if (isset($_POST['change_password'])) {
        $req_fields = array('inputPasswordCurrent', 'inputPasswordNew', 'inputPasswordNew2');
        validate_fields($req_fields);
        if (empty($errors)) {
            $current = remove_junk($db->escape($_POST['inputPasswordCurrent']));
            $new = remove_junk($db->escape($_POST['inputPasswordNew']));
            $new2 = remove_junk($db->escape($_POST['inputPasswordNew2']));
            if ($new !== $new2) {
                $session->msg('d', ' Nueva contraseña y confirmar contraseña no coinciden.');
                redirect('configuracion.php', false);
            }
            $h_pass = sha1($current);
            if ($h_pass === $user['password']) {
                $sql = "UPDATE users SET password='" . sha1($new) . "' WHERE id='{$user['id']}'";
                $result = $db->query($sql);
                if ($result && $db->affected_rows() === 1) {
                    $session->msg('s', "Contraseña actualizada exitosamente! ");
                    redirect('configuracion.php', false);
                } else {
                    $session->msg('d', ' Lo siento, actualización falló.');
                    redirect('configuracion.php', false);
                }
            } else {
                $session->msg('d', ' Contraseña actual incorrecta.');
                redirect('configuracion.php', false);
            }
        } else {
            $session->msg("d", $errors);
            redirect('configuracion.php', false);
        }
    }
}



include_once('layouts/head.php');

?>



<body>
    <div class="wrapper">
        <?php include_once('layouts/sidebar.php'); ?>
        <div class="main">
            <?php include_once('layouts/navbar.php'); ?>
            <!-- Contenedor main -->
            <main class="content">
                <div class="container-fluid p-0">
                    <?php echo display_msg($msg); ?>
                    <h1 class="h3 mb-3">Configuracion</h1>

                    <div class="row">
                        <div class="col-md-3 col-xl-2">

                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Configuración del perfil</h5>
                                </div>

                                <div class="list-group list-group-flush" role="tablist">
                                    <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#account" role="tab">
                                        Cuenta
                                    </a>
                                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#password" role="tab">
                                        Contraseña
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-9 col-xl-10">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="account" role="tabpanel">

                                    <div class="card">
                                        <div class="card-header">

                                            <h5 class="card-title mb-0">Información pública</h5>
                                        </div>
                                        <div class="card-body">
                                            <form method="POST" action="./configuracion.php" enctype="multipart/form-data">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="inputUsername">Username</label>
                                                            <input type="text" class="form-control" name="inputUsername" id="inputUsername" placeholder="Username" value="<?= $user['username'] ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label" for="inputBio">Biografia</label>
                                                            <textarea rows="2" class="form-control" name="inputBio" id="inputBio" placeholder="Cuéntanos algo sobre ti"><?php echo !empty($biografy[0]['biografy']) ? $biografy[0]['biografy'] : ''; ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4" id="profileContainer">
                                                        <div class="text-center">
                                                            <img alt="Charles Hall" src="
                                                            <?php

                                                            if ($user['image'] == '') {
                                                                echo './uploads/users/default.png';
                                                            } else {
                                                                echo './uploads/users/' . $user['image'];
                                                            } ?>
                                                            " class="rounded-circle img-responsive mt-2" width="128" height="128" />
                                                            <div class="mt-2">
                                                                <span id="uploadButton" class="btn btn-primary"><i data-feather="upload"></i> Cargar</span>
                                                                <input type="file" id="fileInput" style="display:none;" name="photo_file" />
                                                            </div>
                                                            <small>Para obtener mejores resultados, utilice una imagen de al menos 128px por 128px en formato .jpg</small>
                                                        </div>
                                                    </div>

                                                </div>

                                                <button type="submit" class="btn btn-primary" name="info_public">Guardar cambios</button>
                                            </form>

                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-header">

                                            <h5 class="card-title mb-0">Información privada</h5>
                                        </div>
                                        <div class="card-body">
                                            <form method="POST" action="./configuracion.php">
                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label" for="inputFirstName">Nombre</label>
                                                        <input type="text" class="form-control" id="inputFirstName" name="inputFirstName" placeholder="Nombre" value="<?= $user['name'] ?>">
                                                    </div>
                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label" for="inputLastName">Apellidos</label>
                                                        <input type="text" class="form-control" id="inputLastName" name="inputLastName" placeholder="Apellidos" value="<?= $user['last_name'] ?>">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="inputEmail4">Email</label>
                                                    <input type="email" class="form-control" id="inputEmail4" name="inputEmail4" placeholder="Email" value="<?= $user['email'] ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="inputAddress">Calle</label>
                                                    <input type="text" class="form-control" id="inputAddress" name="inputAddress" placeholder="Avenida Obregon #122" value="<?php echo !empty($address[0]['calle']) ? $address[0]['calle'] : ''; ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="inputAddress2">Colonia</label>
                                                    <input type="text" class="form-control" id="inputAddress2" name="inputAddress2" placeholder="Valles del Sur" value="<?php echo !empty($address[0]['colonia']) ? $address[0]['colonia'] : ''; ?>">
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label" for="inputCity">Ciudad</label>
                                                        <input type="text" class="form-control" id="inputCity" name="inputCity" placeholder="San Luis Potosi" value="<?php echo !empty($address[0]['ciudad']) ? $address[0]['ciudad'] : ''; ?>">
                                                    </div>
                                                    <div class="mb-3 col-md-4">
                                                        <label class="form-label" for="inputState">Estado</label>
                                                        <select id="inputState" class="form-control" name="inputState">
                                                            <?php
                                                            $states = find_by_sql("SELECT * FROM estados");
                                                            $selected  = $address[0]['id_estado'] ? $address[0]['id_estado'] : '';

                                                            echo "<option value='' disabled selected>Selecciona</option>";
                                                            foreach ($states as $state) {
                                                                if ($selected == $state['id']) {
                                                                    echo "<option value='{$state['id']}' selected>{$state['estado']}</option>";
                                                                    continue;
                                                                } else {
                                                                    echo "<option value='{$state['id']}'>{$state['estado']}</option>";
                                                                }
                                                            }
                                                            ?>

                                                        </select>
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label" for="inputZip">CP</label>
                                                        <input type="text" class="form-control" id="inputZip" name="inputZip" placeholder="78000" value="<?php echo !empty($address[0]['cp']) ? $address[0]['cp'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary" name="info_private">Guardar cambios</button>
                                            </form>

                                        </div>
                                    </div>

                                </div>
                                <div class="tab-pane fade" id="password" role="tabpanel">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Contraseña</h5>

                                            <form method="POST" action="./configuracion.php" id="form_password">
                                                <div class="mb-3">
                                                    <label class="form-label" for="inputPasswordCurrent">Contraseña Actual</label>
                                                    <input type="password" class="form-control" id="inputPasswordCurrent" name="inputPasswordCurrent">
                                                    <!--<small><a href="#">¿Ha olvidado su contraseña?</a></small> -->
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="inputPasswordNew">Nueva Contraseña</label>
                                                    <input type="password" class="form-control" id="inputPasswordNew" name="inputPasswordNew">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="inputPasswordNew2">Verificar contraseña</label>
                                                    <input type="password" class="form-control" id="inputPasswordNew2" name="inputPasswordNew2">
                                                </div>
                                                <button type="submit" class="btn btn-primary" name="change_password">Guardar cambios</button>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>

            <!-- Fin Contenedor main -->
            <?php include_once('layouts/footer.php'); ?>

        </div>
    </div>
    <script>
        document.getElementById('uploadButton').addEventListener('click', function() {
            document.getElementById('fileInput').click();
        });

        document.getElementById('fileInput').addEventListener('change', function() {
            loadImagePreview(this);
        });

        function loadImagePreview(input) {
            var file = input.files[0];

            if (file) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    var imageContainer = document.getElementById('profileContainer').getElementsByTagName('img')[0];
                    imageContainer.src = e.target.result;
                };

                reader.readAsDataURL(file);
                console.log('Imagen cargada:', file.name);
            } else {
                console.log('Ningún archivo seleccionado');
            }
        }
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener referencias a los elementos del formulario
            var form = document.getElementById('form_password');
            var password1 = document.getElementById('inputPasswordNew');
            var password2 = document.getElementById('inputPasswordNew2');

            // Agregar un evento de escucha para el envío del formulario
            form.addEventListener('submit', function(event) {
                // Verificar la longitud de las contraseñas
                if (password1.value.length < 6 || password2.value.length < 6) {
                    // Aplicar estilo Bootstrap para resaltar el error
                    password1.classList.add('is-invalid');
                    password2.classList.add('is-invalid');

                    alert('Las contraseñas deben tener al menos 6 caracteres.');
                    event.preventDefault(); // Evitar el envío del formulario
                }

                // Verificar si las contraseñas coinciden
                else if (password1.value !== password2.value) {
                    // Aplicar estilo Bootstrap para resaltar el error
                    password1.classList.add('is-invalid');
                    password2.classList.add('is-invalid');

                    alert('Las contraseñas no coinciden.');
                    event.preventDefault(); // Evitar el envío del formulario
                }
            });

            // Agregar eventos para quitar la clase de estilo cuando el usuario comienza a escribir
            password1.addEventListener('input', function() {
                password1.classList.remove('is-invalid');
            });

            password2.addEventListener('input', function() {
                password2.classList.remove('is-invalid');
            });
        });
    </script>
    <?php include_once('layouts/scripts.php'); ?>
</body>

</html>