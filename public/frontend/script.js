// =============================
// Configuración base de la API
// =============================
const API_BASE = '/api'; // ajusta si tu app corre en subcarpeta o dominio distinto
let AUTH = {
  token: null,
  user: null, // { id, name, email, rol }
};

// =======================
// Referencias de la UI
// =======================
const loginScreen = document.getElementById('login-screen');
const mechanicDashboard = document.getElementById('mechanic-dashboard');
const clientDashboard = document.getElementById('client-dashboard');
const appointmentsDashboard = document.getElementById('appointments-dashboard');

const loginForm = document.getElementById('login-form');
const passwordToggle = document.getElementById('password-toggle');
const passwordInput = document.getElementById('password');
const emailInput = document.getElementById('email');

const mechanicLogout = document.getElementById('mechanic-logout');
const clientLogout = document.getElementById('client-logout');

const addNoteBtn = document.getElementById('add-note-btn');
const goToAppointmentsBtn = document.getElementById('go-to-appointments');

const noteModal = document.getElementById('note-modal');
const modalClose = document.getElementById('modal-close');
const cancelNoteBtn = document.getElementById('cancel-note');
const saveNoteBtn = document.getElementById('save-note');

const noteForm = document.getElementById('note-form');
const modalTitle = document.getElementById('modal-title');
const noteIdInput = document.getElementById('note-id');
const noteTitleInput = document.getElementById('note-title');
const noteClientSelect = document.getElementById('note-client');
const noteVehicleSelect = document.getElementById('note-vehicle');
const noteMechanicSelect = document.getElementById('note-mechanic');
const noteDescriptionInput = document.getElementById('note-description');

const mechanicNotesContainer = document.getElementById('mechanic-notes-container');
const clientNotesContainer = document.getElementById('client-notes-container');
const emptyNotes = document.getElementById('empty-notes');
const emptyClientNotes = document.getElementById('empty-client-notes');

const deleteModal = document.getElementById('delete-modal');
const deleteModalClose = document.getElementById('delete-modal-close');
const cancelDeleteBtn = document.getElementById('cancel-delete');
const confirmDeleteBtn = document.getElementById('confirm-delete');

const appointmentModal = document.getElementById('appointment-modal');
const appointmentModalClose = document.getElementById('appointment-modal-close');
const cancelAppointmentBtn = document.getElementById('cancel-appointment');
const saveAppointmentBtn = document.getElementById('save-appointment');

const appointmentForm = document.getElementById('appointment-form');
const appointmentTitleInput = document.getElementById('appointment-title');
const appointmentDateInput = document.getElementById('appointment-date');
const appointmentClientSelect = document.getElementById('appointment-client');
const appointmentVehicleSelect = document.getElementById('appointment-vehicle');

const clientAppointmentNotices = document.getElementById('client-appointment-notices'); // sección opcional cliente
const appointmentNoticesContainer = document.getElementById('appointment-notices-container');

const toastContainer = document.getElementById('toast-container');

// Calendario (si usas FullCalendar o el fallback)
let currentCalendarDate = new Date();
let calendarView = 'month';

// Estado interno
let currentNoteId = null;
let notePendingDeleteId = null;


// =======================
// Inicialización
// =======================
document.addEventListener('DOMContentLoaded', init);

function init() {
  setupEventListeners();
  restoreSession();
}

