/* ============================================================
   سرد — notes.js (Backend Connected - LIVE VERSION WITH LOGIN)
   ============================================================ */
(function () {
  "use strict";

  function $(sel, ctx) { return (ctx || document).querySelector(sel); }

  // ============================================================
  // STATE
  // ============================================================
  let notes = [];
  let editingNoteId = null;

  // ============================================================
  // DOM REFS
  // ============================================================
  const notesPanel = document.getElementById('notesPanel');
  const notesBadge = $("#notesBadge");
  const addNoteBtn = $("#addNoteBtn");
  const noteSearchInput = $("#noteSearchInput");
  const noteListEl = $("#noteList");
  const notesEmptyState = $("#notesEmptyState");

  const modalOverlay = $("#noteModalOverlay");
  const modalBox = $(".note-modal", modalOverlay);
  const modalTitle = $("#noteModalTitle");
  const modalTextarea = $("#noteModalTextarea");
  const modalCancelBtn = $("#noteModalCancelBtn");
  const modalSaveBtn = $("#noteModalSaveBtn");
  const modalCloseBtn = $("#noteModalCloseBtn");
  const modalCounter = $("#noteModalCounter");
  const modalError = $("#noteModalError");
  const toastContainer = $("#toastContainer");

  const NOTE_MAX_LENGTH = 500;

  // ============================================================
  // HELPER: Check Login Status (Restored)
  // ============================================================
  const isLoggedIn = notesPanel ? notesPanel.dataset.loggedIn === '1' : false;

  // ============================================================
  // HELPERS
  // ============================================================
  function getCurrentBookPage() {
    var toolbarEl = document.getElementById("readingToolbar");
    if (toolbarEl) {
      var start = parseInt(toolbarEl.getAttribute("data-start-page"), 10);
      if (!isNaN(start)) return start;
    }
    return null;
  }

  function escapeHtml(str) {
    var div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
  }

  function formatDate(isoString) {
    var date = new Date(isoString);
    return date.toLocaleDateString("ar-EG", { day: "numeric", month: "short", year: "numeric" });
  }

  function showToast(message, icon) {
    if (!toastContainer) return;
    var toast = document.createElement("div");
    toast.className = "toast";
    toast.innerHTML = '<span class="toast__icon">' + (icon || "✓") + "</span><span>" + escapeHtml(message) + "</span>";
    toastContainer.appendChild(toast);
    requestAnimationFrame(function () { toast.classList.add("is-visible"); });
    setTimeout(function () {
      toast.classList.remove("is-visible");
      setTimeout(function () { toast.remove(); }, 300);
    }, 2200);
  }

  // ============================================================
  // API CALLS (Fetch)
  // ============================================================
  const endpoint = notesPanel ? notesPanel.dataset.notesEndpoint : '';
  const novelId = notesPanel ? parseInt(notesPanel.dataset.novelId) : 0;
  const chapterId = notesPanel ? parseInt(notesPanel.dataset.chapterId) : null;

  async function loadNotes() {
    if (!endpoint || !novelId) return;
    try {
      const res = await fetch(endpoint + '?action=list&novel_id=' + novelId);
      const data = await res.json();
      if (data.success) {
        notes = data.notes || [];
        renderNotes(noteSearchInput ? noteSearchInput.value : "");
      } else {
        console.warn('Failed to load notes:', data.error);
      }
    } catch (e) {
      console.error('Error loading notes:', e);
    }
  }

  async function createNote(noteText) {
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('novel_id', novelId);
    if (chapterId) formData.append('chapter_id', chapterId);
    formData.append('page_number', getCurrentBookPage() || '');
    formData.append('note_text', noteText);

    try {
      const res = await fetch(endpoint, { method: 'POST', body: formData });
      const data = await res.json();
      if (data.success) {
        await loadNotes(); // Reload list
        return true;
      } else {
        showToast('خطأ: ' + data.error, '⚠️');
        return false;
      }
    } catch (e) {
      showToast('تعذر الاتصال بالخادم', '⚠️');
      console.error(e);
      return false;
    }
  }

  async function updateNote(id, noteText) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', id);
    formData.append('note_text', noteText);

    try {
      const res = await fetch(endpoint, { method: 'POST', body: formData });
      const data = await res.json();
      if (data.success) {
        await loadNotes();
        return true;
      } else {
        showToast('خطأ: ' + data.error, '⚠️');
        return false;
      }
    } catch (e) {
      showToast('تعذر الاتصال بالخادم', '⚠️');
      console.error(e);
      return false;
    }
  }

  async function deleteNote(id) {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    try {
      const res = await fetch(endpoint, { method: 'POST', body: formData });
      const data = await res.json();
      if (data.success) {
        await loadNotes();
      } else {
        showToast('خطأ: ' + data.error, '⚠️');
      }
    } catch (e) {
      showToast('تعذر الاتصال بالخادم', '⚠️');
      console.error(e);
    }
  }

  // ============================================================
  // RENDER
  // ============================================================
  function renderNotes(filterText) {
    var query = (filterText || "").trim();
    var visibleNotes = query
      ? notes.filter(function (n) { return n.note_text.indexOf(query) !== -1; })
      : notes.slice();

    visibleNotes.sort(function (a, b) { return new Date(b.created_at) - new Date(a.created_at); });
    noteListEl.innerHTML = "";
    notesEmptyState.classList.toggle("is-visible", visibleNotes.length === 0);

    if (visibleNotes.length === 0) {
      notesBadge.textContent = "0";
      return;
    }

    notesBadge.textContent = String(visibleNotes.length);

    visibleNotes.forEach(function (note) {
      var li = document.createElement("li");
      li.className = "note-card";
      li.setAttribute("data-note-id", note.id);

      var pageLabel = note.page_number ? "صفحة " + note.page_number : "";
      var dateLabel = formatDate(note.created_at);

      li.innerHTML =
        '<p class="note-card__text">' + escapeHtml(note.note_text) + "</p>" +
        '<div class="note-card__meta">' +
          (pageLabel ? "<span>" + pageLabel + "</span><span class=\"note-card__meta-sep\">•</span>" : "") +
          "<span>" + dateLabel + "</span>" +
        "</div>" +
        '<div class="note-card__actions">' +
          '<button type="button" class="note-card__action-btn note-card__action-btn--edit" data-action="edit">✏ تعديل</button>' +
          '<button type="button" class="note-card__action-btn note-card__action-btn--delete" data-action="delete">🗑 حذف</button>' +
        "</div>";

      noteListEl.appendChild(li);
    });
  }

  // ============================================================
  // MODAL
  // ============================================================
  function updateCounter() {
    if (!modalCounter) return;
    var len = modalTextarea.value.length;
    modalCounter.textContent = len + " / " + NOTE_MAX_LENGTH;
  }

  function setError(visible) {
    modalTextarea.classList.toggle("has-error", visible);
    if (modalError) modalError.classList.toggle("is-visible", visible);
  }

  function openModal(mode, note) {
    editingNoteId = mode === "edit" && note ? note.id : null;
    modalTitle.textContent = mode === "edit" ? "تعديل ملاحظة" : "إضافة ملاحظة";
    modalTextarea.value = mode === "edit" && note ? note.note_text : "";
    setError(false);
    updateCounter();
    modalOverlay.classList.add("is-open");
    setTimeout(function () { modalTextarea.focus(); }, 60);
  }

  function closeModal() {
    if (modalSaveBtn.classList.contains("is-loading")) return;
    modalOverlay.classList.remove("is-open");
    editingNoteId = null;
    modalTextarea.value = "";
    setError(false);
  }

  async function saveFromModal() {
    var text = modalTextarea.value.trim();
    if (!text) {
      setError(true);
      modalTextarea.focus();
      return;
    }
    setError(false);

    modalSaveBtn.disabled = true;
    modalSaveBtn.classList.add("is-loading");

    let success = false;
    if (editingNoteId) {
      success = await updateNote(editingNoteId, text);
    } else {
      success = await createNote(text);
    }

    modalSaveBtn.disabled = false;
    modalSaveBtn.classList.remove("is-loading");

    if (success) {
      closeModal();
      showToast(editingNoteId ? "تم تحديث الملاحظة" : "تم حفظ الملاحظة");
    } else {
        // Keep modal open if failed
    }
  }

  // ============================================================
  // EVENT LISTENERS
  // ============================================================
  // Restore Guard: If not logged in, clicking add button triggers a login prompt
  if (addNoteBtn) {
    addNoteBtn.addEventListener("click", function () { 
      if (!isLoggedIn) {
          alert("يجب تسجيل الدخول أولاً لإضافة ملاحظات.");
          // Optional: Redirect to login page
          // window.location.href = ROOT + "signup";
          return;
      }
      openModal("add"); 
    });
  }

  if (modalCancelBtn) modalCancelBtn.addEventListener("click", closeModal);
  if (modalCloseBtn) modalCloseBtn.addEventListener("click", closeModal);
  if (modalSaveBtn) modalSaveBtn.addEventListener("click", saveFromModal);

  modalOverlay.addEventListener("click", function (e) {
    if (e.target === modalOverlay) closeModal();
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && modalOverlay.classList.contains("is-open")) closeModal();
  });

  modalTextarea.addEventListener("input", function () {
    updateCounter();
    if (modalTextarea.value.trim()) setError(false);
  });

  modalTextarea.addEventListener("keydown", function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === "Enter") {
      e.preventDefault();
      saveFromModal();
    }
  });

  noteListEl.addEventListener("click", async function (e) {
    var btn = e.target.closest("[data-action]");
    if (!btn) return;
    var card = e.target.closest(".note-card");
    var id = card ? card.getAttribute("data-note-id") : null;
    if (!id) return;

    if (btn.getAttribute("data-action") === "edit") {
      var note = notes.find(function (n) { return String(n.id) === id; });
      if (note) openModal("edit", note);
    }
    if (btn.getAttribute("data-action") === "delete") {
      if (confirm("هل أنت متأكد من حذف هذه الملاحظة؟")) {
        await deleteNote(id);
      }
    }
  });

  if (noteSearchInput) {
    noteSearchInput.addEventListener("input", function () {
      renderNotes(noteSearchInput.value);
    });
  }

  // ============================================================
  // INIT
  // ============================================================
  if (notesPanel) {
    loadNotes();
  }

})();