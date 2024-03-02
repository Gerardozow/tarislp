<?php
$title_page = 'Clientes';
//Menus Sidebar
$page = 'cliente';
$separador = 'clientes';

require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}
//CONSULTAS
$states = find_by_sql("SELECT * FROM estados");
$grupos = find_by_sql("SELECT * FROM grupos");

// Método Get para editar un grupo
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    // Realizar una consulta para obtener los datos del grupo con el ID proporcionado
    $sql = "SELECT * FROM clientes WHERE id='{$db->escape($id)}'";
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

//Registro de Clientes metodo POST
if (isset($_POST['registro_cliente'])) {
    $req_fields = array('tienda', 'grupo', 'contacto', 'telefono');
    validate_fields($req_fields);
    if (empty($errors)) {
        $tienda = remove_junk($db->escape($_POST['tienda']));
        $grupo = remove_junk($db->escape($_POST['grupo']));
        $contacto = remove_junk($db->escape($_POST['contacto']));
        $direccion = remove_junk($db->escape($_POST['direccion']));
        $colonia = remove_junk($db->escape($_POST['colonia']));
        $ciudad = remove_junk($db->escape($_POST['ciudad']));
        $estado = remove_junk($db->escape($_POST['estado']));
        $zip = remove_junk($db->escape($_POST['zip']));
        $email = remove_junk($db->escape($_POST['email']));
        $telefono = remove_junk($db->escape($_POST['telefono']));
        $query  = "INSERT INTO clientes (";
        $query .= " tienda, id_grupo, contacto, direccion, colonia, ciudad, id_estado, cp, email, telefono";
        $query .= ") VALUES (";
        $query .= " '{$tienda}', '{$grupo}', '{$contacto}', '{$direccion}', '{$colonia}', '{$ciudad}', '{$estado}', '{$zip}', '{$email}', '{$telefono}'";
        $query .= ")";
        if ($db->query($query)) {
            //sucess
            $session->msg('s', "Cliente agregado exitosamente. ");
            redirect('clientes.php', false);
        } else {
            //failed
            $session->msg('d', ' Lo siento, registro falló.');
            redirect('clientes.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('clientes.php', false);
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
                            <h3>Clientes</h3>
                        </div>

                        <div class="col-auto ms-auto text-end mt-n1">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#creation_cliente">Nuevo Cliente</button>
                        </div>
                    </div>
                    <!-- Tabla de Clientes -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title">Registo de Clientes</h5>
                                    <div class="" id="botones"></div>
                                </div>
                            </div>
                            <div class="table px-3">
                                <table class="table mb-0 display table-striped" style="width:100%" id="clientes">
                                    <thead>
                                        <tr>
                                            <th>°</th>
                                            <th>Tienda</th>
                                            <th>Grupo</th>
                                            <th>Contacto</th>
                                            <th>Telefono</th>
                                            <th>Email</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                        $clientes = find_by_sql("SELECT c.*, g.grupo FROM clientes as c, grupos as g WHERE c.id_grupo = g.id");
                                        foreach ($clientes as $cliente) :
                                        ?>
                                            <tr>
                                                <td><?php echo count_id(); ?></td>
                                                <td><?php echo remove_junk($cliente['tienda']); ?></td>
                                                <td><?php echo remove_junk($cliente['grupo']); ?></td>
                                                <td><?php echo remove_junk($cliente['contacto']); ?></td>
                                                <td><?php echo remove_junk($cliente['telefono']); ?></td>
                                                <td><?php echo remove_junk($cliente['email']); ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#edit_cliente" data-id="<?php echo $cliente['id']; ?>"> <i class="aling-middle" data-feather="eye"></i></button>
                                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete_cliente" data-id="<?php echo $cliente['id']; ?>"><i class="align-middle" data-feather="trash-2">Eliminar</i></button>
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
    <!-- Modal crear cliente -->
    <div class="modal fade" id="creation_cliente" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Nuevo Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body m-3">

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="tienda"><strong>Tienda*</strong></label>
                                <input type="text" class="form-control" id="tienda" placeholder="Nombre del negocio" name="tienda" required>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="grupo"><strong>Grupo*</strong></label>
                                <select id="grupo" class="form-control" name="grupo" required>
                                    <?php
                                    $selected  = $address[0]['id_grupo'] ? $address[0]['id_grupo'] : '';
                                    echo "<option value='' disabled selected>Selecciona</option>";
                                    foreach ($grupos as $grupo) {
                                        if ($selected == $grupo['id']) {
                                            echo "<option value='{$grupo['id']}' selected>{$grupo['grupo']}</option>";
                                            continue;
                                        } else {
                                            echo "<option value='{$grupo['id']}'>{$grupo['grupo']}</option>";
                                        }
                                    }
                                    ?>

                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="contacto"><strong>Contacto*</strong></label>
                            <input type="text" class="form-control" id="contacto" placeholder="Contacto" name="contacto">
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
                                <label class="form-label" for="zip">CP</label>
                                <input type="text" class="form-control" id="zip" name="zip" placeholder="00000">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="example@example.com">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="telefono"><strong>Telefono*</strong></label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Telefono">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <p class="text-start"><strong>*Campos obiligatorios</strong></p>
                        <div class="d-flex gap-2">
                            <button type="reset" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary" name="registro_cliente">Registrar</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal editar cliente -->
    <div class="modal fade" id="edit_cliente" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body m-3">

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="tienda"><strong>Tienda*</strong></label>
                                <input type="text" class="form-control" id="e_tienda" placeholder="Nombre del negocio" name="tienda" required>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="grupo"><strong>Grupo*</strong></label>
                                <select id="e_grupo" class="form-control" name="grupo" required>
                                    <?php
                                    $selected  = $address[0]['id_grupo'] ? $address[0]['id_grupo'] : '';
                                    echo "<option value='' disabled selected>Selecciona</option>";
                                    foreach ($grupos as $grupo) {
                                        if ($selected == $grupo['id']) {
                                            echo "<option value='{$grupo['id']}' selected>{$grupo['grupo']}</option>";
                                            continue;
                                        } else {
                                            echo "<option value='{$grupo['id']}'>{$grupo['grupo']}</option>";
                                        }
                                    }
                                    ?>

                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="contacto"><strong>Contacto*</strong></label>
                            <input type="text" class="form-control" id="e_contacto" placeholder="Contacto" name="contacto">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="direccion">Direccion</label>
                            <input type="text" class="form-control" id="e_direccion" placeholder="Calle y numero" name="direccion">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="colonia">Colonia</label>
                            <input type="text" class="form-control" id="e_colonia" placeholder="Colonia" name="colonia">
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="ciudad">Ciudad</label>
                                <input type="text" class="form-control" id="e_ciudad" name="ciudad" placeholder="Ciudad">
                            </div>
                            <div class="mb-3 col-md-4">
                                <label class="form-label" for="estado"><strong>Estado*</strong></label>
                                <select id="e_inputState" class="form-control" name="estado" required>
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
                                <label class="form-label" for="zip">CP</label>
                                <input type="text" class="form-control" id="e_zip" name="zip" placeholder="00000">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control" id="e_email" name="email" placeholder="example@example.com">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="telefono"><strong>Telefono*</strong></label>
                                    <input type="tel" class="form-control" id="e_telefono" name="telefono" placeholder="Telefono">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <p class="text-start"><strong>*Campos obiligatorios</strong></p>
                        <div class="d-flex gap-2">
                            <button type="reset" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary" name="registro_cliente">Registrar</button>
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
        var modal = document.getElementById('edit_cliente');
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

<!-- Datatables -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-colvis-2.4.2/b-html5-2.4.2/b-print-2.4.2/sc-2.3.0/datatables.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#clientes').DataTable({
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