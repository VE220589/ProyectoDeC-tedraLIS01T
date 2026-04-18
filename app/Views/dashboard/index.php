<?= $this->extend('layouts/dashboard_main') ?>

<?= $this->section('title') ?>Iniciar sesión<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    body {
        background: linear-gradient(135deg, #0077b6, #00b4d8, #90e0ef);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-card {
        padding: 30px;
        border-radius: 12px;
        animation: fadeIn .6s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .brand-logo-login {
        display: block;
        margin: 0 auto 15px;
    }
</style>

<div class="container">
    <div class="row">
        <div class="col s12 m6 offset-m3">
            <div class="card login-card z-depth-3 white">

                <!-- Logo -->
                <img src="/NIT104/public/resources/img/logo.png" height="80" class="brand-logo-login">

                <h5 class="center-align">Bienvenido</h5>

                <form method="post" id="session-form">

                    <div class="input-field">
                        <i class="material-icons prefix">person</i>
                        <input id="alias" type="text" name="alias_usuario" required />
                        <label for="alias">Alias</label>
                    </div>

                    <div class="input-field">
                        <i class="material-icons prefix">lock</i>
                        <input id="clave" type="password" name="clave_usuario" required />
                        <label for="clave">Contraseña</label>
                    </div>

                    <div class="center-align" style="margin-top: 20px;">
                        <button type="submit" class="btn waves-effect waves-light blue darken-3" style="width: 100%; border-radius: 8px;">
                            <i class="material-icons left">send</i>Ingresar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/dashboard/index.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const URL_MAIN = "<?= base_url('main') ?>";
</script>
<script>
    const BASE_URL = "<?= base_url('dashboard') ?>";
    const API_AUTH = "<?= base_url('api/auth/') ?>";
</script>
<?= $this->endSection() ?>

