document.addEventListener('DOMContentLoaded', () => {
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));

    // Verificar si existen usuarios
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
                    // Cuando NO existen usuarios → redirigir a Registro
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


// Evento para iniciar sesión
document.getElementById('session-form').addEventListener('submit', e => {
    e.preventDefault();

    fetch(API_AUTH + 'login', {
        method: 'POST',
        body: new FormData(e.target)
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
                    text: response.exception
                });
            }
        })
        .catch(error => {
            console.error(error);
            Swal.fire("Error", "No se pudo procesar la solicitud", "error");
        });
});
