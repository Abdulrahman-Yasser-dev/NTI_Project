<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>إدارة الفصول - سرد</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;600;700&family=Literata:ital,opsz,wght@0,7..72,200..900;1,7..72,200..900&family=Noto+Serif+Arabic:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
      body { font-family: "IBM Plex Sans Arabic", sans-serif; background-color: #f8f9ff; }
      .font-headline-md, .font-headline-lg, .font-headline-lg-mobile { font-family: "Noto Serif Arabic", serif; }
      ::-webkit-scrollbar { width: 8px; }
      ::-webkit-scrollbar-track { background: transparent; }
      ::-webkit-scrollbar-thumb { background: #bac6ec; border-radius: 4px; }
      ::-webkit-scrollbar-thumb:hover { background: #98a4c9; }
      .book-cover { aspect-ratio: 2/3; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1), 0 10px 30px -10px rgba(24, 36, 66, 0.3); }
    </style>
    <script>
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              primary: "#182442",
              "primary-container": "#2e3a59",
              "on-primary-container": "#98a4c9",
              secondary: "#605e58",
              tertiary: "#312300",
              "tertiary-container": "#4a380c",
              surface: "#f8f9ff",
              "surface-container": "#e5eeff",
              "surface-container-low": "#eff4ff",
              "surface-container-lowest": "#ffffff",
              "surface-bright": "#ffffff",
              outline: "#75777e",
              "outline-variant": "#c6c6ce",
              error: "#ba1a1a",
            },
            animation: {
              'fade-in-up': 'fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
              'fade-in-left': 'fadeInLeft 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
            },
            keyframes: {
              fadeInUp: {
                '0%': { opacity: '0', transform: 'translateY(20px)' },
                '100%': { opacity: '1', transform: 'translateY(0)' },
              },
              fadeInLeft: {
                '0%': { opacity: '0', transform: 'translateX(20px)' },
                '100%': { opacity: '1', transform: 'translateX(0)' },
              }
            }
          },
        },
      };
    </script>
