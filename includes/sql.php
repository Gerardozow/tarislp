<?php
//require_once('includes/load.php');

/*--------------------------------------------------------------*/
/* Function for find all database table rows by table name
/* Funcion para buscar todas las filas de la tabla de la base de datos por nombre de tabla
/*--------------------------------------------------------------*/
function find_all($table)
{
  global $db;
  if (tableExists($table)) {
    return find_by_sql("SELECT * FROM " . $db->escape($table));
  }
}
/*--------------------------------------------------------------*/
/* Function for Perform queries
/* Funcion para realizar consultas
/*--------------------------------------------------------------*/
function find_by_sql($sql)
{
  global $db;
  $result = $db->query($sql);
  $result_set = $db->while_loop($result);
  return $result_set;
}
/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/* Funcion para buscar datos de la tabla por id
/*--------------------------------------------------------------*/
function find_by_id($table, $id)
{
  global $db;
  $id = (int)$id;
  if (tableExists($table)) {
    $sql = $db->query("SELECT * FROM {$db->escape($table)} WHERE id='{$db->escape($id)}' LIMIT 1");
    if ($result = $db->fetch_assoc($sql))
      return $result;
    else
      return null;
  }
}
/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/* Funcion para buscar datos de la tabla por id
/*--------------------------------------------------------------*/
function find_by_email($email)
{
  global $db;
  $sql = $db->query("SELECT id FROM users WHERE email='{$db->escape($email)}' LIMIT 1");
  if ($result = $db->fetch_assoc($sql))
    return $result;
  else
    return null;
}
/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/* Funcion para eliminar datos de la tabla por id
/*--------------------------------------------------------------*/
function delete_by_id($table, $id)
{
  global $db;
  if (tableExists($table)) {
    $sql = "DELETE FROM " . $db->escape($table);
    $sql .= " WHERE id=" . $db->escape($id);
    $sql .= " LIMIT 1";
    $db->query($sql);
    return ($db->affected_rows() === 1) ? true : false;
  }
}
/*--------------------------------------------------------------*/
/* Function for Count id  By table name
/* Funcion para contar id por nombre de tabla
/*--------------------------------------------------------------*/

function count_by_id($table)
{
  global $db;
  if (tableExists($table)) {
    $sql    = "SELECT COUNT(id) AS total FROM " . $db->escape($table);
    $result = $db->query($sql);
    return ($db->fetch_assoc($result));
  }
}
/*--------------------------------------------------------------*/
/* Determine if database table exists
/* Determinar si existe la tabla de base de datos
/*--------------------------------------------------------------*/
function tableExists($table)
{
  global $db;
  $table_exit = $db->query('SHOW TABLES FROM ' . DB_NAME . ' LIKE "' . $db->escape($table) . '"');
  if ($table_exit) {
    if ($db->num_rows($table_exit) > 0)
      return true;
    else
      return false;
  }
}
/*--------------------------------------------------------------*/
/* Login with the data provided in $_POST,coming from the login form.
/* Iniciar sesión con los datos proporcionados en $_POST, procedentes del formulario de inicio de sesión.
/*--------------------------------------------------------------*/
function authenticate($username = '', $password = '')
{
  global $db;
  $username = $db->escape($username);
  $password = $db->escape($password);
  $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username COLLATE utf8_general_ci ='%s' LIMIT 1", $username);
  $result = $db->query($sql);
  if ($db->num_rows($result)) {
    $user = $db->fetch_assoc($result);
    $password_request = sha1($password);
    if ($password_request === $user['password']) {
      return $user['id'];
    }
  }
  return false;
}
/*--------------------------------------------------------------*/
/* Login with the data provided in $_POST,coming from the login_v2.php form. If you used this method then remove authenticate function.
/* Iniciar sesión con los datos proporcionados en $_POST, procedentes del formulario de inicio de sesión. Si utilizó este método, elimine la función de autenticación.
/*--------------------------------------------------------------*/
function authenticate_v2($username = '', $password = '')
{
  global $db;
  $username = $db->escape($username);
  $password = $db->escape($password);
  $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
  $result = $db->query($sql);
  if ($db->num_rows($result)) {
    $user = $db->fetch_assoc($result);
    $password_request = sha1($password);
    if ($password_request === $user['password']) {
      return $user;
    }
  }
  return false;
}


/*--------------------------------------------------------------*/
/* Find current log in user by session id
/* Buscar usuario de inicio de sesión actual por id de sesión
/*--------------------------------------------------------------*/
function current_user()
{
  static $current_user;
  global $db;
  if (!$current_user) {
    if (isset($_SESSION['user_id'])) :
      $user_id = intval($_SESSION['user_id']);
      $current_user = find_by_id('users', $user_id);
    endif;
  }
  return $current_user;
}
/*--------------------------------------------------------------*/
/* Find all user by Joining users table and user gropus table
/* Buscar todos los usuarios uniendo la tabla de usuarios y las tablas de grupos de usuarios
/*--------------------------------------------------------------*/
function find_all_user()
{
  global $db;
  $results = array();
  $sql = "SELECT u.id,u.name,u.username,u.user_level,u.status,u.last_login,";
  $sql .= "g.group_name ";
  $sql .= "FROM users u ";
  $sql .= "LEFT JOIN user_groups g ";
  $sql .= "ON g.group_level=u.user_level ORDER BY u.name ASC";
  $result = find_by_sql($sql);
  return $result;
}
/*--------------------------------------------------------------*/
/* Function to update the last log in of a user
/* Función para actualizar el último inicio de sesión de un usuario
/*--------------------------------------------------------------*/

