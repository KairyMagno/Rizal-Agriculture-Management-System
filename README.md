# 🌾 RizalAgriCultiva

**RizalAgriCultiva** is a web-based Agriculture Information and Management System developed to provide farmers, agricultural personnel, and the public with easy access to agricultural news, announcements, educational resources, and administrative services. The platform streamlines content management while promoting digital accessibility for agricultural programs and information.

---

## 📖 Overview

The system serves as an online agricultural information portal with a dedicated administrator dashboard for managing website content. It enables administrators to publish announcements, articles, multimedia resources, frequently asked questions, and other agricultural updates while providing visitors with a centralized platform for accessing reliable information.

---

## ✨ Features

### Public Website

* Responsive landing page
* Agriculture news and announcements
* Educational articles and resources
* Multimedia gallery
* Frequently Asked Questions (FAQ)
* Contact information
* Visitor tracking and analytics

### Administrator Dashboard

* Secure administrator authentication
* Dashboard overview
* Announcement management
* Article management
* Multimedia management
* User management
* Archive and restore records
* Activity logs
* Report generation (PDF)
* Email notification support
* Website settings management

---

## 🛠️ Technologies Used

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

---

## 📂 Project Structure

```text
RizalAgriCultiva/
│
├── admin/           # Administrator dashboard and management modules
├── assets/          # Images and website assets
├── css/             # Stylesheets
├── includes/        # Database connection and reusable PHP files
├── login/           # Authentication pages
├── uploads/         # Uploaded files and media
├── vendor/          # Composer dependencies
├── Main/            # Main website pages
├── data/            # Data resources
└── index.php        # Landing page
```

---

## 🚀 Installation

1. Clone the repository.

```bash
git clone https://github.com/your-username/RizalAgriCultiva.git
```

2. Move the project into your XAMPP `htdocs` directory.

3. Create a MySQL database.

4. Import the provided SQL database.

5. Configure the database connection inside:

```text
includes/db.php
```

6. Install PHP dependencies.

```bash
composer install
```

7. Start Apache and MySQL using XAMPP.

8. Open your browser and navigate to:

```text
http://localhost/RizalAgriCultiva/
```

---

## 📊 Core Modules

* User Authentication
* Content Management
* News & Announcements
* Agricultural Articles
* Multimedia Gallery
* FAQ Management
* Activity Logging
* Visitor Analytics
* Email Notifications
* PDF Report Generation

---

## 📌 Purpose

The project was developed to modernize the dissemination of agricultural information by providing a centralized digital platform that improves communication between agricultural offices and the community. It reduces manual content management while increasing accessibility to important agricultural updates and educational materials.

---

## 🔒 Dependencies

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

This project is intended for educational and academic purposes. Feel free to fork and modify the source code for learning and research.