</head>
<body class="min-h-screen flex flex-col text-[#0b1c30] relative overflow-x-hidden z-0">
    <!-- Background Decoration -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] rounded-full bg-gradient-to-br from-indigo-100/40 to-blue-50/40 blur-3xl"></div>
    </div>

    <!-- TopAppBar -->
    <header class="bg-surface/80 backdrop-blur-lg border-b border-outline-variant/50 shadow-sm w-full sticky top-0 z-50 transition-all">
      <div class="flex flex-row-reverse justify-between items-center px-4 md:px-10 h-16 w-full max-w-[1200px] mx-auto">
        <!-- Brand -->
        <div class="flex items-center gap-2 flex-row-reverse">
          <img alt="رواياتي" class="h-10 w-auto object-contain drop-shadow-sm" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC3n0B3x5jaIgKWss2vxacZ0q1V5clYRXKo1ovC6GSZ9WdR2yByLb17Cy-zBFxoAXj_DP2qrKVKZ9EDEsueFhPxxRF2v81qrRYCxN5-3_VnenoQbIEv0mryprBQGo-9Ot1xe3yYqARv-PRu5Onr7R5rzadtPAsKuqcTm7EqMDjl72bOgHQwXwklJlMZrLLj1ry4j_h_XaJCTlv-bp5kUqhrWfJhe72u9oaicUYO8CTAcfcIMrqfyZ7ZRT0vghUG5KiE5M1iJ6tFTTdx">
          <span class="font-headline-lg text-2xl hidden md:block text-primary font-bold">سرد</span>
        </div>
        <!-- Navigation Links -->
        <nav class="hidden md:flex flex-row-reverse items-center gap-8">
          <a class="text-[#45464e] font-semibold text-sm hover:text-primary transition-colors" href="HomePage.php">الرئيسية</a>
          <a class="text-[#45464e] font-semibold text-sm hover:text-primary transition-colors" href="Browsebooks.php">استكشف</a>
          <a class="text-[#45464e] font-semibold text-sm hover:text-primary transition-colors" href="#">مكتبتي</a>
          <a class="text-primary font-bold border-b-2 border-primary pb-1 text-sm transition-all" href="#">الكتابة</a>
        </nav>
        <!-- Trailing Actions -->
        <div class="flex flex-row-reverse items-center gap-3">
          <button class="text-[#45464e] hover:text-primary hover:bg-surface-container transition-all p-2 rounded-full flex items-center justify-center relative">
            <span class="material-symbols-outlined">notifications</span>
          </button>
          <button class="text-[#45464e] hover:text-primary hover:bg-surface-container transition-all p-2 rounded-full flex items-center justify-center">
            <span class="material-symbols-outlined">search</span>
          </button>
          <div class="h-9 w-9 rounded-full overflow-hidden border-2 border-outline-variant/30 hover:border-primary transition-colors cursor-pointer mr-2 shadow-sm">
            <img alt="صورة المستخدم" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC1SH_Drj6XumELea1xO9c6Xu97viLHAuQdJXPZ1yQcq5j0j2C-Nsbl8_2vmRMZI-oRNivqyMgP_w5EPti65ONXLY0OZaORPbCtzJHM0SDGyYKeAFromutlAGxylYG3UMK_qt9HFsYosxIrtWQFJ2U1lY4d3qoxiJDSyROjD12rLZ0mPaMoqsmvaMDDiQLrBP3eWR39UKno3BY2X4HkcbVmRFjrWzvVmAt6t3W7YggrBnqhtphW3JkpDn38RwM26nsdFuiaVd64aVLs">
          </div>
          <button class="md:hidden text-primary p-2 flex items-center justify-center hover:bg-surface-container rounded-full">
            <span class="material-symbols-outlined">menu</span>
          </button>
        </div>
      </div>
    </header>

    <main class="flex-grow w-full max-w-[1200px] mx-auto px-4 md:px-10 py-10 flex flex-col items-center">
        <!-- Page Header -->
        <div class="w-full max-w-5xl text-right mb-10 opacity-0 animate-fade-in-up" style="animation-delay: 0.1s;">
            <h1 class="font-headline-lg text-3xl md:text-4xl text-primary mb-2 drop-shadow-sm font-bold">إدارة الفصول</h1>
            <p class="text-[#45464e] text-base">قم بإضافة فصول جديدة أو تعديل الفصول الحالية لروايتك.</p>
        </div>

        <div class="w-full max-w-5xl flex flex-col md:flex-row-reverse gap-8 lg:gap-12">
            <!-- Left Column: Novel Summary Card -->
            <aside class="w-full md:w-1/3 flex flex-col opacity-0 animate-fade-in-left" style="animation-delay: 0.2s;">
                <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-outline-variant/30 flex flex-col items-center text-center sticky top-[100px] transition-all hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
                    <!-- Cover Image -->
                    <div class="w-full max-w-[180px] book-cover rounded-lg overflow-hidden mb-5 relative bg-surface-variant group cursor-pointer">
                        <img alt="غلاف رواية ظلال الماضي" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCoYgpJ4ZHMmFIVzlsmUEm6pssKLVOyV9HAzJ8PVnzWS9oogDRHYfMSui41dEUXizaQ41dJlEm9O3yVOkHVTI5PLRFEU6t5ylY2nDFdIWXapLc2cHf5JTylKmo6N4534F7gDIybFhMPRvbZtwmRV2VmAPnzGLq7FBBN5EGHAO5QULTzF6G2PVUNNWbcmuu6AgJw20ssHx1MTHgbPA0_x54Wp7yDHyCbRx6ulpQcFtbgvc9bIJBza08atcz8G_FSSMJWH5IRbMsLmsj0">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300"></div>
                    </div>
                    <!-- Novel Info -->
                    <h2 class="font-headline-md text-2xl text-primary mb-1 font-bold">ظلال الماضي</h2>
                    <p class="text-xs text-[#45464e] mb-5 bg-surface px-3 py-1 rounded-full border border-outline-variant/30">دراما، غموض • 4 فصول منشورة</p>
                    
                    <!-- Progress Stats -->
                    <div class="w-full bg-surface/50 border border-outline-variant/30 rounded-xl p-4 mb-6 text-right">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-semibold text-[#45464e]">التقدم الإجمالي</span>
                            <span class="text-sm font-bold text-primary">12,450 كلمة</span>
                        </div>
                        <div class="w-full h-1.5 bg-outline-variant/30 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-l from-primary to-primary-container w-1/3 rounded-full"></div>
                        </div>
                    </div>

                    <!-- CTA Buttons -->
                    <button class="w-full bg-gradient-to-r from-[#4a380c] to-[#604911] text-white hover:shadow-lg hover:shadow-[#4a380c]/20 hover:-translate-y-0.5 transition-all duration-300 rounded-lg py-3 px-4 flex items-center justify-center gap-2 font-semibold text-sm active:scale-95">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        إضافة الفصل #5
                    </button>
                    <button class="w-full mt-3 bg-transparent text-[#45464e] border border-outline-variant/60 hover:bg-surface hover:text-primary hover:border-primary/50 transition-all duration-300 rounded-lg py-2.5 px-4 flex items-center justify-center gap-2 font-semibold text-sm">
                        <span class="material-symbols-outlined text-[18px]">settings</span>
                        إعدادات الرواية
                    </button>
                </div>
            </aside>

            <!-- Right Column: Chapters List -->
            <section class="w-full md:w-2/3 flex flex-col gap-4 opacity-0 animate-fade-in-up" style="animation-delay: 0.3s;">
                <div class="flex justify-between items-center mb-3 border-b border-outline-variant/40 pb-3">
                    <h3 class="font-headline-md text-2xl text-primary font-bold">الفصول الحالية</h3>
                    <div class="flex gap-2">
                        <button class="text-[#45464e] hover:text-primary hover:bg-surface-container p-2 rounded-full transition-all" title="ترتيب">
                            <span class="material-symbols-outlined">sort</span>
                        </button>
                    </div>
                </div>

                <!-- Add Chapter Card -->
                <div class="bg-surface/30 rounded-xl p-5 border-2 border-dashed border-primary/30 flex flex-row-reverse justify-center items-center hover:bg-primary/5 hover:border-primary/50 transition-all duration-300 group cursor-pointer gap-3 hover:-translate-y-0.5">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                        <span class="material-symbols-outlined text-primary">add</span>
                    </div>
                    <span class="font-bold text-sm text-primary">إضافة فصل جديد</span>
                </div>

                <!-- Chapter Items -->
                <div class="bg-surface-container-lowest rounded-xl p-4 shadow-sm border border-outline-variant/40 flex flex-row-reverse justify-between items-center hover:shadow-md hover:border-primary/30 transition-all duration-300 group cursor-pointer hover:-translate-y-0.5">
                    <div class="flex flex-row-reverse items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-surface-container flex items-center justify-center text-primary font-headline-md text-xl shrink-0 group-hover:bg-primary group-hover:text-white transition-colors duration-300">1</div>
                        <div class="text-right">
                            <h4 class="font-bold text-sm text-[#0b1c30] mb-1.5 group-hover:text-primary transition-colors">البداية الغامضة</h4>
                            <div class="flex gap-4 text-[#45464e] text-xs">
                                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">visibility</span> 1.2k</span>
                                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">calendar_today</span> 12 أكتوبر</span>
                                <span class="font-medium bg-surface px-2 py-0.5 rounded text-[10px]">3,200 كلمة</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity flex-row-reverse translate-x-2 group-hover:translate-x-0 duration-300">
                        <button class="text-[#45464e] hover:text-primary hover:bg-primary/10 p-2 rounded-full transition-colors" title="تعديل"><span class="material-symbols-outlined text-[20px]">edit</span></button>
                    </div>
                </div>

                <div class="bg-surface-container-lowest rounded-xl p-4 shadow-sm border border-outline-variant/40 flex flex-row-reverse justify-between items-center hover:shadow-md hover:border-primary/30 transition-all duration-300 group cursor-pointer hover:-translate-y-0.5">
                    <div class="flex flex-row-reverse items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-surface-container flex items-center justify-center text-primary font-headline-md text-xl shrink-0 group-hover:bg-primary group-hover:text-white transition-colors duration-300">2</div>
                        <div class="text-right">
                            <h4 class="font-bold text-sm text-[#0b1c30] mb-1.5 group-hover:text-primary transition-colors">الرسالة المفقودة</h4>
                            <div class="flex gap-4 text-[#45464e] text-xs">
                                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">visibility</span> 950</span>
                                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">calendar_today</span> 19 أكتوبر</span>
                                <span class="font-medium bg-surface px-2 py-0.5 rounded text-[10px]">2,850 كلمة</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity flex-row-reverse translate-x-2 group-hover:translate-x-0 duration-300">
                        <button class="text-[#45464e] hover:text-primary hover:bg-primary/10 p-2 rounded-full transition-colors" title="تعديل"><span class="material-symbols-outlined text-[20px]">edit</span></button>
                    </div>
                </div>

                <div class="bg-surface-container-lowest rounded-xl p-4 shadow-sm border border-outline-variant/40 flex flex-row-reverse justify-between items-center hover:shadow-md hover:border-primary/30 transition-all duration-300 group cursor-pointer hover:-translate-y-0.5">
                    <div class="flex flex-row-reverse items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-surface-container flex items-center justify-center text-primary font-headline-md text-xl shrink-0 group-hover:bg-primary group-hover:text-white transition-colors duration-300">3</div>
                        <div class="text-right">
                            <h4 class="font-bold text-sm text-[#0b1c30] mb-1.5 group-hover:text-primary transition-colors">لقاء في المقهى القديم</h4>
                            <div class="flex gap-4 text-[#45464e] text-xs">
                                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">visibility</span> 820</span>
                                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">calendar_today</span> 26 أكتوبر</span>
                                <span class="font-medium bg-surface px-2 py-0.5 rounded text-[10px]">3,100 كلمة</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity flex-row-reverse translate-x-2 group-hover:translate-x-0 duration-300">
                        <button class="text-[#45464e] hover:text-primary hover:bg-primary/10 p-2 rounded-full transition-colors" title="تعديل"><span class="material-symbols-outlined text-[20px]">edit</span></button>
                    </div>
                </div>

                <!-- Draft Item -->
                <div class="bg-surface/50 rounded-xl p-4 shadow-sm border border-dashed border-outline-variant flex flex-row-reverse justify-between items-center hover:shadow-md hover:bg-surface-container-lowest transition-all duration-300 group cursor-pointer hover:-translate-y-0.5">
                    <div class="flex flex-row-reverse items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-transparent border-2 border-outline-variant/50 flex items-center justify-center text-outline font-headline-md text-xl shrink-0 group-hover:border-primary/50 group-hover:text-primary transition-colors">4</div>
                        <div class="text-right">
                            <div class="flex items-center gap-2 flex-row-reverse mb-1.5">
                                <h4 class="font-bold text-sm text-[#0b1c30] group-hover:text-primary transition-colors">ذكريات مشوشة</h4>
                                <span class="bg-orange-100 text-orange-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-orange-200">مسودة</span>
                            </div>
                            <div class="flex gap-4 text-[#45464e] text-xs">
                                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">edit_note</span> آخر تعديل اليوم</span>
                                <span class="font-medium bg-surface px-2 py-0.5 rounded text-[10px] border border-outline-variant/20">3,300 كلمة</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-row-reverse">
                        <button class="text-primary hover:text-white hover:bg-primary font-semibold text-xs px-4 py-2 rounded-lg border border-primary/30 transition-all duration-300">متابعة الكتابة</button>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-surface/50 border-t border-outline-variant/50 mt-auto backdrop-blur-sm">
        <div class="flex flex-col md:flex-row-reverse justify-between items-center gap-6 py-8 px-4 md:px-10 w-full max-w-[1200px] mx-auto">
            <div class="flex items-center flex-row-reverse gap-2">
                <span class="font-headline-md text-xl text-primary font-bold">سرد</span>
            </div>
            <nav class="flex flex-wrap justify-center flex-row-reverse gap-6">
                <a class="text-xs font-semibold text-[#45464e] hover:text-primary transition-all" href="#">التصنيفات</a>
                <a class="text-xs font-semibold text-[#45464e] hover:text-primary transition-all" href="#">الشروط والأحكام</a>
                <a class="text-xs font-semibold text-[#45464e] hover:text-primary transition-all" href="#">سياسة الخصوصية</a>
                <a class="text-xs font-semibold text-[#45464e] hover:text-primary transition-all" href="#">تواصل معنا</a>
            </nav>
            <div class="text-xs text-outline text-center md:text-right">
                © ٢٠٢٤ سرد. جميع الحقوق محفوظة.
            </div>
        </div>
    </footer>
</body>
</html>