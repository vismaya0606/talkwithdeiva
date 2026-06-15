# Multi-Tenant Personal Brand Website (PHP + MySQL)

A lightweight, mobile-responsive, SEO-friendly personal brand / coaching
website built with **HTML5, CSS3, Bootstrap 5, vanilla JavaScript, PHP 8+
and MySQL**. It runs directly on **standard cPanel shared hosting** — no
Node.js, build step, or framework required.

It is designed as a **reusable SaaS template**: one codebase serves many
customers (multi-tenant). Each tenant has its own domain, theme, services,
testimonials, gallery, registrations and admin login. A **Super Admin**
panel manages all tenants.

---

## ✨ Features

**Public site**
- Hero banner (profile image, name, designation, tagline, CTA buttons)
- About section with achievements
- Services cards (admin-editable)
- Image gallery (admin-editable)
- Testimonials slider (admin-editable)
- Contact section with WhatsApp + Email buttons
- Registration form (Name, Mobile, Email, City, State, Profession,
  Interested Service, Message)
- **Sticky "Register Now"** button on every page that scrolls to the form
- Dynamic SEO meta, Open Graph tags, `sitemap.xml`, `robots.txt`

**Admin dashboard** (`/admin/`)
- Secure login (bcrypt password hashing, CSRF, session management)
- Dashboard stats (total + today's registrations, services, testimonials, gallery)
- Theme management (primary/secondary colour, logo, favicon, site name, footer)
- Homepage management (hero, about, contact)
- Services CRUD · Gallery CRUD · Testimonials CRUD
- Registrations: view, search, filter by date, **export to CSV (Excel)**

**Super Admin** (`/superadmin/`)
- Create / activate / deactivate / delete tenants
- Auto-seeds default settings + a tenant admin for each new tenant

**Security**
- Password hashing (`password_hash`/`password_verify`)
- CSRF tokens on every form
- Prepared statements everywhere (SQL-injection safe)
- File upload validation (MIME + size, PHP execution disabled in uploads)
- Hardened `.htaccess` (blocks `config/`, `includes/`, directory listing)

---

## 📁 Project Structure

```
public_html/
├── index.php            # Home (all sections)
├── about.php
├── contact.php
├── register.php         # Registration form + handler
├── sitemap.php          # served as /sitemap.xml
├── robots.txt
├── .htaccess
│
├── admin/
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── homepage.php
│   ├── services.php
│   ├── gallery.php
│   ├── testimonials.php
│   ├── registrations.php
│   ├── settings.php
│   └── inc/ (header.php, footer.php)
│
├── superadmin/
│   └── tenants.php
│
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   └── uploads/ (logos, gallery, testimonials)
│
├── includes/ (header.php, footer.php, register-form.php)
│
├── config/
│   ├── db.php           # <-- edit DB credentials here
│   └── functions.php
│
└── database/
    └── schema.sql
```

---

## 🚀 Deployment on cPanel Shared Hosting

1. **Create the database**
   - cPanel → **MySQL® Databases** → create a database and a user.
   - Add the user to the database with **ALL PRIVILEGES**.

2. **Import the schema**
   - cPanel → **phpMyAdmin** → select your database → **Import** →
     upload `database/schema.sql` → **Go**.

3. **Configure credentials**
   - Edit `config/db.php` and set `DB_NAME`, `DB_USER`, `DB_PASS`
     (host is usually `localhost`).

4. **Upload the files**
   - Upload **all files** into `public_html/` (or the domain's document
     root) via cPanel File Manager or FTP. Keep the folder structure.
   - Ensure `assets/uploads/` and its subfolders are writable (chmod `755`).

5. **Set your domain** (single-tenant use)
   - In phpMyAdmin, edit the `tenants` table row and set `domain` to your
     real domain (without `www.`). The site matches the visitor's domain
     to a tenant; if none matches it falls back to the first active tenant.

6. **Log in and secure**
   - Visit `https://yourdomain.com/admin/login.php`
   - **Default tenant admin:** `admin` / `admin123`
   - **Default super admin:** `superadmin` / `super123`
   - ⚠️ **Change both passwords immediately** (and update the DB).

---

## 🏢 Multi-Tenant (SaaS) Setup

1. Log in as **Super Admin** → you land on `/superadmin/tenants.php`.
2. **Add a tenant**: enter the business name, its domain, and the admin
   username/password. This creates the tenant, seeds default settings, and
   creates that tenant's admin account.
3. In cPanel, add the tenant's domain as an **Addon Domain** (or park it)
   pointing to the **same** `public_html` document root.
4. The visited domain determines which tenant's content is shown. Each
   tenant admin manages only their own data at
   `https://theirdomain.com/admin/login.php`.

Every business table (`settings`, `services`, `testimonials`, `gallery`,
`registrations`, `admins`) carries a `tenant_id`, so data is fully isolated
per tenant.

---

## 🔧 Local Development

```bash
# from the project root (requires PHP 8+ and a MySQL/MariaDB server)
php -S localhost:8000
```

Import `database/schema.sql` into a local MySQL database and update
`config/db.php`. The seed data uses the domain `localhost`, so it works out
of the box. Visit:
- Site: <http://localhost:8000/>
- Admin: <http://localhost:8000/admin/login.php>

---

## 🔐 Default Credentials (change after install!)

| Role        | Username     | Password   |
|-------------|--------------|------------|
| Super Admin | `superadmin` | `super123` |
| Tenant Admin| `admin`      | `admin123` |

To reset a password, generate a hash and update the `admins` table:

```bash
php -r 'echo password_hash("YourNewPassword", PASSWORD_DEFAULT), PHP_EOL;'
```
```sql
UPDATE admins SET password = 'PASTE_HASH_HERE' WHERE username = 'admin';
```
