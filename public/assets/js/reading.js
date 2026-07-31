/* ============================================================
   سرد — reading.js
   Clean reading experience with page turns
   ============================================================ */
(function () {
  "use strict";

  var pagesDataEl = document.getElementById("bookPagesData");
  var bookPages = [];
  try {
    bookPages = JSON.parse(pagesDataEl ? pagesDataEl.textContent : "[]");
  } catch (e) {
    bookPages = [];
  }

  var spreadIndex = 0;
  var totalSpreads = Math.max(1, Math.ceil(bookPages.length / 2));

  function $(sel, ctx) { return (ctx || document).querySelector(sel); }
  function $all(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

  /* ============================================================
     SIDE PANEL DRAWER
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

  /* ============================================================
     PAGE TURN (FLIP) ANIMATION
     ============================================================ */
  var pageTextRight = $("#pageTextRight");
  var pageTextLeft = $("#pageTextLeft");
  var pageFlip = $("#pageFlip");
  var pageTextFlip = $("#pageTextFlip");
  var prevPageBtn = $("#prevPageBtn");
  var nextPageBtn = $("#nextPageBtn");
  var isFlipping = false;

  var toolbarEl = $("#readingToolbar");
  var totalBookPages = toolbarEl ? parseInt(toolbarEl.getAttribute("data-total-pages"), 10) || 0 : 0;
  var startBookPage = toolbarEl ? parseInt(toolbarEl.getAttribute("data-start-page"), 10) || 1 : 1;
  var toolbarPageIndicator = $("#toolbarPageIndicator");
  var toolbarDots = $all(".toolbar__dot");

  function renderSpread() {
    pageTextRight.textContent = bookPages[spreadIndex * 2] || "";
    pageTextLeft.textContent = bookPages[spreadIndex * 2 + 1] || "";
    updateBookProgress();
  }

  function updateBookProgress() {
    var pct = Math.round(((spreadIndex + 1) / totalSpreads) * 100);
    $all(".mini-progress__fill, .chapters-progress__fill, .reading-progress__fill").forEach(function (el) {
      el.style.width = pct + "%";
    });
    $all(".reading-progress__percent").forEach(function (el) {
      el.textContent = pct + "% مكتمل";
    });

    if (toolbarPageIndicator && totalBookPages) {
      var currentPage = Math.min(totalBookPages, startBookPage + spreadIndex * 2);
      toolbarPageIndicator.textContent = currentPage + " / " + totalBookPages;
    }

    $all(".reading-progress__pages").forEach(function (el) {
      var current = Math.min(totalBookPages, startBookPage + spreadIndex * 2);
      el.textContent = "صفحة " + current + " من " + totalBookPages;
    });

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
     TOOLBAR — zoom, font, dark mode, bookmark
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
  var fontSizeDisplay = document.getElementById("fontSizeDisplay");

  function setFontSize(px) {
    px = Math.max(14, Math.min(28, px));
    document.documentElement.style.setProperty("--reader-font-size", px + "px");
    if (fontSizeSlider) fontSizeSlider.value = px;
    if (fontSizeDisplay) fontSizeDisplay.textContent = px;
  }

  if (fontSizeSlider) {
    fontSizeSlider.addEventListener("input", function () {
      var val = parseInt(fontSizeSlider.value, 10);
      setFontSize(val);
    });
  }

  var lineHeightSlider = $("#lineHeightSlider");
  var lineHeightDisplay = document.getElementById("lineHeightDisplay");

  if (lineHeightSlider) {
    lineHeightSlider.addEventListener("input", function () {
      var val = parseFloat(lineHeightSlider.value).toFixed(1);
      document.documentElement.style.setProperty("--reader-line-height", val);
      if (lineHeightDisplay) lineHeightDisplay.textContent = val;
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
  var bookContainer = $("#bookContainer");

  // Also check for existing dark mode from before
  function applyTheme(theme) {
    if (theme === 'dark') {
      bookContainer.classList.add("mode-dark");
      bookContainer.classList.remove("mode-sepia");
    } else if (theme === 'sepia') {
      bookContainer.classList.add("mode-sepia");
      bookContainer.classList.remove("mode-dark");
    } else {
      bookContainer.classList.remove("mode-dark", "mode-sepia");
    }
  }

  if (darkModeBtn) {
    darkModeBtn.addEventListener("click", function () {
      var isDark = bookContainer.classList.toggle("mode-dark");
      darkModeBtn.setAttribute("aria-pressed", String(isDark));
      // If turning dark on, remove sepia
      if (isDark) {
        bookContainer.classList.remove("mode-sepia");
      }
      // Update theme buttons in popover if they exist
      var themeBtns = document.querySelectorAll(".settings-themes button");
      themeBtns.forEach(function (btn) {
        btn.classList.remove("active");
        if (btn.getAttribute("data-theme") === (isDark ? "dark" : "light")) {
          btn.classList.add("active");
        }
      });
    });
  }

  var toolbarBookmarkBtn = $("#toolbarBookmarkBtn");
  if (toolbarBookmarkBtn) {
    toolbarBookmarkBtn.addEventListener("click", function () {
      var pressed = toolbarBookmarkBtn.getAttribute("aria-pressed") === "true";
      toolbarBookmarkBtn.setAttribute("aria-pressed", String(!pressed));
    });
  }

  /* ============================================================
     SETTINGS POPOVER — Above Aa button (Kindle style)
     ============================================================ */
  var fontSettingsBtn = $("#fontSettingsBtn");
  var settingsPopover = $("#settingsPopover");

  if (fontSettingsBtn && settingsPopover) {
    // Toggle popover
    fontSettingsBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      var isOpen = settingsPopover.classList.toggle("is-open");
      fontSettingsBtn.setAttribute("aria-expanded", String(isOpen));
    });

    // Close when clicking outside
    document.addEventListener("click", function (e) {
      if (
        settingsPopover.classList.contains("is-open") &&
        !settingsPopover.contains(e.target) &&
        e.target !== fontSettingsBtn &&
        !fontSettingsBtn.contains(e.target)
      ) {
        settingsPopover.classList.remove("is-open");
        fontSettingsBtn.setAttribute("aria-expanded", "false");
      }
    });

    // Close on Escape
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && settingsPopover.classList.contains("is-open")) {
        settingsPopover.classList.remove("is-open");
        fontSettingsBtn.setAttribute("aria-expanded", "false");
      }
    });

    // Theme buttons inside settings
    var themeButtons = settingsPopover.querySelectorAll(".settings-themes button");
    themeButtons.forEach(function (btn) {
      btn.addEventListener("click", function () {
        // Update active state
        themeButtons.forEach(function (b) { b.classList.remove("active"); });
        btn.classList.add("active");

        // Apply theme
        var theme = btn.getAttribute("data-theme");
        applyTheme(theme);

        // Update dark mode button state
        if (darkModeBtn) {
          darkModeBtn.setAttribute("aria-pressed", theme === "dark" ? "true" : "false");
        }
      });
    });
  }

  /* ============================================================
     OPENING ANIMATION — remove after play
     ============================================================ */
  var bookOpeningCover = $("#bookOpeningCover");
  if (bookOpeningCover) {
    setTimeout(function () {
      if (bookOpeningCover && bookOpeningCover.parentNode) {
        bookOpeningCover.parentNode.removeChild(bookOpeningCover);
      }
    }, 1300);
  }

})();