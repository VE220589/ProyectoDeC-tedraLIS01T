<?= $this->extend('Layouts/dashboard_main') ?>

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

    .login-card .btn {
        width: 100%;
        border-radius: 8px;
    }

    .google-login-wrapper {
        width: 100%;
        max-width: 100%;
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }

    #google-login {
        width: 100%;
        max-width: 100%;
        min-height: 44px;
    }

    #google-login > div,
    #google-login iframe {
        width: 100% !important;
        max-width: 100% !important;
    }

    @media (max-width: 600px) {
        .login-card {
            padding: 24px 18px;
        }

        .brand-logo-login {
            height: 70px;
        }
    }
</style>

<div class="container">
    <div class="row">
        <div class="col s12 m6 offset-m3">
            <div class="card login-card z-depth-3 white">

                <!-- Logo -->
                <img src="/resources/img/logo.png" height="80" class="brand-logo-login">

                <h5 class="center-align">Bienvenido</h5>

                <form method="post" id="session-form" action="/api/auth/login">

                    <div class="input-field">
                        <i class="material-icons prefix">person</i>
                        <input id="alias" type="text" name="alias_usuario" minlength="3" maxlength="25" pattern="[A-Za-z0-9]{3,25}" required />
                        <label for="alias">Alias</label>
                    </div>

                    <div class="input-field">
                        <i class="material-icons prefix">lock</i>
                        <input id="clave" type="password" name="clave_usuario" maxlength="72" required />
                        <label for="clave">Contraseña</label>
                    </div>

                    <div class="center-align" style="margin-top: 20px;">
                        <button type="submit" class="btn waves-effect waves-light blue darken-3">
                            <i class="material-icons left">send</i>Ingresar
                        </button>
                    </div>
                </form>

                <?php if (env('GOOGLE_CLIENT_ID')): ?>
                    <div class="google-login-wrapper">
                        <div id="google-login"></div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/js/dashboard/index.js?v=<?= filemtime(FCPATH . 'js/dashboard/index.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const URL_MAIN = appUrl('main');
</script>
<script>
    const BASE_URL = appUrl('dashboard');
    const API_AUTH = appUrl('api/auth/');
    const GOOGLE_CLIENT_ID = "<?= esc(env('GOOGLE_CLIENT_ID') ?? '') ?>";
</script>
<?php if (env('GOOGLE_CLIENT_ID')): ?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<?php endif; ?>
<?= $this->endSection() ?>

