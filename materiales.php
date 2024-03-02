<?php
$title_page = 'Materiales | Compras';
//Menus Sidebar
$page = 'Materiales';
$separador = 'Compras';
require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}

//Metodo Get para Editar Material
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    // Realizar una consulta para obtener los datos del grupo con el ID proporcionado
    $sql = "SELECT * FROM productos WHERE ID_Producto = '{$db->escape($id)}'";
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

//Metodo Post para Crear Material
if (isset($_POST['Codigo'])) {
    $Codigo = $_POST['Codigo'];
    $Nombre = $_POST['Nombre'];
    //UpperCase
    $Nombre = strtoupper($Nombre);
    $Codigo = strtoupper($Codigo);
    $PrecioCompra = $_POST['PrecioCompra'];
    $StockMinimo = $_POST['StockMinimo'];
    $UnidadMedida = $_POST['UnidadMedida'];

    $query = "INSERT INTO productos (Codigo, Nombre, PrecioCompra, StockMinimo, UnidadMedidaID) VALUES ('{$Codigo}', '{$Nombre}', '{$PrecioCompra}', '{$StockMinimo}', '{$UnidadMedida}')";
    if ($db->query($query)) {
        $session->msg('s', "Material creado exitosamente");
        redirect('materiales.php', false);
    } else {
        $session->msg('d', 'Lo siento, registro falló.');
        redirect('materiales.php', false);
    }
}

//Metodo Post para Editar Material
if (isset($_POST['editar_material'])) {
    $id = $_POST['edit_id'];
    $Codigo = $_POST['edit_Codigo'];
    $Nombre = $_POST['edit_Nombre'];
    $Nombre = strtoupper($Nombre);
    $Codigo = strtoupper($Codigo);
    $PrecioCompra = $_POST['edit_PrecioCompra'];
    $StockMinimo = $_POST['edit_StockMinimo'];
    $UnidadMedida = $_POST['edit_UnidadMedida'];

    $query = "UPDATE productos SET Codigo = '{$Codigo}', Nombre = '{$Nombre}', PrecioCompra = '{$PrecioCompra}', StockMinimo = '{$StockMinimo}', UnidadMedidaID = '{$UnidadMedida}' WHERE ID_Producto = '{$id}'";
    if ($db->query($query)) {
        $session->msg('s', "Material actualizado exitosamente");
        redirect('materiales.php', false);
    } else {
        $session->msg('d', 'Lo siento, actualización falló.');
        redirect('materiales.php', false);
    }
}

