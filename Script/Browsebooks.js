// BrowseBooks.js
// Combines search + sidebar category + sort + grid/list view.
// initialCategory comes from PHP (?category=... in the URL).

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const categoryItems = document.querySelectorAll(".category-item");
    const sortSelect = document.getElementById("sortSelect");
    const cards = Array.from(document.querySelectorAll(".book-card"));
    const bookGrid = document.getElementById("bookGrid");
    const resultsCount = document.getElementById("resultsCount");
    const emptyState = document.getElementById("emptyState");
    const gridViewBtn = document.getElementById("gridViewBtn");
    const listViewBtn = document.getElementById("listViewBtn");

    let activeCategory = typeof initialCategory !== "undefined" ? initialCategory : "all";

    // ===== Sidebar category selection =====
    categoryItems.forEach(function (item) {
        item.addEventListener("click", function () {
            categoryItems.forEach(function (i) { i.classList.remove("category-active"); });
            item.classList.add("category-active");
            activeCategory = item.dataset.category;
            applyFilters();
        });
    });

    // ===== Search =====
    searchInput.addEventListener("input", applyFilters);

    // ===== Sort =====
    sortSelect.addEventListener("change", applyFilters);

    // ===== Grid / List view toggle =====
    gridViewBtn.addEventListener("click", function () {
        bookGrid.classList.remove("list-mode");
        gridViewBtn.classList.add("view-active");
        listViewBtn.classList.remove("view-active");
    });

    listViewBtn.addEventListener("click", function () {
        bookGrid.classList.add("list-mode");
        listViewBtn.classList.add("view-active");
        gridViewBtn.classList.remove("view-active");
    });

    function applyFilters() {
        const term = searchInput.value.trim().toLowerCase();
        let visible = [];

        cards.forEach(function (card) {
            const matchesCategory = activeCategory === "all" || card.dataset.category === activeCategory;
            const matchesSearch =
                term === "" ||
                card.dataset.title.includes(term) ||
                card.dataset.author.includes(term);

            const show = matchesCategory && matchesSearch;
            card.style.display = show ? "" : "none";
            if (show) visible.push(card);
        });

        applySort(visible);

        resultsCount.textContent = visible.length + " رواية";
        bookGrid.style.display = visible.length === 0 ? "none" : "";
        emptyState.style.display = visible.length === 0 ? "block" : "none";
    }

    function applySort(visibleCards) {
        const sortValue = sortSelect.value;
        if (sortValue === "default") return; // keep original DOM order (newest first, as rendered by PHP)

        const sorted = visibleCards.slice().sort(function (a, b) {
            const titleA = a.dataset.titleRaw;
            const titleB = b.dataset.titleRaw;
            if (sortValue === "title-asc") return titleA.localeCompare(titleB, "ar");
            if (sortValue === "title-desc") return titleB.localeCompare(titleA, "ar");
            return 0;
        });

        sorted.forEach(function (card) { bookGrid.appendChild(card); });
    }

    applyFilters(); // run once on load
});