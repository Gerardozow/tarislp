<?php
    if ($_FILES['image']['name']) {
        if (!$_FILES['image']['error']) {
            $name = md5(rand(100, 200));
            $ext = explode('.', $_FILES['image']['name']);
            $filename = $name . '.' . $ext[1];
            $destination = '../uploads/editor/' . $filename;
            $location = $_FILES["image"]["tmp_name"];
            move_uploaded_file($location, $destination);
            echo '/uploads/editor/' . $filename;//change this URL
        }
    else
    {
      echo  $message = '¡Ooops!  Su carga ha provocado el siguiente error:  '.$_FILES['image']['error'];
    }
}
?>