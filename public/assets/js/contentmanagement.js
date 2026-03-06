/* =========================
   GLOBAL / CONFIG
========================= */
const NEWS_API = "../handlers/news_events_api.php";
const PORTALS_API = "../handlers/portals_api.php";

let STUDENT_PORTALS_CACHE = [];
let SERVICE_PORTALS_CACHE = [];

// DB events cache (used by table + edit modal lookup)
let ALL_EVENTS_CACHE = [];

// Featured pick mode (shows ⭐ buttons in table)
let PICK_FEATURED_MODE = false;

// CMS cancel baselines (textareas)
let originalValues = {};

/* =========================
   INIT (RUN ON LOAD)
========================= */
document.addEventListener("DOMContentLoaded", () => {
  // CMS blocks (About tab etc.)
  loadCmsBlocksIntoPage();

  // DB events + featured
  loadAllEvents();
  loadFeaturedEvents();

  // Other sections (only call if the DOM exists in this page)
  loadPortals("student");
  loadPortals("service");
  loadPortals("research");
});

/* =========================
   LAYOUT / TABS
========================= */
function toggleSidebar() {
  document.getElementById("sidebar")?.classList.toggle("collapsed");
}

function openTab(tabName, el) {
  document.querySelectorAll(".tab-content").forEach(t => t.classList.remove("active"));
  document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));

  document.getElementById(tabName)?.classList.add("active");
  el?.classList.add("active");
}

/* =========================
   HELPERS
========================= */
function escapeHtml(str) {
  return String(str ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function normalizeTime(t) {
  // Accept "08:00" -> "08:00:00"
  if (!t) return "";
  const trimmed = t.trim();
  if (/^\d{2}:\d{2}$/.test(trimmed)) return `${trimmed}:00`;
  if (/^\d{2}:\d{2}:\d{2}$/.test(trimmed)) return trimmed;
  return "";
}

function showSuccess(msg = "Changes saved successfully!") {
  const el = document.getElementById("successMessage");
  if (typeof window.showToast === "function") {
    window.showToast(msg, "success", "Success");
  } else if (typeof window.cmsToast === "function") {
    window.cmsToast(msg, "success", "Success");
  }
  if (!el) return;

  const span = el.querySelector("span");
  if (span) span.textContent = msg;

  el.classList.add("show");
  setTimeout(() => el.classList.remove("show"), 2000);
}
function showToast(message, type = "error", title = "") {
  if (typeof window.cmsToast === "function") {
    window.cmsToast(message, type, title);
    return;
  }

  let wrap = document.getElementById("cmsJsToastWrap");
  if (!wrap) {
    wrap = document.createElement("div");
    wrap.id = "cmsJsToastWrap";
    wrap.style.position = "fixed";
    wrap.style.top = "88px";
    wrap.style.right = "20px";
    wrap.style.zIndex = "3500";
    wrap.style.display = "grid";
    wrap.style.gap = "10px";
    wrap.style.width = "min(380px, calc(100vw - 24px))";
    (document.body || document.documentElement).appendChild(wrap);
  }

  const palette = {
    success: { border: "#d3ead4", color: "#1f8f3a", label: "Success" },
    warning: { border: "#f3e1bf", color: "#a26e0c", label: "Warning" },
    info: { border: "#cfe0f6", color: "#2259ac", label: "Notice" },
    error: { border: "#f2d4d4", color: "#b12a2a", label: "Request Failed" }
  };

  const key = String(type || "error").toLowerCase();
  const tone = palette[key] || palette.error;

  const toast = document.createElement("div");
  toast.style.background = "#fff";
  toast.style.border = "1px solid " + tone.border;
  toast.style.borderRadius = "12px";
  toast.style.boxShadow = "0 12px 30px rgba(0,0,0,.18)";
  toast.style.padding = "12px";
  toast.style.opacity = "0";
  toast.style.transform = "translateY(-8px)";
  toast.style.transition = "transform .2s ease, opacity .2s ease";

  toast.innerHTML =
    '<div style="font-size:13px;font-weight:700;color:' + tone.color + ';">' +
    (title || tone.label) +
    '</div>' +
    '<div style="font-size:13px;color:#4e4e4e;line-height:1.35;margin-top:4px;">' +
    String(message || "") +
    '</div>';

  wrap.appendChild(toast);
  requestAnimationFrame(() => {
    toast.style.opacity = "1";
    toast.style.transform = "translateY(0)";
  });

  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transform = "translateY(-8px)";
    setTimeout(() => toast.remove(), 220);
  }, 3200);
}

async function askConfirm(message, title = "Confirm Action", confirmText = "Confirm", tone = "warning") {
  if (typeof window.confirmAction === "function") {
    return await window.confirmAction({ message, title, confirmText, tone });
  }
  return confirm(message);
}

/* =========================
   CMS TEXT BLOCK EDITING
========================= */
function toggleEdit(blockKey) {
  const textarea = document.getElementById(blockKey);
  if (!textarea) return;

  // store baseline once
  if (originalValues[blockKey] === undefined) {
    originalValues[blockKey] = textarea.value;
  }

  textarea.disabled = false;
  textarea.focus();

  const btnBox = document.getElementById(blockKey + "Buttons");
  if (btnBox) btnBox.style.display = "flex";
}

async function saveEdit(blockKey) {
  const textarea = document.getElementById(blockKey);
  if (!textarea) return;

  const form = new FormData();
  form.append("block_key", blockKey);
  form.append("content", textarea.value);

  let res, data;
  try {
    res = await fetch("../handlers/cms_update.php", { method: "POST", body: form });
    data = await res.json();
  } catch {
    showToast("Server error. Please check PHP logs.");
    return;
  }

  if (!data.ok) {
    showToast(data.error || "Failed to save.");
    return;
  }

  originalValues[blockKey] = textarea.value; // update cancel baseline
  textarea.disabled = true;

  const btnBox = document.getElementById(blockKey + "Buttons");
  if (btnBox) btnBox.style.display = "none";

  showSuccess("Changes saved successfully!");
}

