<?= $this->extend('layouts/dashboard_public') ?>

<?= $this->section('title') ?>Gestión de roles<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3 class="center-align">Gestión de Roles</h3>
<!-- Tabla para mostrar los registros existentes -->
<table class="highlight">
    <!-- Cabeza de la tabla para mostrar los títulos de las columnas -->
    <thead>
        <tr>
            <th>ROL</th>
            <th>Módulo de usuarios</th>
            <th>Módulo de servicios</th>
            <th>Módulo de tickets</th>
        </tr>
    </thead>
    <!-- Cuerpo de la tabla para mostrar un registro por fila -->
    <tbody id="tbody-rows">
    </tbody>
</table>

<div id="save-modal" class="modal">
    <div class="modal-content">
        <h4 id="modal-title" class="center-align">Visualización de permisos</h4>
        <form method="post" id="save-form">    
            <input class="hide" type="text" id="id_rol" name="id_rol"/>
            <input  type="text" id="modulo" name="modulo"/>
                <div class="row center-align">
                    <div class="switch">
                        <b>Crear</b>
                        <label>
                        <input type="checkbox" name="create" id="create">
                        <span class="lever"></span>
                        </label>
                    </div>
                    <div class="switch" id="update1">
                        <b>Actualizar</b>
                        <label>
                        <input type="checkbox" name="update" id="update">
                        <span class="lever"></span>
                        </label>
                    </div>
                    <div class="switch" id="delete1">
                        <b>Eliminar</b>
                        <label>
                        <input type="checkbox"  name="delete" id="delete">
                        <span class="lever"></span>
                        </label>
                    </div>
                    <div class="switch">
                        <b>Consultar</b>
                        <label>
                        <input type="checkbox"  name="show" id="show">
                        <span class="lever"></span>
                        </label>
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
<script src="<?= base_url('js/dashboard/main.js') ?>"></script>
<script src="<?= base_url('js/dashboard/roles.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const tipoUsuario = "<?= session()->get('tipo_usuario') ?>";
</script>
<script>
    const BASE_URL = "<?= base_url('dashboard') ?>";
    const API_ROLES = "<?= base_url('api/rolest/') ?>";
    const API_PERMI = "<?= base_url('api/permisos/') ?>";
</script>
<?= $this->endSection() ?>
