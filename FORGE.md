# النشر على Laravel Forge

## إنشاء الموقع

1. اختر **New site → Laravel**.
2. اختر PHP **8.3 أو أحدث**.
3. اربط مستودع Git الذي يحتوي هذا المشروع.
4. اجعل Web Directory هو `/public` (Forge يضبطه تلقائيًا لمواقع Laravel).
5. أنشئ قاعدة MySQL واربطها بالموقع.

## متغيرات البيئة

انسخ `.env.example` في شاشة Environment داخل Forge، ثم اضبط:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=Africa/Algiers

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

ADMIN_EMAIL=your-admin@example.com
ADMIN_PASSWORD=use-a-long-unique-password

NOEST_BASE_URL=https://app.noest-dz.com
NOEST_API_TOKEN=your-noest-token
NOEST_USER_GUID=your-noest-guid
NOEST_AUTO_DISPATCH=false
NOEST_AUTO_VALIDATE=false
```

اضغط **Generate App Key** من Forge أو شغّل `php artisan key:generate` مرة واحدة.

## Deployment Script

```bash
cd $FORGE_SITE_PATH
git pull origin $FORGE_SITE_BRANCH

composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

php artisan migrate --force
php artisan db:seed --force
php artisan optimize
php artisan queue:restart
```

الأصول الحالية CSS/JS وصور جاهزة داخل `public`، لذلك لا يلزم Node أو `npm build` على الخادم.

ملاحظة: `db:seed` آمن لإعادة التشغيل لأنه يستخدم `updateOrCreate` و`upsert`. كلمة مرور المدير تُحدّث من متغيرات البيئة في كل نشر؛ أبقها قوية وسرية.

## بعد النشر

- فعّل SSL من Forge.
- افتح `/` وأنشئ طلبًا تجريبيًا.
- افتح `/admin` وتأكد من ظهوره.
- إذا أضفت مفاتيح NOEST، يجب أن تظهر الحالة **متصل وجاهز للإرسال** في الشريط الجانبي.
- ابدأ Queue Worker من Forge إذا فعّلت أعمالًا مؤجلة مستقبلًا؛ التكامل الحالي يعمل مباشرة ولا يعتمد عليه.