// =======================
// Listeners
// =======================
function setupEventListeners() {
  if (loginForm) loginForm.addEventListener('submit', handleLogin);
  if (passwordToggle) passwordToggle.addEventListener('click', togglePasswordVisibility);

  if (mechanicLogout) mechanicLogout.addEventListener('click', handleLogout);
  if (clientLogout) clientLogout.addEventListener('click', handleLogout);

  if (addNoteBtn) addNoteBtn.addEventListener('click', openAddNoteModal);
  if (modalClose) modalClose.addEventListener('click', closeNoteModal);
  if (cancelNoteBtn) cancelNoteBtn.addEventListener('click', closeNoteModal);
  if (saveNoteBtn) saveNoteBtn.addEventListener('click', saveNote);

  if (deleteModalClose) deleteModalClose.addEventListener('click', closeDeleteModal);
  if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', closeDeleteModal);
  if (confirmDeleteBtn) confirmDeleteBtn.addEventListener('click', confirmDeleteNote);

  if (goToAppointmentsBtn) goToAppointmentsBtn.addEventListener('click', showAppointmentsDashboard);

  const backToMechanicBtn = document.getElementById('back-to-mechanic');
  if (backToMechanicBtn) backToMechanicBtn.addEventListener('click', showMechanicDashboard);

  const appointmentsLogout = document.getElementById('appointments-logout');
  if (appointmentsLogout) appointmentsLogout.addEventListener('click', handleLogout);

  const addAppointmentBtn = document.getElementById('add-appointment-btn');
  if (addAppointmentBtn) addAppointmentBtn.addEventListener('click', openAddAppointmentModal);

  if (appointmentModalClose) appointmentModalClose.addEventListener('click', closeAppointmentModal);
  if (cancelAppointmentBtn) cancelAppointmentBtn.addEventListener('click', closeAppointmentModal);
  if (saveAppointmentBtn) saveAppointmentBtn.addEventListener('click', saveAppointment);

  // campanita cliente: opcional (se mantiene pero datos vendrán de API si existiera endpoint)
  const bellContainer = document.getElementById('bell-container');
  if (bellContainer) {
    bellContainer.addEventListener('click', openClientNotifModal);
  }
  const notifModalClose = document.getElementById('notif-modal-close');
  const closeNotifBtn = document.getElementById('close-notif-btn');
  if (notifModalClose) notifModalClose.addEventListener('click', closeClientNotifModal);
  if (closeNotifBtn) closeNotifBtn.addEventListener('click', closeClientNotifModal);
}