function updateLastLogIn($user_id)
{
  global $db;
  $date = make_date();
  $sql = "UPDATE users SET last_login='{$date}' WHERE id ='{$user_id}' LIMIT 1";
  $result = $db->query($sql);
  return ($result && $db->affected_rows() === 1 ? true : false);
}

/*--------------------------------------------------------------*/
/* Find all Group name
/* Buscar todos los nombres de grupo
/*--------------------------------------------------------------*/
function find_by_groupName($val)
{
  global $db;
  $sql = "SELECT group_name FROM user_groups WHERE group_name = '{$db->escape($val)}' LIMIT 1 ";
  $result = $db->query($sql);
  return ($db->num_rows($result) === 0 ? true : false);
}
/*--------------------------------------------------------------*/
/* Find group level
/* Buscar nivel de grupo
/*--------------------------------------------------------------*/
function find_by_groupLevel($level)
{
  global $db;
  $response = array();

  $sql = "SELECT group_status, group_level FROM user_groups WHERE group_level = '{$db->escape($level)}' LIMIT 1 ";
  $result = $db->query($sql);

  $response['num_rows'] = $db->num_rows($result);

  if ($response['num_rows'] > 0) {
    $groupData = $db->fetch_assoc($result);
    $response['group_status'] = $groupData['group_status'];
    $response['group_level'] = $groupData['group_level'];
  } else {
    $response['group_status'] = null;
    $response['group_level'] = null;
  }

  return $response;
}
/*--------------------------------------------------------------*/
/* Function for cheaking which user level has access to page
/* Función para verificar qué nivel de usuario tiene acceso a la página
/*--------------------------------------------------------------*/
function page_require_level($require_level)
{
  global $session;
  $current_user = current_user();
  $login_level = find_by_groupLevel($current_user['user_level']);
  //if user not login
  if (!$session->isUserLoggedIn(true)) :
    $session->msg('d', 'Please login...');
    redirect('index.php', false);
  //if Group status Deactive
  elseif ($login_level['group_status'] === '0') :
    $session->msg('d', 'Este nivel de usuario ha sido desactivado');
    redirect('home.php', false);
  //cheackin log in User level and Require level is Less than or equal to
  elseif ($current_user['user_level'] <= (int)$require_level) :
    return true;
  else :
    $session->msg("d", "Lo sentimos, no tiene permiso para ver la página.");
    redirect('home.php', false);
  endif;
}

/*--------------------------------------------------------------*/
/* Function para saber si el user name existe en la tabla users
/*--------------------------------------------------------------*/


function find_username($val)
{
  global $db;
  $sql = "SELECT username FROM users WHERE username = '{$db->escape($val)}' LIMIT 1 ";
  $result = $db->query($sql);
  return ($db->num_rows($result) === 0 ? true : false);
}

/*--------------------------------------------------------------*/
/* Function para saber si existe el email en la tabla users
/*--------------------------------------------------------------*/
function find_email($val)
{
  global $db;
  $sql = "SELECT email FROM users WHERE email = '{$db->escape($val)}' LIMIT 1 ";
  $result = $db->query($sql);
  return ($db->num_rows($result) === 0 ? true : false);
}


/*--------------------------------------------------------------*/
/* Function remplazar imagen de perfil
/*--------------------------------------------------------------*/

function replace_image_profile($consulta)
{
  global $db;
  $result = $db->query($consulta);
  return ($result && $db->affected_rows() === 1 ? true : false);
}


/*--------------------------------------------------------------*/
/* Fucion para actualizar informacion de usuario */
/*--------------------------------------------------------------*/
function update_user_info_public($id, $username, $bio)
{
  global $db;
  $sql = "UPDATE users SET username='{$username}', bio='{$bio}' WHERE id='{$id}'";
  $result = $db->query($sql);
  return ($db->affected_rows() === 1 ? true : false);
}
/*--------------------------------------------------------------*/



/*--------------------------------------------------------------*/
/* Funcion para dar acceso a las peginas por nivel
/*--------------------------------------------------------------*/
function page_require_level_access($require_level)
{
  global $session;
  $current_user = current_user();
  $login_level = find_by_groupLevel($current_user['user_level']);
  //if user not login
  if (!$session->isUserLoggedIn(true)) :
    $session->msg('d', 'Please login...');
    redirect('index.php', false);
  //if Group status Deactive
  elseif ($login_level['group_status'] === '0') :
    $session->msg('d', 'Este nivel de usuario ha sido desactivado');
    redirect('home.php', false);
  //cheackin log in User level and Require level is Less than or equal to
  elseif ($current_user['user_level'] <= (int)$require_level) :
    return true;
  else :
    $session->msg("d", "Lo sentimos, no tiene permiso para ver la página.");
    redirect('home.php', false);
  endif;
}
