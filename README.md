<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="350" alt="Archive System API">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/API-Archive%20System-blue" />
  <img src="https://img.shields.io/badge/PostgreSQL-Database-blue" />
  <img src="https://img.shields.io/badge/PDF-Upload-green" />
  <img src="https://img.shields.io/badge/JWT-Auth-orange" />
</p>

---

# 📦 Archive System API

نظام أرشفة إلكتروني مبني كـ REST API لإدارة **الجوابات (Letters)** و**الردود (Replies)** مع دعم رفع ملفات PDF، البحث المتقدم، وتتبع العمليات (Audit Logs).

---

## 🧠 System Workflow

### 📩 إضافة جواب (Letter)
1. المستخدم يرفع ملف PDF
2. إدخال البيانات:
   - رقم الخطاب
   - الموضوع
   - الجهة
   - التاريخ
3. يتم حفظ البيانات في جدول `letters`
4. يتم تسجيل العملية في `audit_logs`

---

### 📎 إضافة رد (Reply)
1. اختيار جواب موجود
2. رفع ملف PDF للرد
3. ربط الرد بـ `letter_id`
4. تسجيل العملية في `audit_logs`

---

## 🧱 Database Schema (PostgreSQL)

### Tables:
- `letters`
- `replies`
- `audit_logs`
- `users`

---

## 🔐 Authentication & Roles

النظام يدعم JWT Authentication مع صلاحيات:

- 👑 Admin
- ✍️ Data Entry
- 👀 Viewer

---

## 🔎 Features

- رفع ملفات PDF (Letters / Replies)
- ربط الردود بالخطابات
- Audit Logs لتتبع كل العمليات
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

## ⚙️ Tech Stack

- Backend: Laravel API (أو Node.js Express)
- Database: PostgreSQL
- ORM: Eloquent / Prisma (حسب التنفيذ)
- Auth: JWT
- File Upload: PDF handling
- QR Code Generator

---

## 🚀 Installation

```bash
git clone https://github.com/your-username/archive-system.git
cd archive-system

composer install
cp .env.example .env

php artisan key:generate
