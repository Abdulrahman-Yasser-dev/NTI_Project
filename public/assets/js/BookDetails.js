// ─── bookDetails.js ─────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function() {

    // ===== STICKY NAVBAR =====
    // Already sticky via CSS position: sticky

    // ===== HAMBURGER MENU =====
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');

    if (hamburger && navLinks) {
        hamburger.addEventListener('click', function() {
            navLinks.classList.toggle('open');
        });
    }

    // ===== FAVORITE BUTTON TOGGLE =====
    const favBtn = document.getElementById('favBtn');
    if (favBtn) {
        favBtn.addEventListener('click', function() {
            this.classList.toggle('liked');
            const icon = this.querySelector('i');
            if (this.classList.contains('liked')) {
                icon.className = 'fas fa-heart';
            } else {
                icon.className = 'far fa-heart';
            }
        });
    }

    // ===== SCROLL TO TOP BUTTON =====
    const scrollBtn = document.getElementById('scrollTop');

    window.addEventListener('scroll', function() {
        if (window.scrollY > 400) {
            scrollBtn.classList.add('visible');
        } else {
            scrollBtn.classList.remove('visible');
        }
    });

    if (scrollBtn) {
        scrollBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // ===== SMOOTH SCROLLING =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // ===== RATING STARS INTERACTION =====
    const ratingStars = document.querySelectorAll('#ratingStars i');
    const ratingInput = document.getElementById('ratingInput');

    if (ratingStars.length > 0) {
        let currentRating = parseInt(ratingInput?.value) || 0;

        // Set initial stars based on user's existing rating
        updateStars(currentRating);

        ratingStars.forEach((star, index) => {
            const value = index + 1;
            
            star.addEventListener('click', function() {
                currentRating = value;
                updateStars(value);
                if (ratingInput) {
                    ratingInput.value = value;
                }
            });

            star.addEventListener('mouseenter', function() {
                updateStars(value);
            });

            star.addEventListener('mouseleave', function() {
                updateStars(currentRating);
            });
        });

        function updateStars(value) {
            ratingStars.forEach((s, i) => {
                if (i < value) {
                    s.className = 'fas fa-star';
                } else {
                    s.className = 'far fa-star';
                }
            });
        }
    }

    // ===== COMMENT REPLY TOGGLE =====
    const replyBtns = document.querySelectorAll('.reply-btn');

    replyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const replyBox = this.closest('.comment-body').querySelector('.reply-box');
            if (replyBox) {
                replyBox.style.display = replyBox.style.display === 'none' ? 'flex' : 'none';
                if (replyBox.style.display === 'flex') {
                    replyBox.querySelector('input').focus();
                }
            }
        });
    });

    // ===== LIKE BUTTON =====
    const likeBtns = document.querySelectorAll('.like-btn');

    likeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const countText = this.textContent.trim();
            let num = parseInt(countText) || 0;
            const commentId = this.dataset.commentId;
            
            if (icon.classList.contains('far')) {
                icon.className = 'fas fa-heart';
                icon.style.color = '#C0392B';
                num++;
                this.innerHTML = '<i class="fas fa-heart" style="color:#C0392B"></i> ' + num;
            } else {
                icon.className = 'far fa-heart';
                icon.style.color = '';
                num--;
                this.innerHTML = '<i class="far fa-heart"></i> ' + num;
            }
        });
    });

    // ===== SUBMIT REPLY (existing comments) =====
    const replySubmitBtns = document.querySelectorAll('.reply-box button');
    replySubmitBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.closest('.reply-box').querySelector('input');
            if (input && input.value.trim()) {
                alert('تم إرسال ردك: "' + input.value.trim() + '"');
                input.value = '';
                this.closest('.reply-box').style.display = 'none';
            } else {
                alert('الرجاء كتابة رد قبل الإرسال.');
            }
        });
    });

    document.querySelectorAll('.reply-box input').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const btn = this.closest('.reply-box').querySelector('button');
                if (btn) btn.click();
            }
        });
    });

    // ===== TABLE OF CONTENTS - CHAPTERS EXPAND/COLLAPSE =====
    const expandBtn = document.getElementById('chaptersExpandBtn');
    const expandBtnText = document.getElementById('expandBtnText');
    const expandBtnIcon = document.getElementById('expandBtnIcon');
    const hiddenChapters = document.querySelectorAll('.chapter-row.chapter-hidden');

    if (expandBtn && hiddenChapters.length > 0) {
        let isExpanded = false;

        expandBtn.addEventListener('click', function() {
            isExpanded = !isExpanded;
            
            hiddenChapters.forEach(chapter => {
                if (isExpanded) {
                    chapter.classList.add('show');
                } else {
                    chapter.classList.remove('show');
                }
            });

            if (isExpanded) {
                expandBtnText.textContent = 'إخفاء الفصول';
                expandBtn.classList.add('expanded');
            } else {
                const totalChapters = hiddenChapters.length + 5;
                expandBtnText.textContent = 'عرض جميع الفصول (' + totalChapters + ')';
                expandBtn.classList.remove('expanded');
            }
        });
    }

    // ===== RELATED BOOKS GALLERY =====
    const galleryContainer = document.getElementById('galleryContainer');
    const prevArrow = document.getElementById('prevArrow');
    const nextArrow = document.getElementById('nextArrow');

    const galleryItems = galleryContainer ? galleryContainer.querySelectorAll('.gallery-item') : [];
    const totalBooks = galleryItems.length;
    const booksPerPage = 4;
    let currentStartIndex = 0;

    function renderGallery(startIndex) {
        if (!galleryContainer) return;

        galleryItems.forEach((item, index) => {
            if (index >= startIndex && index < startIndex + booksPerPage) {
                item.style.display = '';
                item.style.animation = 'fadeIn 0.4s ease';
            } else {
                item.style.display = 'none';
            }
        });

        if (prevArrow) {
            prevArrow.disabled = startIndex === 0;
        }
        if (nextArrow) {
            nextArrow.disabled = startIndex + booksPerPage >= totalBooks;
        }
    }

    if (prevArrow) {
        prevArrow.addEventListener('click', function() {
            if (currentStartIndex > 0) {
                currentStartIndex--;
                renderGallery(currentStartIndex);
            }
        });
    }

    if (nextArrow) {
        nextArrow.addEventListener('click', function() {
            if (currentStartIndex + booksPerPage < totalBooks) {
                currentStartIndex++;
                renderGallery(currentStartIndex);
            }
        });
    }

    if (galleryContainer && galleryItems.length > 0) {
        renderGallery(0);
    }

    // ===== LAZY LOADING FOR IMAGES =====
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    }

    // ===== MORE BUTTONS =====
    const moreBtns = document.querySelectorAll('.more-btn');

    moreBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const parent = this.closest('.card');
            if (parent) {
                const list = parent.querySelector('ul') || parent.querySelector('.author-list');
                if (list) {
                    const items = list.querySelectorAll('.category-item, .author-item');
                    let hiddenCount = 0;
                    items.forEach(item => {
                        if (window.getComputedStyle(item).display === 'none') {
                            item.style.display = 'flex';
                            hiddenCount++;
                        }
                    });
                    if (hiddenCount === 0) {
                        items.forEach((item, index) => {
                            if (index > 5) {
                                item.style.display = 'none';
                            }
                        });
                    }
                    const currentText = this.textContent.trim();
                    if (currentText.includes('المزيد')) {
                        this.textContent = 'عرض أقل';
                    } else {
                        this.textContent = 'المزيد من ' + (parent.querySelector('h3')?.textContent?.trim() || '');
                    }
                }
            }
        });
    });

    document.querySelectorAll('.category-item, .author-item').forEach((item, index) => {
        if (index > 5) {
            item.style.display = 'none';
        }
    });

    // ===== BUTTON ACTIONS =====
    const readBtn = document.querySelector('.btn-primary');
    if (readBtn && readBtn.textContent.trim().includes('قراءة')) {
        readBtn.addEventListener('click', function(e) {});
    }

    const downloadBtn = document.querySelector('.btn-secondary');
    if (downloadBtn && downloadBtn.textContent.trim().includes('تحميل')) {
        downloadBtn.addEventListener('click', function() {
            alert('سيبدأ تحميل الكتاب بصيغة PDF. شكراً لاستخدامك سرد!');
        });
    }

    const searchBtn = document.querySelector('.search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            if (typeof ROOT_URL !== 'undefined') {
                window.location.href = ROOT_URL + 'Browsebooks';
            } else {
                window.location.href = '/NTI_Project/Browsebooks';
            }
        });
    }

    const loginBtn = document.querySelector('.login-btn');
    if (loginBtn) {
        loginBtn.addEventListener('click', function() {
            if (typeof ROOT_URL !== 'undefined') {
                window.location.href = ROOT_URL + 'signup';
            } else {
                window.location.href = '/NTI_Project/signup';
            }
        });
    }

    const registerBtn = document.querySelector('.register-btn');
    if (registerBtn) {
        registerBtn.addEventListener('click', function() {
            if (typeof ROOT_URL !== 'undefined') {
                window.location.href = ROOT_URL + 'signup';
            } else {
                window.location.href = '/NTI_Project/signup';
            }
        });
    }

    console.log('سرد - تفاصيل الكتاب loaded successfully!');
});