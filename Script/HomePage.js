(function() {
    const modal = document.getElementById('bookModal');
    const closeBtn = document.getElementById('modalClose');
    const bookButtons = document.querySelectorAll('.book-btn');

    // Modal elements
    const modalCover = document.getElementById('modalCoverImg');
    const modalTitle = document.getElementById('modalTitle');
    const modalAuthor = document.getElementById('modalAuthor');
    const modalMeta = document.getElementById('modalMeta');
    const modalDesc = document.getElementById('modalDesc');

    // ============================================================
    // BOOK DATA — All 32 books with their metadata
    // ============================================================
    const books = [
        // Row 1 - Books 1-16
        {
            id: 1,
            title: "اللص والكلاب",
            author: "نجيب محفوظ",
            cover: "../images/نجيب-محفوظ-اللص و الكلاب(2).png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٦١",
            pages: "١٤٤",
            description: "تدور أحداث الرواية حول سعيد مهران بعد خروجه من السجن، وسعيه للانتقام ممن خانوه، في عمل يعكس صراع الإنسان مع المجتمع ومع ذاته. من أبرز أعمال نجيب محفوظ الفلسفية."
        },
        {
            id: 2,
            title: "أولاد الناس",
            author: "نجيب محفوظ",
            cover: "../images/اولاد الناس.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٦٢",
            pages: "٢٠٠",
            description: "رواية عن الصراع الطبقي والبحث عن الهوية في المجتمع المصري."
        },
        {
            id: 3,
            title: "ثرثرة فوق النيل",
            author: "نجيب محفوظ",
            cover: "../images/ثرثرة فوق النيل .png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٦٦",
            pages: "١٦٠",
            description: "رواية نقدية ساخرة تصور حالة التيه والضياع التي تعيشها الشخصيات على متن مركب في النيل."
        },
        {
            id: 4,
            title: "طبيب أرياف",
            author: "توفيق الحكيم",
            cover: "../images/طبيب ارياف.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٣٦",
            pages: "١٢٠",
            description: "رواية تصور حياة طبيب في الريف المصري وتناقضات المجتمع."
        },
        {
            id: 5,
            title: "ماجدولين",
            author: "مي زيادة",
            cover: "../images/ماجدولين.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٢٥",
            pages: "١٥٠",
            description: "رواية رومانسية عن الحب والتضحية في المجتمع العربي."
        },
        {
            id: 6,
            title: "إيكادولي",
            author: "توفيق الحكيم",
            cover: "../images/ايكادولي.png",
            genre: "مسرحية",
            category: "أدب عربي",
            year: "١٩٤٢",
            pages: "١٨٠",
            description: "مسرحية فلسفية تستكشف العلاقة بين الإنسان والسلطة."
        },
        {
            id: 7,
            title: "شجرتي",
            author: "محمود درويش",
            cover: "../images/شجرتي.png",
            genre: "شعر",
            category: "شعر عربي",
            year: "١٩٦٤",
            pages: "٨٠",
            description: "ديوان شعري يتناول قضية الوطن والانتماء."
        },
        {
            id: 8,
            title: "بداية ونهاية",
            author: "نجيب محفوظ",
            cover: "../images/نجيب-محفوظ-اللص و الكلاب(2).png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٤٩",
            pages: "٢٢٠",
            description: "رواية عن أسرة مصرية تواجه تحديات الحياة في فترة ما بعد الحرب."
        },
        {
            id: 9,
            title: "الثلاثية",
            author: "نجيب محفوظ",
            cover: "../images/اولاد الناس.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٥٦",
            pages: "٥٠٠",
            description: "ثلاثية تصور حياة أسرة في القاهرة خلال فترة الثورة."
        },
        {
            id: 10,
            title: "الحرافيش",
            author: "نجيب محفوظ",
            cover: "../images/ثرثرة فوق النيل .png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٧٧",
            pages: "٢٤٠",
            description: "رواية عن حياة البسطاء في حارة شعبية وحكاياتهم."
        },
        {
            id: 11,
            title: "مرمر زماني",
            author: "علي أحمد باكثير",
            cover: "../images/طبيب ارياف.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٥٠",
            pages: "١٦٠",
            description: "رواية تاريخية عن أحداث في التاريخ العربي."
        },
        {
            id: 12,
            title: "رجال في الشمس",
            author: "غسان كنفاني",
            cover: "../images/ماجدولين.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٦٣",
            pages: "١٣٠",
            description: "رواية عن معاناة الفلسطينيين وهجرتهم بحثًا عن حياة أفضل."
        },
        {
            id: 13,
            title: "عائد إلى حيفا",
            author: "غسان كنفاني",
            cover: "../images/ايكادولي.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٦٩",
            pages: "١٤٠",
            description: "رواية عن العودة والذاكرة والهوية الفلسطينية."
        },
        {
            id: 14,
            title: "موسم الهجرة",
            author: "الطيب صالح",
            cover: "../images/شجرتي.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٦٦",
            pages: "١٧٠",
            description: "رواية عن الصراع بين الشرق والغرب والبحث عن الذات."
        },
        {
            id: 15,
            title: "زقاق المدق",
            author: "نجيب محفوظ",
            cover: "../images/نجيب-محفوظ-اللص و الكلاب(2).png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٤٧",
            pages: "١٩٠",
            description: "رواية عن حياة الناس في زقاق شعبي بالقاهرة."
        },
        {
            id: 16,
            title: "السمان والخريف",
            author: "نجيب محفوظ",
            cover: "../images/اولاد الناس.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٦٢",
            pages: "١٤٠",
            description: "رواية عن التحولات الاجتماعية في مصر بعد الثورة."
        },
        // Row 2 - Books 17-32
        {
            id: 17,
            title: "أولاد الناس (الجزء الثاني)",
            author: "نجيب محفوظ",
            cover: "../images/اولاد الناس.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٦٢",
            pages: "٢٠٠",
            description: "رواية عن الصراع الطبقي والبحث عن الهوية في المجتمع المصري."
        },
        {
            id: 18,
            title: "إيكادولي (الجزء الثاني)",
            author: "توفيق الحكيم",
            cover: "../images/ايكادولي.png",
            genre: "مسرحية",
            category: "أدب عربي",
            year: "١٩٤٢",
            pages: "١٨٠",
            description: "مسرحية فلسفية تستكشف العلاقة بين الإنسان والسلطة."
        },
        {
            id: 19,
            title: "شجرتي (الجزء الثاني)",
            author: "محمود درويش",
            cover: "../images/شجرتي.png",
            genre: "شعر",
            category: "شعر عربي",
            year: "١٩٦٤",
            pages: "٨٠",
            description: "ديوان شعري يتناول قضية الوطن والانتماء."
        },
        {
            id: 20,
            title: "اللص والكلاب (الجزء الثاني)",
            author: "نجيب محفوظ",
            cover: "../images/نجيب-محفوظ-اللص و الكلاب(2).png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٦١",
            pages: "١٤٤",
            description: "تدور أحداث الرواية حول سعيد مهران بعد خروجه من السجن، وسعيه للانتقام ممن خانوه."
        },
        {
            id: 21,
            title: "ثرثرة فوق النيل (الجزء الثاني)",
            author: "نجيب محفوظ",
            cover: "../images/ثرثرة فوق النيل .png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٦٦",
            pages: "١٦٠",
            description: "رواية نقدية ساخرة تصور حالة التيه والضياع التي تعيشها الشخصيات."
        },
        {
            id: 22,
            title: "طبيب أرياف (الجزء الثاني)",
            author: "توفيق الحكيم",
            cover: "../images/طبيب ارياف.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٣٦",
            pages: "١٢٠",
            description: "رواية تصور حياة طبيب في الريف المصري وتناقضات المجتمع."
        },
        {
            id: 23,
            title: "ماجدولين (الجزء الثاني)",
            author: "مي زيادة",
            cover: "../images/ماجدولين.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٢٥",
            pages: "١٥٠",
            description: "رواية رومانسية عن الحب والتضحية في المجتمع العربي."
        },
        {
            id: 24,
            title: "إيكادولي (الجزء الثالث)",
            author: "توفيق الحكيم",
            cover: "../images/ايكادولي.png",
            genre: "مسرحية",
            category: "أدب عربي",
            year: "١٩٤٢",
            pages: "١٨٠",
            description: "مسرحية فلسفية تستكشف العلاقة بين الإنسان والسلطة."
        },
        {
            id: 25,
            title: "أولاد الناس (الجزء الثالث)",
            author: "نجيب محفوظ",
            cover: "../images/اولاد الناس.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٦٢",
            pages: "٢٠٠",
            description: "رواية عن الصراع الطبقي والبحث عن الهوية في المجتمع المصري."
        },
        {
            id: 26,
            title: "شجرتي (الجزء الثالث)",
            author: "محمود درويش",
            cover: "../images/شجرتي.png",
            genre: "شعر",
            category: "شعر عربي",
            year: "١٩٦٤",
            pages: "٨٠",
            description: "ديوان شعري يتناول قضية الوطن والانتماء."
        },
        {
            id: 27,
            title: "اللص والكلاب (الجزء الثالث)",
            author: "نجيب محفوظ",
            cover: "../images/نجيب-محفوظ-اللص و الكلاب(2).png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٦١",
            pages: "١٤٤",
            description: "تدور أحداث الرواية حول سعيد مهران بعد خروجه من السجن."
        },
        {
            id: 28,
            title: "ثرثرة فوق النيل (الجزء الثالث)",
            author: "نجيب محفوظ",
            cover: "../images/ثرثرة فوق النيل .png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٦٦",
            pages: "١٦٠",
            description: "رواية نقدية ساخرة تصور حالة التيه والضياع."
        },
        {
            id: 29,
            title: "طبيب أرياف (الجزء الثالث)",
            author: "توفيق الحكيم",
            cover: "../images/طبيب ارياف.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٣٦",
            pages: "١٢٠",
            description: "رواية تصور حياة طبيب في الريف المصري."
        },
        {
            id: 30,
            title: "ماجدولين (الجزء الثالث)",
            author: "مي زيادة",
            cover: "../images/ماجدولين.png",
            genre: "رواية",
            category: "أدب عربي",
            year: "١٩٢٥",
            pages: "١٥٠",
            description: "رواية رومانسية عن الحب والتضحية."
        },
        {
            id: 31,
            title: "إيكادولي (الجزء الرابع)",
            author: "توفيق الحكيم",
            cover: "../images/ايكادولي.png",
            genre: "مسرحية",
            category: "أدب عربي",
            year: "١٩٤٢",
            pages: "١٨٠",
            description: "مسرحية فلسفية تستكشف العلاقة بين الإنسان والسلطة."
        },
        {
            id: 32,
            title: "شجرتي (الجزء الرابع)",
            author: "محمود درويش",
            cover: "../images/شجرتي.png",
            genre: "شعر",
            category: "شعر عربي",
            year: "١٩٦٤",
            pages: "٨٠",
            description: "ديوان شعري يتناول قضية الوطن والانتماء."
        }
    ];

    // Variable to store current book ID for the read now button
    let currentBookId = null;

    // ============================================================
    // OPEN MODAL FUNCTION
    // ============================================================
    function openModal(bookId) {
        // Store the current book ID
        currentBookId = bookId;

        // Find the book by ID
        const book = books.find(b => b.id === parseInt(bookId));
        if (!book) return;

        // Update modal content
        modalTitle.textContent = book.title;
        modalAuthor.textContent = book.author;
        modalDesc.textContent = book.description;

        // Update cover image
        modalCover.src = book.cover;
        modalCover.alt = book.title;

        // Update meta tags
        modalMeta.innerHTML = `
            <span>${book.genre}</span>
            <span>${book.category}</span>
            <span>${book.year}</span>
            <span>${book.pages} صفحة</span>
        `;

        // Open modal
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    // ============================================================
    // CLOSE MODAL FUNCTION
    // ============================================================
    function closeModal() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }

    // ============================================================
    // READ NOW BUTTON — Navigate to reading page
    // ============================================================
    const readNowBtn = document.getElementById('readNowBtn');

    if (readNowBtn) {
        readNowBtn.addEventListener('click', function() {
            // Get the current book data from the modal
            const title = document.getElementById('modalTitle').textContent;
            const author = document.getElementById('modalAuthor').textContent;
            const cover = document.getElementById('modalCoverImg').src;

            // Close the modal
            closeModal();

            // Navigate to reading page with book data
            setTimeout(function() {
                window.location.href = 'reading.php?id=' + encodeURIComponent(currentBookId) +
                    '&title=' + encodeURIComponent(title) +
                    '&author=' + encodeURIComponent(author) +
                    '&cover=' + encodeURIComponent(cover);
            }, 300);
        });
    }

    // ============================================================
    // EVENT LISTENERS
    // ============================================================
    // Book buttons
    bookButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const bookId = this.dataset.book;
            openModal(bookId);
        });
    });

    // Close button
    closeBtn.addEventListener('click', closeModal);

    // Click outside modal
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
    });

    // ============================================================
    // NAVBAR SCROLL EFFECT
    // ============================================================
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 60) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // ============================================================
    // CONSOLE LOG
    // ============================================================
    console.log('%c📖 سرد — Dynamic Book Modal Loaded', 'font-size:16px; font-weight:bold; color:#C9A96E;');
    console.log('%c' + books.length + ' books loaded successfully!', 'color:#1D9E75;');

})();