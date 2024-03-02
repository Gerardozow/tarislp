<?php
$title_page = 'Proveedores | Compras';
//Menus Sidebar
$page = 'Proveedores';
$separador = 'Compras';
require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}


//Datos de Tablas SQL
$proveedores = find_all('proveedores');

//Metodo GET para Editar Proveedor
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    // Realizar una consulta para obtener los datos del grupo con el ID proporcionado
    $sql = "SELECT * FROM proveedores WHERE ID_Proveedor = '{$db->escape($id)}'";
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


//Metodo POST para Crear Proveedor
if (isset($_POST['crear_proveedor'])) {
    $req_fields = array('nombre', 'contacto', 'telefono', 'correo');
    validate_fields($req_fields);
    $nombre = remove_junk($db->escape($_POST['nombre']));
    $contacto = remove_junk($db->escape($_POST['contacto']));
    $telefono = remove_junk($db->escape($_POST['telefono']));
    //Agregar guiones al telefono ej. 4442812958 -> 444-2812-958
    $telefono = substr($telefono, 0, 3) . '-' . substr($telefono, 3, 3) . '-' . substr($telefono, 6, 10);
    $correo = remove_junk($db->escape($_POST['correo']));

    if (empty($errors)) {
        $sql = "INSERT INTO proveedores (Nombre, Contacto, Telefono, Correo) VALUES ('{$nombre}', '{$contacto}', '{$telefono}', '{$correo}')";
        if ($db->query($sql)) {
            $session->msg('s', "Proveedor creado exitosamente! ");
            redirect('proveedores.php', false);
        } else {
            $session->msg('d', 'Lo siento, registro falló.');
            redirect('proveedores.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('proveedores.php', false);
    }
}

//Metodo POST para Editar Proveedor
if (isset($_POST['editar_proveedor'])) {
    $req_fields = array('edit_id', 'edit_nombre', 'edit_contacto', 'edit_telefono', 'edit_correo');
    validate_fields($req_fields);
    $id = remove_junk($db->escape($_POST['edit_id']));
    $nombre = remove_junk($db->escape($_POST['edit_nombre']));
    $contacto = remove_junk($db->escape($_POST['edit_contacto']));
    $telefono = remove_junk($db->escape($_POST['edit_telefono']));
    //Agregar guiones al telefono ej. 4442812958 -> 444-2812-958
    $telefono = substr($telefono, 0, 3) . '-' . substr($telefono, 3, 3) . '-' . substr($telefono, 6, 10);
    $correo = remove_junk($db->escape($_POST['edit_correo']));

    if (empty($errors)) {
        $sql = "UPDATE proveedores SET Nombre='{$nombre}', Contacto='{$contacto}', Telefono='{$telefono}', Correo='{$correo}' WHERE ID_Proveedor='{$id}'";
        $result = $db->query($sql);
        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "Proveedor actualizado exitosamente! ");
            redirect('proveedores.php', false);
        } else {
            $session->msg('d', 'Lo siento, actualización falló.');
            redirect('proveedores.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('proveedores.php', false);
    }
}

//Metodo para eliminar Proveedor
if (isset($_POST['eliminar_proveedor'])) {
    $id = remove_junk($db->escape($_POST['delete_id']));
    $proveedor = find_by_sql("SELECT * FROM proveedores WHERE ID_Proveedor = '{$id}'");
    if ($proveedor) {
        $sql = "DELETE FROM proveedores WHERE ID_Proveedor = '{$id}'";
        $result = $db->query($sql);
        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "Proveedor eliminado exitosamente! ");
            redirect('proveedores.php', false);
        } else {
            $session->msg('d', 'Lo siento, eliminación falló.');
            redirect('proveedores.php', false);
        }
    } else {
        $session->msg('d', 'El proveedor no existe con el ID proporcionado.');
        redirect('proveedores.php', false);
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
                        <h1 class="h3 mb-3"><strong>Proveedores</strong> Compras</h1>
                        <div class="col-auto ms-auto text-end mt-n1">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#creation_proveedor">Nuevo Proveedor</button>
                        </div>
                    </div>

                    <!-- Tabla de proveedores -->

                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title">Lista de Proveedores</h5>
                                <div class="" id="botones"></div>
                            </div>
                        </div>
                        <div class="table px-3">
                            <table class="table mb-0 display table-striped" style="width:100%" id="proveedores">
                                <thead>
                                    <tr>
                                        <th class="text-center">°</th>
                                        <th class="text-center">Proveedor</th>
                                        <th class="text-center">Contacto</th>
                                        <th class="text-center">Telefono</th>
                                        <th class="text-center">Email</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    foreach ($proveedores as $proveedor) :
                                    ?>
                                        <tr>
                                            <td class="text-center"><?php echo count_id(); ?></td>
                                            <td class="text-center"><?php echo remove_junk($proveedor['Nombre']); ?></td>
                                            <td class="text-center"><?php echo remove_junk($proveedor['Contacto']); ?></td>
                                            <td class="text-center"><?php echo remove_junk($proveedor['Telefono']); ?></td>
                                            <td class="text-center"><?php echo remove_junk($proveedor['Correo']); ?></td>
                                            <td class="text-center" class="text-center">
                                                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#edit_proveedor" data-id="<?php echo $proveedor['ID_Proveedor']; ?>"> <i class="aling-middle" data-feather="edit"></i></button>
                                                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete_proveedor" data-id="<?php echo $proveedor['ID_Proveedor']; ?>"> <i class="aling-middle" data-feather="trash"></i></button>
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


    <!-- Modal Crear Proveedor -->
    <div class="modal fade" id="creation_proveedor" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="creation_proveedorLabel">Nuevo Proveedor</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Proveedor</label>
                            <input type="text" class="form-control form-control-lg" id="nombre" name="nombre" placeholder="Nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="contacto" class="form-label">Contacto</label>
                            <input type="text" class="form-control form-control-lg" id="contacto" name="contacto" placeholder="Juan Perez" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Telefono</label>
                            <input type="text" class="form-control form-control-lg" id="telefono" name="telefono" placeholder="1234567890" required>
                        </div>
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo</label>
                            <input type="email" class="form-control form-control-lg" id="correo" name="correo" placeholder="ejemplo@example.com" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary" name="crear_proveedor">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Material -->
    <div class="modal fade" id="delete_proveedor" tabindex="-1" aria-labelledby="delete_proveedorLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete_proveedorLabel">Eliminar Material</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" id="delete_id" name="delete_id">
                        <p>¿Estás seguro de que quieres eliminar este material?</p>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger" name="eliminar_proveedor">Eliminar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Editar Proveedor -->
    <div class="modal fade" id="edit_proveedor" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="edit_proveedorLabel">Editar Proveedor</h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control form-control-lg" id="edit_id" name="edit_id">
                        <div class="mb-3">
                            <label for="edit_nombre" class="form-label">Nombre del Proveedor</label>
                            <input type="text" class="form-control form-control-lg" id="edit_nombre" name="edit_nombre" placeholder="Nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_contacto" class="form-label">Contacto</label>
                            <input type="text" class="form-control form-control-lg" id="edit_contacto" name="edit_contacto" placeholder="Juan Perez" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_telefono" class="form-label">Telefono</label>
                            <input type="text" class="form-control form-control-lg" id="edit_telefono" name="edit_telefono" placeholder="1234567890" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_correo" class="form-label">Correo</label>
                            <input type="email" class="form-control form-control-lg" id="edit_correo" name="edit_correo" placeholder="example@example.com" requiered>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary" name="editar_proveedor">Guardar</button>
                        </div>
                </form>
            </div>
        </div>
    </div>





    <?php include_once('layouts/scripts.php'); ?>




    <script>
        //Cargar los datos del usuario a editar
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('edit_proveedor');
            modal.addEventListener('show.bs.modal', function(event) {
                console.log('Modal edit_proveedor abierto');
                var button = event.relatedTarget; // Botón que activó el modal
                var id = button.getAttribute('data-id');
                var ruta = window.location.href + '?edit=';

                // Ahora puedes usar el valor de 'id' para realizar una consulta y llenar el formulario
                // Ejemplo: realiza una petición AJAX para obtener los datos y luego llénalos en el formulario
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            //console.log(xhr.responseText);
                            var data = JSON.parse(xhr.responseText);

                            //Llenar los campos del formulario con los datos obtenidos
                            document.getElementById('edit_id').value = data.ID_Proveedor;
                            document.getElementById('edit_nombre').value = data.Nombre;
                            document.getElementById('edit_contacto').value = data.Contacto;
                            document.getElementById('edit_telefono').value = data.Telefono;
                            document.getElementById('edit_correo').value = data.Correo;



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

    <script>
        //Cargar el input hidden con el id del proveedor a eliminar
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('delete_proveedor');
            modal.addEventListener('show.bs.modal', function(event) {
                console.log('Modal delete abierto');
                var button = event.relatedTarget; // Botón que activó el modal
                var id = button.getAttribute('data-id');
                //console.log(id);
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
            var table = $('#proveedores').DataTable({
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