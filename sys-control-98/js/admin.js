// CONTROL DEL MENÚ LATERAL RESPONSIVE (MÓVIL / TABLET)
function toggleSidebar(open) {
  const sidebar = document.getElementById('adminSidebar');
  const backdrop = document.getElementById('sidebarBackdrop');
  const toggleBtn = document.getElementById('adminMenuToggle');
  const shouldOpen = typeof open === 'boolean' ? open : !sidebar.classList.contains('open');

  if (sidebar) {
    sidebar.classList.toggle('open', shouldOpen);
  }
  if (backdrop) {
    backdrop.classList.toggle('active', shouldOpen);
  }
  if (toggleBtn) {
    toggleBtn.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
  }
  if (window.innerWidth <= 992) {
    document.body.style.overflow = shouldOpen ? 'hidden' : '';
  }
}

// MANEJO DE PESTAÑAS (TABS)
function switchTab(tabId) {
  const item = document.querySelector(`.sidebar-nav .nav-item[data-tab="${tabId}"]`);
  const target = document.getElementById(tabId);
  if (item && target) {
    document.querySelectorAll('.sidebar-nav .nav-item').forEach(i => i.classList.remove('active'));
    document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
    item.classList.add('active');
    target.classList.add('active');
    const titleText = item.textContent.trim();
    const sectionTitle = document.getElementById('section-title');
    if (sectionTitle) sectionTitle.textContent = titleText;
    const adminSecSelect = document.getElementById('adminSectionSelect');
    if (adminSecSelect) adminSecSelect.value = tabId;
    localStorage.setItem('admin_active_tab', tabId);

    // En móviles, cerrar el sidebar drawer al cambiar de sección
    if (window.innerWidth <= 992) {
      toggleSidebar(false);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }
}

document.querySelectorAll('.sidebar-nav .nav-item').forEach(item => {
  item.addEventListener('click', (e) => {
    e.preventDefault();
    const tabId = item.getAttribute('data-tab');
    switchTab(tabId);
  });
});

// Event listeners para drawer responsive
window.addEventListener('DOMContentLoaded', () => {
  const menuToggle = document.getElementById('adminMenuToggle');
  const closeBtn = document.getElementById('sidebarCloseBtn');
  const backdrop = document.getElementById('sidebarBackdrop');

  if (menuToggle) {
    menuToggle.addEventListener('click', () => toggleSidebar(true));
  }
  if (closeBtn) {
    closeBtn.addEventListener('click', () => toggleSidebar(false));
  }
  if (backdrop) {
    backdrop.addEventListener('click', () => toggleSidebar(false));
  }

  // Cerrar sidebar con tecla Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') toggleSidebar(false);
  });

  const hash = window.location.hash.replace('#', '');
  const savedTab = hash || localStorage.getItem('admin_active_tab');
  if (savedTab && document.getElementById(savedTab)) {
    switchTab(savedTab);
  }
});

// Previsualización instantánea de imágenes al seleccionar archivo
document.addEventListener('change', (e) => {
  if (e.target.matches('.img-upload-info input[type="file"]')) {
    const file = e.target.files[0];
    if (file) {
      const box = e.target.closest('.img-upload-box');
      if (box) {
        const preview = box.querySelector('.img-upload-preview');
        const filenameLabel = box.querySelector('.upload-filename');
        if (preview) {
          preview.src = URL.createObjectURL(file);
        }
        if (filenameLabel) {
          filenameLabel.textContent = 'Seleccionado: ' + file.name;
        }
      }
    }
  }
});

// TOAST NOTIFICATIONS
function showToast(message, isError = false) {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.remove('error');
  if (isError) toast.classList.add('error');
  toast.classList.add('show');
  setTimeout(() => {
    toast.classList.remove('show');
  }, 3500);
}

// MODAL CONTROLS
function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.remove('active');
}

// HELPER DE COMUNICACIÓN CON LA API
function getApiEndpoint(action) {
  var base = (document.currentScript && document.currentScript.src)
    ? new URL('../api/manage.php', document.currentScript.src).href
    : (window.location.pathname.includes('/sys-control-98')
        ? window.location.pathname.substring(0, window.location.pathname.indexOf('/sys-control-98') + 15) + '/api/manage.php'
        : 'api/manage.php');
  return action ? base + (base.includes('?') ? '&' : '?') + 'action=' + encodeURIComponent(action) : base;
}

async function fetchApi(url, options) {
  try {
    const res = await fetch(url, options);
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch (e) {
      console.error("Respuesta no JSON del servidor:", text);
      return { success: false, error: 'Error del servidor (' + res.status + '): ' + text.substring(0, 100) };
    }
  } catch (err) {
    return { success: false, error: 'Error de red o conexión al servidor' };
  }
}

