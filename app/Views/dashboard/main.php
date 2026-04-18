
<?= $this->extend('layouts/dashboard_public') ?>

<?= $this->section('title') ?>Iniciar sesión<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container">
   <!-- Se muestra un saludo de acuerdo con la hora del cliente -->
<div class="row">
    <h4 class="center-align blue-text" id="greeting"></h4>
</div>

<div class="container">

    <h4 class="center-align">
        Bienvenido, <?= session('alias_usuario'); ?>
    </h4>

    <h6 class="center-align grey-text">
        Rol: <?= strtoupper(session('tipo_usuario')); ?>
    </h6>

    <!-- ==================== TARJETAS SEGÚN ROL ==================== -->
    <div id="dashboardCards" class="row"></div>

    <!-- ==================== LISTADOS SEGÚN ROL ==================== -->
    <div id="dashboardLists" class="row"></div>

</div>


<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/dashboard/main.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const TICKET_URL = "<?= base_url('ticket') ?>";
    const BASE_URL = "<?= base_url('dashboard') ?>";
    const ROLE_ID  = <?= session('role_id'); ?>;
    const API_TICKETS  = "<?= base_url('api/tickets/') ?>";
    
</script>

<?= $this->endSection() ?>


