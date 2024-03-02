<?php include_once('includes/load.php'); ?>
<?php
$req_fields = array('user', 'password');
validate_fields($req_fields);
$username = remove_junk($_POST['user']);
$password = remove_junk($_POST['password']);

if (empty($errors)) {
    $user_id = authenticate($username, $password);
    if ($user_id) {
        //create session with id
        $session->login($user_id);
        //Update Sign in time
        updateLastLogIn($user_id);
        $session->msg("s", "Bienvenido a TariSLP | ERP.");
        redirect('home.php', false);
    } else {
        $session->msg("d", "Usuario ó Contraseña Incorrecto.");
        redirect('index.php', false);
    }
} else {
    $session->msg("d", $errors);
    redirect('index.php', false);
}

?>