<?php
$title_page = 'Almacenes';
//Menus Sidebar
$page = 'Almacenes';
$separador = 'Almacen';
require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}

//Metodo Get para editar Almacen
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    // Realizar una consulta para obtener los datos del grupo con el ID proporcionado
    $sql = "SELECT * FROM almacenes WHERE ID_Almacen = '{$db->escape($id)}'";
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

//Metodo POST para agregar Almacen
if (isset($_POST['add_almacen'])) {
    $req_fields = array('CodigoAlmacen', 'NombreAlmacen', 'Descripcion');
    validate_fields($req_fields);
    if (empty($errors)) {
        $CodigoAlmacen = remove_junk($db->escape($_POST['CodigoAlmacen']));
        $NombreAlmacen = remove_junk($db->escape($_POST['NombreAlmacen']));
        $Descripcion = remove_junk($db->escape($_POST['Descripcion']));
        $query  = "INSERT INTO almacenes (";
        $query .= " CodigoAlmacen,NombreAlmacen,Descripcion";
        $query .= ") VALUES (";
        $query .= " '{$CodigoAlmacen}', '{$NombreAlmacen}', '{$Descripcion}'";
        $query .= ")";
        if ($db->query($query)) {
            $session->msg('s', "Almacen agregado exitosamente! ");
            redirect('almacenes.php', false);
        } else {
            $session->msg('d', ' Lo siento, registro falló.');
            redirect('almacenes.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('almacenes.php', false);
    }
}

//Metodo POST para editar Almacen
if (isset($_POST['editar_almacen'])) {
    $req_fields = array('edit_id', 'edit_Codigo', 'edit_Nombre', 'edit_Descripcion');
    validate_fields($req_fields);
    if (empty($errors)) {
        $id = remove_junk($db->escape($_POST['edit_id']));
        $CodigoAlmacen = remove_junk($db->escape($_POST['edit_Codigo']));
        $NombreAlmacen = remove_junk($db->escape($_POST['edit_Nombre']));
        $Descripcion = remove_junk($db->escape($_POST['edit_Descripcion']));
        $query  = "UPDATE almacenes SET ";
        $query .= "CodigoAlmacen='{$CodigoAlmacen}',NombreAlmacen='{$NombreAlmacen}',Descripcion='{$Descripcion}'";
        $query .= " WHERE ID_Almacen='{$id}'";
        $result = $db->query($query);
        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "Almacen actualizado exitosamente! ");
            redirect('almacenes.php', false);
        } else {
            $session->msg('d', ' Lo siento, actualización falló.');
            redirect('almacenes.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('almacenes.php', false);
    }
}

//Metodo POST para eliminar Almacen
if (isset($_POST['eliminar_almacen'])) {
    $id = remove_junk($db->escape($_POST['delete_id']));
    $query  = "DELETE FROM almacenes WHERE ID_Almacen = '{$id}'";
    if ($db->query($query)) {
        $session->msg('s', "Almacen eliminado exitosamente! ");
        redirect('almacenes.php', false);
    } else {
        $session->msg('d', ' Lo siento, eliminación falló.');
        redirect('almacenes.php', false);
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
                    <div class="d-flex">
                        <h1 class="h3 mb-3"><strong>Almacenes</strong></h1>
                        <div class="col-auto ms-auto text-end mt-n1">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#creation_Almacen">Agregar Almacen</button>
                        </div>
                    </div>
                    <!-- Tabla de Almacenes -->
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title">Alamacenes</h5>
                                <div class="" id="botones"></div>
                            </div>
                        </div>
                        <div class="table px-3">
                            <table class="table mb-0 display table-striped" style="width:100%" id="Almacenes">
                                <thead>
                                    <tr>
                                        <th class="text-center">°</th>
                                        <th class="text-center">Codigo</th>
                                        <th class="text-center">Almacen</th>
                                        <th class="text-center">Descripcion</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $Almacenes = find_by_sql('SELECT * FROM almacenes');
                                    foreach ($Almacenes as $Almacen) :
                                    ?>
                                        <tr>
                                            <td class="text-center"><?php echo count_id(); ?></td>
                                            <td class="text-center"><?php echo remove_junk($Almacen['CodigoAlmacen']); ?></td>
                                            <td class="text-center"><?php echo remove_junk($Almacen['NombreAlmacen']); ?></td>
                                            <td class="text-center"><?php echo remove_junk($Almacen['Descripcion']); ?></td>

                                            <td class="text-center" class="text-center">
                                                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#edit_almacen" data-id="<?php echo $Almacen['ID_Almacen']; ?>"> <i class="aling-middle" data-feather="edit"></i></button>
                                                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete_almacen" data-id="<?php echo $Almacen['ID_Almacen']; ?>"> <i class="aling-middle" data-feather="trash"></i></button>

                                            </td>
                                        </tr>
                                    <?php endforeach; ?>


                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Fin Contenedor main -->
            <?php include_once('layouts/footer.php'); ?>
        </div>
    </div>

    <!-- Modal Creacion de Almacen -->
    <div class="modal fade" id="creation_Almacen" tabindex="-1" aria-labelledby="creation_Almacen" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Agregar Almacen</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="CodigoAlmacen" class="form-label">Codigo Almacen</label>
                            <input type="text" class="form-control" id="CodigoAlmacen" name="CodigoAlmacen" required>
                        </div>
                        <div class="mb-3">
                            <label for="NombreAlmacen" class="form-label">Nombre Almacen</label>
                            <input type="text" class="form-control" id="NombreAlmacen" name="NombreAlmacen" required>
                        </div>
                        <div class="mb-3">
                            <label for="Descripcion" class="form-label">Descripcion</label>
                            <input type="text" class="form-control" id="Descripcion" name="Descripcion" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" name="add_almacen" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edicion de Almacen -->
    <div class="modal fade" id="edit_almacen" tabindex="-1" aria-labelledby="edit_almacen" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Editar Almacen</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="edit_id" value="">
                        <div class="mb-3">
                            <label for="edit_Codigo" class="form-label">Codigo Almacen</label>
                            <input type="text" class="form-control" id="edit_Codigo" name="edit_Codigo" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_Nombre" class="form-label">Nombre Almacen</label>
                            <input type="text" class="form-control" id="edit_Nombre" name="edit_Nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_Descripcion" class="form-label">Descripcion</label>
                            <input type="text" class="form-control" id="edit_Descripcion" name="edit_Descripcion" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" name="editar_almacen" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Almacen -->
    <div class="modal fade" id="delete_almacen" tabindex="-1" aria-labelledby="delete_almacen" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Eliminar Almacen</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body
                ">
                        <input type="hidden" name="delete_id" id="delete_id" value="">
                        <p>¿Estás seguro de que quieres eliminar este Almacen?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="eliminar_almacen" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <?php include_once('layouts/scripts.php'); ?>

    <script>
        //Cargar los datos del a editar
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('edit_almacen');
            modal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget; // Botón que activó el modal
                var id = button.getAttribute('data-id'); // Obtener el valor del atributo data-id
                console.log(id);
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

                            //Llenar los campos del formulario con los datos obtenidos
                            document.getElementById('edit_id').value = data.ID_Almacen;
                            document.getElementById('edit_Codigo').value = data.CodigoAlmacen;
                            document.getElementById('edit_Nombre').value = data.NombreAlmacen;
                            document.getElementById('edit_Descripcion').value = data.Descripcion;

                        } else {
                            console.error('Error en la petición AJAX:', xhr.statusText);
                        }
                    }
                };
                xhr.open('GET', ruta + id, true);
                xhr.send();
            });
        });

        //Cargar el id a eliminar
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('delete_almacen');
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
            var table = $('#Almacenes').DataTable({
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
</body>

</html>