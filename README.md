<div align="center">

# 💍 Samaaroh

### Premium Wedding Planning & Vendor Management Platform

A PHP + MySQL web application for planning weddings, discovering services, managing vendors, handling bookings, and supporting customer/provider workflows through a server-rendered interface.

[![PHP](https://img.shields.io/badge/PHP-Server--Side-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-UI-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-Interactions-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![Repository](https://img.shields.io/badge/GitHub-Repository-181717?style=for-the-badge&logo=github)](https://github.com/kishan-sip-it/samaaroh_file)

</div>

---

## ✨ What is Samaaroh?

**Samaaroh** is a wedding-planning web platform designed to bring wedding customers and service providers into one place.

The application provides a customer-facing experience for discovering and booking wedding services, alongside provider/admin functionality for managing services and operational data.

The landing page presents wedding services such as vendors, caterers, photographers, and decorators, while the application contains dedicated customer, provider, authentication, booking, feedback, contact, and administration areas.

---

## 🎯 Core Features

- 💍 Wedding planning landing experience
- 🧑‍💼 Customer and provider workflows
- 🏪 Service/vendor discovery
- 📅 Booking management
- 🔐 Registration and login flows
- 🔑 Forgot-password flow
- ⭐ Customer feedback functionality
- 📩 Contact functionality
- 🛠️ Provider/admin management area
- 🧾 Invoice generation
- 🖼️ Image-based wedding/service presentation
- 📱 Responsive UI with Tailwind CSS utilities

---

## 🧰 Tech Stack

| Layer | Technology |
|---|---|
| Server | PHP |
| Database | MySQL |
| Database access | PDO |
| Styling | Tailwind CSS + custom CSS |
| Client-side interaction | JavaScript |
| Typography | Playfair Display + Inter |
| Architecture | Server-rendered PHP pages + shared includes |

---

## 🏗️ Application Structure

```text
samaaroh_file/
├── admin/                 # Administrative/provider management area
├── customer/              # Customer-facing workflows
├── provider/              # Provider/service workflows
├── config/                # Application/database configuration
├── includes/              # Shared PHP components such as navigation
├── assets/                # CSS and frontend assets
├── images/                # Application imagery
├── index.php              # Main landing page
├── about.php              # About page
├── contact.php            # Contact workflow
├── feedback.php           # Feedback workflow
├── login.php              # Authentication entry point
├── register.php           # Registration
├── forgot-password.php    # Password recovery
└── invoice.php            # Invoice generation
```

---

## 🔄 Request Flow

```text
Browser
   │
   ▼
PHP page / workflow
   │
   ├── Shared includes
   ├── Session state
   ├── Validation
   └── PDO queries
          │
          ▼
       MySQL
```

The application uses PHP sessions for user state and PDO for MySQL access. Shared configuration and reusable UI pieces are kept under `config/` and `includes/`.

---

## 🗄️ Data Layer

The application is backed by **MySQL**. The database configuration is centralized in `config/config.php`, and the application connects through PHP's PDO interface.

> **Security:** configure database credentials through your local/server environment rather than committing real passwords to Git. Never copy production credentials into this README.

---

## 🚀 Run Locally

### Requirements

- PHP 8.x recommended
- MySQL / MariaDB
- Apache, XAMPP, WAMP, or another PHP-capable web server
- A browser

### 1. Clone

```bash
git clone https://github.com/kishan-sip-it/samaaroh_file.git
cd samaaroh_file
```

### 2. Create the database

Create a MySQL database matching the database name configured for your local installation, then import the project's SQL/schema data if supplied with your working copy.

### 3. Configure the connection

Update `config/config.php` for your local MySQL host, database, username, and password.

Do **not** commit real credentials.

### 4. Configure the base URL

The project currently uses a configurable `BASE_URL` constant. If you serve the project from a different path, update that value to match your local web-server path.

### 5. Start the server

For a simple PHP development server, from the project directory:

```bash
php -S localhost:8000
```

If your setup depends on Apache/XAMPP routing, place the project in the appropriate web root and open the configured project URL.

---

## 🎨 UI Direction

The visual design combines:

- **Playfair Display** for premium editorial-style headings
- **Inter** for readable interface text
- Warm stone/cream surfaces
- Amber accent colors
- Large photographic hero sections
- Responsive Tailwind utility classes
- Animated/interactive presentation elements

---

## 🔒 Security Notes

Before deploying this application publicly:

- Move database credentials out of source control.
- Use environment/server configuration for secrets.
- Enable HTTPS.
- Validate and sanitize all user-controlled input.
- Review file-upload handling and permissions.
- Use secure session/cookie settings.
- Disable verbose database errors in production.
- Use least-privilege MySQL credentials.

---

## 📌 Project Status

This repository contains the working Samaaroh application source, including customer, provider, authentication, booking, feedback, and administrative areas.

For the exact implementation, browse the source directories and PHP entry points in the repository.

---

## 🔗 Links

**Source code:**
https://github.com/kishan-sip-it/samaaroh_file

<div align="center">

### 💍 Built to make wedding planning feel simpler, more organized, and more connected.

</div>
