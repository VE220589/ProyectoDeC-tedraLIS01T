<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Dashboard - <?= $this->renderSection('title') ?></title>

    <link rel="icon" type="image/png" href="/resources/img/logo.png">
    <link rel="stylesheet" href="/resources/css/materialize.min.css">
    <link rel="stylesheet" href="/resources/css/material_icons.css">
    <link rel="stylesheet" href="/resources/css/dashboard.css">

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>

<body>

<header>
</header>

<main class="container">
    
    <h3 class="center-align"><?= $this->renderSection('title') ?></h3>

    <?= $this->renderSection('content') ?>
</main>

<script src="/resources/js/materialize.min.js"></script>
<script src="/resources/js/sweetalert.min.js"></script>
<script src="/resources/components.js"></script>
<script>
    const APP_ORIGIN = window.location.origin;
    const appUrl = path => `${APP_ORIGIN}/${String(path).replace(/^\/+/, '')}`;
</script>
<?= $this->renderSection('scripts') ?>

</body>
</html>