function cancelEdit(blockKey) {
  const textarea = document.getElementById(blockKey);
  if (!textarea) return;

  if (originalValues[blockKey] !== undefined) {
    textarea.value = originalValues[blockKey];
  }

  textarea.disabled = true;

  const btnBox = document.getElementById(blockKey + "Buttons");
  if (btnBox) btnBox.style.display = "none";
}

/* =========================
   CMS IMAGE BLOCKS (ABOUT TAB ETC.)
========================= */
async function handleImageUpload(sectionId, input) {
  if (!input?.files?.[0]) return;

  const file = input.files[0];
  const form = new FormData();
  form.append("block_key", sectionId);
  form.append("image", file);

  let res, data;
  try {
    res = await fetch("../handlers/cms_upload_image.php", { method: "POST", body: form });
    data = await res.json();
  } catch {
    showToast("Upload failed: cannot reach server or invalid response.");
    return;
  }

  if (!data.ok) {
    showToast(data.error || "Upload failed.");
    return;
  }

  const preview = document.getElementById(sectionId + "ImagePreview");
  if (preview) preview.innerHTML = `<img src="${data.image_path}" alt="Preview">`;

  const deleteBtn = document.getElementById(sectionId + "DeleteBtn");
  if (deleteBtn) deleteBtn.style.display = "inline-flex";

  showSuccess("Image uploaded!");
}

async function deleteImage(sectionId) {
  if (!(await askConfirm("Are you sure you want to delete this image?", "Delete Image", "Delete", "danger"))) return;

  const form = new FormData();
  form.append("block_key", sectionId);

  let res, data;
  try {
    res = await fetch("../handlers/cms_delete_image.php", { method: "POST", body: form });
    data = await res.json();
  } catch {
    showToast("Delete failed: cannot reach server or invalid response.");
    return;
  }

  if (!data.ok) {
    showToast(data.error || "Delete failed.");
    return;
  }

  const preview = document.getElementById(sectionId + "ImagePreview");
  if (preview) {
    preview.innerHTML = `
      <div class="no-image-placeholder">
        <i class="fas fa-image"></i>
        <p>No image uploaded</p>
      </div>
    `;
  }

  const deleteBtn = document.getElementById(sectionId + "DeleteBtn");
  if (deleteBtn) deleteBtn.style.display = "none";

  const input = document.getElementById(sectionId + "ImageInput");
  if (input) input.value = "";

  showSuccess("Image deleted!");
}

/* =========================
   CMS LOAD BLOCKS (DB -> PAGE)
========================= */
async function loadCmsBlocksIntoPage() {
  let res, data;

  try {
    res = await fetch("../handlers/cms_get_blocks.php");
    data = await res.json();
  } catch {
    console.warn("Cannot load CMS blocks (network / invalid JSON).");
    return;
  }

  if (!data.ok || !data.items) {
    console.warn(data.error || "Failed to load CMS blocks");
    return;
  }

  const items = data.items;

  // About tab keys
  const ABOUT_KEYS = [
    "aboutPUP",
    "about_welcomeTaguig",
    "academicExcellence",
    "studentLife",
    "visionMission",
    "logoSymbols",
    "hymn",
    "maps",
    "campusOfficials",
    "strategicPlan",
    "universityCalendar"
  ];

  ABOUT_KEYS.forEach(key => {
    const item = items[key];
    if (!item) return;

    // text
    const textarea = document.getElementById(key);
    if (textarea && typeof item.content === "string") {
      textarea.value = item.content;
      originalValues[key] = item.content;
    }

    // image
    const preview = document.getElementById(key + "ImagePreview");
    const deleteBtn = document.getElementById(key + "DeleteBtn");

    if (preview) {
      const imgPath = (item.image_path || "").trim();
      if (imgPath) {
        preview.innerHTML = `<img src="${imgPath}" alt="Preview">`;
        if (deleteBtn) deleteBtn.style.display = "inline-flex";
      } else {
        preview.innerHTML = `
          <div class="no-image-placeholder">
            <i class="fas fa-image"></i>
            <p>No image uploaded</p>
          </div>
        `;
        if (deleteBtn) deleteBtn.style.display = "none";
      }
    }
  });
}

/* =========================
   EVENTS (DB-BACKED TABLE)
========================= */
async function loadAllEvents() {
  const tbody = document.getElementById("eventsTableBody");
  if (!tbody) return;

  tbody.innerHTML = `<tr><td colspan="5">Loading...</td></tr>`;

  let res, data;
  try {
    res = await fetch(`${NEWS_API}?action=list_events`, {
      method: "GET",
      cache: "no-store",
      credentials: "same-origin"
    });
    data = await res.json();
  } catch {
    tbody.innerHTML = `<tr><td colspan="5">Failed to load events (network).</td></tr>`;
    return;
  }

  if (!data.ok) {
    tbody.innerHTML = `<tr><td colspan="5">${escapeHtml(data.message || "Failed to load events.")}</td></tr>`;
    return;
  }

  ALL_EVENTS_CACHE = Array.isArray(data.items) ? data.items : [];
  renderEventsTable(ALL_EVENTS_CACHE);
}

