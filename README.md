<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-Archive%20System-red" alt="Project Badge">
  <img src="https://img.shields.io/badge/PHP-8.x-blue" alt="PHP Version">
  <img src="https://img.shields.io/badge/MySQL-Database-orange" alt="Database">
</p>

---

## 📦 Archive System

**Archive System** هو مشروع مبني باستخدام إطار العمل Laravel، يهدف إلى إدارة الأرشفة وتنظيم البيانات داخل النظام بشكل سهل ومرن، مع دعم قواعد بيانات MySQL.

---

## 🚀 Features

- إدارة البيانات (Create / Read / Update / Delete)
- أرشفة السجلات داخل قاعدة البيانات
- تنظيم الملفات والبيانات بشكل منظم داخل النظام
- استخدام Laravel MVC Architecture
- دعم MySQL Database

---

## 🛠️ Tech Stack

- Laravel Framework
- PHP 8+
- MySQL
- Blade Templates
- Eloquent ORM

---

## ⚙️ Installation

اتبع الخطوات التالية لتشغيل المشروع محلياً:

```bash
# Clone the repository
git clone https://github.com/Ahmedheshamesmail/archive-system.git

# Enter project directory
cd archive-system

# Install dependencies
composer install

# Copy .env file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database in .env

# Run migrations
php artisan migrate

# Start server
php artisan serve