//Metodo Post para Eliminar Material
if (isset($_POST['eliminar_material'])) {
    $id = $_POST['delete_id'];
    $query = "DELETE FROM productos WHERE ID_Producto = '{$id}'";
    if ($db->query($query)) {
        $session->msg('s', "Material eliminado exitosamente");
        redirect('materiales.php', false);
    } else {
        $session->msg('d', 'Lo siento, eliminación falló.');
        redirect('materiales.php', false);
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
                        <h1 class="h3 mb-3"><strong>Maestro de Materiales</strong> </h1>
                        <div class="col-auto ms-auto text-end mt-n1">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#creation_Material">Nuevo Material</button>
                        </div>
                    </div>

                    <!-- Tabla de Materiales -->
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title">Lista de Materiales</h5>
                                <div class="" id="botones"></div>
                            </div>
                        </div>
                        <div class="table px-3">
                            <table class="table mb-0 display table-striped" style="width:100%" id="Materiales">
                                <thead>
                                    <tr>
                                        <th class="text-center">°</th>
                                        <th class="text-center">SKU</th>
                                        <th class="text-center">Descripcion</th>
                                        <th class="text-center">Costo (mxn)</th>
                                        <th class="text-center">UMB</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $Productos = find_by_sql('SELECT p.*, u.Abreviatura FROM productos as p, unidadesmedida as u WHERE UnidadMedidaID = ID_UnidadMedida');
                                    foreach ($Productos as $Producto) :
                                    ?>
                                        <tr>
                                            <td class="text-center"><?php echo count_id(); ?></td>
                                            <td class="text-center"><?php echo remove_junk($Producto['Codigo']); ?></td>
                                            <td class="text-center"><?php echo remove_junk($Producto['Nombre']); ?></td>
                                            <td class="text-center">$ <?php echo remove_junk($Producto['PrecioCompra']); ?></td>
                                            <td class="text-center"><?php echo strtoupper(remove_junk($Producto['Abreviatura'])); ?></td>

                                            <td class="text-center" class="text-center">
                                                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#edit_material" data-id="<?php echo $Producto['ID_Producto']; ?>"> <i class="aling-middle" data-feather="edit"></i></button>
                                                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete_material" data-id="<?php echo $Producto['ID_Producto']; ?>"> <i class="aling-middle" data-feather="trash"></i></button>

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

    <!-- Modal Crear Material -->
    <div class="modal fade" id="creation_Material" tabindex="-1" aria-labelledby="creation_MaterialLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="creation_MaterialLabel">Nuevo Material</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="materiales.php" method="post">
                        <div class="row">
                            <div class="mb-3">
                                <label for="Codigo" class="form-label">SKU</label>
                                <input type="text" class="form-control" id="Codigo" name="Codigo" required>
                            </div>
                            <div class="mb-3">
                                <label for="Nombre" class="form-label">Descripcion</label>
                                <input type="text" class="form-control" id="Nombre" name="Nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="PrecioCompra" class="form-label">Precio de Compra</label>
                                <input type="number" class="form-control" id="PrecioCompra" name="PrecioCompra" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label for="StockMinimo" class="form-label">Stock Mínimo</label>
                                <input type="number" class="form-control" id="StockMinimo" name="StockMinimo" required>
                            </div>
                            <div class="mb-3">
                                <label for="UnidadMedida" class="form-label">Unidad de Medida</label>
                                <select class="form-select" id="UnidadMedida" name="UnidadMedida" required>
                                    <option value="">Selecciona una opción</option>
                                    <?php
                                    $Unidades = find_all('unidadesmedida');
                                    foreach ($Unidades as $Unidad) :
                                    ?>
                                        <option value="<?php echo $Unidad['ID_UnidadMedida']; ?>"><?php echo $Unidad['Abreviatura']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar Material</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Material -->
    <div class="modal fade" id="edit_material" tabindex="-1" aria-labelledby="edit_materialLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edit_materialLabel">Editar Material</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <div class="row">
                            <input type="hidden" id="edit_id" name="edit_id">
                            <div class="mb-3">
                                <label for="edit_Codigo" class="form-label">SKU</label>
                                <input type="text" class="form-control" id="edit_Codigo" name="edit_Codigo" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_Nombre" class="form-label">Descripcion</label>
                                <input type="text" class="form-control" id="edit_Nombre" name="edit_Nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_PrecioCompra" class="form-label">Precio de Compra</label>
                                <input type="number" class="form-control" id="edit_PrecioCompra" name="edit_PrecioCompra" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_StockMinimo" class="form-label">Stock Mínimo</label>
                                <input type="number" class="form-control" id="edit_StockMinimo" name="edit_StockMinimo" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_UnidadMedida" class="form-label">Unidad de Medida</label>
                                <select class="form-select" id="edit_UnidadMedida" name="edit_UnidadMedida" required>
                                    <option value="">Selecciona una opción</option>
                                    <?php
                                    $Unidades = find_all('unidadesmedida');
                                    foreach ($Unidades as $Unidad) :
                                    ?>
                                        <option value="<?php echo $Unidad['ID_UnidadMedida']; ?>"><?php echo $Unidad['Abreviatura']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary" name="editar_material">Guardar Material</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Material -->
    <div class="modal fade" id="delete_material" tabindex="-1" aria-labelledby="delete_materialLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete_materialLabel">Eliminar Material</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <input type="hidden" id="delete_id" name="delete_id">
                        <p>¿Estás seguro de que quieres eliminar este material?</p>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger" name="eliminar_material">Eliminar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <?php include_once('layouts/scripts.php'); ?>

    <script>
        //Cargar los datos del usuario a editar
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('edit_material');
            modal.addEventListener('show.bs.modal', function(event) {
                console.log("modal edit");
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
                            document.getElementById('edit_id').value = data.ID_Producto;
                            document.getElementById('edit_Codigo').value = data.Codigo;
                            document.getElementById('edit_Nombre').value = data.Nombre;
                            document.getElementById('edit_PrecioCompra').value = data.PrecioCompra;
                            document.getElementById('edit_StockMinimo').value = data.StockMinimo;
                            document.getElementById('edit_UnidadMedida').value = data.UnidadMedidaID;


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
            var modal = document.getElementById('delete_material');
            modal.addEventListener('show.bs.modal', function(event) {
                console.log("modal delete");
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
            var table = $('#Materiales').DataTable({
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