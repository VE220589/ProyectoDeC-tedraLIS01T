document.addEventListener('DOMContentLoaded', () => {
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));
    cargarUsuario();
});

// ============================================================
// CARGAR DATOS DEL USUARIO
// ============================================================
function cargarUsuario() {
    const form = new FormData();
    form.append('id_usuario', iduser);

    fetch(API_USUARIOS + 'readOne', {
        method: 'POST',
        body: form
    })
    .then(res => res.json())
    .then(json => {
        if (!json.status) {
            return Swal.fire("Error", json.exception, "error");
        }

        const d = json.dataset;

        document.getElementById('id_usuario').value = d.id;
        document.getElementById('nombres_usuario').value = d.name;
        document.getElementById('apellidos_usuario').value = d.last_name;
        document.getElementById('correo_usuario').value = d.email;
        document.getElementById('alias_usuario').value = d.username;

        M.updateTextFields();
    })
    .catch(() => Swal.fire("Error", "No se pudo leer el usuario", "error"));
}


// ============================================================
// GUARDAR PERFIL
// ============================================================
document.getElementById('save-form').addEventListener('submit', e => {
    e.preventDefault();

    const form = new FormData(e.target);

    // ==========================
    // Validaciones en frontend
    // ==========================
    const claveActual = form.get("clave_actual");
    if (!claveActual || claveActual.trim() === "") {
        return Swal.fire("Error", "Debe ingresar su contraseña actual.", "error");
    }

    const claveNueva = form.get("clave_usuario");
    const confirmar = form.get("confirmar_clave");

    if (claveNueva) {
        if (claveNueva !== confirmar) {
            return Swal.fire("Error", "Las contraseñas no coinciden.", "error");
        }
    }

    // ======================
    // Llamar API update
    // ======================
    fetch(API_USUARIOS + 'updatePerfil', {
        method: 'POST',
        body: form
    })
    .then(res => res.json())
    .then(json => {

        console.log(json);

        if (!json.status) {
            let errorMessage = 
                json.exception ||
                json.error_db ||
                JSON.stringify(json.errors) ||
                json.message ||
                "Ocurrió un error desconocido";

            return Swal.fire("Error", errorMessage, "error");
        }

        Swal.fire({
            icon: "success",
            title: json.message,
            timer: 3000,
            showConfirmButton: false
        }).then(() => {
            location.reload(); 
        });

    })
    .catch(() => Swal.fire("Error", "No se pudo guardar el usuario", "error"));
});
