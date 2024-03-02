<?php
$title_page = "Login";
ob_start();
require_once('includes/load.php');
if ($session->isUserLoggedIn(true)) {
  redirect('home.php', false);
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
              <h1 class="h2">¡Bienvenido!</h1>
              <p class="lead">Inicie sesión en su cuenta para continuar</p>
              <?php echo display_msg($msg); ?>
            </div>

            <div class="card">
              <div class="card-body">
                <div class="m-sm-3">
                  <form method="POST" action="./auth.php ">
                    <div class="mb-3">
                      <label class="form-label" for="user">Usuario</label>
                      <input class="form-control form-control-lg" type="user" name="user" id="user" placeholder="Introduce tu usuario" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="password">Contraseña</label>
                      <input class="form-control form-control-lg" type="password" name="password" id="password" placeholder="Introduce tu contraseña" />
                      <small>
                        <a href="/recover_password.php">¿Olvidaste tu constraseña?</a>
                      </small>
                    </div>
                    <div>
                      <div class="form-check align-items-center">
                        <input id="customControlInline" type="checkbox" class="form-check-input" value="remember-me" name="remember-me" checked />
                        <label class="form-check-label text-small" for="customControlInline">Recuerdame</label>
                      </div>
                    </div>
                    <div class="d-grid gap-2 mt-3">
                      <input type="submit" value="Iniciar Sesión" class="btn btn-primary">
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