// GUARDAR CONTENIDO GENERAL (TEXTOS E IMÁGENES)
async function saveGeneralContent(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  // Reorganizar claves en formData para procesar textos e imágenes
  const content = {};
  const sendData = new FormData();

  formData.forEach((value, key) => {
    if (value instanceof File) {
      if (value.name) {
        sendData.append(key, value);
      }
    } else {
      content[key] = value;
    }
  });

  for (const [k, v] of Object.entries(content)) {
    sendData.append(`content[${k}]`, v);
  }

  const data = await fetchApi(getApiEndpoint('save_content'), {
    method: 'POST',
    body: sendData
  });

  if (data.success) {
    showToast('Guardado correctamente en la base de datos');
    setTimeout(() => location.reload(), 900);
  } else {
    showToast(data.error || 'Error al guardar', true);
  }
}

// ================= ESPECIALIDADES =================
function openEspecialidadModal() {
  document.getElementById('modal-esp-title').textContent = 'Nueva Especialidad';
  document.getElementById('esp_id').value = '';
  document.getElementById('esp_nombre').value = '';
  document.getElementById('esp_titulo').value = '';
  document.getElementById('esp_duracion').value = '7';
  document.getElementById('esp_icono').value = 'hammer';
  document.getElementById('esp_descripcion').value = '';
  document.getElementById('esp_imagen').value = '';
  const fileInput = document.getElementById('esp_file_input');
  if (fileInput) fileInput.value = '';
  const preview = document.getElementById('esp_img_preview');
  if (preview) preview.src = '../img/hero.jpg';
  const nameDisp = document.getElementById('esp_img_name_display');
  if (nameDisp) nameDisp.textContent = '';
  document.getElementById('modal-especialidad').classList.add('active');
}

function editEspecialidad(esp) {
  document.getElementById('modal-esp-title').textContent = 'Editar Especialidad';
  document.getElementById('esp_id').value = esp.id || '';
  document.getElementById('esp_nombre').value = esp.nombre || '';
  document.getElementById('esp_titulo').value = esp.titulo || '';
  document.getElementById('esp_duracion').value = esp.duracion_anios || 7;
  document.getElementById('esp_icono').value = esp.icono || 'hammer';
  document.getElementById('esp_descripcion').value = esp.descripcion || '';
  document.getElementById('esp_imagen').value = esp.imagen || '';
  const fileInput = document.getElementById('esp_file_input');
  if (fileInput) fileInput.value = '';

  const preview = document.getElementById('esp_img_preview');
  const nameDisp = document.getElementById('esp_img_name_display');
  if (esp.imagen) {
    if (preview) preview.src = (esp.imagen.startsWith('http') || esp.imagen.startsWith('/') ? '' : '../img/') + esp.imagen;
    if (nameDisp) nameDisp.textContent = 'Imagen actual: ' + esp.imagen;
  } else {
    if (preview) preview.src = '../img/hero.jpg';
    if (nameDisp) nameDisp.textContent = 'Sin imagen cargada';
  }
  document.getElementById('modal-especialidad').classList.add('active');
}

function previewEspecialidadImg(e) {
  const file = e.target.files && e.target.files[0];
  if (!file) return;
  const preview = document.getElementById('esp_img_preview');
  const nameDisp = document.getElementById('esp_img_name_display');
  if (nameDisp) nameDisp.textContent = 'Nueva imagen seleccionada: ' + file.name;
  const reader = new FileReader();
  reader.onload = function(evt) {
    if (preview) preview.src = evt.target.result;
  };
  reader.readAsDataURL(file);
}

async function submitEspecialidad(e) {
  e.preventDefault();
  const formData = new FormData(e.target);
  formData.append('action', 'save_especialidad');

  const data = await fetchApi(getApiEndpoint(), { method: 'POST', body: formData });
  if (data.success) {
    showToast('Especialidad guardada con éxito');
    setTimeout(() => location.reload(), 800);
  } else {
    showToast(data.error || 'Error al guardar', true);
  }
}

async function deleteEspecialidad(id) {
  if (!confirm('¿Seguro que deseas eliminar esta especialidad?')) return;
  const formData = new FormData();
  formData.append('action', 'delete_especialidad');
  formData.append('id', id);

  const data = await fetchApi(getApiEndpoint(), { method: 'POST', body: formData });
  if (data.success) {
    showToast('Especialidad eliminada');
    setTimeout(() => location.reload(), 800);
  } else {
    showToast(data.error || 'Error al eliminar', true);
  }
}

// ================= PROYECTOS =================
function openProyectoModal() {
  document.getElementById('modal-proy-title').textContent = 'Nuevo Proyecto ExpoTec';
  document.getElementById('proy_id').value = '';
  document.getElementById('proy_titulo').value = '';
  document.getElementById('proy_subtitulo').value = '';
  document.getElementById('proy_descripcion').value = '';
  document.getElementById('proy_anio').value = new Date().getFullYear();
  document.getElementById('proy_imagen').value = '';
  document.getElementById('modal-proyecto').classList.add('active');
}

function editProyecto(proy) {
  document.getElementById('modal-proy-title').textContent = 'Editar Proyecto ExpoTec';
  document.getElementById('proy_id').value = proy.id;
  document.getElementById('proy_titulo').value = proy.titulo;
  document.getElementById('proy_subtitulo').value = proy.subtitulo || '';
  document.getElementById('proy_descripcion').value = proy.descripcion;
  document.getElementById('proy_especialidad').value = proy.especialidad_id;
  document.getElementById('proy_anio').value = proy.anio;
  document.getElementById('proy_imagen').value = proy.imagen || '';
  document.getElementById('modal-proyecto').classList.add('active');
}

