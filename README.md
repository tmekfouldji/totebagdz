# Charm Tote DZ

متجر عربي لمنتج واحد مبني بالكامل بـ **Laravel 13**، ومجهز للنشر من خيار **Laravel** في Laravel Forge.

## الوظائف

- صفحة بيع عربية RTL ومتجاوبة لحقيبة Charm بسعر ابتدائي 1900 دج.
- أربعة ألوان مع صور المنتج ومخزون مستقل لكل لون.
- الدفع عند الاستلام وشحن مدفوع يُحسب حسب الولاية؛ لا يوجد شحن مجاني.
- 58 ولاية و1541 بلدية محليًا، مع قراءة الولايات والبلديات والأسعار مباشرة من NOEST عند إضافة المفاتيح.
- إنشاء الطلبات، أرقام طلبات فريدة، وحماية المخزون داخل transaction.
- لوحة إدارة عربية: الإحصائيات، البحث والفلترة، تعديل الحالات وبيانات الزبون، حذف الطلبات غير المرسلة، CSV، السعر، التوفر والمخزون.
- إرسال الطلب يدويًا إلى NOEST وتخزين رقم التتبع، مع خيار الإرسال/التأكيد التلقائي.
- دخول إدارة بجلسة Laravel وCSRF وحماية rate limit.

## تشغيل محلي

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

المتجر: `http://127.0.0.1:8000/`  
الإدارة: `http://127.0.0.1:8000/admin`

بيانات التطوير الافتراضية موجودة في `.env` المحلي فقط. غيّر `ADMIN_EMAIL` و`ADMIN_PASSWORD` قبل seeding في الإنتاج.

## NOEST

أضف إلى متغيرات البيئة في Forge:

```dotenv
NOEST_BASE_URL=https://app.noest-dz.com
NOEST_API_TOKEN=your-token
NOEST_USER_GUID=your-guid
NOEST_AUTO_DISPATCH=false
NOEST_AUTO_VALIDATE=false
```

عند غياب المفاتيح، يبقى الطلب فعالًا وتُستخدم تعريفة شحن احتياطية محلية. عند وجودها، تُقرأ بيانات الشحن من NOEST ويمكن إرسال الطلب من لوحة الإدارة.

## الاختبارات

```bash
composer test
```

راجع [FORGE.md](FORGE.md) لخطوات النشر الجاهزة.
