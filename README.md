# 🌾 RizalAgriCultiva

**RizalAgriCultiva** is a web-based Agriculture Information and Management System designed to provide farmers, agricultural personnel, and the public with easy access to agricultural news, announcements, educational resources, and administrative services.

It streamlines content management while promoting digital accessibility for agricultural programs and information.

---

## 📖 Overview

RizalAgriCultiva serves as an online agricultural information portal with a dedicated administrator dashboard for managing website content.

It allows administrators to publish and manage:

* Announcements
* Articles and educational content
* Multimedia resources
* FAQs
* Agricultural updates

While providing visitors with a centralized platform for reliable agricultural information.

---

## ✨ Features

### 🌐 Public Website

* Responsive landing page
* Agricultural news and announcements
* Educational articles and resources
* Multimedia gallery
* Frequently Asked Questions (FAQ)
* Contact information page
* Visitor tracking and analytics

### 🛠️ Administrator Dashboard

* Secure administrator authentication
* Dashboard overview
* Announcement management
* Article management
* Multimedia management
* User management
* Archive and restore records
* Activity logs
* Report generation (PDF)
* Email notification system
* Website settings management

---

## 🧰 Technologies Used

### Backend

* PHP
* MySQL
* PHPMailer

### Frontend

* HTML5
* CSS3
* JavaScript

### Development Tools

* Visual Studio Code
* XAMPP / Apache
* phpMyAdmin
* Composer

---

## 📂 Project Structure

```
RizalAgriCultiva/
│
├── admin/          # Admin dashboard modules
├── assets/         # Images and media assets
├── css/            # Stylesheets
├── includes/       # DB connection & reusable PHP files
├── login/          # Authentication system
├── uploads/        # Uploaded files and media
├── vendor/         # Composer dependencies
├── Main/           # Main website pages
├── data/           # Data resources
└── index.php       # Landing page
```

---

## 🚀 Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/your-username/RizalAgriCultiva.git
   ```

2. Move the project to your **XAMPP `htdocs`** directory.

3. Create a MySQL database.

4. Import the provided SQL file into phpMyAdmin.

5. Configure database connection:

   ```
   includes/db.php
   ```

6. Install dependencies:

   ```bash
   composer install
   ```

7. Start Apache and MySQL using XAMPP.

8. Open in browser:

   ```
   http://localhost/RizalAgriCultiva/
   ```

---

## 📊 Core Modules

* User Authentication
* Content Management System
* News & Announcements
* Agricultural Articles
* Multimedia Gallery
* FAQ Management
* Activity Logging
* Visitor Analytics
* Email Notifications
* PDF Report Generation

---

## 🎯 Purpose

This project was developed to modernize the dissemination of agricultural information by providing a centralized digital platform that improves communication between agricultural offices and the community.

It reduces manual content management while increasing accessibility to important agricultural updates and educational materials.

---

## 🔒 Requirements

* PHP 8+
* MySQL
* Apache Server
* Composer
* PHPMailer

---

## 👨‍💻 Developer

Developed as an academic web application project for agricultural information management.

---

## 📄 License
