<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="حقيبة Charm القابلة للطي — أناقة يومية وعملية بسعر 1900 دج، والدفع عند الاستلام.">
  <title>Charm — حقيبتك لكل يوم</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700&family=Aref+Ruqaa:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('styles.css') }}">
</head>
<body>
  <a class="skip-link" href="#main">انتقلي إلى المحتوى</a>
  <div class="announcement"><span>الدفع عند الاستلام</span><i></i><span>توصيل إلى 58 ولاية</span><i></i><span>الشحن محسوب حسب الولاية</span></div>

  <header class="site-header">
    <a class="wordmark" href="/" aria-label="Charm الرئيسية"><span class="wordmark-latin">CHARM</span><span class="wordmark-ar">حقيبة لكل يوم</span></a>
    <nav aria-label="التنقل الرئيسي"><a href="#story">التفاصيل</a><a href="#colors">الألوان</a><a href="#faq">الأسئلة</a></nav>
    <a class="header-cta" href="#order">اطلبي الآن</a>
  </header>

  <main id="main">
    <section class="hero">
      <div class="hero-copy reveal">
        <p class="eyebrow"><span></span>رفيقتك الخفيفة لكل مشوار</p>
        <h1>كل ما تحتاجينه،<br><em>في حقيبة واحدة.</em></h1>
        <p class="hero-lead">حقيبة Charm واسعة وخفيفة، تنطوي في لحظات وترافقك من الجامعة والعمل إلى التسوق والسفر. تصميم بسيط، جيب عملي، وأربعة ألوان هادئة.</p>
        <div class="price-row"><strong data-product-price>1,900 دج</strong><div><span class="price-note">الدفع عند الاستلام</span><small>سعر التوصيل يظهر بعد اختيار الولاية</small></div></div>
        <div class="hero-actions"><a class="button button-primary" href="#order">أريد حقيبتي <span>←</span></a><a class="text-link" href="#colors">شاهدي الألوان <span>↙</span></a></div>
        <ul class="hero-proof"><li>✓ قماش خفيف ومتين</li><li>✓ تُطوى بسهولة</li><li>✓ توصيل للمنزل</li></ul>
      </div>
      <div class="hero-gallery reveal">
        <div class="gallery-frame"><span class="gallery-label">CHARM · EVERYDAY TOTE</span><img id="main-product-image" src="{{ asset('assets/charm-black.png') }}" alt="حقيبة Charm باللون الأسود"><span class="image-caption">اللون الأسود</span></div>
        <div class="gallery-thumbs" aria-label="صور المنتج">
          <button class="thumb is-active" type="button" data-image="/assets/charm-black.png" data-caption="اللون الأسود" aria-label="الحقيبة باللون الأسود" aria-pressed="true"><img src="/assets/charm-black.png" alt=""></button>
          <button class="thumb" type="button" data-image="/assets/charm-detail.png" data-caption="تفاصيل الحقيبة" aria-label="تفاصيل الحقيبة" aria-pressed="false"><img src="/assets/charm-detail.png" alt=""></button>
          <button class="thumb" type="button" data-image="/assets/charm-folded.png" data-caption="تُطوى في لحظات" aria-label="الحقيبة مطوية" aria-pressed="false"><img src="/assets/charm-folded.png" alt=""></button>
        </div>
        <div class="charm-mark" aria-hidden="true"><span></span><b>خفيفة وأنيقة</b></div>
      </div>
    </section>

    <div class="ticker"><div><span>واسعة وخفيفة</span><b>◆</b><span>تُطوى بسهولة</span><b>◆</b><span>أربعة ألوان</span><b>◆</b><span>مثالية لكل يوم</span></div></div>

    <section class="story" id="story">
      <div class="story-visual reveal"><img src="/assets/charm-lifestyle.png" alt="حقيبة Charm في الاستخدام اليومي" loading="lazy"><div class="story-stamp"><span>مساحة كبيرة</span><strong>خفيفة</strong><small>وسهلة الحمل</small></div></div>
      <div class="story-copy reveal"><p class="eyebrow"><span></span>مصممة للحياة اليومية</p><h2>كبيرة عندما تحتاجينها،<br><em>صغيرة عندما تطوينها.</em></h2><p>ضعيها في حقيبتك الأساسية ثم افتحيها وقت الحاجة. شكلها المرتب ومقبضها العريض يجعلانها خيارًا مريحًا مهما طال يومك.</p><div class="feature-list"><article><span>01</span><div><h3>مساحة ذكية</h3><p>تستوعب مشترياتك، كتبك وأغراضك اليومية.</p></div></article><article><span>02</span><div><h3>طي سريع</h3><p>تتحول إلى حجم صغير يسهل حفظه وحمله.</p></div></article><article><span>03</span><div><h3>راحة طوال اليوم</h3><p>حزام عريض وقماش خفيف لا يضيف وزنًا مزعجًا.</p></div></article></div></div>
    </section>

    <section class="colors-section" id="colors">
      <div class="section-heading reveal"><p class="eyebrow centered"><span></span>اختاري طابعك</p><h2>أربعة ألوان،<br><em>نفس الأناقة.</em></h2><p>اضغطي على اللون الذي يعجبك وسنجهز طلبك به.</p></div>
      <div class="color-grid reveal">
        <button type="button" class="color-card is-selected" data-color="أسود" data-image="/assets/charm-black.png" aria-pressed="true"><img src="/assets/charm-black.png" alt="حقيبة سوداء" loading="lazy"><span><b>أسود</b><small>كلاسيكي</small><i style="--swatch:#171717"></i></span></button>
        <button type="button" class="color-card" data-color="بني" data-image="/assets/charm-brown.png" aria-pressed="false"><img src="/assets/charm-brown.png" alt="حقيبة بنية" loading="lazy"><span><b>بني</b><small>دافئ</small><i style="--swatch:#765143"></i></span></button>
        <button type="button" class="color-card" data-color="أخضر" data-image="/assets/charm-green.png" aria-pressed="false"><img src="/assets/charm-green.png" alt="حقيبة خضراء" loading="lazy"><span><b>أخضر</b><small>هادئ</small><i style="--swatch:#53644d"></i></span></button>
        <button type="button" class="color-card" data-color="بيج" data-image="/assets/charm-beige.png" aria-pressed="false"><img src="/assets/charm-beige.png" alt="حقيبة بيج" loading="lazy"><span><b>بيج</b><small>ناعم</small><i style="--swatch:#d8c4a5"></i></span></button>
      </div>
    </section>

    <section class="dimensions reveal"><div class="dimensions-copy"><p class="eyebrow light"><span></span>حجم يومي مثالي</p><h2>مساحة واسعة<br>دون وزن إضافي.</h2><p>الأبعاد التقريبية للحقيبة عند فتحها، مع تصميم مرن يتكيف مع أغراضك.</p></div><div class="measurements"><div><strong>41</strong><span>سم</span><small>العرض</small></div><i>×</i><div><strong>39</strong><span>سم</span><small>الارتفاع</small></div><i>×</i><div><strong>10</strong><span>سم</span><small>العمق</small></div></div></section>

    <section class="order-section" id="order">
      <div class="order-intro reveal"><p class="eyebrow"><span></span>اطلبيها الآن</p><h2>خطوات بسيطة،<br><em>والدفع عند الاستلام.</em></h2><p>املئي معلومات التوصيل. سعر الشحن غير مجاني ويُحسب تلقائيًا حسب ولايتك.</p><div class="order-product-card"><img id="order-product-image" src="/assets/charm-black.png" alt="حقيبة Charm المختارة"><div><span>Charm foldable tote</span><strong data-product-price>1,900 دج</strong><small>اللون: <b id="summary-color">أسود</b></small></div></div><div class="delivery-assurance"><span aria-hidden="true">🚚</span><p><strong>توصيل للمنزل عبر NOEST</strong><span>نتصل بك لتأكيد الطلب قبل الإرسال.</span></p></div></div>
      <form class="order-form reveal" id="order-form" novalidate>
        <div class="form-heading"><span>معلومات الطلب</span><small>* الحقول المطلوبة</small></div>
        <label class="field"><span>الاسم الكامل *</span><input name="customer_name" type="text" autocomplete="name" placeholder="مثال: سارة بن علي" required minlength="3"><small class="field-error" data-error-for="customer_name"></small></label>
        <label class="field"><span>رقم الهاتف *</span><input name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="0555 12 34 56" required><small class="field-error" data-error-for="phone"></small></label>
        <label class="field"><span>الولاية *</span><select name="wilaya_id" id="wilaya-select" required><option value="">جاري تحميل الولايات…</option></select><small class="field-error" data-error-for="wilaya_id"></small></label>
        <label class="field"><span>البلدية *</span><select name="commune" id="commune-select" required disabled><option value="">اختاري الولاية أولًا</option></select><small class="field-error" data-error-for="commune"></small></label>
        <label class="field field-full"><span>العنوان الكامل *</span><input name="address" type="text" autocomplete="street-address" placeholder="الحي، الشارع، رقم المنزل…" required minlength="5"><small class="field-error" data-error-for="address"></small></label>
        <fieldset class="color-fieldset field-full"><legend>اللون *</legend><div class="color-options"><label><input type="radio" name="color" value="أسود" checked><span><i style="--swatch:#171717"></i>أسود</span></label><label><input type="radio" name="color" value="بني"><span><i style="--swatch:#765143"></i>بني</span></label><label><input type="radio" name="color" value="أخضر"><span><i style="--swatch:#53644d"></i>أخضر</span></label><label><input type="radio" name="color" value="بيج"><span><i style="--swatch:#d8c4a5"></i>بيج</span></label></div><small class="field-error" data-error-for="color"></small></fieldset>
        <label class="field"><span>الكمية</span><select name="quantity" id="quantity-select"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select><small class="field-error" data-error-for="quantity"></small></label>
        <label class="field"><span>ملاحظة (اختياري)</span><input name="notes" type="text" maxlength="500" placeholder="أي توضيح للمُوصّل"><small class="field-error" data-error-for="notes"></small></label>
        <div class="order-totals field-full"><div><span>المنتج</span><strong id="subtotal-price">1,900 دج</strong></div><div><span>التوصيل للمنزل</span><strong id="shipping-price">اختاري الولاية</strong></div><div class="total-line"><span>المجموع</span><strong id="total-price">—</strong></div></div>
        <p class="form-status field-full" id="form-status" role="status"></p>
        <button class="button button-submit field-full" id="submit-order" type="submit" disabled><span>تأكيد الطلب</span><small>الدفع نقدًا عند الاستلام</small><b>←</b></button>
        <p class="privacy-note field-full">🔒 معلوماتك تُستخدم فقط لتأكيد طلبك وتوصيله.</p>
      </form>
    </section>

    <section class="faq reveal" id="faq"><p class="eyebrow centered"><span></span>قبل أن تطلبي</p><h2>أسئلة <em>شائعة.</em></h2><div class="faq-list"><details><summary>هل التوصيل مجاني؟ <span>+</span></summary><p>لا. يُحسب سعر التوصيل تلقائيًا حسب الولاية ويظهر في ملخص الطلب قبل التأكيد.</p></details><details><summary>كيف أدفع؟ <span>+</span></summary><p>الدفع نقدًا عند استلام الحقيبة. لا نطلب أي دفع مسبق.</p></details><details><summary>هل يمكنني اختيار اللون؟ <span>+</span></summary><p>نعم، اختاري بين الأسود والبني والأخضر والبيج حسب توفر المخزون.</p></details><details><summary>متى يصل طلبي؟ <span>+</span></summary><p>تختلف المدة حسب الولاية، وسنتصل بك أولًا لتأكيد معلومات الطلب.</p></details></div></section>
  </main>

  <footer><div class="footer-wordmark"><span>CHARM</span><p>حقيبة لكل يوم، ولكل حكاية.</p><a class="admin-link" href="/admin">إدارة المتجر</a></div><div><a href="#story">المنتج</a><a href="#order">الطلب</a><a href="#faq">الأسئلة</a></div><p>© <span id="year"></span> Charm DZ — جميع الحقوق محفوظة.</p></footer>

  <div class="success-modal" id="success-modal" hidden><button class="modal-backdrop" type="button" data-close-modal aria-label="إغلاق"></button><section role="dialog" aria-modal="true" aria-labelledby="success-title"><div class="success-icon">✓</div><p class="eyebrow centered"><span></span>تم تسجيل طلبك</p><h2 id="success-title">شكرًا لكِ!</h2><p>سنتصل بك قريبًا لتأكيد العنوان والتوصيل.</p><div class="success-order"><span>رقم الطلب</span><strong id="success-order-number">—</strong><small>المجموع: <b id="success-total">—</b></small></div><button class="button button-primary" type="button" data-close-modal>العودة إلى المتجر</button></section></div>
  <div class="mobile-buy-bar" id="mobile-buy-bar"><div><small>حقيبة Charm</small><strong data-product-price>1,900 دج</strong></div><a href="#order">اطلبي الآن</a></div>
  <script type="module" src="{{ asset('app.js') }}"></script>
</body>
</html>
