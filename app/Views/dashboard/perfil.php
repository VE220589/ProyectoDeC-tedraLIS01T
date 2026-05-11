<?= $this->extend('Layouts/dashboard_public') ?>

<?= $this->section('title') ?>Gestión del perfil<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h3 class="center-align">Mi cuenta</h3>
  <div class="container">
    <div class="col s12 m6">
      <div class="card white">
        <div class="card-content black-text">
          <span class="card-title">Gestionar cuenta</span>
          <form method="post" id="save-form">
            <input class="hide" type="text" id="id_usuario" name="id_usuario"/>

            <div class="row">
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">person</i>
                    <input id="nombres_usuario" type="text" name="nombres_usuario" class="validate" minlength="2" maxlength="30" required/>
                    <label for="nombres_usuario">Nombres</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">person</i>
                    <input id="apellidos_usuario" type="text" name="apellidos_usuario" class="validate" minlength="2" maxlength="30" required/>
                    <label for="apellidos_usuario">Apellidos</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">email</i>
                    <input id="correo_usuario" type="email" name="correo_usuario" class="validate" maxlength="100" required/>
                    <label for="correo_usuario">Correo</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">person_pin</i>
                    <input id="alias_usuario" type="text" name="alias_usuario" class="validate" minlength="3" maxlength="25" pattern="[A-Za-z0-9]{3,25}" required/>
                    <label for="alias_usuario">Alias</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">security</i>
                    <input id="clave_usuario" type="password" name="clave_usuario" class="validate" minlength="8" maxlength="72" pattern="(?=.*[A-Za-z])(?=.*\d).{8,72}"/>
                    <label for="clave_usuario">Clave</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">security</i>
                    <input id="confirmar_clave" type="password" name="confirmar_clave" class="validate" minlength="8" maxlength="72"/>
                    <label for="confirmar_clave">Confirmar clave</label>
                </div>

                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">security</i>
                    <input id="clave_actual" type="password" name="clave_actual" class="validate" maxlength="72"/>
                    <label for="clave_actual">Clave actual</label>
                </div>

            </div>

            <div class="row center-align">
                <button type="submit" class="btn waves-effect blue tooltipped" data-tooltip="Guardar datos">
                    <i class="material-icons">save</i>
                </button>
            </div>
        </form>
        </div>
      </div>
    </div>
  </div>
         


<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/dashboard/main.js') ?>"></script>
<script src="<?= base_url('js/dashboard/perfil.js?v=' . filemtime(FCPATH . 'js/dashboard/perfil.js')) ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const tipoUsuario = "<?= session()->get('tipo_usuario') ?>";
</script>
<script>
    const iduser = "<?= session()->get('id_usuario') ?>";
</script>
<script>
    const BASE_URL = "<?= base_url('dashboard') ?>";
    const API_USUARIOS = "<?= base_url('api/usuarios/') ?>";
</script>
<?= $this->endSection() ?>