async function submitProyecto(e) {
  e.preventDefault();
  const formData = new FormData(e.target);
  formData.append('action', 'save_proyecto');

  const data = await fetchApi(getApiEndpoint(), { method: 'POST', body: formData });
  if (data.success) {
    showToast('Proyecto guardado con éxito');
    setTimeout(() => location.reload(), 800);
  } else {
    showToast(data.error || 'Error al guardar', true);
  }
}

async function deleteProyecto(id) {
  if (!confirm('¿Seguro que deseas eliminar este proyecto?')) return;
  const formData = new FormData();
  formData.append('action', 'delete_proyecto');
  formData.append('id', id);

  const data = await fetchApi(getApiEndpoint(), { method: 'POST', body: formData });
  if (data.success) {
    showToast('Proyecto eliminado');
    setTimeout(() => location.reload(), 800);
  } else {
    showToast(data.error || 'Error al eliminar', true);
  }
}

// ================= HITOS HISTÓRICOS (PROYECTOS EMBLEMÁTICOS) =================
function openHitoModal() {
  document.getElementById('modal-hito-title').textContent = 'Nuevo Hito / Proyecto Emblemático';
  document.getElementById('hito_id').value = '';
  document.getElementById('hito_anio').value = new Date().getFullYear();
  document.getElementById('hito_titulo').value = '';
  document.getElementById('hito_resumen').value = '';
  document.getElementById('hito_descripcion').value = '';
  document.getElementById('hito_especialidad').value = '';
  document.getElementById('hito_destino').value = '';
  document.getElementById('hito_premio').value = '';
  document.getElementById('hito_imagen').value = '';
  document.getElementById('hito_enlace').value = '';
  document.getElementById('hito_orden').value = '1';
  document.getElementById('modal-hito').classList.add('active');
}

function editHito(hito) {
  document.getElementById('modal-hito-title').textContent = 'Editar Hito / Proyecto Emblemático';
  document.getElementById('hito_id').value = hito.id;
  document.getElementById('hito_anio').value = hito.anio;
  document.getElementById('hito_titulo').value = hito.titulo;
  document.getElementById('hito_resumen').value = hito.resumen || '';
  document.getElementById('hito_descripcion').value = hito.descripcion;
  document.getElementById('hito_especialidad').value = hito.especialidad || '';
  document.getElementById('hito_destino').value = hito.destino || '';
  document.getElementById('hito_premio').value = hito.premio || '';
  document.getElementById('hito_imagen').value = hito.imagen || '';
  document.getElementById('hito_enlace').value = hito.enlace || '';
  document.getElementById('hito_orden').value = hito.orden;
  document.getElementById('modal-hito').classList.add('active');
}

async function submitHito(e) {
  e.preventDefault();
  const formData = new FormData(e.target);
  formData.append('action', 'save_hito');

  const data = await fetchApi(getApiEndpoint(), { method: 'POST', body: formData });
  if (data.success) {
    showToast('Hito guardado con éxito');
    setTimeout(() => location.reload(), 800);
  } else {
    showToast(data.error || 'Error al guardar', true);
  }
}

async function deleteHito(id) {
  if (!confirm('¿Seguro que deseas eliminar este hito?')) return;
  const formData = new FormData();
  formData.append('action', 'delete_hito');
  formData.append('id', id);

  const data = await fetchApi(getApiEndpoint(), { method: 'POST', body: formData });
  if (data.success) {
    showToast('Hito eliminado');
    setTimeout(() => location.reload(), 800);
  } else {
    showToast(data.error || 'Error al eliminar', true);
  }
}

// ================= SEGURIDAD =================
async function changePassword(e) {
  e.preventDefault();
  const pass = document.getElementById('new_admin_password').value;
  const confirm = document.getElementById('confirm_admin_password').value;

  if (pass !== confirm) {
    showToast('Las contraseñas no coinciden', true);
    return;
  }

  const formData = new FormData();
  formData.append('action', 'change_password');
  formData.append('new_password', pass);

  const data = await fetchApi(getApiEndpoint(), { method: 'POST', body: formData });
  if (data.success) {
    showToast(data.message || 'Contraseña actualizada');
    document.getElementById('new_admin_password').value = '';
    document.getElementById('confirm_admin_password').value = '';
  } else {
    showToast(data.error || 'Error al actualizar', true);
  }
}

// ================= MENSAJES DE CONTACTO =================
async function deleteMensajeContacto(id) {
  if (!confirm("¿Seguro que deseas eliminar este mensaje de la bandeja de respaldo?")) return;
  const formData = new FormData();
  formData.append('id', id);
  const data = await fetchApi(getApiEndpoint('delete_mensaje_contacto'), { method: 'POST', body: formData });
  if (data.success) {
    showToast("Mensaje eliminado");
    setTimeout(() => location.reload(), 600);
  } else {
    showToast(data.error || "Error al eliminar", true);
  }
}
