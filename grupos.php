<?php
$title_page = 'Clientes';
//Menus Sidebar
$page = 'grupo';
$separador = 'clientes';
require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}

//Consultas para rellenar informacion
$states = find_by_sql("SELECT * FROM estados");
$groups = find_by_sql("SELECT * FROM grupos");


// Método Get para editar un grupo
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    // Realizar una consulta para obtener los datos del grupo con el ID proporcionado
    $sql = "SELECT * FROM grupos WHERE id='{$db->escape($id)}'";
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
//metodo get para eliminar un grupo
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "DELETE FROM grupos WHERE id='{$db->escape($id)}'";
    if ($db->query($query)) {
        //sucess
        $session->msg('s', "Grupo eliminado exitosamente. ");
        redirect('grupos.php', false);
    } else {
        //failed
        $session->msg('d', 'Lo siento, registro falló.');
        redirect('grupos.php', false);
    }
}



// Metodo POST para registrar un nuevo grupo
if (isset($_POST['registro_grupo'])) {
    $req_fields = array('grupo', 'contacto', 'email', 'telefono');
    validate_fields($req_fields);

    if (empty($errors)) {
        $grupo = remove_junk($db->escape($_POST['grupo']));
        $contacto = remove_junk($db->escape($_POST['contacto']));
        $direccion = remove_junk($db->escape($_POST['direccion']));
        $colonia = remove_junk($db->escape($_POST['colonia']));
        $ciudad = remove_junk($db->escape($_POST['ciudad']));
        $estado = remove_junk($db->escape($_POST['estado']));
        $zip = remove_junk($db->escape($_POST['zip']));
        $email = remove_junk($db->escape($_POST['email']));
        $telefono = remove_junk($db->escape($_POST['telefono']));

        $query  = "INSERT INTO grupos (";
        $query .= "grupo, contacto, direccion, colonia, ciudad, id_estado, cp, email, telefono";
        $query .= ") VALUES (";
        $query .= "'{$grupo}', '{$contacto}', '{$direccion}', '{$colonia}', '{$ciudad}', '{$estado}', '{$zip}', '{$email}', '{$telefono}'";
        $query .= ")";
        if ($db->query($query)) {
            //sucess
            $session->msg('s', "Grupo agregado exitosamente. ");
            redirect('grupos.php', false);
        } else {
            //failed
            $session->msg('d', 'Lo siento, registro falló.');
            redirect('grupos.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('grupos.php', false);
    }
}

//Metodo POST para editar un grupo
if (isset($_POST['e_registro_grupo'])) {
    $req_fields = array('e_grupo', 'e_contacto', 'e_email', 'e_telefono');
    validate_fields($req_fields);

    if (empty($errors)) {
        $id = remove_junk($db->escape($_POST['e_id']));
        $grupo = remove_junk($db->escape($_POST['e_grupo']));
        $contacto = remove_junk($db->escape($_POST['e_contacto']));
        $direccion = remove_junk($db->escape($_POST['e_direccion']));
        $colonia = remove_junk($db->escape($_POST['e_colonia']));
        $ciudad = remove_junk($db->escape($_POST['e_ciudad']));
        $estado = remove_junk($db->escape($_POST['e_estado']));
        $zip = remove_junk($db->escape($_POST['e_zip']));
        $email = remove_junk($db->escape($_POST['e_email']));
        $telefono = remove_junk($db->escape($_POST['e_telefono']));

        $query  = "UPDATE grupos SET ";
        $query .= "grupo='{$grupo}', contacto='{$contacto}', direccion='{$direccion}', colonia='{$colonia}', ciudad='{$ciudad}', id_estado='{$estado}', cp='{$zip}', email='{$email}', telefono='{$telefono}'";
        $query .= "WHERE id='{$db->escape($id)}'";
        if ($db->query($query)) {
            //sucess
            $session->msg('s', "Grupo actualizado exitosamente. ");
            redirect('grupos.php', false);
        } else {
            //failed
            $session->msg('d', 'Lo siento, actualizacion falló.');
            redirect('grupos.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('grupos.php', false);
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
                            <h3>Grupos</h3>
                        </div>

                        <div class="col-auto ms-auto text-end mt-n1">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#coloredModalPrimary">Nuevo Grupo</button>
                        </div>
                    </div>
                    <!-- Tabla de Clientes -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title">Registo de Grupos</h5>
                                    <div class="" id="botones"></div>
                                </div>
                            </div>
                            <div class="table px-3">
                                <table class="table mb-0 display table-striped" style="width:100%" id="grupos">
                                    <thead>
                                        <tr>
                                            <th class="text-center">°</th>
                                            <th class="text-center">Grupo</th>
                                            <th class="text-center">Contacto</th>
                                            <th class="text-center">Telefono</th>
                                            <th class="text-center">Email</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($groups as $group) :
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo count_id(); ?></td>
                                                <td><?php echo $group['grupo']; ?></td>
                                                <td><?php echo $group['contacto']; ?></td>
                                                <td class="text-center"><a href="tel:<?php echo $group['telefono']; ?>"><?php echo $group['telefono']; ?></a></td>
                                                <td><a href="mailto:<?php echo $group['email']; ?>"><?php echo $group['email']; ?></a></td>
                                                <td class="text-center">
                                                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#edit_group" data-id="<?php echo $group['id']; ?>"> <i class="aling-middle" data-feather="eye"></i></button>
                                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete_group" data-id="<?php echo $group['id']; ?>"><i class="align-middle" data-feather="trash-2">Eliminar</i></button>
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
    <!-- Modal Nuevo Grupo -->
    <div class=" modal fade" id="coloredModalPrimary" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Grupo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body m-3">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="grupo"><strong>Grupo*</strong></label>
                                <input type="text" class="form-control" id="grupo" placeholder="Grupo" name="grupo" required>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="contacto"><strong>Contacto*</strong></label>
                                <input type="text" class="form-control" id="contacto" placeholder="Nombre del Contacto" name="contacto" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="direccion">Direccion</label>
                            <input type="text" class="form-control" id="direccion" placeholder="Calle y numero" name="direccion">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="colonia">Colonia</label>
                            <input type="text" class="form-control" id="colonia" placeholder="Colonia" name="colonia">
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="ciudad">Ciudad</label>
                                <input type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad">
                            </div>
                            <div class="mb-3 col-md-4">
                                <label class="form-label" for="estado"><strong>Estado*</strong></label>
                                <select id="inputState" class="form-control" name="estado" required>
                                    <?php
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
                                <label class="form-label" for="zip">CP</label>
                                <input type="text" class="form-control" id="zip" name="zip" placeholder="00000">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="email"><strong>Email*</strong></label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="example@example.com" required>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="telefono"><strong>Telefono*</strong></label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Telefono" required>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="modal-footer justify-content-between">
                        <p class="text-start"><strong>*Campos obiligatorios</strong></p>
                        <div class="d-flex gap-2">
                            <button type="reset" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary" name="registro_grupo">Registrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Fin Modal Nuevo Grupo -->

    <!-- Modal Editar Grupo -->
    <div class=" modal fade" id="edit_group" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Grupo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body m-3">
                        <input type="text" name="e_id" hidden id="e_id">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="e_grupo"><strong>Grupo*</strong></label>
                                <input type="text" class="form-control" id="e_grupo" placeholder="Grupo" name="e_grupo">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="e_contacto"><strong>Contacto*</strong></label>
                                <input type="text" class="form-control" id="e_contacto" placeholder="Nombre del Contacto" name="e_contacto">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="e_direccion">Direccion</label>
                            <input type="text" class="form-control" id="e_direccion" placeholder="Calle y numero" name="e_direccion">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="e_colonia">Colonia</label>
                            <input type="text" class="form-control" id="e_colonia" placeholder="Colonia" name="e_colonia">
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="e_ciudad">Ciudad</label>
                                <input type="text" class="form-control" id="e_ciudad" name="e_ciudad" placeholder="Ciudad">
                            </div>
                            <div class="mb-3 col-md-4">
                                <label class="form-label" for="e_estado"><strong>Estado*</strong></label>
                                <select id="e_inputState" class="form-control" name="e_estado">
                                    <?php

                                    //$selected  = $address[0]['id_estado'] ? $address[0]['id_estado'] : '';
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
                                <label class="form-label" for="e_zip">CP</label>
                                <input type="text" class="form-control" id="e_zip" name="e_zip" placeholder="00000">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="e_email"><strong>Email*</strong></label>
                                    <input type="email" class="form-control" id="e_email" name="e_email" placeholder="example@example.com">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="e_telefono"><strong>Telefono*</strong></label>
                                    <input type="tel" class="form-control" id="e_telefono" name="e_telefono" placeholder="Telefono">
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="modal-footer justify-content-between">
                        <p class="text-start"><strong>*Campos obiligatorios</strong></p>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" name="e_registro_grupo">Actualizar</button>
                            <button type="reset" class="btn btn-info" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmacion Eliminar -->
    <div class="modal fade" id="delete_group" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Grupo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body m-3">
                    <p>¿Realmente desea eliminar este grupo?, esta accion no se puede desahacer.</p>
                </div>
                <div class="modal-footer">
                    <!-- detectar el id de eliminacion -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var deleteModal = document.getElementById('delete_group');

                            deleteModal.addEventListener('show.bs.modal', function(event) {
                                var button = event.relatedTarget; // Botón que activó el modal
                                var id = button.getAttribute('data-id');

                                console.log(id);

                                // Corrección: Utiliza document.getElementById para obtener el elemento por su ID
                                var deleteLink = document.getElementById('delete_link');
                                deleteLink.href = "grupos.php?delete=" + id;
                            });
                        });
                    </script>
                    <a class="btn btn-danger" href="" id="delete_link"><i class="align-middle" data-feather="trash-2">Eliminar</i></a>
                    <button type="reset" class="btn btn-info" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('edit_group');
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
                            console.log(xhr.responseText);
                            var data = JSON.parse(xhr.responseText);

                            // Llena el formulario con los datos obtenidos
                            document.getElementById('e_id').value = data.id;
                            document.getElementById('e_grupo').value = data.grupo;
                            document.getElementById('e_contacto').value = data.contacto;
                            document.getElementById('e_direccion').value = data.direccion;
                            document.getElementById('e_colonia').value = data.colonia;
                            document.getElementById('e_ciudad').value = data.ciudad;
                            document.getElementById('e_zip').value = data.zip;
                            document.getElementById('e_email').value = data.email;
                            document.getElementById('e_telefono').value = data.telefono;
                            //Selecionar el estado en el select
                            document.getElementById('e_inputState').value = data.id_estado;
                        } else {
                            console.error('Error en la petición AJAX:', xhr.statusText);
                        }
                    }
                };
                xhr.open('GET', ruta + id, true);
                xhr.send();
            });
        });
    </script>


    <!-- Fin Modal Editar Grupo -->
    <?php include_once('layouts/scripts.php'); ?>
</body>

<!-- Datatables -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-colvis-2.4.2/b-html5-2.4.2/b-print-2.4.2/sc-2.3.0/datatables.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#grupos').DataTable({
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