// =======================
// Sesión / Auth
// =======================
async function handleLogin(e) {
  e.preventDefault();
  const email = (emailInput?.value || '').trim();
  const password = (passwordInput?.value || '').trim();

  try {
    const res = await fetch(`${API_BASE}/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });

    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.message || 'Credenciales incorrectas');
    }

    const data = await res.json();
    AUTH.token = data.token;
    AUTH.user = data.user;

    AUTH.user.rol = AUTH.user.rol || AUTH.user.role;

    sessionStorage.setItem('auth', JSON.stringify(AUTH));
    showToast('Inicio de sesión exitoso', 'success');

    routeByRole();
    loginForm.reset();
  } catch (err) {
    showToast(err.message || 'Error al iniciar sesión', 'error');
  }
}

function restoreSession() {
  const stored = sessionStorage.getItem('auth');
  if (stored) {
    AUTH = JSON.parse(stored);
    routeByRole();
  } else {
    showLogin();
  }
}

async function handleLogout() {
  try {
    if (AUTH?.token) {
      await apiFetch('/logout', { method: 'POST' }).catch(() => {});
    }
  } finally {
    AUTH = { token: null, user: null };
    sessionStorage.removeItem('auth');
    showLogin();
    showToast('Sesión cerrada correctamente', 'success');
  }
}

function routeByRole() {
  const rol = AUTH?.user?.rol;
  if (rol === 'admin') {
    showMechanicDashboard(); // “Panel Mecánico” lo dejamos como panel admin/operador
  } else if (rol === 'client') {
    showClientDashboard();
  } else if (rol === 'mechanic') {
    // IMPORTANTE: según tu api.php, los CRUD de logs/appointments requieren rol:admin.
    // Así que solo mostraremos vistas de lectura permitidas para mechanic.
    showMechanicDashboard(true); // modo lectura/restringido
  } else {
    showLogin();
  }
}

function showLogin() {
  hideAllScreens();
  loginScreen?.classList.add('active');
}

function togglePasswordVisibility() {
  if (!passwordInput) return;
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    passwordToggle?.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    passwordInput.type = 'password';
    passwordToggle?.classList.replace('fa-eye-slash', 'fa-eye');
  }
}

// =======================
// Navegación
// =======================
function hideAllScreens() {
  loginScreen?.classList.remove('active');
  mechanicDashboard?.classList.remove('active');
  clientDashboard?.classList.remove('active');
  appointmentsDashboard?.classList.remove('active');
}

async function showMechanicDashboard(readOnly = false) {
  hideAllScreens();
  mechanicDashboard?.classList.add('active');
  // Cargamos notas/logs
  await loadMechanicNotes(readOnly);
}

async function showClientDashboard() {
  hideAllScreens();
  clientDashboard?.classList.add('active');
  await loadClientNotes();
  // Opcional: clientAppointmentNotices -> no hay endpoint específico en tu backend para citas del cliente
  clientAppointmentNotices?.style && (clientAppointmentNotices.style.display = 'none');
}

function showAppointmentsDashboard() {
  hideAllScreens();
  appointmentsDashboard?.classList.add('active');
  initializeCalendar();
}

// =======================
// Fetch helper
// =======================
async function apiFetch(path, options = {}) {
  const headers = Object.assign(
    { 'Content-Type': 'application/json' },
    options.headers || {}
  );
  if (AUTH?.token) headers['Authorization'] = `Bearer ${AUTH.token}`;

  const res = await fetch(`${API_BASE}${path}`, { ...options, headers });
  if (!res.ok) {
    let msg = 'Error en la solicitud';
    try {
      const j = await res.json();
      if (j?.message) msg = j.message;
    } catch (_) {}
    throw new Error(msg);
  }
  // 204 No Content
  if (res.status === 204) return null;
  return res.json();
}

// =======================
// Logs (Notas)
// =======================
async function loadMechanicNotes(readOnly = false) {
  try {
    // Solo admin puede listar logs según api.php
    const rol = AUTH?.user?.rol;
    let logs = [];
    if (rol === 'admin') {
      logs = await apiFetch('/logs', { method: 'GET' });
    } else if (rol === 'mechanic') {
      // No hay endpoint de “mis logs” para mecánico; mostramos mensaje vacío
      logs = [];
    }

    mechanicNotesContainer.innerHTML = '';
    if (!logs || logs.length === 0) {
      emptyNotes.style.display = 'block';
      return;
    }
    emptyNotes.style.display = 'none';

    logs.forEach(log => {
      const card = createNoteCardFromAPI(log, /*isMechanicView*/ true, /*readOnly*/ readOnly || rol !== 'admin');
      mechanicNotesContainer.appendChild(card);
    });
  } catch (err) {
    mechanicNotesContainer.innerHTML = '';
    emptyNotes.style.display = 'block';
    showToast(err.message, 'error');
  }
}

async function loadClientNotes() {
  try {
    // /api/my-logs accesible a roles client y mechanic
    const logs = await apiFetch('/my-logs', { method: 'GET' });
    clientNotesContainer.innerHTML = '';

    if (!logs || logs.length === 0) {
      emptyClientNotes.style.display = 'block';
      return;
    }
    emptyClientNotes.style.display = 'none';

    logs.forEach(log => {
      const card = createNoteCardFromAPI(log, /*isMechanicView*/ false, /*readOnly*/ true);
      clientNotesContainer.appendChild(card);
    });
  } catch (err) {
    clientNotesContainer.innerHTML = '';
    emptyClientNotes.style.display = 'block';
    showToast(err.message, 'error');
  }
}

function createNoteCardFromAPI(log, isMechanicView, readOnly) {
  // log: { id, title, description, client, vehicle, mechanic }
  const card = document.createElement('div');
  card.className = 'note-card';

  const withRel = log; // viene con relaciones por with('client','vehicle','mechanic')
  const clientName = withRel?.client?.name || withRel?.client?.email || `#${withRel?.client_id}`;
  const vehicleLabel = withRel?.vehicle
    ? `${withRel.vehicle.brand} ${withRel.vehicle.model} (${withRel.vehicle.year}) - ${withRel.vehicle.plate}`
    : `Vehículo #${withRel?.vehicle_id}`;
  const mechanicName = withRel?.mechanic?.name || `#${withRel?.mechanic_id}`;

  card.innerHTML = `
    <h3 class="note-title">${escapeHTML(withRel.title)}</h3>
    <p class="note-detail"><i class="fas fa-car"></i> <span>Vehículo:</span> ${escapeHTML(vehicleLabel)}</p>
    <p class="note-detail"><i class="fas fa-user"></i> <span>Cliente:</span> ${escapeHTML(clientName)}</p>
    <p class="note-detail"><i class="fas fa-user-cog"></i> <span>Mecánico:</span> ${escapeHTML(mechanicName)}</p>
    <p class="note-detail"><i class="fas fa-align-left"></i> <span>Descripción:</span> ${escapeHTML(withRel.description || '')}</p>
  `;

  if (isMechanicView && !readOnly) {
    const actions = document.createElement('div');
    actions.className = 'note-actions';

    const editBtn = document.createElement('button');
    editBtn.className = 'btn-action btn-edit';
    editBtn.innerHTML = '<i class="fas fa-edit"></i>';
    editBtn.addEventListener('click', () => openEditNoteModal(withRel));

    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'btn-action btn-delete';
    deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
    deleteBtn.addEventListener('click', () => openDeleteModal(withRel.id));

    actions.appendChild(editBtn);
    actions.appendChild(deleteBtn);
    card.appendChild(actions);
  }

  return card;
}

function openAddNoteModal() {
  modalTitle.textContent = 'Nueva Nota';
  noteForm.reset();
  noteIdInput.value = '';
  currentNoteId = null;
  // cargar selects
  preloadNoteSelects().finally(() => noteModal.classList.add('active'));
}

function openEditNoteModal(log) {
  modalTitle.textContent = 'Editar Nota';
  noteForm.reset();
  noteIdInput.value = log.id;
  currentNoteId = log.id;
  noteTitleInput.value = log.title || '';
  noteDescriptionInput.value = log.description || '';

  // Pre-carga selects y selecciona valores actuales
  preloadNoteSelects().then(() => {
    if (log.client_id) noteClientSelect.value = String(log.client_id);
    if (log.vehicle_id) noteVehicleSelect.value = String(log.vehicle_id);
    if (log.mechanic_id) noteMechanicSelect.value = String(log.mechanic_id);
  });

  noteModal.classList.add('active');
}

function closeNoteModal() {
  noteModal.classList.remove('active');
}

async function preloadNoteSelects() {
  // Los endpoints de clients/vehicles/logs están bajo rol:admin
  const rol = AUTH?.user?.rol;
  if (rol !== 'admin') {
    // deshabilitar edición si no es admin
    noteClientSelect.innerHTML = `<option value="">No permitido</option>`;
    noteVehicleSelect.innerHTML = `<option value="">No permitido</option>`;
    noteMechanicSelect.innerHTML = `<option value="">No permitido</option>`;
    return;
  }

  const [clients, vehicles, mechanics] = await Promise.all([
    apiFetch('/clients', { method: 'GET' }),
    apiFetch('/vehicles', { method: 'GET' }),
    apiFetch('/mechanics', { method: 'GET' }),
  ]);

  fillSelect(noteClientSelect, clients.map(c => ({ value: c.id, label: `${c.name} (${c.email})` })));
  fillSelect(noteVehicleSelect, vehicles.map(v => ({
    value: v.id,
    label: `${v.brand} ${v.model} ${v.year} - ${v.plate} [Cliente #${v.client_id}]`
  })));
  fillSelect(noteMechanicSelect, mechanics.map(m => ({ value: m.id, label: m.name })));
}

function fillSelect(selectEl, items, firstLabel = 'Seleccionar') {
  selectEl.innerHTML = `<option value="">${firstLabel}</option>`;
  items.forEach(it => {
    const opt = document.createElement('option');
    opt.value = it.value;
    opt.textContent = it.label;
    selectEl.appendChild(opt);
  });
}

async function saveNote() {
  const rol = AUTH?.user?.rol;
  if (rol !== 'admin') {
    showToast('No tienes permisos para crear/editar notas', 'error');
    return;
  }

  const payload = {
    title: (noteTitleInput.value || '').trim(),
    vehicle_id: Number(noteVehicleSelect.value),
    client_id: Number(noteClientSelect.value),
    mechanic_id: Number(noteMechanicSelect.value),
    description: (noteDescriptionInput.value || '').trim(),
  };

  if (!payload.title || !payload.vehicle_id || !payload.client_id || !payload.mechanic_id || !payload.description) {
    showToast('Completa todos los campos', 'error');
    return;
  }

  try {
    if (currentNoteId) {
      const updated = await apiFetch(`/logs/${currentNoteId}`, {
        method: 'PUT',
        body: JSON.stringify(payload),
      });
      showToast('Nota actualizada', 'success');
    } else {
      const created = await apiFetch('/logs', {
        method: 'POST',
        body: JSON.stringify(payload),
      });
      showToast('Nota creada', 'success');
    }
    closeNoteModal();
    await loadMechanicNotes(false);
  } catch (err) {
    showToast(err.message, 'error');
  }
}

function openDeleteModal(id) {
  notePendingDeleteId = id;
  deleteModal.classList.add('active');
}

function closeDeleteModal() {
  notePendingDeleteId = null;
  deleteModal.classList.remove('active');
}

async function confirmDeleteNote() {
  const rol = AUTH?.user?.rol;
  if (rol !== 'admin') {
    showToast('No tienes permisos para eliminar notas', 'error');
    return;
  }
  if (!notePendingDeleteId) return;

  try {
    await apiFetch(`/logs/${notePendingDeleteId}`, { method: 'DELETE' });
    showToast('Nota eliminada', 'success');
    closeDeleteModal();
    await loadMechanicNotes(false);
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// =======================
// Citas (Appointments)
// =======================
function openAddAppointmentModal() {
  appointmentForm.reset();

  // min hoy (ISO local sin segundos)
  const now = new Date();
  const tzOffset = now.getTimezoneOffset();
  const local = new Date(now.getTime() - tzOffset * 60000).toISOString().slice(0, 16);
  appointmentDateInput.min = local;

  preloadAppointmentSelects().finally(() => {
    appointmentModal.classList.add('active');
  });
}

function closeAppointmentModal() {
  appointmentModal.classList.remove('active');
}

async function preloadAppointmentSelects() {
  const rol = AUTH?.user?.rol;
  if (rol !== 'admin') {
    fillSelect(appointmentClientSelect, [], 'No permitido');
    fillSelect(appointmentVehicleSelect, [], 'No permitido');
    return;
  }

  const [clients, vehicles] = await Promise.all([
    apiFetch('/clients', { method: 'GET' }),
    apiFetch('/vehicles', { method: 'GET' }),
  ]);

  fillSelect(appointmentClientSelect, clients.map(c => ({ value: c.id, label: `${c.name} (${c.email})` })));
  fillSelect(appointmentVehicleSelect, vehicles.map(v => ({
    value: v.id,
    label: `${v.brand} ${v.model} ${v.year} - ${v.plate} [Cliente #${v.client_id}]`
  })));
}

async function saveAppointment() {
  const rol = AUTH?.user?.rol;
  if (rol !== 'admin') {
    showToast('No tienes permisos para crear/editar citas', 'error');
    return;
  }

  const editingId = appointmentForm.dataset.editingId;
  const payload = {
    title: (appointmentTitleInput.value || '').trim(),
    client_id: Number(appointmentClientSelect.value),
    vehicle_id: Number(appointmentVehicleSelect.value),
    scheduled_at: appointmentDateInput.value, // datetime-local
  };

  if (!payload.title || !payload.client_id || !payload.vehicle_id || !payload.scheduled_at) {
    showToast('Completa todos los campos', 'error');
    return;
  }

  try {
    if (editingId) {
      await apiFetch(`/appointments/${editingId}`, {
        method: 'PUT',
        body: JSON.stringify(payload),
      });
      appointmentForm.dataset.editingId = '';
      showToast('Cita editada', 'success');
    } else {
      await apiFetch('/appointments', {
        method: 'POST',
        body: JSON.stringify(payload),
      });
      showToast('Cita creada', 'success');
    }
    closeAppointmentModal();
    initializeCalendar();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function editAppointment(appointmentId) {
  const rol = AUTH?.user?.rol;
  if (rol !== 'admin') return;

  try {
    const appt = await apiFetch(`/appointments/${appointmentId}`, { method: 'GET' });
    await preloadAppointmentSelects();
    appointmentTitleInput.value = appt.title || '';
    appointmentClientSelect.value = String(appt.client_id);
    appointmentVehicleSelect.value = String(appt.vehicle_id);
    // convertir a local yyyy-MM-ddTHH:mm
    const dt = new Date(appt.scheduled_at);
    const localISO = new Date(dt.getTime() - dt.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    appointmentDateInput.value = localISO;

    appointmentForm.dataset.editingId = appointmentId;
    appointmentModal.classList.add('active');
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function deleteAppointment(appointmentId) {
  const rol = AUTH?.user?.rol;
  if (rol !== 'admin') return;
  if (!confirm('¿Seguro que deseas eliminar esta cita?')) return;

  try {
    await apiFetch(`/appointments/${appointmentId}`, { method: 'DELETE' });
    showToast('Cita eliminada', 'success');
    initializeCalendar();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// =======================
// Calendario
// =======================
async function initializeCalendar() {
  const calendarEl = document.getElementById('calendar');

  // Carga eventos desde API
  const events = await loadAppointmentsForCalendar();

  // Si existe FullCalendar, úsalo
  try {
    if (typeof FullCalendar !== 'undefined') {
      if (calendarEl.classList.contains('fc')) {
        const inst = calendarEl._calendar;
        if (inst) inst.destroy();
      }
      const cal = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events,
        eventClick: (info) => {
          const id = info.event.id;
          const rol = AUTH?.user?.rol;
          if (rol === 'admin') {
            editAppointment(id);
          }
        }
      });
      cal.render();
      calendarEl._calendar = cal;

      // Oculta el calendario custom
      document.getElementById('custom-calendar').style.display = 'none';
      calendarEl.style.display = 'block';
      return;
    }
  } catch (_) {}

  // Fallback: calendario propio
  initializeCustomCalendar(events);
}

async function loadAppointmentsForCalendar() {
  const rol = AUTH?.user?.rol;
  if (rol !== 'admin') return []; // solo admin puede listar citas (según api.php)

  const appts = await apiFetch('/appointments', { method: 'GET' });
  return appts.map(a => {
    return {
      id: String(a.id),
      title: `${a.title} – Cliente #${a.client_id}`,
      start: a.scheduled_at
    };
  });
}

// ---------- Calendario custom (fallback) ----------
function initializeCustomCalendar(events = []) {
  document.getElementById('calendar').style.display = 'none';
  document.getElementById('custom-calendar').style.display = 'block';

  // Controles
  const prevBtn = document.getElementById('prev-btn');
  const nextBtn = document.getElementById('next-btn');
  const todayBtn = document.getElementById('today-btn');
  const monthViewBtn = document.getElementById('month-view-btn');
  const weekViewBtn = document.getElementById('week-view-btn');

  prevBtn.onclick = navigatePrevious;
  nextBtn.onclick = navigateNext;
  todayBtn.onclick = navigateToday;
  monthViewBtn.onclick = () => switchView('month');
  weekViewBtn.onclick = () => switchView('week');

  // Render
  updateCalendarView(events);
}

function updateCalendarView(events = []) {
  if (calendarView === 'month') {
    renderMonthView(events);
  } else {
    renderWeekView(events);
  }
  updateCalendarTitle();
}

function updateCalendarTitle() {
  const titleEl = document.getElementById('calendar-title');
  const options = { year: 'numeric', month: 'long' };
  if (calendarView === 'week') {
    const weekStart = getStartOfWeek(currentCalendarDate);
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekStart.getDate() + 6);

    const startMonth = weekStart.toLocaleDateString('es', { month: 'short' });
    const endMonth = weekEnd.toLocaleDateString('es', { month: 'short' });

    titleEl.textContent = startMonth === endMonth
      ? `${startMonth} ${weekStart.getDate()} - ${weekEnd.getDate()}, ${weekStart.getFullYear()}`
      : `${startMonth} ${weekStart.getDate()} - ${endMonth} ${weekEnd.getDate()}, ${weekStart.getFullYear()}`;
  } else {
    titleEl.textContent = currentCalendarDate.toLocaleDateString('es', options);
  }
}

function renderMonthView(events = []) {
  renderCalendarHeader();
  const firstDayOfMonth = new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth(), 1);
  const firstDayOfGrid = new Date(firstDayOfMonth);
  const dayOfWeek = firstDayOfMonth.getDay();
  firstDayOfGrid.setDate(firstDayOfMonth.getDate() - dayOfWeek);

  const grid = document.getElementById('calendar-grid');
  grid.innerHTML = '';
  grid.className = 'calendar-grid month-view';

  for (let i = 0; i < 42; i++) {
    const currentDate = new Date(firstDayOfGrid);
    currentDate.setDate(firstDayOfGrid.getDate() + i);

    const dayEl = document.createElement('div');
    dayEl.className = 'calendar-day';

    if (currentDate.getMonth() !== currentCalendarDate.getMonth()) {
      dayEl.classList.add('inactive');
    }

    const today = new Date();
    if (currentDate.toDateString() === today.toDateString()) {
      dayEl.classList.add('today');
    }

    const dayNumber = document.createElement('div');
    dayNumber.className = 'day-number';
    dayNumber.textContent = currentDate.getDate();
    dayEl.appendChild(dayNumber);

    const dayEvents = document.createElement('div');
    dayEvents.className = 'day-events';

    const dayISO = currentDate.toISOString().slice(0, 10);
    const todays = events.filter(ev => (ev.start || '').slice(0, 10) === dayISO);

    todays.forEach(ev => {
      const eventEl = document.createElement('div');
      eventEl.className = 'calendar-event';
      eventEl.textContent = ev.title;
      eventEl.title = ev.title;
      eventEl.dataset.id = ev.id;

      // Acciones compactas
      const actions = document.createElement('span');
      actions.className = 'calendar-actions';

      const editBtn = document.createElement('button');
      editBtn.className = 'calendar-action-btn edit-btn';
      editBtn.innerHTML = '<i class="fas fa-edit"></i>';
      editBtn.title = 'Editar cita';
      editBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        editAppointment(ev.id);
      });

      const deleteBtn = document.createElement('button');
      deleteBtn.className = 'calendar-action-btn delete-btn';
      deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
      deleteBtn.title = 'Eliminar cita';
      deleteBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        deleteAppointment(ev.id);
      });

      actions.appendChild(editBtn);
      actions.appendChild(deleteBtn);
      eventEl.appendChild(actions);

      dayEvents.appendChild(eventEl);
    });

    dayEl.appendChild(dayEvents);
    grid.appendChild(dayEl);
  }
}

function renderWeekView(events = []) {
  renderCalendarHeader();
  const firstDay = getStartOfWeek(currentCalendarDate);

  const grid = document.getElementById('calendar-grid');
  grid.innerHTML = '';
  grid.className = 'calendar-grid week-view';

  for (let i = 0; i < 7; i++) {
    const currentDate = new Date(firstDay);
    currentDate.setDate(firstDay.getDate() + i);

    const dayEl = document.createElement('div');
    dayEl.className = 'calendar-day';

    const today = new Date();
    if (currentDate.toDateString() === today.toDateString()) {
      dayEl.classList.add('today');
    }

    const dayNumber = document.createElement('div');
    dayNumber.className = 'day-number';
    dayNumber.textContent = currentDate.getDate();
    dayEl.appendChild(dayNumber);

    const dayEvents = document.createElement('div');
    dayEvents.className = 'day-events';

    const dayISO = currentDate.toISOString().slice(0, 10);
    const todays = events.filter(ev => (ev.start || '').slice(0, 10) === dayISO);

    todays.forEach(ev => {
      const eventEl = document.createElement('div');
      eventEl.className = 'calendar-event';
      eventEl.textContent = ev.title;
      eventEl.title = ev.title;
      eventEl.dataset.id = ev.id;

      const actions = document.createElement('span');
      actions.className = 'calendar-actions';

      const editBtn = document.createElement('button');
      editBtn.className = 'calendar-action-btn edit-btn';
      editBtn.innerHTML = '<i class="fas fa-edit"></i>';
      editBtn.title = 'Editar cita';
      editBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        editAppointment(ev.id);
      });

      const deleteBtn = document.createElement('button');
      deleteBtn.className = 'calendar-action-btn delete-btn';
      deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
      deleteBtn.title = 'Eliminar cita';
      deleteBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        deleteAppointment(ev.id);
      });

      actions.appendChild(editBtn);
      actions.appendChild(deleteBtn);
      eventEl.appendChild(actions);

      dayEvents.appendChild(eventEl);
    });

    dayEl.appendChild(dayEvents);
    grid.appendChild(dayEl);
  }
}

