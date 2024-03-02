<?php
$title_page = 'Inventario';
//Menus Sidebar
$page = 'Inventario';
$separador = 'Almacen';
require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
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
                    <h1 class="h3 mb-3"><strong>Reporte</strong> Inventario</h1>
                </div>
                <!-- Generar Tabla con el inventario -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title">Inventario</h5>
                                <div class="" id="botones"></div>
                            </div>
                        </div>
                        <div class="table px-3">
                            <table class="table mb-0 display table-striped" style="width:100%" id="inventario">
                                <thead>
                                    <tr>
                                        <th class="text-center">°</th>
                                        <th class="text-center">SKU</th>
                                        <th class="text-center">Nombre</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-center">Almacen</th>
                                        <th class="text-center">Unidad Medida</th>
                                        <th class="text-center">Precio Compra</th>
                                        <th class="text-center">Importe en Stock</th>

                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $Inventarios = find_by_sql("SELECT i.CantidadEnStock, p.codigo, p.Nombre, p.PrecioCompra, a.CodigoAlmacen, u.NombreUnidad from inventario as i, productos as p, almacenes as a, unidadesmedida AS u where i.ProductoID = p.ID_Producto and i.AlmacenID = a.ID_Almacen and p.UnidadMedidaID = u.ID_UnidadMedida");
                                    foreach ($Inventarios as $Inventario) :
                                    ?>
                                        <tr>
                                            <td class="text-center"><?php echo count_id(); ?></td>
                                            <td class="text-center"><?php echo remove_junk($Inventario['codigo']); ?></td>
                                            <td class="text-center"><?php echo remove_junk($Inventario['Nombre']); ?></td>
                                            <td class="text-center"><?php echo number_format($Inventario['CantidadEnStock'], 0, '.', ','); ?></td>
                                            <td class="text-center"><?php echo remove_junk($Inventario['CodigoAlmacen']); ?></td>
                                            <td class="text-center"><?php echo remove_junk($Inventario['NombreUnidad']); ?></td>
                                            <td class="text-center">$ <?php echo number_format($Inventario['PrecioCompra'], 2, '.', ','); ?></td>
                                            <td class="text-center">$ <?php echo number_format($Inventario['PrecioCompra'] * $Inventario['CantidadEnStock'], 2, '.', ','); ?></td>

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

    <?php include_once('layouts/scripts.php'); ?>


    <!-- Datatables -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-colvis-2.4.2/b-html5-2.4.2/b-print-2.4.2/sc-2.3.0/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#inventario').DataTable({
                paging: true,
                pageLength: 100,
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