<?php

require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}

//Metodo Get para validar el SKU y regresar el nombre del SKU por JSON a la peticion AJAX
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['sku'])) {
        $sku = $_GET['sku'];
        $sql = "SELECT * FROM productos WHERE Codigo = '$sku'";
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
            echo json_encode(['error' => 'No existe el material solicitado.']);
            exit; // Terminar la ejecución del script PHP después de enviar el mensaje de error
        }
    }
}
