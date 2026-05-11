document.addEventListener('DOMContentLoaded', () => {
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));
    cargarUsuario();
});

function formatApiError(json, fallback = 'Ocurrio un error desconocido') {
    if (json && json.errors && typeof json.errors === 'object') {
        return Object.values(json.errors).join('\n');
    }

    return (json && (json.message || json.exception || json.error_db || json.error)) || fallback;
}

const NAME_PATTERN = /^[\p{L}]+(?:\s+[\p{L}]+)*$/u;
const ALIAS_PATTERN = /^[A-Za-z0-9]{3,25}$/;
const PASSWORD_PATTERN = /^(?=.*[A-Za-z])(?=.*\d).{8,72}$/;

function showValidationError(message, fieldId) {
    Swal.fire('Revise el formulario', message, 'warning').then(() => {
        document.getElementById(fieldId)?.focus();
    });

    return false;
}

function validateProfileForm(form) {
    const nombres = String(form.get('nombres_usuario') || '').trim();
    const apellidos = String(form.get('apellidos_usuario') || '').trim();
    const correo = String(form.get('correo_usuario') || '').trim();
    const alias = String(form.get('alias_usuario') || '').trim();
    const claveNueva = String(form.get('clave_usuario') || '');
    const confirmar = String(form.get('confirmar_clave') || '');

    if (!NAME_PATTERN.test(nombres) || nombres.length < 2 || nombres.length > 30) {
        return showValidationError('Los nombres deben tener de 2 a 30 caracteres y solo letras con espacios internos.', 'nombres_usuario');
    }

    if (!NAME_PATTERN.test(apellidos) || apellidos.length < 2 || apellidos.length > 30) {
        return showValidationError('Los apellidos deben tener de 2 a 30 caracteres y solo letras con espacios internos.', 'apellidos_usuario');
    }

    if (!correo || correo.length > 100 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        return showValidationError('Ingrese un correo valido de maximo 100 caracteres.', 'correo_usuario');
    }

    if (!ALIAS_PATTERN.test(alias)) {
        return showValidationError('El alias debe tener de 3 a 25 caracteres y solo letras o numeros.', 'alias_usuario');
    }

    if (claveNueva || confirmar) {
        if (!PASSWORD_PATTERN.test(claveNueva)) {
            return showValidationError('La clave debe tener minimo 8 caracteres e incluir al menos una letra y un numero.', 'clave_usuario');
        }

        if (claveNueva !== confirmar) {
            return showValidationError('La confirmacion de clave no coincide.', 'confirmar_clave');
        }
    }

    return true;
}

function cargarUsuario() {
    fetch(API_USUARIOS + 'readPerfil')
        .then(res => res.json())
        .then(json => {
            if (!json.status) {
                return Swal.fire('Error', formatApiError(json, 'No se pudo leer el usuario'), 'error');
            }

            const d = json.dataset;

            document.getElementById('id_usuario').value = d.id || '';
            document.getElementById('nombres_usuario').value = d.name || '';
            document.getElementById('apellidos_usuario').value = d.last_name || '';
            document.getElementById('correo_usuario').value = d.email || '';
            document.getElementById('alias_usuario').value = d.username || '';

            M.updateTextFields();
        })
        .catch(() => Swal.fire('Error', 'No se pudo leer el usuario', 'error'));
}

document.getElementById('save-form').addEventListener('submit', e => {
    e.preventDefault();

    const form = new FormData(e.target);

    if (!validateProfileForm(form)) {
        return;
    }

    fetch(API_USUARIOS + 'updatePerfil', {
        method: 'POST',
        body: form
    })
        .then(res => res.json())
        .then(json => {
            if (!json.status) {
                return Swal.fire('Error', formatApiError(json), 'error');
            }

            Swal.fire({
                icon: 'success',
                title: json.message,
                timer: 3000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        })
        .catch(() => Swal.fire('Error', 'No se pudo guardar el usuario', 'error'));
});
