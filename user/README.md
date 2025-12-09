# User Atom API

مشروع Laravel لإدارة المستخدمين (Users) والأدوار (Roles) مع نظام المصادقة JWT كجزء من نظام Atoms الموزع.

## نظرة عامة

User Atom هو خدمة مستقلة مسؤولة عن إدارة المستخدمين المعتمدين في النظام. يوفر نظام مصادقة كامل باستخدام JWT، إدارة الأدوار، وتخصيص الفئات للمستخدمين. هذا المشروع يعمل كخدمة المصادقة المركزية للأنظمة الأخرى (Guest Atom و Category Atom).

## المميزات

- ✅ إدارة كاملة للمستخدمين
- ✅ نظام المصادقة باستخدام JWT (Firebase JWT)
- ✅ إدارة الأدوار (Roles)
- ✅ تخصيص الفئات للمستخدمين (Category Assignments)
- ✅ عمليات CRUD كاملة
- ✅ البحث والتصفية حسب الدور والحالة
- ✅ الترقيم (Pagination)
- ✅ الحذف الناعم (Soft Deletes)
- ✅ تشفير كلمات المرور تلقائياً
- ✅ API موثق بـ OpenAPI/Swagger
- ✅ Postman Collection متوفر

## المتطلبات

- PHP >= 8.2
- Composer
- Laravel 12.0
- Firebase JWT (firebase/php-jwt)
- SQLite (أو أي قاعدة بيانات مدعومة من Laravel)

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

قم بتعديل ملف `.env` لإعداد قاعدة البيانات و JWT:

```env
# قاعدة البيانات
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

# إعدادات JWT
JWT_SECRET=your-secret-key-here
JWT_ALGORITHM=HS256
JWT_EXPIRATION=3600

# إعدادات WSO2 (اختياري)
WSO2_ENABLED=false
WSO2_GATEWAY_URL=
WSO2_USER_URL=http://localhost:8003/api/v1
```

## API Endpoints

### المصادقة (Authentication)

#### تسجيل الدخول
```
POST /api/v1/auth/login
```

**Body:**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "status": true,
  "data": {
    "token": "jwt-token-here",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": {...}
    }
  }
}
```

#### التحقق من صحة Token
```
POST /api/v1/auth/validate
```

**Body:**
```json
{
  "token": "jwt-token-here"
}
```

**Response:**
```json
{
  "status": true,
  "data": {
    "valid": true,
    "user_id": 1
  }
}
```

### المستخدمون (Users)

**ملاحظة:** جميع endpoints التالية تتطلب JWT Token في Header:
```
Authorization: Bearer {jwt-token}
```

#### قائمة المستخدمين
```
GET /api/v1/users
```

**Query Parameters:**
- `q` (string): البحث في الاسم أو البريد الإلكتروني
- `role_id` (integer): تصفية حسب الدور
- `is_active` (0|1): تصفية حسب الحالة
- `per_page` (integer): عدد العناصر في الصفحة

#### عرض مستخدم واحد
```
GET /api/v1/users/{id}
```

#### إنشاء مستخدم جديد
```
POST /api/v1/users
```

**Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "role_id": 1,
  "is_active": true
}
```

#### تحديث مستخدم
```
PUT /api/v1/users/{id}
```

**Body:**
```json
{
  "name": "John Doe Updated",
  "email": "john@example.com",
  "password": "newpassword",  // اختياري
  "role_id": 2,
  "is_active": false
}
```

#### حذف مستخدم (Soft Delete)
```
DELETE /api/v1/users/{id}
```

#### استعادة مستخدم محذوف
```
POST /api/v1/users/{id}/restore
```

#### حذف دائم
```
DELETE /api/v1/users/{id}/force
```

### تخصيص الفئات (Category Assignments)

#### تخصيص فئة لمستخدم
```
POST /api/v1/users/{id}/assign-category
```

**Body:**
```json
{
  "category_id": 1,
  "subcategory_id": 2  // اختياري
}
```

#### إزالة تخصيص فئة
```
DELETE /api/v1/users/{id}/assignments/{assignmentId}
```

### الأدوار (Roles)

#### قائمة الأدوار
```
GET /api/v1/roles
```

#### عرض دور واحد
```
GET /api/v1/roles/{id}
```

#### إنشاء دور جديد
```
POST /api/v1/roles
```

**Body:**
```json
{
  "name": "Admin",
  "description": "Administrator role"
}
```

#### تحديث دور
```
PUT /api/v1/roles/{id}
```

#### حذف دور
```
DELETE /api/v1/roles/{id}
```

## هيكل المشروع

