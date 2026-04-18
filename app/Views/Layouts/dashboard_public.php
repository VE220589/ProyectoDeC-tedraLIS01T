
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Dashboard - <?= $this->renderSection('title') ?></title>

    <link rel="icon" type="image/png" href="<?= base_url('resources/img/logo.png') ?>">
    <link rel="stylesheet" href="<?= base_url('resources/css/materialize.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('resources/css/material_icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('resources/datatable/dataTables.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('resources/css/dashboard.css') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>

<body>

<header>
                        <div class="navbar-fixed">
                            <nav class="teal">
                                <div class="nav-wrapper">
                                    <a href="main" class="brand-logo right"><img src="/NIT104/public/resources/img/logo.png" height="60"></a>
                                    <a href="#" data-target="mobile" class="sidenav-trigger"><i class="material-icons">menu</i></a>
                                    <ul class="left hide-on-med-and-down">
                                        <?php if (in_array('roles.view', session('permissions'))): ?>
                                            <li><a href="roles"><i class="material-icons left">admin_panel_settings</i>Roles</a></li>
                                        <?php endif; ?>
                                        <?php if (in_array('services.view', session('permissions'))): ?>
                                            <li><a href="servicios"><i class="material-icons left">handyman</i>Servicios</a></li>
                                        <?php endif; ?>
                                        <?php if (in_array('tickets.view', session('permissions'))): ?>
                                            <li><a href="ticket"><i class="material-icons left">assignment</i>Tickets</a></li>
                                        <?php endif; ?>
                                             <?php if (in_array('users.view', session('permissions'))): ?>
                                        <li><a href="usuarios1"><i class="material-icons left">group</i>Usuarios</a></li>
                                            <?php endif; ?>
                                        <li><a href="#" class="dropdown-trigger" data-target="dropdown"><i class="material-icons left">verified_user</i>Cuenta: <b><?= session()->get('alias_usuario') ?? 'Invitado' ?></b></a></li>
                                    </ul>
                                    <ul id="dropdown" class="dropdown-content">
                                        <li><a href="#" onclick="openProfileDialog()"><i class="material-icons">face</i><?= session()->get('tipo_usuario') ?></a></li>
                                        <li><a href="perfil"><i class="material-icons">lock</i>Ver cuenta</a></li>
                                        <li><a href="#" onclick="logOut()"><i class="material-icons">clear</i>Salir</a></li>
                                    </ul>
                                </div>
                            </nav>
                        </div>
                        <ul class="sidenav" id="mobile">
                            <?php if (in_array('roles.view', session('permissions'))): ?>
                                <li><a href="roles"><i class="material-icons">admin_panel_settings</i>Roles</a></li>
                            <?php endif; ?>
                            <?php if (in_array('services.view', session('permissions'))): ?>
                                <li><a href="servicios"><i class="material-icons">handyman</i>Servicios</a></li>
                            <?php endif; ?>
                            <?php if (in_array('tickets.view', session('permissions'))): ?>
                                <li><a href="ticket"><i class="material-icons">assignment</i>Tickets</a></li>
                            <?php endif; ?>
                            <li><a href="usuarios1"><i class="material-icons">group</i>Usuarios</a></li>
                            <li><a class="dropdown-trigger" href="#" data-target="dropdown-mobile"><i class="material-icons">verified_user</i>Cuenta: <b><?= session()->get('alias_usuario') ?? 'Invitado' ?></b></a></li>
                        </ul>
                        <ul id="dropdown-mobile" class="dropdown-content">
                            <li><a href="#" onclick="openProfileDialog()"><i class="material-icons">face</i><?= session()->get('tipo_usuario') ?></a></li>
                            <li><a href="perfil"><i class="material-icons">lock</i>Ver cuenta</a></li>
                            <li><a href="#" onclick="logOut()"><i class="material-icons">clear</i>Salir</a></li>
                        </ul>
                    </header>

<main class="container">                                       
    <?= $this->renderSection('content') ?>
   
</main>

<footer class="page-footer teal">
    <div class="container">
        <p class="white-text">Derechos reservados 2025</p>
    </div>
</footer>


<script src="<?= base_url('resources/js/materialize.min.js') ?>"></script>
<script src="<?= base_url('resources/js/sweetalert.min.js') ?>"></script>
<script src="<?= base_url('resources/components.js') ?>"></script>
<script src="<?= base_url('js/dashboard/initialization.js') ?>"></script>
<script src="<?= base_url('resources/datatable/jquery-3.7.1.min.js') ?>"></script>
<script src="<?= base_url('resources/datatable/dataTables.min.js') ?>"></script>
<script>
    const API_AUTH = "<?= base_url('api/auth/') ?>";
</script>
<?= $this->renderSection('scripts') ?>

</body>
</html>

