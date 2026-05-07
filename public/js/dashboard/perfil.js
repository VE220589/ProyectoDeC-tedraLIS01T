document.addEventListener('DOMContentLoaded', () => {
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));
    cargarUsuario();
});

function cargarUsuario() {
    fetch(API_USUARIOS + 'readPerfil')
        .then(res => res.json())
        .then(json => {
            if (!json.status) {
                return Swal.fire('Error', json.message || json.exception || 'No se pudo leer el usuario', 'error');
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
    const claveNueva = form.get('clave_usuario');
    const confirmar = form.get('confirmar_clave');

    if (claveNueva && claveNueva !== confirmar) {
        return Swal.fire('Error', 'Las contrasenas no coinciden.', 'error');
    }

    fetch(API_USUARIOS + 'updatePerfil', {
        method: 'POST',
        body: form
    })
        .then(res => res.json())
        .then(json => {
            if (!json.status) {
                const errorMessage =
                    json.message ||
                    json.exception ||
                    json.error_db ||
                    JSON.stringify(json.errors) ||
                    'Ocurrio un error desconocido';

                return Swal.fire('Error', errorMessage, 'error');
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
