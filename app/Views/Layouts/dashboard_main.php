<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Dashboard - <?= $this->renderSection('title') ?></title>

    <link rel="icon" type="image/png" href="<?= base_url('resources/img/logo.png') ?>">
    <link rel="stylesheet" href="<?= base_url('resources/css/materialize.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('resources/css/material_icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('resources/css/dashboard.css') ?>">

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>

<body>

<header>
</header>

<main class="container">
    
    <h3 class="center-align"><?= $this->renderSection('title') ?></h3>

    <?= $this->renderSection('content') ?>
</main>

<script src="<?= base_url('resources/js/materialize.min.js') ?>"></script>
<script src="<?= base_url('resources/js/sweetalert.min.js') ?>"></script>
<script src="<?= base_url('resources/components.js') ?>"></script>
<?= $this->renderSection('scripts') ?>

</body>
</html>
