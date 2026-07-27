<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>محرر الرواية - سرد</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;600;700&family=Literata:ital,opsz,wght@0,7..72,200..900;1,7..72,200..900&family=Noto+Serif+Arabic:wght@400;600;700;800&display=swap" rel="stylesheet"/>
    <style>
      body { font-family: "IBM Plex Sans Arabic", sans-serif; }
      .font-headline-md, .font-headline-lg { font-family: "Noto Serif Arabic", serif; }
      .font-body-reading { font-family: "Literata", serif; }
      ::-webkit-scrollbar { width: 6px; }
      ::-webkit-scrollbar-track { background: transparent; }
      ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
      ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

      .glass-panel {
          background: rgba(255, 255, 255, 0.75);
          backdrop-filter: blur(16px);
          -webkit-backdrop-filter: blur(16px);
          border: 1px solid rgba(255, 255, 255, 0.4);
      }
      .editor-content:empty:before {
          content: attr(data-placeholder);
          color: #94a3b8;
          pointer-events: none;
          display: block;
      }
      .parchment-bg {
          background-color: #fdfbf7;
          background-image: url('data:image/svg+xml;utf8,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><filter id="noise"><feTurbulence type="fractalNoise" baseFrequency="0.8" numOctaves="4" stitchTiles="stitch"/></filter><rect width="100" height="100" filter="url(%23noise)" opacity="0.02"/></svg>');
      }
      
      [contenteditable]:focus { outline: none; }
    </style>
    <script>
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              primary: "#182442",
              "primary-container": "#2e3a59",
              secondary: "#605e58",
              "accent-orange": "#d97706",
              surface: "#f8f9ff",
              "surface-container": "#e5eeff",
              "surface-container-low": "#eff4ff",
              "surface-container-lowest": "#ffffff",
              "surface-container-highest": "#d3e4fe",
              outline: "#75777e",
              "outline-variant": "#c6c6ce",
            },
            animation: {
              'fade-in': 'fadeIn 0.5s ease-out forwards',
              'slide-up': 'slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
            },
            keyframes: {
              fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
              slideUp: {
                '0%': { opacity: '0', transform: 'translate(-50%, 20px)' },
                '100%': { opacity: '1', transform: 'translate(-50%, 0)' },
              }
            }
          },
        },
      };
    </script>
