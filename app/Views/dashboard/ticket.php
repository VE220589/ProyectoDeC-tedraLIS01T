<?= $this->extend('layouts/dashboard_public') ?>

<?= $this->section('title') ?>Gestión de Tickets<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3 class="center-align">Gestión de Tickets</h3>
<div class="row">
    <?php if (in_array('tickets.create', session('permissions'))): ?>
            <a href="#" onclick="openCreateDialog()" 
        class="btn-large teal darken-1 tooltipped left"
        data-tooltip="Crear nuevo ticket">
            <i class="material-icons left">add_circle</i>
            Nuevo
    </a>
    <?php endif; ?>
    
</div>

<!-- Tabla para mostrar los registros existentes -->
<table id="myTable" class="highlight">
    <!-- Cabeza de la tabla para mostrar los títulos de las columnas -->
    <thead>
        <tr>
            <th>TICKET</th>
            <th>TITULO</th>
            <th>DESCRIPCIÓN</th>
            <th>TIPO</th>
            <th>ESTADO</th>
            <th>PRIORIDAD</th>
            <th>SERVICIO</th>
            <th>CREADO POR</th>
            <th>ASIGNADO A</th>
            <th>CERRADO POR</th>
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
            <input class="hide" type="text" id="id_ticket" name="id_ticket"/>

            <div class="row">
                <div class="input-field col s12 m6" id="titlecontainer">
                    <i class="material-icons prefix">list_alt</i>
                    <input id="title" type="text" name="title" class="validate" required/>
                    <label for="title">Titulo</label>
                </div>

                <div class="input-field col s12 m6" id="desccontainer">
                    <i class="material-icons prefix">description</i>
                    <input id="desc" type="text" name="desc" class="validate" required/>
                    <label for="desc">Descripción</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">note_add</i>
                    <select id="id_tipo_ticket" name="id_tipo_ticket" required>
                        <option value="incident">Incidente</option>
                        <option value="problem">Problema</option>
                        <option value="change">Cambio</option>
                    </select>
                    <label for="id_tipo_ticket">Tipo de ticket</label>
                </div>


                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">priority_high</i>
                    <select id="prioridad" name="prioridad" required>
                        <option value="S">S (Crítica)</option>
                        <option value="A">A (Alta)</option>
                        <option value="B">B (Media)</option>
                        <option value="C">C (Baja)</option>
                    </select>
                    <label for="prioridad">Prioridad</label>
                </div>


                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">bookmark_manager</i>
                    <select id="id_servicio" name="id_servicio" required>
                        <option value="" disabled selected>Seleccione un Servicio</option>
                    </select>
                    <label for="id_servicio">Servicio</label>
                </div>
               
                     <div class="input-field col s12 m6" id="check_container">
                        <p>
                            <label>
                                <input type="checkbox"  id="miCheck"/>
                                <span>¿Desea asignar un usuario al ticket?</span>
                            </label>
                        </p>
                    </div>
                     <div class="input-field col s12 m6" id="usuarios_container">
                        <i class="material-icons prefix">assignment_ind</i>
                        <select id="id_asignado" name="id_asignado" required>
                        </select>
                        <label for="id_asignado">Asignar a:</label>
                    </div>
                
               

                <div class="input-field col s12 m6" id="estado_container">
                    <i class="material-icons prefix">assignment_turned_in</i>
                    <select id="estado" name="estado" required>
                        <option value="open">Abierto</option>
                        <option value="in_progress">En progreso</option>
                        <option value="mitigated">Mitigado</option>
                    </select>
                    <label for="estado">Estado</label>
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

<!-- Componente Modal de actividades para mostrar una caja de dialogo -->
<div id="view-modal" class="modal modal-grande">
    <div class="modal-content">
        <h4 id="modal-title" class="center-align">Gestión de notas y actividades</h4>

        <form method="post" id="saveview-form">
            <input class="hide" type="text" id="id_ticketnota" name="id_ticketnota"/>
            <div class="row">
                <div id="createnote">
                    <div class="input-field col s12 m6">
                    <i class="material-icons prefix">description</i>
                    <input id="descnote" type="text" name="descnote" class="validate" required/>
                    <label for="descnote">Descripción de la nota</label>
                    </div>
                    <div class="input-field col s12 m6">
                        <button type="submit" 
                            class="btn-large waves-effect blue tooltipped left" 
                            data-tooltip="Guardar">
                            <i class="material-icons left">add_circle</i>
                            Agregar nota
                        </button>
                    </div>
                </div>

                <table class="highlight">
                    <!-- Cabeza de la tabla para mostrar los títulos de las columnas -->
                    <thead>
                        <tr>
                            <th>TICKET</th>
                            <th>ACTIVIDAD</th>
                            <th>CREADO POR</th>
                            <th>FECHA</th>
                            <th>TIPO</th>
                        </tr>
                    </thead>
                    <!-- Cuerpo de la tabla para mostrar un registro por fila -->
                    <tbody id="tbody1-rows">
                    </tbody>
                </table>
            </div>
            <div class="row center-align">
                <a href="#" class="btn waves-effect grey tooltipped modal-close" data-tooltip="Cancelar">
                    <i class="material-icons">cancel</i>
                </a>
            </div>
        </form>
    </div>
</div>



<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/dashboard/main.js') ?>"></script>
<script src="<?= base_url('js/dashboard/tickets.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const tipoUsuario = "<?= session()->get('tipo_usuario') ?>";
</script>
<script>
    const ROLE_ID = <?= session()->get('role_id') ?>;
</script>
<script>
    const MAIN_URL = "<?= base_url('main') ?>";
    const BASE_URL = "<?= base_url('dashboard') ?>";
    const API_TICKETS  = "<?= base_url('api/tickets/') ?>";
    const API_NOTAS  = "<?= base_url('api/notas/') ?>";
</script>
<?= $this->endSection() ?>

