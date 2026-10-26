/**
 * LUXE E-Commerce - Main JavaScript
 */

// ============================================================
// NAVBAR SCROLL EFFECT
// ============================================================
window.addEventListener('scroll', function () {
    const navbar = document.getElementById('navbar');
    if (navbar) {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
});

// ============================================================
// MOBILE MENU TOGGLE
// ============================================================
function toggleMobileMenu() {
    const menu = document.getElementById('navMenu');
    if (menu) {
        menu.classList.toggle('active');
    }
}

// ============================================================
// HERO SLIDER
// ============================================================
let currentSlide = 0;
let slideInterval;

function initSlider() {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    if (slides.length === 0) return;

    function showSlide(index) {
        slides.forEach(s => s.classList.remove('active'));
        dots.forEach(d => d.classList.remove('active'));
        currentSlide = index;
        if (currentSlide >= slides.length) currentSlide = 0;
        if (currentSlide < 0) currentSlide = slides.length - 1;
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    // Auto-slide
    slideInterval = setInterval(nextSlide, 5000);

    // Dot click
    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            clearInterval(slideInterval);
            showSlide(i);
            slideInterval = setInterval(nextSlide, 5000);
        });
    });

    showSlide(0);
}

document.addEventListener('DOMContentLoaded', initSlider);

// ============================================================
// QUANTITY SELECTOR
// ============================================================
function updateQuantity(action, inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    let val = parseInt(input.value);
    if (action === 'increase') {
        val++;
    } else if (action === 'decrease' && val > 1) {
        val--;
    }
    input.value = val;
}

// ============================================================
// ADD TO CART (AJAX-style form submit)
// ============================================================
function addToCart(productId, quantity) {
    quantity = quantity || 1;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.siteUrl + '/pages/cart_action.php';

    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'add';
    form.appendChild(actionInput);

    const productInput = document.createElement('input');
    productInput.type = 'hidden';
    productInput.name = 'product_id';
    productInput.value = productId;
    form.appendChild(productInput);

    const qtyInput = document.createElement('input');
    qtyInput.type = 'hidden';
    qtyInput.name = 'quantity';
    qtyInput.value = quantity;
    form.appendChild(qtyInput);

    document.body.appendChild(form);
    form.submit();
}

// ============================================================
// FLASH MESSAGE AUTO-HIDE
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(msg => {
        setTimeout(() => {
            msg.style.opacity = '0';
            msg.style.transform = 'translateY(-10px)';
            setTimeout(() => msg.remove(), 300);
        }, 4000);
    });
});

// ============================================================
// SMOOTH SCROLL
// ============================================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// ============================================================
// FORM VALIDATION HELPERS
// ============================================================
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[0-9]{10}$/;
    return re.test(phone);
}

// ============================================================
// PROMO COUNTDOWN TIMER
// ============================================================
function initCountdown() {
    const timers = document.querySelectorAll('.countdown-timer');
    timers.forEach(timer => {
        // Set end date to 3 days from now
        const endDate = new Date();
        endDate.setDate(endDate.getDate() + 3);

        function updateTimer() {
            const now = new Date().getTime();
            const distance = endDate.getTime() - now;

            if (distance < 0) return;

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const daysEl = timer.querySelector('.days');
            const hoursEl = timer.querySelector('.hours');
            const minutesEl = timer.querySelector('.minutes');
            const secondsEl = timer.querySelector('.seconds');

            if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
            if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
            if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
            if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    });
}

document.addEventListener('DOMContentLoaded', initCountdown);
