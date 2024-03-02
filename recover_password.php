<?php
$title_page = "Login";
ob_start();
require_once('includes/load.php');





/** Generar un metodo para enviar un correo electronico */
function sendEmail($email, $hash)
{
    $to = $email;
    $subject = "Recuperar contraseña";
    $txt = "Hola, haz solicitado recuperar tu contraseña, para restablecerla haz click en el siguiente enlace: http://localhost:8080/reset_password.php?hash=$hash";
    $headers = "From:noreplay@gzow.me" . "\r\n";
    mail($to, $subject, $txt, $headers);
}


if (isset($_POST['recover'])) {
    $email = $_POST['email'];
    $hash = generateHash();
    /** Verificar si existe un registro en la tabla recover_password y remplazar hash, si no existe registro de user_id hay insertarlo */
    $sql = "SELECT r.email FROM recover_password as r WHERE email='$email'";
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
        $sql = "UPDATE recover_password SET hash='$hash' WHERE email='$email'";
        $result = $db->query($sql);
        sendEmail($email, $hash);
        $session->msg('s', "Si el correo $email existe, se enviara un correo electronico para restablecer tu contraseña");
        redirect('index.php', false);
    } else {
        /** Verificar si existe el correo en la tabla users */
        $sql = "SELECT u.email FROM users as u WHERE email='$email'";
        $result = $db->query($sql);
        if ($result->num_rows > 0) {
            $sql = "INSERT INTO recover_password (email, hash) VALUES ('$email', '$hash')";
            $result = $db->query($sql);
            sendEmail($email, $hash);
            $session->msg('s', "Si el correo $email existe, se enviara un correo electronico para restablecer tu contraseña");
            redirect('index.php', false);
        } else {
            $session->msg('s', "SSi el correo $email existe, se enviara un correo electronico para restablecer tu contraseña");
            redirect('index.php', false);
        }
    }
}



include_once('layouts/head.php');
?>



<body>
    <main class="d-flex w-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">
                        <div class="text-center mt-4">
                            <h1 class="h2">Restablece tu contraseña</h1>
                            <p class="lead">Ingresa tu email para restablecer tu contraseña</p>
                            <?php echo display_msg($msg); ?>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <form method="POST" action="./recover_password.php">
                                        <div class="mb-3">
                                            <label class="form-label" for="email">Email</label>
                                            <input class="form-control form-control-lg" type="email" id="email" name="email" placeholder="Ingresa tu email" />
                                        </div>
                                        <div class="d-grid gap-2 mt-3">
                                            <input type="submit" class='btn btn-lg btn-primary' name="recover" value="Recuperar Contraseña">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/app.js"></script>
</body>

</html>