

// Método manejador de eventos que se ejecuta cuando el documento ha cargado.
document.addEventListener('DOMContentLoaded', function () {
    let today = new Date();
    let hour = today.getHours();
    let greeting = '';

    if (hour < 12) {
        greeting = 'Buenos días';
    } else if (hour < 19) {
        greeting = 'Buenas tardes';
    } else {
        greeting = 'Buenas noches';
    }

    document.getElementById('greeting').textContent = greeting;
    loadDashboard();
});

// Dispatcher según rol
function loadDashboard() {
    if (ROLE_ID === 1) loadAdminDashboard();
    else if (ROLE_ID === 2) loadSupportDashboard();
    else loadUserDashboard();
}

/* ============================================================
   DASHBOARD PARA ADMINISTRADOR
   ============================================================ */
function loadAdminDashboard() {

    document.getElementById("dashboardCards").innerHTML = `
        <div class="col s12 m4">
            <div class="card blue lighten-1 white-text">
                <div class="card-content">
                    <span class="card-title">Tickets Totales</span>
                    <h4 id="totalTickets">...</h4>
                </div>
            </div>
        </div>

        <div class="col s12 m4">
            <div class="card green lighten-1 white-text">
                <div class="card-content">
                    <span class="card-title">Usuarios Registrados</span>
                    <h4 id="totalUsuarios">...</h4>
                </div>
            </div>
        </div>

        <div class="col s12 m4">
            <div class="card orange lighten-1 white-text">
                <div class="card-content">
                    <span class="card-title">Servicios</span>
                    <h4 id="totalServicios">...</h4>
                </div>
            </div>
        </div>
    `;

    document.getElementById("dashboardLists").innerHTML = `
        <h5>Últimos Tickets</h5>
        <ul class="collection" id="ultimosTickets"></ul>
    `;

    loadAdminData();
}

function loadAdminData() {

    // Tickets
   fetch(API_TICKETS + "index")
    .then(r => r.json())
    .then(json => {
        if (!json.status) return;

        // 🔥 Ordenar por fecha descendente
        const tickets = json.dataset.sort((a, b) => 
            new Date(b.created_at) - new Date(a.created_at)
        );

        document.getElementById("totalTickets").innerText = tickets.length;

        let html = "";
        tickets.slice(0, 5).forEach(t => {
            html += `
                <li class="collection-item">
                    <b>${t.ticket_number}</b> - ${t.title}
                    <span class="secondary-content">${t.status}</span>
                </li>
            `;
        });

        document.getElementById("ultimosTickets").innerHTML = html;
    });

    // Usuarios
    fetch(API_AUTH + "index")
        .then(r => r.json())
        .then(json => {
            if (json.status) {
                document.getElementById("totalUsuarios").innerText = json.dataset.length;
            }
        });

    // Servicios
    fetch(API_TICKETS + "getServices")
        .then(r => r.json())
        .then(json => {
            if (json.status) {
                document.getElementById("totalServicios").innerText = json.dataset.length;
            }
        });
}

/* ============================================================
   DASHBOARD PARA SOPORTE
   ============================================================ */
function loadSupportDashboard() {

    document.getElementById("dashboardCards").innerHTML = `
        <div class="col s12 m6">
            <div class="card blue lighten-1 white-text">
                <div class="card-content">
                    <span class="card-title">Tickets Asignados</span>
                    <h4 id="asignadosSoporte">...</h4>
                </div>
            </div>
        </div>

        <div class="col s12 m6">
            <div class="card red lighten-1 white-text">
                <div class="card-content">
                    <span class="card-title">Pendientes</span>
                    <h4 id="pendientesSoporte">...</h4>
                </div>
            </div>
        </div>
    `;

    document.getElementById("dashboardLists").innerHTML = `
        <h5>Mis Tickets</h5>
        <ul class="collection" id="ticketsSoporte"></ul>
    `;

    loadSupportData();
}

function loadSupportData() {
    fetch(API_TICKETS  + "supporTickets")
        .then(r => r.json())
        .then(json => {
            if (!json.status) return;

            const data = json.dataset;

            document.getElementById("asignadosSoporte").innerText = data.length;

            const pendientes = data.filter(t => t.status !== "closed").length;
            document.getElementById("pendientesSoporte").innerText = pendientes;

            let html = "";
            data.slice(0, 5).forEach(t => {
                html += `
                    <li class="collection-item">
                        <b>${t.ticket_number}</b> - ${t.title}
                        <span class="secondary-content">${t.priority}</span>
                    </li>
                `;
            });

            document.getElementById("ticketsSoporte").innerHTML = html;
        });
}

/* ============================================================
   DASHBOARD PARA USUARIO FINAL
   ============================================================ */
function loadUserDashboard() {

    document.getElementById("dashboardCards").innerHTML = `
        <div class="col s12 center-align">
            <a href="ticket" class="btn-large blue">
                Crear Ticket
            </a>
        </div>
    `;

    document.getElementById("dashboardLists").innerHTML = `
        <h5>Mis Tickets</h5>
        <ul class="collection" id="ticketsUsuario"></ul>
    `;

    loadUserData();
}

function loadUserData() {
    fetch(API_TICKETS  + "userTickets")
        .then(r => r.json())
        .then(json => {
            if (!json.status) return;

            let html = "";
            json.dataset.forEach(t => {
                html += `
                    <li class="collection-item">
                        <b>${t.ticket_number}</b> - ${t.title}
                        <span class="secondary-content">${t.status}</span>
                    </li>
                `;
            });

            document.getElementById("ticketsUsuario").innerHTML = html;
        });
}

function logOut() {
    Swal.fire({
        title: 'Advertencia',
        text: '¿Quiere cerrar la sesión?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then(result => {
        if (result.isConfirmed) {

            fetch(API_AUTH + 'logOut', { method: 'GET' })
                .then(r => r.json())
                .then(response => {

                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sesión cerrada',
                            text: response.message
                        }).then(() => {
                            window.location.href = BASE_URL;
                        });

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.exception
                        });
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire("Error", "No se pudo cerrar la sesión", "error");
                });

        } else {
            Swal.fire({
                icon: 'info',
                title: 'Sesión activa',
                text: 'Puede continuar con la sesión'
            });
        }
    });
}
