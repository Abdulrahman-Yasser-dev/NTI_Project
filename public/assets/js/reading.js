/* ============================================================
   سرد — reading.js
   Realistic 3D Page Flip (RTL Book Style)
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
     SIDE PANEL & SEARCH
     ============================================================ */
  var sidePanel = $("#sidePanel");
  var drawerToggle = $("#drawerToggle");
  if (drawerToggle) {
    drawerToggle.addEventListener("click", function () {
      var open = sidePanel.classList.toggle("is-open");
      drawerToggle.setAttribute("aria-expanded", String(open));
    });
  }

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
     REAL 3D PAGE TURN ENGINE JS DRIVER
     ============================================================ */
  // DOM Elements for Engine
  var pageTextRight = $("#pageTextRight");
  var pageTextLeft = $("#pageTextLeft");
  var pageTurnWrapper = $("#pageTurnWrapper");
  var pageTextFront = $("#pageTextFront");
  var pageTextBack = $("#pageTextBack");
  var pageNumFront = $("#pageNumFront");
  var pageNumBack = $("#pageNumBack");
  var prevPageBtn = $("#prevPageBtn");
  var nextPageBtn = $("#nextPageBtn");
  var pageRightEl = document.getElementById("pageRight");
  var pageLeftEl = document.getElementById("pageLeft");

  // State
  var isFlipping = false;
  var currentPageNumber = 2;

  var toolbarEl = $("#readingToolbar");
  var totalBookPages = toolbarEl ? parseInt(toolbarEl.getAttribute("data-total-pages"), 10) || 0 : 0;
  var startBookPage = toolbarEl ? parseInt(toolbarEl.getAttribute("data-start-page"), 10) || 1 : 1;
  var toolbarPageIndicator = $("#toolbarPageIndicator");
  var toolbarDots = $all(".toolbar__dot");

  function renderSpread() {
    pageTextRight.textContent = bookPages[spreadIndex * 2] || "صفحة فارغة";
    pageTextLeft.textContent = bookPages[spreadIndex * 2 + 1] || "صفحة فارغة";
    
    var rightPageNum = startBookPage + (spreadIndex * 2);
    var leftPageNum = rightPageNum - 1;
    currentPageNumber = rightPageNum;
    
    var rightPageEl = document.querySelector(".page--right .page__number");
    var leftPageEl = document.querySelector(".page--left .page__number");
    if (rightPageEl) rightPageEl.textContent = rightPageNum;
    if (leftPageEl) leftPageEl.textContent = leftPageNum;
    
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
      var current = Math.min(totalBookPages, startBookPage + spreadIndex * 2);
      toolbarPageIndicator.textContent = current + " / " + totalBookPages;
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

  function flipPage(direction) {
    if (isFlipping) return;
    
    // Boundary checks
    var canGoNext = direction === "next" && spreadIndex < totalSpreads - 1;
    var canGoPrev = direction === "prev" && spreadIndex > 0;
    if (!canGoNext && !canGoPrev) return;

    isFlipping = true;

    // 1. Prepare the Flip Page Data
    var flipContent = direction === "next" ? pageTextRight.textContent : pageTextLeft.textContent;
    var flipPageNum = direction === "next" ? currentPageNumber : currentPageNumber - 1;
    
    var currentFontFamily = pageTextRight.style.fontFamily || "'Amiri', serif";
    var currentFontSize = pageTextRight.style.fontSize || "19px";
    var currentLineHeight = pageTextRight.style.lineHeight || "1.9";

    [pageTextFront, pageTextBack].forEach(function(el) {
      el.textContent = flipContent;
      el.style.fontFamily = currentFontFamily;
      el.style.fontSize = currentFontSize;
      el.style.lineHeight = currentLineHeight;
    });
    pageNumFront.textContent = flipPageNum;
    pageNumBack.textContent = flipPageNum;

    // 2. Reset the wrapper thoroughly to prevent glitches
    pageTurnWrapper.classList.remove("flip-forward", "flip-backward", "flip-forward-finalized", "flip-backward-finalized", "is-flipping");
    void pageTurnWrapper.offsetWidth; // Force reflow

    // 3. Trigger the animation
    pageTurnWrapper.classList.add("is-flipping");
    if (direction === "next") {
      pageTurnWrapper.classList.add("flip-forward");
    } else {
      pageTurnWrapper.classList.add("flip-backward");
    }

    // 4. Wait for the animation to finish (MUST match CSS 0.85s duration)
    setTimeout(function () {
      // Update the underlying data logic
      spreadIndex = direction === "next" ? spreadIndex + 1 : spreadIndex - 1;
      renderSpread();
      
      // Remove the active animation classes
      pageTurnWrapper.classList.remove("flip-forward", "flip-backward", "is-flipping");
      
      // Add the "Finalized" class to trigger Stack Thickness Shifting (1~3px shift)
      if (direction === "next") {
        pageTurnWrapper.classList.add("flip-forward-finalized");
        pageTurnWrapper.classList.remove("flip-backward-finalized");
      } else {
        pageTurnWrapper.classList.add("flip-backward-finalized");
        pageTurnWrapper.classList.remove("flip-forward-finalized");
      }
      
      isFlipping = false;
    }, 880); // 850ms animation + 30ms buffer
  }

  /* ============================================================
     EVENT LISTENERS (Mouse & Keyboard)
     ============================================================ */
  
  if (nextPageBtn) {
    nextPageBtn.addEventListener("click", function () { flipPage("next"); });
  }
  if (prevPageBtn) {
    prevPageBtn.addEventListener("click", function () { flipPage("prev"); });
  }

  if (pageRightEl) {
    pageRightEl.addEventListener("click", function(e) {
      if (window.getSelection().toString().length > 0) return;
      flipPage("next");
    });
  }
  if (pageLeftEl) {
    pageLeftEl.addEventListener("click", function(e) {
      if (window.getSelection().toString().length > 0) return;
      flipPage("prev");
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "ArrowLeft") {
      e.preventDefault();
      flipPage("next");
    }
    if (e.key === "ArrowRight") {
      e.preventDefault();
      flipPage("prev");
    }
  });

  // Touch Events for Mobile
  var touchStartX = 0;
  var touchStartY = 0;
  var bookContainer = document.getElementById("bookContainer");

  if (bookContainer) {
    bookContainer.addEventListener("touchstart", function(e) {
      var touch = e.touches[0];
      touchStartX = touch.clientX;
      touchStartY = touch.clientY;
    }, { passive: true });

    bookContainer.addEventListener("touchend", function(e) {
      if (touchStartX === 0) return;
      
      var touchEnd = e.changedTouches[0];
      var diffX = touchStartX - touchEnd.clientX;
      var diffY = Math.abs(touchStartY - touchEnd.clientY);
      
      if (Math.abs(diffX) > 40 && diffY < 80) {
        if (diffX > 0) { // Swipe left in RTL means Next
          flipPage("next");
        } else { // Swipe right in RTL means Prev
          flipPage("prev");
        }
      }
      touchStartX = 0;
    }, { passive: true });
  }

  renderSpread();

  /* ============================================================
     SETTINGS & THEMES 
     ============================================================ */
  var bookContainerEl = document.getElementById('bookContainer');
  var darkModeBtn = document.getElementById('darkModeBtn');
  var themeButtons = document.querySelectorAll('.settings-themes button');

  function applyTheme(theme) {
    if (!bookContainerEl) return;
    bookContainerEl.classList.remove('mode-dark', 'mode-sepia');
    if (theme === 'dark') bookContainerEl.classList.add('mode-dark');
    else if (theme === 'sepia') bookContainerEl.classList.add('mode-sepia');
    
    themeButtons.forEach(function(btn) {
      btn.classList.remove('active');
      if (btn.getAttribute('data-theme') === theme) btn.classList.add('active');
    });
    if (darkModeBtn) darkModeBtn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
    localStorage.setItem('sard_reading_theme', theme);
  }

  var savedTheme = localStorage.getItem('sard_reading_theme') || 'light';
  applyTheme(savedTheme);

  themeButtons.forEach(function(btn) {
    btn.addEventListener('click', function() { applyTheme(this.getAttribute('data-theme')); });
  });
  if (darkModeBtn) {
    darkModeBtn.addEventListener('click', function() {
      var currentTheme = localStorage.getItem('sard_reading_theme') || 'light';
      applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
    });
  }

  // Font Size
  var fontSizeSlider = document.getElementById('fontSizeSlider');
  var fontSizeDisplay = document.getElementById('fontSizeDisplay');

  if (fontSizeSlider && fontSizeDisplay) {
    var initialSize = parseInt(fontSizeSlider.value) || 19;
    fontSizeDisplay.textContent = initialSize;
    
    function applyFontSize(size) {
      var texts = document.querySelectorAll('.page__text, #pageTextRight, #pageTextLeft, #pageTextFront, #pageTextBack');
      texts.forEach(function(el) {
        if (el) el.style.fontSize = size + 'px';
      });
      document.documentElement.style.setProperty('--reader-font-size', size + 'px');
      fontSizeDisplay.textContent = size;
      localStorage.setItem('sard_font_size', size);
    }
    
    applyFontSize(initialSize);
    
    fontSizeSlider.addEventListener('input', function() {
      var val = parseInt(this.value);
      applyFontSize(val);
    });
  }

  // Line Height
  var lineHeightSlider = document.getElementById('lineHeightSlider');
  var lineHeightDisplay = document.getElementById('lineHeightDisplay');

  if (lineHeightSlider && lineHeightDisplay) {
    var initialLineHeight = parseFloat(lineHeightSlider.value) || 1.9;
    lineHeightDisplay.textContent = initialLineHeight.toFixed(1);
    
    function applyLineHeight(val) {
      var texts = document.querySelectorAll('.page__text, #pageTextRight, #pageTextLeft, #pageTextFront, #pageTextBack');
      texts.forEach(function(el) {
        if (el) el.style.lineHeight = val;
      });
      document.documentElement.style.setProperty('--reader-line-height', val);
      lineHeightDisplay.textContent = val.toFixed(1);
      localStorage.setItem('sard_line_height', val);
    }
    
    applyLineHeight(initialLineHeight);
    
    lineHeightSlider.addEventListener('input', function() {
      var val = parseFloat(this.value);
      applyLineHeight(val);
    });
  }

  // Font Family
  var fontFamilySelect = document.getElementById('fontFamilySelect');

  if (fontFamilySelect) {
    var initialFont = fontFamilySelect.value || "'Amiri', serif";
    
    function applyFontFamily(value) {
      var texts = document.querySelectorAll('.page__text, #pageTextRight, #pageTextLeft, #pageTextFront, #pageTextBack');
      texts.forEach(function(el) {
        if (el) el.style.fontFamily = value;
      });
      localStorage.setItem('sard_font_family', value);
    }
    
    applyFontFamily(initialFont);
    
    fontFamilySelect.addEventListener('change', function() {
      applyFontFamily(this.value);
    });
  }

  // Zoom Button
  var zoomBtn = document.getElementById('zoomBtn');
  var zoomedIn = false;

  if (zoomBtn) {
    zoomBtn.addEventListener('click', function() {
      zoomedIn = !zoomedIn;
      var size = zoomedIn ? 23 : 19;
      
      if (fontSizeSlider) fontSizeSlider.value = size;
      if (fontSizeDisplay) fontSizeDisplay.textContent = size;
      
      var texts = document.querySelectorAll('.page__text, #pageTextRight, #pageTextLeft, #pageTextFront, #pageTextBack');
      texts.forEach(function(el) {
        if (el) el.style.fontSize = size + 'px';
      });
      document.documentElement.style.setProperty('--reader-font-size', size + 'px');
      zoomBtn.setAttribute('aria-pressed', String(zoomedIn));
      localStorage.setItem('sard_font_size', size);
    });
  }

  // Bookmark Button
  var toolbarBookmarkBtn = document.getElementById('toolbarBookmarkBtn');

  if (toolbarBookmarkBtn) {
    toolbarBookmarkBtn.addEventListener('click', function() {
      var pressed = this.getAttribute('aria-pressed') === 'true';
      this.setAttribute('aria-pressed', String(!pressed));
      if (!pressed) {
        this.style.color = '#C8A15A';
        var svg = this.querySelector('svg');
        if (svg) svg.style.fill = '#C8A15A';
      } else {
        this.style.color = '';
        var svg = this.querySelector('svg');
        if (svg) svg.style.fill = '';
      }
    });
  }

  // Load saved settings
  function loadSavedSettings() {
    var savedFontSize = localStorage.getItem('sard_font_size');
    if (savedFontSize && fontSizeSlider && fontSizeDisplay) {
      var size = parseInt(savedFontSize);
      fontSizeSlider.value = size;
      fontSizeDisplay.textContent = size;
      var texts = document.querySelectorAll('.page__text, #pageTextRight, #pageTextLeft, #pageTextFront, #pageTextBack');
      texts.forEach(function(el) {
        if (el) el.style.fontSize = size + 'px';
      });
      document.documentElement.style.setProperty('--reader-font-size', size + 'px');
    }
    
    var savedLineHeight = localStorage.getItem('sard_line_height');
    if (savedLineHeight && lineHeightSlider && lineHeightDisplay) {
      var lh = parseFloat(savedLineHeight);
      lineHeightSlider.value = lh;
      lineHeightDisplay.textContent = lh.toFixed(1);
      var texts = document.querySelectorAll('.page__text, #pageTextRight, #pageTextLeft, #pageTextFront, #pageTextBack');
      texts.forEach(function(el) {
        if (el) el.style.lineHeight = lh;
      });
      document.documentElement.style.setProperty('--reader-line-height', lh);
    }
    
    var savedFontFamily = localStorage.getItem('sard_font_family');
    if (savedFontFamily && fontFamilySelect) {
      fontFamilySelect.value = savedFontFamily;
      var texts = document.querySelectorAll('.page__text, #pageTextRight, #pageTextLeft, #pageTextFront, #pageTextBack');
      texts.forEach(function(el) {
        if (el) el.style.fontFamily = savedFontFamily;
      });
    }
  }

  loadSavedSettings();

  // SETTINGS POPOVER - Toggle
  var fontSettingsBtn = document.getElementById('fontSettingsBtn');
  var settingsPopover = document.getElementById('settingsPopover');

  if (fontSettingsBtn && settingsPopover) {
    fontSettingsBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      var isOpen = settingsPopover.classList.toggle('is-open');
      fontSettingsBtn.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', function(e) {
      if (
        settingsPopover.classList.contains('is-open') &&
        !settingsPopover.contains(e.target) &&
        e.target !== fontSettingsBtn &&
        !fontSettingsBtn.contains(e.target)
      ) {
        settingsPopover.classList.remove('is-open');
        fontSettingsBtn.setAttribute('aria-expanded', 'false');
      }
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && settingsPopover.classList.contains('is-open')) {
        settingsPopover.classList.remove('is-open');
        fontSettingsBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Opening Animation
  var bookOpeningCover = $("#bookOpeningCover");
  if (bookOpeningCover) {
    setTimeout(function () {
      if (bookOpeningCover && bookOpeningCover.parentNode) {
        bookOpeningCover.parentNode.removeChild(bookOpeningCover);
      }
    }, 1300);
  }

})();