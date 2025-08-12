<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema de Taller Mecánico</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>

  <link rel="stylesheet" href="{{ asset('frontend/styles.css') }}">
</head>
<body>
  <!-- Login Screen -->
  <div id="login-screen" class="screen active">
    <div class="login-container">
      <div class="login-card">
        <div class="login-logo">
          <i class="fas fa-car-mechanic"></i>
          <h1>Taller Mecánico</h1>
        </div>
        <form id="login-form">
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" class="form-control" placeholder="tu@correo.com" required>
          </div>
          <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" class="form-control" placeholder="••••••••" required>
            <i class="fas fa-eye password-toggle" id="password-toggle"></i>
          </div>
          <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Mechanic Dashboard Screen -->
  <div id="mechanic-dashboard" class="screen">
    <header class="header">
      <div class="container">
        <div class="header-content">
          <h1 class="header-title"><i class="fas fa-wrench"></i> Panel Mecánico</h1>
          <div>
            <button id="go-to-appointments" class="btn-logout" style="margin-right: 10px;">Citas</button>
            <button id="mechanic-logout" class="btn-logout">Cerrar Sesión</button>
          </div>
        </div>
      </div>
    </header>

    <div class="content">
      <div class="container">
        <div id="mechanic-notes-container" class="notes-grid">
          <!-- Notes will be loaded here -->
        </div>
        
        <!-- Empty state -->
        <div id="empty-notes" class="empty-state">
          <i class="fas fa-clipboard"></i>
          <h3>No hay notas disponibles</h3>
          <p>Comienza creando una nota con el botón de abajo</p>
        </div>
      </div>
    </div>

    <!-- FAB Button -->
    <button id="add-note-btn" class="fab-button">
      <i class="fas fa-plus"></i>
    </button>
  </div>

  <!-- Client Dashboard Screen -->
  <div id="client-dashboard" class="screen">
    <header class="header">
      <div class="container">
        <div class="header-content">
          <h1 class="header-title"><i class="fas fa-clipboard-list"></i> Mis Notas</h1>
          <div style="display: flex; align-items: center;">
            <div class="bell-container" id="bell-container">
              <i class="fas fa-bell" id="notif-bell"></i>
              <!-- Badge will be added dynamically when notifications exist -->
            </div>
            <button id="client-logout" class="btn-logout">Cerrar Sesión</button>
          </div>
        </div>
      </div>
    </header>

    <div class="content">
      <div class="container">
        <div id="client-notes-container" class="notes-grid">
          <!-- Client notes will be loaded here -->
        </div>
        
        <!-- Empty state -->
        <div id="empty-client-notes" class="empty-state">
          <i class="fas fa-clipboard"></i>
          <h3>No hay notas disponibles</h3>
          <p>No tienes notas asignadas en este momento</p>
        </div>
      </div>
    </div>

    <!-- Appointment notices section -->
    <div id="client-appointment-notices" class="appointment-notices" style="display:none;">
      <h3 style="margin-bottom: 1rem;"><i class="fas fa-calendar-check"></i> Próximas Citas</h3>
      <div id="appointment-notices-container">
        <!-- Appointment notices will be loaded here -->
      </div>
    </div>
  </div>

  <!-- Appointments Dashboard Screen -->
  <div id="appointments-dashboard" class="screen">
    <header class="header">
      <div class="container">
        <div class="header-content">
          <h1 class="header-title"><i class="fas fa-calendar"></i> Citas</h1>
          <div>
            <button id="back-to-mechanic" class="btn-logout" style="margin-right: 10px;">Volver</button>
            <button id="appointments-logout" class="btn-logout">Cerrar Sesión</button>
          </div>
        </div>
      </div>
    </header>

    <div class="content">
      <div class="container">
        <div class="calendar-container">
          <!-- Calendar controls -->
          <div class="calendar-controls">
            <div class="calendar-navigation">
              <button id="prev-btn" class="btn-calendar"><i class="fas fa-chevron-left"></i></button>
              <h3 id="calendar-title" class="calendar-title">Agosto 2023</h3>
              <button id="today-btn" class="btn-calendar">Hoy</button>
              <button id="next-btn" class="btn-calendar"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="calendar-view-toggle">
              <button id="month-view-btn" class="btn-calendar active">Vista Mensual</button>
              <button id="week-view-btn" class="btn-calendar">Vista Semanal</button>
            </div>
          </div>
          
          <!-- Calendar container -->
          <div id="calendar"></div>
          
          <!-- Custom calendar container -->
          <div id="custom-calendar" style="display: none;">
            <div class="custom-calendar">
              <div class="calendar-header" id="calendar-header">
                <!-- Days of week will be added here -->
              </div>
              <div class="calendar-grid" id="calendar-grid">
                <!-- Calendar days will be added here -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- FAB Button for appointments -->
    <button id="add-appointment-btn" class="fab-button">
      <i class="fas fa-plus"></i>
    </button>
  </div>

  <!-- Note Modal -->
  <div id="note-modal" class="modal-overlay">
    <div class="modal">
      <div class="modal-header">
        <h2 class="modal-title" id="modal-title">Nueva Nota</h2>
        <button class="modal-close" id="modal-close">&times;</button>
      </div>
      <div class="modal-body">
        <form id="note-form">
          <input type="hidden" id="note-id">
          <div class="form-group">
            <label for="note-title">Título</label>
            <input type="text" id="note-title" class="form-control" placeholder="Título" required>
          </div>

          <div class="form-group">
            <label for="note-client">Cliente</label>
            <select id="note-client" class="form-control" required>
              <option value="">Seleccionar cliente</option>
            </select>
          </div>

          <div class="form-group">
            <label for="note-vehicle">Vehículo</label>
            <select id="note-vehicle" class="form-control" required>
              <option value="">Seleccionar vehículo</option>
            </select>
          </div>

          <div class="form-group">
            <label for="note-mechanic">Mecánico</label>
            <select id="note-mechanic" class="form-control" required>
              <option value="">Seleccionar mecánico</option>
            </select>
          </div>

          <div class="form-group">
            <label for="note-description">Descripción</label>
            <textarea id="note-description" class="form-control" placeholder="Descripción del trabajo" rows="4" required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-cancel btn-modal" id="cancel-note">Cancelar</button>
        <button class="btn btn-primary btn-modal" id="save-note">Guardar</button>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="delete-modal" class="modal-overlay">
    <div class="modal">
      <div class="modal-header">
        <h2 class="modal-title">Confirmar Eliminación</h2>
        <button class="modal-close" id="delete-modal-close">&times;</button>
      </div>
      <div class="modal-body">
        <p>¿Estás seguro que deseas eliminar esta nota? Esta acción no se puede deshacer.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-cancel btn-modal" id="cancel-delete">Cancelar</button>
        <button class="btn btn-primary btn-modal" id="confirm-delete">Eliminar</button>
      </div>
    </div>
  </div>

  <!-- Appointment Modal -->
  <div id="appointment-modal" class="modal-overlay">
    <div class="modal">
      <div class="modal-header">
        <h2 class="modal-title">Agregar Cita</h2>
        <button class="modal-close" id="appointment-modal-close">&times;</button>
      </div>
      <div class="modal-body">
        <form id="appointment-form">
          <div class="form-group">
            <label for="appointment-title">Título</label>
            <input type="text" id="appointment-title" class="form-control" placeholder="Cambio de aceite" required>
          </div>

          <div class="form-group">
            <label for="appointment-client">Cliente</label>
            <select id="appointment-client" class="form-control" required>
              <option value="">Seleccionar cliente</option>
            </select>
          </div>

          <div class="form-group">
            <label for="appointment-vehicle">Vehículo</label>
            <select id="appointment-vehicle" class="form-control" required>
              <option value="">Seleccionar vehículo</option>
            </select>
          </div>

          <div class="form-group">
            <label for="appointment-date">Fecha y hora</label>
            <input type="datetime-local" id="appointment-date" class="form-control" required>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button class="btn btn-cancel btn-modal" id="cancel-appointment">Cancelar</button>
        <button class="btn btn-primary btn-modal" id="save-appointment">Guardar Cita</button>
      </div>
    </div>
  </div>

  <!-- Toast container -->
  <div class="toast-container" id="toast-container"></div>

  <!-- Client Notification Modal -->
  <div id="notif-modal" class="notif-modal-overlay">
    <div class="notif-modal">
      <div class="modal-header">
        <h2 class="modal-title">Mis Citas</h2>
        <button class="modal-close" id="notif-modal-close">&times;</button>
      </div>
      <div class="modal-body">
        <p>A continuación se muestran tus próximas citas programadas:</p>
        
        <div id="client-appointments-list" class="credentials-box">
          <!-- Client appointments will be listed here -->
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary btn-modal" id="close-notif-btn">Cerrar</button>
      </div>
    </div>
  </div>

  <script src="{{ asset('frontend/script.js') }}" defer></script>  
</body>
</html>