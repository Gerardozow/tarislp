<?php
$title_page = 'Registro';
require_once('includes/load.php');

include_once('layouts/head.php');


if (isset($_POST['add_user'])) {
  $req_fields = array('user', 'name', 'last_name', 'email', 'password', 'confirm-password');
  validate_fields($req_fields);

  //validar la que la contraseña este confirmada
  if (password_validate('password', 'confirm-password') != null) {
    $errors = password_validate('password', 'confirm-password');
  }

  if (empty($errors)) {
    $user = remove_junk($db->escape($_POST['user']));
    $name = remove_junk($db->escape($_POST['name']));
    $last_name = remove_junk($db->escape($_POST['last_name']));
    $email = remove_junk($db->escape($_POST['email']));
    $password = remove_junk($db->escape($_POST['password']));
    $password = sha1($password);
    $query = "INSERT INTO users (";
    $query .= "username, name, last_name, email, password";
    $query .= ") VALUES (";
    $query .= " '{$user}', '{$name}', '{$last_name}', '{$email}', '{$password}'";
    $query .= ")";
    $result = $db->query($query);
    //detectar si la consulta fue exitosa y no hubo errores

    if ($result && $db->affected_rows() === 1) {
      $session->msg('s', "Cuenta creada exitosamente! ");
      redirect('rh.php', false);
    } else {
      $session->msg('d', ' Lo siento, registro falló.');
      redirect('registro.php', false);
    }
  } else {
    $session->msg("d", $errors);
    redirect('registro.php', false);
  }
}


?>



<?php

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
          <h1 class="h3 mb-3"><strong>General</strong> Dashboard</h1>

        </div>
        <div class="card">
          <div class="card-body">
            <div class="m-sm-3">
              <form method="POST">
                <?php echo display_msg($msg); ?>
                <div class="mb-3">
                  <label class="form-label" for="user">Usuario</label>
                  <input class="form-control form-control-lg" type="text" name="user" id="user" placeholder="@" />
                </div>
                <div class="mb-3">
                  <label class="form-label" for="name">Nombre</label>
                  <input class="form-control form-control-lg" type="text" name="name" id="name" placeholder="Ingresa tu nombre" />
                </div>
                <div class="mb-3">
                  <label class="form-label" for="last_name">Apellidos</label>
                  <input class="form-control form-control-lg" type="text" name="last_name" id="last_name" placeholder="Ingresa tus Apellidos" />
                </div>
                <div class="mb-3">
                  <label class="form-label" for="email">Email</label>
                  <input class="form-control form-control-lg" type="text" name="email" id="email" placeholder="email@example.com" />
                </div>
                <div class="mb-3">
                  <label class="form-label" for="password">Contraseña</label>
                  <input class="form-control form-control-lg" type="password" name="password" id="password" placeholder="Ingresa una contraseña" />
                </div>
                <div class="mb-3">
                  <label class="form-label" for="confirm-password">Confirma tu contraseña</label>
                  <input class="form-control form-control-lg" type="password" name="confirm-password" id="confirm-password" placeholder="Confirma tu contraseña" />
                </div>
                <div class="d-grid gap-2 mt-3">
                  <button class="btn btn-success" type="submit" name="add_user">Registrar</button>
                  <button class="btn btn-info" type="reset">Limpiar</button>
                </div>
              </form>
            </div>
          </div>
      </main>

      <!-- Fin Contenedor main -->
      <?php include_once('layouts/footer.php'); ?>

    </div>
  </div>

  <?php include_once('layouts/scripts.php'); ?>
</body>

</html>