</head>
<body class="bg-surface text-[#0b1c30] h-screen flex flex-col overflow-hidden z-0">
    <!-- TopAppBar -->
    <header class="bg-white/80 backdrop-blur-md shadow-sm border-b border-outline-variant/40 sticky top-0 z-50">
        <div class="flex flex-row-reverse justify-between items-center px-6 h-16 w-full mx-auto">
            <!-- Trailing Actions & Profile -->
            <div class="flex items-center gap-4">
                <button class="text-[#45464e] hover:text-primary hover:bg-surface-container transition-all p-2 rounded-full">
                    <span class="material-symbols-outlined text-[22px]">notifications</span>
                </button>
                <div class="w-8 h-8 rounded-full overflow-hidden border-2 border-outline-variant/30 hover:border-primary transition-colors cursor-pointer shadow-sm">
                    <img alt="صورة المستخدم" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCYP12sr_eYqSBDRBrcNB6Z6PlIFyDKysKUSDtWAHIVJ5kS0esZgSTU_jpNZG5wBw4fwLRbo6GtrTEdir5o4vXlcvs1iTwDTkrbXPoZ1-P8zmSoZR2lGSqpHSqLR4WSisL0k67IM6LqQtSlySJAlAZu3kd8aRxjF6OmlRXX-aBzinFL__m03IVc6mq3OIuNcCmnVA71ga4nu2nnh8YRv7uLCV2Ub4PVl00vXrVPYi4gNXTDPbyqTa5xMv9OAoNOuNFprBSwzntr92Gj"/>
                </div>
            </div>
            <!-- Navigation -->
            <nav class="hidden md:flex gap-8 flex-row-reverse items-center absolute left-1/2 -translate-x-1/2">
                <a class="text-[#45464e] font-semibold text-sm hover:text-primary transition-colors" href="HomePage.php">الرئيسية</a>
                <a class="text-[#45464e] font-semibold text-sm hover:text-primary transition-colors" href="Browsebooks.php">استكشف</a>
                <a class="text-[#45464e] font-semibold text-sm hover:text-primary transition-colors" href="#">مكتبتي</a>
                <a class="text-primary font-bold text-sm border-b-2 border-primary py-5" href="#">الكتابة</a>
            </nav>
            <!-- Brand Logo -->
            <div class="flex items-center gap-2">
                <img alt="رواياتي" class="h-9 object-contain drop-shadow-sm" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC3n0B3x5jaIgKWss2vxacZ0q1V5clYRXKo1ovC6GSZ9WdR2yByLb17Cy-zBFxoAXj_DP2qrKVKZ9EDEsueFhPxxRF2v81qrRYCxN5-3_VnenoQbIEv0mryprBQGo-9Ot1xe3yYqARv-PRu5Onr7R5rzadtPAsKuqcTm7EqMDjl72bOgHQwXwklJlMZrLLj1ry4j_h_XaJCTlv-bp5kUqhrWfJhe72u9oaicUYO8CTAcfcIMrqfyZ7ZRT0vghUG5KiE5M1iJ6tFTTdx"/>
                <span class="font-headline-lg text-xl font-bold text-primary hidden lg:block">سرد</span>
            </div>
        </div>
    </header>

    <!-- Main Editor Layout -->
    <main class="flex-1 flex overflow-hidden opacity-0 animate-fade-in" style="animation-delay: 0.1s;">
        <!-- Side Panel: Metadata -->
        <aside class="w-72 bg-white/60 border-l border-outline-variant/40 flex flex-col p-6 overflow-y-auto hidden md:flex shrink-0 shadow-[inset_-4px_0_15px_-10px_rgba(0,0,0,0.05)] z-10">
            <h2 class="font-headline-md text-xl text-primary font-bold mb-8">تفاصيل الفصل</h2>
            <div class="mb-7 group">
                <label class="block text-xs font-bold text-primary mb-2 transition-colors">عنوان الفصل</label>
                <input class="w-full bg-transparent border-b border-outline-variant/60 text-[#0b1c30] text-sm focus:border-primary focus:border-b-2 focus:outline-none focus:ring-0 px-0 py-1.5 transition-all" type="text" value="الفصل الأول: البداية"/>
            </div>
            <div class="mb-7 group">
                <label class="block text-xs font-bold text-primary mb-2 transition-colors">ملاحظات الكاتب</label>
                <textarea class="w-full bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 text-sm text-[#0b1c30] focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all resize-none shadow-sm placeholder:text-outline/60" placeholder="أضف ملاحظاتك هنا (مسار الأحداث، شخصيات...)" rows="6"></textarea>
            </div>
            <div class="mt-auto pt-6 border-t border-outline-variant/40">
                <div class="flex justify-between items-center mb-3 bg-surface-container p-3 rounded-lg border border-outline-variant/20">
                    <span class="text-xs font-semibold text-[#45464e]">عدد الكلمات</span>
                    <span class="text-sm font-bold text-primary">1,245</span>
                </div>
                <div class="flex justify-between items-center px-1">
                    <span class="text-xs font-semibold text-[#45464e]">حالة الحفظ</span>
                    <span class="text-xs font-bold text-emerald-600 flex items-center gap-1.5 bg-emerald-50 px-2 py-1 rounded-full border border-emerald-100">
                        <span class="material-symbols-outlined text-[14px]">cloud_done</span> محفوظ
                    </span>
                </div>
            </div>
        </aside>

        <!-- Editor Core -->
        <section class="flex-1 flex flex-col relative bg-slate-50/50">
            <!-- Toolbar -->
            <div class="glass-panel h-14 border-b border-outline-variant/40 flex items-center justify-center px-4 gap-3 z-10 shadow-sm">
                <div class="flex bg-white rounded-lg shadow-sm border border-outline-variant/30 p-1 gap-1">
                    <button class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-[#45464e] hover:text-primary transition-colors" title="غامق"><span class="material-symbols-outlined text-[20px]">format_bold</span></button>
                    <button class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-[#45464e] hover:text-primary transition-colors" title="مائل"><span class="material-symbols-outlined text-[20px]">format_italic</span></button>
                    <button class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-[#45464e] hover:text-primary transition-colors" title="تسطير"><span class="material-symbols-outlined text-[20px]">format_underlined</span></button>
                </div>
                <div class="w-px h-6 bg-outline-variant/50 mx-1"></div>
                <div class="flex bg-white rounded-lg shadow-sm border border-outline-variant/30 p-1 gap-1">
                    <button class="w-8 h-8 flex items-center justify-center rounded bg-primary/10 text-primary transition-colors" title="محاذاة لليمين"><span class="material-symbols-outlined text-[20px]">format_align_right</span></button>
                    <button class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-[#45464e] hover:text-primary transition-colors" title="توسيط"><span class="material-symbols-outlined text-[20px]">format_align_center</span></button>
                    <button class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-[#45464e] hover:text-primary transition-colors" title="محاذاة لليسار"><span class="material-symbols-outlined text-[20px]">format_align_left</span></button>
                </div>
                <div class="w-px h-6 bg-outline-variant/50 mx-1"></div>
                <select class="bg-white border border-outline-variant/30 rounded-lg text-sm font-semibold text-[#45464e] py-1.5 pl-8 pr-4 focus:border-primary focus:ring-0 shadow-sm cursor-pointer hover:bg-slate-50 transition-colors">
                    <option>نص عادي</option>
                    <option>عنوان رئيسي 1</option>
                    <option>عنوان فرعي 2</option>
                </select>
                <div class="flex-1"></div>
                <button class="flex items-center gap-2 px-4 py-1.5 rounded-lg border border-primary/40 text-primary font-semibold text-sm hover:bg-primary/5 hover:border-primary transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                    معاينة
                </button>
            </div>

            <!-- Writing Canvas -->
            <div class="flex-1 overflow-y-auto p-6 md:p-10 flex justify-center pb-40">
                <div class="parchment-bg w-full max-w-[760px] min-h-full rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-outline-variant/40 p-10 lg:p-16 relative transition-shadow hover:shadow-[0_8px_30px_rgb(0,0,0,0.1)]">
                    <!-- Title in Canvas -->
                    <h1 class="font-headline-lg text-4xl text-primary text-center mb-10 outline-none leading-tight font-bold" contenteditable="true">الفصل الأول: البداية</h1>
                    <!-- Content -->
                    <div class="editor-content font-body-reading text-lg text-[#1e293b] outline-none min-h-[600px] leading-loose text-justify" contenteditable="true" data-placeholder="ابدأ بكتابة قصتك هنا...">
                        <p class="mb-6">في ليلة مقمرة، حيث كانت النجوم تتلألأ كحبيبات الماس المنثورة على بساط مخملي أسود، وقف وحيداً يتأمل الأفق البعيد. كانت الرياح الباردة تهمس بين أوراق الأشجار العتيقة، حاملة معها ذكريات لا تُنسى.</p>
                        <p class="mb-6">لم يكن يعلم أن هذه الليلة ستكون نقطة التحول في حياته، وأن القرارات التي سيتخذها قبل بزوغ الفجر ستغير مسار قدره إلى الأبد.</p>
                    </div>
                </div>
            </div>

            <!-- Floating Save Bar -->
            <div class="absolute bottom-8 left-1/2 glass-panel rounded-2xl px-6 py-4 flex items-center gap-5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.15)] z-20 border border-white/60 opacity-0 animate-slide-up" style="animation-delay: 0.4s;">
                <button class="text-[#45464e] font-semibold text-sm hover:text-error transition-colors px-2">تجاهل التغييرات</button>
                <div class="w-px h-6 bg-outline-variant/60"></div>
                <button class="px-5 py-2.5 rounded-xl border-2 border-primary text-primary font-bold text-sm hover:bg-primary/5 transition-all shadow-sm active:scale-95">حفظ كمسودة</button>
                <button class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-accent-orange to-[#b45309] text-white font-bold text-sm hover:shadow-lg hover:shadow-accent-orange/30 hover:-translate-y-0.5 transition-all duration-300 active:scale-95 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">publish</span>
                    نشر الفصل
                </button>
            </div>
        </section>
    </main>
</body>
</html>