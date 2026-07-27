/**
 * HomePage.js — Premium Homepage (Marketing-Focused)
 * سرد (Sard) — Arabic Reading Platform
 */

document.addEventListener('DOMContentLoaded', function() {

    // ============================================================
    // MOBILE MENU TOGGLE
    // ============================================================

    const mobileToggle = document.getElementById('mobileMenuToggle');
    const navLinks = document.querySelector('.nav-links');

    if (mobileToggle && navLinks) {
        mobileToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-times');
            }
        });
    }

    // ============================================================
    // SMOOTH SCROLL
    // ============================================================

    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
                if (navLinks) {
                    navLinks.classList.remove('active');
                }
                const icon = mobileToggle?.querySelector('i');
                if (icon) {
                    icon.classList.add('fa-bars');
                    icon.classList.remove('fa-times');
                }
            }
        });
    });

    // ============================================================
    // CLOSE MOBILE MENU ON RESIZE
    // ============================================================

    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && navLinks) {
            navLinks.classList.remove('active');
            const icon = mobileToggle?.querySelector('i');
            if (icon) {
                icon.classList.add('fa-bars');
                icon.classList.remove('fa-times');
            }
        }
    });

    // ============================================================
    // TESTIMONIALS CAROUSEL
    // ============================================================

    const track = document.getElementById('testimonialsTrack');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.getElementById('prevTestimonial');
    const nextBtn = document.getElementById('nextTestimonial');
    
    if (track && dots.length > 0) {
        let currentIndex = 0;
        let autoSlideInterval = null;
        let isTransitioning = false;
        
        // Get visible cards count based on screen width
        function getVisibleCards() {
            if (window.innerWidth < 768) return 1;
            if (window.innerWidth < 1024) return 2;
            return 3;
        }
        
        // Get card width including gap
        function getCardWidth() {
            const card = track.querySelector('.testimonial-card');
            if (!card) return 0;
            const rect = card.getBoundingClientRect();
            const gap = 28; // match gap from CSS
            return rect.width + gap;
        }
        
        // Update carousel position
        function updateCarousel(index, animate = true) {
            if (isTransitioning) return;
            
            const cards = track.querySelectorAll('.testimonial-card');
            const totalCards = cards.length;
            const visible = getVisibleCards();
            const maxIndex = Math.max(0, totalCards - visible);
            
            // Clamp index
            if (index < 0) index = 0;
            if (index > maxIndex) index = maxIndex;
            
            currentIndex = index;
            
            // Update dots
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
            
            // Move track
            const cardWidth = getCardWidth();
            const offset = index * cardWidth;
            
            if (animate) {
                track.style.transition = 'transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
            } else {
                track.style.transition = 'none';
            }
            
            track.style.transform = `translateX(-${offset}px)`;
            
            // Reset transition after animation
            if (animate) {
                isTransitioning = true;
                setTimeout(() => {
                    isTransitioning = false;
                }, 500);
            }
        }
        
        // Next slide
        function nextSlide() {
            const cards = track.querySelectorAll('.testimonial-card');
            const totalCards = cards.length;
            const visible = getVisibleCards();
            const maxIndex = Math.max(0, totalCards - visible);
            
            if (currentIndex < maxIndex) {
                updateCarousel(currentIndex + 1);
            } else {
                updateCarousel(0);
            }
        }
        
        // Previous slide
        function prevSlide() {
            const cards = track.querySelectorAll('.testimonial-card');
            const totalCards = cards.length;
            const visible = getVisibleCards();
            const maxIndex = Math.max(0, totalCards - visible);
            
            if (currentIndex > 0) {
                updateCarousel(currentIndex - 1);
            } else {
                updateCarousel(maxIndex);
            }
        }
        
        // Start auto-slide
        function startAutoSlide() {
            if (autoSlideInterval) {
                clearInterval(autoSlideInterval);
            }
            autoSlideInterval = setInterval(nextSlide, 5000);
        }
        
        // Stop auto-slide
        function stopAutoSlide() {
            if (autoSlideInterval) {
                clearInterval(autoSlideInterval);
                autoSlideInterval = null;
            }
        }
        
        // Reset auto-slide timer
        function resetAutoSlide() {
            stopAutoSlide();
            startAutoSlide();
        }
        
        // Event listeners for dots
        dots.forEach((dot) => {
            dot.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                const cards = track.querySelectorAll('.testimonial-card');
                const totalCards = cards.length;
                const visible = getVisibleCards();
                const maxIndex = Math.max(0, totalCards - visible);
                
                if (index <= maxIndex) {
                    updateCarousel(index);
                    resetAutoSlide();
                }
            });
        });
        
        // Event listeners for arrows
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                prevSlide();
                resetAutoSlide();
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                nextSlide();
                resetAutoSlide();
            });
        }
        
        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight') {
                prevSlide();
                resetAutoSlide();
            } else if (e.key === 'ArrowLeft') {
                nextSlide();
                resetAutoSlide();
            }
        });
        
        // Pause on hover
        const container = document.querySelector('.testimonials-carousel-container');
        if (container) {
            container.addEventListener('mouseenter', stopAutoSlide);
            container.addEventListener('mouseleave', startAutoSlide);
        }
        
        // Responsive: update on resize
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                const cards = track.querySelectorAll('.testimonial-card');
                const totalCards = cards.length;
                const visible = getVisibleCards();
                const maxIndex = Math.max(0, totalCards - visible);
                
                if (currentIndex > maxIndex) {
                    updateCarousel(maxIndex, false);
                } else {
                    updateCarousel(currentIndex, false);
                }
            }, 250);
        });
        
        // Initialize
        updateCarousel(0, false);
        startAutoSlide();
    }

    // ============================================================
    // CONSOLE
    // ============================================================

    console.log('%c📖 سرد — Premium Homepage', 'font-size:20px; font-weight:bold; color:#C9A96E;');
    console.log('%cتم تحميل الصفحة بنجاح!', 'color:#1D9E75;');

});