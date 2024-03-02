<?php
$title_page = 'Ordenes de Compra | Compras';
//Menus Sidebar
$page = 'OrdenesCompra';
$separador = 'Compras';
require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}


//Metodo GET para Obetner informacion
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $oc = find_by_sql("SELECT *, a.CodigoAlmacen FROM ordenescompra, almacenes as a WHERE AlmacenEntrega = ID_Almacen and ID_OrdenCompra = '{$id}'");
    $oc_detalle = find_by_sql("SELECT *, p.* FROM detalleordencompra, productos as p WHERE ProductoID = p.ID_Producto and ID_OrdenCompra = '{$id}'");
    $proveedor = find_by_sql("SELECT * FROM proveedores WHERE ID_Proveedor = '{$oc[0]['ProveedorID']}'");
    $productos = find_by_sql("SELECT * FROM productos");

    if (!$oc) {
        $session->msg("d", "Orden de Compra no encontrada.");
        redirect('ordenes_compra.php');
    }
} else {
    $session->msg("d", "Orden de Compra no encontrada.");
    redirect('ordenes_compra.php');
}


?>

<!-- Html con la informacion de la OC para imprimir -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Orden de Compra</title>
    <link href="assets/css/app.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet" />
    <style>
        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="page-break">
        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-heading">
                        <strong>
                            <h3 class="text-center">Orden de Compra - <?php echo $oc[0]['OrdenCompra'] ?></h3>
                        </strong>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Proveedor:</strong>
                                <p><?php echo $proveedor[0]['Nombre']; ?></p>
                                <strong>Email:</strong>
                                <p><?php echo $proveedor[0]['Correo']; ?></p>
                                <strong>Telefono:</strong>
                                <p><?php echo $proveedor[0]['Telefono']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Fecha:</strong>
                                <p><?php echo $oc[0]['FechaPedido']; ?></p>
                                <strong>Orden de Compra:</strong>
                                <p><?php echo $oc[0]['OrdenCompra']; ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 50px;">#</th>
                                            <th class="text-center" style="width: 50px;">Producto</th>
                                            <th class="text-center" style="width: 50px;">Cantidad</th>
                                            <th class="text-center" style="width: 50px;">Precio</th>
                                            <th class="text-center" style="width: 50px;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($oc_detalle as $ocd) : ?>
                                            <tr>
                                                <td class="text-center"><?php echo count_id(); ?></td>
                                                <td class="text-center"><?php echo $oc_detalle[0]['Codigo'] ?></td>
                                                <td class="text-center"><?php echo $ocd['Cantidad']; ?></td>
                                                <td class="text-center"><?php echo $ocd['PrecioUnitario']; ?></td>
                                                <td class="text-center"><?php echo $ocd['Subtotal']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!--Datos Generales -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <strong>Subtotal: </strong>
                                    <p> $
                                        <?php
                                        //Sumar todos los subtotales
                                        $subtotal = 0;
                                        foreach ($oc_detalle as $ocd) {
                                            $subtotal += $ocd['Subtotal'];
                                        }
                                        //Imprimir el subtotal en sistema decimar americano
                                        echo number_format($subtotal, 2);

                                        ?>
                                    </p>
                                </div>

                                <div class="d-flex">
                                    <strong>IVA:</strong>
                                    <p>
                                        <?php
                                        //Calcular el IVA
                                        $iva = $subtotal * 0.16;
                                        //Imprimir el IVA en sistema decimar americano
                                        echo number_format($iva, 2);
                                        ?>
                                    </p>
                                </div>
                                <div class="d-flex">
                                    <strong>Total:</strong>
                                    <p> $<?php
                                            //Calcular el total
                                            $total = $subtotal + $iva;
                                            //Imprimir el total en sistema decimar americano
                                            echo number_format($total, 2);
                                            ?></p>
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <strong>Observaciones:</strong>
                                <p>Entregar en el Almacen: <?php echo $oc[0]['CodigoAlmacen'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<script>
    window.print();
    setTimeout(function() {
        window.close();
    }, 100);
</script>