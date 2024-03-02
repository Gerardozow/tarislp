<?php
$title_page = 'Perfil';
//Menus Sidebar
$page = 'perfil';
require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) {
    redirect('index.php', false);
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
                    <div class="mb-3">
                        <h1 class="h3 d-inline align-middle">Perfil</h1>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-xl-3">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Detalles del Perfil</h5>
                                </div>
                                <div class="card-body text-center">
                                    <img src="<?php $image = !empty($user['image']) ? $user['image'] : 'default.png';
                                                echo 'uploads/users/' . $image ?>" alt="<?= $user['name'] . ' ' . $user['last_name']; ?>" class="img-fluid rounded-circle mb-2" width="128" height="128" />
                                    <h5 class="card-title mb-1"><?= $user['name'] . " " . $user['last_name'] ?></h5>
                                    <div class="text-muted mb-0"><span class="fw-bold">@</span><?= $user['username'] ?></div>

                                </div>
                                <hr class="my-0" />
                                <div class="card-body">
                                    <h5 class="h6 card-title">Mas Informacion</h5>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-1"><span data-feather="mail" class="feather-sm me-1"></span> <?= $user['email'] ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8 col-xl-9">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Ultimo Login</h5>
                                </div>
                                <div class="card-body h-100">
                                    <div>
                                        <?php
                                        $fecha = $user['last_login'];
                                        $fecha = date("d-m-Y H:i", strtotime($fecha));
                                        echo $fecha;
                                        ?></div>
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
</body>

</html>