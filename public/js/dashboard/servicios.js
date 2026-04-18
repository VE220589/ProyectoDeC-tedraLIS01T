document.addEventListener('DOMContentLoaded', () => {
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));
    M.Modal.init(document.querySelectorAll('.modal'));
    cargarServicios();
    cargarCombos();
});

// ===============================
// Cargar combos (TIPO y ESTADO)
// ===============================

// ===============================
// Cargar combos (TIPO y ESTADO)
// ===============================

function cargarCombos() {
    // Para tipos de usuario
    cargarCombo('getTipo', 'id_tipo_servicio', 'id', 'nombre');

    // Para estados de usuario
    //cargarCombo('getEstado', 'id_estado_usuario', 'id', 'nombre');
}

function cargarCombo(endpoint, selectId, idField, textField) {
    fetch(API_SERVICIOS + endpoint)
        .then(res => res.json())
        .then(json => {
            if (!json.status) return;

            const select = document.getElementById(selectId);
            select.innerHTML = ''; // Limpiar opciones anteriores

            // Agregar opciones desde la base de datos
            json.dataset.forEach(item => {
                select.innerHTML += `<option value="${item[idField]}">${item[textField]}</option>`;
            });

            // Inicializa el select de Materialize
            M.FormSelect.init(select);
        })
        .catch(err => console.error("Error cargando combo:", err));
}



// ===============================
// CARGA DE TABLA
// ===============================

function cargarServicios() {
    fetch(API_SERVICIOS + 'index')
        .then(res => res.json())
        .then(json => {
            console.log(json); // Ver respuesta en consola

            if (json.status) {
                // Verificar si dataset tiene datos
                if (json.dataset && json.dataset.length > 0) {
                    llenarTabla(json.dataset);
                } else {
                    Swal.fire("Sin datos", "No se encontraron usuarios.", "info");
                }
            } else {
                Swal.fire("Error", json.exception, "error");
            }
        })
        .catch(() => Swal.fire("Error", "No se pudo cargar la tabla", "error"));
}

function hasPermission(permission) {
    return USER_PERMISSIONS.includes(permission);
}

let tabla = null;

function llenarTabla(dataset) {
    let content = '';

    dataset.forEach(row => {
        let btnUpdate = '';
        let btnDelete = '';

        if (hasPermission('services.update')) {
            btnUpdate = `
                <a class="btn blue tooltipped" data-tooltip="Actualizar"
                    onclick="openUpdateDialog(${row.id})">
                    <i class="material-icons">mode_edit</i>
                </a>`;
        }

        if (hasPermission('services.delete')) {
            btnDelete = `
                <a class="btn red tooltipped" data-tooltip="Eliminar"
                    onclick="openDeleteDialog(${row.id})">
                    <i class="material-icons">delete</i>
                </a>`;
        }

        content += `
            <tr>
                <td>${row.description}</td>
                <td>${row.tipo}</td>
                <td>${row.is_active}</td>
                <td>${btnUpdate} ${btnDelete}</td>
            </tr>
        `;
    });

    // 1) Si DataTable ya existe, destruirlo ANTES de cambiar el HTML
    if (tabla !== null) {
        tabla.destroy();
        tabla = null;
    }

    // 2) Reemplazar el contenido de la tabla
    document.getElementById('tbody-rows').innerHTML = content;

    // 3) Inicializar DataTable correctamente (versión 2.x)
    tabla = new DataTable('#myTable', {
        responsive: true
    });

    // 4) Reactivar tooltips
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));
}



// ===============================
// CREAR
// ===============================

window.openCreateDialog = function () {
    const modal = M.Modal.getInstance(document.getElementById('save-modal'));

    document.getElementById('save-form').reset();
    document.getElementById('modal-title').textContent = 'Crear servicio';
   document.getElementById('id_servicio').value = '';


    cargarCombos();
    modal.open();
};

// ===============================
// ACTUALIZAR
// ===============================

window.openUpdateDialog = function (id) {
    const modal = M.Modal.getInstance(document.getElementById('save-modal'));

    const form = new FormData();
    form.append('id_servicio', id);

    fetch(API_SERVICIOS + 'readOne', {
        method: 'POST',
        body: form
    })
    .then(res => res.json())
    .then(json => {
        console.log(json); // Ver respuesta en consola
        if (!json.status) return Swal.fire("Error", json.exception, "error");

        const d = json.dataset;

        document.getElementById('modal-title').textContent = 'Actualizar servicio';
        document.getElementById('id_servicio').value = d.id;
        document.getElementById('desc').value = d.description;
        // Asignar valores de selects y reinicializar Materialize
        setTimeout(() => {
            document.getElementById('id_tipo_servicio').value = d.idservice_classification;

            // Reinicializar selects de Materialize para que tomen el valor
            const grupo = document.querySelectorAll('#save-modal select');
            M.FormSelect.init(grupo);
        }, 50);

        M.updateTextFields();
        modal.open();
    })
    .catch(() => Swal.fire("Error", "No se pudo leer el usuario", "error"));
};


// ===============================
// GUARDAR (CREATE / UPDATE)
// ===============================

document.getElementById('save-form').addEventListener('submit', e => {
    e.preventDefault();

    const form = new FormData(e.target);
    const isUpdate = form.get('id_servicio') !== '';
    const action = isUpdate ? 'update' : 'create';

    fetch(API_SERVICIOS + action, {
        method: 'POST',
        body: form
    })
    .then(res => res.json())
    .then(json => {
        console.log(json);//Para ver la respuesta del json en la consola
        if (!json.status) {
            let errorMessage = 
                json.exception ||
                json.error_db ||
                JSON.stringify(json.errors) ||
                json.message ||
                "Ocurrió un error desconocido";

            Swal.fire("Error", errorMessage, "error");
            return;
        }

        Swal.fire("Éxito", json.message, "success");
        M.Modal.getInstance(document.getElementById('save-modal')).close();
        cargarServicios();
    })
    .catch(() => Swal.fire("Error", "No se pudo guardar el usuario", "error"));
});


// ===============================
// ELIMINAR
// ===============================

window.openDeleteDialog = function (id) {

    Swal.fire({
        title: "¿Desea dar de baja el servicio?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Eliminar",
        cancelButtonText: "Cancelar"
    })
        .then(result => {
            if (!result.isConfirmed) return;

            const form = new FormData();
            form.append('id_servicio', id);

            fetch(API_SERVICIOS + 'deletelogic', {
                method: 'POST',
                body: form
            })
                .then(res => res.json())
                .then(json => {
                    console.log(json);
                    if (!json.status) return Swal.fire("Error", json.exception, "error");

                    Swal.fire("Elominado", "El servicio ha sido eliminado correctamente", "success");
                    cargarServicios();
                })
                .catch(() => Swal.fire("Error", "No se pudo eliminar", "error"));
        });
};

