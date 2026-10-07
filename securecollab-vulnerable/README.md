# SecureCollab — PHP/MySQL Team Collaboration & Security Demonstration

SecureCollab is a full-stack PHP & MySQL team collaboration web application designed to demonstrate 5 core web application security vulnerabilities and their corresponding industry-standard mitigations.

---

## 🚀 Key Project Highlights
- **Two Completely Separate Web Applications**:
  - `http://localhost/securecollab-vulnerable/` (Vulnerable Version)
  - `http://localhost/securecollab-secure/` (Secure Version)
- **Authentic Enterprise UI/UX**: Built with modern Bootstrap 5 styling, font-awesome icons, clean cards, and responsive layout. Does NOT display tacky "Security Lab" banners. Both apps look identical to end users.
- **Real Feature Implementations**: Full authentication system, workspace projects, project search, discussion threads, comment timelines, team roster management, file repository, user profiles, admin control console, and activity audit logs.

---

## 📋 Requirements
- **OS**: Windows (Standard XAMPP Installation)
- **Web Server**: Apache
- **Database**: MySQL / MariaDB
- **PHP**: PHP 8.x
- **Database Tool**: phpMyAdmin

---

## 🛠️ Quick Installation & Database Setup

### Step 1: Start XAMPP
Open **XAMPP Control Panel** on Windows and click **Start** for:
- **Apache**
- **MySQL**

### Step 2: Initialize Database
You can initialize the database using either of two methods:

#### Method A: One-Click Web Installer (Recommended)
Open your web browser and navigate to:
`http://localhost/securecollab-vulnerable/database/setup_db.php`
*This automatically creates the `securecollab` database, sets up all 7 relational tables, and inserts initial seed data.*

#### Method B: phpMyAdmin SQL Import
1. Open `http://localhost/phpmyadmin/` in your browser.
2. Click **Import** tab.
3. Choose file: `C:\xampp\htdocs\securecollab-vulnerable\database\securecollab.sql`.
4. Click **Go** to execute the SQL script.

---

## 🔑 Login Credentials

| User Role | Email Address | Password | Privileges |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@securecollab.local` | `SecureDemo!2026` | Full Access + Admin Console & Security Matrix |
| **Normal User** | `user@securecollab.local` | `SecureDemo!2026` | Projects, Discussions, Files, Profile |
| **Team Lead** | `sarah@securecollab.local` | `SecureDemo!2026` | Projects, Discussions, Files, Profile |

---

## 🛡️ Vulnerability & Mitigation Breakdown

| # | Vulnerability | Application Feature | Vulnerable File | Secure File | Mitigation Used |
|---|---|---|---|---|---|
| **1** | **Reflected XSS** | Project Search | `projects/search.php` | `projects/search.php` | `htmlspecialchars($q, ENT_QUOTES \| ENT_SUBSTITUTE, "UTF-8")` |
| **2** | **Stored XSS** | Discussion Comments | `discussions/view.php` | `discussions/view.php` | Output encoding with `htmlspecialchars()` on rendering |
| **3** | **DOM-Based XSS** | Dynamic Project Preview | `assets/js/preview.js` | `assets/js/preview.js` | Safe DOM property `textContent` instead of `innerHTML` |
| **4** | **CSRF** | Change Email | `profile/change_email.php` | `profile/change_email.php` | Session anti-CSRF token + `hash_equals()` validation |
| **5** | **Clickjacking** | Archive Project | `projects/archive.php` | `projects/archive.php` | `X-Frame-Options: SAMEORIGIN` & CSP `frame-ancestors 'self'` |

---

## 📂 Project Structure

```
C:\xampp\htdocs\securecollab-vulnerable\
├── config/
│   └── database.php       # Database connection
├── includes/
│   ├── auth.php
│   ├── functions.php      # Helpers & CSRF handling
│   ├── header.php         # Navbar & Anti-Clickjacking headers
│   ├── footer.php
│   └── sidebar.php
├── assets/
│   ├── css/style.css
│   └── js/preview.js      # Client-side DOM preview script
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── dashboard/
│   └── index.php          # Workspace dashboard
├── projects/
│   ├── index.php
│   ├── create.php
│   ├── view.php
│   ├── search.php        # Reflected XSS Target
│   ├── members.php
│   └── archive.php       # Clickjacking Target
├── discussions/
│   ├── index.php
│   ├── create.php
│   └── view.php          # Stored XSS Target
├── profile/
│   ├── index.php
│   └── change_email.php  # CSRF Target
├── files/
│   ├── index.php
│   └── upload.php
├── admin/
│   ├── index.php
│   ├── logs.php
│   └── security.php      # Faculty Audit Matrix
├── demos/
│   ├── csrf_poc.html
│   └── clickjacking_poc.html
└── database/
    ├── securecollab.sql
    └── setup_db.php
```

---

## 🎓 Faculty Demonstration Script
For detailed step-by-step presentation instructions, payload commands, and viva preparation, refer to **`FACULTY_DEMO.md`**.