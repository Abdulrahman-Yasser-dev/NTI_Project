/**
 * BrowseBooks.js — Premium Luxury Bookstore
 * سرد (Sard) — Arabic Reading Platform
 */

document.addEventListener('DOMContentLoaded', function() {

    'use strict';

    const searchInput = document.getElementById('searchInput');
    const searchClear = document.getElementById('searchClear');
    const categoryChips = document.querySelectorAll('.category-premium-chip');
    const shelfSections = document.querySelectorAll('.shelf-premium-section');
    const emptyState = document.getElementById('emptyState');
    const books = document.querySelectorAll('.book-premium-stand');

    let currentSearch = '';
    let currentCategory = 'الكل';

    // ============================================================
    // CATEGORY FILTER — Show/Hide Shelves with Animation
    // ============================================================

    function filterCategory(category) {
        let hasVisibleBooks = false;

        shelfSections.forEach(function(section) {
            const sectionCategory = section.dataset.category;
            
            if (category === 'الكل' || sectionCategory === category) {
                section.classList.remove('hidden');
                section.style.display = '';
                // Trigger reflow for animation
                void section.offsetWidth;
                section.style.animation = 'none';
                setTimeout(function() {
                    section.style.animation = 'shelfFadeIn 0.5s ease forwards';
                }, 10);
                hasVisibleBooks = true;
            } else {
                section.style.animation = 'shelfFadeIn 0.3s ease reverse forwards';
                setTimeout(function() {
                    section.classList.add('hidden');
                    section.style.display = 'none';
                }, 250);
            }
        });

        // Show/hide empty state
        if (emptyState) {
            if (!hasVisibleBooks) {
                emptyState.classList.add('visible');
            } else {
                emptyState.classList.remove('visible');
            }
        }
    }

    // ============================================================
    // SEARCH
    // ============================================================

    function filterBooks() {
        const searchTerm = currentSearch.trim().toLowerCase();
        const category = currentCategory;

        let hasVisibleBooks = false;

        books.forEach(function(book) {
            const title = book.getAttribute('title')?.toLowerCase() || '';
            const bookCategory = book.dataset.category || '';

            const matchesSearch = title.includes(searchTerm);
            const matchesCategory = category === 'الكل' || bookCategory === category;

            if (matchesSearch && matchesCategory) {
                book.style.display = '';
                hasVisibleBooks = true;
                book.style.animation = 'none';
                setTimeout(function() {
                    book.style.animation = 'bookRise 0.4s ease forwards';
                }, 10);
            } else {
                book.style.display = 'none';
            }
        });

        // Update empty state
        if (emptyState) {
            if (!hasVisibleBooks) {
                emptyState.classList.add('visible');
            } else {
                emptyState.classList.remove('visible');
            }
        }
    }

    // ============================================================
    // SEARCH INPUT
    // ============================================================

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentSearch = this.value;
            if (searchClear) {
                searchClear.classList.toggle('visible', this.value.length > 0);
            }
            filterBooks();
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', function() {
            if (searchInput) {
                searchInput.value = '';
                searchClear.classList.remove('visible');
                currentSearch = '';
                filterBooks();
                searchInput.focus();
            }
        });
    }

    // ============================================================
    // CATEGORY CHIPS
    // ============================================================

    if (categoryChips.length > 0) {
        categoryChips.forEach(function(chip) {
            chip.addEventListener('click', function() {
                categoryChips.forEach(function(c) { c.classList.remove('active'); });
                this.classList.add('active');
                currentCategory = this.dataset.category;
                
                // Reset search
                if (searchInput) {
                    searchInput.value = '';
                    if (searchClear) searchClear.classList.remove('visible');
                    currentSearch = '';
                }
                
                filterCategory(currentCategory);
                filterBooks();
            });
        });
    }

    // ============================================================
    // NAVBAR SCROLL EFFECT
    // ============================================================

    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', function() {
        if (navbar) {
            navbar.classList.toggle('scrolled', window.scrollY > 60);
        }
    });

    // ============================================================
    // KEYBOARD SHORTCUTS
    // ============================================================

    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey && e.key === 'f') || (e.key === '/' && !e.ctrlKey && !e.metaKey)) {
            e.preventDefault();
            if (searchInput) searchInput.focus();
        }
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            if (searchInput) {
                searchInput.blur();
                searchInput.value = '';
                if (searchClear) searchClear.classList.remove('visible');
                currentSearch = '';
                filterBooks();
            }
        }
    });

    // ============================================================
    // INIT
    // ============================================================

    // Show all shelves by default
    filterCategory('الكل');
    filterBooks();

    console.log('%c📖 سرد — Premium Luxury Bookstore', 'font-size:20px; font-weight:bold; color:#D4A64A;');
    console.log('%cAll books loaded — 3 categories', 'color:#E8C86A;');

});