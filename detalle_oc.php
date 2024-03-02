<?php
$title_page = 'Ordenes de Compra | Compras';
//Menus Sidebar
$page = 'OrdenesCompra';
$separador = 'Compras';
require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}

//Metodo Get para editar un material de la OC

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    // Realizar una consulta para obtener los datos del grupo con el ID proporcionado
    $sql = "SELECT d.*, p.nombre as Nombre, p.codigo as Codigo FROM detalleordencompra as d, productos as p WHERE d.ProductoID = p.ID_Producto and d.ID_DetalleOrden = " . (int)$id;
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


//Obetener los datos de la orden de compra
$oc_id = $_GET['id'];
$oc = find_by_sql('SELECT oc.*, p.nombre as Proveedor, a.CodigoAlmacen FROM ordenescompra as oc, proveedores as p, almacenes as a WHERE ProveedorID = ID_Proveedor and AlmacenEntrega = ID_Almacen and ID_OrdenCompra =' . (int)$oc_id);
$oc = $oc[0];
$totaloc = find_by_sql('SELECT SUM(Subtotal) as Total FROM detalleordencompra WHERE ID_OrdenCompra =' . (int)$oc_id);
$totaloc = $totaloc[0]['Total'];
//Poner comas al total de la OC en sistema americano e ignorar si esta vacio
if ($totaloc != '') {
    $totaloc = number_format($totaloc, 2, '.', ',');
}





//Metodo post para agregar un material a la OC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_material'])) {
    $productoID = $_POST['ID_Producto'];
    $cantidad = $_POST['Cantidad'];
    $precio = $_POST['Precio'];
    $subtotal = $_POST['Subtotal'];
    $ocID = $_POST['OrdenCompraID'];
    $proveedorID = $_POST['ProveedorID'];

    $sql = "INSERT INTO detalleordencompra (ID_OrdenCompra, ProductoID, Cantidad, CantidadPendiente, PrecioUnitario, Subtotal, ProveedorID) VALUES ('$ocID', '$productoID', '$cantidad','$cantidad', '$precio', '$subtotal', '$proveedorID')";
    if ($db->query($sql)) {
        $session->msg('s', 'Material agregado a la Orden de Compra');
        redirect('detalle_oc.php?id=' . $ocID, false);
    } else {
        $session->msg('d', 'Error al agregar el material a la Orden de Compra');
        redirect('detalle_oc.php?id=' . $ocID, false);
    }
}


//Metodo post para editar un material de la OC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];
    $cantidad = $_POST['edit_Cantidad'];
    $precio = $_POST['edit_Precio'];
    $ocID = $_POST['OrdenCompraID'];
    $sql = "UPDATE detalleordencompra SET Cantidad = '$cantidad', PrecioUnitario = '$precio' WHERE ID_DetalleOrden = " . (int)$id;
    if ($db->query($sql)) {
        //Actualiza Cantidades Pendientes
        $sql = "UPDATE detalleordencompra SET CantidadPendiente = Cantidad - CantidadEntregada WHERE ID_DetalleOrden = " . (int)$id;
        $db->query($sql);
        //Actualizar Subtotal
        $sql = "UPDATE detalleordencompra SET Subtotal = Cantidad * PrecioUnitario WHERE ID_DetalleOrden = " . (int)$id;
        $db->query($sql);
        $session->msg('s', 'Material editado de la Orden de Compra');
        redirect('detalle_oc.php?id=' . $ocID, false);
    } else {
        $session->msg('d', 'Error al editar el material de la Orden de Compra');
        redirect('detalle_oc.php?id=' . $ocID, false);
    }
}

//Metodo post para eliminar un material de la OC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_material'])) {
    $id = $_POST['delete_id'];
    $ocID = $_POST['OrdenCompraID'];
    $sql = "DELETE FROM detalleordencompra WHERE ID_DetalleOrden = " . (int)$id;
    if ($db->query($sql)) {
        $session->msg('s', 'Material eliminado de la Orden de Compra');
        redirect('detalle_oc.php?id=' . $ocID, false);
    } else {
        $session->msg('d', 'Error al eliminar el material de la Orden de Compra');
        redirect('detalle_oc.php?id=' . $ocID, false);
    }
}

