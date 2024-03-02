<?php
$title_page = 'Usuarios';
//Menus Sidebar
$page = 'Usuarios';
$separador = 'Usuarios';

require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}


page_require_level_access(1);

$grupos = find_by_sql("SELECT * FROM user_groups");

// Método Get para editar usuarios
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    // Realizar una consulta para obtener los datos del grupo con el ID proporcionado
    $sql = "SELECT u.username, u.name, u.last_name, u.email, u.creation, u.user_level,  g.id as id_g FROM users as u, user_groups as g WHERE u.user_level = g.group_level and u.id='{$db->escape($id)}'";
    $address = find_by_sql($sql);

    // Verificar si se obtuvieron resultados
    if (!empty($address)) {
        // Imprimir los datos como un objeto JSON
        header('Content-Type: application/json');
        echo json_encode($address[0]);
        exit; // Terminar la ejecución del script PHP después de enviar los datos
    } else {
        // Manejar el caso en que no se encuentren datos para el ID proporcionado
        // Puedes redirigir a una página de error o mostrar un mensaje apropiado
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No se encontraron datos para el ID proporcionado.']);
        exit; // Terminar la ejecución del script PHP después de enviar el mensaje de error
    }
}


// Método Post para agregar usuarios
if (isset($_POST['add_user'])) {
    $req_fields = array('user', 'name', 'last_name', 'email', 'rol', 'password', 'confirm-password');
    validate_fields($req_fields);

    //validar la que la contraseña este confirmada
    if (password_validate('password', 'confirm-password') != null) {
        $errors = password_validate('password', 'confirm-password');
    }

    if (empty($errors)) {
        $user = remove_junk($db->escape($_POST['user']));
        $name = remove_junk($db->escape($_POST['name']));
        $last_name = remove_junk($db->escape($_POST['last_name']));
        $email = remove_junk($db->escape($_POST['email']));
        $user_level = intval(remove_junk($db->escape($_POST['rol'])));
        $password = remove_junk($db->escape($_POST['password']));
        $password = sha1($password);
        $query = "INSERT INTO users (";
        $query .= "username, name, last_name, email, user_level, password";
        $query .= ") VALUES (";
        $query .= " '{$user}', '{$name}', '{$last_name}', '{$email}', $user_level, '{$password}'";
        $query .= ")";
        $result = $db->query($query);
        //detectar si la consulta fue exitosa y no hubo errores

        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "Cuenta creada exitosamente! ");
            redirect('usuarios.php', false);
        } else {
            $session->msg('d', ' Lo siento, registro falló.');
            redirect('usuarios.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('usuarios.php', false);
    }
}


// Método Post para editar usuarios
if (isset($_POST['edit_user_confirm'])) {
    $req_fields = array('edit_user', 'edit_name', 'edit_last_name', 'edit_email', 'edit_rol');
    validate_fields($req_fields);



    if (empty($errors)) {
        $id = (int)$_POST['edit_id'];
        $user = remove_junk($db->escape($_POST['edit_user']));
        $name = remove_junk($db->escape($_POST['edit_name']));
        $last_name = remove_junk($db->escape($_POST['edit_last_name']));
        $email = remove_junk($db->escape($_POST['edit_email']));
        $user_level = intval(remove_junk($db->escape($_POST['edit_rol'])));
        $password = remove_junk($db->escape($_POST['edit_password']));
        $password = sha1($password);
        $query = "UPDATE users SET ";
        $query .= "username='{$user}', name='{$name}', last_name='{$last_name}', email='{$email}', user_level={$user_level}";
        if ($password != '') {
            $query .= ", password='{$password}'";
        }
        $query .= " WHERE id='{$id}'";
        $result = $db->query($query);
        //detectar si la consulta fue exitosa y no hubo errores

        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "Cuenta actualizada exitosamente! ");
            redirect('usuarios.php', false);
        } else {
            $session->msg('d', ' Lo siento, actualización falló.');
            redirect('usuarios.php', false);
        }
    } else {
        debug($errors);
        $session->msg("d", $errors);
        redirect('usuarios.php', false);
    }
}

