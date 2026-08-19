<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex,nofollow">
  <title>إدارة متجر Charm</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700&family=Aref+Ruqaa:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('admin.css') }}">
</head>
<body>
  <main class="login-screen" id="login-screen">
    <section class="login-art"><a class="admin-wordmark" href="/"><span>CHARM</span><small>STORE ADMIN</small></a><div class="login-quote"><p>كل طلب هو بداية<br><em>حكاية جديدة.</em></p><span>لوحة بسيطة لمتابعة الطلبات والمخزون.</span></div><img src="/assets/charm-folded.png" alt=""></section>
    <section class="login-card"><p class="kicker">مرحبًا بعودتك</p><h1>دخول الإدارة</h1><p>أدخل بيانات المدير للوصول إلى المتجر.</p><form id="login-form"><label><span>البريد الإلكتروني</span><input type="email" name="email" value="admin@charm.dz" autocomplete="username" required></label><label><span>كلمة المرور</span><input type="password" name="password" autocomplete="current-password" required></label><p class="login-error" id="login-error"></p><button class="primary-button" type="submit">دخول لوحة التحكم <span>←</span></button></form><a href="/">← العودة إلى المتجر</a></section>
  </main>

  <div class="admin-shell" id="admin-shell" hidden>
    <aside class="sidebar"><a class="admin-wordmark" href="/"><span>CHARM</span><small>STORE ADMIN</small></a><nav><button class="nav-item is-active" data-view="overview"><span>⌂</span>نظرة عامة</button><button class="nav-item" data-view="orders"><span>▤</span>الطلبات <b id="pending-badge">0</b></button><button class="nav-item" data-view="product"><span>◇</span>المنتج والمخزون</button></nav><div class="sidebar-status"><span class="status-dot" id="noest-dot"></span><p><strong>NOEST API</strong><small id="noest-status">جاري التحقق…</small></p></div><button class="logout-button" id="logout-button">تسجيل الخروج</button></aside>
    <div class="admin-main">
      <header class="admin-header"><button class="menu-button" id="menu-button">☰</button><div><p id="today-label"></p><h1 id="view-title">نظرة عامة</h1></div><div class="header-actions"><button class="secondary-button" id="refresh-button">↻ تحديث</button><a class="primary-button" href="/" target="_blank">فتح المتجر ↗</a></div></header>

      <section class="admin-view is-active" data-view-panel="overview">
        <div class="stats-grid"><article class="stat-card accent"><span>إجمالي المبيعات</span><strong id="stat-revenue">—</strong><small>الطلبات المسلّمة</small></article><article class="stat-card"><span>كل الطلبات</span><strong id="stat-orders">—</strong><small>منذ بداية المتجر</small></article><article class="stat-card"><span>طلبات اليوم</span><strong id="stat-today">—</strong><small>آخر 24 ساعة</small></article><article class="stat-card"><span>بانتظار التأكيد</span><strong id="stat-pending">—</strong><small>تحتاج متابعة</small></article><article class="stat-card"><span>المخزون الكلي</span><strong id="stat-stock">—</strong><small>كل الألوان</small></article></div>
        <div class="overview-grid"><section class="panel"><div class="panel-heading"><div><span>آخر النشاطات</span><h2>الطلبات الأخيرة</h2></div><button class="secondary-button" data-go-orders>عرض الكل</button></div><div id="recent-orders"><p class="empty-row">جاري التحميل…</p></div></section><section class="panel"><div class="panel-heading"><div><span>التوزيع</span><h2>حالات الطلبات</h2></div></div><div class="status-chart" id="status-chart"></div></section></div>
      </section>

      <section class="admin-view" data-view-panel="orders">
        <div class="toolbar"><div class="search-box"><span>⌕</span><input id="order-search" type="search" placeholder="رقم الطلب، الاسم أو الهاتف…"></div><select id="status-filter"><option value="">كل الحالات</option><option value="pending">بانتظار التأكيد</option><option value="confirmed">مؤكد</option><option value="shipped">قيد الشحن</option><option value="delivered">تم التسليم</option><option value="cancelled">ملغى</option><option value="returned">مرتجع</option></select><button class="secondary-button" id="export-button">تصدير CSV ↓</button></div>
        <section class="panel orders-panel"><div class="table-summary"><h2>كل الطلبات</h2><span id="orders-count">0 طلب</span></div><div class="table-scroll"><table><thead><tr><th>الطلب</th><th>الزبون</th><th>التوصيل</th><th>المنتج</th><th>المجموع</th><th>الحالة</th><th>NOEST</th><th></th></tr></thead><tbody id="orders-body"><tr><td colspan="8" class="empty-row">جاري التحميل…</td></tr></tbody></table></div><div class="pagination" id="pagination"></div></section>
      </section>

      <section class="admin-view" data-view-panel="product">
        <form id="product-form" class="product-layout"><section class="panel product-main"><div class="panel-heading"><div><span>CHARM TOTE</span><h2>السعر والتوفر</h2></div><span id="product-availability">—</span></div><div class="price-fields"><label>سعر البيع (دج)<input type="number" name="price" min="100" required></label><label>السعر قبل التخفيض (دج)<input type="number" name="compare_at_price" min="0"></label></div><p class="fixed-shipping-note">التوصيل مدفوع دائمًا ويُحسب حسب الولاية.</p><label class="switch-label"><input type="checkbox" name="active"><span></span>المنتج متاح للبيع</label><label class="switch-label"><input type="checkbox" name="track_inventory"><span></span>تتبع المخزون تلقائيًا</label><div class="panel-heading"><div><span>حسب اللون</span><h2>المخزون</h2></div><p class="stock-total">المجموع: <b id="product-total-stock">0</b></p></div><div class="inventory-list" id="inventory-list"></div></section><aside class="save-card panel"><img src="/assets/charm-black.png" alt="حقيبة Charm"><p>آخر تعديل</p><strong id="product-updated">—</strong><button class="primary-button" type="submit">حفظ كل التغييرات</button></aside></form>
      </section>
    </div>
  </div>

  <div class="drawer-wrap" id="order-drawer" hidden><button class="drawer-backdrop" data-close-drawer aria-label="إغلاق"></button><aside class="order-drawer"><header><div><span>تفاصيل الطلب</span><h2 id="drawer-title">—</h2></div><button type="button" data-close-drawer>×</button></header><form id="order-edit-form"><input type="hidden" name="id"><div class="drawer-status-row"><label><span>حالة الطلب</span><select name="status"><option value="pending">بانتظار التأكيد</option><option value="confirmed">مؤكد</option><option value="shipped">قيد الشحن</option><option value="delivered">تم التسليم</option><option value="cancelled">ملغى</option><option value="returned">مرتجع</option></select></label><label><span>حالة الدفع</span><select name="payment_status"><option value="pending">غير مدفوع</option><option value="paid">مدفوع</option><option value="refunded">مسترجع</option></select></label></div><div class="drawer-product"><img id="drawer-product-image" src="/assets/charm-black.png" alt=""><div><strong>حقيبة Charm</strong><span id="drawer-variant">—</span><small id="drawer-prices">—</small></div></div><div class="drawer-fields"><label><span>الاسم</span><input name="customer_name" required></label><label><span>الهاتف</span><input name="phone" required></label><label class="full"><span>العنوان</span><input name="address" required></label><label><span>الولاية</span><input name="wilaya_name" required></label><label><span>البلدية</span><input name="commune" required></label><label class="full"><span>ملاحظة</span><input name="notes"></label></div><div class="drawer-noest"><div><span class="status-dot" id="drawer-noest-dot"></span><p><strong>NOEST</strong><small id="drawer-noest-text">—</small></p></div><button type="button" class="secondary-button" id="dispatch-button">إرسال إلى NOEST</button></div><p class="drawer-error" id="drawer-error"></p><section class="history"><h3>سجل الطلب</h3><div id="order-history"></div></section><div class="drawer-actions"><button type="button" class="danger-button" id="delete-order">حذف الطلب</button><button type="submit" class="primary-button">حفظ التعديلات</button></div></form></aside></div>
  <div class="toast" id="toast" role="status"></div>
  <script type="module" src="{{ asset('admin.js') }}"></script>
</body>
</html>