function renderEventsTable(events) {
  const tbody = document.getElementById("eventsTableBody");
  if (!tbody) return;

  if (!events.length) {
    tbody.innerHTML = `<tr><td colspan="5">No events found.</td></tr>`;
    return;
  }

  tbody.innerHTML = events.map(ev => {
    const id = ev.event_id;

    // ONLY short desc in table; fallback to truncated full desc if short is empty
    const short = (ev.short_description || "").trim();
    const desc = short
      ? short
      : ((ev.full_description || "").trim().slice(0, 120) + ((ev.full_description || "").length > 120 ? "..." : ""));

    return `
      <tr>
        <td>${escapeHtml(ev.title || "")}</td>
        <td>${escapeHtml(ev.location || "")}</td>
        <td>${escapeHtml(ev.event_date || "")}</td>
        <td class="event-desc-cell">${escapeHtml(desc)}</td>
        <td class="event-actions">
          <button class="btn btn-edit btn-small" type="button" onclick="openEditEventModal('${id}')">
            <i class="fas fa-edit"></i><span>Edit</span>
          </button>

          <button class="btn btn-danger btn-extra-small" type="button" onclick="deleteEvent('${id}')">
            <i class="fas fa-trash"></i><span>Delete</span>
          </button>

          ${
            PICK_FEATURED_MODE
              ? `
            <button class="btn btn-feature btn-small" type="button" onclick="featureEvent('${id}')">
              ⭐ <span>Feature</span>
            </button>
          `
              : ""
          }
        </td>
      </tr>
    `;
  }).join("");
}

function searchEvents() {
  const q = (document.getElementById("eventsSearchInput")?.value || "").trim().toLowerCase();
  if (!q) return renderEventsTable(ALL_EVENTS_CACHE);

  const filtered = ALL_EVENTS_CACHE.filter(ev => {
    const hay = [
      ev.title,
      ev.location,
      ev.event_date,
      ev.short_description,
      ev.full_description,
      ev.category_name
    ]
      .join(" ")
      .toLowerCase();
    return hay.includes(q);
  });

  renderEventsTable(filtered);
}

/* =========================
   FEATURED EVENT (DB)
========================= */
function enablePickFeaturedMode() {
  PICK_FEATURED_MODE = true;
  showSuccess("Pick an event from the table and click ⭐ Feature.");

  const table = document.getElementById("eventsTable");
  if (table) {
    table.scrollIntoView({ behavior: "smooth", block: "start" });
    table.classList.add("pick-featured-highlight");
    setTimeout(() => table.classList.remove("pick-featured-highlight"), 2000);
  }

  renderEventsTable(ALL_EVENTS_CACHE);
}

async function featureEvent(eventId) {
  const form = new FormData();
  form.append("action", "set_featured");
  form.append("event_id", eventId);

  let res, data;
  try {
    res = await fetch(NEWS_API, { method: "POST", body: form });
    data = await res.json();
  } catch {
    showToast("Server error while featuring event.");
    return;
  }

  if (!data.ok) {
    showToast(data.message || "Failed to feature event.");
    return;
  }

  PICK_FEATURED_MODE = false; // turn off mode
  showSuccess("Featured event updated!");

  await loadFeaturedEvents();
  renderEventsTable(ALL_EVENTS_CACHE);
}

async function unsetFeatured(eventId) {
  const form = new FormData();
  form.append("action", "unset_featured");
  form.append("event_id", eventId);

  let res, data;
  try {
    res = await fetch(NEWS_API, { method: "POST", body: form });
    data = await res.json();
  } catch {
    showToast("Server error while removing featured.");
    return;
  }

  if (!data.ok) {
    showToast(data.message || "Failed to remove featured event.");
    return;
  }

  await loadFeaturedEvents();
}

async function loadFeaturedEvents() {
  const wrap = document.getElementById("featuredEventsList");
  if (!wrap) return;

  wrap.innerHTML = `<div style="color:#666;">Loading featured event...</div>`;

  let res, raw, data;

  try {
    res = await fetch(`${NEWS_API}?action=list_featured`, {
      method: "GET",
      cache: "no-store",
      credentials: "same-origin"
    });
    raw = await res.text();
  } catch (err) {
    console.error("loadFeaturedEvents fetch failed:", err);
    wrap.innerHTML = `<div style="color:#b00000;">Failed to load featured event (network).</div>`;
    return;
  }

  try {
    data = JSON.parse(raw);
  } catch (e) {
    console.error("Featured API returned non-JSON:", raw);
    wrap.innerHTML = `<div style="color:#b00000;">Failed to load featured event (non-JSON). Check console.</div>`;
    return;
  }

  if (!data.ok) {
    wrap.innerHTML = `<div style="color:#b00000;">${escapeHtml(
      data.message || "Failed to load featured event."
    )}</div>`;
    return;
  }

  const featured = data.items || [];
  if (featured.length === 0) {
    wrap.innerHTML = `<div style="color:#666;">No featured event yet.</div>`;
    return;
  }

  wrap.innerHTML = featured
    .map(item => {
      return `
        <div class="event-row" style="display:flex; gap:12px; align-items:flex-start; padding:12px; border:1px solid #eee; border-radius:10px; margin-bottom:10px;">
          <div style="flex:1;">
            <div style="font-weight:700; color:#800000;">${escapeHtml(item.title || "")}</div>
            <div style="font-size:13px; color:#555;">${escapeHtml(item.event_date || "")}</div>
            <div style="font-size:13px; color:#777;">📍 ${escapeHtml(item.location || "")}</div>
            <div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;">
              <button class="btn-success" type="button" onclick="openEditEventModal('${item.event_id}')">
                <i class="fas fa-pen"></i> Edit
              </button>
              <button class="btn-success" type="button" style="background:#777;" onclick="unsetFeatured('${item.event_id}')">
                <i class="fas fa-star-half-alt"></i> Remove Featured
              </button>
            </div>
          </div>
        </div>
      `;
    })
    .join("");
}

/* =========================
   EVENT MODALS (ADD / EDIT)
========================= */
function openAddEventModal() {
  document.getElementById("addEventTitle").value = "";
  document.getElementById("addEventLocation").value = "PUP Taguig";

  const today = new Date().toISOString().slice(0, 10);
  document.getElementById("addEventDate").value = today;

  document.getElementById("addEventStartTime").value = "";
  document.getElementById("addEventEndTime").value = "";
  document.getElementById("addEventCategory").value = "Events";
  document.getElementById("addEventShortDesc").value = "";
  document.getElementById("addEventFullDesc").value = "";
  document.getElementById("addEventImageFile").value = "";

  document.getElementById("addEventModal")?.classList.add("show");
}

