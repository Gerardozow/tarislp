<?php
$errors = array();

/*--------------------------------------------------------------*/
/* Function for Remove escapes special characters in a string for use in an SQL statement
/* Funcion para eliminar caracteres especialesen una cadena para usar en una declaración SQL
/*--------------------------------------------------------------*/
function real_escape($str)
{
  global $con;
  $escape = mysqli_real_escape_string($con, $str);
  return $escape;
}
/*--------------------------------------------------------------*/
/* Function for Remove html characters
/* Funcion para eliminar caracteres html
/*--------------------------------------------------------------*/
function remove_junk($str)
{
  $str = nl2br($str);
  $str = htmlspecialchars(strip_tags($str, ENT_QUOTES));
  return $str;
}
/*--------------------------------------------------------------*/
/* Function for Uppercase first character
/* Funcion para poner la primera letra en mayuscula
/*--------------------------------------------------------------*/
function first_character($str)
{
  $val = str_replace('-', " ", $str);
  $val = ucfirst($val);
  return $val;
}
/*--------------------------------------------------------------*/
/* Function for Checking input fields not empty
/* Funcion para verificar que los campos de entrada no esten vacios
/*--------------------------------------------------------------*/
function validate_fields($var)
{
  global $errors;
  foreach ($var as $field) {
    $val = remove_junk($_POST[$field]);
    if (isset($val) && $val == '') {
      $errors = "Verificar la informacion ingresada en los campos " . $field . " esta vacio.";
      return $errors;
    }
  }
}
/*--------------------------------------------------------------*/
/* Function for Display Session Message Ex echo displayt_msg($message);
/* Funcion para mostrar mensaje de sesion Ej echo displayt_msg($message);
/*--------------------------------------------------------------*/
function display_msg($msg = array())
{
  $output = '';
  if (!empty($msg)) {
    foreach ($msg as $key => $value) {
      $output .= "<div class=\"alert alert-{$key} alert-dismissible\" role=\"alert\">";
      $output .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
      $output .= '<div class="alert-icon"><i data-feather="bell"></i></div>';
      $output .= '<div class="alert-message">' . remove_junk(first_character($value)) .    '</div>';
      $output .= "</div>";
    }
    return $output;
  } else {
    return "";
  }
}

/*--------------------------------------------------------------*/
/* Function for redirect
/* Funcion para redireccionar
/*--------------------------------------------------------------*/
function redirect($url, $permanent = false)
{
  if (headers_sent() === false) {
    header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
  }

  exit();
}
/*--------------------------------------------------------------*/
/* Function for get timestamp and convert to readable format
/* Funcion para obtener la marca de tiempo y convertirla en un formato legible
/*--------------------------------------------------------------*/
function make_date($timestamp = null)
{
  if ($timestamp === null) {
    $timestamp = time();
  }
  // Establecer el huso horario a México
  date_default_timezone_set('America/Mexico_City');
  return date("Y-m-d H:i:s", $timestamp);
}

/*--------------------------------------------------------------*/
/* Function password validate
/* Funcion para validar contraseña
/*--------------------------------------------------------------*/
function password_validate($password, $confirm_password)
{
  global $errors;
  if (remove_junk($_POST[$password]) != remove_junk($_POST[$confirm_password])) {
    $errors = "No coiciden las contraseñas";
    return $errors;
  }
}

/*--------------------------------------------------------------*/
/* Function to validate user registration
/* Funcion para validar registro de usuario
/*--------------------------------------------------------------*/
function username_validation($username)
{
  global $errors;

  $exist = find_username($_POST[$username]);
  if ($exist === true) {
  } else {
    $errors = 'Usuario ya existe.';
    return $errors;
  }
}

/*--------------------------------------------------------------*/
/* Function to validate email registration
/* Function validar registro de email
/*--------------------------------------------------------------*/
function email_validation($email)
{
  global $errors;

  $exist = find_email($_POST[$email]);
  if ($exist === true) {
  } else {
    $errors = 'Email ya registrado.';
    return $errors;
  }
}

/*--------------------------------------------------------------*/
/* Password generation function
/* Funcion para generar contraseña  
/*--------------------------------------------------------------*/
function generarContrasena($longitud)
{
  $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  $contrasena = '';

  for ($i = 0; $i < $longitud; $i++) {
    $indice = mt_rand(0, strlen($caracteres) - 1);
    $contrasena .= $caracteres[$indice];
  }

  return $contrasena;
}

/*--------------------------------------------------------------*/

/*--------------------------------------------------------------*/
/* DEBUGUEAR VARIABLES */
/*--------------------------------------------------------------*/
function debug($var)
{
  echo '<pre>';
  var_dump($var);
  echo '</pre>';
  exit;
}
/*--------------------------------------------------------------*/


/*--------------------------------------------------------------*/
/** Generar un método para crear un hash para restablecer la contraseña */
/*--------------------------------------------------------------*/
function generateHash($length = 32)
{
  $hash = bin2hex(random_bytes($length));

  return $hash;
}


/*--------------------------------------------------------------*/
/** Funcion para hacer un contador*/
/*--------------------------------------------------------------*/
function count_id()
{
  static $count = 1;
  return $count++;
}
