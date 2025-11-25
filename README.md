# P3KU EmpowerAbility Platform

The **P3KU EmpowerAbility Platform** is a web-based system developed to help
**Kelab Kebun Komuniti Anak Istimewa (P3KU)** digitalize participant management,
task assignment, progress tracking, and parent/admin monitoring.

It is built using **PHP**, **MySQL**, **HTML/CSS**, and follows an MVC-style structure (Controllers, Models, Views).

---

## 🌟 Key Features

### 👤 1. User Registration & Login

* Users can register for an account (Admin, Parent, Participant depending on role setup).
* Secure login system with session handling.
* Role-based redirection to dashboards.

---

### 📊 2. User Dashboard

Each user has a personalized dashboard showing:

* Overview of tasks or activities
* Progress summary
* Notifications or updates
* Access to skill tracking and journals (depending on role)

---

### 📝 3. Digital Journal (Participant Feature)

Participants can:

* Create new journal entries
* Edit or update previous entries
* Delete entries
* Keep a private reflection log for personal growth

---

### 🎯 4. Skill Tracker

Participants or admins can:

* Add new skills
* Track skill progress in stages (e.g., Beginner → Improving → Mastered)
* Update skill status anytime
* View progression history

---

### 🏅 5. Badge System

The platform awards badges when a participant:

* Completes milestones
* Reaches specific skill levels
* Achieves activity streaks

Badges help motivate continuous learning and participation.

---

### 🧱 6. Community Wall

A public “Community Wall” where users can:

* Post updates
* Share achievements
* View posts from other users
* Edit or delete their own posts

---

### 🛠 7. Admin Panel

Admins can:

* Manage participants
* Create or assign tasks
* Monitor user progress and reports
* View logs and submissions

---

## 📁 Project Structure

```
p3ku_platform/
│
├── controllers/        # Business logic for each module
├── models/             # Database interaction (CRUD logic)
├── views/              # HTML/PHP views
├── api/                # API endpoints (if applicable)
├── config/             # Configuration files (DB, environment)
├── sql/                # SQL scripts for database creation
├── assets/             # Images, CSS, JavaScript
├── index.php           # App entry point
└── README.md
```

---

# 🚀 How the System Works (High-Level Flow)

### 1. **User logs in**

* Login request is sent to the `AuthController`
* Credentials are validated
* User session is created
* User is redirected based on role

### 2. **Dashboard loads**

* Controller retrieves user-related data
* Dashboard view renders tasks / skills / journals / updates

### 3. **Participant updates progress**

* Participant writes journal → saved to database
* Participant updates skills → system records progression
* Participant completes tasks → badges awarded if criteria met

### 4. **Admin manages system**

* Admin creates tasks
* Admin assigns tasks
* Admin views participant progress
* Admin monitors overall system activity

---

# 🛠️ Installation & Setup Guide

Follow these steps to run the project on **XAMPP**, **MAMP**, or any local PHP server.

---

## 1️⃣ Requirements

* PHP 7.4 or above
* MySQL 5.7 or above
* Apache (via XAMPP / MAMP / WAMP)
* Git (optional)

---

## 2️⃣ Download or Clone the Project

### **Option A: Clone using Git**

```
git clone https://github.com/Wehttam10/p3ku_platform.git
```

### **Option B: Download ZIP**

* Go to the repository on GitHub
* Click **Code → Download ZIP**
* Extract into your server folder (`htdocs` for XAMPP)

---

## 3️⃣ Configure the Database

1. Start **Apache** and **MySQL** in XAMPP/MAMP
2. Open **phpMyAdmin**
3. Create a new database:

```
p3ku_platform
```

4. Import the SQL file:

   * Go to `Import`
   * Select the file from:
     **/sql/database.sql**
   * Click **Import**

---

## 4️⃣ Configure the Application

1. Open the folder:

```
config/
```

2. Copy:

```
config.php.example → config.php
```

3. Open `config.php` and update your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'p3ku_platform');
define('DB_USER', 'root');
define('DB_PASS', '');
```

> ⚠️ If your MySQL has a password, update it.

---

## 5️⃣ Run the System

Open your browser and go to:

```
http://localhost/p3ku_platform/
```

You should see the login page.

---

# 🧪 Testing

* Create a test user through the registration page
* Log in and explore the features
* Check if dashboards, journals, tasks, and skill tracker function correctly

---

# 🤝 Contribution Guidelines

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Submit a Pull Request

---

# 📄 License

This project is licensed under **MIT License** (update if different).

---

# 📬 Contact

For issues or enhancements: open a GitHub Issue
For direct help: contact the project maintainer (Matthew)

---
