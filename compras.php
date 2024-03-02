<?php
$title_page = 'Dashboard Compras';
//Menus Sidebar
$page = 'Dashboard_compras';
$separador = 'Compras';

require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}

include_once('layouts/head.php');


//Traer datos de la tabla materiales


//Consulta stock minimo
$Criticos = find_by_sql("SELECT COUNT(*) AS CantidadMaterialesCriticos FROM Inventario I JOIN Productos P ON I.ProductoID = P.ID_Producto WHERE I.CantidadEnStock < P.StockMinimo;");
//Consulta de produtos creados
$Productos = find_by_sql("SELECT COUNT(*) AS CantidadProductos FROM Productos;");
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
                    <h1 class="h3 mb-3"><strong>Compras</strong> Dashboard</h1>
                </div>
                <div class="row">
                    <div class="col-xl-2 col-xxl-3 d-flex">
                        <div class="w-100">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="card ">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col mt-0">
                                                    <h5 class="card-title">Materiales Criticos</h5>
                                                </div>
                                                <a href="#" class="col-auto">
                                                    <div class="stat text-primary">
                                                        <i class="align-middle" data-feather="dollar-sign"></i>
                                                    </div>
                                                </a>
                                            </div>
                                            <h1 class="mt-1 mb-3"><?= $Criticos[0]['CantidadMaterialesCriticos'] ?> Criticos</h1>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">


                            </div>
                        </div>
                    </div>

                    <div class="col-xl-10 col-xxl-9">
                        <div class="card flex-fill w-100">
                            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 ">Materiales Criticos</h5>
                                <div id="botones"></div>
                            </div>
                            <div class="card-body py-3 pt-0">
                                <table class="table mb-0 display table-striped" style="width:100%" id="criticos">
                                    <thead>
                                        <tr>
                                            <th class="text-center">°</th>
                                            <th class="text-center">Codigo</th>
                                            <th class="text-center">Material</th>
                                            <th class="text-center">Almacen</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-center">Minimo Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                        $MatCriticos = find_by_sql("SELECT 
                                                                    P.Codigo AS CodigoProducto,
                                                                    P.Nombre AS Producto,
                                                                    A.NombreAlmacen AS Almacen,
                                                                    I.CantidadEnStock,
                                                                    P.StockMinimo,
                                                                    P.UnidadMedidaID,
                                                                    UM.NombreUnidad AS UnidadMedida,
                                                                    (P.StockMinimo - I.CantidadEnStock) AS CantidadRequerida
                                                                FROM Inventario I
                                                                JOIN Productos P ON I.ProductoID = P.ID_Producto
                                                                JOIN Almacenes A ON I.AlmacenID = A.ID_Almacen
                                                                JOIN UnidadesMedida UM ON P.UnidadMedidaID = UM.ID_UnidadMedida
                                                                WHERE I.CantidadEnStock < P.StockMinimo;
                                                                ");
                                        foreach ($MatCriticos as $MatCritico) :
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo count_id(); ?></td>
                                                <td class="text-center"><?php echo remove_junk($MatCritico['CodigoProducto']); ?></td>
                                                <td class="text-center"><?php echo remove_junk($MatCritico['Producto']); ?></td>
                                                <td class="text-center"><?php echo remove_junk($MatCritico['Almacen']); ?></td>
                                                <td class="text-center"><?php echo remove_junk($MatCritico['CantidadEnStock']); ?></td>
                                                <td class="text-center"><?php echo remove_junk($MatCritico['StockMinimo']); ?></td>


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

    <?php include_once('layouts/scripts.php'); ?>

    <!-- Datatables -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-colvis-2.4.2/b-html5-2.4.2/b-print-2.4.2/sc-2.3.0/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#criticos').DataTable({
                //ocultar busqueda y ordenamiento
                searching: false,
                ordering: false,
                paging: false,
                pageLength: 5,
                scrollCollapse: true,
                scrollY: '18vh',
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
                //parametros de exportacion
                //idioma
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