/* ─── Mobile Navbar Toggle ─── */
var navToggle = document.getElementById('navMobileToggle');
var navLinks = document.querySelector('.nav-premium-links');
if (navToggle && navLinks) {
    navToggle.addEventListener('click', function() {
        navLinks.classList.toggle('is-open');
        navToggle.textContent = navLinks.classList.contains('is-open') ? '✕' : '☰';
    });
}

/* ─── Profile Dropdown ─── */
function toggleProfileMenu(e) {
    if (e) e.stopPropagation();
    var menu = document.getElementById('profileMenu');
    if (menu) menu.classList.toggle('show');
}
document.addEventListener('click', function(e) {
    var toggle = document.querySelector('.profile-toggle');
    var menu = document.getElementById('profileMenu');
    if (toggle && menu && !toggle.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('show');
    }
});

/* ─── Form UI Logic ─── */
const titleInput = document.getElementById('title');
const titleCount = document.getElementById('title-count');
const descInput = document.getElementById('description');
const descCount = document.getElementById('desc-count');
const coverInput = document.getElementById('cover_image');
const fileNameDisplay = document.getElementById('fileNameDisplay');
const form = document.getElementById('novelForm');

titleInput.addEventListener('input', () => titleCount.textContent = titleInput.value.length);
descInput.addEventListener('input', () => descCount.textContent = descInput.value.length);

// File name update
coverInput.addEventListener('change', function() {
    if (this.files && this.files.length > 0) {
        fileNameDisplay.textContent = this.files[0].name;
        document.getElementById('err-cover').textContent = '';
    } else {
        fileNameDisplay.textContent = 'اختر صورة...';
    }
});

// Category Dropdown
const catToggle = document.getElementById('catToggle');
const catMenu = document.getElementById('catMenu');
const catToggleText = document.getElementById('catToggleText');
const catDropdown = document.getElementById('catDropdown');

catToggle.addEventListener('click', () => {
    catMenu.classList.toggle('open');
});

document.addEventListener('click', (e) => {
    if (!catDropdown.contains(e.target)) {
        catMenu.classList.remove('open');
    }
});

catMenu.querySelectorAll('input[type="checkbox"]').forEach(cb => {
    cb.addEventListener('change', () => {
        const checked = catMenu.querySelectorAll('input:checked');
        catToggleText.textContent = checked.length > 0
            ? checked.length + ' تصنيف مختار'
            : 'اختر التصنيفات';
        if (checked.length > 0) {
            document.getElementById('err-cat').textContent = '';
            catToggle.classList.remove('invalid');
        }
    });
});

// Form Validation
form.addEventListener('submit', (e) => {
    let valid = true;

    if (coverInput.files.length === 0) {
        document.getElementById('err-cover').textContent = 'هذا الحقل مطلوب';
        valid = false;
    } else {
        document.getElementById('err-cover').textContent = '';
    }

    if (titleInput.value.trim() === '') {
        document.getElementById('err-title').textContent = 'هذا الحقل مطلوب';
        titleInput.classList.add('invalid');
        valid = false;
    } else {
        document.getElementById('err-title').textContent = '';
        titleInput.classList.remove('invalid');
    }

    if (descInput.value.trim() === '') {
        document.getElementById('err-desc').textContent = 'هذا الحقل مطلوب';
        descInput.classList.add('invalid');
        valid = false;
    } else {
        document.getElementById('err-desc').textContent = '';
        descInput.classList.remove('invalid');
    }

    const checkedCats = catMenu.querySelectorAll('input:checked');
    if (checkedCats.length === 0) {
        document.getElementById('err-cat').textContent = 'هذا الحقل مطلوب';
        catToggle.classList.add('invalid');
        valid = false;
    }

    if (!valid) {
        e.preventDefault();
    }
});

titleInput.addEventListener('input', () => {
    if (titleInput.value.trim() !== '') {
        document.getElementById('err-title').textContent = '';
        titleInput.classList.remove('invalid');
    }
});

descInput.addEventListener('input', () => {
    if (descInput.value.trim() !== '') {
        document.getElementById('err-desc').textContent = '';
        descInput.classList.remove('invalid');
    }
});

coverInput.addEventListener('change', () => {
    if (coverInput.files.length > 0) {
        document.getElementById('err-cover').textContent = '';
    }
});