```
user/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/
│   │   │       ├── Auth/
│   │   │       │   ├── LoginController.php
│   │   │       │   └── ValidateController.php
│   │   │       ├── UserController.php
│   │   │       └── RoleController.php
│   │   ├── Requests/
│   │   │   └── Api/V1/
│   │   └── Resources/
│   │       └── Api/V1/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Role.php
│   │   └── UserCategoryAssignment.php
│   └── Services/
│       ├── UserService.php
│       ├── RoleService.php
│       ├── Auth/
│       │   └── JwtService.php
│       └── Integration/
│           └── WSO2Service.php
├── database/
│   └── migrations/
├── routes/
│   └── api.php
└── docs/
    ├── openapi.yaml
    └── User_Atom.postman_collection.json
```

## النماذج (Models)

### User
- `id`: معرف المستخدم
- `name`: اسم المستخدم
- `email`: البريد الإلكتروني (فريد)
- `password`: كلمة المرور (مشفرة تلقائياً)
- `role_id`: معرف الدور
- `is_active`: حالة التفعيل
- `created_at`, `updated_at`, `deleted_at`: التواريخ

**العلاقات:**
- `role()`: علاقة مع Role
- `categoryAssignments()`: علاقة مع UserCategoryAssignment

### Role
- `id`: معرف الدور
- `name`: اسم الدور
- `description`: وصف الدور
- `created_at`, `updated_at`: التواريخ

### UserCategoryAssignment
- `id`: معرف التخصيص
- `user_id`: معرف المستخدم
- `category_id`: معرف الفئة
- `subcategory_id`: معرف التصنيف الفرعي (اختياري)
- `created_at`, `updated_at`: التواريخ

## الخدمات (Services)

### UserService
- `list(array $params)`: قائمة المستخدمين مع البحث والتصفية
- `create($data)`: إنشاء مستخدم جديد
- `find($id)`: البحث عن مستخدم
- `update($id, $data)`: تحديث مستخدم
- `delete($id)`: حذف ناعم
- `restore($id)`: استعادة مستخدم محذوف
- `forceDelete($id)`: حذف دائم
- `assignCategory($userId, $categoryId, $subcategoryId)`: تخصيص فئة
- `removeCategoryAssignment($userId, $assignmentId)`: إزالة تخصيص

### RoleService
- `list()`: قائمة الأدوار
- `create($data)`: إنشاء دور جديد
- `find($id)`: البحث عن دور
- `update($id, $data)`: تحديث دور
- `delete($id)`: حذف دور

### JwtService
- `generateToken($user)`: توليد JWT Token
- `validateToken($token)`: التحقق من صحة Token
- `decodeToken($token)`: فك تشفير Token

## Middleware

### auth.jwt
Middleware للتحقق من JWT Token في الطلبات المحمية. يتم تطبيقه على جميع routes المحمية.

## نظام الصلاحيات

- يتم تحديد الصلاحيات بناءً على الدور (Role)
- يمكن تخصيص فئات محددة للمستخدمين
- المستخدمون الإداريون لديهم صلاحيات كاملة
- المستخدمون العاديون مقيدون بالفئات المخصصة لهم

## التشغيل

```bash
# تشغيل الخادم
php artisan serve --port=8003

# أو استخدام Composer script
composer run dev
```

الخادم سيعمل على: `http://localhost:8003`

## الاختبارات

```bash
php artisan test
```

## التوثيق

- **OpenAPI/Swagger**: متوفر في `docs/openapi.yaml`
- **Postman Collection**: متوفر في `docs/User_Atom.postman_collection.json`

يمكن استيراد Postman Collection مباشرة في Postman للاختبار السريع.

## التكامل مع الأنظمة الأخرى

هذا المشروع يعمل كخدمة المصادقة المركزية ويتكامل مع:
- **Guest Atom**: للتحقق من الهوية والصلاحيات عند الموافقة على الضيوف
- **Category Atom**: للحصول على معلومات الفئات عند التخصيص
- **WSO2 API Gateway**: للتوجيه والتحكم في الوصول (اختياري)

## استخدام JWT Token في الأنظمة الأخرى

```php
// في Guest Atom أو أي نظام آخر
$token = $request->header('Authorization'); // Bearer {token}
$token = str_replace('Bearer ', '', $token);

// التحقق من Token عبر User Atom API
$response = Http::post('http://localhost:8003/api/v1/auth/validate', [
    'token' => $token
]);
```

## ملاحظات مهمة

1. جميع كلمات المرور يتم تشفيرها تلقائياً عند الحفظ
2. JWT Tokens لها تاريخ انتهاء (افتراضي: 3600 ثانية)
3. يجب تحديث JWT_SECRET في ملف `.env` في بيئة الإنتاج
4. يمكن استخدام WSO2 Gateway للتوجيه المركزي
5. Category Assignments تسمح بتخصيص فئات محددة للمستخدمين

## الترخيص

MIT License