// Método Post para eliminar usuarios
if (isset($_POST['delete_user_confirm'])) {
    $id = (int)$_POST['delete_id'];
    $query = "DELETE FROM users WHERE id='{$id}'";
    $result = $db->query($query);
    if ($result && $db->affected_rows() === 1) {
        $session->msg('s', "Usuario eliminado exitosamente! ");
        redirect('usuarios.php', false);
    } else {
        $session->msg('d', ' Lo siento, eliminación falló.');
        redirect('usuarios.php', false);
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
                    <div class="row mb-2 mb-xl-3">
                        <div class="col-auto">
                            <h3>Usuarios</h3>
                        </div>

                        <div class="col-auto ms-auto text-end mt-n1">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#creation_user">Nuevo Usuario</button>
                        </div>
                    </div>
                    <!-- Tabla de Clientes -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title">Usuarios Registrados</h5>
                                    <div class="" id="botones"></div>
                                </div>
                            </div>
                            <div class="table px-3">
                                <table class="table mb-0 display table-striped" style="width:100%" id="users">
                                    <thead>
                                        <tr>
                                            <th>°</th>
                                            <th>Username</th>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Rol</th>
                                            <th>Fecha de Creacion</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                        $users = find_by_sql("SELECT u.*, g.group_name FROM users as u, user_groups as g WHERE u.user_level = g.group_level ORDER BY u.id");
                                        foreach ($users as $user) :
                                        ?>
                                            <tr>
                                                <td><?php echo count_id(); ?></td>

                                                <td><?php echo remove_junk($user['username']); ?></td>
                                                <td><?php echo remove_junk($user['name'] . " " . $user['last_name']); ?></td>
                                                <td><?php echo remove_junk($user['email']); ?></td>
                                                <td><?php echo remove_junk($user['group_name']); ?></td>
                                                <td><?php echo remove_junk($user['creation']); ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#edit_user_modal" data-id="<?php echo $user['id']; ?>"> <i class="aling-middle" data-feather="eye"></i></button>
                                                    <?php if ($_SESSION['user_id'] !== $user['id']) : ?>
                                                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete_user" data-id="<?php echo $user['id']; ?>"><i class="align-middle" data-feather="trash-2">Eliminar</i></button>
                                                    <?php else : ?>
                                                        <button class="btn btn-danger" disabled><i class="align-middle" data-feather="trash-2">Eliminar</i></button>
                                                    <?php endif; ?>

                                                </td>
                                            </tr>
                                        <?php endforeach; ?>


                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>

            <!-- Fin Contenedor main -->
            <?php include_once('layouts/footer.php'); ?>

        </div>
    </div>
    <!-- Modal crear ususario -->
    <div class="modal fade" id="creation_user" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Nuevo Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body mx-3">
                        <div class="mb-3">
                            <label class="form-label" for="user">Usuario</label>
                            <input class="form-control form-control-lg" type="text" name="user" id="user" placeholder="@" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="name">Nombre</label>
                            <input class="form-control form-control-lg" type="text" name="name" id="name" placeholder="Ingresa tu nombre" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="last_name">Apellidos</label>
                            <input class="form-control form-control-lg" type="text" name="last_name" id="last_name" placeholder="Ingresa tus Apellidos" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control form-control-lg" type="text" name="email" id="email" placeholder="email@example.com" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="rol"><strong>Rol</strong></label>
                            <select id="rol" class="form-control" name="rol" required>
                                <?php
                                echo "<option value='' disabled selected>Selecciona</option>";
                                foreach ($grupos as $grupo) {
                                    echo "<option value='{$grupo['group_level']}'>{$grupo['group_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Contraseña</label>
                            <input class="form-control form-control-lg" type="password" name="password" id="password" placeholder="Ingresa una contraseña" />
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="confirm-password">Confirma tu contraseña</label>
                            <input class="form-control form-control-lg" type="password" name="confirm-password" id="confirm-password" placeholder="Confirma tu contraseña" />
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <p class="text-start"><strong>Todos los Campos son obiligatorios</strong></p>
                        <div class="d-flex gap-2">
                            <button type="reset" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                            <button class="btn btn-success" type="submit" name="add_user">Registrar</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal editar usuario -->
    <div class="modal fade" id="edit_user_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body mx-3">
                        <input type="hidden" name="edit_id" id="edit_id" />
                        <div class="mb-3">
                            <label class="form-label" for="edit_user">Usuario*</label>
                            <input class="form-control form-control-lg" type="text" name="edit_user" id="edit_user" placeholder="@" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="edit_name">Nombre*</label>
                            <input class="form-control form-control-lg" type="text" name="edit_name" id="edit_name" placeholder="Ingresa tu nombre" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="edit_last_name">Apellidos*</label>
                            <input class="form-control form-control-lg" type="text" name="edit_last_name" id="edit_last_name" placeholder="Ingresa tus Apellidos" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="edit_email">Email*</label>
                            <input class="form-control form-control-lg" type="text" name="edit_email" id="edit_email" placeholder="email@example.com" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="edit_rol"><strong>Rol*</strong></label>
                            <select id="edit_rol" class="form-control" name="edit_rol" required>
                                <?php
                                echo "<option value='' disabled selected>Selecciona</option>";
                                foreach ($grupos as $grupo) {
                                    if ($selected == $grupo['group_level']) {
                                        echo "<option value='{$grupo['group_level']}' selected>{$grupo['group_name']}</option>";
                                    } else {
                                        echo "<option value='{$grupo['group_level']}'>{$grupo['group_name']}</option>";
                                    }
                                }
                                ?>

                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="edit_password">Contraseña</label>
                            <input class="form-control form-control-lg" type="password" name="edit_password" id="edit_password" placeholder="Ingresa una contraseña" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="edit_confirm-password">Confirma tu contraseña</label>
                            <input class="form-control form-control-lg" type="password" name="edit_confirm-password" id="edit_confirm-password" placeholder="Confirma tu contraseña" />
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <p class="text-start"><strong>*Campos obiligatorios</strong></p>
                        <div class="d-flex gap-2">
                            <button type="reset" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success" name="edit_user_confirm">Actualizar</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal eliminar usuario -->
    <div class="modal fade" id="delete_user" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Eliminar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body mx-3">
                        <input type="hidden" name="delete_id" id="delete_id" />
                        <p>¿Estás seguro de que quieres eliminar este usuario?</p>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success" name="delete_user_confirm">Eliminar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <?php include_once('layouts/scripts.php'); ?>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('edit_user_modal');
        modal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Botón que activó el modal
            var id = button.getAttribute('data-id'); // Obtener el valor del atributo data-id
            var ruta = window.location.href + '?edit=';
            console.log(ruta);

            // Ahora puedes usar el valor de 'id' para realizar una consulta y llenar el formulario
            // Ejemplo: realiza una petición AJAX para obtener los datos y luego llénalos en el formulario
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        //console.log(xhr.responseText);
                        var data = JSON.parse(xhr.responseText);

                        // Llena el formulario con los datos obtenidos
                        document.getElementById('edit_id').value = id;
                        document.getElementById('edit_user').value = data.username;
                        document.getElementById('edit_name').value = data.name;
                        document.getElementById('edit_last_name').value = data.last_name;
                        document.getElementById('edit_email').value = data.email;
                        //Selecionar el rol en el select
                        var userLevel = data.user_level;
                        document.getElementById('edit_rol').value = data.user_level;
                    } else {
                        console.error('Error en la petición AJAX:', xhr.statusText);
                    }
                }
            };
            xhr.open('GET', ruta + id, true);
            xhr.send();
        });
    });

    //Cargar el id del usuario a eliminar
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('delete_user');
        modal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Botón que activó el modal
            var id = button.getAttribute('data-id'); // Obtener el valor del atributo data-id
            // Llena el formulario con los datos obtenidos
            document.getElementById('delete_id').value = id;
        });
    });
</script>

<!-- Datatables -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-colvis-2.4.2/b-html5-2.4.2/b-print-2.4.2/sc-2.3.0/datatables.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#users').DataTable({
            paging: true,
            pageLength: 25,
            scrollCollapse: true,
            scrollY: '50vh',
            buttons: {
                buttons: [{
                        extend: 'copy',
                        text: 'Copiar',
                        className: 'btn btn-primary btn-sm'
                    },
                    {
                        extend: 'excel',
                        text: 'Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        className: 'btn btn-danger btn-sm'
                    },

                ]
            },
            language: {
                Search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros por página",
                zeroRecords: "No se encontraron resultados en su búsqueda",
                info: "Mostrando registros de _START_ al _END_ de un total de _TOTAL_ registros",
                infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
                infoFiltered: "(filtrado de un total de _MAX_ registros)",
                sSearch: "Buscar:",
                oPaginate: {
                    sFirst: "Primero",
                    sLast: "Último",
                    sNext: "Siguiente",
                    sPrevious: "Anterior",
                },

            },
        });

        table.buttons().container().appendTo('#botones');
    });
</script>
<style>
</style>

</html>