function closeAddEventModal() {
  document.getElementById("addEventModal")?.classList.remove("show");
}

async function saveAddEvent() {
  const title = document.getElementById("addEventTitle").value.trim();
  const location = document.getElementById("addEventLocation").value.trim();
  const event_date = document.getElementById("addEventDate").value;
  const start_time = document.getElementById("addEventStartTime").value;
  const end_time = document.getElementById("addEventEndTime").value;
  const category = document.getElementById("addEventCategory").value;
  const short_description = document.getElementById("addEventShortDesc").value.trim();
  const full_description = document.getElementById("addEventFullDesc").value.trim();
  const imageFile = document.getElementById("addEventImageFile").files[0];

  if (!title) return showToast("Title is required.");
  if (!event_date) return showToast("Date is required.");
  if (!full_description) return showToast("Full description is required.");
  if (start_time && end_time && start_time > end_time) return showToast("End time must be later than start time.");

  const form = new FormData();
  form.append("action", "create_event");
  form.append("title", title);
  form.append("location", location);
  form.append("event_date", event_date);
  form.append("start_time", normalizeTime(start_time));
  form.append("end_time", normalizeTime(end_time));
  form.append("short_description", short_description);
  form.append("full_description", full_description);
  form.append("category_name", category);

  if (imageFile) form.append("image", imageFile);

  let res, data;
  try {
    res = await fetch(NEWS_API, { method: "POST", body: form });
    data = await res.json();
  } catch {
    showToast("Server error while creating event.");
    return;
  }

  if (!data.ok) {
    showToast(data.message || "Failed to create event.");
    return;
  }

  closeAddEventModal();
  showSuccess("Event added!");
  await loadAllEvents();
  await loadFeaturedEvents();
}

function openEditEventModal(eventId) {
  const ev = ALL_EVENTS_CACHE.find(x => Number(x.event_id) === Number(eventId));
  if (!ev) {
    showToast("Event not found. Try refreshing the News tab.");
    return;
  }

  document.getElementById("editEventId").value = ev.event_id;
  document.getElementById("editEventTitle").value = ev.title || "";
  document.getElementById("editEventLocation").value = ev.location || "";
  document.getElementById("editEventDate").value = ev.event_date || "";

  document.getElementById("editEventStartTime").value = ev.start_time ? ev.start_time.slice(0, 5) : "";
  document.getElementById("editEventEndTime").value = ev.end_time ? ev.end_time.slice(0, 5) : "";

  document.getElementById("editEventCategory").value = ev.category_name || "Events";
  document.getElementById("editEventShortDescription").value = ev.short_description || "";
  document.getElementById("editEventFullDescription").value = ev.full_description || "";

  // NOTE: file inputs cannot be prefilled. Keep empty.
  // Always clear file input (normal)
    document.getElementById("editEventImageFile").value = "";

    // Show current saved image preview
    const preview = document.getElementById("editEventImagePreview");
    const pathBox = document.getElementById("editEventImagePathText");

    const imgPath = (ev.image_path || "").trim();

    if (pathBox) pathBox.value = imgPath;

    if (preview) {
    if (imgPath) {
        preview.innerHTML = `<img src="${imgPath}" alt="Event image" style="max-width:100%; border-radius:10px;">`;
    } else {
        preview.innerHTML = `
        <div class="no-image-placeholder">
            <i class="fas fa-image"></i>
            <p>No image uploaded</p>
        </div>
        `;
    }
    }


  document.getElementById("editEventModal")?.classList.add("show");
}

function closeEditEventModal() {
  document.getElementById("editEventModal")?.classList.remove("show");
}

async function saveEventEdit() {
  const eventId = document.getElementById("editEventId").value;

  const title = document.getElementById("editEventTitle").value.trim();
  const location = document.getElementById("editEventLocation").value.trim();
  const event_date = document.getElementById("editEventDate").value.trim();

  const start_time = normalizeTime(document.getElementById("editEventStartTime").value.trim());
  const end_time = normalizeTime(document.getElementById("editEventEndTime").value.trim());

  const category = document.getElementById("editEventCategory").value.trim();
  const short_description = document.getElementById("editEventShortDescription").value.trim();
  const full_description = document.getElementById("editEventFullDescription").value.trim();

  const imageFile = document.getElementById("editEventImageFile").files[0];

  if (!title || !event_date || !full_description) {
    showToast("Please fill: Title, Date, Full Description.");
    return;
  }

  const form = new FormData();
  form.append("action", "update_event");
  form.append("event_id", eventId);
  form.append("title", title);
  form.append("location", location);
  form.append("event_date", event_date);
  form.append("start_time", start_time);
  form.append("end_time", end_time);
  form.append("short_description", short_description);
  form.append("full_description", full_description);
  form.append("category_name", category);

  if (imageFile) form.append("image", imageFile);

  let res, data;
  try {
    res = await fetch(NEWS_API, { method: "POST", body: form });
    data = await res.json();
  } catch {
    showToast("Server error while updating event.");
    return;
  }

  if (!data.ok) {
    showToast(data.message || "Failed to update event.");
    return;
  }

  closeEditEventModal();
  await loadAllEvents();
  await loadFeaturedEvents();
  showSuccess("Event updated!");
}

async function deleteEvent(eventId) {
  if (!(await askConfirm("Delete this event?", "Delete Event", "Delete", "danger"))) return;

  const form = new FormData();
  form.append("action", "delete_event");
  form.append("event_id", eventId);

  let res, data;
  try {
    res = await fetch(NEWS_API, { method: "POST", body: form });
    data = await res.json();
  } catch {
    showToast("Server error while deleting event.");
    return;
  }

  if (!data.ok) {
    showToast(data.message || "Failed to delete event.");
    return;
  }

  await loadAllEvents();
  await loadFeaturedEvents();
}

