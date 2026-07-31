/**
 * BookDetails.js — Premium Book Details Page
 * سرد (Sard) — Arabic Reading Platform
 */

document.addEventListener('DOMContentLoaded', function() {

    'use strict';

    // ============================================================
    // TABS (Chapters / Comments)
    // ============================================================

    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    if (tabBtns.length > 0) {
        tabBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const target = this.dataset.tab;

                tabBtns.forEach(function(b) {
                    b.classList.remove('active');
                });
                this.classList.add('active');

                tabContents.forEach(function(content) {
                    content.classList.remove('active');
                });

                const targetContent = document.getElementById('tab-' + target);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    }

    // ============================================================
    // START READING BUTTON
    // ============================================================

    const startBtn = document.getElementById('startReadingBtn');
    if (startBtn) {
        startBtn.addEventListener('click', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const bookId = urlParams.get('id') || 1;
            window.location.href = 'reading.php?id=' + bookId;
        });
    }

    // ============================================================
    // NAVBAR SCROLL EFFECT
    // ============================================================

    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 60) {
                navbar.style.background = 'rgba(252, 248, 242, 0.95)';
            } else {
                navbar.style.background = 'rgba(252, 248, 242, 0.92)';
            }
        });
    }

    // ============================================================
    // BOOK COVER FLOATING (Mouse hover interaction)
    // ============================================================

    const cover = document.querySelector('.book-cover-floating');
    if (cover) {
        cover.addEventListener('mouseenter', function() {
            this.style.animationDuration = '1.5s';
        });
        cover.addEventListener('mouseleave', function() {
            this.style.animationDuration = '4s';
        });
    }

    // ============================================================
    // KEYBOARD SHORTCUTS
    // ============================================================

    document.addEventListener('keydown', function(e) {
        // 'R' key to start reading
        if ((e.key === 'r' || e.key === 'R') && !e.ctrlKey && !e.metaKey) {
            e.preventDefault();
            if (startBtn) startBtn.click();
        }
    });

    // ============================================================
    // CONSOLE
    // ============================================================

    console.log('%c📖 سرد — Book Details Page', 'font-size:20px; font-weight:bold; color:#D4A64A;');
    console.log('%cتم تحميل صفحة الكتاب بنجاح!', 'color:#E8C86A;');

});