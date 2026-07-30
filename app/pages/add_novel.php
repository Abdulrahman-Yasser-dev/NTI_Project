<?php require_once '../app/core/init.php'; ?>
<!doctype html>
<html dir="rtl" lang="ar">
  <head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>إضافة رواية جديدة - سرد</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;600&family=Literata:opsz,wght@7..72,400&family=Noto+Serif+Arabic:wght@600;700&display=swap" rel="stylesheet" />
    <style>
      body { font-family: "IBM Plex Sans Arabic", sans-serif; background-color: #f8f9ff; }
      .font-headline-md, .font-headline-lg, .font-headline-lg-mobile, .font-display-lg { font-family: "Noto Serif Arabic", serif; }
      .font-body-reading { font-family: "Literata", serif; }
      /* Custom Scrollbar */
      ::-webkit-scrollbar { width: 8px; }
      ::-webkit-scrollbar-track { background: transparent; }
      ::-webkit-scrollbar-thumb { background: #bac6ec; border-radius: 4px; }
      ::-webkit-scrollbar-thumb:hover { background: #98a4c9; }
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
              'fade-in': 'fadeIn 0.5s ease-out forwards',
            },
            keyframes: {
              fadeInUp: {
                '0%': { opacity: '0', transform: 'translateY(20px)' },
                '100%': { opacity: '1', transform: 'translateY(0)' },
              },
              fadeIn: {
                '0%': { opacity: '0' },
                '100%': { opacity: '1' },
              }
            }
          },
        },
      };
    </script>
  </head>
  <body class="text-[#0b1c30] min-h-screen flex flex-col relative overflow-x-hidden z-0">
    <!-- Background Decoration -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute -top-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-gradient-to-br from-blue-100/40 to-purple-100/40 blur-3xl"></div>
        <div class="absolute top-[60%] -left-[10%] w-[40%] h-[40%] rounded-full bg-gradient-to-tr from-amber-100/30 to-orange-50/30 blur-3xl"></div>
    </div>

    <!-- TopAppBar -->
    <header class="bg-surface/80 backdrop-blur-lg border-b border-outline-variant/50 shadow-sm w-full sticky top-0 z-50 transition-all">
      <div class="flex flex-row-reverse justify-between items-center px-4 md:px-10 h-16 w-full max-w-[1200px] mx-auto">
        <!-- Brand Logo -->
        <div class="flex items-center gap-2">
          <img alt="رواياتي" class="h-10 w-auto object-contain drop-shadow-sm" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC3n0B3x5jaIgKWss2vxacZ0q1V5clYRXKo1ovC6GSZ9WdR2yByLb17Cy-zBFxoAXj_DP2qrKVKZ9EDEsueFhPxxRF2v81qrRYCxN5-3_VnenoQbIEv0mryprBQGo-9Ot1xe3yYqARv-PRu5Onr7R5rzadtPAsKuqcTm7EqMDjl72bOgHQwXwklJlMZrLLj1ry4j_h_XaJCTlv-bp5kUqhrWfJhe72u9oaicUYO8CTAcfcIMrqfyZ7ZRT0vghUG5KiE5M1iJ6tFTTdx" />
          <span class="font-headline-lg text-2xl hidden md:block text-primary font-bold">سرد</span>
        </div>
        <!-- Navigation Links (Desktop) -->
        <nav class="hidden md:flex items-center gap-8">
          <a class="text-[#45464e] font-semibold text-sm hover:text-primary transition-colors" href="index">الرئيسية</a>
          <a class="text-[#45464e] font-semibold text-sm hover:text-primary transition-colors" href="Browsebooks">استكشف</a>
          <a class="text-[#45464e] font-semibold text-sm hover:text-primary transition-colors" href="#">مكتبتي</a>
          <a class="text-primary font-bold border-b-2 border-primary pb-1 text-sm transition-all" href="#">الكتابة</a>
        </nav>
        <!-- Actions & Profile -->
        <div class="flex items-center gap-3 flex-row-reverse">
          <button class="text-[#45464e] hover:text-primary hover:bg-surface-container transition-all p-2 rounded-full flex items-center justify-center">
            <span class="material-symbols-outlined">search</span>
          </button>
          <button class="text-[#45464e] hover:text-primary hover:bg-surface-container transition-all p-2 rounded-full flex items-center justify-center relative">
            <span class="material-symbols-outlined">notifications</span>
            <span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full border border-surface"></span>
          </button>
          <div class="h-9 w-9 rounded-full overflow-hidden border-2 border-outline-variant/30 hover:border-primary transition-colors cursor-pointer ml-2 shadow-sm">
            <img alt="صورة المستخدم" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAnz-F-q5dGpz8tKeJCW1yGsNbauW6sI3lnzR5qIgBf7pzCDzoMG7xtX4amf1ChxQIaZ4qf_5rtnB9GKxzh-LerrPPz2iJwMSSbQHYZq04nXG7lKVtK1d1uD71e46lfylRV4IiVjakhpRFMONYo3QIZCMwChtMpixF-iKMoEVhtavWuWrz4d6vCKCHW9wI6eoZvx65TuMYdZfDxlNvE0oAAz_ySOVYmgjwtvE1l45TfB9GorNHD43SUMA8TrTcsuytIPEJJUsmwCmI0" />
          </div>
          <!-- Mobile Menu Toggle -->
          <button class="md:hidden text-primary p-2 flex items-center justify-center hover:bg-surface-container rounded-full">
            <span class="material-symbols-outlined">menu</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content Canvas -->
    <main class="flex-grow w-full max-w-[720px] mx-auto px-4 py-10 md:py-16 flex flex-col gap-8 opacity-0 animate-fade-in-up">
      <div class="text-center">
        <h1 class="font-headline-lg text-3xl md:text-4xl text-primary mb-3 drop-shadow-sm font-bold">إضافة رواية جديدة</h1>
        <p class="text-[#45464e] text-base md:text-lg">ابدأ رحلتك الإبداعية، وشارك قصتك مع العالم.</p>
      </div>

      <!-- Form Card -->
      <div class="bg-surface-container-lowest rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-outline-variant/30 p-6 md:p-10 transition-all hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
        <form action="#" class="flex flex-col gap-7" method="POST">
          <!-- Cover Image Upload Area -->
          <div class="flex flex-col gap-3">
            <label class="font-semibold text-sm text-primary">غلاف الرواية</label>
            <div class="border-2 border-dashed border-outline-variant/60 rounded-xl bg-surface/50 hover:bg-primary/5 hover:border-primary/50 transition-all duration-300 p-10 flex flex-col items-center justify-center cursor-pointer group">
              <div class="w-16 h-16 rounded-full bg-surface-container flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-primary/10 transition-transform duration-300">
                  <span class="material-symbols-outlined text-3xl text-primary/70 group-hover:text-primary transition-colors">add_photo_alternate</span>
              </div>
              <span class="font-semibold text-sm text-[#45464e] group-hover:text-primary mb-1 transition-colors">انقر لرفع صورة الغلاف</span>
              <span class="text-xs text-outline text-center">PNG, JPG, أو WEBP (الحد الأقصى 5MB)<br>نسبة العرض إلى الارتفاع المفضلة 2:3</span>
              <input accept="image/*" class="hidden" type="file" />
            </div>
          </div>

          <!-- Novel Title -->
          <div class="flex flex-col gap-2">
            <label class="font-semibold text-sm text-primary" for="novel-title">عنوان الرواية</label>
            <input class="w-full bg-surface/30 border border-outline-variant/60 rounded-lg text-[#0b1c30] px-4 py-3 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all hover:border-outline-variant placeholder:text-outline/70" id="novel-title" name="title" placeholder="أدخل عنوان روايتك هنا..." required type="text" />
          </div>

          <!-- Genre Selection -->
          <div class="flex flex-col gap-2">
            <label class="font-semibold text-sm text-primary" for="novel-genre">التصنيف (النوع الأدبي)</label>
            <div class="relative">
              <select class="w-full bg-surface/30 border border-outline-variant/60 rounded-lg text-[#0b1c30] px-4 py-3 appearance-none focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all hover:border-outline-variant cursor-pointer" id="novel-genre" name="genre" required>
                <option disabled selected value="">اختر التصنيف الأقرب لروايتك</option>
                <option value="romance">رومانسي</option>
                <option value="fantasy">خيال</option>
                <option value="mystery">غموض / تشويق</option>
                <option value="scifi">خيال علمي</option>
                <option value="historical">تاريخي</option>
                <option value="drama">دراما</option>
              </select>
              <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">expand_more</span>
            </div>
          </div>

          <!-- Description -->
          <div class="flex flex-col gap-2">
            <label class="font-semibold text-sm text-primary" for="novel-description">نبذة عن الرواية (الوصف)</label>
            <textarea class="w-full bg-surface/30 border border-outline-variant/60 rounded-lg text-[#0b1c30] px-4 py-3 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all hover:border-outline-variant placeholder:text-outline/70 resize-y" id="novel-description" name="description" placeholder="اكتب نبذة مختصرة تشوق القراء لمتابعة أحداث روايتك..." required rows="5"></textarea>
          </div>

          <!-- Actions -->
          <div class="flex gap-4 mt-6 pt-6 border-t border-outline-variant/40 flex-row-reverse">
            <button class="px-6 py-2.5 rounded-lg text-primary border border-primary/30 font-semibold text-sm hover:bg-primary/5 hover:border-primary transition-all ml-auto" type="button">إلغاء</button>
            <button class="px-8 py-2.5 rounded-lg bg-gradient-to-r from-primary to-primary-container text-white font-semibold text-sm hover:shadow-lg hover:shadow-primary/30 hover:-translate-y-0.5 transition-all duration-300 active:scale-95" type="submit">إنشاء الرواية وبدء الكتابة</button>
          </div>
        </form>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-surface/50 border-t border-outline-variant/50 mt-auto backdrop-blur-sm">
      <div class="flex flex-col md:flex-row-reverse justify-between items-center gap-6 py-8 px-4 md:px-10 w-full max-w-[1200px] mx-auto">
        <div class="font-headline-md text-xl text-primary font-bold">سرد</div>
        <nav class="flex flex-wrap justify-center gap-6">
          <a class="text-[#45464e] hover:text-primary transition-all font-semibold text-xs" href="#">التصنيفات</a>
          <a class="text-[#45464e] hover:text-primary transition-all font-semibold text-xs" href="#">الشروط والأحكام</a>
          <a class="text-[#45464e] hover:text-primary transition-all font-semibold text-xs" href="#">سياسة الخصوصية</a>
          <a class="text-[#45464e] hover:text-primary transition-all font-semibold text-xs" href="#">تواصل معنا</a>
        </nav>
        <div class="text-outline text-xs text-center md:text-right">© ٢٠٢٤ سرد. جميع الحقوق محفوظة.</div>
      </div>
    </footer>
  </body>
</html>