function renderCalendarHeader() {
  const headerEl = document.getElementById('calendar-header');
  headerEl.innerHTML = '';
  ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'].forEach(d => {
    const div = document.createElement('div');
    div.textContent = d;
    headerEl.appendChild(div);
  });
}

function getStartOfWeek(date) {
  const d = new Date(date);
  d.setDate(d.getDate() - d.getDay());
  return d;
}

function navigatePrevious() {
  if (calendarView === 'month') {
    currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
  } else {
    currentCalendarDate.setDate(currentCalendarDate.getDate() - 7);
  }
  initializeCalendar();
}

function navigateNext() {
  if (calendarView === 'month') {
    currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
  } else {
    currentCalendarDate.setDate(currentCalendarDate.getDate() + 7);
  }
  initializeCalendar();
}

function navigateToday() {
  currentCalendarDate = new Date();
  initializeCalendar();
}

function switchView(view) {
  if (view === calendarView) return;
  calendarView = view;
  document.getElementById('month-view-btn').classList.toggle('active', view === 'month');
  document.getElementById('week-view-btn').classList.toggle('active', view === 'week');
  initializeCalendar();
}

// =======================
// Notificaciones Cliente (UI)
// =======================
function openClientNotifModal() {
  // No hay endpoint de citas por cliente; mostramos modal vacío.
  const notifModal = document.getElementById('notif-modal');
  const list = document.getElementById('client-appointments-list');
  if (list) list.innerHTML = '<p>No hay información de citas disponible.</p>';
  notifModal?.classList.add('active');
}

function closeClientNotifModal() {
  document.getElementById('notif-modal')?.classList.remove('active');
}

// =======================
// Helpers
// =======================
function showToast(message, type) {
  if (!toastContainer) return;
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <span>${message}</span>
    <button class="toast-close">&times;</button>
  `;
  toastContainer.appendChild(toast);

  setTimeout(() => (toast.style.animation = 'slideIn 0.3s forwards'), 10);

  const closeBtn = toast.querySelector('.toast-close');
  closeBtn.addEventListener('click', () => {
    toast.style.animation = 'slideOut 0.3s forwards';
    setTimeout(() => toast.remove(), 300);
  });

  setTimeout(() => {
    if (toast.parentNode) {
      toast.style.animation = 'slideOut 0.3s forwards';
      setTimeout(() => toast.remove(), 300);
    }
  }, 5000);
}

function escapeHTML(str) {
  return String(str || '').replace(/[&<>"']/g, s => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[s]));
}