function addStudentPortal() {
    studentPortalCounter++;
    studentPortalsData.push({
        id: studentPortalCounter,
        title: 'New Portal',
        description: 'Enter portal description here...',
        imageSrc: ''
    });
    renderStudentPortals();
}

async function removeStudentPortal(id) {
    if (await askConfirm('Are you sure you want to remove this portal?', 'Remove Portal', 'Remove', 'danger')) {
        studentPortalsData = studentPortalsData.filter(p => p.id !== id);
        renderStudentPortals();
        showSuccess();
    }
}

function togglePortalEdit(id, type, field) {
    const fieldId = `${type}Portal${field.charAt(0).toUpperCase() + field.slice(1)}${id}`;
    const fieldElement = document.getElementById(fieldId);
    const buttons = document.getElementById(fieldId + 'Buttons');
    
    if (fieldElement.disabled) {
        originalValues[fieldId] = fieldElement.value;
        fieldElement.disabled = false;
        fieldElement.focus();
        buttons.style.display = 'flex';
    }
}

function savePortalField(id, type, field) {
    const fieldId = `${type}Portal${field.charAt(0).toUpperCase() + field.slice(1)}${id}`;
    const fieldElement = document.getElementById(fieldId);
    const buttons = document.getElementById(fieldId + 'Buttons');
    
    const portalData = type === 'student' ? studentPortalsData : servicePortalsData;
    const portal = portalData.find(p => p.id === id);
    
    if (portal) {
        portal[field] = fieldElement.value;
        fieldElement.disabled = true;
        buttons.style.display = 'none';
        delete originalValues[fieldId];
        showSuccess();
    }
}

function cancelPortalEdit(id, type, field) {
    const fieldId = `${type}Portal${field.charAt(0).toUpperCase() + field.slice(1)}${id}`;
    const fieldElement = document.getElementById(fieldId);
    const buttons = document.getElementById(fieldId + 'Buttons');
    
    if (originalValues[fieldId] !== undefined) {
        fieldElement.value = originalValues[fieldId];
    }
    
    fieldElement.disabled = true;
    buttons.style.display = 'none';
    delete originalValues[fieldId];
}

