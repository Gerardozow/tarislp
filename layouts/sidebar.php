<?php
//definir los permisos de usuario
//obener el user level de tabla users
$user_level = find_by_sql('SELECT user_level FROM users WHERE id =' . (int)$_SESSION['user_id']);
$user_level = $user_level[0]['user_level'];
?>

<!-- Sidebar -->
<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="index.html">
            <span class="align-middle">TariSLP | ERP</span>
        </a>

        <ul class="sidebar-nav">
            <!-- Dashboard -->
            <li class="sidebar-header">Pages</li>

            <li class="sidebar-item <?= $page == "home" ? "active" : ""; ?>">
                <a class="sidebar-link" href="home.php"> <i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Dashboard</span> </a>
            </li>
            <!-- Fin Dashboard -->

            <!-- Menu Compras -->
            <li class="sidebar-header">Materiales</li>

            <li class="sidebar-item <?= $separador == "Compras" ? "active" : ""; ?>">
                <a data-bs-target="#Compras_Menu" data-bs-toggle="collapse" class="sidebar-link <?= $separador == "Compras" ? "" : "collapsed"; ?>"><i class="align-middle" data-feather="dollar-sign"></i>Compras</a>
                <ul id="Compras_Menu" class="sidebar-dropdown list-unstyled collapse <?= $separador == "Compras" ? "show" : ""; ?>">
                    <li class="sidebar-item <?= $page == "Dashboard_compras" ? "active" : ""; ?>">
                        <a class="sidebar-link" href="./compras.php">General</a>
                    </li>
                    <li class="sidebar-item <?= $page == "Proveedores" ? "active" : ""; ?>">
                        <a class="sidebar-link" href="./proveedores.php">Proveedores</a>
                    </li>
                    <li class="sidebar-item <?= $page == "OrdenesCompra" ? "active" : ""; ?>">
                        <a class="sidebar-link" href="./ordenes_compra.php">Ordenes de Compra</a>
                    </li>
                    <li class="sidebar-item <?= $page == "Materiales" ? "active" : ""; ?>">
                        <a class="sidebar-link" href="./materiales.php">Materiales</a>
                    </li>
                </ul>
            </li>

            <!-- Fin Menu Compras -->

            <!-- Menu Almacen -->
            <li class="sidebar-header">Almacen</li>

            <li class="sidebar-item <?= $separador == "Almacen" ? "active" : ""; ?>">
                <a data-bs-target="#Almacen_menu" data-bs-toggle="collapse" class="sidebar-link <?= $separador == "Almacen" ? "" : "collapsed"; ?>"><i class="align-middle" data-feather="package"></i>Almacen</a>
                <ul id="Almacen_menu" class="sidebar-dropdown list-unstyled collapse <?= $separador == "Almacen" ? "show" : ""; ?>">
                    <!-- <li class="sidebar-item <?= $page == "Dashboard_Almacen" ? "active" : ""; ?>">
                        <a class="sidebar-link" href="./almacen_general.php">General</a>
                    </li> -->
                    <li class="sidebar-item <?= $page == "Movimientos" ? "active" : ""; ?>">
                        <a class="sidebar-link" href="./movimientos.php">Movimientos</a>
                    <li class="sidebar-item <?= $page == "Inventario" ? "active" : ""; ?>">
                        <a class="sidebar-link" href="./inventario.php">Reporte Inventario</a>
                    </li>
                    <li class="sidebar-item <?= $page == "recibo_material" ? "active" : ""; ?>">
                        <a class="sidebar-link" href="./recepcion_material.php">Recepcion de Materiales</a>
                    </li>
                    <li class="sidebar-item <?= $page == "Almacenes" ? "active" : ""; ?>">
                        <a class="sidebar-link" href="./almacenes.php">Almacenes</a>
                    </li>
                </ul>
            </li>


            <?php
            //mostrar solo si el usuario es administrador
            if ($user_level == 1) { ?>
                <!-- Menu HR -->
                <li class="sidebar-header">HR</li>

                <li class="sidebar-item <?= $separador == "HR" ? "active" : ""; ?>">
                    <a data-bs-target="#HR_menu" data-bs-toggle="collapse" class="sidebar-link <?= $separador == "Usuarios" ? "" : "collapsed"; ?>"><i class="align-middle" data-feather="users"></i>HR</a>
                    <ul id="HR_menu" class="sidebar-dropdown list-unstyled collapse <?= $separador == "Usuarios" ? "show" : ""; ?>">
                        <li class="sidebar-item <?= $page == "Usuarios" ? "active" : ""; ?>">
                            <a class="sidebar-link" href="./usuarios.php">Usuarios</a>
                        </li>
                    </ul>
                </li>
            <?php
            }
            ?>


            <!-- Fin Menu Principal -->

            <!-- Configuracion -->
            <li class="sidebar-header">Configuracion</li>

            <li class="sidebar-item <?= $page == "perfil" ? "active" : ""; ?>">
                <a class="sidebar-link" href="perfil.php"> <i class="align-middle" data-feather="user"></i> <span class="align-middle">Perfil</span> </a>
            </li>

            <li class="sidebar-item <?= $page == "configuracion" ? "active" : ""; ?>">
                <a class="sidebar-link" href="configuracion.php"> <i class="align-middle" data-feather="settings"></i> <span class="align-middle">Ajustes</span> </a>
            </li>
            <!-- Fin Configuracion -->


        </ul>

    </div>
</nav>
<!-- Fin Sidebar -->