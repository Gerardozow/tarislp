<?php
$title_page = 'Movimientos';
//Menus Sidebar
$page = 'Movimientos';
$separador = 'Almacen';
require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}

//Metodo Post para generar bajas y devoluciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generarmovimiento'])) {
    $req_fields = array('almacen', 'tipo_movimiento', 'producto', 'cantidad');
    validate_fields($req_fields);
    if (empty($errors)) {
        $almacen = intval($_POST['almacen']);
        $tipo_movimiento = $_POST['tipo_movimiento'];
        $producto = $_POST['producto'];
        $cantidad = $_POST['cantidad'];
        $motivo = $_POST['motivo'];
        //Fecha de movimiento actual
        $fecha = date('Y-m-d-H:i:s');

        //Acciones si es devolucion
        if ($tipo_movimiento == 'Devolucion') {
            $producto_almacen = find_by_sql("SELECT * FROM inventario WHERE ProductoID = '$producto' AND AlmacenID = '$almacen'");
            if ($producto_almacen) {
                $cantidad_actual = $producto_almacen[0]['CantidadEnStock'];
                $new_stock = $cantidad_actual + $cantidad;
                $sql = "UPDATE inventario SET CantidadEnStock = '$new_stock' WHERE ProductoID = '$producto' AND AlmacenID = '$almacen'";
                if ($db->query($sql)) {
                    //Registrar en tabla de historicomovimientos con los campos
                    //ID_Movimiento	ProductoID	TipoMovimiento	Cantidad	AlmacenID	FechaMovimiento	Notas
                    $sql = "INSERT INTO historicomovimientos (ProductoID, TipoMovimiento, Cantidad, AlmacenID, FechaMovimiento, Notas) VALUES ('$producto', '$tipo_movimiento', '$cantidad', '$almacen', '$fecha', '$motivo')";
                    $db->query($sql);
                    $session->msg('s', 'Movimiento generado correctamente.');
                    redirect('movimientos.php', false);
                } else {
                    $session->msg('d', 'Ocurrió un error al generar el movimiento.');
                    redirect('movimientos.php', false);
                }
            } else {
                $session->msg('d', 'El producto no existe en el almacen.');
                redirect('movimientos.php', false);
            }
        }

        //Acciones si es baja
        if ($tipo_movimiento == 'Baja') {
            $producto_almacen = find_by_sql("SELECT * FROM inventario WHERE ProductoID = '$producto' AND AlmacenID = '$almacen'");
            if ($producto_almacen) {
                $cantidad_actual = $producto_almacen[0]['CantidadEnStock'];
                if ($cantidad_actual >= $cantidad) {
                    $new_stock = $cantidad_actual - $cantidad;
                    $sql = "UPDATE inventario SET CantidadEnStock = '$new_stock' WHERE ProductoID = '$producto' AND AlmacenID = '$almacen'";
                    if ($db->query($sql)) {
                        //Registrar en tabla de historicomovimientos con los campos
                        //ID_Movimiento	ProductoID	TipoMovimiento	Cantidad	AlmacenID	FechaMovimiento	Notas
                        $sql = "INSERT INTO historicomovimientos (ProductoID, TipoMovimiento, Cantidad, AlmacenID, FechaMovimiento, Notas) VALUES ('$producto', '$tipo_movimiento', '$cantidad', '$almacen', '$fecha', '$motivo')";
                        $db->query($sql);
                        $session->msg('s', 'Movimiento generado correctamente.');
                        redirect('movimientos.php', false);
                    } else {
                        $session->msg('d', 'Ocurrió un error al generar el movimiento.');
                        redirect('movimientos.php', false);
                    }
                } else {
                    $session->msg('d', 'La cantidad a dar de baja es mayor a la cantidad en stock.');
                    redirect('movimientos.php', false);
                }
            } else {
                $session->msg('d', 'El producto no existe en el almacen.');
                redirect('movimientos.php', false);
            }
        }
    } else {
        $session->msg("d", $errors);
        redirect('movimientos.php', false);
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
                    <h1 class="h3 mb-3"><strong>Movimientos</strong></h1>
                </div>

                <!-- Formulario para generar bajas o devoluciones en almacenes -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Bajas y Devoluciones</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Almacen</label>
                                            <select class="form-select" name="almacen" id="almacen" required>
                                                <option value="">Seleccione un almacen</option>
                                                <?php
                                                $almacenes = find_all('almacenes');
                                                foreach ($almacenes as $almacen) : ?>
                                                    <option value="<?php echo $almacen['ID_Almacen'] ?>">
                                                        <?php echo $almacen['CodigoAlmacen'] . "-" . $almacen["NombreAlmacen"] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tipo de Movimiento</label>
                                            <select class="form-select" name="tipo_movimiento" id="tipo_movimiento" required>
                                                <option value="">Seleccione un tipo de movimiento</option>
                                                <option value="Baja" selected>Baja</option>
                                                <option value="Devolucion">Devolucion</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Producto</label>
                                            <select class="form-select" name="producto" id="producto" required>
                                                <option value="">Seleccione un producto</option>
                                                <?php
                                                $productos = find_all('productos');
                                                foreach ($productos as $producto) : ?>
                                                    <option value="<?php echo $producto['ID_Producto'] ?>">
                                                        <?php echo $producto['Codigo'] . "  -  " . $producto['Nombre'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Cantidad</label>
                                            <input type="number" class="form-control" name="cantidad" id="cantidad" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Motivo</label>
                                            <input type="text" class="form-control" name="motivo" id="motivo">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Fecha</label>
                                            <input type="date" class="form-control" name="fecha" id="fecha" disabled required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary" name="generarmovimiento">Generar Movimiento</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Generar Tabla con los movientos -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title">Inventario</h5>
                                <div class="" id="botones"></div>
                            </div>
                        </div>
                        <div class="table px-3">
                            <table class="table mb-0 display table-striped" style="width:100%" id="movimientos">
                                <thead>
                                    <tr>
                                        <th class="text-center">°</th>
                                        <th class="text-center">Movimiento</th>
                                        <th class="text-center">Fecha</th>
                                        <th class="text-center">Tipo</th>
                                        <th class="text-center">Almacen</th>
                                        <th class="text-center">SKU</th>
                                        <th class="text-center">Material</th>
                                        <th class="text-center">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $Movimientos = find_by_sql("SELECT h.*, p.Codigo, p.Nombre, a.CodigoAlmacen FROM historicomovimientos as h, productos as p, almacenes as a WHERE h.ProductoID = p.ID_Producto and h.AlmacenID = a.ID_Almacen");
                                    foreach ($Movimientos as $movimiento) :
                                    ?>
                                        <tr>
                                            <td class="text-center"><?php echo count_id(); ?></td>
                                            <?php
                                            $tipo_movimiento = $movimiento['TipoMovimiento'] == 'Baja' ? 'B' : 'D';
                                            $fecha = date_create($movimiento['FechaMovimiento']);
                                            $fecha = date_format($fecha, 'mdH');
                                            $id_movimiento = $tipo_movimiento . $fecha . $movimiento['ID_Movimiento'];
                                            $ID = $tipo_movimiento . $fecha . $movimiento['ID_Movimiento'];
                                            ?>
                                            <td class="text-center"><?php echo $ID; ?></td>
                                            <td class="text-center"><?php echo $movimiento['FechaMovimiento']; ?></td>
                                            <td class="text-center"><?php echo $movimiento['TipoMovimiento']; ?></td>
                                            <td class="text-center"><?php echo $movimiento['CodigoAlmacen']; ?></td>
                                            <td class="text-center"><?php echo $movimiento['Codigo'] ?></td>
                                            <td class="text-center"><?php echo $movimiento['Nombre'] ?></td>
                                            <td class="text-center"><?php echo $movimiento['Cantidad'] ?></td>


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

    <script>
        //Cargar fecha actual
        document.addEventListener("DOMContentLoaded", function() {
            var date = new Date(Date.now());
            document.getElementById('fecha').value = date.toISOString().split('T')[0];
        });
    </script>
    <!-- Datatables -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-colvis-2.4.2/b-html5-2.4.2/b-print-2.4.2/sc-2.3.0/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#movimientos').DataTable({
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