document.addEventListener('DOMContentLoaded', () => {
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));
    M.Modal.init(document.querySelectorAll('.modal'));
    cargarTickets();
    const select = document.getElementById('prioridad');
    M.FormSelect.init(select);
    const select1 = document.getElementById('estado');
    M.FormSelect.init(select1);
    const select2 = document.getElementById('id_tipo_ticket');
    M.FormSelect.init(select2);
    cargarCombos();
});


// ===============================
// Cargar combos (TIPO y ESTADO)
// ===============================

function cargarCombos() {
    // Para tipos de usuario
    cargarCombo('getServices', 'id_servicio', 'id', 'desc');

    // Para cargar los usuarios
    cargarCombo('getUsuarios', 'id_asignado', 'id', 'nombre');
}

function cargarCombo(endpoint, selectId, idField, textField) {
    fetch(API_TICKETS + endpoint)
        .then(res => res.json())
        .then(json => {
             console.log(json);
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

function cargarTickets() {
    if(ROLE_ID == 2){
        cargarInfo('supporTickets');
    }else if(ROLE_ID == 3){
        cargarInfo('userTickets');
    }else{
        cargarInfo('index');
    }
    
}

function cargarInfo(action){
    fetch(API_TICKETS + action)
        .then(res => res.json())
        .then(json => {
            console.log(json); // Ver respuesta en consola

            if (json.status) {
                // Verificar si dataset tiene datos
                if (json.dataset && json.dataset.length > 0) {
                    llenarTabla(json.dataset);
                } else {
                    Swal.fire("Sin datos", "No se encontraron tickets asignados/creados", "info");
                    //window.location.href = MAIN_URL;
                }
            } else {
                Swal.fire("Error", json.exception, "error");
            }
        })
        .catch(() => Swal.fire("Error", "No se pudo cargar la tabla", "error"));
}


let tabla = null;
function llenarTabla(dataset) {
    let content = '';
    dataset.forEach(row => {
        if(ROLE_ID == 1){
            //Caso 1, ticket asignado y estado diferente a cerrado
            if(row.assigned_to != null && row.status != "closed"){
                content += `
                    <tr>
                        <td>${row.ticket_number}</td>
                        <td>${row.title}</td>
                        <td>${row.description}</td>
                        <td>${row.ticket_type}</td>
                        <td>${row.status}</td>
                        <td>${row.priority}</td>
                        <td>${row.service_name}</td>
                        <td>${row.creado_por}</td>
                        <td>${row.asignado_a}</td>
                        <td>${row.cerrado_por}</td>
                    <td>
                            <a class="btn blue tooltipped" style="margin-top: 5px;" data-tooltip="Actualizar"
                                onclick="openUpdateDialog(${row.id})">
                                <i class="material-icons">mode_edit</i>
                            </a>
                            <a class="btn red tooltipped" style="margin-top: 5px;" data-tooltip="Cerrar ticket"
                                onclick="openDeleteDialog(${row.id})">
                                <i class="material-icons">exit_to_app</i>
                            </a>
                            <a class="btn blue tooltipped" style="margin-top: 5px;" data-tooltip="Ver notas"
                                onclick="openNoteDialog(${row.id})">
                                <i class="material-icons">article</i>
                            </a>
                        </td>

                    </tr>
                `; 
            
                //Ticket aún no asigando y aún no cerrado (Solo se podrá editar)
            }else if(row.assigned_to == null && row.status != "closed") {
                    content += `
                <tr>
                    <td>${row.ticket_number}</td>
                    <td>${row.title}</td>
                    <td>${row.description}</td>
                    <td>${row.ticket_type}</td>
                    <td>${row.status}</td>
                    <td>${row.priority}</td>
                    <td>${row.service_name}</td>
                    <td>${row.creado_por}</td>
                    <td>${row.asignado_a}</td>
                    <td>${row.cerrado_por}</td>
                <td>
                        <a class="btn blue tooltipped" style="margin-top: 5px;" data-tooltip="Actualizar"
                            onclick="openUpdateDialog(${row.id})">
                            <i class="material-icons">mode_edit</i>
                        </a>
                    </td>
                </tr>
            `;
            //Caso contrario, ticket cerrado, solo ver notas.
            }else{
                    content += `
                <tr>
                    <td>${row.ticket_number}</td>
                    <td>${row.title}</td>
                    <td>${row.description}</td>
                    <td>${row.ticket_type}</td>
                    <td>${row.status}</td>
                    <td>${row.priority}</td>
                    <td>${row.service_name}</td>
                    <td>${row.creado_por}</td>
                    <td>${row.asignado_a}</td>
                    <td>${row.cerrado_por}</td>
                    <td>
                        <a class="btn blue tooltipped" style="margin-top: 5px;" data-tooltip="Ver notas"
                            onclick="openNoteDialog(${row.id}, '${row.status}')">
                            <i class="material-icons">article</i>
                        </a>
                    </td>
                </tr>
            `;
            }
        }else if (ROLE_ID == 2){
            //Caso 1, ticket asignado y estado diferente a cerrado
            if(row.status != "closed"){
                content += `
                    <tr>
                        <td>${row.ticket_number}</td>
                        <td>${row.title}</td>
                        <td>${row.description}</td>
                        <td>${row.ticket_type}</td>
                        <td>${row.status}</td>
                        <td>${row.priority}</td>
                        <td>${row.service_name}</td>
                        <td>${row.creado_por}</td>
                        <td>${row.asignado_a}</td>
                        <td>${row.cerrado_por}</td>
                    <td>
                            <a class="btn blue tooltipped" style="margin-top: 5px;" data-tooltip="Actualizar"
                                onclick="openUpdateDialog(${row.id})">
                                <i class="material-icons">mode_edit</i>
                            </a>
                            <a class="btn blue tooltipped" style="margin-top: 5px;" data-tooltip="Ver notas"
                                onclick="openNoteDialog(${row.id})">
                                <i class="material-icons">article</i>
                            </a>
                        </td>

                    </tr>
                `; 
                //Ticket cerrados.
            }else if(row.status == "closed"){
                    content += `
                <tr>
                    <td>${row.ticket_number}</td>
                    <td>${row.title}</td>
                    <td>${row.description}</td>
                    <td>${row.ticket_type}</td>
                    <td>${row.status}</td>
                    <td>${row.priority}</td>
                    <td>${row.service_name}</td>
                    <td>${row.creado_por}</td>
                    <td>${row.asignado_a}</td>
                    <td>${row.cerrado_por}</td>
                    <td>
                        <a class="btn blue tooltipped" style="margin-top: 5px;" data-tooltip="Ver notas"
                             onclick="openNoteDialog(${row.id}, '${row.status}')">
                            <i class="material-icons">article</i>
                        </a>
                    </td>
                </tr>
            `;
            }
        }else{
            //Caso 1, ticket asignado y estado diferente a cerrado
            if(row.assigned_to != null && row.status != "closed"){
                content += `
                    <tr>
                        <td>${row.ticket_number}</td>
                        <td>${row.title}</td>
                        <td>${row.description}</td>
                        <td>${row.ticket_type}</td>
                        <td>${row.status}</td>
                        <td>${row.priority}</td>
                        <td>${row.service_name}</td>
                        <td>${row.creado_por}</td>
                        <td>${row.asignado_a}</td>
                        <td>${row.cerrado_por}</td>
                    <td>
                            <a class="btn blue tooltipped" style="margin-top: 5px;" data-tooltip="Actualizar"
                                onclick="openUpdateDialog(${row.id})">
                                <i class="material-icons">mode_edit</i>
                            </a>

                            <a class="btn blue tooltipped" style="margin-top: 5px;" data-tooltip="Ver notas"
                                onclick="openNoteDialog(${row.id})">
                                <i class="material-icons">article</i>
                            </a>
                            <a class="btn green tooltipped" style="margin-top: 5px;" data-tooltip="Solicitar cierre"
                                onclick="openRequestDialog(${row.id})">
                                <i class="material-icons">multiple_stop</i>
                            </a>
                        </td>

                    </tr>
                `; 
                //Ticket aún no asigando y aún no cerrado (Solo se podrá editar)
            }else if(row.assigned_to == null && row.status != "closed") {
                    content += `
                <tr>
                    <td>${row.ticket_number}</td>
                    <td>${row.title}</td>
                    <td>${row.description}</td>
                    <td>${row.ticket_type}</td>
                    <td>${row.status}</td>
                    <td>${row.priority}</td>
                    <td>${row.service_name}</td>
                    <td>${row.creado_por}</td>
                    <td>${row.asignado_a}</td>
                    <td>${row.cerrado_por}</td>
                    <td>
                        <a class="btn blue tooltipped" style="margin-top: 5px;" data-tooltip="Actualizar"
                            onclick="openUpdateDialog(${row.id})">
                            <i class="material-icons">mode_edit</i>
                        </a>
                    </td>
                </tr>
            `;
            //Caso contrario, ticket cerrado, solo ver notas.
            }else{
                    content += `
                <tr>
                    <td>${row.ticket_number}</td>
                    <td>${row.title}</td>
                    <td>${row.description}</td>
                    <td>${row.ticket_type}</td>
                    <td>${row.status}</td>
                    <td>${row.priority}</td>
                    <td>${row.service_name}</td>
                    <td>${row.creado_por}</td>
                    <td>${row.asignado_a}</td>
                    <td>${row.cerrado_por}</td>
                    <td>
                        <a class="btn blue tooltipped" style="margin-top: 5px;" data-tooltip="Ver notas"
                            onclick="openNoteDialog(${row.id}, '${row.status}')">
                            <i class="material-icons">article</i>
                        </a>
                    </td>
                </tr>
            `;
            }
        }
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
// Logica para mostrar los datos
// ===============================




// ===============================
// CREAR
// ===============================

window.openCreateDialog = function () {
    const modal = M.Modal.getInstance(document.getElementById('save-modal'));

    document.getElementById('save-form').reset();
    document.getElementById('modal-title').textContent = 'Crear ticket';
    document.getElementById('id_ticket').value = '';
    
    if(ROLE_ID == 1){
        document.getElementById('estado_container').style.display = "none";
        document.getElementById('check_container').style.display = "block";
        document.getElementById('usuarios_container').style.display = "none";
    }else if(ROLE_ID == 3){
        document.getElementById('estado_container').style.display = "none";
        document.getElementById('check_container').style.display = "none";
        document.getElementById('usuarios_container').style.display = "none";
    }
    cargarCombos();
    modal.open();
};


window.openNoteDialog = function (id, status = 'open') {

    const modal = M.Modal.getInstance(document.getElementById('view-modal'));
    document.getElementById('saveview-form').reset();
    if(ROLE_ID == 3){
        document.getElementById('createnote').style.display = "none";
    }else{
        document.getElementById('createnote').style.display = "block";
    }
    if(status == 'closed'){
        document.getElementById('createnote').style.display = "none";
    }
    cargarNotas(id);
    document.getElementById('id_ticketnota').value = id;
    modal.open();
};

function cargarNotas(id) {
    const form = new FormData();
    form.append('id_ticketnota', id);
    fetch(API_NOTAS + 'index', {
        method: 'POST',
        body: form
    })
    .then(res => res.json())
    .then(json => {
        console.log(json);

        if (json.status) {
            if (json.dataset && json.dataset.length > 0) {
                llenarTablanotas(json.dataset);
            } else {
                Swal.fire("Sin datos", "No se encontraron notas asignadas al ticket", "info");
            }
        } else {
            Swal.fire("Error", json.exception, "error");
        }
    })
    .catch(() => Swal.fire("Error", "No se pudo cargar la tabla", "error"));
}


function llenarTablanotas(dataset) {
    let content = '';

    dataset.forEach(row => {
            if(row.note_type == 'request'){
                content += `
                    <tr style="color: #FF8226;">
                        <td>${row.ticket_number}</td>
                        <td>${row.action}</td>
                        <td>${row.actor_name}</td>
                        <td>${row.created_at}</td>
                        <td>${row.note_type}</td>
                    </tr>
                `;
            }else if(row.note_type == 'approved'){
                content += `
                    <tr style="color: #2DCC2D;">
                        <td>${row.ticket_number}</td>
                        <td>${row.action}</td>
                        <td>${row.actor_name}</td>
                        <td>${row.created_at}</td>
                        <td>${row.note_type}</td>
                    </tr>
                `;
            }else{
                content += `
                    <tr>
                        <td>${row.ticket_number}</td>
                        <td>${row.action}</td>
                        <td>${row.actor_name}</td>
                        <td>${row.created_at}</td>
                        <td>${row.note_type}</td>
                    </tr>
                `;
            }
            
    });

    document.getElementById('tbody1-rows').innerHTML = content;
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));
}

// ===============================
// ACTUALIZAR
// ===============================

window.openUpdateDialog = function (id) {
    const modal = M.Modal.getInstance(document.getElementById('save-modal'));
    document.getElementById('miCheck').checked = false;
    const form = new FormData();
    form.append('id_ticket', id);

    fetch(API_TICKETS + 'readOne', {
        method: 'POST',
        body: form
    })
    .then(res => res.json())
    .then(json => {
        if (!json.status) return Swal.fire("Error", json.exception, "error");

        const d = json.dataset;

        document.getElementById('modal-title').textContent = 'Actualizar ticket';
        document.getElementById('id_ticket').value = d.id;
        document.getElementById('title').value = d.title;
        document.getElementById('desc').value = d.description;
        //Activar los campos de estado y usuario
        document.getElementById('estado_container').style.display = "block";
        document.getElementById('usuarios_container').style.display = "none";
        // Asignar valores de selects y reinicializar Materialize
        setTimeout(() => {
            document.getElementById('id_tipo_ticket').value = d.ticket_type;
            document.getElementById('prioridad').value = d.priority;
            document.getElementById('id_servicio').value = d.service_id;
            document.getElementById('estado').value = d.status;
            if(ROLE_ID == 1){
                 if (d.assigned_to === null) {
                document.getElementById('miCheck').checked = false
                document.getElementById('check_container').style.display = "block";
                document.getElementById('id_asignado').value = 2;
                } else {
                    document.getElementById('usuarios_container').style.display = "block";
                    document.getElementById('check_container').style.display = "none";
                    document.getElementById('id_asignado').value = d.assigned_to;
                }
            }else if(ROLE_ID ==2)
            {
                //Valida que a la hora de actualizar no pueda reasingar usuarios, y 
                // que no pueda modificar el titulo ni la desc
                
                document.getElementById('usuarios_container').style.display = "none";
                document.getElementById('check_container').style.display = "none";
                document.getElementById('id_asignado').value = d.assigned_to;
                document.getElementById('titlecontainer').style.display = "none";
                document.getElementById('desccontainer').style.display = "none";
            }else{
                //Valida que el usuario no pueda modificar el estado del ticket ni el usuario asignado
                document.getElementById('usuarios_container').style.display = "none";
                document.getElementById('check_container').style.display = "none";
                document.getElementById('estado_container').style.display = "none";
            }

            // Reinicializar selects de Materialize para que tomen el valor
            const grupo = document.querySelectorAll('#save-modal select');
            M.FormSelect.init(grupo);
        }, 50);

        M.updateTextFields();
        modal.open();
    })
    .catch(() => Swal.fire("Error", "No se pudo leer el TICKET", "error"));
};


document.getElementById('miCheck').addEventListener('change', function () {
    if (this.checked) {
        document.getElementById('usuarios_container').style.display = "block";
    } else {
         document.getElementById('usuarios_container').style.display = "none";
    }
});

// ===============================
// GUARDAR (CREATE / UPDATE)
// ===============================

document.getElementById('save-form').addEventListener('submit', e => {
    e.preventDefault();

    const form = new FormData(e.target);

    // --- CONTROL DE ASIGNADO ---
    const checkUsers = document.getElementById('usuarios_container');
    const asignadoSelect = document.getElementById('id_asignado');

    if (checkUsers.style.display === "block") {
         // Si está visible → enviar valor del select (aunque esté vacío)
        form.set('id_asignado', asignadoSelect.value);
    } else if (checkUsers.style.display === "none") {

        // Si está oculto → asignado debe ser NULL
        form.set('id_asignado', null);
    }

    // Saber si es update o create
    const isUpdate = form.get('id_ticket') !== '';
    const action = isUpdate ? 'update' : 'create';

    fetch(API_TICKETS + action, {
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

            Swal.fire("Error", errorMessage, "error");
            return;
        }

        Swal.fire("Éxito", json.message, "success");
        M.Modal.getInstance(document.getElementById('save-modal')).close();
        cargarTickets();
    })
    .catch(() => Swal.fire("Error", "No se pudo guardar el TICKET", "error"));
});


document.getElementById('saveview-form').addEventListener('submit', e => {
    e.preventDefault();

    const form = new FormData(e.target);
    fetch(API_NOTAS + 'create', {
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

            Swal.fire("Error", errorMessage, "error");
            return;
        }

        Swal.fire("Éxito", json.message, "success");
        M.Modal.getInstance(document.getElementById('save-modal')).close();
        let iddelticket = document.getElementById('id_ticketnota').value;
        document.getElementById("descnote").value = "";
        cargarTickets();
        cargarNotas(iddelticket);
        
    })
    .catch(() => Swal.fire("Error", "No se pudo guardar la nota", "error"));
});




// ===============================
// ELIMINAR
// ===============================

window.openDeleteDialog = function (id) {

    Swal.fire({
        title: "¿Desea cerrar el ticket?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Eliminar",
        cancelButtonText: "Cancelar"
    })
        .then(result => {
            if (!result.isConfirmed) return;

            const form = new FormData();
            form.append('id_ticket', id);

            fetch(API_TICKETS + 'deletelogic', {
                method: 'POST',
                body: form
            })
                .then(res => res.json())
                .then(json => {
                    console.log(json);
                    if (!json.status) return Swal.fire("Error", json.exception, "error");

                    Swal.fire("Cerrado", "Ticket cerrado correctamente", "success");
                    cargarTickets();
                })
                .catch(() => Swal.fire("Error", "No se pudo eliminar", "error"));
        });
};

window.openRequestDialog = function (id) {

    Swal.fire({
        title: "¿Desea solicitar cierre del ticket?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Solicitar",
        cancelButtonText: "Cancelar"
    })
        .then(result => {
            if (!result.isConfirmed) return;

            const form = new FormData();
            form.append('id_ticketnota', id);

            fetch(API_NOTAS + 'createRequest', {
                method: 'POST',
                body: form
            })
                .then(res => res.json())
                .then(json => {
                    console.log(json);
                    if (!json.status) return Swal.fire("Error", json.exception, "error");

                    Swal.fire("Solicitado", "Ticket se ha solicitado para su cierre", "success");
                    cargarTickets();
                })
                .catch(() => Swal.fire("Error", "No se pudo solicitar", "error"));
        });
};


