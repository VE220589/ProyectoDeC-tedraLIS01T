<?= $this->extend('layouts/dashboard_public') ?>

<?= $this->section('title') ?>Gestión de usuarios<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3 class="center-align">Gestión de Usuarios</h3>
<div class="row">
    <?php if (in_array('users.create', session('permissions'))): ?>
        <a href="#" onclick="openCreateDialog()" class="btn-large teal darken-1 tooltipped left"
        data-tooltip="Crear nuevo registro">
        <i class="material-icons left">add_circle</i> Nuevo</a>
    <?php endif; ?>
</div>

<!-- Tabla para mostrar los registros existentes -->
<table id="myTable" class="highlight">
    <!-- Cabeza de la tabla para mostrar los títulos de las columnas -->
    <thead>
        <tr>
            <th>APELLIDOS</th>
            <th>NOMBRES</th>
            <th>CORREO</th>
            <th>ALIAS</th>
            <th>ROL</th>
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
            <input class="hide" type="text" id="id_usuario" name="id_usuario"/>

            <div class="row">
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">person</i>
                    <input id="nombres_usuario" type="text" name="nombres_usuario" class="validate" required/>
                    <label for="nombres_usuario">Nombres</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">person</i>
                    <input id="apellidos_usuario" type="text" name="apellidos_usuario" class="validate" required/>
                    <label for="apellidos_usuario">Apellidos</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">email</i>
                    <input id="correo_usuario" type="email" name="correo_usuario" class="validate" required/>
                    <label for="correo_usuario">Correo</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">person_pin</i>
                    <input id="alias_usuario" type="text" name="alias_usuario" class="validate" readonly/>
                    <label for="alias_usuario">Alias</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">security</i>
                    <input id="clave_usuario" type="password" name="clave_usuario" class="validate" required/>
                    <label for="clave_usuario">Clave</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">security</i>
                    <input id="confirmar_clave" type="password" name="confirmar_clave" class="validate" required/>
                    <label for="confirmar_clave">Confirmar clave</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">assignment_ind</i>
                    <select id="id_tipo_usuario" name="id_tipo" required>
                        <option value="" disabled selected>Seleccione un tipo</option>
                    </select>
                    <label for="id_tipo_usuario">Tipo de usuario</label>
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
<script src="<?= base_url('js/dashboard/usuarios.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const tipoUsuario = "<?= session()->get('tipo_usuario') ?>";
</script>
<script>
    const BASE_URL = "<?= base_url('dashboard') ?>";
    const API_USUARIOS = "<?= base_url('api/usuarios/') ?>";
</script>
<?= $this->endSection() ?>

