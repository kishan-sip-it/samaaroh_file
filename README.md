<div align="center">

# 💍 Samaaroh

### Premium Wedding Planning & Vendor Management Platform

A PHP + PostgreSQL web application for planning weddings, discovering services, managing vendors, handling bookings, and supporting customer/provider workflows through a server-rendered interface.

[![PHP](https://img.shields.io/badge/PHP-Server--Side-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Database-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Supabase](https://img.shields.io/badge/Supabase-Backend-3FCF8E?style=for-the-badge&logo=supabase&logoColor=white)](https://supabase.com/)
[![Render](https://img.shields.io/badge/Render-Deployed-46E3B7?style=for-the-badge&logo=render&logoColor=111111)](https://render.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-UI-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-Interactions-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![Repository](https://img.shields.io/badge/GitHub-Repository-181717?style=for-the-badge&logo=github)](https://github.com/kishan-sip-it/samaaroh_file)

### 🚀 [Live Demo — Samaaroh](https://samaaroh-uron.onrender.com/)

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
| Server | PHP 8.x |
| Database | PostgreSQL |
| Database platform | Supabase |
| Database access | PDO (PostgreSQL) |
| Hosting | Render |
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
├── tools/                 # Database/runtime maintenance utilities
├── docker/                # Render runtime startup configuration
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
Render Web Service
   │
   ▼
PHP application
   │
   ├── Shared includes
   ├── Session state
   ├── Validation
   └── PDO queries
          │
          ▼
   Supabase PostgreSQL
```

The application uses PHP sessions for user state and PDO for PostgreSQL access. Shared configuration and reusable UI pieces are kept under `config/` and `includes/`.

---

## 🗄️ Data Layer

The production application uses **PostgreSQL hosted by Supabase**. The runtime reads its connection from the Render `DATABASE_URL` environment variable, while the local WAMP setup can continue using the project's MySQL-compatible development configuration.

> **Security:** configure database credentials through your local/server environment rather than committing real passwords to Git. Never copy production credentials into this README.

---

## 🚀 Run Locally

### Requirements

- PHP 8.x recommended
- MySQL / MariaDB for the existing WAMP development setup
- Apache, XAMPP, WAMP, or another PHP-capable web server
- A browser

### 1. Clone

```bash
git clone https://github.com/kishan-sip-it/samaaroh_file.git
cd samaaroh_file
```

### 2. Configure the local database

Create the local MySQL database matching your local installation and configure the credentials through the local environment/configuration.

### 3. Start the application

For a simple PHP development server, from the project directory:

```bash
php -S localhost:8000
```

If your setup depends on Apache/XAMPP routing, place the project in the appropriate web root and open the configured project URL.

---

## ☁️ Production Deployment

Samaaroh is deployed on **Render** with **Supabase PostgreSQL**:

**Live:** https://samaaroh-uron.onrender.com/

The repository is connected to the Render web service so pushes to `main` trigger a fresh deployment.

---

## 🎨 UI Direction

The visual design combines:

- **Playfair Display** for premium editorial-style headings
- **Inter** for readable interface text
- Warm stone/cream surfaces
- Amber/rose accent colors
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
- Use least-privilege database credentials.

---

## 📌 Project Status

This repository contains the working Samaaroh application source, including customer, provider, authentication, booking, feedback, and administrative areas.

The current production stack is **PHP + Render + Supabase PostgreSQL**.

---

## 🔗 Links

**Live application:**
https://samaaroh-uron.onrender.com/

**Source code:**
https://github.com/kishan-sip-it/samaaroh_file

<div align="center">

### 💍 Built to make wedding planning feel simpler, more organized, and more connected.

</div>
