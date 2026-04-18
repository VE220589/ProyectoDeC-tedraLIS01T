<?= $this->extend('layouts/dashboard_public') ?>

<?= $this->section('title') ?>Gestión de servicios<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3 class="center-align">Gestión de Servicios</h3>
<div class="row">
    <?php if (in_array('services.create', session('permissions'))): ?>
        <a href="#" onclick="openCreateDialog()" class="btn-large teal darken-1 tooltipped left"
        data-tooltip="Crear nuevo registro">
        <i class="material-icons left">add_circle</i> Nuevo</a>
    <?php endif; ?>
</div>

<table id="myTable" class="highlight">
    <!-- Cabeza de la tabla para mostrar los títulos de las columnas -->
    <thead>
        <tr>
            <th>DESCRIPCIÓN</th>
            <th>CLASIFICACIÓN</th>
            <th>ESTADO</th>
            <th class="actions-column">ACCIONES</th>
        </tr>
    </thead>
    <!-- Cuerpo de la tabla para mostrar un registro por fila -->
    <tbody id="tbody-rows">
    </tbody>
</table>

<!-- Componente Modal para mostrar una caja de dialogo -->
<div id="save-modal" class="modal">
    <div class="modal-content">
        <h4 id="modal-title" class="center-align"></h4>

        <form method="post" id="save-form">
            <input class="hide" type="text" id="id_servicio" name="id_servicio"/>

            <div class="row">
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">assignment</i>
                    <input id="desc" type="text" name="desc" class="validate" required/>
                    <label for="desc">Servicio</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">assignment_ind</i>
                    <select class="forselect" id="id_tipo_servicio" name="id_tipo"  required>
                        <option value="" disabled selected>Seleccione un tipo</option>
                    </select>
                    <label for="id_tipo_servicio">Tipo de usuario</label>
                </div>

            </div>

            <div class="row center-align">
                <a href="#" class="btn waves-effect grey tooltipped modal-close" data-tooltip="Cancelar">
                    <i class="material-icons">cancel</i>
                </a>
                <button type="submit" class="btn waves-effect blue tooltipped" data-tooltip="Guardar">
                    <i class="material-icons">save</i>
                </button>
            </div>
        </form>
    </div>
</div>



<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const USER_PERMISSIONS = <?= json_encode(session('permissions')); ?>;
</script>
<script src="<?= base_url('js/dashboard/main.js') ?>"></script>
<script src="<?= base_url('js/dashboard/servicios.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const tipoUsuario = "<?= session()->get('tipo_usuario') ?>";
</script>
<script>
    const BASE_URL = "<?= base_url('dashboard') ?>";
    const API_SERVICIOS = "<?= base_url('api/services/') ?>";
</script>
<?= $this->endSection() ?>

