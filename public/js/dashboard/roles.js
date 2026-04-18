document.addEventListener('DOMContentLoaded', () => {
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));
    M.Modal.init(document.querySelectorAll('.modal'));
    cargarRoles();
});

function cargarRoles() {
    fetch(API_ROLES + 'index')
        .then(res => res.json())
        .then(json => {
            console.log(json); // Ver respuesta en consola

            if (json.status) {
                // Verificar si dataset tiene datos
                if (json.dataset && json.dataset.length > 0) {
                    llenarTabla(json.dataset);
                } else {
                    Swal.fire("Sin datos", "No se encontraron roles.", "info");
                }
            } else {
                Swal.fire("Error", json.exception, "error");
            }
        })
        .catch(() => Swal.fire("Error", "No se pudo cargar la tabla", "error"));
}

function llenarTabla(dataset) {
    let content = '';

    dataset.forEach(row => {
             content += `
            <tr>
                <td>${row.name}</td>
                 <td>
                    <a class="btn blue tooltipped" data-tooltip="Visualizar permisos"
                        onclick="openUpdateDialog(${row.id}, 'users')">
                        <i class="material-icons">visibility</i>
                    </a>
                </td>
                 <td>
                    <a class="btn blue tooltipped" data-tooltip="Visualizar permisos"
                        onclick="openUpdateDialog(${row.id}, 'services')">
                        <i class="material-icons">visibility</i>
                    </a>
                </td>
                <td>
                    <a class="btn blue tooltipped" data-tooltip="Visualizar permisos"
                        onclick="openUpdateDialog(${row.id}, 'tickets')">
                        <i class="material-icons">visibility</i>
                    </a>
                </td>
            </tr>
        `;
    });

    document.getElementById('tbody-rows').innerHTML = content;
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));
}

window.openUpdateDialog = function (id, moduleName) {
    const modal = M.Modal.getInstance(document.getElementById('save-modal'));
    document.getElementById('modulo').value = moduleName;
    document.getElementById('id_rol').value = id;
    const form = new FormData();
    form.append('role_id', id);
    form.append('module', moduleName); 

    fetch(API_PERMI + 'readByRoleAndModule', {
        method: 'POST',
        body: form
    })
    .then(res => res.json())
    .then(json => {
        console.log(json);

        if (!json.status) {
            return Swal.fire("Error", json.exception, "error");
        }

        const permisos = json.dataset;

        // Reiniciar switches a FALSE antes de poner los valores
        document.getElementById('create').checked = false;
        document.getElementById('update').checked = false;
        document.getElementById('delete').checked = false;
        document.getElementById('show').checked   = false;

        // Mapeo entre permiso y switch
        const map = {
            "create": document.getElementById('create'),
            "update": document.getElementById('update'),
            "delete": document.getElementById('delete'),
            "view":   document.getElementById('show')
        };

        // Recorrer permisos que vienen del backend
        permisos.forEach(p => {
            // name: "roles.create" → extraer "create"
            const accion = p.name.split('.')[1];

            if (map[accion]) {
                map[accion].checked = (p.status === 't'); 
            }
        });

        // Mostrar/ocultar switches según el módulo
        if (moduleName === 'tickets') {
            document.getElementById('update1').style.display = "none";
            document.getElementById('delete1').style.display = "none";
        } else {
            document.getElementById('update1').style.display = "block";
            document.getElementById('delete1').style.display = "block";
        }
        // Abrir modal
        modal.open();
    })
    .catch(err => {
        console.error(err);
        Swal.fire("Error", "No se pudo cargar los permisos", "error");
    });
};


function savePermisos() {
    const form = new FormData();

    form.append('role_id', document.getElementById('id_rol').value);
    form.append('modulo', document.getElementById('modulo').value);

    form.append('create', document.getElementById('create').checked ? "1" : "0");
    form.append('update', document.getElementById('update').checked ? "1" : "0");
    form.append('delete', document.getElementById('delete').checked ? "1" : "0");
    form.append('view',   document.getElementById('show').checked ? "1" : "0");

    fetch(API_PERMI + 'updateByRoleAndModule', {
        method: 'POST',
        body: form
    })
    .then(res => res.json())
    .then(json => {
                if (json.status) {
                    Swal.fire({
                icon: "success",
                title: "Permisos actualizados",
                timer: 1200,
                showConfirmButton: false
            }).then(() => {
                location.reload();  
            });
             M.Modal.getInstance(document.getElementById('save-modal')).close();
            cargarRoles();
        } else {
            Swal.fire("Error", json.message, "error");
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire("Error", "No se pudo actualizar los permisos", "error");
    });
}


document.getElementById('save-form').addEventListener('submit', function(e) {
    e.preventDefault(); // evitar reload

    savePermisos();     // llamar a tu función
});