//Metodo post para autorizar la OC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['autorizar_oc'])) {
    $id = $_POST['autorizar_id'];
    $sql = "UPDATE ordenescompra SET Estado = 'Abierta' WHERE ID_OrdenCompra = " . (int)$id;
    if ($db->query($sql)) {
        $session->msg('s', 'Orden de Compra Autorizada');
        redirect('ordenes_compra.php?id=' . $id, false);
    } else {
        $session->msg('d', 'Error al autorizar la Orden de Compra');
        redirect('detalle_oc.php?id=' . $id, false);
    }
}

//Metodo post para cerrar la OC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cerrar_oc'])) {
    $id = $_POST['cerrar_id'];
    $sql = "UPDATE ordenescompra SET Estado = 'Cerrada' WHERE ID_OrdenCompra = " . (int)$id;
    if ($db->query($sql)) {
        $session->msg('s', 'Orden de Compra Cerrada');
        redirect('ordenes_compra.php?id=' . $id, false);
    } else {
        $session->msg('d', 'Error al cerrar la Orden de Compra');
        redirect('detalle_oc.php?id=' . $id, false);
    }
}

//Metodo post para rechazar la OC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rechazar_oc'])) {
    $id = $_POST['rechazar_id'];
    $sql = "UPDATE ordenescompra SET Estado = 'Rechazada' WHERE ID_OrdenCompra = " . (int)$id;
    if ($db->query($sql)) {
        $session->msg('s', 'Orden de Compra Rechazada');
        redirect('ordenes_compra.php?id=' . $id, false);
    } else {
        $session->msg('d', 'Error al rechazar la Orden de Compra');
        redirect('detalle_oc.php?id=' . $id, false);
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

                        <div class="d-flex">
                            <h1 class="h3 mb-3"><strong>Detalle OC</strong> "<?php echo  $oc["Proveedor"] . " | " . $oc['OrdenCompra'] ?>" </h1>
                            <div class="ms-2">
                                <!-- bagde con el estatus de la OC -->
                                <?php if ($oc['Estado'] == "Abierta") : ?>
                                    <span class="badge bg-success">Aprobada</span>
                                <?php elseif ($oc['Estado'] == "Pendiente") : ?>
                                    <span class="badge bg-info">Aprobacion Pendiente</span>
                                <?php elseif ($oc['Estado'] == "Cerrada") : ?>
                                    <span class="badge bg-danger">Cerrada</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-auto ms-auto text-end mt-n1">
                            <?php if ($user['user_level'] == 1) : ?>
                                <?php if ($oc['Estado'] == "Pendiente") : ?>
                                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#autorizarOC">Autorizar OC</button>
                                    <!-- rechazar OC -->
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rechazarOC">Rechazar OC</button>
                                <?php elseif ($oc['Estado'] == "Abierta") : ?>
                                    <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#cerrarOC">Cerrar OC</button>

                                <?php endif; ?>


                            <?php endif; ?>
                            <!--Link para imprimir la OC si esta aprovada-->
                            <?php if ($oc['Estado'] == "Abierta") : ?>
                                <a href="imprimir_oc.php?id=<?php echo $oc_id; ?>" target="_blank" class="btn btn-info btn-sm">Imprimir OC</a>
                            <?php endif; ?>
                            <a href="ordenes_compra.php" class="btn btn-secondary btn-sm">Regresar</a>


                        </div>


                    </div>
                </div>
                <!--Tabla de Materiales-->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header pb-0">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title">Lista de Materiales</h5>
                                    <div class="d-flex gap-3 align-items-center">
                                        <div class="col-auto ms-auto text-end mt-n1">
                                            <div class="" id="botones"></div>
                                        </div>
                                        <?php if ($user['user_level'] <= 2) : ?>
                                            <button class="btn btn-primary btn-sm mt-n1" data-bs-toggle="modal" data-bs-target="#modal_agregar_material">Agregar Material</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table px-3">
                                    <table class="table mb-0 display table-striped" style="width:100%" id="detalleoctable">

                                        <thead>
                                            <tr>
                                                <th class="text-center">°</th>
                                                <th class="text-center">SKU</th>
                                                <th class="text-center">Material</th>
                                                <th class="text-center">Cantidad OC</th>
                                                <th class="text-center">Cantidad Entregada</th>
                                                <th class="text-center">Cantidad Pendiete</th>
                                                <th class="text-center">Precio Unitario</th>
                                                <th class="text-center">Subtotal</th>
                                                <th class="text-center">Entrega</th>
                                                <?php if ($user['user_level'] == 1) : ?>
                                                    <th class="text-center">Acciones</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php
                                            $DetalleOCs = find_by_sql('SELECT doc.*, p.Nombre, p.Codigo FROM detalleordencompra as doc, productos as p WHERE ProductoID = ID_Producto and ID_OrdenCompra =' . (int)$oc_id);
                                            foreach ($DetalleOCs as $DetalleOC) :
                                            ?>
                                                <tr>
                                                    <td class="text-center"><?php echo count_id(); ?></td>
                                                    <td class="text-center"><?php echo remove_junk($DetalleOC['Codigo']); ?></td>
                                                    <td class="text-center"><?php echo remove_junk($DetalleOC['Nombre']); ?></td>
                                                    <td class="text-center"><?php echo remove_junk($DetalleOC['Cantidad']); ?></td>
                                                    <td class="text-center"><?php echo remove_junk($DetalleOC['CantidadEntregada']); ?></td>
                                                    <td class="text-center"><?php echo remove_junk($DetalleOC['CantidadPendiente']); ?></td>
                                                    <td class="text-center">$<strong><?php echo number_format($DetalleOC['PrecioUnitario'], 2, '.', ','); ?></strong></td>
                                                    <td class="text-center">$<strong><?php echo number_format($DetalleOC['Subtotal'], 2, '.', ','); ?></strong></td>
                                                    <?php if ($DetalleOC['Entrega'] == 1) : ?>
                                                        <td class="text-center"><span class="badge bg-success">Entregado</span></td>
                                                    <?php elseif ($DetalleOC['Entrega'] == 3) : ?>
                                                        <td class="text-center"><span class="badge bg-danger">Pendiente</span></td>
                                                    <?php else : ?>
                                                        <td class="text-center"><span class="badge bg-warning">Parcial</span></td>
                                                    <?php endif; ?>
                                                    <?php if ($user['user_level'] == 1) : ?>
                                                        <td class="text-center" class="text-center">
                                                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#edit_detalleOC" data-id="<?php echo $DetalleOC['ID_DetalleOrden']; ?>"> <i class="aling-middle" data-feather="edit"></i></button>
                                                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete_detalleOC" data-id="<?php echo $DetalleOC['ID_DetalleOrden']; ?>"> <i class="aling-middle" data-feather="trash"></i></button>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Detllaes de la OC -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Detalles de la Orden de Compra</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="Proveedor" class="form-label">Proveedor</label>
                                            <input type="text" class="form-control" id="Proveedor" name="Proveedor" value="<?php echo $oc['Proveedor']; ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="OrdenCompra" class="form-label">Orden de Compra</label>
                                            <input type="text" class="form-control" id="OrdenCompra" name="OrdenCompra" value="<?php echo $oc['OrdenCompra']; ?>" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="Fecha" class="form-label">Fecha Entrega</label>
                                            <input type="text" class="form-control" id="Fecha" name="Fecha" value="<?php echo $oc['FechaEntregaEstimada']; ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="Total" class="form-label">Total</label>
                                            <input type="text" class="form-control" id="Total" name="Total" value="$ <?php echo $totaloc; ?>" disabled>
                                        </div>
                                    </div>
                                </div>
                                <!-- Almacen de entrega y fecha del pedido -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="Almacen" class="form-label">Almacen de Entrega</label>
                                            <input type="text" class="form-control" id="Almacen" name="Almacen" value="<?php echo $oc['CodigoAlmacen']; ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="FechaPedido" class="form-label">Fecha Pedido</label>
                                            <input type="text" class="form-control" id="FechaPedido" name="FechaPedido" value="<?php echo $oc['FechaPedido']; ?>" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </main>


            <!-- Fin Contenedor main -->
            <?php include_once('layouts/footer.php'); ?>

        </div>
    </div>

    <!-- Modal para agrear materiales a la OC, Tiene que tener los siguientes campos (ProductoID, Cantidad)-->
    <div class="modal fade" id="modal_agregar_material" tabindex="-1" aria-labelledby="modal_agregar_material" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_agregar_material">Agregar Material a la Orden de Compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="OC" method="post">
                        <div class="mb-3">
                            <label for="ProductoID" class="form-label" id="LabelProductoID">Material</label>
                            <!-- Aqui se tiene un input donde se ingresa el SKU del material y se tien un boton que valida que exista y regresa el nombre del SKU a un label -->
                            <div class="input-group">
                                <input type="text" class="form-control" id="ProductoID" name="ProductoID" required>
                                <button class="btn btn-success" type="button" id="btn_validar_sku">Validar SKU</button>
                            </div>
                            <!-- input hidden para guardar el ID del producto -->
                            <input type="hidden" name="ID_Producto" id="ID_Producto" value="">

                        </div>
                        <!-- Inputs de cantidad precio-->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="Cantidad" class="form-label">Cantidad</label>
                                    <input type="number" class="form-control" id="Cantidad" name="Cantidad" step="0.01" min="1" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="Precio" class="form-label">Precio</label>
                                    <input type="number" class="form-control" id="Precio" name="Precio" step="0.01" disabled>
                                </div>
                            </div>
                        </div>

                        <!-- Mostrar un Subtotal en un h3 de la cantidad por el precio -->
                        <p><strong>Subtotal: $ </strong><span id="subtotalTex"></span></p>
                        <input type="hidden" name="Subtotal" id="Subtotal" value="">
                        <input type="hidden" name="OrdenCompraID" value="<?php echo $oc_id; ?>">
                        <input type="hidden" name="ProveedorID" value="<?php echo $oc['ProveedorID']; ?>">
                        <!--colocar el boton del lado derecho-->
                        <div class="col-auto ms-auto text-end">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary" name="nuevo_material">Agregar Material</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Fin Modal para agrear materiales a la OC -->
    <!--Modal para editar un material de la OC-->
    <div class="modal fade" id="edit_detalleOC" tabindex="-1" aria-labelledby="edit_detalleOC" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edit_detalleTitle">Editar Material de la Orden de Compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="OC" method="post">
                        <div class="mb-3">
                            <label for="edit_ProductoID" class="form-label">Material</label>
                            <input type="text" class="form-control" id="edit_ProductoID" name="edit_ProductoID" required disabled>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_Cantidad" class="form-label">Cantidad</label>
                                    <input type="number" class="form-control" id="edit_Cantidad" name="edit_Cantidad" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_Precio" class="form-label">Precio</label>
                                    <input type="number" class="form-control" id="edit_Precio" name="edit_Precio" step="0.01">
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="edit_id" id="edit_id" value="">
                        <input type="hidden" name="OrdenCompraID" value="<?php echo $oc_id; ?>">
                        <input type="hidden" name="ProveedorID" value="<?php echo $oc['ProveedorID']; ?>">
                        <div class="col-auto ms-auto text-end">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!--Fin Modal para eliminar un material de la OC-->
    <div class="modal fade" id="delete_detalleOC" tabindex="-1" aria-labelledby="delete_detalleOC" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete_detalleTitle">Eliminar Material de la Orden de Compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="OC" method="post">
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas eliminar este material de la Orden de Compra?</p>
                        <input type="hidden" name="delete_id" id="delete_id" value="">
                        <input type="hidden" name="OrdenCompraID" value="<?php echo $oc_id; ?>">
                        <input type="hidden" name="ProveedorID" value="<?php echo $oc['ProveedorID']; ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger" name="eliminar_material">Eliminar Material</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--Modal para autorizar la OC-->
    <div class="modal fade" id="autorizarOC" tabindex="-1" aria-labelledby="autorizarOC" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="autorizarOCTitle">Autorizar Orden de Compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="OC" method="post">
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas autorizar esta Orden de Compra?</p>
                        <!-- Mostrar los datos generales de la OC -->
                        <div class="mb-3">
                            <label for="Proveedor" class="form-label">Proveedor</label>
                            <input type="text" class="form-control" id="Proveedor" name="Proveedor" value="<?php echo $oc['Proveedor']; ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="OrdenCompra" class="form-label">Orden de Compra</label>
                            <input type="text" class="form-control" id="OrdenCompra" name="OrdenCompra" value="<?php echo $oc['OrdenCompra']; ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="Almacen" class="form-label">Almacen de Entrega</label>
                            <input type="text" class="form-control" id="Almacen" name="Almacen" value="<?php echo $oc['CodigoAlmacen']; ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="Fecha" class="form-label">Fecha Entrega</label>
                            <input type="text" class="form-control" id="Fecha" name="Fecha" value="<?php echo $oc['FechaEntregaEstimada']; ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="Total" class="form-label">Total</label>
                            <input type="text" class="form-control" id="Total" name="Total" value="$ <?php echo $totaloc; ?>" disabled>
                        </div>

                        <input type="hidden" name="autorizar_id" id="autorizar_id" value="<?php echo $oc_id; ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" name="autorizar_oc">Autorizar OC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--Modal para cerrar la OC-->
    <div class="modal fade" id="cerrarOC" tabindex="-1" aria-labelledby="cerrarOC" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cerrarOCTitle">Cerrar Orden de Compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="OC" method="post">
                    <div class="modal-body
                    ">
                        <p>¿Estás seguro de que deseas cerrar esta Orden de Compra?</p>
                        <h3 class="text-danger text-uppercase text-center">Esta accion no se puede deshacer!!</h3>
                        <!-- Mostrar los datos generales de la OC -->
                        <div class="mb-3">
                            <label for="Proveedor" class="form-label">Proveedor</label>
                            <input type="text" class="form-control" id="Proveedor" name="Proveedor" value="<?php echo $oc['Proveedor']; ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="OrdenCompra" class="form-label">Orden de Compra</label>
                            <input type="text" class="form-control" id="OrdenCompra" name="OrdenCompra" value="<?php echo $oc['OrdenCompra']; ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="Fecha" class="form-label">Fecha Entrega</label>
                            <input type="text" class="form-control" id="Fecha" name="Fecha" value="<?php echo $oc['FechaEntregaEstimada']; ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="Total" class="form-label">Total</label>
                            <input type="text" class="form-control" id="Total" name="Total" value="$ <?php echo $totaloc; ?>" disabled>
                        </div>
                    </div>
                    <input type="hidden" name="cerrar_id" id="cerrar_id" value="<?php echo $oc_id; ?>">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger" name="cerrar_oc">Cerrar OC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para rechazar la OC -->
    <div class="modal fade" id="rechazarOC" tabindex="-1" aria-labelledby="rechazarOC" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rechazarOCTitle">Rechazar Orden de Compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="OC" method="post">
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas rechazar esta Orden de Compra?</p>
                        <h3 class="text-danger text-uppercase text-center">Esta accion no se puede deshacer!!</h3>
                        <!-- Mostrar los datos generales de la OC -->
                        <div class="mb-3">
                            <label for="Proveedor" class="form-label">Proveedor</label>
                            <input type="text" class="form-control" id="Proveedor" name="Proveedor" value="<?php echo $oc['Proveedor']; ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="OrdenCompra" class="form-label">Orden de Compra</label>
                            <input type="text" class="form-control" id="OrdenCompra" name="OrdenCompra" value="<?php echo $oc['OrdenCompra']; ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="Fecha" class="form-label">Fecha Entrega</label>
                            <input type="text" class="form-control" id="Fecha" name="Fecha" value="<?php echo $oc['FechaEntregaEstimada']; ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="Total" class="form-label">Total</label>
                            <input type="text" class="form-control" id="Total" name="Total" value="$ <?php echo $totaloc; ?>" disabled>
                        </div>
                    </div>
                    <input type="hidden" name="rechazar_id" id="rechazar_id" value="<?php echo $oc_id; ?>">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger" name="rechazar_oc">Rechazar OC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <?php include_once('layouts/scripts.php'); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('btn_validar_sku').addEventListener('click', function() {
                var sku = document.getElementById('ProductoID').value;
                //Uppercase a sku
                sku = sku.toUpperCase();
                fetch('validar_sku.php?sku=' + sku)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                        } else {
                            console.log(data);
                            document.getElementById('ProductoID').classList.add('is-valid');
                            document.getElementById('ProductoID').classList.remove('is-invalid');
                            //Cambiar el valor del label LabelProductoID por el nombre del SKU y desabilitar el input y el boton de validar
                            document.getElementById('LabelProductoID').innerHTML = "Material: <strong>" + data.Nombre + "</strong>";
                            document.getElementById('ProductoID').setAttribute('disabled', 'true');
                            document.getElementById('btn_validar_sku').setAttribute('disabled', 'true');
                            //Guardar el ID del producto en un input hidden
                            document.getElementById('ID_Producto').value = data.ID_Producto;
                            //Habilitar los inputs de cantidad y precio y seleccionar el input de cantidad
                            document.getElementById('Cantidad').removeAttribute('disabled');
                            document.getElementById('Precio').removeAttribute('disabled');
                            document.getElementById('Cantidad').focus();
                            //Agregar el precio de la base de datos al input de precio
                            document.getElementById('Precio').value = data.PrecioCompra;
                        }
                    });
            });
            //Calcular el subtotal al cambiar la cantidad o el precio
            document.getElementById('Cantidad').addEventListener('change', function() {
                var cantidad = document.getElementById('Cantidad').value;
                var precio = document.getElementById('Precio').value;
                //Redondear a 2 decimales
                var subtotal = (cantidad * precio).toFixed(2);
                document.getElementById('Subtotal').value = subtotal;
                document.getElementById('subtotalTex').innerHTML = subtotal;

            });

            //desabilitar el enter en el formulario OC y calcular el subtotal al presionar enter
            document.getElementById('OC').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });

            //Resetear todo el Form al cerrar el modal
            document.getElementById('modal_agregar_material').addEventListener('hidden.bs.modal', function() {
                document.getElementById('OC').reset();
                document.getElementById('ProductoID').removeAttribute('disabled');
                document.getElementById('btn_validar_sku').removeAttribute('disabled');
                document.getElementById('LabelProductoID').innerHTML = "Material";
                document.getElementById('ProductoID').classList.remove('is-valid');
                document.getElementById('ProductoID').classList.remove('is-invalid');
                document.getElementById('ID_Producto').value = '';
                document.getElementById('Cantidad').setAttribute('disabled', 'true');
                document.getElementById('Precio').setAttribute('disabled', 'true');
                document.getElementById('Subtotal').value = '';
                document.getElementById('subtotalTex').innerHTML = '';
            });

            //Cargar los datos a editar
            var modal = document.getElementById('edit_detalleOC');
            modal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget; // Botón que activó el modal
                var id = button.getAttribute('data-id'); // Obtener el valor del atributo data-id
                console.log(id);
                var ruta = window.location.href + '&edit=';
                console.log(ruta);

                // Ahora puedes usar el valor de 'id' para realizar una consulta y llenar el formulario
                // Ejemplo: realiza una petición AJAX para obtener los datos y luego llénalos en el formulario
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            //console.log(xhr.responseText);
                            var data = JSON.parse(xhr.responseText);
                            console.log(data);
                            // Llena el formulario con los datos obtenidos
                            document.getElementById('edit_id').value = data.ID_DetalleOrden;
                            document.getElementById('edit_ProductoID').value = data.Nombre + " | " + data.Codigo;
                            document.getElementById('edit_Cantidad').value = data.Cantidad;
                            document.getElementById('edit_Precio').value = data.PrecioUnitario;




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
            var modal = document.getElementById('delete_detalleOC');
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
            var table = $('#detalleoctable').DataTable({
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