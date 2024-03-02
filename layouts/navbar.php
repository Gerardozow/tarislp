<?php $user = find_by_id('users', $_SESSION['user_id']);
$apellido = explode(" ", $user['last_name']);
$rol = find_by_sql("SELECT u.group_name FROM user_groups AS u JOIN users ON u.group_level = users.user_level WHERE users.id = '{$user['id']}'");

?>
<!-- navbar -->
<nav class="navbar navbar-expand navbar-light navbar-bg">
    <a class="sidebar-toggle js-sidebar-toggle">
        <i class="hamburger align-self-center"></i>
    </a>

    <div class="navbar-collapse collapse">

        <ul class="navbar-nav navbar-align">
            <li class="nav-item dropdown">
                <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
                    <i class="align-middle" data-feather="settings"></i>
                </a>

                <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
                    <img src="<?php $image = !empty($user['image']) ? $user['image'] : 'default.png';
                                echo 'uploads/users/' . $image ?>" class="avatar img-fluid rounded me-1" alt="<?= $user['name'] . " " . $apellido[0]  ?>" /> <span class="text-dark"><?= $user['name'] . " " . $apellido[0]  ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="perfil.php"><i class="align-middle me-1" data-feather="user"></i> Perfil</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="./configuracion.php"><i class="align-middle me-1" data-feather="settings"></i> Configuración y privacidad</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="./logout.php">Salir</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
<!-- Fin navbar -->