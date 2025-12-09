# Category Atom API

مشروع Laravel لإدارة الفئات (Categories) والتصنيفات الفرعية (Subcategories) كجزء من نظام Atoms الموزع.

## نظرة عامة

Category Atom هو خدمة مستقلة مسؤولة عن إدارة الفئات والتصنيفات الفرعية في النظام. يوفر API كامل لعمليات CRUD مع دعم البحث والتصفية والترقيم (Pagination) والحذف الناعم (Soft Deletes).

## المميزات

- ✅ إدارة كاملة للفئات والتصنيفات الفرعية
- ✅ عمليات CRUD كاملة (Create, Read, Update, Delete)
- ✅ البحث والتصفية
- ✅ الترقيم (Pagination)
- ✅ الحذف الناعم (Soft Deletes) مع إمكانية الاستعادة
- ✅ الحذف الدائم (Force Delete)
- ✅ علاقة بين الفئات والتصنيفات الفرعية
- ✅ API موثق بـ OpenAPI/Swagger

## المتطلبات

- PHP >= 8.2
- Composer
- Laravel 12.0
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

قم بتعديل ملف `.env` لإعداد قاعدة البيانات:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

## API Endpoints

### الفئات (Categories)

#### قائمة الفئات
```
GET /api/v1/categories
```

**Query Parameters:**
- `q` (string): البحث في اسم الفئة
- `is_active` (0|1): تصفية حسب الحالة
- `per_page` (integer): عدد العناصر في الصفحة (افتراضي: 15)

**مثال:**
```
GET /api/v1/categories?q=tech&is_active=1&per_page=20
```

#### عرض فئة واحدة
```
GET /api/v1/categories/{id}
```

#### إنشاء فئة جديدة
```
POST /api/v1/categories
```

**Body:**
```json
{
  "name": "Technology",
  "description": "Technology related categories",
  "is_active": true,
  "auto_approve": false
}
```

#### تحديث فئة
```
PUT /api/v1/categories/{id}
```

#### حذف فئة (Soft Delete)
```
DELETE /api/v1/categories/{id}
```

#### استعادة فئة محذوفة
```
POST /api/v1/categories/{id}/restore
```

#### حذف دائم
```
DELETE /api/v1/categories/{id}/force
```

### التصنيفات الفرعية (Subcategories)

#### قائمة التصنيفات الفرعية
```
GET /api/v1/subcategories
```

#### عرض التصنيفات الفرعية لفئة معينة
```
GET /api/v1/categories/{categoryId}/subcategories
```

#### عرض تصنيف فرعي واحد
```
GET /api/v1/subcategories/{id}
```

#### إنشاء تصنيف فرعي جديد
```
POST /api/v1/subcategories
```

**Body:**
```json
{
  "name": "Web Development",
  "description": "Web development subcategory",
  "category_id": 1,
  "is_active": true
}
```

#### تحديث تصنيف فرعي
```
PUT /api/v1/subcategories/{id}
```

#### حذف تصنيف فرعي (Soft Delete)
```
DELETE /api/v1/subcategories/{id}
```

#### استعادة تصنيف فرعي محذوف
```
POST /api/v1/subcategories/{id}/restore
```

#### حذف دائم
```
DELETE /api/v1/subcategories/{id}/force
```

## هيكل المشروع

```
category/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/
│   │   │       ├── CategoryController.php
│   │   │       └── SubcategoryController.php
│   │   ├── Requests/
│   │   │   └── Api/V1/
│   │   └── Resources/
│   │       └── Api/V1/
│   ├── Models/
│   │   ├── Category.php
│   │   └── Subcategory.php
│   └── Services/
│       ├── CategoryService.php
│       └── SubcategoryService.php
├── database/
│   └── migrations/
├── routes/
│   └── api.php
└── docs/
    └── openapi.yaml
```

## النماذج (Models)

### Category
- `id`: معرف الفئة
- `name`: اسم الفئة
- `description`: وصف الفئة
- `is_active`: حالة التفعيل
- `auto_approve`: الموافقة التلقائية
- `created_at`, `updated_at`, `deleted_at`: التواريخ

### Subcategory
- `id`: معرف التصنيف الفرعي
- `name`: اسم التصنيف الفرعي
- `description`: وصف التصنيف الفرعي
- `category_id`: معرف الفئة الأب
- `is_active`: حالة التفعيل
- `created_at`, `updated_at`, `deleted_at`: التواريخ

## الخدمات (Services)

### CategoryService
- `list(array $params)`: قائمة الفئات مع البحث والتصفية
- `create($data)`: إنشاء فئة جديدة
- `find($id)`: البحث عن فئة
- `update($id, $data)`: تحديث فئة
- `delete($id)`: حذف ناعم
- `restore($id)`: استعادة فئة محذوفة
- `forceDelete($id)`: حذف دائم

### SubcategoryService
- نفس العمليات للتصنيفات الفرعية

## التشغيل

```bash
# تشغيل الخادم
php artisan serve

# أو استخدام Composer script
composer run dev
```

الخادم سيعمل على: `http://localhost:8000`

## الاختبارات

```bash
php artisan test
```

## التوثيق

التوثيق الكامل متوفر في ملف `docs/openapi.yaml` ويمكن استيراده في أدوات مثل Postman أو Swagger UI.

## التكامل مع الأنظمة الأخرى

هذا المشروع جزء من نظام Atoms الموزع ويمكن التكامل مع:
- **Guest Atom**: لإدارة الضيوف المرتبطين بالفئات
- **User Atom**: لإدارة المستخدمين المرتبطين بالفئات

## الترخيص

MIT License
