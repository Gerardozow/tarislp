<?php
$title_page = 'Recepcion de Materiales | Almacen';
//Menus Sidebar
$page = 'recibo_material';
$separador = 'Almacen';
require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}



//Metodo GET para Cargar los Datos de la Orden de Compra
if (isset($_GET['OC'])) {
    $OC = $_GET['OC'];
    $OC = trim($OC); // Eliminar espacios en blanco al inicio y al final del string
    $query = "SELECT oc.*, p.nombre, a.CodigoAlmacen, a.ID_Almacen FROM ordenescompra as oc, proveedores as p, almacenes as a WHERE ProveedorID = ID_Proveedor and AlmacenEntrega = ID_Almacen and OrdenCompra = '{$OC}'";
    $address = find_by_sql($query);

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

// Verificar si se proporciona el parámetro 'id_OC' en la URL
if (isset($_GET['id_OC'])) {
    $id_OC = $_GET['id_OC'];

    // Consultar el detalle de la orden de compra
    $query = "SELECT d.*, m.Nombre FROM detalleordencompra as d, productos as m WHERE ProductoID = Id_Producto and ID_OrdenCompra = '{$id_OC}' and CantidadPendiente > 0";
    $details = find_by_sql($query);

    // Verificar si se obtuvieron resultados
    if (!empty($details)) {
        // Imprimir los datos como un objeto JSON
        header('Content-Type: application/json');
        echo json_encode($details);
        exit; // Terminar la ejecución del script PHP después de enviar los datos
    } else {
        // Manejar el caso en que no se encuentren datos para el ID proporcionado
        // Puedes redirigir a una página de error o mostrar un mensaje apropiado
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No se encontraron detalles para la Orden de Compra proporcionada.']);
        exit; // Terminar la ejecución del script PHP después de enviar el mensaje de error
    }
}


//Metodo Post para Recibir la Cantidad de Materiales de la OC solo seran los Post que empiecen con ID_DetalleOrden_
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si se envió el botón para enviar la cantidad recibida
    if (isset($_POST['btnEnviarCantidad'])) {
        // Crear un array para almacenar los errores
        $errors = array();

        // Recorrer los datos recibidos para obtener las cantidades recibidas
        foreach ($_POST as $key => $value) {
            // Verificar si el nombre del campo comienza con 'ID_DetalleOrden_'
            if (strpos($key, 'ID_DetalleOrden_') === 0) {
                // Obtener el ID del detalle de la orden de compra
                $id_detalle = substr($key, 16);
                // Obtener la cantidad recibida
                $cantidad_recibida = (int)$value;

                // Verificar si la cantidad recibida es un número entero positivo, 
                if (!is_int($cantidad_recibida) || $cantidad_recibida < 0) {
                    $errors[] = 'La cantidad recibida debe ser un número entero positivo.';
                }

                // Verificar si la cantidad recibida es mayor que cero
                if ($cantidad_recibida > 0) {
                    // Actualizar la cantidad recibida en la base de datos

                    $query = "UPDATE detalleordencompra SET CantidadEntregada = CantidadEntregada + '{$cantidad_recibida}', CantidadPendiente = CantidadPendiente - '{$cantidad_recibida}' WHERE ID_DetalleOrden = '{$id_detalle}'";
                    $result = $db->query($query);
                    if ($result) {
                    } else {
                        $errors[] = 'Ocurrió un error';
                    }

                    //Crear una variable de identificacion de movimiento que solo se genere si no existe Ejemplo (GR-OC"YMM"-0001) "MM" es el mes de la fecha de ingreso y Y es el año
                    $query = "SELECT MAX(ID_Recepcion) as ID_Recepcion FROM recepcionmateriales";
                    $result = $db->query($query);
                    $row = $db->fetch_assoc($result);
                    $ID_Movimiento = $row['ID_Recepcion'] + 1;
                    $ID_Movimiento = str_pad($ID_Movimiento, 4, "0", STR_PAD_LEFT);
                    $ID_Movimiento = "GR-OC" . date('ym') . "-" . $ID_Movimiento;

                    //Obtener Datos de Factura, numero de factura y notas para ingresarlos a la tabla de registro_ingresos
                    $FechaFactura = $_POST['FechaFactura'];
                    $NumeroFactura = $_POST['NumeroFactura'];
                    $Notas = $_POST['Notas'];
                    $FechaIngreso = date('Y-m-d H:i:s');
                    $AlmacenID = $_POST['AlmacenEntregaID'];


                    //Ingresar los datos a la tabla llamaa recepcionmateriales con las siguientes columnas
                    //ID_Recepcion	ID_OrdenCompra	ID_DetalleOrdenCompra	FechaRecepcion	AlmacenID	NumeroFactura	FechaFactura	Notas	Estado	

                    $query = "INSERT INTO recepcionmateriales (ID_OrdenCompra, ID_DetalleOrdenCompra, FechaRecepcion, AlmacenID, NumeroFactura, FechaFactura, Notas, Movimiento) VALUES ('{$_POST['OC_id']}', '{$id_detalle}', '{$FechaIngreso}', '{$AlmacenID}', '{$NumeroFactura}', '{$FechaFactura}', '{$Notas}', '{$ID_Movimiento}')";
                    $result = $db->query($query);
                    if ($result) {
                    } else {
                        $errors[] = 'Ocurrió un error';
                    }




                    //Insertar las modificaciones en la tabla inventario con los campos
                    //ID_Inventario	ProductoID	CantidadEnStock	FechaCreacion	FechaModificacion	Notas	AlmacenID
                    $query = "SELECT ProductoID FROM detalleordencompra WHERE ID_DetalleOrden = '{$id_detalle}'";
                    $result = $db->query($query);
                    $row = $db->fetch_assoc($result);
                    $ProductoID = $row['ProductoID'];
                    //Verificar si el producto ya existe en el inventario
                    $query = "SELECT * FROM inventario WHERE ProductoID = '{$ProductoID}' and AlmacenID = '{$AlmacenID}'";
                    $result = $db->query($query);
                    if ($db->num_rows($result) > 0) {
                        //Actualizar la cantidad en stock
                        $query = "UPDATE inventario SET CantidadEnStock = CantidadEnStock + '{$cantidad_recibida}' WHERE ProductoID = '{$ProductoID}' and AlmacenID = '{$AlmacenID}'";
                        $result = $db->query($query);
                    } else {
                        //Insertar un nuevo registro en el inventario
                        $query = "INSERT INTO inventario (ProductoID, CantidadEnStock, FechaCreacion, FechaModificacion, Notas, AlmacenID) VALUES ('{$ProductoID}', '{$cantidad_recibida}', '{$FechaIngreso}', '{$FechaIngreso}', '{$Notas}', '{$AlmacenID}')";
                        $result = $db->query($query);
                    }



                    //Verificar que la CantidadPendiente sea 0 para cambiar el estado de la OC
                    $query = "SELECT CantidadPendiente FROM detalleordencompra WHERE ID_DetalleOrden = '{$id_detalle}'";
                    $result = $db->query($query);
                    $pendientes = 0;
                    while ($row = $db->fetch_assoc($result)) {
                        $pendientes += $row['CantidadPendiente'];
                    }
                    if ($pendientes == 0) {
                        $query = "UPDATE detalleordencompra SET Entrega = 1 WHERE ID_DetalleOrden = '{$id_detalle}'";
                        $result = $db->query($query);
                    }
                }
            }
        }

        //Verificar si la sumatoria de la columna CantidadPendiente es 0 para cambiar el estado de la OC
        $OC_id = $_POST['OC_id'];
        $query = "SELECT sum(CantidadPendiente) as total FROM detalleordencompra WHERE ID_OrdenCompra = '{$OC_id}'";
        $total = find_by_sql($query);
        if ($total[0]['total'] == 0) {
            $query = "UPDATE ordenescompra SET Estado = 'Cerrada' WHERE ID_OrdenCompra = '{$OC_id}'";
            $result = $db->query($query);
        }

        //Mostrar mensajes de Guardado y errores
        if (empty($errors)) {
            $session->msg('s', "Cantidad Recibida Guardada con el movimiento " . $ID_Movimiento);
            redirect('recepcion_material.php', false);
        } else {
            $session->msg('d', join('<br>', $errors));
            redirect('recepcion_material.php', false);
        }
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
                    <h1 class="h3 mb-3"><strong>Recepcion de Materiales</strong> Almacen</h1>
                </div>

                <!-- Input para Cargar los Datos de la Orden de Compra -->
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header pb-0 d-flex">
                                    <h5 class="card-title">Datos de la Orden de Compra</h5>
                                    <div class="col-auto ms-auto text-end mt-n1">
                                        <button type="submit" class="btn btn-primary " name="ValidarOC" id="ValidarOC">Validar</button>
                                    </div>
                                </div>
                                <div class="card-body pt-1">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="inputOrdenCompra">Orden de Compra</label>
                                                <input type="text" class="form-control" id="inputOrdenCompra" name="inputOrdenCompra" placeholder="Ingrese el Numero de Orden de Compra">
                                                <div class="" id="ValidadorOC"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="inputProveedor">Proveedor</label>
                                                <input type="text" class="form-control" id="inputProveedor" name="inputProveedor" placeholder="" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="AlmacenEntrega">Almacen de Entrega</label>
                                                <input type="text" class="form-control" id="AlmacenEntrega" name="AlmacenEntrega" placeholder="" disabled>
                                            </div>
                                        </div>
                                        <!--input hidden para tener el id de la OC-->
                                        <input type="hidden" id="idOC" name="idOC" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Fin Input para Cargar los Datos de la Orden de Compra -->
                    <!-- Cargar Detalle de la Orden de Compra en un formulario similar a una tabla -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h5 class="card-title">Detalle de la Orden de Compra</h5>
                                </div>
                                <div class="card-body pt-1">
                                    <div class="table-responsive">
                                        <form action="" method="POST">
                                            <!-- Div escondido con inputs de "fecha de factura", "numero de Factura" y "notas -->
                                            <div class="card d-none" id="datos_factura">
                                                <div class="card-header pb-0">
                                                    <h5 class="card-title">Datos de Factura</h5>
                                                </div>
                                                <div class="card-body pt-1">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="FechaFactura">Fecha de Factura</label>
                                                                <input type="date" class="form-control" id="FechaFactura" name="FechaFactura" placeholder="Ingrese la Fecha de Factura" require>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="NumeroFactura">Numero de Factura</label>
                                                                <input type="text" class="form-control" id="NumeroFactura" name="NumeroFactura" placeholder="Ingrese el Numero de Factura" require>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="Notas">Notas</label>
                                                                <input type="text" class="form-control" id="Notas" name="Notas" placeholder="Ingrese alguna Observacion">
                                                            </div>
                                                        </div>
                                                        <!--input hidden para tener el id de la OC-->
                                                        <input type="hidden" id="OC_id" name="OC_id" value="">
                                                        <input type="hidden" id="AlmacenEntregaID" name="AlmacenEntregaID" value="">
                                                    </div>
                                                </div>
                                            </div>



                                            <table class="table table-striped table-bordered" id="tableOC">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Nombre Material</th>
                                                        <th>Cantidad</th>
                                                        <th>Precio</th>
                                                        <th>Subtotal</th>
                                                        <th>Recibido</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Las filas se agregarán dinámicamente aquí -->
                                                </tbody>
                                            </table>
                                        </form>
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


    <?php include_once('layouts/scripts.php'); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            //Script para Cargar los Datos de la Orden de Compra atravez de una peticion GET por ajax sin jquery al dar click en el boton ValidarOC y cargar los datos en los inputs
            document.getElementById('ValidarOC').addEventListener('click', function() {
                var OC = document.getElementById('inputOrdenCompra').value;
                //Uppercase a OC
                OC = OC.toUpperCase();
                //Alerta si la OC esta vacia
                if (OC == "") {
                    alert("Ingrese el Numero de Orden de Compra");
                } else {
                    console.log(OC);
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', 'recepcion_material.php?OC=' + OC, true);
                    xhr.send();
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            var data = JSON.parse(xhr.responseText);
                            //Validar si la orden esta abierta, rechazada o no existe
                            if (data.Estado == "Cerrada") {
                                //Cambiar el #<div class="" id="ValidadorOC"></div> para mostrar el mensaje de error
                                document.getElementById('ValidadorOC').innerHTML = "La Orden de Compra esta Cerrada";
                                //Colocar clases de invalid-feddback al input de la OC
                                document.getElementById('inputOrdenCompra').classList.add("is-invalid");
                                document.getElementById('ValidadorOC').classList.add("invalid-feedback");

                                return;
                            } else if (data.Estado == "Rechazada") {
                                //Cambiar el #<div class="" id="ValidadorOC"></div> para mostrar el mensaje de error
                                document.getElementById('ValidadorOC').innerHTML = "La Orden de Compra esta Rechazada";
                                //Colocar clases de invalid-feddback al input de la OC
                                document.getElementById('inputOrdenCompra').classList.add("is-invalid");
                                document.getElementById('ValidadorOC').classList.add("invalid-feedback");
                                return;
                            } else if (data.error) {
                                //Cambiar el #<div class="" id="ValidadorOC"></div> para mostrar el mensaje de error
                                document.getElementById('ValidadorOC').innerHTML = "La Orden de Compra no Existe";
                                //Colocar clases de invalid-feddback al input de la OC
                                document.getElementById('inputOrdenCompra').classList.add("is-invalid");
                                document.getElementById('ValidadorOC').classList.add("invalid-feedback");
                                return;
                            }

                            //Cargar el id en el input hidden
                            document.getElementById('idOC').value = data.ID_OrdenCompra;

                            //Cambiar el #<div class="" id="ValidadorOC"></div> para mostrar el ok
                            document.getElementById('ValidadorOC').innerHTML = "Orden de Compra Valida";
                            document.getElementById('inputOrdenCompra').classList.add("is-valid");
                            document.getElementById('ValidadorOC').classList.add("valid-feedback");
                            //Quitar clases de invalid-feddback al input de la OC
                            document.getElementById('inputOrdenCompra').classList.remove("is-invalid");
                            document.getElementById('ValidadorOC').classList.remove("invalid-feedback");

                            //Cargar Datos
                            document.getElementById('inputProveedor').value = data.nombre;
                            document.getElementById('AlmacenEntrega').value = data.CodigoAlmacen;
                            document.getElementById('idOC').value = data.ID_OrdenCompra;
                            //Cargar el id del almacen en el input hidden
                            document.getElementById('AlmacenEntregaID').value = data.ID_Almacen;

                            //Desabilitar el input de la OC
                            document.getElementById('inputOrdenCompra').disabled = true;
                            //Cambiar el texto del boton
                            document.getElementById('ValidarOC').innerHTML = "Validado";
                            //Desabilitar el boton
                            document.getElementById('ValidarOC').disabled = true;

                            //Llamar a la funcion para cargar el detalle de la OC
                            cargarDetalleOC(data.ID_OrdenCompra);
                        }
                    }
                }
            }) //Fin del EventListener para validar la OC

            //Funcion para generar una tabla con el detalle de la OC atraves de una consulta a la base de datos cuando se valide la OC pero adentro de un form  para poder enviar la cantidad recibida
            function cargarDetalleOC(OC) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'recepcion_material.php?id_OC=' + OC, true);

                // Manejar la carga exitosa de la solicitud AJAX
                xhr.onload = function() {
                    if (xhr.status == 200) {
                        var data = JSON.parse(xhr.responseText);

                        console.log(data);
                        //Si no hay datos que mostrar Cambiar el #<div class="" id="ValidadorOC"></div> para mostrar el mensaje de error 
                        if (data.error) {
                            document.getElementById('ValidadorOC').innerHTML = "No hay lineas disponibles para la Orden de Compra";
                            document.getElementById('ValidadorOC').classList.add("invalid-feedback");
                            return;
                        }


                        // Cargar la información en la tabla
                        var table = document.getElementById('tableOC').getElementsByTagName('tbody')[0];
                        table.innerHTML = ''; // Limpiar el contenido de la tabla
                        for (var i = 0; i < data.length; i++) {
                            console.log(data[i]);
                            var newRow = table.insertRow(table.rows.length);
                            newRow.insertCell(0).innerHTML = i + 1;
                            newRow.insertCell(1).innerHTML = data[i].Nombre;
                            newRow.insertCell(2).innerHTML = data[i].CantidadPendiente;
                            //PrecioUnitario y Subtotal en formato de moneda americano
                            newRow.insertCell(3).innerHTML = new Intl.NumberFormat('es-MX', {
                                style: 'currency',
                                currency: 'MXN'
                            }).format(data[i].PrecioUnitario);
                            newRow.insertCell(4).innerHTML = new Intl.NumberFormat('es-MX', {
                                style: 'currency',
                                currency: 'MXN'
                            }).format(data[i].Subtotal);

                            newRow.insertCell(5).innerHTML = '<input type="number" class="form-control" id="ID_DetalleOrden_' + data[i].ID_DetalleOrden + '" name="ID_DetalleOrden_' + data[i].ID_DetalleOrden + '" placeholder="Cantidad Recibida" max="' + data[i].CantidadPendiente + '">';
                        }

                        //Agregar un boton para enviar la cantidad recibida, solo si la tabla tiene filas y no existe el boton y guardar el id de la OC
                        if (table.rows.length > 0 && !document.getElementById('btnEnviarCantidad')) {
                            var btnEnviarCantidad = document.createElement('button');
                            btnEnviarCantidad.type = 'submit';
                            btnEnviarCantidad.className = 'btn btn-primary';
                            btnEnviarCantidad.name = 'btnEnviarCantidad';
                            btnEnviarCantidad.id = 'btnEnviarCantidad';
                            btnEnviarCantidad.innerHTML = 'Enviar Cantidad Recibida';
                            document.querySelector('form').appendChild(btnEnviarCantidad);
                            //Quitar el d-none de la card de datos de factura
                            document.getElementById('datos_factura').classList.remove('d-none');
                            //Evitar el envio del formulario si no estan llenos los campos de fecha factura y numero de factura
                            document.getElementById('btnEnviarCantidad').addEventListener('click', function(event) {
                                var FechaFactura = document.getElementById('FechaFactura').value;
                                var NumeroFactura = document.getElementById('NumeroFactura').value;
                                if (FechaFactura == "" || NumeroFactura == "") {
                                    alert("Ingrese la Fecha de Factura y el Numero de Factura");
                                    event.preventDefault();
                                }
                            });

                        }

                        //Agregar el id de la OC a los inputs de la factura OC_id
                        document.getElementById('OC_id').value = OC;



                    } else {
                        console.error('Error en la solicitud:', xhr.status, xhr.statusText);
                    }
                };

                // Manejar errores durante la solicitud AJAX
                xhr.onerror = function() {
                    console.error('Error en la solicitud AJAX');
                };

                // Enviar la solicitud AJAX
                xhr.send();
            } //Fin de la funcion para cargar el detalle de la OC



        }); //Fin del DOMContentLoaded
    </script>
</body>

</html>