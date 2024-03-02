<?php

require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
}

// Obtener el término de búsqueda desde la solicitud AJAX
$searchTerm = $_POST['search'];

// Realizar la consulta a la base de datos
$sql = "SELECT * FROM productos WHERE codigo LIKE '%$searchTerm%'";
$result = $conn->query($sql);

// Construir las opciones para el elemento select
$options = '';
while ($row = $result->fetch_assoc()) {
    $options .= '<option value="' . $row['valor'] . '">' . $row['etiqueta'] . '</option>';
}

echo $options;
