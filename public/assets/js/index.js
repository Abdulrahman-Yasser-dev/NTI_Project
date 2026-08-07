/**
 * index.js — Premium Homepage
 * سرد (Sard) — Arabic Reading Platform
 */

document.addEventListener('DOMContentLoaded', function() {

    'use strict';

    // ============================================================
    // DOM ELEMENTS
    // ============================================================
    const modal = document.getElementById('bookModal');
    const closeBtn = document.getElementById('bookCloseBtn');
    const bookBtns = document.querySelectorAll('.book-btn');
    const modalCover = document.getElementById('bookCover');
    const modalTitle = document.getElementById('pTitle');
    const modalAuthor = document.getElementById('pAuthor');
    const modalMeta = document.getElementById('pMeta');
    const modalDesc = document.getElementById('pDesc');
    const navbar = document.getElementById('navbar');
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    const viewer = document.getElementById('bookViewer');
    const overlayBg = viewer.querySelector('.overlay-bg');
    const paperPanel = document.getElementById('paperPanel');
    const paperContent = document.getElementById('paperContent');

    // ============================================================
    // MODAL DATA (from PHP)
    // ============================================================
    const modalData = typeof MODAL_DATA !== 'undefined' ? MODAL_DATA : {};
    const rootUrl = typeof ROOT_URL !== 'undefined' ? ROOT_URL : '/';

    let activeWrapper = null;
    let selectedBook = null;
    let isOpen = false;
    let isAnimating = false;
    let originalRect = null;
    let placeholder = null;

    // ============================================================
    // OPEN MODAL FUNCTION
    // ============================================================
    function openBookModal(wrapper, bookId) {
        const data = modalData[bookId];
        if (!data) {
            console.warn('Book data not found for ID:', bookId);
            return;
        }

        if (isAnimating || isOpen) return;
        isAnimating = true;
        activeWrapper = wrapper;
        selectedBook = data;

        // Get exact position
        const rect = wrapper.getBoundingClientRect();
        originalRect = rect;

        // Create placeholder
        placeholder = document.createElement('div');
        placeholder.style.width = rect.width + 'px';
        placeholder.style.height = rect.height + 'px';
        placeholder.style.flex = '1 1 0';
        placeholder.style.minWidth = wrapper.style.minWidth || '40px';
        placeholder.style.maxWidth = wrapper.style.maxWidth || '130px';
        placeholder.style.marginRight = wrapper.style.marginRight || '-5px';
        placeholder.style.marginLeft = wrapper.style.marginLeft || '-5px';
        placeholder.style.visibility = 'hidden';
        wrapper.parentElement.insertBefore(placeholder, wrapper);

        // Make wrapper fly
        wrapper.classList.add('flying');
        wrapper.style.left = rect.left + 'px';
        wrapper.style.top = rect.top + 'px';
        wrapper.style.width = rect.width + 'px';
        wrapper.style.height = rect.height + 'px';
        wrapper.style.transform = 'scale(1) rotateY(0deg)';
        wrapper.style.opacity = '1';
        wrapper.style.zIndex = '1003';

        // Stage 1: lift & scale to center
        requestAnimationFrame(function() {
            const w = window.innerWidth;
            const h = window.innerHeight;
            const targetW = Math.min(w * 0.35, 320);
            const targetH = targetW * (rect.height / rect.width);
            const cx = (w - targetW) / 2;
            const cy = (h - targetH) / 2;

            wrapper.style.left = cx + 'px';
            wrapper.style.top = cy + 'px';
            wrapper.style.width = targetW + 'px';
            wrapper.style.height = targetH + 'px';
            wrapper.style.transform = 'scale(1.08) rotateY(-3deg) translateZ(20px)';
        });

        // After flight, show overlay & prepare paper
        setTimeout(function() {
            viewer.classList.add('active');

            // Set book cover (left)
            const coverPath = data.cover_image
                ? rootUrl + 'assets/images/' + data.cover_image
                : rootUrl + 'assets/images/placeholder.jpg';
            modalCover.src = coverPath;
            modalCover.alt = data.title || 'غلاف الكتاب';

            // Paper content
            modalTitle.textContent = data.title || 'عنوان الكتاب';
            modalAuthor.textContent = data.author || 'المؤلف';

            const category = data.category || 'رواية';
            const year = data.year || '—';
            const pages = data.pages || '—';
            const rating = data.rating || 0;
            const publisher = data.publisher || 'دار نشر';

            modalMeta.innerHTML = `
                <div class="meta-item"><span class="label">التصنيف</span><span class="value">${category}</span></div>
                <div class="meta-item"><span class="label">اللغة</span><span class="value">العربية</span></div>
                <div class="meta-item"><span class="label">الصفحات</span><span class="value">${pages}</span></div>
                <div class="meta-item"><span class="label">التقييم</span><span class="value">⭐ ${rating}</span></div>
                <div class="meta-item"><span class="label">الناشر</span><span class="value">${publisher}</span></div>
                <div class="meta-item"><span class="label">السنة</span><span class="value">${year}</span></div>
            `;
            modalDesc.textContent = data.desc || 'لا يوجد وصف متاح.';

            // Reset content visibility
            document.querySelectorAll('#paperContent .content-hidden, #paperContent .content-visible').forEach(function(el) {
                el.classList.remove('content-visible');
                el.classList.add('content-hidden');
            });

            // Paper slides from behind the book (right side)
            setTimeout(function() {
                paperPanel.classList.remove('hidden');
                paperPanel.classList.add('visible');

                // Content appears after paper slide
                setTimeout(function() {
                    paperContent.classList.add('visible');
                    var els = [modalTitle, modalAuthor, modalMeta, modalDesc, document.querySelector('#pActions')];
                    var delay = 200;
                    els.forEach(function(el) {
                        setTimeout(function() {
                            el.classList.remove('content-hidden');
                            el.classList.add('content-visible');
                        }, delay);
                        delay += 100;
                    });
                    closeBtn.classList.add('visible');
                    isOpen = true;
                    isAnimating = false;
                    // Hide the flying book (it's now part of modal)
                    wrapper.style.display = 'none';
                }, 500);
            }, 200);
        }, 700);
    }

    // ============================================================
    // CLOSE MODAL FUNCTION
    // ============================================================
    function closeModal() {
        if (!isOpen || isAnimating) return;
        isAnimating = true;

        // Hide content
        document.querySelectorAll('#paperContent .content-visible').forEach(function(el) {
            el.classList.remove('content-visible');
            el.classList.add('content-hidden');
        });
        closeBtn.classList.remove('visible');

        setTimeout(function() {
            paperContent.classList.remove('visible');

            setTimeout(function() {
                paperPanel.classList.remove('visible');
                paperPanel.classList.add('hidden');

                setTimeout(function() {
                    viewer.classList.remove('active');

                    var wrapper = activeWrapper;
                    if (wrapper && originalRect) {
                        // Restore wrapper
                        wrapper.style.display = 'block';
                        wrapper.classList.remove('flying');
                        wrapper.style.position = '';
                        wrapper.style.left = '';
                        wrapper.style.top = '';
                        wrapper.style.width = '';
                        wrapper.style.height = '';
                        wrapper.style.transform = '';
                        wrapper.style.zIndex = '';
                        wrapper.style.pointerEvents = '';
                        wrapper.style.opacity = '';
                        wrapper.style.filter = '';
                        wrapper.style.transition = '';

                        // Remove placeholder
                        if (placeholder && placeholder.parentElement) {
                            placeholder.parentElement.removeChild(placeholder);
                        }

                        // Reinsert wrapper at original position
                        if (originalRect && originalRect.parent) {
                            var children = originalRect.parent.children;
                            var inserted = false;
                            for (var i = 0; i < children.length; i++) {
                                if (children[i].classList.contains('book-placeholder')) continue;
                                if (i >= originalRect.index) {
                                    originalRect.parent.insertBefore(wrapper, children[i]);
                                    inserted = true;
                                    break;
                                }
                            }
                            if (!inserted) {
                                originalRect.parent.appendChild(wrapper);
                            }
                        }
                    }

                    modalCover.src = '';
                    paperPanel.style.transform = '';
                    paperPanel.style.opacity = '';

                    activeWrapper = null;
                    selectedBook = null;
                    isOpen = false;
                    isAnimating = false;
                    placeholder = null;
                    originalRect = null;

                }, 400);
            }, 300);
        }, 300);
    }

    // ============================================================
    // EVENT LISTENERS — Book Buttons
    // ============================================================
    bookBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const bookId = parseInt(this.dataset.book);
            if (bookId && modalData[bookId]) {
                const wrapper = this.closest('.book-wrapper');
                if (wrapper) {
                    const parent = wrapper.parentElement;
                    const idx = Array.from(parent.children).indexOf(wrapper);
                    originalRect = { parent: parent, index: idx };
                }
                openBookModal(wrapper, bookId);
            } else {
                console.warn('Book ID not found or invalid:', bookId);
            }
        });
    });

    // ============================================================
    // EVENT LISTENERS — Modal Close
    // ============================================================
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    overlayBg.addEventListener('click', closeModal);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isOpen) {
            closeModal();
        }
    });

    // ============================================================
    // NAVBAR SCROLL EFFECT
    // ============================================================
    window.addEventListener('scroll', function() {
        if (window.scrollY > 60) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // ============================================================
    // HAMBURGER MENU TOGGLE
    // ============================================================
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function() {
            var isOpen = navLinks.classList.contains('mobile-open');

            if (isOpen) {
                navLinks.classList.remove('mobile-open');
                navToggle.innerHTML = '<i class="fas fa-bars"></i>';
            } else {
                navLinks.classList.add('mobile-open');
                navToggle.innerHTML = '<i class="fas fa-times"></i>';
            }
        });

        navLinks.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                navLinks.classList.remove('mobile-open');
                navToggle.innerHTML = '<i class="fas fa-bars"></i>';
            });
        });
    }

    // ============================================================
    // CLOSE MOBILE MENU ON RESIZE
    // ============================================================
    window.addEventListener('resize', function() {
        if (window.innerWidth > 900 && navLinks) {
            navLinks.classList.remove('mobile-open');
            if (navToggle) {
                navToggle.innerHTML = '<i class="fas fa-bars"></i>';
            }
        }
    });

    // ============================================================
    // RESPONSIVE BOOK SIZES
    // ============================================================
    function adjustBookSizes() {
        var wrappers = document.querySelectorAll('.book-wrapper:not(.flying)');
        var w = window.innerWidth;
        wrappers.forEach(function(el) {
            if (w < 400) { el.style.minWidth = '14px'; el.style.maxWidth = '42px'; }
            else if (w < 550) { el.style.minWidth = '18px'; el.style.maxWidth = '55px'; }
            else if (w < 768) { el.style.minWidth = '24px'; el.style.maxWidth = '70px'; }
            else if (w < 992) { el.style.minWidth = '28px'; el.style.maxWidth = '85px'; }
            else if (w < 1200) { el.style.minWidth = '32px'; el.style.maxWidth = '100px'; }
            else if (w < 1400) { el.style.minWidth = '36px'; el.style.maxWidth = '115px'; }
            else { el.style.minWidth = '40px'; el.style.maxWidth = '130px'; }
        });
    }
    window.addEventListener('load', adjustBookSizes);
    window.addEventListener('resize', adjustBookSizes);

    // ============================================================
    // PROFILE DROPDOWN
    // ============================================================
    window.toggleProfileMenu = function(e) {
        if(e) e.stopPropagation();
        var menu = document.getElementById('profileMenu');
        if(menu) {
            menu.classList.toggle('show');
        }
    };

    window.addEventListener('click', function(e) {
        var toggle = document.querySelector('.profile-toggle');
        var menu = document.getElementById('profileMenu');
        if (toggle && menu) {
            if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        }
    });

    // ============================================================
    // CONSOLE
    // ============================================================
    console.log('%c📖 سرد — Premium Homepage Loaded', 'font-size:20px; font-weight:bold; color:#e7c877;');
    console.log('%c' + Object.keys(modalData).length + ' books loaded successfully!', 'color:#c69a45;');
    console.log('%c✨ Using homepage_books table for curated shelf', 'color:#b5a184;');

});