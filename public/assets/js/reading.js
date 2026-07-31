/* ============================================================
   سرد — reading.js
   Vanilla JS only. No frameworks.
   ============================================================ */
(function () {
  "use strict";

  /* ----------------------------------------------------------
     Data handed off from PHP
     ---------------------------------------------------------- */
  var pagesDataEl = document.getElementById("bookPagesData");
  var bookPages = [];
  try {
    bookPages = JSON.parse(pagesDataEl ? pagesDataEl.textContent : "[]");
  } catch (e) {
    bookPages = [];
  }
  // We always show a *spread* of two pages, so pair them up.
  var spreadIndex = 0; // index of the right-hand page in bookPages
  var totalSpreads = Math.max(1, Math.ceil(bookPages.length / 2));

  /* ----------------------------------------------------------
     Helpers
     ---------------------------------------------------------- */
  function $(sel, ctx) { return (ctx || document).querySelector(sel); }
  function $all(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

  function animateFills() {
    $all("[data-target]").forEach(function (el) {
      var target = parseInt(el.getAttribute("data-target"), 10) || 0;
      requestAnimationFrame(function () {
        el.style.width = target + "%";
      });
    });
  }
  window.addEventListener("load", animateFills);

  /* ============================================================
     BOOK INFO — mobile collapsible
     ============================================================ */
  var bookInfoPanel = $("#bookInfoPanel");
  var bookInfoToggle = $("#bookInfoToggle");
  if (bookInfoToggle) {
    bookInfoToggle.addEventListener("click", function () {
      var expanded = bookInfoPanel.classList.toggle("is-expanded");
      bookInfoToggle.setAttribute("aria-expanded", String(expanded));
    });
  }

  /* ============================================================
     SIDE PANEL DRAWER (tablet / phone)
     ============================================================ */
  var sidePanel = $("#sidePanel");
  var drawerToggle = $("#drawerToggle");
  if (drawerToggle) {
    drawerToggle.addEventListener("click", function () {
      var open = sidePanel.classList.toggle("is-open");
      drawerToggle.setAttribute("aria-expanded", String(open));
    });
  }

  /* ============================================================
     TABS — الفصول / التعليقات
     ============================================================ */
  var tabChapters = $("#tabChapters");
  var tabComments = $("#tabComments");
  var chaptersPanel = $("#chaptersPanel");
  var commentsPanel = $("#commentsPanel");

  function activateTab(which) {
    var chaptersActive = which === "chapters";
    tabChapters.classList.toggle("tab--active", chaptersActive);
    tabComments.classList.toggle("tab--active", !chaptersActive);
    tabChapters.setAttribute("aria-selected", String(chaptersActive));
    tabComments.setAttribute("aria-selected", String(!chaptersActive));
    chaptersPanel.hidden = !chaptersActive;
    commentsPanel.hidden = chaptersActive;
  }
  if (tabChapters && tabComments) {
    tabChapters.addEventListener("click", function () { activateTab("chapters"); });
    tabComments.addEventListener("click", function () { activateTab("comments"); });
  }

  /* ============================================================
     CHAPTER SEARCH
     ============================================================ */
  var chapterSearchInput = $("#chapterSearchInput");
  var chapterRows = $all(".chapter-row");
  if (chapterSearchInput) {
    chapterSearchInput.addEventListener("input", function () {
      var q = chapterSearchInput.value.trim();
      chapterRows.forEach(function (row) {
        var match = row.getAttribute("data-title").indexOf(q) !== -1;
        row.style.display = match ? "" : "none";
      });
    });
  }
  chapterRows.forEach(function (row) {
    row.addEventListener("click", function () { setActiveChapter(row); });
    row.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") { e.preventDefault(); setActiveChapter(row); }
    });
  });
  function setActiveChapter(row) {
    chapterRows.forEach(function (r) { r.classList.remove("chapter-row--current"); });
    row.classList.remove("chapter-row--completed", "chapter-row--unread");
    row.classList.add("chapter-row--current");
  }

  /* ============================================================
     COMMENTS
     ============================================================ */
  var commentForm = $("#commentForm");
  var commentInput = $("#commentInput");
  var commentList = $("#commentList");
  var commentEmptyState = $("#commentEmptyState");

  if (commentForm) {
    commentForm.addEventListener("submit", function (e) {
      e.preventDefault();
      var text = commentInput.value.trim();
      if (!text) return;

      if (commentEmptyState) {
        commentEmptyState.remove();
        commentEmptyState = null;
      }

      var li = document.createElement("li");
      li.className = "comment-card";
      li.innerHTML =
        '<div class="comment-card__avatar" aria-hidden="true">ز</div>' +
        '<div class="comment-card__body">' +
        '<div class="comment-card__head"><span class="comment-card__name">أنتِ</span><span class="comment-card__date">الآن</span></div>' +
        '<p class="comment-card__text"></p>' +
        '<div class="comment-card__actions"><button class="comment-action">إعجاب</button><button class="comment-action">رد</button></div>' +
        "</div>";
      li.querySelector(".comment-card__text").textContent = text;
      commentList.insertBefore(li, commentList.firstChild);
      commentInput.value = "";
    });
  }

  /* ============================================================
     OPEN BOOK — page turn (flip) animation
     ============================================================ */
  var bookContainer = $("#bookContainer");
  var pageTextRight = $("#pageTextRight");
  var pageTextLeft = $("#pageTextLeft");
  var pageFlip = $("#pageFlip");
  var pageTextFlip = $("#pageTextFlip");
  var prevPageBtn = $("#prevPageBtn");
  var nextPageBtn = $("#nextPageBtn");
  var isFlipping = false;

  function renderSpread() {
    pageTextRight.textContent = bookPages[spreadIndex * 2] || "";
    pageTextLeft.textContent = bookPages[spreadIndex * 2 + 1] || "";
    updateBookProgress();
  }

  var toolbarEl = $("#readingToolbar");
  var totalBookPages = toolbarEl ? parseInt(toolbarEl.getAttribute("data-total-pages"), 10) || 0 : 0;
  var startBookPage = toolbarEl ? parseInt(toolbarEl.getAttribute("data-start-page"), 10) || 1 : 1;
  var toolbarPageIndicator = $("#toolbarPageIndicator");
  var toolbarDots = $all(".toolbar__dot");

  function updateBookProgress() {
    var pct = Math.round(((spreadIndex + 1) / totalSpreads) * 100);
    $all(".mini-progress__fill, .chapters-progress__fill").forEach(function (el) {
      el.style.width = pct + "%";
    });

    if (toolbarPageIndicator && totalBookPages) {
      var currentPage = Math.min(totalBookPages, startBookPage + spreadIndex * 2);
      toolbarPageIndicator.textContent = currentPage + " / " + totalBookPages;
    }

    var filledDots = Math.max(1, Math.round((pct / 100) * toolbarDots.length));
    toolbarDots.forEach(function (dot, i) {
      dot.classList.toggle("toolbar__dot--filled", i < filledDots);
    });
  }

  function flip(direction) {
    if (isFlipping) return;
    var canGoNext = direction === "next" && spreadIndex < totalSpreads - 1;
    var canGoPrev = direction === "prev" && spreadIndex > 0;
    if (!canGoNext && !canGoPrev) return;

    isFlipping = true;

    var currentText = direction === "next" ? pageTextRight.textContent : pageTextLeft.textContent;
    pageTextFlip.textContent = currentText;
    pageTextFlip.style.fontFamily = pageTextRight.style.fontFamily;
    pageTextFlip.style.fontSize = pageTextRight.style.fontSize;
    pageTextFlip.style.lineHeight = pageTextRight.style.lineHeight;

    pageFlip.classList.remove("is-flipping-next", "is-flipping-prev");
    // force reflow so the animation restarts cleanly
    void pageFlip.offsetWidth;
    pageFlip.classList.add(direction === "next" ? "is-flipping-next" : "is-flipping-prev");

    pageFlip.addEventListener("animationend", function onEnd() {
      pageFlip.removeEventListener("animationend", onEnd);
      pageFlip.classList.remove("is-flipping-next", "is-flipping-prev");
      spreadIndex = direction === "next" ? spreadIndex + 1 : spreadIndex - 1;
      renderSpread();
      isFlipping = false;
    });
  }

  if (nextPageBtn) nextPageBtn.addEventListener("click", function () { flip("next"); });
  if (prevPageBtn) prevPageBtn.addEventListener("click", function () { flip("prev"); });
  document.addEventListener("keydown", function (e) {
    if (e.key === "ArrowLeft") flip(document.documentElement.dir === "rtl" ? "next" : "prev");
    if (e.key === "ArrowRight") flip(document.documentElement.dir === "rtl" ? "prev" : "next");
  });

  renderSpread();

  /* ============================================================
     TOOLBAR — zoom, font size, font family, dark/sepia, bookmark,
     fullscreen, settings popover
     ============================================================ */
  var zoomBtn = $("#zoomBtn");
  var zoomedIn = false;
  if (zoomBtn) {
    zoomBtn.addEventListener("click", function () {
      zoomedIn = !zoomedIn;
      document.documentElement.style.setProperty("--reader-font-size", zoomedIn ? "23px" : "19px");
      zoomBtn.setAttribute("aria-pressed", String(zoomedIn));
    });
  }

  var fontSizeSlider = $("#fontSizeSlider");
  function setFontSize(px) {
    px = Math.max(14, Math.min(28, px));
    document.documentElement.style.setProperty("--reader-font-size", px + "px");
    if (fontSizeSlider) fontSizeSlider.value = px;
  }
  if (fontSizeSlider) fontSizeSlider.addEventListener("input", function () {
    setFontSize(parseInt(fontSizeSlider.value, 10));
  });

  var lineHeightSlider = $("#lineHeightSlider");
  if (lineHeightSlider) {
    lineHeightSlider.addEventListener("input", function () {
      document.documentElement.style.setProperty("--reader-line-height", lineHeightSlider.value);
    });
  }

  var fontFamilySelect = $("#fontFamilySelect");
  function applyFontFamily(value) {
    [pageTextRight, pageTextLeft, pageTextFlip].forEach(function (el) {
      if (el) el.style.fontFamily = value;
    });
  }
  if (fontFamilySelect) {
    fontFamilySelect.addEventListener("change", function () {
      applyFontFamily(fontFamilySelect.value);
    });
  }

  var darkModeBtn = $("#darkModeBtn");
  if (darkModeBtn) {
    darkModeBtn.addEventListener("click", function () {
      var active = bookContainer.classList.toggle("mode-dark");
      darkModeBtn.setAttribute("aria-pressed", String(active));
    });
  }

  var toolbarBookmarkBtn = $("#toolbarBookmarkBtn");
  var bookmarkToggleBtn = $("#bookmarkToggleBtn");
  function toggleBookmark() {
    var pressed = toolbarBookmarkBtn.getAttribute("aria-pressed") === "true";
    var next = String(!pressed);
    toolbarBookmarkBtn.setAttribute("aria-pressed", next);
    if (bookmarkToggleBtn) bookmarkToggleBtn.setAttribute("aria-pressed", next);
  }
  if (toolbarBookmarkBtn) toolbarBookmarkBtn.addEventListener("click", toggleBookmark);
  if (bookmarkToggleBtn) bookmarkToggleBtn.addEventListener("click", toggleBookmark);

  var favoriteToggleBtn = $("#favoriteToggleBtn");
  if (favoriteToggleBtn) {
    favoriteToggleBtn.addEventListener("click", function () {
      var pressed = favoriteToggleBtn.getAttribute("aria-pressed") === "true";
      favoriteToggleBtn.setAttribute("aria-pressed", String(!pressed));
    });
  }

  var fontSettingsBtn = $("#fontSettingsBtn");
  var settingsPopover = $("#settingsPopover");
  if (fontSettingsBtn && settingsPopover) {
    fontSettingsBtn.addEventListener("click", function () {
      var isHidden = settingsPopover.hidden;
      settingsPopover.hidden = !isHidden;
      fontSettingsBtn.setAttribute("aria-expanded", String(isHidden));
    });
    document.addEventListener("click", function (e) {
      if (!settingsPopover.hidden && !settingsPopover.contains(e.target) && e.target !== fontSettingsBtn && !fontSettingsBtn.contains(e.target)) {
        settingsPopover.hidden = true;
        fontSettingsBtn.setAttribute("aria-expanded", "false");
      }
    });
  }

  var bookOpeningCover = $("#bookOpeningCover");
  if (bookOpeningCover) {
    setTimeout(function () {
      if (bookOpeningCover && bookOpeningCover.parentNode) {
        bookOpeningCover.parentNode.removeChild(bookOpeningCover);
      }
    }, 1300);
  }

  var continueReadingBtn = $("#continueReadingBtn");
  if (continueReadingBtn) {
    continueReadingBtn.addEventListener("click", function () {
      $("#bookContainer").scrollIntoView({ behavior: "smooth", block: "center" });
    });
  }

})();