<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="350" alt="Archive System API">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/API-Archive%20System-blue" />
  <img src="https://img.shields.io/badge/PostgreSQL-Database-blue" />
  <img src="https://img.shields.io/badge/Laravel-Sanctum-green" />
  <img src="https://img.shields.io/badge/PDF-Upload-red" />
</p>

---

# 📦 Archive System API

نظام أرشفة إلكتروني مبني بـ **Laravel REST API** لإدارة الخطابات (Letters) والردود (Replies)، مع دعم رفع ملفات PDF، البحث المتقدم، وتسجيل كل العمليات داخل النظام (Audit Logs).

---

# 🧠 System Workflow

## 📩 إضافة خطاب (Letter)
1. المستخدم يرفع ملف PDF
2. إدخال البيانات:
   - رقم الخطاب
   - الموضوع
   - الجهة
   - التاريخ
3. يتم حفظ البيانات داخل جدول `letters`
4. يتم تسجيل العملية داخل `audit_logs`

---

## 📎 إضافة رد (Reply)
1. اختيار خطاب موجود
2. رفع ملف PDF للرد
3. ربط الرد بـ `letter_id`
4. حفظ البيانات في جدول `replies`
5. تسجيل العملية في `audit_logs`

---

# 🗄️ Database First Approach (PostgreSQL)

المشروع مبني باستخدام **Database First Approach** على PostgreSQL:

- تم تصميم قاعدة البيانات أولاً (Tables & Relationships)
- ثم تم بناء الـ API بناءً على الهيكل الموجود
- الجداول الأساسية:
  - `letters`
  - `replies`
  - `audit_logs`
  - `users`

✔ هذا يساعد على:
- سرعة تطوير النظام
- وضوح العلاقات بين الجداول
- سهولة التوسعة لاحقاً

---

# 🔐 Authentication (Laravel Sanctum)

النظام يستخدم **Laravel Sanctum** لحماية الـ API.

## 🔑 تسجيل الدخول
- يتم تسجيل الدخول عبر API
- يتم إصدار Token لكل مستخدم

## 📡 إرسال التوكن
Authorization: Bearer {token}
---

---

# 📡 API Endpoints

## 🔐 Authentication

POST /api/login
POST /api/logout
GET /api/me


✔ يستخدم Laravel Sanctum للتوثيق
---

## 📊 Dashboard

GET /api/dashboard

✔ عرض إحصائيات النظام
---

## 📩 Letters (الخطابات)

### عرض البيانات

GET /api/letters
GET /api/letters/{id}


### إنشاء / تعديل

POST /api/letters
PUT /api/letters/{id}


### حذف واسترجاع (Admin فقط)

DELETE /api/letters/{id}
POST /api/letters/{id}/restore


---

## 📎 Replies (الردود)

### عرض

GET /api/letters/{id}/replies
GET /api/replies/{id}


### إنشاء / تعديل

POST /api/letters/{id}/replies
PUT /api/replies/{id}


### حذف واسترجاع (Admin فقط)

DELETE /api/replies/{id}
POST /api/replies/{id}/restore


---

## 📁 Attachments (PDF Files)

### Letters Attachments

GET /api/attachments/{id}/view
GET /api/attachments/{id}/download


### Replies Attachments

GET /api/reply-attachments/{id}/view
GET /api/reply-attachments/{id}/download


---

## 🔐 Roles & Permissions

النظام يعتمد على Middleware:

- 👑 `admin`
- ✍️ `data_entry`
- 👀 `viewer`

### الصلاحيات:

| Feature | Admin | Data Entry | Viewer |
|--------|------|------------|--------|
| View Letters | ✔ | ✔ | ✔ |
| Create Letters | ✔ | ✔ | ❌ |
| Edit Letters | ✔ | ✔ | ❌ |
| Delete Letters | ✔ | ❌ | ❌ |
| Restore Data | ✔ | ❌ | ❌ |

---

## 🔒 Middleware Protection

- جميع الـ routes محمية بـ `auth:sanctum`
- بعض العمليات محمية بـ:
  - `role:admin`
  - `role:admin,data_entry`

---

## 📌 Notes

- كل الملفات (PDF) يتم رفعها وربطها بالخطابات أو الردود
- النظام يسجل كل العمليات داخل `audit_logs`
- جميع الـ endpoints ترجع JSON API responses
---


# 🔎 Features

- رفع ملفات PDF (Letters / Replies)
- ربط الردود بالخطابات
- Audit Logs لتسجيل كل العمليات
- بحث متقدم:
  - رقم الخطاب
  - الموضوع
  - الجهة
  - التاريخ
- فلترة زمنية (Date Range)
- Dashboard إحصائيات
- أرشفة سنوية تلقائية
- QR Code لكل خطاب

---

# 🧾 Audit Logs System

كل العمليات داخل النظام يتم تسجيلها مثل:
- إنشاء خطاب
- تعديل خطاب
- إضافة رد
- رفع ملفات PDF

---

# ⚙️ Tech Stack

- Laravel 10+
- PostgreSQL
- Laravel Sanctum
- Eloquent ORM
- File Storage (PDF Upload)
- QR Code Generator

---

# 🚀 Installation

```bash
git clone https://github.com/Ahmedheshamesmail/archive-system.git
cd archive-system

composer install

cp .env.example .env
php artisan key:generate
