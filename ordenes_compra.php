<?php
$title_page = 'Ordenes de Compra | Compras';
//Menus Sidebar
$page = 'OrdenesCompra';
$separador = 'Compras';
require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}

include_once('layouts/head.php');



//Metodo Post para Crear Orden de Compra
if (isset($_POST['crear_orden_compra'])) {
    $req_fields = array('Proveedor', 'FechaPedido', 'FechaEntregaEstimada', 'AlmacenEntrega');
    validate_fields($req_fields);
    if (empty($errors)) {
        $Proveedor = remove_junk($db->escape($_POST['Proveedor']));
        $FechaPedido = remove_junk($db->escape($_POST['FechaPedido']));
        $FechaEntregaEstimada = remove_junk($db->escape($_POST['FechaEntregaEstimada']));
        $AlmacenEntrega = remove_junk($db->escape($_POST['AlmacenEntrega']));
        $count = find_by_sql('SELECT COUNT(ID_OrdenCompra) AS total FROM ordenescompra;');
        $count = $count[0]['total'];
        $OrdenCompra = 'OC' . date('ym') . intval($count[0]) + 1;
        $Estado = 'Pendiente';
        $sql = "INSERT INTO ordenescompra (OrdenCompra, ProveedorID, FechaPedido, FechaEntregaEstimada, AlmacenEntrega, Estado) VALUES ('{$OrdenCompra}', '{$Proveedor}', '{$FechaPedido}', '{$FechaEntregaEstimada}', '{$AlmacenEntrega}', '{$Estado}')";
        if ($db->query($sql)) {
            $session->msg('s', "Orden de Compra creada exitosamente! ");
            //redireccionar a la pagina de detalle de la orden de compra con metodo GET para mostrar la orden de compra recien creada
            $id = find_by_sql('SELECT ID_OrdenCompra FROM ordenescompra ORDER BY ID_OrdenCompra DESC LIMIT 1;');
            $id = $id[0]['ID_OrdenCompra'];
            redirect('detalle_oc.php?id=' . $id, false);
        } else {
            $session->msg('d', ' Lo siento, registro falló.');
            redirect('ordenes_compra.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('ordenes_compra.php', false);
    }
}
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
                        <h1 class="h1 mb-3"><strong>Ordenes de Compra</strong> </h1>
                        <?php if ($user['user_level'] <= 2) : ?>
                            <div class="col-auto ms-auto text-end mt-n1">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#creation_Material">Nueva Orden de Compra</button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Tabla de Ordenes de Compra -->
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title">Historial de Ordenes de Compra</h5>
                                <div class="" id="botones"></div>
                            </div>
                        </div>
                        <div class="table px-3">
                            <table class="table mb-0 display table-striped" style="width:100%" id="OrdenCompra">
                                <thead>
                                    <tr>
                                        <th class="text-center">°</th>
                                        <th class="text-center">Orden de Compra</th>
                                        <th class="text-center">Proveedor</th>
                                        <th class="text-center">Fecha del Pedido</th>
                                        <th class="text-center">Fecha de Entrega</th>
                                        <th class="text-center">Almacen de Entrega</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>

                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $OrdenesCompra = find_by_sql('SELECT oc.*, p.nombre as NombreProveedor, a.CodigoAlmacen as Almacen FROM ordenescompra as oc, proveedores as p, almacenes as a WHERE oc.ProveedorID = p.ID_Proveedor AND AlmacenEntrega = a.ID_Almacen ORDER BY oc.ID_OrdenCompra DESC;');
                                    foreach ($OrdenesCompra as $OrdenCompra) : ?>
                                        <tr>
                                            <td class="text-center"><?php echo count_id(); ?></td>
                                            <td class="text-center"><?php echo remove_junk($OrdenCompra['OrdenCompra']); ?></td>
                                            <td class="text-center"><?php echo remove_junk($OrdenCompra['NombreProveedor']); ?></td>
                                            <td class="text-center"><?php echo remove_junk($OrdenCompra['FechaPedido']); ?></td>
                                            <td class="text-center"><?php echo strtoupper(remove_junk($OrdenCompra['FechaEntregaEstimada'])); ?></td>
                                            <td class="text-center"><?php echo remove_junk($OrdenCompra['Almacen']); ?></td>
                                            <?php if ($OrdenCompra['Estado'] == "Abierta") : ?>
                                                <td class="text-center"><span class="badge bg-success">Aprovada</span></td>
                                            <?php elseif ($OrdenCompra['Estado'] == "Pendiente") : ?>
                                                <td class="text-center"><span class="badge bg-warning">Pendiente</span></td>
                                            <?php elseif ($OrdenCompra['Estado'] == "Rechazada") : ?>
                                                <td class="text-center"><span class="badge bg-danger">Rechazada</span></td>
                                            <?php else : ?>
                                                <td class="text-center"><span class="badge bg-secondary">Cerrada</span></td>
                                            <?php endif; ?>

                                            <td class="text-center" class="text-center">
                                                <a href="detalle_oc.php?id=<?php echo $OrdenCompra['ID_OrdenCompra']; ?>" class="btn btn-info btn-sm"> <i class="aling-middle" data-feather="eye"></i></a>
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


    <!-- Modal Crear Orden de Compra -->
    <div class="modal fade" id="creation_Material" tabindex="-1" aria-labelledby="creation_MaterialLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="creation_MaterialLabel">Nueva Orden de Compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="ordenes_compra.php" method="post">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Proveedor" class="form-label">Proveedor</label>
                                    <select class="form-select" name="Proveedor" id="Proveedor" required>
                                        <option value="">Seleccione un Proveedor</option>
                                        <?php
                                        $proveedores = find_all('proveedores');
                                        foreach ($proveedores as $proveedor) :
                                        ?>
                                            <option value="<?php echo $proveedor['ID_Proveedor']; ?>"><?php echo $proveedor['Nombre']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="FechaPedido" class="form-label">Fecha del Pedido</label>
                                    <input type="date" class="form-control" name="FechaPedido" id="FechaPedido" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="FechaEntregaEstimada" class="form-label">Fecha de Entrega Estimada</label>
                                    <input type="date" class="form-control" name="FechaEntregaEstimada" id="FechaEntregaEstimada" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="AlmacenEntrega" class="form-label">Almacen de Entrega</label>
                                    <select class="form-select" name="AlmacenEntrega" id="AlmacenEntrega" required>
                                        <option value="">Seleccione un Almacen</option>
                                        <?php
                                        $almacenes = find_all('almacenes');
                                        foreach ($almacenes as $almacen) :
                                        ?>
                                            <option value="<?php echo $almacen['ID_Almacen']; ?>"><?php echo $almacen['CodigoAlmacen']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" name="crear_orden_compra" class="btn btn-primary">Generar Orden de Compra</button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once('layouts/scripts.php'); ?>

    <script>
        //Cargar los datos del usuario a editar
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('edit_OrdenCompra');
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
            var modal = document.getElementById('delete_OrdenCompra');
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
            var table = $('#OrdenCompra').DataTable({
                paging: true,
                pageLength: 25,
                scrollCollapse: true,
                scrollY: '70vh',
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