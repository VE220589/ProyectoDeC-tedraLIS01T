document.addEventListener('DOMContentLoaded', () => {
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));

    // Verifica si hay usuarios cargados antes de permitir el uso normal del login.
    fetch(API_AUTH + 'exists')
        .then(r => r.json())
        .then(response => {

            if (response.status) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sesión requerida',
                    text: 'Debe autenticarse para ingresar'
                });

            } else {
                if (response.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.exception
                    });

                } else {
                    // Cuando no existen usuarios, avisa que la base inicial debe revisarse.
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin usuarios registrados, revise la base de datos',
                        text: response.exception
                    }).then(() => {
                         window.location.href = BASE_URL;
                    });
                }
            }
        })
        .catch(error => {
            console.error(error);
            Swal.fire("Error", "No se pudo verificar el estado del sistema", "error");
        });
});

function showValidationError(message, fieldId) {
    Swal.fire('Revise el formulario', message, 'warning').then(() => {
        document.getElementById(fieldId)?.focus();
    });

    return false;
}

function validateLoginForm(form) {
    const alias = String(form.get('alias_usuario') || '').trim();
    const clave = String(form.get('clave_usuario') || '');

    if (!/^[A-Za-z0-9]{3,25}$/.test(alias)) {
        return showValidationError('El alias debe tener de 3 a 25 caracteres y solo puede usar letras o numeros.', 'alias');
    }

    if (!clave || clave.length > 72) {
        return showValidationError('Ingrese una contrasena valida.', 'clave');
    }

    return true;
}

// Login local: envia alias y contrasena al endpoint PHP.
document.getElementById('session-form').addEventListener('submit', e => {
    e.preventDefault();
    const form = new FormData(e.target);

    if (!validateLoginForm(form)) {
        return;
    }

    fetch(API_AUTH + 'login', {
        method: 'POST',
        body: form
    })
        .then(r => r.json())
        .then(response => {

            if (response.status) {
                Swal.fire({
                    icon: 'success',
                    title: 'Bienvenido',
                    text: response.message
                }).then(() => {
                    window.location.href = URL_MAIN;
                });

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al iniciar sesión',
                    text: response.message || response.exception || 'Revise sus credenciales e intente nuevamente'
                });
            }
        })
        .catch(error => {
            console.error(error);
            Swal.fire("Error", "No se pudo procesar la solicitud", "error");
        });
});

window.handleGoogleCredential = response => {
    // Google devuelve un token firmado; el backend lo valida antes de iniciar sesion.
    const body = new FormData();
    body.append('credential', response.credential);

    fetch(API_AUTH + 'google', {
        method: 'POST',
        body
    })
        .then(r => r.json())
        .then(payload => {
            if (payload.status) {
                Swal.fire({
                    icon: 'success',
                    title: 'Bienvenido',
                    text: payload.message
                }).then(() => {
                    window.location.href = URL_MAIN;
                });
                return;
            }

            Swal.fire({
                icon: 'error',
                title: 'No se pudo iniciar con Google',
                text: payload.message || 'Intente nuevamente o use usuario y contrasena'
            });
        })
        .catch(error => {
            console.error(error);
            Swal.fire('Error', 'No se pudo validar la cuenta de Google', 'error');
        });
};

const renderGoogleButton = () => {
    const container = document.getElementById('google-login');

    if (!window.google || !GOOGLE_CLIENT_ID || !container) {
        return;
    }

    // El boton oficial de Google necesita un ancho numerico; se calcula segun la tarjeta.
    const width = Math.max(220, Math.min(400, Math.floor(container.getBoundingClientRect().width)));
    container.innerHTML = '';

    google.accounts.id.initialize({
        client_id: GOOGLE_CLIENT_ID,
        callback: handleGoogleCredential
    });

    google.accounts.id.renderButton(container, {
        theme: 'outline',
        size: 'large',
        width,
        text: 'signin_with'
    });
};

window.addEventListener('load', renderGoogleButton);

let googleResizeTimer = null;
window.addEventListener('resize', () => {
    clearTimeout(googleResizeTimer);
    googleResizeTimer = setTimeout(renderGoogleButton, 150);
});