function handlePortalImageUpload(id, type, input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const portalData = type === 'student' ? studentPortalsData : servicePortalsData;
            const portal = portalData.find(p => p.id === id);
            
            if (portal) {
                portal.imageSrc = e.target.result;
                const preview = document.getElementById(`${type}Portal${id}ImagePreview`);
                preview.innerHTML = `<img src="${e.target.result}" alt="${portal.title}">`;
                
                const deleteBtn = document.getElementById(`${type}Portal${id}DeleteBtn`);
                if (deleteBtn) {
                    deleteBtn.style.display = 'inline-flex';
                }
                showSuccess();
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

async function deletePortalImage(id, type) {
    if (await askConfirm('Are you sure you want to delete this image?', 'Delete Image', 'Delete', 'danger')) {
        const portalData = type === 'student' ? studentPortalsData : servicePortalsData;
        const portal = portalData.find(p => p.id === id);
        
        if (portal) {
            portal.imageSrc = '';
            const preview = document.getElementById(`${type}Portal${id}ImagePreview`);
            preview.innerHTML = `
                <div class="no-image-placeholder">
                    <i class="fas fa-image"></i>
                    <p>No image uploaded</p>
                </div>
            `;
            
            const deleteBtn = document.getElementById(`${type}Portal${id}DeleteBtn`);
            if (deleteBtn) {
                deleteBtn.style.display = 'none';
            }
            
            const input = document.getElementById(`${type}Portal${id}ImageInput`);
            if (input) {
                input.value = '';
            }
            showSuccess();
        }
    }
}

// Service Portals Management
function renderServicePortals() {
    const container = document.getElementById('servicePortals');
    container.innerHTML = '';
    
    servicePortalsData.forEach(portal => {
        const portalHTML = `
            <div class="portal-item" id="servicePortal${portal.id}">
                <div class="portal-content">
                    <div class="portal-text-section">
                        <div class="form-group">
                            <div class="portal-field-header">
                                <label>Service Portal Title</label>
                                <button class="btn-edit-small" onclick="togglePortalEdit(${portal.id}, 'service', 'title')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                            <input type="text" id="servicePortalTitle${portal.id}" value="${portal.title}" disabled>
                            <div class="edit-buttons" id="servicePortalTitle${portal.id}Buttons" style="display: none;">
                                <button class="btn-success-small" onclick="savePortalField(${portal.id}, 'service', 'title')">
                                    <i class="fas fa-save"></i> Save
                                </button>
                                <button class="btn-cancel-small" onclick="cancelPortalEdit(${portal.id}, 'service', 'title')">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="portal-field-header">
                                <label>Service Portal Description</label>
                                <button class="btn-edit-small" onclick="togglePortalEdit(${portal.id}, 'service', 'description')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                            <textarea id="servicePortalDescription${portal.id}" disabled>${portal.description}</textarea>
                            <div class="edit-buttons" id="servicePortalDescription${portal.id}Buttons" style="display: none;">
                                <button class="btn-success-small" onclick="savePortalField(${portal.id}, 'service', 'description')">
                                    <i class="fas fa-save"></i> Save
                                </button>
                                <button class="btn-cancel-small" onclick="cancelPortalEdit(${portal.id}, 'service', 'description')">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="portal-image-section">
                        <label><i class="fas fa-image"></i> Portal Image</label>
                        <div class="image-preview-container" id="servicePortal${portal.id}ImagePreview">
                            ${portal.imageSrc ? `<img src="${portal.imageSrc}" alt="${portal.title}">` : `
                                <div class="no-image-placeholder">
                                    <i class="fas fa-image"></i>
                                    <p>No image uploaded</p>
                                </div>
                            `}
                        </div>
                        <div class="image-actions">
                            <input type="file" id="servicePortal${portal.id}ImageInput" accept="image/*" style="display: none;" onchange="handlePortalImageUpload(${portal.id}, 'service', this)">
                            <button class="btn-image-action" onclick="document.getElementById('servicePortal${portal.id}ImageInput').click()">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                            <button class="btn-image-action btn-danger" onclick="deletePortalImage(${portal.id}, 'service')" id="servicePortal${portal.id}DeleteBtn" style="${portal.imageSrc ? 'display: inline-flex;' : 'display: none;'}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
                <button class="btn-danger btn-remove-portal" onclick="removeServicePortal(${portal.id})">
                    <i class="fas fa-trash"></i> Remove Portal
                </button>
            </div>
        `;
        container.innerHTML += portalHTML;
    });
}

function addServicePortal() {
    servicePortalCounter++;
    servicePortalsData.push({
        id: servicePortalCounter,
        title: 'New Service Portal',
        description: 'Enter service portal description here...',
        imageSrc: ''
    });
    renderServicePortals();
}

async function removeServicePortal(id) {
    if (await askConfirm('Are you sure you want to remove this service portal?', 'Remove Service Portal', 'Remove', 'danger')) {
        servicePortalsData = servicePortalsData.filter(p => p.id !== id);
        renderServicePortals();
        showSuccess();
    }
}

function renderStudentPortals() {
    const container = document.getElementById('studentPortals');
    if (!container) return; // Safety check

    // Generate the HTML for all portals using backticks (``)
    const allPortalsHTML = studentPortalsData.map(portal => `
        <div class="portal-item" id="studentPortal${portal.id}">
            <div class="portal-content">
                <div class="portal-text-section">
                    <div class="form-group">
                        <div class="portal-field-header">
                            <label>Portal Title</label>
                            <button class="btn-edit-small" onclick="togglePortalEdit(${portal.id}, 'student', 'title')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                        <input type="text" id="studentPortalTitle${portal.id}" value="${portal.title}" disabled>
                        <div class="edit-buttons" id="studentPortalTitle${portal.id}Buttons" style="display: none;">
                            <button class="btn-success-small" onclick="savePortalField(${portal.id}, 'student', 'title')">
                                <i class="fas fa-save"></i> Save
                            </button>
                            <button class="btn-cancel-small" onclick="cancelPortalEdit(${portal.id}, 'student', 'title')">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="portal-field-header">
                            <label>Portal Description</label>
                            <button class="btn-edit-small" onclick="togglePortalEdit(${portal.id}, 'student', 'description')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                        <textarea id="studentPortalDescription${portal.id}" disabled>${portal.description}</textarea>
                        <div class="edit-buttons" id="studentPortalDescription${portal.id}Buttons" style="display: none;">
                            <button class="btn-success-small" onclick="savePortalField(${portal.id}, 'student', 'description')">
                                <i class="fas fa-save"></i> Save
                            </button>
                            <button class="btn-cancel-small" onclick="cancelPortalEdit(${portal.id}, 'student', 'description')">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>
                </div>

                <div class="portal-image-section">
                    <label><i class="fas fa-image"></i> Portal Image</label>
                    <div class="image-preview-container" id="studentPortal${portal.id}ImagePreview">
                        ${portal.imageSrc 
                            ? `<img src="${portal.imageSrc}" alt="${portal.title}">` 
                            : `<div class="no-image-placeholder"><i class="fas fa-image"></i> <p>No image uploaded</p></div>`
                        }
                    </div>
                    <div class="image-actions">
                        <input type="file" id="studentPortal${portal.id}ImageInput" accept="image/*" style="display: none;" onchange="handlePortalImageUpload(${portal.id}, 'student', this)">
                        <button class="btn-image-action" onclick="document.getElementById('studentPortal${portal.id}ImageInput').click()">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                        <button class="btn-image-action btn-danger" onclick="deletePortalImage(${portal.id}, 'student')" id="studentPortal${portal.id}DeleteBtn" style="${portal.imageSrc ? 'display: inline-flex;' : 'display: none;'}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
            <button class="btn-danger btn-remove-portal" onclick="removeStudentPortal(${portal.id})">
                <i class="fas fa-trash"></i> Remove Portal
            </button>
        </div>
    `).join(''); // Combine the array into one large string

    container.innerHTML = allPortalsHTML;
}

async function loadPortals(type) {
  const containerId = type === "student" ? "studentPortals" : "servicePortals";
  const container = document.getElementById(containerId);
  if (!container) return;

  container.innerHTML = `<div style="color:#666;">Loading portals...</div>`;

  let res, data;
  try {
    res = await fetch(`${PORTALS_API}?action=list&type=${encodeURIComponent(type)}`, { cache: "no-store" });
    data = await res.json();
  } catch {
    container.innerHTML = `<div style="color:#b00000;">Failed to load portals.</div>`;
    return;
  }

  if (!data.ok) {
    container.innerHTML = `<div style="color:#b00000;">${escapeHtml(data.message || "Failed.")}</div>`;
    return;
  }

  const items = Array.isArray(data.items) ? data.items : [];
  if (type === "student") STUDENT_PORTALS_CACHE = items;
  if (type === "service") SERVICE_PORTALS_CACHE = items;

  renderPortals(type, items);
}

function renderPortals(type, items) {
  const containerId =
    type === "student" ? "studentPortals" :
    type === "service" ? "servicePortals" :
    "researchPortals";

  const container = document.getElementById(containerId);
  if (!container) return;

  if (!items.length) {
    container.innerHTML = `<div style="color:#666;">No ${type} portals yet.</div>`;
    return;
  }

  container.innerHTML = items.map(p => {
    const id = Number(p.portal_id);
    const title = p.title || "";
    const desc = p.description || "";
    const img = (p.image_path || "").trim(); // stored like "uploads/portals/xxx.png"

    return `
      <div class="portal-item" id="${type}Portal${id}">
        <div class="portal-content">

          <div class="portal-text-section">

            <div class="form-group">
              <div class="portal-field-header">
                <label>${type === "student" ? "Portal Title" : "Service Portal Title"}</label>

                <button class="btn-edit-small" type="button" onclick="openEditPortalModal(${id}, '${type}')">
                    <i class="fas fa-edit"></i> Edit
                </button>

              </div>

              <input type="text" value="${escapeHtml(title)}" disabled>
            </div>

            <div class="form-group">
              <div class="portal-field-header">
                <label>${type === "student" ? "Portal Description" : "Service Portal Description"}</label>
              </div>

              <textarea disabled>${escapeHtml(desc)}</textarea>
            </div>

          </div>

          <div class="portal-image-section">
            <label><i class="fas fa-image"></i> Portal Image</label>

            <div class="image-preview-container" id="${type}Portal${id}ImagePreview">
              ${
                img
                  ? `<img src="../${escapeHtml(img)}" alt="${escapeHtml(title)}">`
                  : `
                    <div class="no-image-placeholder">
                      <i class="fas fa-image"></i>
                      <p>No image uploaded</p>
                    </div>
                  `
              }
            </div>

            <div class="image-actions">
              <input type="file"
                     id="${type}Portal${id}ImageInput"
                     accept="image/*"
                     style="display:none;"
                     onchange="uploadPortalImageDb(${id}, '${type}', this)">

              <button class="btn-image-action" type="button"
                      onclick="document.getElementById('${type}Portal${id}ImageInput').click()">
                <i class="fas fa-upload"></i> Upload
              </button>

              <button class="btn-image-action btn-danger" type="button"
                      onclick="deletePortalImageDb(${id}, '${type}')"
                      id="${type}Portal${id}DeleteBtn"
                      style="${img ? "display:inline-flex;" : "display:none;"}">
                <i class="fas fa-trash"></i> Delete
              </button>
            </div>

          </div>
        </div>

        <button class="btn-danger btn-remove-portal" type="button"
                onclick="deletePortalDb(${id}, '${type}')">
          <i class="fas fa-trash"></i> Remove Portal
        </button>
      </div>
    `;
  }).join("");
}

async function addPortalDb(type) {
  const title = prompt("Portal title:");
  if (!title) return;

  const description = prompt("Portal description:");
  if (!description) return;

  const form = new FormData();
  form.append("action", "create");
  form.append("type", type);
  form.append("title", title);
  form.append("description", description);

  const res = await fetch(PORTALS_API, { method: "POST", body: form });
  const data = await res.json();

  if (!data.ok) return showToast(data.message || "Failed to create portal.");

  showSuccess("Portal added!");
  loadPortals(type);
}

async function deletePortalDb(portalId, type) {
  if (!(await askConfirm("Remove this portal?", "Remove Portal", "Remove", "danger"))) return;

  const form = new FormData();
  form.append("action", "delete");
  form.append("type", type);
  form.append("portal_id", portalId);

  const res = await fetch(PORTALS_API, { method: "POST", body: form });
  const data = await res.json();

  if (!data.ok) return showToast(data.message || "Failed to remove portal.");

  showSuccess("Portal removed!");
  loadPortals(type);
}

async function uploadPortalImageDb(portalId, type, input) {
  if (!input?.files?.[0]) return;

  const form = new FormData();
  form.append("action", "update");
  form.append("type", type);
  form.append("portal_id", portalId);

  // keep old values if you don’t edit them yet:
  form.append("title", "");        // your API should handle empty by keeping existing OR you must send current
  form.append("description", "");  // same as above
  form.append("link_url", "");
  form.append("image", input.files[0]);

  const res = await fetch(PORTALS_API, { method: "POST", body: form });
  const data = await res.json();

  if (!data.ok) {
    showToast(data.message || "Failed to upload image.");
    return;
  }

  showSuccess("Portal image updated!");
  loadPortals(type);
}

async function deletePortalImageDb(portalId, type) {
  // easiest: implement an action in PHP like action=clear_image
  const form = new FormData();
  form.append("action", "clear_image");
  form.append("type", type);
  form.append("portal_id", portalId);

  const res = await fetch(PORTALS_API, { method: "POST", body: form });
  const data = await res.json();

  if (!data.ok) {
    showToast(data.message || "Failed to delete image.");
    return;
  }

  showSuccess("Portal image deleted!");
  loadPortals(type);
}

function openAddPortalModal(type) {
  document.getElementById("addPortalType").value = type;
  document.getElementById("addPortalTitle").value = "";
  document.getElementById("addPortalDescription").value = "";
  document.getElementById("addPortalUrl").value = "";
  document.getElementById("addPortalImage").value = "";

  document.getElementById("addPortalModal")?.classList.add("show");
}

function closeAddPortalModal() {
  document.getElementById("addPortalModal")?.classList.remove("show");
}

async function saveAddPortalDb() {
  const type = document.getElementById("addPortalType").value;
  const title = document.getElementById("addPortalTitle").value.trim();
  const description = document.getElementById("addPortalDescription").value.trim();
  const link_url = document.getElementById("addPortalUrl").value.trim();
  const imageFile = document.getElementById("addPortalImage").files[0];

  if (!title) return showToast("Title is required.");
  if (!description) return showToast("Description is required.");

  const form = new FormData();
  form.append("action", "create");
  form.append("type", type);
  form.append("title", title);
  form.append("description", description);
  form.append("link_url", link_url);

  if (imageFile) form.append("image", imageFile);

  let res, data;
  try {
    res = await fetch(PORTALS_API, { method: "POST", body: form });
    data = await res.json();
  } catch {
    showToast("Server error while creating portal.");
    return;
  }

  if (!data.ok) {
    showToast(data.message || "Failed to create portal.");
    return;
  }

  closeAddPortalModal();
  showSuccess("Portal added!");
  loadPortals(type);
}

async function openEditPortalModal(portalId, type) {
  // Load latest from DB (safer than cache)
  let res, data;
  try {
    res = await fetch(`${PORTALS_API}?action=get_one&type=${encodeURIComponent(type)}&portal_id=${encodeURIComponent(portalId)}`, { cache: "no-store" });
    data = await res.json();
  } catch {
    showToast("Failed to load portal details.");
    return;
  }

  if (!data.ok || !data.item) {
    showToast(data.message || "Portal not found.");
    return;
  }

  const p = data.item;

  document.getElementById("editPortalId").value = p.portal_id;
  document.getElementById("editPortalType").value = type;

  document.getElementById("editPortalTitle").value = p.title || "";
  document.getElementById("editPortalDescription").value = p.description || "";
  document.getElementById("editPortalUrl").value = p.link_url || "";

  // image preview
  const preview = document.getElementById("editPortalImagePreview");
  const delBtn = document.getElementById("editPortalDeleteImageBtn");
  const img = (p.image_path || "").trim();

  if (preview) {
    if (img) {
      preview.innerHTML = `<img src="../${escapeHtml(img)}" alt="Portal image" style="max-width:100%; border-radius:10px;">`;
      if (delBtn) delBtn.style.display = "inline-flex";
    } else {
      preview.innerHTML = `
        <div class="no-image-placeholder">
          <i class="fas fa-image"></i>
          <p>No image uploaded</p>
        </div>
      `;
      if (delBtn) delBtn.style.display = "none";
    }
  }

  // clear file input
  const file = document.getElementById("editPortalImageFile");
  if (file) file.value = "";

  document.getElementById("editPortalModal")?.classList.add("show");
}

function closeEditPortalModal() {
  document.getElementById("editPortalModal")?.classList.remove("show");
}

async function savePortalEditDb() {
  const portalId = document.getElementById("editPortalId").value;
  const type = document.getElementById("editPortalType").value;

  const title = document.getElementById("editPortalTitle").value.trim();
  const description = document.getElementById("editPortalDescription").value.trim();
  const link_url = document.getElementById("editPortalUrl").value.trim();
  const imageFile = document.getElementById("editPortalImageFile").files[0];

  if (!title) return showToast("Title is required.");
  if (!description) return showToast("Description is required.");

  const form = new FormData();
  form.append("action", "update");
  form.append("type", type);
  form.append("portal_id", portalId);
  form.append("title", title);
  form.append("description", description);
  form.append("link_url", link_url);

  if (imageFile) form.append("image", imageFile);

  let res, data;
  try {
    res = await fetch(PORTALS_API, { method: "POST", body: form });
    data = await res.json();
  } catch {
    showToast("Server error while saving portal.");
    return;
  }

  if (!data.ok) {
    showToast(data.message || "Failed to update portal.");
    return;
  }

  closeEditPortalModal();
  showSuccess("Portal updated!");
  loadPortals(type);
}

async function deletePortalImageDb(portalId, type) {
  if (!(await askConfirm("Delete this portal image?", "Delete Portal Image", "Delete", "danger"))) return;

  const form = new FormData();
  form.append("action", "clear_image");
  form.append("type", type);
  form.append("portal_id", portalId);

  let res, data;
  try {
    res = await fetch(PORTALS_API, { method: "POST", body: form });
    data = await res.json();
  } catch {
    showToast("Server error while deleting portal image.");
    return;
  }

  if (!data.ok) {
    showToast(data.message || "Failed to delete portal image.");
    return;
  }

  showSuccess("Portal image deleted!");
  loadPortals(type);
}

// helper for the modal button (uses hidden fields)
function deletePortalImageDbFromModal() {
  const portalId = document.getElementById("editPortalId").value;
  const type = document.getElementById("editPortalType").value;
  deletePortalImageDb(portalId, type);
}

/* =========================
   GLOBAL EXPORTS (INLINE onclick)
========================= */
// Layout
window.toggleSidebar = toggleSidebar;
window.openTab = openTab;

// CMS text blocks
window.toggleEdit = toggleEdit;
window.saveEdit = saveEdit;
window.cancelEdit = cancelEdit;
window.showSuccess = showSuccess;

// CMS image blocks
window.handleImageUpload = handleImageUpload;
window.deleteImage = deleteImage;

// Events (DB)
window.loadAllEvents = loadAllEvents;
window.renderEventsTable = renderEventsTable;
window.searchEvents = searchEvents;

window.openAddEventModal = openAddEventModal;
window.closeAddEventModal = closeAddEventModal;
window.saveAddEvent = saveAddEvent;

window.openEditEventModal = openEditEventModal;
window.closeEditEventModal = closeEditEventModal;
window.saveEventEdit = saveEventEdit;

window.deleteEvent = deleteEvent;

// Portals
// window.renderStudentPortals = renderStudentPortals;
// window.renderServicePortals = renderServicePortals;

// window.addStudentPortal = addStudentPortal;
// window.removeStudentPortal = removeStudentPortal;
// window.addServicePortal = addServicePortal;
// window.removeServicePortal = removeServicePortal;

// window.togglePortalEdit = togglePortalEdit;
// window.savePortalField = savePortalField;
// window.cancelPortalEdit = cancelPortalEdit;
// window.handlePortalImageUpload = handlePortalImageUpload;
// window.deletePortalImage = deletePortalImage;

window.addPortalDb = addPortalDb;
window.deletePortalDb = deletePortalDb;
window.uploadPortalImageDb = uploadPortalImageDb;
window.deletePortalImageDb = deletePortalImageDb;
window.openAddPortalModal = openAddPortalModal;
window.closeAddPortalModal = closeAddPortalModal;
window.saveAddPortalDb = saveAddPortalDb;
window.openEditPortalModal = openEditPortalModal;
window.closeEditPortalModal = closeEditPortalModal;
window.savePortalEditDb = savePortalEditDb;
window.deletePortalImageDb = deletePortalImageDb;
window.deletePortalImageDbFromModal = deletePortalImageDbFromModal;

// Featured (DB)
window.loadFeaturedEvents = loadFeaturedEvents;
window.enablePickFeaturedMode = enablePickFeaturedMode;
window.featureEvent = featureEvent;
window.unsetFeatured = unsetFeatured;
