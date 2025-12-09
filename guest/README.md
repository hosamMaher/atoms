# Guest Atom API

مشروع Laravel لإدارة الضيوف (Guests) مع نظام الموافقة والاعتماد (Approval Workflow) كجزء من نظام Atoms الموزع.

## نظرة عامة

Guest Atom هو خدمة مستقلة مسؤولة عن إدارة حسابات الضيوف في النظام. يوفر نظام موافقة متكامل حيث يمكن للمستخدمين المعتمدين الموافقة على أو رفض طلبات التسجيل للضيوف. يتكامل مع User Atom للتحقق من الهوية والصلاحيات.

## المميزات

- ✅ إدارة كاملة للضيوف
- ✅ نظام الموافقة والرفض (Approval/Rejection Workflow)
- ✅ التحقق من الهوية باستخدام JWT من User Atom
- ✅ التكامل مع WSO2 API Gateway
- ✅ ربط الضيوف بالفئات والتصنيفات الفرعية
- ✅ عمليات CRUD كاملة
- ✅ البحث والتصفية حسب الحالة والفئة
- ✅ الترقيم (Pagination)
- ✅ الحذف الناعم (Soft Deletes)
- ✅ إدارة الصور
- ✅ API موثق بـ OpenAPI/Swagger

## المتطلبات

- PHP >= 8.2
- Composer
- Laravel 12.0
- Firebase JWT (firebase/php-jwt)
- SQLite (أو أي قاعدة بيانات مدعومة من Laravel)
- تكامل مع User Atom للتحقق من الهوية

## التثبيت

```bash
# تثبيت المكتبات
composer install

# نسخ ملف البيئة
cp .env.example .env

# توليد مفتاح التطبيق
php artisan key:generate

# تشغيل Migrations
php artisan migrate

# تشغيل Seeders (إن وجدت)
php artisan db:seed
```

## الإعداد

قم بتعديل ملف `.env` لإعداد قاعدة البيانات والتكامل:

```env
# قاعدة البيانات
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

# إعدادات WSO2 (اختياري)
WSO2_ENABLED=false
WSO2_GATEWAY_URL=
WSO2_GUEST_URL=http://localhost:8002/api/v1
WSO2_CATEGORY_URL=http://localhost:8001/api/v1
WSO2_USER_URL=http://localhost:8003/api/v1

# إعدادات JWT
JWT_SECRET=your-secret-key
JWT_ALGORITHM=HS256
```

## API Endpoints

### المصادقة (Authentication)

#### تسجيل الدخول للضيف
```
POST /api/v1/guest/auth/login
```

**Body:**
```json
{
  "email": "guest@example.com",
  "password": "password"
}
```

#### التحقق من صحة Token
```
POST /api/v1/guest/auth/validate
```

**Body:**
```json
{
  "token": "jwt-token-here"
}
```

### الضيوف (Guests)

#### قائمة الضيوف
```
GET /api/v1/guests
```

**Headers:**
```
Authorization: Bearer {jwt-token}
```

**Query Parameters:**
- `q` (string): البحث في الاسم أو البريد الإلكتروني
- `status` (string): تصفية حسب الحالة (pending, approved, rejected)
- `category_id` (integer): تصفية حسب الفئة
- `subcategory_id` (integer): تصفية حسب التصنيف الفرعي
- `per_page` (integer): عدد العناصر في الصفحة

**ملاحظة:** 
- المستخدمون الإداريون يرون جميع الضيوف
- المستخدمون العاديون يرون فقط الضيوف في الفئات المخصصة لهم

#### عرض ضيف واحد
```
GET /api/v1/guests/{id}
```

#### إنشاء ضيف جديد
```
POST /api/v1/guests
```

**Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "mobile": "1234567890",
  "photo": "path/to/photo.jpg",
  "category_id": 1,
  "subcategory_id": 2
}
```

**ملاحظة:** عند الإنشاء، الحالة الافتراضية هي `pending`.

#### تحديث ضيف
```
PUT /api/v1/guests/{id}
```

#### حذف ضيف (Soft Delete)
```
DELETE /api/v1/guests/{id}
```

#### استعادة ضيف محذوف
```
POST /api/v1/guests/{id}/restore
```

#### حذف دائم
```
DELETE /api/v1/guests/{id}/force
```

### نظام الموافقة (Approval Workflow)

#### الموافقة على ضيف
```
POST /api/v1/guests/{id}/approve
```

**Headers:**
```
Authorization: Bearer {jwt-token-from-user-atom}
```

**ملاحظة:** يجب أن يكون المستخدم لديه صلاحية الموافقة على الضيوف في الفئة المحددة.

#### رفض ضيف
```
POST /api/v1/guests/{id}/reject
```

**Headers:**
```
Authorization: Bearer {jwt-token-from-user-atom}
```

**Body:**
```json
{
  "reason": "Reason for rejection"
}
```

## حالات الضيف (Guest Statuses)

- `pending`: في انتظار الموافقة (افتراضي)
- `approved`: تمت الموافقة
- `rejected`: تم الرفض

## هيكل المشروع

```
guest/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/
│   │   │       ├── Auth/
│   │   │       │   ├── LoginController.php
│   │   │       │   └── ValidateController.php
│   │   │       └── GuestController.php
│   │   ├── Requests/
│   │   │   └── Api/V1/
│   │   └── Resources/
│   │       └── Api/V1/
│   ├── Models/
│   │   └── Guest.php
│   └── Services/
│       ├── GuestService.php
│       ├── Auth/
│       │   └── UserAuthService.php
│       └── Integration/
│           └── WSO2Service.php
├── database/
│   └── migrations/
├── routes/
│   └── api.php
└── docs/
    └── openapi.yaml
```

## النموذج (Model)

### Guest
- `id`: معرف الضيف
- `name`: اسم الضيف
- `email`: البريد الإلكتروني (فريد)
- `password`: كلمة المرور (مشفرة)
- `mobile`: رقم الجوال
- `photo`: مسار الصورة
- `category_id`: معرف الفئة
- `subcategory_id`: معرف التصنيف الفرعي
- `status`: الحالة (pending, approved, rejected)
- `approved_by`: معرف المستخدم الذي وافق
- `approved_at`: تاريخ الموافقة
- `rejected_by`: معرف المستخدم الذي رفض
- `rejected_at`: تاريخ الرفض
- `reject_reason`: سبب الرفض
- `created_at`, `updated_at`, `deleted_at`: التواريخ

## الخدمات (Services)

### GuestService
- `list(array $params, $userId, $userData)`: قائمة الضيوف مع التحقق من الصلاحيات
- `create($data)`: إنشاء ضيف جديد
- `find($id)`: البحث عن ضيف
- `update($id, $data)`: تحديث ضيف
- `delete($id)`: حذف ناعم
- `restore($id)`: استعادة ضيف محذوف
- `forceDelete($id)`: حذف دائم
- `approveGuest($id, $userId)`: الموافقة على ضيف
- `rejectGuest($id, $userId, $reason)`: رفض ضيف
- `canApproveGuest($id, $userId, $token)`: التحقق من صلاحية الموافقة

### UserAuthService
- `validateToken($token)`: التحقق من صحة JWT Token
- `getUser($userId, $token)`: الحصول على بيانات المستخدم من User Atom

### WSO2Service
- `request($service, $method, $endpoint, $data, $headers)`: طلب عام لخدمات WSO2
- التكامل مع Category Atom و User Atom عبر WSO2 Gateway

## نظام الصلاحيات

- **المستخدمون الإداريون**: يمكنهم الموافقة على جميع الضيوف
- **المستخدمون العاديون**: يمكنهم الموافقة فقط على الضيوف في الفئات المخصصة لهم
- يتم التحقق من الصلاحيات عبر User Atom

## التشغيل

```bash
# تشغيل الخادم
php artisan serve --port=8002

# أو استخدام Composer script
composer run dev
```

الخادم سيعمل على: `http://localhost:8002`

## الاختبارات

```bash
php artisan test
```

## التوثيق

التوثيق الكامل متوفر في ملف `docs/openapi.yaml` ويمكن استيراده في أدوات مثل Postman أو Swagger UI.

## التكامل مع الأنظمة الأخرى

هذا المشروع يتكامل مع:
- **User Atom**: للتحقق من الهوية والصلاحيات
- **Category Atom**: للحصول على معلومات الفئات والتصنيفات الفرعية
- **WSO2 API Gateway**: للتوجيه والتحكم في الوصول (اختياري)

## ملاحظات مهمة

1. جميع عمليات الموافقة والرفض تتطلب JWT Token من User Atom
2. يتم التحقق من الصلاحيات قبل السماح بالعمليات
3. يمكن تفعيل WSO2 Gateway للتوجيه المركزي
4. الصور يتم حفظها في نظام الملفات (يمكن التكامل مع S3 أو أي خدمة تخزين)

## الترخيص

MIT License
