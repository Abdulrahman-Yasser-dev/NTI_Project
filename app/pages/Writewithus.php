<?php
// WriteWithUs.php
// Static-content page explaining submission policy + evaluation
// criteria for writers. No DB needed — this is policy text.
require_once "../app/core/init.php";

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>اكتب معنا — ريشة</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Noto+Naskh+Arabic:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/WriteWithUs.css">
</head>
<body>

<header class="site-header">
    <div class="logo" onclick="location.href='index'" style="cursor:pointer;">
        <span class="logo-mark">✒</span>
        <span class="logo-text">ريشة</span>
    </div>
    <div class="header-actions">
        <button class="btn btn-outline" onclick="location.href='Login'">دخول</button>
        <button class="btn btn-filled" onclick="location.href='Signup?role=writer'">حساب جديد</button>
    </div>
</header>

<main class="article-wrap">

    <h1 class="page-title">اكتب معنا</h1>

    <section class="article-section">
        <h2>سياسة استقبال الأعمال</h2>
        <p>
            تنطلق منصة «ريشة» من رسالة واحدة: إتاحة فرصة نشر حقيقية للكتّاب الجادين والأعمال العربية
            التي تضيف للقارئ العربي. لذا ترحب المنصة باستقبال الروايات من كل من يرغب في الكتابة والنشر.
        </p>
        <p>
            لا تُنشر كل الأعمال المرسلة تلقائياً — يراجع فريق الإدارة كل رواية وفصل قبل النشر للتأكد من
            توافقها مع معايير المنصة، وقد يُطلب من الكاتب إجراء بعض التعديلات قبل الموافقة على النشر.
        </p>
    </section>

    <section class="article-section">
        <h2>معايير قبول الأعمال</h2>
        <ol class="criteria-list">
            <li>ألا يقل عدد كلمات الفصل الواحد عن 800 كلمة، ولا يزيد عن 2500 كلمة.</li>
            <li>ألا يتجاوز طول عنوان الفصل أو الرواية 60 حرفًا.</li>
            <li>أن تكون الرواية مكتوبة بالعربية الفصحى، أو بلغة عربية أدبية سليمة وواضحة المعنى.</li>
            <li>أن يكون العمل أصليًا من تأليف الكاتب نفسه، وغير منشور من قبل على أي منصة أخرى.</li>
            <li>عدم انتهاك حقوق الملكية الفكرية، أو استخدام مقتطفات من أعمال أخرى دون توثيق واضح.</li>
            <li>خلو المحتوى من أي تحريض على العنف أو خطاب كراهية أو مواد غير لائقة.</li>
            <li>الالتزام بترتيب الفصول بشكل متسلسل ومنطقي داخل الرواية الواحدة.</li>
        </ol>
    </section>

    <section class="article-section">
        <h2>كيفية الإرسال</h2>
        <p>
            على عكس بعض المنصات التي تستقبل الأعمال عبر البريد الإلكتروني، النشر في «ريشة» يتم مباشرة
            من داخل حسابك:
        </p>
        <ol class="steps-list">
            <li>أنشئي حساب ككاتبة من صفحة <a href="Signup.php?role=writer">إنشاء حساب</a> واختاري "كاتب" كنوع الحساب.</li>
            <li>من لوحة التحكم الخاصة بك، اختاري "إضافة رواية جديدة" وأدخلي بيانات الرواية (العنوان، الوصف، التصنيف).</li>
            <li>أضيفي فصولك واحدًا تلو الآخر من نفس اللوحة.</li>
            <li>يظهر كل عمل جديد بحالة "قيد المراجعة" حتى يراجعه فريق الإدارة.</li>
            <li>عند الموافقة، يُنشر العمل تلقائيًا ويظهر للقراء. في حال الرفض، تصلك ملاحظات محددة داخل لوحتك لتعديل العمل وإعادة إرساله.</li>
        </ol>
    </section>

    <section class="article-section">
        <h2>التزامات ريشة تجاه الكاتب</h2>
        <ul class="commitments-list">
            <li>مراجعة كل عمل جديد خلال مدة معقولة، وعدم ترك الأعمال معلقة دون رد.</li>
            <li>توضيح سبب الرفض بشكل مباشر عند عدم قبول أي فصل أو رواية.</li>
            <li>عدم التعديل على نص الكاتب أو أسلوبه دون الرجوع إليه أولًا.</li>
            <li>احتفاظ الكاتب الكامل بحقوق عمله؛ المنصة وسيلة نشر لا مالكة للمحتوى.</li>
        </ul>
    </section>

</main>

<footer class="site-footer">
    <p>© <?php echo date("Y"); ?> ريشة — منصة كتابة وقراءة الروايات العربية</p>
</footer>

</body>
</html>






